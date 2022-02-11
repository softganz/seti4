<?php
/**
* Project idea
* Content :
* 	- ข้อมูลโครงการ
*			- ชื่อโครงการ
*			- ความเป็นมาและสถานการณ์
*			- กิจกรรมหลักที่จะดำเนินโครงการ
* 	- ข้อมูลผู้ขอทุน
*			- ชื่อ หน่วยงาน โทร อีเมล์
*/
function view_project_idea_form($data) {
	if (is_array($data)) $data=(object)$data;
	$form=new Form('topic',url('project/idea/create'),'project-idea-form','__formcreate');
	$form->addClass('sg-form');
	$form->addData('checkValid',true);
	$form->addData('test','test value');

	$form->addField('tpid',array('type'=>'hidden','value'=>$data->tpid));
	$form->addField(
					'title',
					array(
						'type'=>'text',
						'label'=>'ชื่อโครงการ',
						'class'=>'-fill',
						'require'=>true,
						'placeholder'=>'ระบุชื่อโครงการ',
						'value'=>$data->title,
					)
				);
	$form->addField(
					'problem',
					array(
						'type'=>'textarea',
						'label'=>'ความเป็นมาและสถานการณ์',
						'class'=>'-fill',
						'require'=>true,
						'placeholder'=>'อธิบายความเป็นมาและสถานการณ์ที่ต้องการแก้ปัญหา',
						'value'=>$data->problem,
					)
				);
	$form->addField(
						'activity',
						array(
							'type'=>'textarea',
							'label'=>'กิจกรรมหลักที่จะดำเนินโครงการ',
							'class'=>'-fill','require'=>true,
							'placeholder'=>'บรรยายกิจกรรมหลักที่จะดำเนินโครงการ',
							'value'=>$data->activity,
						)
					);
	$form->addField(
						'byname',
						array(
							'type'=>'text',
							'label'=>'ชื่อผู้ขอทุน',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุชื่อ-นามสกุลผู้ขอทุน',
							'value'=>$data->byname,
						)
					);
	$form->addField(
						'orgname',
						array(
							'type'=>'text',
							'label'=>'ชื่อหน่วยงาน',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุชื่อหน่วยงาน',
							'value'=>$data->orgname,
						)
					);
	$form->addField(
						'phone',
						array(
							'type'=>'text',
							'label'=>'โทรศัพท์',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุหมายเลขโทรศัพท์',
							'value'=>$data->phone,
						)
					);
	$form->addField(
						'email',
						array(
							'type'=>'text',
							'label'=>'อีเมล์',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุอีเมล์',
							'value'=>$data->email,
						)
					);
	$form->addField(
						'save',
						array(
							'type'=>'button',
							'name'=>'save',
							'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
						)
					);

	/*
	$form->addField('title',array('type'=>'text','label'=>'ชื่อแนวคิดที่จะขอสนับสนุนทุน','class'=>'-fill','require'=>true,'placeholder'=>'ระบุชื่อแนวคิดที่จะขอสนับสนุนทุน','value'=>$data->title));
	$form->addField('problem',array('type'=>'textarea','label'=>'ความเป็นมาและสถานการณ์','class'=>'-fill','require'=>true,'placeholder'=>'อธิบายความเป็นมาและสถานการณ์ที่ต้องการแก้ปัญหา'));
	$form->addField('activity',array('type'=>'textarea','label'=>'กิจกรรมหลักที่จะดำเนินโครงการ','class'=>'-fill','require'=>true,'placeholder'=>'บรรยายกิจกรรมหลักที่จะดำเนินโครงการ'));
	$form->addField('name',array('type'=>'text','label'=>'ชื่อผู้ขอทุน','class'=>'-fill','require'=>true,'placeholder'=>'ระบุชื่อ-นามสกุลผู้ขอทุน'));
	$form->addField('orgname',array('type'=>'text','label'=>'ชื่อหน่วยงาน','class'=>'-fill','require'=>true,'placeholder'=>'ระบุชื่อหน่วยงาน'));
	$form->addField('phone',array('type'=>'text','label'=>'โทรศัพท์','class'=>'-fill','require'=>true,'placeholder'=>'ระบุหมายเลขโทรศัพท์'));
	$form->addField('email',array('type'=>'text','label'=>'อีเมล์','class'=>'-fill','require'=>true,'placeholder'=>'ระบุอีเมล์'));
	$form->addField('save',array('type'=>'button','name'=>'save','value'=>'<i class="icon -save -white"></i><span>เสนอโครงร่าง/แนวคิด</span>'));
*/
	$ret=$form->build();
	return $ret;
}
?>