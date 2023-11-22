<?php
/**
* Paper   :: Info API
* Created :: 2023-07-23
* Modify  :: 2023-11-21
* Version :: 3
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage paper/info/api/{nodeId}/{action}[/{tranId}]
*/

use Paper\Model\PaperModel;

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
			'right' => (Object) [
				'admin' => user_access('administer contents,administer papers'),
				'edit' => $nodeInfo->RIGHT & _IS_EDITABLE,
			]
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PROCESS ERROR');
		else if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		return parent::build();
	}

	function detail() {
		$nodeInfo = PaperModel::get($this->nodeId);

		$photoList = [];
		$docList = [];

		foreach($nodeInfo->photos as $photo) {
			$photoList[] = (Object) [
				'src' => _DOMAIN.$photo->src,
				'exits' => $photo->exists,
				'size' => $photo->size,
				'width' => $photo->width,
				'height' => $photo->height,
			];
		}

		foreach($nodeInfo->docs as $doc) {
			$docList[] = (Object) [
				'src' => _DOMAIN.$doc->src,
				'exits' => $doc->exists,
				'size' => $doc->size,
			];
		}

		return (Object) [
			'nodeId' => $nodeInfo->nodeId,
			'title' => $nodeInfo->title,
			'body' => $nodeInfo->info->body,
			'photoList' => $photoList,
			'docList' => $docList,
			// 'info' => $nodeInfo,
		];
	}

	function delete() {
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

	function photoAdd() {
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

		$result = FileModel::upload($_FILES['photo'], $data, $options);
		// return new debugMsg($result, '$result');
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
		if (!\SG\confirm()) return error(_HTTP_ERROR_BAD_REQUEST, 'กรุณายืนยัน');

		$result = FileModel::delete($this->tranId);
		// debugMsg($result, '$result');
		return success('ลบภาพเรียบร้อย');
	}
}
?>