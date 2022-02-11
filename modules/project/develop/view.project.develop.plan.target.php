<?php
function view_project_develop_plan_target($devInfo,$mainactid) {
	$tagname='develop:mainact';
	$tpid=$devInfo->tpid;
	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;

	$tables = new Table();
	$tables->addClass('project-develop-plan-target');
	$tables->thead=array('target'=>'กลุ่มเป้าหมาย','amt -target'=>'จำนวน(คน)');
	if ($isEdit) $tables->thead['center -center']='';

	$stmt='SELECT t.*, g.`name`, p.`name` `targetGroupName`
					FROM %project_target% t
						LEFT JOIN %tag% g ON g.`taggroup`="project:target" AND g.`catid`=t.`tgtid`
						LEFT JOIN %tag% p ON p.`taggroup`="project:targetgroup" AND p.`catid`=g.`catparent`
					WHERE `tpid`=:tpid AND `trid`=:mainactid AND `tagname`=:tagname
					ORDER BY g.`catparent`;
					-- {sum:"amount"}';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':mainactid',$mainactid, ':tagname',$tagname);

	foreach ($dbs->items as $rs) {
		if ($rs->targetGroupName!=$currentGroupName) {
			$tables->rows[]=array('<th colspan="'.($isEdit?3:2).'" style="text-align:left;">'.$rs->targetGroupName.'</th>','config'=>array('class'=>'subheader'));
			$currentGroupName=$rs->targetGroupName;
		}
		unset($row);
		$row=array($rs->name, number_format($rs->amount));
		if ($isEdit) $row[]='<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/removetarget/'.$rs->trid,array('target'=>$rs->tgtid)).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -delete -hover"></i></a>';
		$tables->rows[]=$row;
	}
	$form=new Form('target',url('project/develop/plan/'.$tpid.'/addtarget/'.$mainactid),'target','sg-form project-target-form');
	$form->addData('rel','replace');
	$form->addField('target',array('type'=>'select','name'=>'target','id'=>'target','class'=>'-fill -showbtn','label'=>'กลุ่มเป้าหมาย','options'=>array(''=>'** เลือกกลุ่มเป้าหมาย **')+R::Model('project.target.getoption')));
	$form->addField('h1',array('type'=>'textfield','value'=>'<b>สถานการณ์ปัจจุบัน</b>'));
	$form->addField('currentact',array('type'=>'text','label'=>'มีกิจกรรมทางกาย ร้อยละ'));
	$form->addField('currentslow',array('type'=>'text','label'=>'มีพฤติกรรมเนือยนิ่ง ร้อยละ'));
	$form->addField('h2',array('type'=>'textfield','value'=>'<b>ความคาดหวัง</b>'));
	$form->addField('expectact',array('type'=>'text','label'=>'มีกิจกรรมทางกาย ร้อยละ'));
	$form->addField('expectslow',array('type'=>'text','label'=>'มีพฤติกรรมเนือยนิ่ง ร้อยละ'));
	$form->addField('save',array('type'=>'button','value'=>'<i class="icon -add"></i><span>เพิ่มกลุ่มเป้าหมาย</span>'));
	//$form->get('target');
	//$ret.=$form->build();
	//$ret.=print_o($form->get(),'form');

	if ($isEdit) {
		$ret.=$form->get('form');
		$tables->rows[]=array(
											'<div class="form-item">'.$form->get('target').'</div>',
											'<div class="form-item" style="display:none;"><input class="form-text -numeric -fill" type="text" name="amount" size="4" placeholder="0" tabindex="6" /></div>',
											'<div class="form-item" style="display:none;"><button class="btn -nowrap"><i class="icon -add"></i><span>เพิ่ม</span></button></div>',
											'config'=>array('class'=>'-no-print')
											);
	}

	unset($row);
	$row=array('รวมกลุ่มเป้าหมาย','<td class="col-amt">'.number_format($dbs->sum->amount).'</td>');
	if ($isEdit) $row[]='';
	$tables->tfoot[]=$row;
	$ret.=$tables->build();
	if ($isEdit) $ret.='</form>';
	//$ret.=print_o($mainact,'$mainact');
	return $ret;
}
?>