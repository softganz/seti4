<?php
function org_mapping_subject($self, $orgId = NULL, $mapId = NULL, $action = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	$ret = '';

	$isEdit = $orgInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_OFFICER) && $action == 'edit';

	$ret.='<div id="org-subject">';
	$stmt = 'SELECT b.*, t.`name`
					FROM %bigdata% b
						LEFT JOIN %tag% t ON t.`taggroup`="subject" AND t.`catid` = b.`flddata`
					WHERE b.`keyname` = "map" AND `keyid` = :tranId AND `fldname`  = "subject"
					ORDER BY `weight`';

	$dbs = mydb::select($stmt,':tranId',$mapId);
	//$ret .= print_o($dbs);

	$exceptSubject = array(0);

	$ui = new Ui(NULL, 'ui-tag');
	//$ret .= '<ul id="org-subject" class="org-subject">';
	foreach ($dbs->items as $rs) {
		$exceptSubject[] = $rs->flddata;
		$menu = $isEdit ? '<a class="sg-action -hover" href="'.url('org/'.$orgId.'/mapping/subject.delete/'.$rs->bigid).'" data-rel="replace:.org-subject" data-ret="'.url('org/'.$orgId.'/mapping.subject/'.$mapId.'/edit').'" data-removeparent="li" data-confirm="ลบรายการนี้ กรุณายืนยัน?"><i class="icon -remove -gray"></i></a>' : '';
		/*
		$ret .= '<li class="ui-item -hover-parent">'
				. $rs->name
				. $menu
				. '</li>';
				*/
		$ui->add($rs->name.$menu, '{class: "-hover-parent"}');
	}

	//if ($isEdit) $ret .= '<li>'.__org_subject_add($orgInfo, $mapId, implode(',', $exceptSubject)).'</li>';
	//if ($isEdit) $ret.='<li><a class="sg-action" href="'.url('org/'.$orgInfo->orgid.'/subject/add').'" data-rel="box" data-width="300"><i class="icon -add"></i></a></li>';
	//$ret .= '</ul>';


	$ret .= $ui->build();
	if ($isEdit) $ret .= __org_subject_add($orgInfo, $mapId, implode(',', $exceptSubject));

	//$ret.=print_o($orgInfo,'$orgInfo');
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div><!-- org-subject -->';
	return $ret;
}

function __org_subject_add($orgInfo, $mapId, $exceptSubject = 0) {
	// Show subject form
	$orgSubjectList = R::Model(
			'category.get',
			'subject',
			'catid',
			(object) array(
				'result'=>'group',
				'condition'=>'tg.`catid` NOT IN ('.($exceptSubject).')',
				'order'=>'IF(tg.`catparent` IS NULL, tg.`weight`, CONVERT(tg.`name` USING tis620)) ASC',
			)
		);

	$form = new Form(NULL, url('org/'.$orgInfo->orgid.'/mapping/subject.add/'.$mapId),'org-subject-edit','sg-form -inlineitem');
	$form->addData('checkValid',true);
	$form->addData('rel','replace:#org-subject');
	$form->addData('ret',url('org/'.$orgInfo->orgid.'/mapping.subject/'.$mapId.'/edit'));

	$form->addField(
					'subject',
					array(
						//'label' => 'ประเด็นการทำงานขององค์กร:',
						'type' => 'select',
						'class' => '-fill',
						'options' => array('== เพิ่มประเด็นการทำงาน ==')+$orgSubjectList,
						'attr' => array('onChange'=>'$(this).closest(\'form\').submit()'),
						'container' => array('class'=>'-inline'),
					)
				);
	/*
	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -add"></i><span class="-hidden">{tr:SAVE}</span>',
						'class' => '-link -circle24',
						//'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close">ยกเลิก</a>',
						'container' => array('class'=>'-inline'),
					)
				);
				*/

	$ret .= $form->build();

	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>