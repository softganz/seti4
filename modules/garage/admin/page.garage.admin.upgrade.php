<?php
/**
 * Upgrade Garage Database Staucture
 *
 * @return String
 */
function garage_admin_upgrade($self) {
	new Toolbar($self,'Garage Database Upgrade');

	$self->theme->sidebar = R::View('garage.admin.menu');

	$ret .= '<header class="header"><h3>Garage Database Upgrade</h3></header>';

	if (!SG\confirm()) {
		return $ret
			. '<p>ต้องการอัพเกรดฐานข้อมูลของระบบบริหารโครงการจริงหรือไม่?<br />กรุณายืนยันการอัพเกรด!!!<br />'
			. '<nav class="nav -page -sg-text-right"><a class="btn -danger" href="'.url('garage/admin/upgrade',array('confirm'=>'Yes')).'"><i class="icon -material">done_all</i><span>ยืนยันการอัพเกรด</span></a></nav>'
			. '</p>'
			. '<p><b>คำเตือน</b> ควรสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการอัพเกรดข้อมูล</p>';
	}

	$result = array();

	if (!mydb::columns('%garage_shop%','package')) {
		$stmt = 'ALTER TABLE %garage_shop%
			ADD `package` VARCHAR(20) NULL DEFAULT NULL AFTER `vatrate`,
			ADD `registerdate` DATE NULL DEFAULT NULL AFTER `package`,
			ADD `expiredate` DATE NULL DEFAULT NULL AFTER `registerdate`,
			ADD `options` TEXT NULL DEFAULT NULL AFTER `expiredate`,
			ADD INDEX (`package`),
			ADD INDEX (`registerdate`),
			ADD INDEX (`expiredate`)
		';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}

	if ($result) {
		$ret .= '<ul><li>'.implode('</li><li>',$result).'</li></ul>';
	} else {
		$ret .= '<p>ระบบเป็นรุ่นล่าสุดแล้ว!!!</p>';
	}
	return $ret;
}
?>