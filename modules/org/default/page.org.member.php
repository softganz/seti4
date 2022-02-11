<?php
function org_member($self,$psnid=NULL) {
	$self->theme->title='สมาชิก';
	$psnid=SG\getFirst(post('id'),$psnid);

	if ($psnid && is_numeric($psnid)) {
		return R::Page('org.member.info',$self,$psnid);
	}

	if (post('qn')) {
		return R::Page('org.member.search',$self,post('qn'));
	}



	$year=post('y');
	$page=intval(SG\getFirst(post('p'),1));
	$items=50;

	$myorg = org_model::get_my_org();

	if (!$myorg) return message('notify', 'ขออภัย ท่านไม่มีองค์การในความดูแลอยู่ในระบบ');

	$years = mydb::select('SELECT DISTINCT FROM_UNIXTIME(`atdate`,"%Y") `year` FROM %org_doings% WHERE `orgid` IN (:myorg) ORDER BY `year` DESC',':myorg','SET:'.$myorg);

	mydb::where('m.`orgid` IN (:myorg)',':myorg','SET:'.$myorg);
	if ($year) mydb::where('YEAR(`joindate`)=:year',':year',$year);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		  p.*
		, m.*
		, mo.`orgid` inorgid
		, o.`name` orgname
		, COUNT(*) orgcount
		, (SELECT COUNT(*) FROM %org_dos% WHERE `psnid`=m.`psnid` AND `isjoin`=1) joins
		FROM %org_mjoin% AS m
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %org_morg% mo USING(`psnid`)
			LEFT JOIN %db_org% o ON o.`orgid`=mo.`orgid`
		%WHERE%
		GROUP BY m.`psnid`
		ORDER BY m.`joindate` DESC
		LIMIT '.(($page-1)*$items).','.$items;

	$dbs=mydb::select($stmt);

	$totals = $dbs->_found_rows;


	$ui=new Ui();
	$ui->add(
		'<form method="get" action="'.url('org/member').'" class="search-box" data-query="'.url('org/api/member').'" data-rel="#detail" role="search" style="width:200px;">'
			//.'<select class="form-select" name="y" onchange="this.form.submit()" style="border:none;"><option value="">=ทุกปี=</option>';
			//foreach ($years->items as $item) $ret.='<option value="'.$item->year.'"'.($item->year==$year?' selected="selected"':'').'>'.($item->year+543).'</option>';
			//.'</select>';
			.'<input type="text" name="qm" class="form-text" size="20" value="'.post('qm').'" placeholder="ชื่อสมาชิก" style="position:absolute;;margin-left:10px;top:0;"><input type="submit" class="button" value="ค้นหา" />'
			.'</form>'
	);

	$start = $items*($page-1)+1;
	$end = $start+$dbs->_num_rows-1;
	$prevpage = $page>1?$page-1:1;
	$nextpage = $dbs->_num_rows==$items?$page+1:$page;
	$ui->add(number_format($totals).' รายการ');
	$ui->add('<a href="'.url(q(),array('y'=>$year,'qm'=>post('qm'),'p'=>$prevpage)).'" class="sg-action" data-rel="#main"><i class="icon -back"></i></a>');
	$ui->add($start.'-'.$end);
	$ui->add('<a href="'.url(q(),array('y'=>$year,'qm'=>post('qm'),'p'=>$nextpage)).'" class="sg-action" data-rel="#main"><i class="icon -forward"></i></a>');

	$ret .= '<nav class="nav -page"><header class="header"><h3>สมาชิกล่าสุด</h3></header>'.$ui->build().'</nav><!-- nav -->';

	if ($myorg) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('org/member/add').'" title="เพิ่มสมาชิกใหม่"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}



	$ret.='<div id="detail">';
	$tables = new Table();
	$tables->thead=array('date'=>'วันที่เข้าร่วม','ชื่อ - นามสกุล','amt'=>'จำนวนครั้งเข้าร่วม','icons -c1'=>'');
	foreach ($dbs->items as $rs) {
		$isEdit=org_model::is_edit($rs->orgid);
		unset($row);
		$row[]=sg_date($rs->joindate,'ว ดด ปปปป');
		$row[]='<a href="'.url('org/member/info/'.$rs->psnid).'"><strong>'.trim($rs->prename.' '.$rs->name.' '.$rs->lname).'</strong></a>';
		$row[]=$rs->joins;
		if ($rs->joins==0 && $isEdit) {
			$row[]='<a href="'.url('org/member/remove',array('remove'=>$rs->psnid)).'" class="sg-action hover--menu" data-confirm="ต้องการลบกิจกรรมนี้ กรุณายืนยัน?" data-removeparent="tr" title="ลบรายการ"><i class="icon -cancel -hover"></i></a>';
		} else {
			$row[]='';
		}
		$tables->rows[]=$row;
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div><!--detail-->';
	return $ret;
}
?>