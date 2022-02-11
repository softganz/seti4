<?php
/**
* Project Objective Form
*
* @param Object $self
* @param Integer $tpid
* @return String
*/
function project_problem_form($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	$stmt='SELECT p.*,pn.`name` `planName`
					FROM %tag% p
						LEFT JOIN %tag% pn ON pn.`taggroup`="project:planning" AND CONCAT("project:problem:",pn.`catid`)=p.`taggroup`
					WHERE p.`taggroup` IN
						(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="supportplan")';
	$problemDbs=mydb::select($stmt,':tpid',$tpid);


	$form=new Form(NULL,url('project/'.$tpid.'/info.problem/edit'),NULL,'sg-form project-info-problem-form');
	$form->addData('rel','#project-info-problem');
	$form->addData('checkValid',true);
	$form->addConfig('title','เพิ่มสถานการณ์');

	/*
	$optionsObjective['']='==เลือกตัวอย่างสถานการณ์==';
	foreach ($problemDbs->items as $rs) {
		$detail=json_decode($rs->description);
		$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid]=$rs->name;
	}
	$form->addField(
						'problemref',
						array(
							'type'=>'select',
							'name'=>'problemref',
							'label'=>'เลือกสถานการณ์ตัวอย่างจากแผนงาน:',
							'class'=>'-fill',
							'options'=>$optionsObjective,
						)
					);
	*/
	$form->addField(
						'problemother',
						array(
							'type'=>'text',
							'name'=>'problemother',
							'label'=>'ระบุสถานการณ์อื่นๆ',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุสถานการณ์เพิ่มเติม',
						)
					);

	$form->addField(
						'problemsize',
						array(
							'type'=>'text',
							'name'=>'problemsize',
							'label'=>'ขนาด',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'0.00',
						)
					);

	$form->addField(
				'save',
				array(
					'type'=>'button',
					'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
					'posttext'=>'<a class="sg-action" href="'.url('project/'.$tpid.'/info.problem/edit').'" data-rel="#project-info-problem">{tr:Cancel}</a>',
					)
				);
	$ret.=$form->build();
	$ret.='<style type="text/css">
	.project-info-problem-form {margin:32px 16px; text-align:left; box-shadow:0 0 0 1px #eee inset;border-radius:4px;}
	.project-info-problem-form h3.title {border-radius:4px 4px 0 0;font-size:1.2em;background:#e5e5e5;color:#666;text-align:center;}
	.project-info-problem-form .form-item {padding:8px;}
	</style>';

	//$ret.=print_o($problemDbs);

	return $ret;
}
?>