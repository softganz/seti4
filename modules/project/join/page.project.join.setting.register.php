<?php
/**
* Project Action Join Setting
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calid
* @return String
*/

$debug = true;

function project_join_setting_register($self, $tpid, $calid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');
	$tpid = $projectInfo->tpid;

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isAdmin) return message('error', 'access denied');



	$doingInfo = R::Model('org.doing.get', array('calid' => $calid), '{data: "info"}');

	$inlineAttr['class'] = 'sg-inline-edit';
	$inlineAttr['data-tpid'] = $tpid;
	$inlineAttr['data-update-url'] = url('project/edit/tr');
	if (post('debug')=='inline') $inlineAttr['data-debug'] = 'yes';

	$ret.='<div id="project-join-setting" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<h3>การลงทะเบียนล่วงหน้า</h3>'
		. view::inlineedit(
				array('group' => 'doings', 'fld' => 'isregister', 'tr' => $doingInfo->doid, 'class' => '-fill', 'value'=>$doingInfo->isregister, 'blank'=>'NULL'),
				':ไม่เปิดให้ลงทะเบียนล่วงหน้า',
				$isEdit,
				'radio'
			).'<br />';

	$ret .= view::inlineedit(
		array('group' => 'doings', 'fld' => 'isregister', 'tr' => $doingInfo->doid, 'class' => '-fill', 'value'=>$doingInfo->isregister, 'blank'=>'NULL'),
			'1:เปิดให้เฉพาะเจ้าของโครงการลงทะเบียนล่วงหน้า',
			$isEdit,
			'radio'
		).'<br />';

	$ret .= view::inlineedit(
			array('group' => 'doings', 'fld' => 'isregister', 'tr' => $doingInfo->doid, 'class' => '-fill', 'value'=>$doingInfo->isregister, 'blank'=>'NULL'),
			'2:เปิดให้เฉพาะสมาชิกของโครงการลงทะเบียนล่วงหน้า',
			$isEdit,
			'radio'
		).'<br />';

	$ret .= view::inlineedit(
			array('group' => 'doings', 'fld' => 'isregister', 'tr' => $doingInfo->doid, 'class' => '-fill', 'value'=>$doingInfo->isregister, 'blank'=>'NULL'),
			'8:เปิดให้สมาชิกทั่วไปสามารถลงทะเบียนล่วงหน้า',
			$isEdit,
			'radio'
		).'<br />';

	$ret .= view::inlineedit(
			array('group' => 'doings', 'fld' => 'isregister', 'tr' => $doingInfo->doid, 'class' => '-fill', 'value'=>$doingInfo->isregister, 'blank'=>'NULL'),
			'9:เปิดให้บุคคลทั่วไปสามารถลงทะเบียนล่วงหน้า',
			$isEdit,
			'radio'
		).'<br />';

	$ret .= view::inlineedit(
			array('label' => 'ลงทะเบียนตั้งแต่วันที่', 'group' => 'doings', 'fld' => 'registstart', 'tr' => $doingInfo->doid, 'class' => '', 'ret'=>'date:ว ดดด ปปปป', 'convert'=>'U', 'blank'=>'NULL'),
			$doingInfo->registstart,
			$isEdit,
			'datepicker'
		)
		. ' ถึง '
		. view::inlineedit(
			array('group' => 'doings', 'fld' => 'registend', 'tr' => $doingInfo->doid, 'class' => '', 'ret'=>'date:ว ดดด ปปปป', 'convert'=>'U', 'blank'=>'NULL'),
			$doingInfo->registend,
			$isEdit,
			'datepicker'
		);

	$ret .= view::inlineedit(
			array('group' => 'doings', 'fld' => 'registerrem', 'tr' => $doingInfo->doid, 'class' => '-fill', 'label' => 'หมายเหตุท้ายใบลงทะเบียน', 'ret' => 'html'),
			$doingInfo->registerrem,
			$isEdit,
			'textarea'
		);

	$ret .= '<style type="text/css">
	.inline-edit-item>.inline-edit-label {font-weight: bold; display: block;}
	</style>';

	$ret .= '</div><!-- setting -->';

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo);
	return $ret;
}
?>