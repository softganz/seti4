<?php
function saveup_treat_list($self) {
	$getPage = post('page');
	$itemPerPage = SG\getFirst(post('i'),100);

	$getMemberId = post('mid');

	$payTypeList = saveup_var::$payType;

	R::View('saveup.toolbar',$self,'รายการเบิกค่ารักษาพยาบาล','treat');

	$where = array();
	if ($_GET['sn']) {
		list($firstname,$lastname)=explode(' ',$_GET['sn']);
		if ($firstname && $lastname) {
			mydb::where('(fu.firstname LIKE :firstname AND fu.lastname LIKE :lastname)', ':firstname', $firstname.'%', ':lastname', $lastname.'%');
		} else if ($firstname) {
			mydb::where('(fu.firstname LIKE :firstname OR fu.lastname LIKE "'.$firstname.'%" OR fu.nickname LIKE :firstname)', ':firstname', $firstname.'%');
		}
	}

	if ($getMemberId) mydb::where('tr.mid = :mid', ':mid', $getMemberId);


	mydb::value('$ORDER', $getOrder ? $getOrder : 'tr.`tid`');
	mydb::value('$SORT', $getSort ? $getSort : 'DESC');
	//mydb::value('$LIMIT', (($getPage-1)*$itemPerPage).','.$itemPerPage);

	if ($itemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage-1)*$itemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.($firstRow ? $firstRow.' , ' : '').$itemPerPage);
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
						  tr.`tid`, tr.`date`, tr.`ref`, tr.`mid`
						, CONCAT(fu.`firstname`," ",fu.`lastname`) name
						, tr.`amount`
						, tr.`payfor`
						, tr.`paytype`
						, tr.`disease`
						FROM %saveup_treat% AS tr
							LEFT JOIN %saveup_member% fu USING(`mid`)
						%WHERE%
						ORDER BY $ORDER $SORT
						$LIMIT$;';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($dbs);

	$totalsRows = $dbs->_found_rows;

	$pagePara['o'] = $getOrder;
	$pagePara['s'] = $getSort;
	$pagePara['i'] = $itemPerPage == 100 ? NULL : $itemPerPage;
	$pagePara['page'] = $getPage;
	$pagenv = new PageNavigator($itemPerPage,$getPage,$totalsRows,q(),false,$pagePara);

	//$ret .= '$itemPerPage='.$itemPerPage.' post_i='.post('i');
	//$ret .= print_o($pagePara,'$pagePara');

	//$pagenv = new PageNavigator($items,$getPage,$total_items,q());

	if ($dbs->_empty) return $ret.message('error','ไม่มีรายการเบิกค่ารักษาพยาบาลตามเงื่อนไขที่กำหนด');

	$ret .= $pagenv->show._NL;

	$tables = new Table();
	$tables->addClass('saveup-member-list');
	$tables->thead=array('date date'=>'วันที่','ref -nowrap'=>'เอกสารอ้างอิง','id -nowrap'=>'รหัส','สมาชิก','amt -money'=>'จำนวนเงิน','เพื่อเป็นค่า', 'deseas -hover-parent' => 'โรค');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->date?sg_date($rs->date,'ว ดด ปป'):'',
													$rs->ref,
													$rs->mid,$rs->name.($rs->nickname?' ('.$rs->nickname.')':''),
													number_format($rs->amount,2),
													$payTypeList[$rs->paytype].' ('.$rs->payfor.')',
													$rs->disease
													.'<nav class="nav iconset -hover"><a href="'.url('saveup/treat/view/'.$rs->tid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
													.'<a href="'.url('saveup/treat/modify/'.$rs->tid).'" title="แก้ไขรายละเอียด"><i class="icon -edit"></i></a></nav>',
												);
			$total+=$rs->amount;
	}
	$tables->tfoot[]=array('','','','<strong>รวมทั้งสิ้น</strong>','<strong>'.number_format($total,2).'</strong>','','','');

	$ret .= $tables->build();

	$ret.= $pagenv->show._NL;
	return $ret;
}
?>