<?php
/**
* Project Join Recieve
* Created 2019-02-21
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_addrcvtr($self, $projectInfo, $dopid = NULL, $data = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$post = (object) post('dopaidtr');
	if ($post->catid) {
		if (empty($post->doptrid)) $post->doptrid = NULL;
		$post->dopid = $dopid;
		$post->amt = sg_strip_money($post->amt);
		$stmt = 'INSERT INTO %org_dopaidtr%
			(`doptrid`, `dopid`, `catid`, `detail`, `amt`)
			VALUES
			(:doptrid, :dopid, :catid, :detail, :amt)
			ON DUPLICATE KEY UPDATE
			`catid` = :catid
			, `detail` = :detail
			, `amt` = :amt
			';
		mydb::query($stmt, $post);
		//$ret.=mydb()->_query;

		R::Model('org.dopaid.update.total', $dopid);
		//$ret.=print_o($post,'$post');
		$ret .= R::Page('project.join', NULL, $tpid, $calId, 'rcv', $dopid);
		return $ret;
	}

	if ($data) $post = (object) $data;

	$form = new Form('dopaidtr', url('project/join/'.$tpid.'/'.$calId.'/addrcvtr/'.$dopid), NULL, 'sg-form project-dopaidtr-form -sg-text-left');
	$form->addData('rel', 'replace:#project-rcv-wrapper');

	$form->addConfig('title', ($post->doptrid ? 'แก้ไข' : 'เพิ่ม').'รายการจ่าย');

	$form->addField('doptrid', array('type' => 'hidden', 'value' => $post->doptrid));

	$expCategoty = model::get_category_by_group('project:expcode', 'catid');
	$expCategoty = R::Model('project.expense.code.get', NULL, NULL, '{resultType: "select"}');
	//$ret.=print_o($expCategoty,'$expCategoty');

	$form->addField(
		'catid',
		array(
			'type' => 'select',
			'label' => 'รายการค่าใช้จ่าย:',
			'class' => '-fill',
			'options' => $expCategoty,
			'value' => $post->catid,
		)
	);
	$form->addField(
		'detail',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียดค่าใช้จ่าย',
			'class' => '-fill',
			'rows' => 1,
			'value' => $post->detail,
		)
	);
	$form->addField(
		'amt',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน',
			'class' => '-money -fill',
			'placeholder' => '0.00',
			'autocomplete' => 'off',
			'value' => htmlspecialchars($post->amt),
		)
	);
	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกรายการจ่าย</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$dopid).'" data-rel="replace:#project-rcv-wrapper"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'containerclass' => '-sg-text-right',
		)
	);
	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.project-objective-form {margin:32px 16px; text-align:left; box-shadow:0 0 0 1px #eee inset;border-radius:4px;}
	.project-objective-form h3.title {border-radius:4px 4px 0 0;font-size:1.2em;background:#e5e5e5;color:#666;text-align:center;}
	.project-objective-form .form-item {padding:8px;}
	</style>';

	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>