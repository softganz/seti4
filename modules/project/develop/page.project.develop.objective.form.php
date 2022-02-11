<?php
/**
* Project Objective Form
*
* @param Object $self
* @param Integer $tpid
* @return String
*/
function project_develop_objective_form($self, $tpid = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;

	$stmt='SELECT p.*,pn.`name` `planName`
					FROM %tag% p
						LEFT JOIN %tag% pn ON pn.`taggroup`="project:planning" AND CONCAT("project:problem:",pn.`catid`)=p.`taggroup`
					WHERE p.`taggroup` IN
						(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="supportplan")';
	$problemDbs=mydb::select($stmt,':tpid',$tpid);

	//$ret.=print_o($problemDbs);

	$form=new Form(NULL,url('project/develop/info/'.$tpid.'/objective.edit'),NULL,'sg-form project-objective-form');
	$form->addData('rel','replace:#project-develop-objective');
	$form->addData('ret',url('project/develop/'.$tpid.'/objective/edit'));

	$form->addConfig('title','เพิ่มวัตถุประสงค์');

	/*
	$optionsObjective['']='==เลือกตัวอย่างวัตถุประสงค์==';
	foreach ($problemDbs->items as $rs) {
		$detail=json_decode($rs->description);
		$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid]=$detail->objective;
	}
	$form->addField(
						'problemref',
						array(
							'type'=>'select',
							'label'=>'เลือกตัวอย่างวัตถุประสงค์:',
							'class'=>'-fill',
							'options'=>$optionsObjective,
						)
					);
	*/
	$form->addField(
						'objective',
						array(
							'type'=>'text',
							'label'=>'ระบุวัตถุประสงค์',
							'class'=>'-fill',
							'placeholder'=>'ระบุวัตถุประสงค์ด้วยตนเอง',
						)
					);
	$form->addField(
						'indicator',
						array(
							'type'=>'textarea',
							'label'=>'ตัวชี้วัดความสำเร็จ',
							'class'=>'-fill',
							'rows'=>3,
						)
					);
	$form->addField(
						'problemsize',
						array(
							'type'=>'text',
							'label'=>'ขนาดปัญหา',
							'class'=>'-fill',
							'placeholder'=>'0.00',
							'autocomplete'=>'off',
						)
					);
	$form->addField(
						'targetsize',
						array(
							'type'=>'text',
							'label'=>'เป้าหมาย 1 ปี',
							'class'=>'-fill',
							'placeholder'=>'0.00',
							'autocomplete'=>'off',
						)
					);
	$form->addField(
				'save',
				array(
					'type'=>'button',
					'value'=>'<i class="icon -save -white"></i><span>บันทึกวัตถุประสงค์</span>',
					'pretext'=>'<a class="sg-action btn -link -cancel" href="'.url('project/develop/objective/'.$tpid.'/edit').'" data-rel="#project-develop-objective"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a> ',
					)
				);
	$ret.=$form->build();
	$ret.='<script type="text/javascript">
	$("#edit-problemref").change(function(){
		var $this=$(this);
		console.log("Change "+$this.val());
		if ($this.val()!="") {
			$("#form-item-edit-objective").hide();
			$("#edit-objective").val("");
			$("#form-item-edit-indicator").hide();
		} else {
			$("#form-item-edit-objective").show();
			$("#form-item-edit-indicator").show();
		}
	});
	</script>';
	return $ret;
}






function project_develop_objective_form_v1($self,$tpid) {
	$nextid=post('nextid');
	$objTypeList=array();
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;

	$form=new Form(
							'data',
							url('project/develop/objective/'.$tpid.'/add'),
							'project-develop-object-form',
							'sg-form box project-develop-object-form'
							);

	$form->addData('rel','parent');
	$form->addData('checkValid',true);
	$form->addField(
					'title',
					array(
						'type'=>'text',
						'label'=>'วัตถุประสงค์',
						'class'=>'-fill',
						'require'=>true,
						'placeholder'=>'ระบุวัตถุประสงค์ที่ต้องการเพิ่ม'
						)
					);
	$form->addField(
						'parent',
						array(
							'type'=>'radio',
							'label'=>'กลุ่มวัตถุประสงค์:',
							'options'=>$objTypeList,
							'require'=>true
							)
						);
	$form->addField(
						'indicators',
						array(
							'type'=>cfg('project.objective.single')?'textarea':'hidden',
							'label'=>'ตัวชี้วัดความสำเร็จ',
							'class'=>'-fill',
							'rows'=>4,
							'description'=>'* ระบุตัวชี้วัดรายการละ 1 บรรทัด'
							)
						);
	$form->addField(
						'save',
						array(
							'type'=>'button',
							'value'=>'<i class="icon -save -white"></i><span>เพิ่มวัตถุประสงค์'.($nextid?' ข้อที่ '.$nextid:'').'</span>'
							)
						);
	$ret.=$form->build();
	return $ret;
}
?>