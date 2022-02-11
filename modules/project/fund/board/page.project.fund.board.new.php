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

function project_fund_board_new($self, $fundInfo, $tranId = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');
	if (!($isEdit = $fundInfo->right->edit)) return message('error', 'Access Denied');

	$ret = '';

	//$ret .= 'Org ID = '.$orgId.' , Action = '.$action.' , TranId = '.$tranId.'<br />'.print_o($fundInfo,'$fundInfo');


	// Edit Old Board
	if ($tranId) {
		$data = mydb::select('SELECT * FROM %org_board% WHERE `brdid` = :brdid AND `orgid` = :orgid LIMIT 1', ':brdid', $tranId, ':orgid', $orgId);
	}


	$data->datein = sg_date(SG\getFirst($data->datein,date('Y-m-d')),'d/m/Y');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.($data->brdid?'แก้ไขข้อมูลกรรมการ':'เพิ่มกรรมการบริหาร').'</h3></header>';

	$form = new Form(NULL, url('project/fund/'.$orgId.'/info/board.save'),'project-board-form','sg-form -project-board-form');
	$form->addData('checkValid', true);
	$form->addData('rel','notify');
	$form->addData('done', 'back | load:.box-page');


	if ($data->brdid) $form->addField('id', array('type' => 'hidden', 'value' => $data->brdid));

	if (empty($data->datedue)) {
		$stmt = 'SELECT MAX(`datedue`) `datedue` FROM %org_board% WHERE `orgid` = :orgid AND `status` = :inboard LIMIT 1';
		$data->datedue = mydb::select($stmt, ':orgid', $orgId, ':inboard', _INBOARD_CODE)->datedue;
	}
	if (empty($data->datedue)) $data->datedue = (date('Y')+4).'-09-30';
	$data->datedue = sg_date($data->datedue,'d/m/Y');


	// OptGroup
	$stmt = 'SELECT t.`catid` `board`, t.`name` `boardName`
		, p.`catid` `position`, p.`name` `positionName`
		, p.`catparent`, p.`process`
		FROM %tag% p
			LEFT JOIN %tag% t ON t.`catid`=p.`catparent` AND t.`taggroup`="project:board"
		WHERE p.`taggroup`="project:boardpos" AND p.`process` IS NOT NULL
		ORDER BY p.`catparent`,p.`weight`';

	$sdbs = mydb::select($stmt);

	$boardList[] = '==เลือกตำแหน่ง/องค์ประกอบของคณะกรรมการ==';
	foreach ($sdbs->items as $rs) {
		if ($rs->process > 1) {
			for ($i = 1; $i <= $rs->process; $i++) {
				$boardList[$rs->boardName][$rs->position.':'.$i] = $rs->positionName.' คนที่  '.$i;
			}
		} else {
			$boardList[$rs->boardName][$rs->position] = $rs->positionName;
		}
	}
	//$ret.=print_o($boardList,'$boardList');

	$form->addField(
		'position',
		array(
			'label' => 'ตำแหน่ง/องค์ประกอบของคณะกรรมการ:',
			'type' => 'select',
			'class' => '-fill',
			'require' => true,
			'options' => $boardList,
			'value' => $data->position.':'.$data->posno,
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

	$form->addField(
		'fromorg',
		array(
			'type' => 'text',
			'label' => 'ชื่อหน่วยงานต้นสังกัด',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->fromorg),
			'placeholder' => 'ระบุชื่อหน่วยงานต้นสังกัด',
		)
	);

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
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>