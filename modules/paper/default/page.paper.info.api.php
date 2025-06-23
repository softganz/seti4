<?php
/**
* Paper   :: Information API
* Created :: 2021-11-22
* Modify  :: 2025-06-23
* Version :: 7
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage api/paper/{nodeId}/{action}[/{tranId}]
*/

use Paper\Model\PaperModel;

class PaperInfoApi extends Page {
	var $topicId;
	var $action;
	var $tranId;
	var $topicInfo;

	function __construct($topicId, $action, $tranId = NULL) {
		$this->topicId = $topicId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->topicInfo = PaperModel::get($this->topicId, '{initTemplate: true}');
	}

	function build() {
		// debugMsg('topicId '.$this->topicId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$topicInfo = $this->topicInfo;
		$tpid = $topicInfo->tpid;
		$tranId = $this->tranId;

		if (empty($this->topicId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $topicInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $topicInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		$isAddmin = user_access('administer contents,administer papers');
		$isEdit = $topicInfo->RIGHT & _IS_EDITABLE;

		if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
		else if (!$topicInfo) return message('error', 'TOPIC NOT FOUND.');
		else if (!$isEdit) return message('error', 'Access Denied');

		$ret = '';
		switch ($this->action) {


			case 'poll.update':
				$ret .= 'Create Poll';
				$data = (Object) post('poll',_TRIM+_STRIPTAG);
				if (!mydb::select('SELECT `tpid` FROM %poll% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $tpid)->tpid) {
					$stmt = 'INSERT INTO %poll% (`tpid`, `start_date`, `end_date`, `created`) VALUES (:tpid, :start_date, :end_date, :created)';

					mydb::query($stmt,':tpid',$tpid, ':start_date',date('Y-m-d H:i:s'), ':end_date', 'func.NULL', ':created',date('Y-m-d H:i:s'));
					//$ret .= mydb()->_query.'<br />';
				}

				foreach ($data as $k=>$v) {
					if (mydb::select('SELECT `choice` FROM %poll_choice% WHERE `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$tpid, ':choice',$k)->choice) {
						if ($v=='') { // Delete on empty
							mydb::query('DELETE FROM %poll_choice% WHERE  `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$tpid, ':choice',$k);
						} else { // Update
							mydb::query('UPDATE %poll_choice% SET `detail`=:detail WHERE  `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$tpid, ':choice',$k,':detail',$v);
						}
						//$ret.='Update '.mydb()->_query.'<br />';
					} else {
						if ($v=='') {
						} else {
							mydb::query('INSERT INTO %poll_choice% (`tpid`, `choice`, `detail`) VALUES (:tpid, :choice, :detail)',':tpid',$tpid, ':choice',$k, ':detail',$v);
							//$ret.='Insert '.mydb()->_query.'<br />';
						}
					}
				}
				//$ret .= print_o($data,'$data');
				//location('paper/'.$topic->tpid);
				break;

			case 'comment.save':
				$post = (Object) post('comment');
				$stmt = mydb::create_update_cmd('%topic_comments%',$post,'cid='.$tranId);
				mydb::query($stmt,$post);

				$photo = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :commentId AND `type`="photo" LIMIT 1', ':tpid', $tpid, ':commentId', $tranId);

				if (post('delete_photo') && $photo->fid) {
					if ($photo->file) {
						$oldfile = cfg('paper.upload.photo.folder').$photo->file;
						if (file_exists($oldfile) && is_file($oldfile)) unlink($oldfile);
					}
					mydb::query('DELETE FROM %topic_files% WHERE `fid` = :fid LIMIT 1', ':fid', $photo->fid);
				}

				// save upload photo
				if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
					$photo = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :commentId AND `type`="photo" LIMIT 1', ':tpid', $tpid, ':commentId', $tranId);
					$folder = cfg('paper.upload.photo.folder');

					$upload = new classFile($_FILES['photo'],$folder,cfg('photo.file_type'));
					if (!$upload->valid_format()) $error[] = 'Invalid upload file format';

					if (!user_access('administer contents,administer papers') && !$upload->valid_size(cfg('photo.max_file_size')*1024)) $error[]='Invalid upload file size :maximun file size is '.cfg('photo.max_file_size').' KB.';

					if (!$error) {
						if ($upload->duplicate()) $upload->generate_nextfile();
						if (!$upload->copy()) $error[] = 'Save file error (unknown error)';
						if ($error) return $ret.message('error',$error);
						$photo_upload = $upload->filename;

						if ($photo->_num_rows) {
							$oldfile = cfg('paper.upload.photo.folder').$photo->file;
							// debugMsg('Remove photo '.$oldfile);
							if (file_exists($oldfile) && is_file($oldfile)) unlink($oldfile);

							mydb::query('UPDATE %topic_files% SET `file` = :filename WHERE `fid` = :fid LIMIT 1', ':filename', $photo_upload, ':fid', $photo->fid);
						} else {
							$photo->tpid = $tpid;
							$photo->cid = $tranId;
							$photo->type = 'photo';
							$photo->uid = i()->uid;
							$photo->file = $photo_upload;
							$photo->timestamp = 'func.NOW()';
							$photo->ip = ip2long(GetEnv('REMOTE_ADDR'));
							mydb::query(mydb::create_insert_cmd('%topic_files%',$photo),$photo);
						}
					}
				}

				LogModel::save([
					'module' => 'paper',
					'keyword' => 'Paper comment edit',
					'message' => 'Edit comment id '.$tranId.' of <a href="'.url('paper/'.$tpid.'#comment-'.$tranId).'">paper/'.$tpid.'</a>'
				]);
				// if ($_SERVER['HTTP_REFERER']) location($_SERVER['HTTP_REFERER']); else location('paper/'.$tpid);

				break;

			case 'comment.hide':
				$ret .= 'Hide comment';
				if ($tranId) {
					$stmt = 'UPDATE %topic_comments% SET `status` = IF(`status` = '._BLOCK.','._PUBLISH.','._BLOCK.') WHERE `tpid` = :tpid AND `cid` = :cid LIMIT 1';
					mydb::query($stmt, ':tpid', $tpid, ':cid', $tranId);
					//$ret .= mydb()->_query;
				}
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>