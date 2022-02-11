<?php
function project_planning_year($self, $orgId = NULL, $year = NULL, $action = NULL) {
	// Data Model
	$orgId = SG\getFirst(post('f','fundid'),$orgId);
	$fundInfo = NULL;

	if ($orgId) {
		$fundInfo = R::Model('project.fund.get',$orgId);
		if (!$fundInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');
	}

	$isEdit = $fundInfo->right->edit || $fundInfo->right->trainer || user_access('administer projects');

	$yearPlanDbs = mydb::select(
		'SELECT p.*, t.`title`, t.`orgid`, pt.`refid` `plangroup`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project_tr% pt ON pt.`tpid` = p.`tpid` AND pt.`formid` = "info" AND pt.`part` = "title"
		WHERE p.`prtype` = "แผนงาน" AND t.`orgid` = :orgid AND p.`pryear` = :pryear
		ORDER BY `pryear` ASC, `tpid` ASC;
		-- {key:"plangroup"}',
		':orgid', $fundInfo->orgid,
		':pryear', $year
	);
	//$ret.=print_o($yearPlanDbs);


	$sitList = mydb::select(
		'SELECT * FROM %tag%
		WHERE `taggroup` = "project:planning"
		ORDER BY `weight` ASC, `catid` ASC;
		-- {key:"catid"}'
	);
	//$ret.=print_o($sitList);



	// View Model
	R::View('project.toolbar',$self,'แผนงานปีงบประมาณ '.($year+543).' - '.$fundInfo->name, $fundInfo->fundid ? 'fund' : 'org', $fundInfo);

	$ret .= '<h3>แผนงานปีงบประมาณ '.($year+543).'</h3>';

	$tables = new Table();
	$tables->thead=array('ชื่อปัญหา','center -size'=>'ขนาด','center -target'=>'เป้าหมาย(%)','icons -c1'=>'');
	$ui=new Ui(NULL,'card -planning');

	$sitSelect='<select class="form-select" name="sid"><option value="">== เลือกแผนงาน ==</option>';

	foreach ($sitList->items as $sitKey=>$sitValue) {
		// If not localfund then hide แผนงานบริหารกองทุน
		if ($sitKey == 15 && empty($fundInfo->fundid)) continue;

		$isCreated=array_key_exists($sitKey, $yearPlanDbs->items);
		if ($sitValue->process || $isCreated) {
			$str='<h3>'.$sitValue->name.'</h3>';
			$str.='<nav class="nav">';
			if ($isCreated) $str.='<a class="btn" href="'.url('project/planning/'.$yearPlanDbs->items[$sitKey]->tpid).'" title="ดูรายละเอียดแผนงาน"><i class="icon -material">find_in_page</i></a>';
			if ($isEdit) {
				if ($isCreated) {
					$str.='<a class="btn" href="'.url('project/planning/'.$yearPlanDbs->items[$sitKey]->tpid).'" title="แก้ไขรายละเอียดแผนงาน"><i class="icon -material">edit</i></a>';
				} else {
					$str.='<a class="sg-action btn -primary" href="'.url('project/planning/create',array('yr'=>$year,'oid'=>$fundInfo->orgid,'sid'=>$sitKey)).'" data-title="สร้างแผนงานใหม่" data-confirm="ต้องการสร้างแผนงานใหม่ กรุณายืนยัน?"><i class="icon -material">add</i></a>';
				}
			}
			$str.='</nav>';
			$ui->add($str);
		} else {
			$sitSelect.='<option value="'.$sitKey.'">'.$sitValue->name.'</option>';
		}
	}
	$sitSelect.='</select>';
	$ui->add('<h3>แผนงานอื่น ๆ</h3><form class="form" method="get" action="'.url('project/planning/create').'"><input type="hidden" name="yr" value="'.$year.'" /><input type="hidden" name="oid" value="'.$fundInfo->orgid.'" />'.$sitSelect.'<button class="btn -primary"><i class="icon -material">add</i><span></span></button>'.'</form>');
	$tables->rows[]=array($sitSelect,'<input type="text" size="3" />','<input type="text" size="3" />','<a class="-primary" href="javascript:void(0)"><i class="icon -material -circle">add</i></a>');

	$ret.=$ui->build();

	/*
	$ret.='<div class="box clear">';
	$ret.='<h3>สถานการณ์สุขภาพ</h3>';
	$ret.=$tables->build();
	$ret.='</div>';
	*/

	head(
		'<style type="text/css">
		.card.-planning h3 {padding: 8px 0;background: #eee; border-radius: 8px 8px 0 0;}
		.card.-planning .ui-item {height:160px; text-align:center;border-top:1px #ddd solid;border-left:1px #ddd solid; border-radius: 8px;}
		.card.-planning .nav {margin:32px 0;}
		.card.-planning .btn {width:24px;height:24px;padding:16px;border-radius:50%;display:inline-block;margin:0 32px;}
		.card.-planning .form-select {display: block;margin: 8px auto;}
		.card.-planning .form .btn {padding: 28px; margin:0;position:relative;}
		.card.-planning .form .btn .icon {position:absolute;top:16px;left:16px;}
		</style>');
	return $ret;
}
?>