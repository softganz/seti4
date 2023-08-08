<?php
/**
* Admin   :: Clear Empty Session in Database
* Created :: 2021-10-10
* Modify  :: 2023-08-05
* Version :: 3
*
* @return Widget
*
* @usage admin/config/session/clear
*/

use Softganz\DB;

class AdminConfigSessionClear extends Page {
	function build() {
		if (\SG\confirm()) return $this->clearEmptySession();

		$totals = DB::select([
			'SELECT COUNT(IF(`user` != "", 1, NULL)) `users`, COUNT(*) `totals`
			FROM %session%
			LIMIT 1'
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Clear Empty Session ('.$totals->users.'/'.$totals->totals.')',
				'navigator' => 	R::View('admin.default.nav'),
			]),
			'body' => new Widget([
				'children' => [
					'<div class="-sg-text-center -sg-paddingmore">'
					. 'กรุณายืนยันการล้างข้อมูล session?<br /><br />'
					. '<nav class="nav -page"><a class="btn -link -cancel" href="'.url('admin/config').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <a class="sg-action btn -danger" href="'.url('admin/config/session/clear').'" data-rel="notify" data-done="load:#main" data-title="ลบข้อมูล session" data-confirm="ต้องการล้างข้อมูล session กรุณายืนยัน?"><i class="icon -material">done_all</i><span>ยืนยันการล้างข้อมูล session</span></a></nav>'
					. '</div>',
					new ScrollView([
						'child' => new Table([
							'thead' => ['id', 'user', 'startdate -date' => 'start', 'accessdate -date' => 'access', 'data'],
							'children' => array_map(
								function($item) {
									return [$item->sess_id, $item->user, $item->sess_start, $item->sess_last_acc, $item->sess_data];
								},
								mydb::select('SELECT * FROM %session% LIMIT 100')->items
							),
						]), // Table
					]), // ScrollView
				],
			]),
		]);
	}

	function clearEmptySession() {
		mydb::query('DELETE FROM %session% WHERE `user` IS NULL OR `user` = ""');
		return success('ดำเนินการเรียบร้อย');
	}
}
?>