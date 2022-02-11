<?php
/**
* Create project form
* Created 2018-12-29
* Modify  2019-09-03
*
* @param
* @return String
*/

$debug = true;

function view_project_create_form($data) {
	R::View('project.toolbar', $self, 'Create new project');

	$data->parent = SG\getFirst($data->parent, $data->projectset,post('parent'));
	$data->orgid = SG\getFirst($data->orgid,post('orgid'));

	$isAdmin = user_access('administer projects');

	$property = property('project');


	$form = new Form('data', url('project/create',array('abtest' => post('abtest'))), 'project-new', 'project-new');
	$form->addClass('sg-form');
	$form->addData('checkValid', true);

	/*
	$stmt='SELECT
					  o.`orgid`, o.`name`
					FROM %db_org% o
					ORDER BY
						CASE WHEN `orgid`=1 THEN 0
						ELSE CONVERT(`name` USING tis620)
						END ASC
					';
	$projectSet=mydb::select($stmt);

	$orgOptions=array();
	$projectOptions[0]='=== เลือกองค์กร ===';
	foreach (mydb::select($stmt)->items as $rs) {
		$orgOptions[$rs->orgid]=$rs->name;
	}

	$form->addField(
						'orgid',
						array(
							'type'=>'select',
							'label'=>'องค์กร:',
							'options'=>$orgOptions,
							'value'=>$data->orgid,
							)
						);
	*/

	if ($data->orgid) {
		$form->addField(
			'orgid',
			array(
				'type'=>'hidden',
				'label'=>'องค์กร:',
				'value'=>$data->orgid,
			)
		);
	}

	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'ชื่อ{tr:โครงการ}', //tr('Project name'),
			'class' => '-fill',
			'require' => true,
			'value' => $data->title,
		)
	);

	$form->addField(
		'prtype',
		array(
			'type' => $isAdmin ? 'radio' : 'hidden',
			'label' => 'ประเภท:',
			'class' => '-fill',
			'require' => true,
			'options' => $isAdmin ? array(
				'แผนงาน' => 'แผนงาน',
				'ชุดโครงการ' => 'ชุดโครงการ',
				'โครงการ' => 'โครงการ'
			) : array('โครงการ' => 'โครงการ'),
			'value' => SG\getFirst($data->prtype, 'โครงการ'),
		)
	);

	$yearOptions = array();
	for ($i = $data->startyear; $i <= date('Y')+1; $i++) $yearOptions[$i] = $i+543;
	$form->addField(
		'pryear',
		array(
			'type' => count($yearOptions) > 5 ? 'select' : 'radio',
			'label' => 'ประจำปี:',
			'class' => '-fill',
			'options' => $yearOptions,
			'require' => true,
			'display' => 'inline',
			'value' => SG\getFirst($data->pryear, date('Y'))
		)
	);


	if ($data->parent) {
		$form->addField(
			'parent',
			array(
				'type' => 'hidden',
				'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
				'value' => $data->parent
			)
		);
	} else {
		// Select planning
		mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild` > 0');
		//if ($data->orgid) mydb::where('t.`orgid` = :orgid', ':orgid', $data->orgid);

		mydb::value('$JOIN$','');
		if (!$isAdmin && cfg('PROJECT.PROJECT.CREATE_SELECT_PLANNING') == 'ONLY_MEMBERSHIP') {
			mydb::where('(t.`uid` = :uid OR tu.`uid` = :uid)', ':uid',i()->uid);
			mydb::value('$JOIN$', 'LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`');
		}

		$stmt = 'SELECT DISTINCT
			  t.`tpid`, t.`title`, p.`prtype`, t.`parent`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				$JOIN$
			%WHERE%
			ORDER BY CONVERT(`title` USING tis620) ASC
			';

		$projectSet = mydb::select($stmt);
		//if (i()->uid == 10) $ret .= print_o($projectSet, '$projectSet');

		// Create Planning & Proejct Set Tree
		foreach ($projectSet->items as $rs) {
			$treeParent[$rs->tpid] = $rs->parent;
			$treeItems[$rs->tpid] = $rs;
		}
		foreach ($treeParent as $key => $value) {
			//$ret .= 'items of '.$value.'='.$treeParent[$value].'<br />';
			if (!array_key_exists($value, array_keys($treeParent))) {
				$treeParent[$key] = NULL;
				$treeItems[$key]->parent = NULL;
			}
		}
		//$tree[3714] = NULL;
		//$treeItems[3714]->parent = NULL;

		//$ret .= print_o($treeParent,'$treeParent').print_o($treeItems,'$treeItems');

		$planningTree = sg_printTreeTable($treeItems,sg_parseTree($treeItems,$treeParent));

		//if (i()->uid == 10) $ret .= print_o($projectSet, '$projectSet');
		//if (i()->uid == 10) $ret .= print_o($planningTree,'$planningTree');

		$itemsForRadio = 5;
		$projectOptions = array();
		if ($projectSet->_num_rows <= $itemsForRadio) {
			$projectOptions['top'] = 'ไม่มีแผนงาน/ชุดโครงการ';
		} else {
			$projectOptions[0] =  '=== เลือกแผนงาน/ชุดโครงการ ===';
			$projectOptions['top']='ไม่มีแผนงาน/ชุดโครงการ';
			$projectOptions['sep']='--- ภายใต้แผนงาน/ชุดโครงการ ---';
		}
		foreach ($planningTree as $rs) {
			$projectOptions[$rs->tpid] = str_repeat('--',$rs->treeLevel).' '.$rs->title.' ('.$rs->prtype.')';
		}
		$form->addField(
			'parent',
			array(
				'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
				'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
				'class' => '-fill',
				'require' => true,
				'options' => $projectOptions,
				'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->parent,'top') : $data->parent
			)
		);
	}


	$provinceOption=array();
	$provinceOption[-1]='--- เลือกจังหวัด ---';

	$stmt='SELECT `provid`, `provname`
					FROM %co_province%
					ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt)->items;

	$provinceOptions=array();
	$provinceOptions[-1]='=== เลือกพื้นที่ ===';
	$provinceOptions['TH']='++ ทั้งประเทศ';
	$provinceOptions['ระดับภาค'][1]='++ ภาคกลาง';
	$provinceOptions['ระดับภาค'][3]='++ ภาคตะวันออกเฉียงเหนือ';
	$provinceOptions['ระดับภาค'][5]='++ ภาคเหนือ';
	$provinceOptions['ระดับภาค'][8]='++ ภาคใต้';

	$stmt = 'SELECT `provid`, `provname` `changwatName` FROM %co_province% ORDER BY CONVERT(`changwatName` USING tis620) ASC';
	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$provinceOptions['ระดับจังหวัด'][$rs->provid] = $rs->changwatName;
	}

	$form->addField(
		'changwat',
		array(
			'type' => 'select',
			'label' => 'พื้นที่ดำเนินการ:',
			'class' => 'sg-changwat -fill',
			'require' => true,
			'options' => $provinceOptions,
			)
		);

	$form->addField(
		'ampur',
		array(
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'options' => array('' => '== เลือกอำเภอ =='),
		)
	);

	$form->addField(
		'tambon',
		array(
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'options' => array('' => '== เลือกตำบล =='),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}{tr:โครงการ}</span></span>',
			'container' => '{class: "-sg-box-bottom -sg-text-right"}',
		)
	);

	$ret.=$form->build();
	//$ret.=print_o($data,'$data');

	$ret .= '<script type="text/javascript">
	$("#edit-data-prtype").change(function() {
		console.log($(this).val())
		$(".project-title-label").text($(this).val())
	});
	</script>';
	return $ret;
}
?>