<?php
/*
* Org meeting homepage
*
* @param Int $doid
* @return String
*/
function org_admin_meeting($self) {
	$self->theme->title='กิจกรรม@Admin';
	$year=post('y');
	$page=intval(SG\getFirst(post('p'),1));
	$items=10;
	$order=SG\getFirst(post('o'),'d.`atdate`');

	$years=mydb::select('SELECT DISTINCT FROM_UNIXTIME(`atdate`,"%Y") `year` FROM %org_doings% ORDER BY `year` DESC');


	if (post('org')) mydb::where('d.`orgid`=:orgid',':orgid',post('org'));
	else if (post('tpid')) mydb::where('d.`tpid`=:tpid',':tpid',post('tpid'));
	if ($year) mydb::where('FROM_UNIXTIME(`atdate`,"%Y")=:year',':year',$year);
	if (post('qm')) mydb::where('d.`doings` LIKE :doings',':doings','%'.post('qm').'%');

	mydb::value('$order',$order);
	mydb::value('$start',($page-1)*$items);
	mydb::value('$items',$items);

	$stmt='SELECT SQL_CALC_FOUND_ROWS d.*,
							(SELECT COUNT(*) FROM %org_dos% jd WHERE jd.`doid`=d.`doid` AND jd.`isjoin`=1) joins
						FROM %org_doings% d
						%WHERE%
						ORDER BY $order DESC
						LIMIT $start , $items';
	$dbs=mydb::select($stmt);
	//$ret .= mydb()->_query;
	$totals = $dbs->_found_rows;


	$ui=new Ui(NULL,'ui-nav');
	$formStr='<form method="get" action="'.url('org/admin/meeting').'" data-query="'.url('org/api/meeting').'" data-rel="#detail" role="search">';
	if (post('tpid')) $formStr.='<input type="hidden" name="tpid" value="'.post('tpid').'" />';
	$formStr.='<select class="form-select" name="y" onchange="this.form.submit()" style="border:none;"><option value="">=ทุกปี=</option>';
	foreach ($years->items as $item) $formStr.='<option value="'.$item->year.'"'.($item->year==$year?' selected="selected"':'').'>'.($item->year+543).'</option>';
	$formStr.='</select> ';
	$formStr.='<input type="text" name="qm" class="sg-autocomplete form-text" size="20" value="'.post('qm').'" data-query="'.url('org/api/meeting').'" placeholder="ชื่อกิจกรรม" style="width:200px;"> ';
	$formStr.='<button class="btn -primary" type="submit"><i class="icon -search -white"></i><span>ค้นหา</span></button>';
	$formStr.='</form> ';
	$ui->add($formStr);

	$start=$items*($page-1)+1;
	$end=$start+$dbs->_num_rows-1;
	$prevpage=$page>1?$page-1:1;
	$nextpage=$dbs->_num_rows==$items?$page+1:$page;

	$ui->add('&nbsp;'.$totals.' กิจกรรม');
	$ui->add('<a href="'.url(q(),array('y'=>$year,'qm'=>post('qm'),'org'=>post('org'),'o'=>post('o'),'tpid'=>post('tpid'),'p'=>$prevpage)).'" class="sg-action" data-rel="#main"><i class="icon -back"></i></a>');
	$ui->add($start.'-'.$end);
	$ui->add('<a href="'.url(q(),array('y'=>$year,'qm'=>post('qm'),'org'=>post('org'),'tpid'=>post('tpid'),'o'=>post('o'),'p'=>$nextpage)).'" class="sg-action" data-rel="#main"><i class="icon -forward"></i></a>');

	$ret.='<nav class="nav -page"><header class="header -hidden"><h3>กิจกรรมล่าสุด</h3></header>'._NL;
	$ret.=$ui->build();
	$ret.='</nav><!--navbar-->'._NL;

	$ret.='<div id="detail">';
	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','กิจกรรม','amt -hover-parent'=>'ผู้เข้าร่วม(คน)');
	foreach ($dbs->items as $rs) {
		$isEdit=org_model::is_edit($rs->orgid);
		unset($row);
		$row[]=sg_date($rs->atdate,'ว ดด ปปปป');
		$row[]='<a href="'.url('org/'.$rs->orgid.'/meeting.info/'.$rs->doid).'"><strong>'.SG\getFirst($rs->doings,'???').'</strong></a><br /><span>ณ '.$rs->place.'</span>';
		$row[] = $rs->joins
					. ($rs->joins==0 && $isEdit ? '<nav class="nav iconset -hover"><a href="'.url('org/'.$rs->orgid.'/meeting.delete/'.$rs->doid).'" class="sg-action" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?" data-removeparent="tr" data-rel="this" title="ลบรายการ"><i class="icon -cancel -gray"></i></a></nav>' : '');
		$tables->rows[]=$row;
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div><!--detail-->';
	return $ret;
}
?>