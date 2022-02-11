<?php
/**
* Project Action Join View/Edit by Ref Code
* Created 2019-02-20
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param String $refcode
* @return String
*/

function project_join_ref($self, $projectInfo, $refcode = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$joinInfo = R::Model('project.join.get', array('refcode' => $refcode, 'calid' => $calId));

	$url = 'project/join/'.$tpid.'/'.$calId.'/ref/'.$refcode;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	if (post('refcode') === $joinInfo->refcode && post('cid') === $joinInfo->cid) {
		$isRefAuth = true;
		$_SESSION['auth.join.refcode'] = $refcode;
	} else if ($_SESSION['auth.join.refcode'] == $refcode) {
		$isRefAuth = true;
	} else {
		$isRefAuth = false;
	}


	//$ret .= 'Ref Code = '.$refcode.' auth = '.$_SESSION['auth.join.refcode'].'<br />';
	//$ret .= print_o($_POST,'$_POST');
	//$ret .= print_o($_SESSION, '$_SESSION');

	if (empty($joinInfo))
		return message('error', 'ERROR : ไม่มีข้อมูลการลงทะเบียนตามที่ระบุ');

	if (!($isEdit || (i()->ok && $joinInfo->uid == i()->uid) || $isRefAuth))
		return __auth_form($joinInfo);

	/*
	ตรวจสอบสิทธิ์ หากเป็น Editanble หรือ ป้อน CID
	ถามก่อนว่าจะทำอะไร เช่น ดู แก้ไข
	*/
	//$ret .= '<div class="btn-floating -right-bottom -no-print"><a href="'.url($url, array('do' => 'view')).'"><i class="icon -viewdoc"></i><span class="-hidden">ดูรายละเอียด</span></a>';
	//$ret .= '<a href="'.url('project/join/'.$tpid.'/'.$calId.'/ref/'.$refcode, array('do' => 'edit')).'"><i class="icon -edit"></i><span class="-hidden">แก้ไขรายละเอียด</span></a>';
	//$ret .= '</div>';

	$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url($url).'"><i class="icon -viewdoc -white"></i></a><a class="sg-action btn -floating -circle48" href="'.url($url, array('do' => 'edit')).'"><i class="icon -edit -white"></i></a></div>';

	switch (post('do')) {
		case 'edit':
			$ret .= R::View('project.join.register.form', $joinInfo, '{mode: "edit"}');
			break;
		
		default:
			$ret .= R::View('project.join.register.form', $joinInfo, '{mode: "view"}');
			break;
	}

	//$ret .= R::View('project.join.register.form', $data, '{mode: "'.$action.'"}');
	//$ret .= print_o($joinInfo);

	return $ret;
}

function __auth_form($joinInfo) {
	$form = new Form(NULL, url('project/join/'.$joinInfo->tpid.'/'.$joinInfo->calid.'/ref/'.$joinInfo->refcode));

	$form->addField(
		'refcode',
		array(
			'type' => 'text',
			'label' => 'Ref. Code',
			'readonly' => true,
			'class' => '-disabled',
			'value' => $joinInfo->refcode,
		)
	);

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'ป้อนเลขประจำบัตรประชาชน (ที่ได้ลงทะเบียนไว้แล้ว)',
			'autocomplete' => 'off',
			'maxlength' => 13,
		)
	);

	$form->addField(
		'login',
		array(
			'type' => 'button',
			'value' => '<i class="icon -signin -white"></i><span>เข้าสู่ระบบสมาชิก</span>',
		)
	);

	$ret .= $form->build();
	return $ret;
}
?>