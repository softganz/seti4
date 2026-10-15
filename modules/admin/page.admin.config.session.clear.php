<?php
/**
 * Admin    :: Clear Empty Session in Database
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-10-10
 * Modified :: 2026-10-16
 * Version  :: 5
 *
 * @return Widget
 *
 * @uses admin/config/session/clear
 */

use Softganz\DB;

class AdminConfigSessionClear extends Page {
	function build() {
		if (\SG\confirm()) return self::clearEmptySession();

		// ini_set('session.gc_probability', '1');
		// ini_set('session.gc_divisor', '100'); // 1 หาร 1 = 100% โอกาสทำงานเต็มที่

		// // ลองตั้งค่าให้ session หมดอายุเร็วขึ้น เช่น 10 วินาที เพื่อทดสอบลบข้อมูลเก่า
		// ini_set('session.gc_maxlifetime', '10'); 

		$totals = self::getTotals();

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Clear Empty Session (' . $totals->users . '/' . $totals->totals . ')'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					"Probability: " . ini_get('session.gc_probability') . ' ',
					"Divisor: " . ini_get('session.gc_divisor') . ' ',
					"MaxlifeTime: " . ini_get('session.gc_maxlifetime') . ' ',
					"<br>",
					'<div class="-sg-text-center -sg-paddingmore">'
					. 'กรุณายืนยันการล้างข้อมูล session?<br /><br />'
					. '<nav class="nav -page"><a class="btn -link -cancel" href="' . Url::link('admin/config') . '"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <a class="sg-action btn -danger" href="' . Url::link('admin/config/session/clear') . '" data-rel="notify" data-done="load:#main" data-title="ลบข้อมูล session" data-confirm="ต้องการล้างข้อมูล session กรุณายืนยัน?"><i class="icon -material">done_all</i><span>ยืนยันการล้างข้อมูล session</span></a></nav>'
					. '</div>',
					new ScrollView([
						'child' => new Table([
							'thead' => ['no' => '', 'id', 'user -nowrap' => 'user', 'startdate -date' => 'start', 'accessdate -date' => 'access', 'data'],
							'children' => array_map(
								function($item) {
									static $no = 0;

									return [
										++$no,
										$item->sess_id,
										$item->user,
										$item->sess_start,
										$item->sess_last_acc,
										$item->sess_data
									];
								},
								DB::select([
									'SELECT `sess_id`, `user`, `sess_start`, `sess_last_acc`, `sess_data`
									FROM %session%
									WHERE `user` IS NOT NULL AND `user` NOT IN ("0", "")
									ORDER BY `sess_last_acc` DESC
									LIMIT 100'
								])->items
							),
						]), // Table
					]), // ScrollView
				],
			]),
		]);
	}

	private static function getTotals() {
		return DB::select([
			'SELECT COUNT(IF(`user` IS NOT NULL AND `user` != 0 AND `user` != "", 1, NULL)) AS `users`, COUNT(`sess_id`) AS `totals`
			FROM %session%
			LIMIT 1'
		]);
	}

	private function clearEmptySession() {
		DB::query([
			'DELETE FROM %session% WHERE `user` IS NULL OR `user` IN ("0", "")'
		]);

		return success('ดำเนินการเรียบร้อย');
	}
}
?>