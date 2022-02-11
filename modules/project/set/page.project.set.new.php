<?php
/**
* Create new project planning
*
* @param Object $self
* @return String
*/

$debug = true;

function project_set_new($self) {
	R::View('project.toolbar',$self,'สร้างชุดโครงการ','set');

	$data->projectset = post('parent');

	$isAdmin = user_access('administer projects');
	$isCreatable = user_access('create project set');

	if (!$isCreatable) return message('error','สิทธิ์ในการเข้าถึงถูกปฎิเสธ : ท่านไม่ได้รับอนุญาตให้สร้างชุดโครงการ');

	$maxPlanningPerPerson = cfg('PROJECT.SET.MAX_PER_USER');
	$myPlanningCount = mydb::select('SELECT COUNT(*) `totals` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype`="ชุดโครงการ" AND t.`uid` = :uid LIMIT 1',':uid',i()->uid)->totals;

	if (!$isAdmin && $maxPlanningPerPerson > 0 && $myPlanningCount >= $maxPlanningPerPerson) {
		$errorMsg .= 'ท่านสามารถสร้างชุดโครงการได้เพียง '.$maxPlanningPerPerson.' ชุดโครงการ กรุณาอัพเกรด';
		return $ret.message('error', $errorMsg);
	}

	$form=new Form('data',url('project/set/api/create'),'project-set-new','project-set-new');
	$form->addClass('sg-form');
	$form->addData('checkValid',true);

	$form->addConfig('title','รายละเอียดชุดโครงการ');

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
	$form->addField(
						'prtype',
						array(
							'type'=>'hidden',
							'label'=>'ประเภท:',
							'class'=>'-fill',
							'require'=>true,
							'value'=>'ชุดโครงการ',
							)
						);

	$form->addField(
						'title',
						array(
							'type'=>'text',
							'label'=>'ชื่อชุดโครงการ',
							'class'=>'-fill',
							'require'=>true,
							'value'=>$data->title,
							)
						);

	$form->addField(
						'pryear',
						array(
							'type'=>'radio',
							'label'=>'ประจำปี:',
							'class'=>'-fill',
							'options'=>array(
														date('Y')-1=>date('Y')-1+543,
														date('Y')+543,
														date('Y')+1+543
														),
							'require'=>true,
							'display'=>'inline',
							'value'=>SG\getFirst($data->pryear,date('Y'))
							)
						);

	if ($data->projectset) {
		$form->addField(
							'projectset',
							array(
								'type' => 'hidden',
								'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
								'value' => $data->projectset
								)
							);
	} else {
		// Select set
		mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');

		mydb::value('$JOIN$','');
		if (cfg('PROJECT.SET.CREATE_SELECT_PLANNING') == 'ONLY_MEMBERSHIP') {
			mydb::where('tu.`uid` = :uid', ':uid',i()->uid);
			mydb::value('$JOIN$', 'LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`');
		}

		$stmt = 'SELECT DISTINCT
						  t.`tpid`, t.`title`, p.`prtype`, t.`parent`, UPPER(tu.`membership`) `membership`
						FROM %project% p
							LEFT JOIN %topic% t USING(`tpid`)
							$JOIN$
						%WHERE%
						ORDER BY CONVERT(`title` USING tis620) ASC
						';
		$projectSet = mydb::select($stmt);
		//$ret .= print_o($projectSet, '$projectSet');

		// Create Planning & Proejct Set Tree
		foreach ($projectSet->items as $rs) {
			$tree[$rs->tpid] = $rs->parent;
			$items[$rs->tpid] = $rs;
		}
		$planningTree = sg_printTreeTable($items,sg_parseTree($items,$tree));
		//$ret .= print_o($planningTree,'$planningTree');

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
							'projectset',
							array(
								'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
								'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
								'class' => '-fill',
								'require' => true,
								'options' => $projectOptions,
								'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->projectset,'top') : $data->projectset
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

	$stmt='SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$provinceOptions['ระดับจังหวัด'][$rs->provid]=$rs->provname;
	}

	$form->addField(
						'changwat',
						array(
							'type'=>'select',
							'label'=>'พื้นที่ดำเนินการ:',
							'class'=>'-fill',
							'require'=>true,
							'options'=>$provinceOptions,
							'value'=>$data->changwat
							)
						);

	$form->addField(
					'fieldname',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:CREATE NEW PROJECT SET}</span>',
						'container' => array('class'=>'-sg-text-right'),
						)
					);
	$ret.=$form->build();
	return $ret;
}
?>