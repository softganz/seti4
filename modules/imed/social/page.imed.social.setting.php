<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_setting($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	$isEdit = $isAdmin;

	if (!($isAdmin)) return message('error','ขออภัย ท่านไม่ได้สิทธิ์ในการแก้ไข');

	$ui = new Ui();

	if ($isEdit) {
		$ui->add('<a href="'.url('org/'.$orgId).'" target="_blank"><i class="icon -material">edit</i></a>');
	}


	$ret .= '<header class="header -box"><h3>@Group Settings</h3><nav class="nav">'.$ui->build().'</nav></header>';


	$inlineAttr['class'] = 'imed-social-setting';

	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('org/edit/info/'.$doid);
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}
	$ret.='<div id="imed-social-setting" '.sg_implode_attr($inlineAttr).'>';


	$ret .= view::inlineedit(array('group'=>'org', 'fld'=>'name', 'tr'=>$orgInfo->orgid, 'class'=>'-fill', 'label'=>'ชื่อกลุ่ม'),$orgInfo->name,$isEdit);

	$ret .= '<p><b>ที่อยู่</b><br />'.$orgInfo->info->address.'</p>';

	//$ret .= view::inlineedit(array('group'=>'org', 'fld'=>'address', 'tr'=>$orgInfo->orgid, 'class'=>'-fill', 'label'=>'ที่อยู่'),$orgInfo->info->address,false);

	$ret .= view::inlineedit(array('group'=>'org', 'fld'=>'phone', 'tr'=>$orgInfo->orgid, 'class'=>'-fill', 'label'=>'โทรศัพท์'),$orgInfo->info->phone,$isEdit);


	$ret .= '</div>';

	$ret .= '<style>
	.inline-edit-item {margin: 16px 0; display: block; padding: 0;}
	.inline-edit-item label {font-weight: bold;}
	</style>';
	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>