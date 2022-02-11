<?php
/**
* Admin :: Clear Empty Session in Database
* Created 2021-10-10
* Modify  2021-10-10
*
* @param String $_GET['confirm']
* @return Widget
*
* @usage admin/config/session/clear
*/

$debug = true;

class AdminConfigSessionClear extends Page {
	function build() {
		if (SG\confirm()) return $this->clearEmptySession();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Clear Empty Session',
			]),
			'body' => new Widget([
				'children' => [
					'<div class="-sg-text-center -sg-paddingmore">'
					. 'กรุณายืนยันการล้างข้อมูล session?<br /><br />'
					. '<nav class="nav -page"><a class="btn -link -cancel" href="'.url('admin/config').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <a class="sg-action btn -danger" href="'.url('admin/config/session/clear', ['confirm' => 'Yes']).'" data-rel="#main"><i class="icon -material">done_all</i><span>ยืนยันการล้างข้อมูล session</span></a></nav>'
					. '</div>',
					new Table([
						'thead' => ['id', `user`, 'start', 'access', 'data'],
						'children' => (function() {
							foreach (mydb::select('SELECT * FROM %session% LIMIT 100')->items as $item) {
								$rows[] = [$item->sess_id, $item->user, $item->sess_start, $item->sess_last_acc, $sess_data];
							}
							return $rows;
						})(),
					]), // Table
				],
			]),
		]);
	}

	function clearEmptySession() {
		mydb::query('DELETE FROM %session% WHERE `user` IS NULL');
		return new Container([
			'child' => 'ดำเนินการเรียบร้อย<br />'.mydb()->_query,
		]);
	}
}
?>