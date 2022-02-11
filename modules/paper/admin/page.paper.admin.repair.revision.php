<?php
/**
* Module :: Description
* Created 2021-09-15
* Modify  2021-09-15
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class PaperAdminRepairRevision extends Page {
	function build() {
		$isDupRevision = mydb::select('SELECT COUNT(*) `dupCount` FROM %topic_revisions% GROUP BY `tpid` HAVING `dupCount` > 1');
		if ($isDupRevision->count() > 0) return message('notify', 'มีการใช้งาน Revision ในระบบ ไม่สามารถเรียบลำดับใหม่ได้');

		if (SG\confirm()) return $this->_startRepair();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Topic and Revision Id',
			]),
			'body' => new Widget([
				'children' => [
					'<p class="notify">กรุณาสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการซ่อมแซม</p>',
					'<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('paper/admin/repair/revision').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR TOPIC REVISION ID</a></nav>',
				],
			]),
		]);
	}

	function _startRepair() {
		mydb::query('UPDATE %topic% t SET t.`revid` = t.`tpid` ORDER BY t.`tpid` ASC');

		debugMsg(mydb()->_query);

		mydb::query('UPDATE %topic_revisions% SET `revid` = `tpid` ORDER BY `tpid` ASC');

		debugMsg(mydb()->_query);

		// mydb::query('UPDATE %topic_revisions%')
	}
}
?>