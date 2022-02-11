<?php
/**
 * ibuy_franchise class for shop on web
 *
 * @package ibuy
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2009-06-22
 * @modify 2009-12-09
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

 function ibuy_franchise($self, $id = NULL) {
	$self->theme->title='Franchise Shop';

	if ($id && is_numeric($id)) return R::Page('ibuy.franchise.view',$self,$id);

	$ret.=R::View('ibuy.franchise.menu','franchise');

	$todays=mydb::select('SELECT f.`custname` , f.`custtype`, u.`uid` , u.`username` , p.name province FROM %ibuy_customer% f LEFT JOIN %users% u ON u.uid=f.uid LEFT JOIN %province% p ON p.pid=f.pid WHERE `datein` BETWEEN "'.date('Y-m-d').' 00:00:00" AND "'.date('Y-m-d').' 23:59:59"');
	if ($todays->_num_rows) {
		$ret.='<p>สมาชิกสมัครใหม่ของวันนี้ : ';
		foreach ($todays->items as $rs) {
			$ret.='<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="'.ibuy_define::custtype($rs->custtype).'">'.$rs->custname.' ('.strtoupper(substr($rs->custtype,0,1)).') - '.$rs->province.'</a> , ';
		}
		$ret=trim($ret,' , ').'</p>';
	}

	$ret.='<p><form id="search" class="search-box" method="get" action="'.url('ibuy/franchise').'" name="memberlist" role="search"><input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="search" size="40" value="'.post('search').'" placeholder="ค้นหา ชื่อร้าน หรือ จังหวัด" data-query="'.url('ibuy/api/franchise').'" data-callback="'.url('ibuy/franchise/').'" data-altfld="sid" style="width:100%;"><button type="submit"><i class="icon -search"></i><span>ค้นหา</span></button></form></p>'._NL;

	/*
	$ret .= '<p><form method="get" action="'.url('ibuy/franchise').'"><input type="hidden" name="fid" id="fid" />';
	$ret .= '<label>ชื่อร้าน หรือ จังหวัด</label> <input class="form-text" type="text" name="search" id="search-name" size="60" value="'.$_GET['search'].'"><button type="submit"><i class="icon -search"></i><span>ค้นหา</span></button> <button type="submit" name="view"><i class="icon -search"></i><span>ดูรายละเอียด</span></button>';
	$ret .= '</form></p>';
	$ret.='<p class="description">ตัวอย่างการค้นหา เช่น <strong>กันเอง</strong> คือการค้นหาร้านที่มีชื่อขึ้นต้นว่า "กันเอง" , <strong>จังหวัดสงขลา</strong> คือการค้นหารายชื่อทุกร้านที่อยู่ในจังหวัด "สงขลา"</p>';
	$ret.='<script type="text/javascript">
var options_xml = {script: function (input) { return "'.url('ibuy/franchise/get/name','').'input="+input; },varname:"input",minchars:2,callback: function set_fid(o) {	document.getElementById("fid").value=o.id;}};
var as_xml = new bsn.AutoSuggest(\'search-name\', options_xml);
</script>';
		head('<script type="text/javascript" src="/library/autocomplete/bsn.AutoSuggest_2.1.3.js" charset="utf-8"></script>
<link rel="stylesheet" href="/library/autocomplete/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />');
*/

		$search=post('search');

	mydb()->where('`custtype`="franchise"');
	if ($search) mydb()->where('p.`name` LIKE :name OR f.`custname` LIKE :name',':name','%'.$search.'%');

	$stmt='SELECT f.`custname`, f.`custtype`, f.`latlng`
					, f.`uid`, f.`discount`, f.`discount_hold`
					, u.`uid`, u.`username`, p.`name` province_name
					, f.`custaddress`, f.`custphone`, t.`tpid`
				FROM %ibuy_customer% f
					LEFT JOIN %province% p ON p.`pid`=f.`pid`
					LEFT JOIN %users% u ON u.`uid`=f.`uid`
					LEFT JOIN %topic% t ON t.`type`="franchise" AND t.`uid`=f.`uid`
				%WHERE%
				ORDER BY CONVERT(f.`custname` USING tis620) ASC';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead['no']='no';
	$tables->thead[]='ชื่อร้าน';
	$tables->thead[]='T';
	if (user_access('administer ibuys')) {
		$tables->thead[]='M';
	}
	$tables->thead['province']='จังหวัด';
	$tables->thead[]='';
	$tables->thead[]='ที่อยู่';
	$tables->thead[]='โทรศัพท์';
	if (user_access('administer ibuys')) {
		$tables->thead['money discount']='ส่วนลดสะสม';
	}
	foreach ($dbs->items as $rs) {
		if (in_array($rs->uid,array(1))) continue;
		unset($rows);
		$rows[]=++$no;
		$rows[]='<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="ดูรายละเอียด '.ibuy_define::custtype($rs->custtype).' '.htmlspecialchars($rs->custname).'">'.SG\getFirst($rs->custname,'ไม่ระบุ').'</a>';
		$rows[]=strtoupper(substr($rs->custtype,0,1));
		if (user_access('administer ibuys')) {
			$rows[]=$rs->discount_hold<0?'-':'<a href="'.url('ibuy/franchise/'.$rs->uid.'/edit').'" title="เฟรนไชส์ '.htmlspecialchars($rs->custname).' ยังไม่สามารถใช้ค่าการตลาดเป็นส่วนลดได้">H</a>';
		}
		$rows[]=$rs->province_name;
		$rows[]=($rs->latlng?'<img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|EEEEEE|FFFFFF" height="24" alt="Map" title="บันทึกแผนที่แล้ว"/>':'');
		$rows[]=$rs->custaddress;
		$rows[]=user_access('access user profiles') ? $rs->custphone : '**';
		$rows['config']=array('class'=>$rs->custtype);
		if (user_access('administer ibuys')) {
			$rows[]=$rs->discount>0?number_format($rs->discount,2):'-';
		}
		$tables->rows[]=$rows;
	}

	$ret .= $tables->build();

	return $ret;
}
?>