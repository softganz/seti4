<?php
function school_dashboard_delete($self,$orgid) {
	$schoolInfo=R::Model('school.get',$orgid);

	R::View('school.toolbar',$self,'Person : '.$schoolInfo->name,NULL,$schoolInfo);

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);


	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	if (!$isEditable) return message('error','access denied');

	if (SG\confirm()) {
		$stmt='DELETE FROM %school% WHERE `orgid`=:orgid LIMIT 1';
		mydb::query($stmt,':orgid',$orgid);
		location('school/my');
	}

	$ret.='<h2>ลบโรงเรียนออกจากระบบ</h2>';
	$ret.='<p>ต้องการลบโรงเรียน '.$schoolInfo->name.' จริงหรือไม่?</p>';

	$form=new Form(NULL,url('school/dashboard/delete/'.$orgid),NULL,'sg-form');
	$form->addData('checkValid',true);
	$form->addConfig('method', 'GET');

	$form->addField(
						'confirm',
						array(
							'type'=>'checkbox',
							'require'=>true,
							'options'=>array('yes'=>'ยืนยันการลบโรงเรียน '.$schoolInfo->name)
							)
						);
	$form->addField(
				'submit',
				array(
					'type'=>'button',
					'name'=>'',
					'value'=>'<i class="icon -delete -white"></i><span>ลบโรงเรียน</span>',
					)
				);
	$ret.=$form->build();

	return $ret;
}
?>