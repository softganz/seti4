<?php
/**
* Project :: View/Create form
* Created 2020-01-01
* Modify  2021-01-17
*
* @param Object $data
* @return String
*
* @usage R::View("project.admin.project.form")
*/

$debug = true;

function view_project_admin_project_form($data) {
	R::View('project.toolbar',$self,'Create new project','admin');
	$self->theme->sidebar = R::View('project.admin.menu','follow');

	$property = property('project');


	$form = new Form('data',url('project/admin/follow/create'),'project-new','project-new');
	$form->addClass('sg-form');
	$form->addData('checkValid',true);

	$form->addConfig('title','รายละเอียดแผนงาน/ชุดโครงการ/โครงการ');

	$stmt = 'SELECT
		  o.`orgid`, o.`name`
		FROM %db_org% o
		ORDER BY
			CASE WHEN `orgid`=1 THEN 0
			ELSE CONVERT(`name` USING tis620)
			END ASC
		';

	$projectSet = mydb::select($stmt);

	$orgOptions = array();
	$projectOptions[0] = '=== เลือกองค์กร ===';
	foreach (mydb::select($stmt)->items as $rs) {
		$orgOptions[$rs->orgid] = $rs->name;
	}

	$form->addField(
		'orgid',
		array(
			'type' => 'select',
			'label' => 'องค์กร:',
			'class' => '-fill',
			'options' => $orgOptions,
			'value' => $data->orgid,
		)
	);

	$form->addField(
		'prtype',
		array(
			'type' => 'select',
			'label' => 'ประเภท:',
			'class' => '-fill',
			'require'=>true,
			'options'=>array('แผนงาน' => 'แผนงาน','ชุดโครงการ' => 'ชุดโครงการ','โครงการ' => 'โครงการ'),
			'value'=>SG\getFirst($data->prtype,'โครงการ'),
		)
	);

	$stmt = 'SELECT
		  t.`tpid`, t.`title`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE (p.`prtype` IN ("แผนงาน","ชุดโครงการ") || p.`ischild` > 0) AND p.`project_status`="กำลังดำเนินโครงการ"
		ORDER BY CONVERT(`title` USING tis620) ASC
		';
	$projectSet = mydb::select($stmt);

	$projectOptions = array();
	$projectOptions[0] = '=== เลือกแผนงาน ===';
	$projectOptions['top'] = 'แผนงานระดับบนสุด';
	$projectOptions['sep'] = '--- ภายใต้ ---';
	foreach ($projectSet->items as $rs) {
		$projectOptions[$rs->tpid] = $rs->title;
	}
	$form->addField(
		'projectset',
		array(
			'type' => 'select',
			'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
			'class' => '-fill',
			'require'=>true,
			'options' => $projectOptions,
			'value' => $data->projectset
		)
	);

	$form->addField(
		'pryear',
		array(
			'type' => 'radio',
			'label' => 'ประจำปี:',
			'class' => '-fill',
			'options'=> array(
				date('Y')-1 => date('Y')-1+543,
				date('Y')+543,
				date('Y')+1+543
			),
			'require' => true,
			'display' => 'inline',
			'value' => SG\getFirst($data->pryear,date('Y'))
		)
	);

	$provinceOption = array();
	$provinceOption[-1] = '--- เลือกจังหวัด ---';

	$stmt = 'SELECT `provid`, `provname`
		FROM %co_province%
		ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs = mydb::select($stmt)->items;

	$provinceOptions = array(
		-1 => '=== เลือกพื้นที่ ===',
		'TH' => '++ ทั้งประเทศ',
		'ระดับภาค' => array(
			1 => '++ ภาคกลาง',
			3 => '++ ภาคตะวันออกเฉียงเหนือ',
			5 => '++ ภาคเหนือ',
			8 => '++ ภาคใต้',
		)
	);

	$stmt = 'SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$provinceOptions['ระดับจังหวัด'][$rs->provid] = $rs->provname;
	}

	$form->addField(
		'changwat',
		array(
			'type' => 'select',
			'label' => 'พื้นที่ดำเนินการ:',
			'class' => '-fill',
			'require'=>true,
			'options' => $provinceOptions,
			'value' => $data->changwat
		)
	);

	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'ชื่อแผนงาน/ชุดโครงการ/โครงการ',
			'class' => '-fill',
			'require'=>true,
			'value' => $data->title,
		)
	);

	$form->addField(
		'fieldname',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:Save}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>