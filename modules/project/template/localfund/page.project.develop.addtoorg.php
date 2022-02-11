<?php
/**
* Set project develop to local fund
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_develop_addtoorg($self, $tpid = NULL) {
	$devInfo = R::Model('project.develop.get', $tpid);
	$orgInfo = R::Model('org.get', $devInfo->info->toorg);
	$isEdit = $orgInfo->RIGHT & _IS_ADMIN;

	R::View('project.toolbar',$self,'ส่งพัฒนาโครงการให้กองทุน','develop', $devInfo);

	if (!$isEdit) return message('error', 'access denied');

	$ret = '';

	if (SG\confirm() && $devInfo->info->toorg) {
		$stmt = 'UPDATE %topic% SET `orgid` = :orgid WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt, ':tpid', $tpid, ':orgid', $devInfo->info->toorg);
		
		$ret .= message('status', 'ส่งพัฒนาโครงการให้กองทุน '.$orgInfo->name.' เรียบร้อย');
		$ret .= R::Page('project.develop.view',NULL,$tpid);
		return $ret;
	}

	$ret .= '<h3 class="title -box">รับโครงการไว้พิจารณา</h3>';


	$form = new Form(NULL, url('project/develop/addtoorg/'.$tpid), NULL, 'sg-form -sg-paddingmore');
	$form->addConfig('title', $devInfo->info->title);
	$form->addData('rel', '#main');
	$form->addData('complete', 'closebox');

	$form->addField('confirm', array('type'=>'hidden','value'=>'yes'));

	$form->addText('กรุณายืนยันการรับโครงการไว้พิจารณา');
	$form->addField('save',
						array(
							'type' => 'button',
							'container' => array('class'=>'-sg-text-right'),
							'value' => '<i class="icon -save -white"></i><span>ยืนยัน</span>',
							'pretext' => '<a class="sg-action btn -link" href="javascript:void(0)" data-rel="close"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></a>',
						)
					);

	$form->addConfig('posttext','หลังจากยืนยันเรียบร้อย พัฒนาโครงการจะถูกนำเข้าสู่องค์กร');
	$ret .= $form->build();

	//$ret .= print_o(post(),'post()');
	return $ret;
}
?>