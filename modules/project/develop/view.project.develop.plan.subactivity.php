<?php
function view_project_develop_plan_activity($devInfo,$mainactid) {
	$tagname='develop';
	$tpid=$devInfo->tpid;
	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$mainact=$devInfo->mainact[$mainactid];
	$totalBudget=0;

	$stmt='SELECT
					`tpid`,`parent`,`date1` `from_date`,`date2` `to_date`,`detail1` `title`,`num1` `budget`
					FROM %project_tr%
					WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" AND `parent`=:mainactid
					ORDER BY `from_date` ASC;
					-- {sum:"budget"}';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':tagname',$tagname, ':mainactid',$mainactid);
	//$ret.=print_o($dbs);
	$ret.='<form class="sg-form" action="'.url('project/develop/plan/'.$tpid.'/addactivity/'.$mainactid).'" data-rel="replace" data-checkvalid="true">';
	$tables = new Table();
	$tables->thead=array('date'=>'วันที่ทำกิจกรรม','ชื่อกิจกรรมย่อย','amt'=>'งบประมาณ','');

	if ($dbs->_empty) $tables->rows[]=array('<td colspan="4" align="center">ยังไม่กำหนดกิจกรรมย่อย</td>');
	foreach ($dbs->items as $rs) {
		if ($rs->from_date==$rs->to_date) $actionDate= sg_date($rs->from_date,'ว ดด ปป');
		else if (sg_date($rs->from_date,'Y-m')==sg_date($rs->to_date,'Y-m')) $actionDate=sg_date($rs->from_date,'ว').'-'.sg_date($rs->to_date,'ว').' '.sg_date($rs->from_date,'ดด ปป');
		else $actionDate=sg_date($rs->from_date,'ว ดด ปป').'-'.sg_date($rs->to_date,'ว ดด ปป');

		$tables->rows[]=array(
											$actionDate,
											$rs->title,
											number_format($rs->budget,2),
											'',
											);
	}
	if ($isEdit) {
		$todateid='project-develop-activity-todate-'.$mainactid;
		$tables->rows[]=array(
											'<div class="form-item -inlineblock"><input class="form-text sg-datepicker -date -showbtn -require sg-checkdatefrom" type="text" name="datefrom" placeholder="ระบุวันที่ทำกิจกรรม" size="10" readonly="true" data-diff="'.$todateid.'" /></div><div class="form-item -inlineblock" style="display:none;">&nbsp;-&nbsp;<input id="'.$todateid.'" class="form-text sg-datepicker -date -require sg-checkdateto" type="text" name="dateto" placeholder="ถึงวันที่" size="10" readonly="true" /></div>',
											'<div class="form-item" style="display:none;"><input class="form-text -fill -require" type="text" name="title" size="4" placeholder="ระบุชื่อกิจกรรมย่อย" tabindex="6" /></div>',
											'<div class="form-item" style="display:none;"><input class="form-text -numeric -fill" type="text" name="amount" size="4" placeholder="0.00" tabindex="6" /></div>',
											'<div class="form-item" style="display:none;"><button class="btn -nowrap"><i class="icon -add"></i><span>เพิ่มกิจกรรมย่อย</span></button></div>',
											'config'=>array('class'=>'-no-print')
											);
	}
	$tables->tfoot[]=array('','รวมงบประมาณ','<span class="'.($mainact->budget==$dbs->sum->budget?'-ok':'-error').'" data-tooltip="งบประมาณกิจกรรมหลัก = '.number_format($mainact->budget,2).' บาท<br />งบประมาณกิจกรรมย่อย = '.number_format($dbs->sum->budget,2).' บาท">'.number_format($dbs->sum->budget,2).'</span>','');
	$ret.=$tables->build();
	$ret.='</form>';
	//$ret.=$isEdit?'<p align="right"><a class="sg-action btn -primary -no-print" href="'.url('project/develop/plan/'.$tpid,array('action'=>'addactivity','id'=>$mainact->trid)).'" data-rel="box" title="เพิ่มกิจกรรมย่อย"><i class="icon -add"></i><span>เพิ่มกิจกรรมย่อย</span></a></p>':'';
	//$ret.=print_o($mainact);
	return $ret;
}
?>