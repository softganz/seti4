<?php
/**
* Paper   :: Info API
* Created :: 2023-07-23
* Modify  :: 2025-06-25
* Version :: 20
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage api/paper/{nodeId}/{action}[/{tranId}]
*/

use Paper\Model\PaperModel;
use Softganz\DB;

class PaperApi extends PageApi {
	var $actionDefault = 'detail';
	var $nodeId;
	var $action;
	var $tranId;
	var $right;
	var $nodeInfo;

	function __construct($nodeId = NULL, $action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'nodeInfo' => $nodeInfo = (is_numeric($nodeId) ? PaperModel::get($nodeId) : NULL),
			'nodeId' => $nodeInfo->nodeId,
			'right' => $nodeInfo->right,
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PROCESS ERROR');

		if ($this->action === 'node') return $this->nodeAPI();

		return parent::build();
	}

	function nodeApi() {
		$apiMethod = preg_replace_callback('/\.(\w)/', function($matches) {return strtoupper($matches[1]);}, $this->tranId); // Change .\w to uppercase
		$apiClassName = 'Paper'.$this->nodeId.'Api';

		$apiCode = DB::select([
			'SELECT `rev`.`phpBackend`
			FROM %topic% `topic`
				LEFT JOIN %topic_revisions% `rev` ON `topic`.`tpid` = `rev`.`tpid` AND `topic`.`revId` = `rev`.`revId`
			WHERE `topic`.`tpid` = :nodeId
			LIMIT 1',
			'var' => [':nodeId' => $this->nodeId]
		])->phpBackend;

		if (!preg_match('/^\<\?php/', $apiCode)) $apiCode = '<?php'._NL.$apiCode._NL.'?>';

		eval('?>'.$apiCode.'<?php'._NL);

		if (!class_exists($apiClassName)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API class not found');

		$api = new $apiClassName($this->nodeInfo);

		if (!method_exists($api, $apiMethod)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API method not found');

		return $api->{$apiMethod}();
	}

	function detail() {
		$nodeInfo = PaperModel::get($this->nodeId);

		$photoList = [];
		$docList = [];

		foreach($nodeInfo->photos as $photo) {
			$photoList[] = (Object) [
				'photoId' => $photo->fid,
				'src' => _DOMAIN.$photo->src,
				'exits' => $photo->exists,
				'size' => $photo->size,
				'width' => $photo->width,
				'height' => $photo->height,
			];
		}

		foreach($nodeInfo->docs as $doc) {
			$docList[] = (Object) [
				'docId' => $doc->fid,
				'src' => _DOMAIN.$doc->src,
				'exits' => $doc->exists,
				'size' => $doc->size,
			];
		}

		return (Object) [
			'nodeId' => $nodeInfo->nodeId,
			'title' => $nodeInfo->title,
			'body' => $nodeInfo->info->body,
			'tags' => array_values($nodeInfo->tags),
			'photoList' => $photoList,
			'docList' => $docList,
			// 'info' => $nodeInfo,
		];
	}

	function delete() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!\SG\confirm()) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
		else if ($this->nodeInfo->info->status == _LOCK) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'This topic was lock:You cannot delete a lock topic. Please unlock topic and go to delete again.');

		$firstTag = is_array($this->nodeInfo->tags) ? reset($this->nodeInfo->tags) : NULL;

		$deleteResult = PaperModel::delete($this->nodeId);
		// debugMsg($deleteResult, '$deleteResult');

		// send email alert on delete
		if (cfg('alert.email') && in_array('paper',explode(',',cfg('alert.module')))) {
			$mail = (Object) [
				'to' => cfg('alert.email'),
				'title' => '-- topic : '.strip_tags($this->nodeInfo->title).' : '.$firstTag->name,
				'name' => i()->name,
				'from' => 'alert@'.cfg('domain.short'),
				'cc' => cfg('alert.cc') ? cfg('alert.cc') : NULL,
				'bcc' => cfg('alert.bcc') ? cfg('alert.bcc') : NULL,
				'body' => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
					<html>
					<head>
					<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
					<title>'.$this->nodeInfo->title.'</title>
					</head>
					<body>
					<strong>topic was delete by '.i()->name.' ('.i()->uid.') on '.date('Y-m-d H:i:s').'</strong>
					<hr size=1>
					Submit by <b>'.$this->nodeInfo->info->poster.'</b> on <b>'.$this->nodeInfo->info->created.'</b> | paper id : <b>'.$this->nodeId.'</b><br />
					<hr size=1>'.
					sg_text2html($this->nodeInfo->info->body).'
					</body>
					</html>',
			];
			BasicModel::sendmail($mail);
		}
	}

	function detailUpdate() {
		$simulate = true;
		$post = (Object) post();
		$debug = false;

		if (!$this->right->edit) {
			return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		} else if (!$_POST) {
			return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
		} else if (isset($post->detail['phpBackend']) && !$this->right->editBackend) {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		} else if (isset($post->detail['css']) && !$this->right->editCss) {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		} else if (isset($post->detail['script']) && !$this->right->editScript) {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		} else if (isset($post->detail['data']) && !$this->right->editData) {
			return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		}

		// debugMsg($post, '$post');

		$result = PaperModel::updateInfo($this->nodeInfo, $post);

		// $ret .= 'Update Completed';

		if ($simulate) {
			$ret .= print_o($result, '$result');
		} else if ($this->nodeInfo->info->module != 'paper') {
			$onViewResult = R::On($this->nodeInfo->info->module.'.paper.edit.complete', $self, $this->nodeInfo, $data);
		}

		if ($debug) {
			$ret .= '<p>UPDATE INFORMATION</p>';
			$ret .= print_o($result, '$result');
			$ret .= print_o(post(),'post()');
			return $ret;
		}

		return apiSuccess('บันทึกเรียบร้อย');
	}

	function photoAdd() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		// if (!post('upload')) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลไฟล์แนบ');

		$is_simulate = debug('simulate');
		$data = (Object) [
			'nodeId' => $this->nodeId,
			'type' => 'photo',
			'title' => post('title'),
			'description' => post('description'),
			'folder' => cfg('paper')->photoUploadFolder,
		];

		$options = (Object) [
			'debug' => false,
			'useSourceFilename' => post('noRename') ? true : false,
		];

		$result = FileModel::upload($_FILES['image'], $data, $options);

		if ($result->error) return error(_HTTP_ERROR_NOT_ACCEPTABLE, implode(',', $result->error));

		return [
			'items' => array_map(
				function($photo) {
					return [
						'fileId' => $photo->fid,
						'url' => $photo->photo->url,
						'link' => $photo->link,
					];
				},
				$result->items
			),
		];
	}

	function photoChange() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!is_uploaded_file($_FILES['photo']['tmp_name'])) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีไฟล์แนบ');

		$fileId = $this->tranId;
		$photoInfo = FileModel::get($fileId);

		if (empty($photoInfo->fileId)) return error(_HTTP_ERROR_NOT_FOUND, 'File not found');

		$data = (Object) [
			'nodeId' => $this->nodeId,
			'fileId' => $fileId,
			'type' => 'photo',
			'folder' => $photoInfo->folder,
		];

		$options = (Object) [
			'debug' => false,
			'useSourceFilename' => post('noRename') ? true : false,
		];

		// Delete old file if not inused by other
		if (!FileModel::getFileInUse($photoInfo->fileId, $photoInfo->fileName, $photoInfo->folder)) {
			FileModel::delete($data->fileId, ['deleteRecord' => false]);
		}

		$result = FileModel::upload($_FILES['photo'], $data, $options);

		if ($result->error) return error(_HTTP_ERROR_NOT_ACCEPTABLE, '<ul><li>'.implode('</li><li>', $result->error).'</li></ul>');

		return success('บันทึกเรียบร้อย');
	}

	function photoDelete() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!\SG\confirm()) return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณายืนยัน');

		$result = FileModel::delete($this->tranId);
		return $result->code ? error($result->code, $result->msg) : success('ลบภาพเรียบร้อย');
	}

	function docAdd() {
		if (!$this->right->edit) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!file_exists($_FILES['doc']['tmp_name'])) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'No upload file');

		$desc = (Object) array_merge(
			(Array) post('info', _TRIM+_STRIPTAG),
			[
				'nodeId' => $this->nodeId,
				'type' => 'doc',
			]
		);

		$options = (Object) [
			'debug' => false,
			'useSourceFilename' => $desc->noRename ? true : false,
		];

		$result = FileModel::upload($_FILES['doc'], $desc, $options);

		if ($result->error) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, implode(',', $result->error));

		return [
			'items' => array_map(
				function($doc) {
					$docProperty = FileModel::docProperty($doc->file, $doc->folder);
					return (Object) [
						'fileId' => $doc->fileId,
						'url' => _DOMAIN.$docProperty->src,
						'exists' => $docProperty->exists,
						'size' => $docProperty->size,
						// 'link' => $doc->link,
						// 'property' => $docProperty,
					];
				},
				$result->items
			),
		];
	}

	function docDelete() {
		$fileId = post('fileId');

		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($fileId)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลตามที่ระบุ');
		if (!SG\confirm()) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีการยืนยัน');

		$fileInfo = FileModel::get(['fileId' => $fileId, 'nodeId' => $this->nodeId]);

		if (empty($fileInfo)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลตามที่ระบุ');
		$result = FileModel::delete($fileId);
	}

	function propSave() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

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
		$post->option->title = $post->option->title ? true : false;
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

		$newProperty = \SG\json_decode($post, $this->nodeInfo->property);
		$data = (Object) [
			'detail' => (Object) [
				'property' => SG\json_encode($newProperty),
			],
		];
		$result = PaperModel::updateInfo($this->nodeInfo, $data);
		// debugMsg($result, '$result');
	}

	function tagAdd() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		$getTag = post('tag');
		$getVocab = post('vocab');
		if (empty($getTag) || empty($getVocab)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');

		DB::query([
			'INSERT INTO %tag_topic% (`tpid`, `vid`, `tid`) VALUES (:tpid, :vid, :tid) ON DUPLICATE KEY UPDATE `tid` = :tid',
			'var' => [
				':tpid' => $this->nodeId,
				':vid' => $getVocab,
				':tid' => $getTag
			]
		]);
	}

	function tagRemove() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($this->tranId)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');

		DB::query([
			'DELETE FROM %tag_topic% WHERE `tpid` = :tpid AND `tid` = :tid LIMIT 1',
			'var' => [
				':tpid' => $this->nodeId,
				':tid' => $this->tranId,
			]
		]);
	}

	function nodeDuplicate() {
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		// Get old record
		$oldNode = DB::select([
			'SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1',
			'var' => [':tpid' => $this->nodeId]
		]);

		$oldRev = DB::select([
			'SELECT * FROM %topic_revisions% WHERE `revid` = :revid LIMIT 1',
			'var' => [':revid' => $oldNode->revid]
		]);

		// Create new topic record
		$oldNode->tpid = NULL;
		$oldNode->revid = NULL;

		$result = DB::query([
			'INSERT INTO %topic%
				(`'.implode('`,`', array_keys((Array) get_object_vars($oldNode))).'`)
				VALUES
				(:'.implode(', :', array_keys((Array) get_object_vars($oldNode))).')',
			'var' => $oldNode
		]);
		$newNodeId = $result->insertId();

		// debugMsg('$newNodeId = '.$newNodeId);
		// debugMsg(mydb()->_query);

		// Create new revision record
		$oldRev->tpid = $newNodeId;
		$oldRev->revid = NULL;

		$result = DB::query([
			'INSERT INTO %topic_revisions%
				(`'.implode('`,`', array_keys((Array) get_object_vars($oldRev))).'`)
				VALUES
				(:'.implode(', :', array_keys((Array) get_object_vars($oldRev))).')',
			'var' => $oldRev
		]);
		$newRevId = $result->insertId();

		// debugMsg('$newRevId = '.$newRevId);
		// debugMsg(mydb()->_query);

		// Update topic revid
		DB::query([
			'UPDATE %topic% SET `revid` = :newRevId WHERE `tpid` = :newNodeId LIMIT 1',
			'var' => [
				':newNodeId' => $newNodeId,
				':newRevId' => $newRevId,
			]
		]);
		// debugMsg(mydb()->_query);

		// Create topic user
		$nodeUser = DB::select([
			'SELECT * FROM %topic_user% WHERE `tpid` = :oldNodeId',
			'var' => [':oldNodeId' => $this->nodeId]
		]);
		// debugMsg($nodeUser, '$nodeUser');
		foreach ($nodeUser->items as $user) {
			$user->tpid = $newNodeId;
			DB::query([
				'INSERT INTO %topic_user%
				(`'.implode('`,`', array_keys((Array) $user)).'`)
				VALUES
				(:'.implode(', :', array_keys((Array) $user)).')',
				'var' => $user
			]);
			// debugMsg(mydb()->_query);
		}
		return ['code' => 200, 'text' => 'ดำเนินการเสร็จสิ้น', 'nodeId' => $newNodeId];
	}

	function backend() {
		if (!$this->right->admin) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		$backend = NodeModel::getBackend($this->nodeId);
		return $backend;
	}

	function commentUpdate() {
		$nodeId = $this->nodeId;
		$commentId = $this->tranId;

		$comment = NodeModel::getCommentById($commentId);

		if (empty($commentId)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลความเห็นที่ต้องการลบ');
		if (empty($comment->cid)) return apiError(_HTTP_ERROR_NOT_FOUND, 'ไม่พบความเห็นที่ต้องการลบ');
		if (!($this->right->edit || (i()->ok && $comment->uid === i()->uid))) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		$post = (Object) post('comment');
		$stmt = mydb::create_update_cmd('%topic_comments%',$post,'cid='.$commentId);
		mydb::query($stmt,$post);

		if (post('delete_photo')) {
			$deletePhoto = DB::select([
				'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :commentId AND `type`="photo" LIMIT 1',
				'var' => [
					':tpid' => $nodeId,
					':commentId' => $commentId
				]
			]);
			if ($deletePhoto->fid) FileModel::delete($deletePhoto->fid, ['deleteRecord' => true]);
		}

		// save upload photo
		if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
			$photo = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :commentId AND `type`="photo" LIMIT 1', ':tpid', $nodeId, ':commentId', $commentId);
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
					$photo->tpid = $nodeId;
					$photo->cid = $commentId;
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
			'message' => 'Edit comment id '.$commentId.' of <a href="'.url('paper/'.$nodeId.'#comment-'.$commentId).'">paper/'.$nodeId.'</a>'
		]);
		// if ($_SERVER['HTTP_REFERER']) location($_SERVER['HTTP_REFERER']); else location('paper/'.$nodeId);
		return apiSuccess('บันทึกความเห็นเรียบร้อย');
	}

	function commentDelete() {
		$commentId = SG\getFirstInt(post('commentId'));

		$comment = NodeModel::getCommentById($commentId);

		if (empty($commentId)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลความเห็นที่ต้องการลบ');
		if (!SG\confirm()) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'กรุณายืนยันการลบความเห็น');
		if (empty($comment->cid)) return apiError(_HTTP_ERROR_NOT_FOUND, 'ไม่พบความเห็นที่ต้องการลบ');
		if (!($this->right->edit || (i()->ok && $comment->uid === i()->uid))) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		$result = NodeModel::deleteCommentById($commentId);

		if ($result->error) return apiError(_HTTP_ERROR_BAD_REQUEST, $result->message);

		return apiSuccess('ลบความเห็นเรียบร้อย');
	}

	function commentHide() {
		$commentId = SG\getFirstInt(post('commentId'));

		$comment = NodeModel::getCommentById($commentId);

		if (empty($commentId)) return apiError(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลความเห็นที่ต้องการซ่อน');
		if (empty($comment->cid)) return apiError(_HTTP_ERROR_NOT_FOUND, 'ไม่พบความเห็นที่ต้องการซ่อน');
		if (!(is_admin())) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		NodeModel::hideCommentById($commentId);

		return apiSuccess('ซ่อนความเห็นเรียบร้อย');
	}

	function sendDelete() {
		$post = (Object) post('contact');

		if (empty($post->detail)) return apiError(_HTTP_ERROR_NOT_FOUND, 'กรุณาระบุความไม่เหมาะสมของเนื้อหา');
		if (empty($post->sender)) return apiError(_HTTP_ERROR_NOT_FOUND, 'กรุณาป้อนชื่อผู้ส่ง');
		if (!i()->ok && !sg_valid_daykey(5,post('daykey'))) apiError(_HTTP_ERROR_NOT_FOUND, 'Invalid Anti-spam word');

		// if (load_lib('class.mail.php', 'lib')) {
		// 	$mail = new Mail();

		// 	$mail->FromName('noreply');
		// 	$mail->FromEmail('noreply@'.cfg('domain.short'));

		// 	$mailTo = cfg('email.delete_message');
		// 	$mailTitle = 'แจ้งลบหัวข้อ : '.strip_tags($this->nodeInfo->title);
		// 	$mailMessage = '
		// 	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
		// 	<html>
		// 	<head>
		// 	<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
		// 	<title>'.$this->nodeInfo->title.'</title>
		// 	</head>
		// 	<body>
		// 	<h2><a href="'.cfg('domain').url('paper/'.$this->nodeInfo->tpid).'" target=_blank><strong>'.$this->nodeInfo->title.'</strong></a></h2>'
		// 	. '<p>Submit by <strong>'.\SG\getFirst($this->nodeInfo->info->owner, $this->nodeInfo->info->poster)
		// 	. ($this->nodeInfo->uid ? '('.$this->nodeInfo->uid.')' : '')
		// 	. '</strong> '
		// 	. ' on <strong>'.$this->nodeInfo->info->created.'</strong> | ip : '.GetEnv('REMOTE_ADDR')
		// 	. ' | paper id : <strong><a href="'.cfg('domain').url('paper/'.$this->nodeInfo->tpid).'" target=_blank>'.$this->nodeInfo->tpid.'</a></strong><p>
		// 	<hr size=1>
		// 	<h3>แจ้งโดย : '.$post->sender.' &lt;'.$post->email.'&gt;</h3>
		// 	<h3>ความไม่เหมาะสมของเนื้อหา</h3><p>'.$post->detail.'<p>
		// 	<h3>ข้อความ</h3>'
		// 	. $this->nodeInfo->info->body
		// 	.'
		// 	</body>
		// 	</html>';


		// 	if ( $mailTo ) {
		// 		$mail_result = $mail->Send($mailTo, $mailTitle, $mailMessage, false, 'https://service.softganz.com');
		// 		if ($mail_result) {
		// 			//$ret .= 'Send mail complete';
		// 		}
		// 	}
		// }

		LogModel::save([
			'module' => 'paper',
			'keyword' => 'Send delete paper',
			'message' => 'Paper : '.$this->nodeId.' : '.$this->nodeInfo->title.'<br />'.$post->detail
		]);

		sgSendLog([
			'file' => __FILE__,
			'line' => __LINE__,
			'url' => _DOMAIN.url('paper/'.$this->nodeId),
			'type' => 'Report deletion',
			'user' => i()->uid,
			'name' => SG\getFirst(i()->name, $post->sender),
			'description' => $post->detail,
		]);
		//BasicModel::sendmail($mail);
		//$ret .= print_o($mail,'$mail');

		$ret .= message('success','ส่งข้อความแจ้งลบหัวข้อที่ไม่เหมาะสมเรียบร้อย');
		return $ret;
	}

	// TODO: This method is work in progress
	function pollUpdate() {
		$nodeId = $this->nodeId;
		if (!$this->right->edit) return apiError(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		$data = (Object) post('poll',_TRIM+_STRIPTAG);
		if (!mydb::select('SELECT `tpid` FROM %poll% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $nodeId)->tpid) {
			$stmt = 'INSERT INTO %poll% (`tpid`, `start_date`, `end_date`, `created`) VALUES (:tpid, :start_date, :end_date, :created)';

			mydb::query($stmt,':tpid',$nodeId, ':start_date',date('Y-m-d H:i:s'), ':end_date', 'func.NULL', ':created',date('Y-m-d H:i:s'));
			//$ret .= mydb()->_query.'<br />';
		}

		foreach ($data as $k=>$v) {
			if (mydb::select('SELECT `choice` FROM %poll_choice% WHERE `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$nodeId, ':choice',$k)->choice) {
				if ($v=='') { // Delete on empty
					mydb::query('DELETE FROM %poll_choice% WHERE  `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$nodeId, ':choice',$k);
				} else { // Update
					mydb::query('UPDATE %poll_choice% SET `detail`=:detail WHERE  `tpid`=:tpid AND `choice`=:choice LIMIT 1', ':tpid',$nodeId, ':choice',$k,':detail',$v);
				}
				//$ret.='Update '.mydb()->_query.'<br />';
			} else {
				if ($v=='') {
				} else {
					mydb::query('INSERT INTO %poll_choice% (`tpid`, `choice`, `detail`) VALUES (:tpid, :choice, :detail)',':tpid',$nodeId, ':choice',$k, ':detail',$v);
					//$ret.='Insert '.mydb()->_query.'<br />';
				}
			}
		}
	}
}
?>