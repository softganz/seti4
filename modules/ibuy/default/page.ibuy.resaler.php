<?php
/**
 * Listing Resaler
 *
 * @param Argument list in parameter format
 * @return String
 */
function ibuy_resaler($self) {
	$self->theme->title='Resaler Shop';
	//$ret.=$this->__menu('resaler');
	$isAdmin=user_access('administer ibuys');

	$ret='<ul class="tabs tabs-primary">'._NL;
	$ret.='<li class="tabs-franchise"><a href="'.url('ibuy/franchise').'">เฟรนไชส์</a></li>'._NL;
	$ret.='<li class="tabs-resaler active"><a href="'.url('ibuy/resaler').'">ตัวแทนจำหน่าย</a></li>'._NL;
	if (user_access('administer ibuys')) $ret.='<li class="tabs-customer'.($active=='customer'?' active':'').'"><a href="'.url('ibuy/customer').'">ลูกค้าทั่วไป</a></li>'._NL;
	$ret.='</ul>'._NL;

	$todays=mydb::select('SELECT f.`custname` , f.`custtype` , u.`username` , p.name province FROM %ibuy_customer% f LEFT JOIN %users% u ON u.uid=f.uid LEFT JOIN %province% p ON p.pid=f.pid WHERE `datein` BETWEEN "'.date('Y-m-d').' 00:00:00" AND "'.date('Y-m-d').' 23:59:59"');
	if ($todays->_num_rows) {
		$ret.='<p>สมาชิกสมัครใหม่ของวันนี้ : ';
		foreach ($todays->items as $rs) {
			$ret.='<a href="'.url('ibuy/franchise/'.$rs->username).'" title="'.ibuy_define::custtype($rs->custtype).'">'.$rs->custname.' ('.strtoupper(substr($rs->custtype,0,1)).') - '.$rs->province.'</a> , ';
		}
		$ret=trim($ret,' , ').'</p>';
	}
	$stmt='SELECT
					f.`custname` , f.`custtype` , f.`latlng` , f.`uid` ,  f.`discount`
					, u.`uid`, u.`username`,p.`name` province_name
					, f.`custaddress`,f.`custphone`
					, t.`tpid`
				FROM %ibuy_customer% f
					LEFT JOIN %province% p ON p.`pid`=f.`pid`
					LEFT JOIN %users% u ON u.`uid`=f.`uid`
					LEFT JOIN %topic% t ON t.`type`="franchise" AND t.`uid`=f.`uid`
				WHERE `custtype`="resaler"
				ORDER BY f.`custname` ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead['no']='no';
	$tables->thead[]='ชื่อร้าน';
	$tables->thead[]='T';
	$tables->thead['province']='จังหวัด';
	$tables->thead[]='';
	$tables->thead[]='ที่อยู่';
	$tables->thead[]='โทรศัพท์';
	if (user_access('administer ibuys') && cfg('ibuy.resaler.discount')>0) {
		$tables->thead['money discount']='ส่วนลดสะสม';
	}
	foreach ($dbs->items as $rs) {
		if (in_array($rs->uid,array(1))) continue;
		if (!$isAdmin && !in_array($rs->custtype, array('resaler','franchise'))) continue;
		unset($rows);
		$rows[]=++$no;
		$rows[]='<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="'.ibuy_define::custtype($rs->custtype).'">'.SG\getFirst($rs->custname,'ไม่ระบุ').'</a>';
		$rows[]=strtoupper(substr($rs->custtype,0,1));
		$rows[]=$rs->province_name;
		$rows[]=($rs->latlng?'<img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|EEEEEE|FFFFFF" height="24" alt="Map" title="บันทึกแผนที่แล้ว"/>':'');
		$rows[]=$rs->custaddress;
		$rows[]=user_access('access user profiles') ? $rs->custphone : '**';
		if (user_access('administer ibuys') && cfg('ibuy.resaler.discount')>0) {
			$rows[]=$rs->discount>0?number_format($rs->discount,2):'-';
		}
		$rows['config']=array('class'=>$rs->custtype);
		$tables->rows[]=$rows;
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>