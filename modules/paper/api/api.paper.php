<?php
/**
* Paper   :: Info API
* Created :: 2023-07-23
* Modify  :: 2024-08-15
* Version :: 13
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
			'right' => (Object) array_merge(
				(Array) $nodeInfo->right,
				[
					'editBackend' => is_admin(),
					'editCss' => is_admin(),
					'editScript' => is_admin(),
					'editData' => is_admin()
				]
			)
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
			// 'modelClassName' => 'Paper'.$nodeInfo->nodeId.'Model',

		$apiCode = DB::select([
			'SELECT `phpBackend` FROM %topic_revisions% WHERE `tpid` = :nodeId LIMIT 1',
			'var' => [':nodeId' => $this->nodeId]
		])->phpBackend;

		if (!preg_match('/^\<\?php/', $apiCode)) $apiCode = '<?php'._NL.$apiCode._NL.'?>';

		eval('?>'.$apiCode.'<?php'._NL);

		if (!class_exists($apiClassName)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API not found');

		$api = new $apiClassName($this->nodeInfo);

		if (!method_exists($api, $apiMethod)) return apiError(_HTTP_ERROR_NOT_FOUND, 'API not found');

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
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!file_exists($_FILES['doc']['tmp_name'])) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'No upload file');

		// $desc = (Object) post('info',_TRIM+_STRIPTAG);
		// $desc->tpid = $this->nodeId;
		// $desc->type = 'doc';

		// $desc = (Object) [
		// 	...post('info',_TRIM+_STRIPTAG),
		// 	'tpid' => $this->nodeId,
		// 	'type' => 'doc',
		// ];

		$desc = (Object) array_merge(
			(Array) post('info',_TRIM+_STRIPTAG),
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

		// $result = R::Model('doc.upload', $_FILES['doc'], $desc, $options);

		// debugMsg($desc,'$desc');
		// debugMsg($result,'$result');
		// debugMsg(post(),'post()');
		// debugMsg($_FILES,'$_FILES');
		// debugMsg($_POST, '$_POST');

		if ($result->error) return error(_HTTP_ERROR_NOT_ACCEPTABLE, implode(',', $result->error));
		// debugMsg([
		// 	'items' => array_map(
		// 		function($doc) {
		// 			$docProperty = FileModel::docProperty($doc->file, $doc->folder);
		// 			return (Object) [
		// 				'fileId' => $doc->fileId,
		// 				'url' => _DOMAIN.$docProperty->src,
		// 				'exists' => $docProperty->exists,
		// 				'size' => $docProperty->size,
		// 				// 'link' => $doc->link,
		// 				// 'property' => $docProperty,
		// 			];
		// 		},
		// 		$result->items
		// 	),
		// ],'aaa');

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
}
?>