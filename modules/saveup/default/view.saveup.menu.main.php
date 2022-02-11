<?php
/**
 * Home page
 *
 * @return String
 */
function view_saveup_menu_main() {
	$ret.='<ul id="saveup-main-menu" class="row -sg-flex saveup-menu -main">
<li class=""><a href="'.url('saveup/member').'">ระบบสมาชิก</a>
	<ul>
	<li><a href="'.url('saveup/member/post').'"><i class="icon -person-add"></i><span>เพิ่มสมาชิกเข้าใหม่</span></a></li>
	<li><a href="'.url('saveup/member/list').'"><i class="icon -people"></i><span>รายชื่อสมาชิกปัจจุบัน</span></a>
	<li><a href="'.url('saveup/member',array('st'=>'all')).'"><i class="icon -people"></i><span>รายชื่อสมาชิกทั้งหมด</span></a>
	<li><a href="'.url('saveup/member',array('st'=>'inactive')).'"><i class="icon -people -gray"></i><span>รายชื่อสมาชิกพ้นสภาพ</span></a>
	<li><a href="'.url('saveup/member/line').'"><i class="icon -material">folder_shared</i><span>รายชื่อสายสัมพันธ์</span></a></li>
	</ul>
</li>
<li class=""><a href="'.url('saveup/gl').'">ระบบบัญชี</a>
	<ul>
	<li><h4>ใบรับเงิน</h4>
		<ul>
		<li><a href="'.url('saveup/rcv').'">ใบรับเงิน</a></li>
		<li><a href="'.url('saveup/rcv/money').'">บันทึกการรับเงิน</a></li>
		</ul></li>
	<li><h4>เงินกู้</h4>
		<ul>
		<li><a href="'.url('saveup/loan').'">ใบกู้เงิน</a></li>
		<li><a href="'.url('saveup/loan/new').'">บันทึกการกู้เงินรายใหม่</a></li>
		<li><a href="'.url('saveup/loan').'">บันทึกการรับชำระเงินกู้</a></li>
		</ul></li>
	<li><h4>ค่ารักษาพยาบาล</h4>
		<ul>
		<li><a href="'.url('saveup/treat/list').'">รายการเบิกค่ารักษาพยาบาล</a></li>
		<li><a href="'.url('saveup/treat/post').'">บันทึกรายการเบิกค่ารักษาพยาบาล</a></li>
		<li><a href="'.url('saveup/treat/summary').'">สรุปรายการเบิกค่ารักษาพยาบาล</a></li>
		</ul></li>
	<li><h4>ยอดยกมา</h4>
		<ul>
		<li><a href="'.url('saveup/gl/openbalance/saving').'">บันทึกยอดยกมาสมาชิก-เงินฝาก</a></li>
		</ul></li>
	</ul>
</li>
<li class=""><a href="'.url('saveup/report').'">รายงาน</a>
	<ul>
	<li><h4>โอนเงิน</h4>
		<ul>
		<li><a href="'.url('saveup/payment/list').'">บันทึกการแจ้งโอนเงิน</a></li>
		<li><a href="'.url('saveup/report/websend').'">รายงานจำนวนครั้งในการแจ้งโอน</a></li>
		</ul></li>
	<li><h4>สมาชิก</h4>
		<ul>
		<li><a href="'.url('saveup/report/member/province/count').'">จำนวนสมาชิกแต่ละจังหวัด</a></li>
		<li><a href="'.url('saveup/report/member/prename/count').'">จำนวนสมาชิกตามคำนำหน้าชื่อ</a></li>
		<li><a href="'.url('saveup/report/member/peryear').'">จำนวนสมาชิกเข้าใหม่แต่ละปี</a></li>
		<li><a href="'.url('saveup/report/havetreat').'">รายงานสมาชิกเบิกค่ารักษาพยาบาล</a></li>
		<li><a href="'.url('saveup/report/nocost').'">รายงานสมาชิกไม่เคยเบิกค่ารักษาพยาบาล</a></li>
		<li><a href="'.url('saveup/report/member/age').'">รายงานอายุของสมาชิก</a></li>
		<li><a href="'.url('saveup/report/member/memage').'">รายงานอายุการเป็นสมาชิก</a></li>
		<li><a href="'.url('saveup/report/member/memage/bygroup').'">รายงานอายุการเป็นสมาชิกแยกเป็นช่วง</a></li>
		<li><a href="'.url('saveup/report/map').'">แผนที่สมาชิก</a></li>
		<li><a href="'.url('saveup/report/mtype').'">รายงานกลุ่มสมาชิก</a></li>
		<li><a href="'.url('saveup/report/occupation').'">รายงานอาชีพสมาชิก</a></li>
		</ul></li>
	<li><h4>บัญชี</h4>
		<ul>
		<li><a href="'.url('saveup/treat/summary').'">รายงานสรุปการเบิกค่ารักษาพยาบาลประจำปี</a></li>
		<li><a href="'.url('saveup/report/treat/payfor').'">ค่ารักษาพยาบาลแยกตามประเภท</a></li>
		<li><a href="'.url('saveup/report/treat/payforlist').'">ค่ารักษาพยาบาลแต่ละตามประเภท</a></li>
		<li><a href="'.url('saveup/report/treat/disease').'">ค่ารักษาพยาบาลแยกตามโรค</a></li>
		</ul></li>
	</li>
	</ul>
</li>
</ul>'._NL;
	return $ret;
}
?>