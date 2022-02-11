<?php
/**
 * View member information
 *
 * @param Integer $mid
 * @return String
 */


function org_member_search($self,$qn = NULL) {
	$isAdmin = user_access('administer orgdbs');
	$qn = SG\getFirst($qn,post('qn'));

	$dbs = org_model::search_member($qn,org_model::get_my_org());
	$self->theme->title = 'ค้นหาสมาชิก - '.$qn;

	$stext = array();
	if ( $_GET['q'] ) $stext[] = $_GET['q'];
	if ( $_GET['f'] ) $stext[] = $_GET['f'].'*';

	//$ret .= '<table width=100% style="border-top:1px gray solid;background-color:#f5f5f5;"><tr><td>Search</td><td align="right">Results '.($dbs->_num_rows? '1 - '.$dbs->_num_rows.' of '.$dbs->_num_rows.' for '.implode(' | ',$stext):'0 record.').'</td></tr></table>';

	$tables = new Table();
	if ($isAdmin) $tables->thead[] = '';
	$tables->thead['name'] = 'ชื่อ-นามสกุล';
	$tables->thead[] = 'องค์กร';
	$tables->thead['amt'] = '<span title="จำนวนครั้งที่เคยเข้าร่วมกิจกรรม">เข้าร่วม(ครั้ง)</span>';
	//$tables->thead[]='ที่อยู่';
	$tables->thead[] = 'โทรศัพท์';
	$tables->thead[] = 'อีเมล์';
	foreach ($dbs->items as $rs) {
		unset($rows);
		if ($isAdmin) {
			$rows[] = '<input type="checkbox" name="id['.$rs->psnid.']" value="'.$rs->psnid.'" />';
		}
		$rows[] = '<a href="'.url('org/member/'.$rs->psnid).'">'.$rs->name.' '.$rs->lname.'</a>';
		$rows[] = $rs->orgname?'<a href="'.url('org/'.$rs->inorgid).'">'.$rs->orgname.'</a>'.($rs->orgcount>1?' ('.$rs->orgcount.' องค์กร)':''):'';
		$rows[] = $rs->joins;
		//$rows[] = SG\implode_address($rs,'short');
		$rows[] = $isAdmin ? $rs->phone : '';
		$rows[] = $isAdmin ? $rs->email : '';
		$tables->rows[] = $rows;
	}

	if ($isAdmin) {
		$ret .= '<form method="POST" action="'.url('org/admin/merge').'">';
		$ret .= '<button class="btn -primary" type="submit" name="save"><i class="icon -addbig -white"></i><span>รวมชุดข้อมูล</span></button> รวม <b>'.number_format($dbs->count()).'</b> รายการ';
	}

	$ret .= $tables->build();
	if ($isAdmin) {
		$ret .= '</form>';
	}
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>