<?php
/**
* Project :: Form for create new project planning
* Created 2021-08-01
* Modify  2021-08-01
*
* @request org,parent,useissue
* @return Widget
*
* @usage project/planning/new
*/

$debug = true;

import('model:org.php');

class ProjectPlanningNew extends Page {
	function build() {
		$getOrgId = post('org');
		$getParent = post('parent');
		$getUseIssue = post('useissue');

		if ($getOrgId) $orgInfo = OrgModel::get($getOrgId);

		$isAdmin = user_access('administer projects');
		$isCreatable = user_access('create project planning');

		if (!$isCreatable) return message('error','สิทธิ์ในการเข้าถึงถูกปฎิเสธ : ท่านไม่ได้รับอนุญาตให้สร้างแผนงาน');

		$maxPlanningPerPerson = cfg('PROJECT.PLANNING.MAX_PER_USER');
		$myPlanningCount = mydb::select('SELECT COUNT(*) `totals` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype`="แผนงาน" AND t.`uid` = :uid LIMIT 1',':uid',i()->uid)->totals;

		if (!$isAdmin && $maxPlanningPerPerson > 0 && $myPlanningCount >= $maxPlanningPerPerson) {
			$errorMsg .= 'ท่านสามารถสร้างแผนงานได้เพียง '.$maxPlanningPerPerson.' แผนงาน กรุณาอัพเกรด';
			return message('error', $errorMsg);
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

		foreach (mydb::select('SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC')->items as $rs) {
			$provinceOptions['ระดับจังหวัด'][$rs->provid]=$rs->provname;
		}


		if (!$getParent) {
			// Select planning form org
			if ($getOrgId) {
				$parentOrgId = mydb::select('SELECT `parent` FROM %db_org% WHERE `orgid` = :orgId LIMIT 1', ':orgId', $getOrgId)->parent;
				mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');
				mydb::where('(t.`orgid` = :orgId'.($parentOrgId ? ' OR t.`orgid` = :parentOrgId' : '').')', ':orgId', $getOrgId, ':parentOrgId', $parentOrgId);

				$stmt = 'SELECT DISTINCT
					  t.`tpid`, t.`title`, p.`prtype`, t.`parent`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					%WHERE%
					ORDER BY CONVERT(`title` USING tis620) ASC
					';
				$projectSet = mydb::select($stmt);
				// debugMsg($projectSet, '$projectSet');

				// Create Planning & Proejct Set Tree
				foreach ($projectSet->items as $rs) {
					$tree[$rs->tpid] = $rs->parent;
					$items[$rs->tpid] = $rs;
				}
				$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
			} else {
				mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');

				if (cfg('PROJECT.PLANNING.CREATE_SELECT_PLANNING') == 'ONLY_MEMBERSHIP') {
					mydb::where('tu.`uid` = :uid', ':uid',i()->uid);
				}

				$stmt = 'SELECT DISTINCT
					  t.`tpid`, t.`title`, p.`prtype`, t.`parent`, UPPER(tu.`membership`) `membership`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
					%WHERE%
					ORDER BY CONVERT(`title` USING tis620) ASC
					';
				$projectSet = mydb::select($stmt);

				// Create Planning & Proejct Set Tree
				foreach ($projectSet->items as $rs) {
					$tree[$rs->tpid] = $rs->parent;
					$items[$rs->tpid] = $rs;
				}
				$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
				//$ret .= print_o($planningTree,'$planningTree');
			}

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
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างแผนงาน',
			]),
			'body' => new Form([
				'variable' => 'data',
				'action' => url('project/planning/create'),
				'id' => 'project-planning-new',
				'class' => 'sg-form project-planning-new',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'reload:'.url('project/planning/last'),
				'children' => [
					'<header class="header -box -hidden">'._HEADER_BACK.'<h3>สร้างแผนงาน</h3></header>',
					'orgid' => ['type' => 'hidden', 'value' => $getOrgId],
					'useplan' => ['type' => 'hidden', 'value' => $getUsePlan],
					'issue' => $getUseIssue ? [
						'type' => 'select',
						'label' => 'แผนงานประเด็น:',
						'class' => '-fill',
						'require' => true,
						'options' => R::Model('category.get', 'project:planning', 'catid', '{selectText: "== เลือกแผนงาน =="}'),
					] : NULL,
					'title' => [
						'type' => $getUseIssue ? 'hidden' : 'text',
						'label' => 'ชื่อแผนงาน',
						'class' => '-fill',
						'require' => $getUseIssue ? false : true,
						'value' => $data->title,
						'placeholder' => 'ระบุชื่อแผนงาน',
					],
					'pryear' => [
						'type'=>'radio',
						'label'=>'ประจำปี:',
						'class'=>'-fill',
						'options'=>array(
							date('Y')-1=>date('Y')-1+543,
							date('Y')+543,
							date('Y')+1+543,
						),
						'require'=>true,
						'display'=>'inline',
						'value'=>SG\getFirst($data->pryear,date('Y'))
					],
					'belowplanname' => [
						'type' => 'text',
						'label' => 'ภายใต้ชื่อแผนงาน:',
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อแผนงาน/ชุดโครงการที่ไม่อยู่ในรายการด้านล่าง',
					],
					'parent' => $getParent ? ['type' => 'hidden', 'value' => $getParent] : [
						'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
						'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
						'options' => $projectOptions,
						'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->projectset,'top') : $data->projectset
					],
					'areacode' => ['type' => 'hidden'],
					'changwat' => [
						'type'=>'select',
						'label'=>'พื้นที่ดำเนินการ:',
						'class' => 'sg-changwat -fill',
						'require'=>true,
						'options'=>$provinceOptions,
						'value'=>$data->areacode,
						'attr' => ['data-altfld' => '#edit-data-areacode'],
					],
					'ampur' => [
						'type' => 'select',
						'class' => 'sg-ampur -fill -hidden',
						'options' => array('' => '== เลือกอำเภอ =='),
						'attr' => ['data-altfld' => '#edit-data-areacode'],
					],
					'tambon' => [
						'type' => 'select',
						'class' => 'sg-tambon -fill -hidden',
						'options' => array('' => '== เลือกตำบล =='),
						'attr' => ['data-altfld' => '#edit-data-areacode'],
					],
					'save' => [
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:CREATE NEW PLANNING}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				],
			]),
		]);
	}
}
?>
<?php
// function project_planning_new($self) {
// 	$getOrgId = post('org');
// 	$getParent = post('parent');
// 	$getUseIssue = post('useissue');

// 	R::View('project.toolbar',$self,'สร้างแผนงาน','planning');

// 	if ($getOrgId) $orgInfo = OrgModel::get($getOrgId);

// 	$isAdmin = user_access('administer projects');
// 	$isCreatable = user_access('create project planning');

// 	if (!$isCreatable) return message('error','สิทธิ์ในการเข้าถึงถูกปฎิเสธ : ท่านไม่ได้รับอนุญาตให้สร้างแผนงาน');

// 	$maxPlanningPerPerson = cfg('PROJECT.PLANNING.MAX_PER_USER');
// 	$myPlanningCount = mydb::select('SELECT COUNT(*) `totals` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype`="แผนงาน" AND t.`uid` = :uid LIMIT 1',':uid',i()->uid)->totals;

// 	if (!$isAdmin && $maxPlanningPerPerson > 0 && $myPlanningCount >= $maxPlanningPerPerson) {
// 		$errorMsg .= 'ท่านสามารถสร้างแผนงานได้เพียง '.$maxPlanningPerPerson.' แผนงาน กรุณาอัพเกรด';
// 		return message('error', $errorMsg);
// 	}



// 	$provinceOption=array();
// 	$provinceOption[-1]='--- เลือกจังหวัด ---';

// 	$stmt='SELECT `provid`, `provname`
// 		FROM %co_province%
// 		ORDER BY CONVERT(`provname` USING tis620) ASC';
// 	$dbs=mydb::select($stmt)->items;

// 	$provinceOptions=array();
// 	$provinceOptions[-1]='=== เลือกพื้นที่ ===';
// 	$provinceOptions['TH']='++ ทั้งประเทศ';
// 	$provinceOptions['ระดับภาค'][1]='++ ภาคกลาง';
// 	$provinceOptions['ระดับภาค'][3]='++ ภาคตะวันออกเฉียงเหนือ';
// 	$provinceOptions['ระดับภาค'][5]='++ ภาคเหนือ';
// 	$provinceOptions['ระดับภาค'][8]='++ ภาคใต้';

// 	foreach (mydb::select('SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC')->items as $rs) {
// 		$provinceOptions['ระดับจังหวัด'][$rs->provid]=$rs->provname;
// 	}


// 	if (!$getParent) {
// 		// Select planning form org
// 		if ($getOrgId) {
// 			$parentOrgId = mydb::select('SELECT `parent` FROM %db_org% WHERE `orgid` = :orgId LIMIT 1', ':orgId', $getOrgId)->parent;
// 			mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');
// 			mydb::where('(t.`orgid` = :orgId'.($parentOrgId ? ' OR t.`orgid` = :parentOrgId' : '').')', ':orgId', $getOrgId, ':parentOrgId', $parentOrgId);

// 			$stmt = 'SELECT DISTINCT
// 				  t.`tpid`, t.`title`, p.`prtype`, t.`parent`
// 				FROM %project% p
// 					LEFT JOIN %topic% t USING(`tpid`)
// 				%WHERE%
// 				ORDER BY CONVERT(`title` USING tis620) ASC
// 				';
// 			$projectSet = mydb::select($stmt);
// 			// debugMsg($projectSet, '$projectSet');

// 			// Create Planning & Proejct Set Tree
// 			foreach ($projectSet->items as $rs) {
// 				$tree[$rs->tpid] = $rs->parent;
// 				$items[$rs->tpid] = $rs;
// 			}
// 			$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
// 		} else {
// 			mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');

// 			if (cfg('PROJECT.PLANNING.CREATE_SELECT_PLANNING') == 'ONLY_MEMBERSHIP') {
// 				mydb::where('tu.`uid` = :uid', ':uid',i()->uid);
// 			}

// 			$stmt = 'SELECT DISTINCT
// 				  t.`tpid`, t.`title`, p.`prtype`, t.`parent`, UPPER(tu.`membership`) `membership`
// 				FROM %project% p
// 					LEFT JOIN %topic% t USING(`tpid`)
// 					LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`
// 				%WHERE%
// 				ORDER BY CONVERT(`title` USING tis620) ASC
// 				';
// 			$projectSet = mydb::select($stmt);

// 			// Create Planning & Proejct Set Tree
// 			foreach ($projectSet->items as $rs) {
// 				$tree[$rs->tpid] = $rs->parent;
// 				$items[$rs->tpid] = $rs;
// 			}
// 			$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
// 			//$ret .= print_o($planningTree,'$planningTree');
// 		}

// 		$itemsForRadio = 5;
// 		$projectOptions = array();
// 		if ($projectSet->_num_rows <= $itemsForRadio) {
// 			$projectOptions['top'] = 'ไม่มีแผนงาน/ชุดโครงการ';
// 		} else {
// 			$projectOptions[0] =  '=== เลือกแผนงาน/ชุดโครงการ ===';
// 			$projectOptions['top']='ไม่มีแผนงาน/ชุดโครงการ';
// 			$projectOptions['sep']='--- ภายใต้แผนงาน/ชุดโครงการ ---';
// 		}
// 		foreach ($planningTree as $rs) {
// 			$projectOptions[$rs->tpid] = str_repeat('--',$rs->treeLevel).' '.$rs->title.' ('.$rs->prtype.')';
// 		}
// 	}


// 	// $form=new Form('data',url('project/planning/create'),'project-planning-new','project-planning-new');
// 	// $form->addClass('sg-form');
// 	// $form->addData('checkValid',true);


// 	// $form->addField(
// 	// 	'prtype',
// 	// 	array(
// 	// 		'type'=>'hidden',
// 	// 		'label'=>'ประเภท:',
// 	// 		'class'=>'-fill',
// 	// 		'require'=>true,
// 	// 		'value'=>'แผนงาน',
// 	// 		)
// 	// );

// 	// $form->addField(
// 	// 	'title',
// 	// 	array(
// 	// 		'type'=>'text',
// 	// 		'label'=>'ชื่อแผนงาน',
// 	// 		'class'=>'-fill',
// 	// 		'require'=>true,
// 	// 		'value'=>$data->title,
// 	// 		)
// 	// );

// 	// $form->addField(
// 	// 	'pryear',
// 	// 	array(
// 	// 		'type'=>'radio',
// 	// 		'label'=>'ประจำปี:',
// 	// 		'class'=>'-fill',
// 	// 		'options'=>array(
// 	// 			date('Y')-1=>date('Y')-1+543,
// 	// 			date('Y')+543,
// 	// 			date('Y')+1+543,
// 	// 			date('Y')+2+543
// 	// 		),
// 	// 		'require'=>true,
// 	// 		'display'=>'inline',
// 	// 		'value'=>SG\getFirst($data->pryear,date('Y'))
// 	// 	)
// 	// );

// 	// if ($data->projectset) {
// 	// 	$form->addField(
// 	// 		'projectset',
// 	// 		array(
// 	// 			'type' => 'hidden',
// 	// 			'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
// 	// 			'value' => $data->projectset
// 	// 			)
// 	// 	);
// 	// } else {
// 	// 	$form->addField(
// 	// 		'projectset',
// 	// 		array(
// 	// 			'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
// 	// 			'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
// 	// 			'class' => '-fill',
// 	// 			'require' => true,
// 	// 			'options' => $projectOptions,
// 	// 			'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->projectset,'top') : $data->projectset
// 	// 		)
// 	// 	);
// 	// }

// 	// $form->addField(
// 	// 	'changwat',
// 	// 	array(
// 	// 		'type'=>'select',
// 	// 		'label'=>'พื้นที่ดำเนินการ:',
// 	// 		'class'=>'-fill',
// 	// 		'require'=>true,
// 	// 		'options'=>$provinceOptions,
// 	// 		'value'=>$data->changwat
// 	// 		)
// 	// );

// 	// $form->addField(
// 	// 	'fieldname',
// 	// 	array(
// 	// 		'type'=>'button',
// 	// 		'value'=>'<i class="icon -save -white"></i><span>{tr:CREATE NEW PLANNING}</span>',
// 	// 		'container' => array('class'=>'-sg-text-right'),
// 	// 		)
// 	// );


// 	return new Scaffold([
// 		'appBar' => new AppBar([
// 			'title' => 'สร้างแผนงาน',
// 		]),
// 		'body' => new Form([
// 			'variable' => 'data',
// 			'action' => url('project/planning/create'),
// 			'id' => 'project-planning-new',
// 			'class' => 'sg-form project-planning-new',
// 			'checkValid' => true,
// 			'rel' => 'notify',
// 			'done' => 'reload:'.url('project/planning/last'),
// 			'children' => [
// 				'<header class="header -box -hidden">'._HEADER_BACK.'<h3>สร้างแผนงาน</h3></header>',
// 				'orgid' => ['type' => 'hidden', 'value' => $getOrgId],
// 				'useplan' => ['type' => 'hidden', 'value' => $getUsePlan],
// 				'issue' => $getUseIssue ? [
// 					'type' => 'select',
// 					'label' => 'แผนงานประเด็น:',
// 					'class' => '-fill',
// 					'require' => true,
// 					'options' => R::Model('category.get', 'project:planning', 'catid', '{selectText: "== เลือกแผนงาน =="}'),
// 				] : NULL,
// 				'title' => [
// 					'type' => $getUseIssue ? 'hidden' : 'text',
// 					'label' => 'ชื่อแผนงาน',
// 					'class' => '-fill',
// 					'require' => $getUseIssue ? false : true,
// 					'value' => $data->title,
// 					'placeholder' => 'ระบุชื่อแผนงาน',
// 				],
// 				'pryear' => [
// 					'type'=>'radio',
// 					'label'=>'ประจำปี:',
// 					'class'=>'-fill',
// 					'options'=>array(
// 						date('Y')-1=>date('Y')-1+543,
// 						date('Y')+543,
// 						date('Y')+1+543,
// 						date('Y')+2+543
// 					),
// 					'require'=>true,
// 					'display'=>'inline',
// 					'value'=>SG\getFirst($data->pryear,date('Y'))
// 				],
// 				'belowplanname' => [
// 					'type' => 'text',
// 					'label' => 'ภายใต้ชื่อแผนงาน:',
// 					'class' => '-fill',
// 					'placeholder' => 'ระบุชื่อแผนงาน/ชุดโครงการที่ไม่อยู่ในรายการด้านล่าง',
// 				],
// 				'parent' => $getParent ? ['type' => 'hidden', 'value' => $getParent] : [
// 					'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
// 					'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
// 					'options' => $projectOptions,
// 					'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->projectset,'top') : $data->projectset
// 				],
// 				'areacode' => ['type' => 'hidden'],
// 				'changwat' => [
// 					'type'=>'select',
// 					'label'=>'พื้นที่ดำเนินการ:',
// 					'class' => 'sg-changwat -fill',
// 					'require'=>true,
// 					'options'=>$provinceOptions,
// 					'value'=>$data->areacode,
// 					'attr' => ['data-altfld' => '#edit-data-areacode'],
// 				],
// 				'ampur' => [
// 					'type' => 'select',
// 					'class' => 'sg-ampur -fill -hidden',
// 					'options' => array('' => '== เลือกอำเภอ =='),
// 					'attr' => ['data-altfld' => '#edit-data-areacode'],
// 				],
// 				'tambon' => [
// 					'type' => 'select',
// 					'class' => 'sg-tambon -fill -hidden',
// 					'options' => array('' => '== เลือกตำบล =='),
// 					'attr' => ['data-altfld' => '#edit-data-areacode'],
// 				],
// 				'save' => [
// 					'type'=>'button',
// 					'value'=>'<i class="icon -save -white"></i><span>{tr:CREATE NEW PLANNING}</span>',
// 					'container' => '{class: "-sg-text-right"}',
// 				],
// 			],
// 		]),
// 	]);
// }
?>