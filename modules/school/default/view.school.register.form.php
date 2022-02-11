<?php
function view_school_register_form($data = NULL) {
	$ret.='<h2>School Information</h2>';

	$form=new Form('data',url('school/create'),'school-register-form','sg-form school-register-form');
	$form->addData('checkValid',true);

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'ชื่อโรงเรียน',
							'class'=>'-fill',
							'require'=>true,
							)
						);

	$form->addField(
						'address',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่',
							'class'=>'sg-address -fill',
							'require'=>true,
							'attr'=>array('data-altfld'=>'edit-data-areacode'),
							)
						);

	$form->addField(
						'areacode',
						array(
							'type'=>'hidden',
							'label'=>'ที่อยู่',
							'require'=>true,
							)
						);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:Creat New School}</span>',
						)
					);

	$ret.=$form->build();
	return $ret;
}
?>