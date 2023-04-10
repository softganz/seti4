<?php
/**
* Paper   :: Update Infomation
* Created :: 2023-04-07
* Modify  :: 2023-04-07
* Version :: 1
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return Array/Object
*
* @usage api/paper/update/{id}/{action}[/{tranId}]
*/

import('model:paper.php');

class PaperUpdateApi extends PageApi {
	var $nodeId;
	var $action;
	var $tranId;

	function __construct($nodeId = NULL, $action = NULL, $tranId = NULL) {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			'nodeInfo' => $nodeInfo = (is_numeric($nodeId) ? PaperModel::get($nodeId) : NULL),
			'nodeId' => $nodeInfo->tpid,
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลเอกสารที่ระบุ');
		else if (!user_access('administer contents,administer papers,administer '.$this->nodeInfo->info->module.' paper','edit own paper',$this->nodeInfo->uid)) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');

		return parent::build();
	}

	function delete() {
		if (!SG\confirm()) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');
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

		// if ($simulate) $ret .= print_o($deleteResult,'$deleteResult');
		// else if (function_exists('module_exists') && module_exists($classname,'__delete_complete')) call_user_func(array($classname,'__delete_complete'),$self,$this->nodeInfo,$para,$result);
		// else if ($para->deleteonly) {
		// 	// do nothing
		// } else location('tags/'.$this->nodeInfo->tags[0]->tid,$ret);
		// return success('ลบหัวข้อเรียบร้อย');
	}
}
?>