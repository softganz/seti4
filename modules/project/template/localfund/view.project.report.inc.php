<?php

/**
 * Send Document Report
 *
 */
function view_project_report_inc() {
	$menu='<header class="header"><h3>รายงานสำหรับผู้บริหาร</h3></header>';

	$ui = new Ui(NULL, 'ui-menu project-report-menu');

	$ui->add('<a href="'.url('project/fund/report/join').'">1. รายงานสรุปจำนวนองค์กรปกครองส่วนท้องถิ่นที่เข้าใช้งานโปรแกรมฯ</a>');
	$ui->add('<a href="'.url('project/fund/report/budget').'">2. รายงานสรุปจำนวนเงินงบประมาณของกองทุนฯ</a>');
	$ui->add('<a href="'.url('project/fund/report/balance').'">3. รายงานสรุปจำนวนเงินคงเหลือของกองทุนฯ</a>');
	$ui->add('<a href="'.url('project/fund/report/expensebymonth').'">4. รายงานสรุปการใช้จ่ายเงินรายเดือน</a>');
	$ui->add('<a class="" href="'.url('project/fund/report/accomulation').'">5. รายงานสรุปการใช้จ่ายสะสมรายเดือน (Accomulation)</a>');
	$ui->add('<a class="-new" href="'.url('project/fund/report/balancerate').'">6. รายงานสรุปเงินคงเหลือ และสัดส่วนการใช้จ่าย ของกองทุนตำบล แยกรายจังหวัด</a>');
	$ui->add('<a href="'.url('project/fund/report/project/budget').'">7. รายงานสรุปจำนวนการสนับสนุนงบประมาณตามแผนงาน/โครงการ/กิจกรรมของกองทุนฯ</a>');
	$ui->add('<a href="'.url('project/fund/report/project').'">8. รายงานสรุปจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ</a>');
	$ui->add('<a href="'.url('project/fund/report/project/target').'">9. รายงานสรุปจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับกลุ่มเป้าหมาย</a>');
	$ui->add('<a href="'.url('project/fund/report/project/org').'">10. รายงานสรุปจำนวนแผนงาน/โครงการ/กิจกรรมของกองทุนฯ เปรียบเทียบกับองค์กรผู้รับทุน</a>');
	//$ui->add('<a href="'.url('project/fund/report/').'">รายงานสรุปผลการประเมินกองทุนฯ</a>');

	$ui->add('<a href="'.url('project/report/haveplan').'">11. รายงานการจัดทำแผนงานของกองทุน</a>');
	$ui->add('<a href="'.url('project/fund/report/recieve').'">12. รายงานการบันทึกการรับเงินเข้ากองทุน</a>');
	$ui->add('<a href="'.url('project/fund/report/movement').'">13. รายงานการความเคลื่อนไหวของกองทุน</a>');
	$ui->add('<a href="'.url('project/fund/report/maxbalance').'">15. กองทุนที่มีเงินสะสมเหลือมากที่สุด</a> (Waiting)');

	$ui->add('<sep>');

	$ui->add('<a class="-new" href="'.url('project/fund/report/board').'">15. รายงานรายชื่อกรรมการกองทุน</a>');
	$ui->add('<a class="-new" href="'.url('project/fund/report/boardletter').'">16. รายงานการแต่งตั้งกรรมการกองทุน</a>');
	$ui->add('<a class="-new" href="'.url('project/fund/report/population').'">17. รายงานจำนวนประชากร</a>');


	$ui->add('<sep>');

	$ui->add('<a class="-new" href="'.url('project/report/haveplan').'">18. รายงานการจัดทำแผนงานของกองทุน</a>');

	$menu .= $ui->build();

	return $menu;
}
?>