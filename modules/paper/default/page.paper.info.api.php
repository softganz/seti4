<?php
/**
* Paper :: Information API
* Created 2021-11-22
* Modify  2021-11-22
*
* @param Int $topicId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage paper/info/api/{id}/{action}[/{tranId}]
*/

$debug = true;

class PaperInfoApi extends Page {
	var $topicId;
	var $action;
	var $tranId;
	var $topicInfo;

	function __construct($topicId, $action, $tranId = NULL) {
		$this->topicId = $topicId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->topicInfo = R::Model('paper.get', $this->topicId, '{initTemplate: true}');
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
		case 'photo.add':
			if (post('upload')) {
				$is_simulate = debug('simulate');
				$desc = (Object) post('info',_TRIM+_STRIPTAG);
				$desc->tpid = $topicInfo->tpid;
				$desc->type = 'photo';

				$options = new stdClass;
				$options->debug = false;
				if ($desc->norename) $options->useSourceFilename = true;

				//$ret .= print_o($desc,'$desc');

				$result = R::Model('photo.upload', $_FILES['photo'], $desc, $options);

				//$topicInfo = R::Model('paper.get', $topicInfo->tpid);
				//$ret .= R::Page('paper.edit.photo', NULL, $topicInfo);
				//$ret .= print_o($result,'$result');
				/*
				$uploads = array();
				foreach ($photos as $photo) {
					if (is_uploaded_file($photo->tmp_name)) {
						$result = R::Model('photo.save',$photo);
						if ($result->complete && $result->save->_file) {
							$uploads[]=$desc->file=$result->save->_file;
							$stmt = mydb::create_insert_cmd('%topic_files%',$desc);
							//$ret.='$stmt='.$stmt.'<br />';
							mydb::query($stmt,$desc);
							//$ret.='_query='.mydb()->_query.'<br />';
						}
					}
				}
				*/

				/*
				if ($uploads) {
					$topic=paper_model::get_topic_by_id($topic->tpid);
					$ret.=notify('Upload photo file complete :<ul><li>'.implode('</li><li>',$uploads).'</li></ul>');
				}
				*/
			}
			//$ret .= print_o($_FILES, '$_FILES');
			//$ret .= print_o(post(), 'post()');

			location('paper/'.$tpid.'/edit.photo');

			break;

		case 'photo.delete':
			if (SG\confirm()) {
				$result = R::Model('photo.delete', $tranId);
				$ret .= 'ลบภาพเรียบร้อย';
				// $ret .= print_o($result,'$result');
			}
			break;

		case 'photo.change':
			if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
				$result = R::Model('paper.photo.change', $tranId, $_FILES['photo']);
				if ($result->complete) {
					$ret .= R::Page('paper.edit.photo',NULL, $topicInfo, $tranId);
					// '<img src="'.$result->file->_url.'"  height="100" />';
				} else {
					$ret .= '<ul><li>'.implode('</li><li>', $result->error).'</li></ul>';
				}
			}
			break;

		case 'doc.upload':
			if (post('upload')) {
				$is_simulate = debug('simulate');
				$desc = (Object) post('info',_TRIM+_STRIPTAG);
				$desc->tpid = $topicInfo->tpid;
				$desc->type = 'doc';

				$options = new stdClass;
				$options->debug = false;
				if ($desc->norename) $options->useSourceFilename = true;

				//$ret .= print_o($desc,'$desc');

				$result = R::Model('doc.upload', $_FILES['doc'], $desc, $options);

				//$topicInfo = R::Model('paper.get', $topicInfo->tpid);
				location('paper/'.$tpid.'/edit.docs');

				//$ret .= print_o($result,'$result');
			}
			//$ret .= print_o(post(),'post()');
			//$ret .= print_o($_FILES,'$_FILES');
			//location('paper/'.$tpid.'/edit.photo');
			break;

		case 'doc.delete':
			if ($tranId) {
				$doc = mydb::select('SELECT * FROM %topic_files% WHERE fid = :fid LIMIT 1', ':fid',$tranId);
				if ($doc->_num_rows) {
					$doc_file = cfg('paper.upload.document.folder').$doc->file;
					if ($doc->file && file_exists($doc_file) && is_file($doc_file)) unlink($doc_file);
					mydb::query('DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1', ':fid', $tranId);
					$ret.=message('status','Delete document complete : Document file <em>'.$doc->file.'</em> was deleted');
				} else $ret.=message('error','Document not found');
			}
			break;

		case 'prop.save':
			$post = (Object) post('prop',_TRIM+_STRIPTAG);
			$post->option = (Object) post('option');

			if ($post->show_photo == 'some' && post('show_photo_num')) {
				$post->show_photo = round(post('show_photo_num'));
			}

			$post->slide_width = $post->slide_width ? round($post->slide_width) : NULL;
			$post->slide_height = $post->slide_height ? round($post->slide_height) : NULL;

			$post->option->fullpage = $post->option->fullpage ? true : false;
			$post->option->secondary = $post->option->secondary ? true : false;
			$post->option->header = $post->option->header ? true : false;
			$post->option->ribbon = $post->option->ribbon ? true : false;
			$post->option->toolbar = $post->option->toolbar ? true : false;
			$post->option->container = $post->option->container ? true : false;
			$post->option->timestamp = $post->option->timestamp ? true : false;
			$post->option->related = $post->option->related ? true : false;
			$post->option->docs = $post->option->docs ? true : false;
			$post->option->footer = $post->option->footer ? true : false;
			$post->option->package = $post->option->package ? true : false;
			$post->option->commentwithphoto = $post->option->commentwithphoto ? true : false;
			$post->option->social = $post->option->social ? true : false;
			$post->option->ads = $post->option->ads ? true : false;
			$post->option->show_video = $post->option->show_video ? true : false;

			$newProperty = SG\json_decode($post, $topicInfo->property);
			$data->detail->property = SG\json_encode($newProperty);
			$result = R::Model('paper.info.update', $topicInfo, $data);

			break;

		case 'tag.add':
			$getTag = post('tag');
			$getVocab = post('vocab');
			if ($getTag && $getVocab) {
				$stmt = 'INSERT INTO %tag_topic% (`tpid`, `vid`, `tid`) VALUES (:tpid, :vid, :tid) ON DUPLICATE KEY UPDATE `tid` = :tid';
				mydb::query($stmt, ':tpid', $tpid, ':vid', $getVocab,':tid', $getTag);
			}
			break;

		case 'tag.remove':
			if ($tranId) {
				$stmt = 'DELETE FROM %tag_topic% WHERE `tpid` = :tpid AND `tid` = :tid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':tid', $tranId);
				debugMsg(mydb()->_query);
			}
			break;

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

			model::watch_log('paper','Paper comment edit','Edit comment id '.$tranId.' of <a href="'.url('paper/'.$tpid.'#comment-'.$tranId).'">paper/'.$tpid.'</a>');
			// if ($_SERVER['HTTP_REFERER']) location($_SERVER['HTTP_REFERER']); else location('paper/'.$tpid);

			break;

		case 'comment.delete':
			if ($tranId && SG\confirm()) {
				$result = R::Model('paper.comment.delete',$tranId);
				$ret = $result->complete ? new Message(['responseCode' => _HTTP_OK, 'text' => 'Comment deleted.']) : new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'มีข้อผิดพลาดในการลบความเห็น']);
			}
			break;

		case 'comment.hide':
			$ret .= 'Hide comment';
			if ($tranId) {
				$stmt = 'UPDATE %topic_comments% SET `status` = IF(`status` = '._BLOCK.','._PUBLISH.','._BLOCK.') WHERE `tpid` = :tpid AND `cid` = :cid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':cid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'update':
			// Update paper information
			if ($_POST) {
				$post = (Object) post();
				$debug = false;

				$result = R::Model('paper.info.update', $topicInfo, $post);

				$ret .= 'Update Completed';

				if ($simulate) {
					$ret .= print_o($result, '$result');
				} else if ($topicInfo->info->module != 'paper') {
					$onViewResult = R::On($topicInfo->info->module.'.paper.edit.complete', $self, $topicInfo, $data);
				}

				if ($debug) {
					$ret .= '<p>UPDATE INFORMATION</p>';
					$ret .= print_o($result, '$result');
					$ret .= print_o(post(),'post()');
					return $ret;
				}
				//location('paper/'.$tpid.'/edit');
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