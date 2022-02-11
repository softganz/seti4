<?php
/**
* Project Action Join Setting
* Created 2019-05-15
* Modify  2019-07-30
*
* @param Object $self
* @param Int $tpid
* @param Int $calId
* @return String
*/

$debug = true;

function project_join_setting($self, $tpid, $calId) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');
	$tpid = $projectInfo->tpid;

	$isWebAdmin = user_access('access administrator pages');
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isAdmin) return message('error', 'access denied');

	$calendarInfo = is_object($calId) ? $calId : R::Model('project.calendar.get', $calId);
	$calId = $projectInfo->calid = $calendarInfo->calid;

	$doingInfo = R::Model('org.doing.get', array('calid' => $calId),'{data: "info"}');


	R::View('project.toolbar', $self, $calendarInfo->title, 'join', $projectInfo);

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/join.setting/'.$calId).'" data-rel="#main">ใบสำคัญรับเงิน</a>');
	$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/join.setting.register/'.$calId).'" data-rel="#main">การลงทะเบียนล่วงหน้า</a>');
	$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/join.setting.group/'.$calId).'" data-rel="#main">การเบิกจ่าย</a>');
	$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/join.setting.distance/'.$calId).'" data-rel="#main">กำหนดระยะทาง</a>');

	if ($isWebAdmin) {
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/join.setting.options/'.$calId).'" data-rel="#main">Options</a>');
	}

	$self->theme->sidebar = '<h3>SETTING</h3>'.$ui->build();

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')=='inline') $inlineAttr['data-debug'] = 'yes';
	}
	$ret.='<div id="project-join-setting" '.sg_implode_attr($inlineAttr).'>'._NL;


	$ret .= '<h3>ใบสำคัญรับเงิน</h3>';

	$ret .= view::inlineedit(
					array('group' => 'project', 'fld' => 'agrno', 'tr' => $tpid, 'class' => '-fill', 'label'=>'เลขที่ข้อตกลง'),
					$projectInfo->info->agrno,
					$isEdit
				);

	$ret .= view::inlineedit(
					array('group' => 'doings', 'fld' => 'paiddocfrom', 'tr' => $doingInfo->doid, 'class' => '-fill', 'label'=>'ได้รับเงินจาก'),
					$doingInfo->paiddocfrom,
					$isEdit
				);

	$ret .= view::inlineedit(
					array('group' => 'doings', 'fld' => 'paiddoctagid', 'tr' => $doingInfo->doid, 'class' => '-fill', 'label'=>'เลขประจำตัวผู้เสียภาษี', 'options' => '{maxlength: 13}'),
					$doingInfo->paiddoctagid,
					$isEdit
				);

	$ret .= view::inlineedit(
					array('group' => 'doings', 'fld' => 'paiddocbyname', 'tr' => $doingInfo->doid, 'class' => '-fill', 'label' => 'ผู้จ่ายเงิน'),
					$doingInfo->paiddocbyname,
					$isEdit
				);

	$ret .= view::inlineedit(
					array('group' => 'doings', 'fld' => 'paiddocdate', 'tr' => $doingInfo->doid, 'class' => '', 'value'=>sg_date($doingInfo->paiddocdate,'d/m/Y'), 'ret'=>'date:ว ดดด ปปปป', 'label' => 'วันที่จ่ายเงิน'),
					$doingInfo->paiddocdate,
					$isEdit,
					'datepicker'
				);


	$ret .= view::inlineedit(
					array(
						'label'=>'รหัสอำเภอจัดกิจกรรม (สำหรับคำนวณระยะทางในการเบิกค่าเดินทาง)',
						'group' => 'doings',
						'fld' => 'areacode',
						'tr' => $doingInfo->doid,
						'areacode' => $doingInfo->areacode,
						'options' => '{
							class: "-fill",
							autocomplete: {
								target: "areacode",
								query: "'.url('api/address').'",
								minlength: 5
							}
						}',
					),
					$doingInfo->doChangwat ? ($doingInfo->doTambon ? 'ต.'.$doingInfo->doTambon : '').' อ.'.$doingInfo->doAmpur.' จ.'.$doingInfo->doChangwat : '',
					$isEdit,
					'autocomplete'
				);

	$ret .= 'กลุ่มเจ้าหน้าที่เอกสารการเงิน';
	$ret .= 'กลุ่มเจ้าหน้าที่การเงิน';

	$ret .= '<style type="text/css">
	.inline-edit-item>.inline-edit-label {font-weight: bold; display: block;}
	</style>';

	$ret .= '</div><!-- setting -->';

	//$ret .= print_o($calendarInfo, '$calendarInfo');
	//$ret .= print_o($projectInfo);
	//$ret .= print_o($doingInfo,'$doingInfo');
	return $ret;
}
?>