<?php
function view_project_plan_target_view($projectInfo,$actid = NULL) {
	$tpid=$projectInfo->tpid;
	$tagname='project:mainact';
	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;

	$tables = new Table();
	$tables->addClass('project-target');
	$tables->addConfig('showHeader',false);
	$tables->thead=array('target'=>'กลุ่มเป้าหมาย','amt -target'=>'จำนวน(คน)');
	if ($isEdit) $tables->thead['icons -center']='';

	$stmt='SELECT t.*, g.`name`, p.`name` `targetGroupName`
					FROM %project_target% t
						LEFT JOIN %tag% g ON g.`taggroup`="project:target" AND g.`catid`=t.`tgtid`
						LEFT JOIN %tag% p ON p.`taggroup`="project:targetgroup" AND p.`catid`=g.`catparent`
					WHERE t.`tpid`=:tpid AND t.`tagname`=:tagname AND `trid`=:trid
					ORDER BY g.`catparent`,g.`catid`;
					-- {sum:"amount"}';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':tagname',$tagname, ':trid',$actid);
	//$ret.=mydb()->_query;

	if ($dbs->_empty) {
		if ($isEdit) $tables->rows[]=array('<td colspan="3"><p class="notify">ยังไม่ได้กำหนดกลุ่มเป้าหมาย กรุณา<b>เลือกกลุ่มเป้าหมาย</b>จากช่องเลือกด้านล่าง</p></td>');
		$tables->addConfig('showHeader',true);
	}

	foreach ($dbs->items as $rs) {
		if ($rs->targetGroupName!=$currentGroupName) {
			$tables->rows[]=array('<th colspan="'.($isEdit?3:2).'" style="text-align:left;">'.$rs->targetGroupName.'</th>','config'=>array('class'=>'subheader'));
			$currentGroupName=$rs->targetGroupName;
			$tables->rows[]='<header>';
		}
		$row=array(
					$rs->name,
					number_format($rs->amount)
					);
		if ($isEdit) $row[]='<a class="sg-action" href="'.url('project/plan/'.$tpid.'/deletetarget/'.$rs->trid,array('tgtid'=>$rs->tgtid)).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -hover"></i></a>';
		$tables->rows[]=$row;
	}



	$form=new Form('target',url('project/'.$tpid.'/info/target.add/'.$actid),'target','sg-form project-target-form');
	$form->addData('rel','replace');

	$form->addField('target',array('type'=>'select','name'=>'target','id'=>'target','class'=>'-fill -showbtn','label'=>'กลุ่มเป้าหมาย','options'=>array(''=>'** เลือกกลุ่มเป้าหมาย **')+R::Model('project.target.getoption')));

	if ($isEdit) {
		$ret.=$form->get('form');
		$ret.='<input type="hidden" name="tagname" value="project:mainact" />';
		$ret.='<input type="hidden" name="currentpa" /><input type="hidden" name="currentslow" /><input type="hidden" name="currentfat" />';
		$ret.='<input type="hidden" name="expectpa" /><input type="hidden" name="expectslow" /><input type="hidden" name="expectfat" />';
		$tables->rows[]=array(
											'<div class="form-item">'.$form->get('target').'</div>',
											'<div class="form-item" style="display:none;"><input class="form-text -numeric" type="text" name="amount" size="4" placeholder="0" tabindex="8" /></div>',
											'<div class="form-item" style="display:none;"><button class="btn -primary -nowrap"><i class="icon -save -white"></i><span class="-hidden">เพิ่มกลุ่มเป้าหมาย</span></button></div>',
											'config'=>array('class'=>'-no-print')
											);
	}

	$row=array('<td>รวมกลุ่มเป้าหมาย</td>','<td class="col-amt">'.number_format($dbs->sum->amount).'</td>');
	if ($isEdit) $row[]='';
	$tables->tfoot[]=$row;
	$ret.=$tables->build();
	if ($isEdit) $ret.='</form>';
	return $ret;
}
?>