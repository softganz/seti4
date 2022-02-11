<?php
/**
* Project Action Join View/Edit by CID/Phone
* Created 2019-02-20
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param String $refcode
* @return String
*/

$debug = true;

function project_join_modify($self, $projectInfo, $refcode = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$debug = false; //i()->username == 'softganz';

	$url = 'project/join/'.$tpid.'/'.$calId.'/modify';

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	$authCID = post('cid');
	$authPhone = post('phone');
	$sessionCID = $_SESSION['auth.join.cid'];
	$sessionPhone = $_SESSION['auth.join.phone'];

	if (post('clear')) {
		unset($_SESSION['auth.join.cid']);
		unset($_SESSION['auth.join.phone']);
	} else if ($authCID) {
		$joinInfo = R::Model('project.join.get', array('calid' => $calId, 'cid' => $authCID), '{debug: '.($debug?'true':'false').'}');

		if ($debug) $ret .= 'SESSION AUTH CHECK '.$authCID. ' '.$authPhone.'<br />';
		//if (i()->username=='softganz') $ret .= print_o($joinInfo, '$joinInfo');

		if ($joinInfo) {
			if ($debug) $ret .= 'SESSION AUTH CHECK OK<br />';
			$isRefAuth = true;
			$_SESSION['auth.join.cid'] = $joinInfo->cid;
			$_SESSION['auth.join.phone'] = $joinInfo->phone;
		} else {
			if ($debug) $ret .= 'SESSION AUTH CHECK FAIL<br />';
			$isRefAuth = false;
			unset($_SESSION['auth.join.cid']);
			unset($_SESSION['auth.join.phone']);
			unset($joinInfo);
			$ret .= message('error', 'ไม่มีข้อมูลการลงทะเบียนตามที่ระบุ : เลขประจำบัตรประชาชนที่ระบุอาจจะยังไม่ได้ลงทะเบียนหรือระบุเลขประจำบัตรประชาชนผิดพลาด');
		}
	} else if ($sessionCID) {
		$joinInfo = R::Model('project.join.get', array('calid' => $calId, 'cid' => $sessionCID));
		if ($debug) $ret .= 'SESSION WAS SET<br />';
		$isRefAuth = $joinInfo ? true : false;
	} else {
		if ($debug) $ret .= 'NO SESSION AUTH<br />';
		$isRefAuth = false;
	}


	if ($debug) $ret .= 'Ref Code = '.$refcode.' auth.cid = '.$_SESSION['auth.join.cid'].' auth.phone = '.$_SESSION['auth.join.phone'].'<br />';
	if ($debug) $ret .= 'Ref Code = '.$refcode.' auth.refcode = '.$_SESSION['auth.join.refcode'].'<br />';
	//$ret .= print_o($_POST,'$_POST');
	if ($sebug) $ret .= print_o($_SESSION, '$_SESSION');

	//if (!($isEdit || (i()->ok && $joinInfo->uid == i()->uid) || $isRefAuth))
	if (!$isRefAuth)
		return $ret.__auth_form($projectInfo->calendarInfo);

	if (empty($joinInfo))
		return $ret.message('error', 'ERROR : ไม่มีข้อมูลการลงทะเบียนตามที่ระบุ');


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
			$linkUrl = url('project/join/'.$joinInfo->tpid.'/'.$joinInfo->calid.'/modify');
			$ret .= '<p class="notify">ท่านได้ลงทะเบียนเรียบร้อยแล้ว สำหรับการแก้ไขข้อมูลการลงทะเบียนในภายหลัง ได้ที่เว็ปไซต์ ( <a href="'._DOMAIN.$linkUrl.'">'._DOMAIN.'</a>) หรือใช้โทรศัพท์สแกน QR CODE ด้านบน </p>';

			break;
	}

	//$ret .= R::View('project.join.register.form', $data, '{mode: "'.$action.'"}');
	if ($debug) $ret .= print_o($joinInfo, '$joinInfo');

	return $ret;
}

function __auth_form($calendarInfo) {
	$ret = '<h2>แก้ไขรายละเอียดการลงทะเบียน</h2>';
	$form = new Form(NULL, url('project/join/'.$calendarInfo->tpid.'/'.$calendarInfo->calid.'/modify'));

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'ป้อนเลขประจำบัตรประชาชน (ที่ได้ลงทะเบียนไว้แล้ว)',
			'autocomplete' => 'off',
			'maxlength' => 13,
		)
	);
	/*
	$form->addField('phone',
		array(
			'type' => 'text',
			'label' => 'ป้อนเบอร์โทรศัพท์มือถือ (ที่ได้ลงทะเบียนไว้แล้ว)',
			'autocomplete' => 'off',
			'maxlength' => 13,
		)
	);
	*/
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