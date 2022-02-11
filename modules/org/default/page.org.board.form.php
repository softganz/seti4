<?php
/**
* Project :: Fund Board Add/Edit
* Created 2018-10-04
* Modify  2020-06-10
*
* @param Object $self
* @param Object $fundInfo
* @param Int $tranId
* @return String
*
* @call project/fund/$orgId/board.new[/$tranId]
*/

$debug = true;

function org_board_form($self, $orgInfo, $tranId = NULL) {
	// Data Model
	if (!($orgId = $orgInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $orgInfo->is->editable)) return message('error', 'Access Denied');

	// Edit Old Board
	$data = new stdClass();

	if ($tranId) {
		$data = mydb::select(
			'SELECT * FROM %org_board% WHERE `brdid` = :brdid AND `orgid` = :orgid LIMIT 1',
			':brdid', $tranId,
			':orgid', $orgId
		);
	}

	$data->datein = sg_date(SG\getFirst($data->datein,date('Y-m-d')),'d/m/Y');

	if (empty($data->datedue)) {
		$stmt = 'SELECT MAX(`datedue`) `datedue` FROM %org_board% WHERE `orgid` = :orgid AND `status` = :inboard LIMIT 1';
		$data->datedue = mydb::select($stmt, ':orgid', $orgId, ':inboard', _INBOARD_CODE)->datedue;
	}
	if (empty($data->datedue)) $data->datedue = (date('Y')+4).'-09-30';
	$data->datedue = sg_date($data->datedue,'d/m/Y');

	// OptGroup
	$stmt = 'SELECT
		p.`catid` `position`, p.`name` `positionName`
		, p.`catparent`, p.`process`
		FROM %tag% p
		WHERE p.`taggroup` = "board:position" AND p.`process` IS NOT NULL
		ORDER BY p.`catparent`,p.`weight`';

	$boardList = array('' => '==เลือกตำแหน่ง==');
	foreach ($dbs = mydb::select($stmt)->items as $rs) {
		if ($rs->process > 1) {
			for ($i = 1; $i <= $rs->process; $i++) {
				$boardList[$rs->position.':'.$i] = $rs->positionName.' คนที่  '.$i;
			}
		} else {
			$boardList[$rs->position] = $rs->positionName;
		}
	}

// debugMsg($dbs, '$dbs');
// debugMsg(mydb()->_query);


	// View Model
	$ret = '';

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.($data->brdid?'แก้ไขข้อมูลกรรมการ':'เพิ่มกรรมการบริหาร').'</h3></header>';

	$form = new Form(NULL, url('org/info/api/'.$orgId.'/board.save'), 'org-board-form','sg-form -org-board-form');
	$form->addData('checkValid', true);
	$form->addData('rel','notify');
	$form->addData('done', 'close | load');


	if ($data->brdid) $form->addField('id', array('type' => 'hidden', 'value' => $data->brdid));

	$form->addField(
		'position',
		array(
			'label' => 'ตำแหน่ง:',
			'type' => 'select',
			'class' => '-fill',
			'require' => true,
			'options' => $boardList,
			// 'value' => $data->position.':'.$data->posno,
			'value' => $data->position,
		)
	);

	$form->addField(
		'prename',
		array(
			'type' => 'text',
			'label' => 'คำนำหน้า',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->prename),
			'placeholder' => 'เช่น นาย',
		)
	);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อ-นามสกุล',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->name),
			'placeholder' => 'เช่น สมชาย สกุลดี',
		)
	);

	// $form->addField(
	// 	'fromorg',
	// 	array(
	// 		'type' => 'text',
	// 		'label' => 'ชื่อหน่วยงานต้นสังกัด',
	// 		'class' => '-fill',
	// 		'require' => true,
	// 		'value' => htmlspecialchars($data->fromorg),
	// 		'placeholder' => 'ระบุชื่อหน่วยงานต้นสังกัด',
	// 	)
	// );

	$form->addField(
		'datein',
		array(
			'type' => 'text',
			'label' => 'วันที่เริ่มดำรงตำแหน่ง',
			'class' => 'sg-datepicker -fill',
			'require' => true,
			'autocomplete' => 'off',
			'value' => htmlspecialchars($data->datein),
			'placeholder' => 'รูปแบบ dd/mm/yy',
		)
	);

	$form->addField(
		'datedue',
		array(
			'type' => 'text',
			'label' => 'วันที่ครบวาระ',
			'class' => 'sg-datepicker -fill',
			'require' => true,
			'autocomplete' => 'off',
			'value' => htmlspecialchars($data->datedue),
			'placeholder' => 'รูปแบบ dd/mm/yy',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>'.($data->brdid?'บันทึกการแก้ไข':'บันทึกกรรมการใหม่').'</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/fund/'.$orgId.'/board').'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>