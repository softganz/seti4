<?php
function project_money_dopaidaddtr($self, $projectInfo, $dopid) {
	$tpid = $projectInfo->tpid;
	$dopaidInfo = R::Model('org.dopaid.doc.get', $dopid);

	$isEdit = true;

	$post = (object) post('dopaidtr');
	if ($post->catid) {
		$post->dopid = $dopid;
		$post->amt = sg_strip_money($post->amt);
		$stmt = 'INSERT INTO %org_dopaidtr% (`dopid`, `catid`, `detail`, `amt`) VALUES (:dopid, :catid, :detail, :amt)';
		mydb::query($stmt, $post);

		$stmt = 'UPDATE %org_dopaid% d
					INNER JOIN (SELECT `dopid`, SUM(`amt`) `totalAmt` FROM %org_dopaidtr% tr WHERE tr.`dopid` = :dopid) b ON b.`dopid` = d.`dopid`
					SET d.`total` = b.`totalAmt`
					WHERE d.`dopid` = :dopid';
		mydb::query($stmt, ':dopid',$dopid);
		//$ret.=mydb()->_query;
		//$ret.=print_o($post,'$post');
		$ret .= R::Page('project.money', NULL, $tpid, 'dopaidview', $dopid);
		return $ret;
	}

	$form = new Form('dopaidtr', url('project/money/'.$tpid.'/dopaidaddtr/'.$dopid), NULL, 'sg-form project-dopaidtr-form -sg-text-left');
	$form->addData('rel', '#main');

	$form->addConfig('title', 'เพิ่มรายการจ่าย');

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
							'rows' => 3,
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
					'pretext' => '<a class="sg-action" href="'.url('project/money/'.$tpid.'/dopaidview/'.$dopid).'" data-rel="#main">{tr:Cancel}</a>',
					'containerclass' => '-sg-text-right',
					)
				);
	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.project-objective-form {margin:32px 16px; text-align:left; box-shadow:0 0 0 1px #eee inset;border-radius:4px;}
	.project-objective-form h3.title {border-radius:4px 4px 0 0;font-size:1.2em;background:#e5e5e5;color:#666;text-align:center;}
	.project-objective-form .form-item {padding:8px;}
	</style>';

	//$ret.=print_o($dopaidInfo,'$dopaidInfo');
	return $ret;
}
?>