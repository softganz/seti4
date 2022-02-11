<?php
/**
* Create new project planning
*
* @param Object $self
* @return String
*/

$debug = true;

class xProjectPlanningNew extends Page {
	function build() {
		$isAdmin = user_access('administer projects');
		$isCreatable = user_access('create project planning');

		if (!$isCreatable) return message('error','สิทธิ์ในการเข้าถึงถูกปฎิเสธ : ท่านไม่ได้รับอนุญาตให้สร้างแผนงาน');

		$maxPlanningPerPerson = cfg('PROJECT.PLANNING.MAX_PER_USER');
		$myPlanningCount = mydb::select('SELECT COUNT(*) `totals` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype`="แผนงาน" AND t.`uid` = :uid LIMIT 1',':uid',i()->uid)->totals;

		if (!$isAdmin && $maxPlanningPerPerson > 0 && $myPlanningCount >= $maxPlanningPerPerson) {
			$errorMsg .= 'ท่านสามารถสร้างแผนงานได้เพียง '.$maxPlanningPerPerson.' แผนงาน กรุณาอัพเกรด';
			return $ret.message('error', $errorMsg);
		}


		$provinceOption=array();
		$provinceOption[-1]='--- เลือกจังหวัด ---';

		$stmt='SELECT `provid`, `provname`
			FROM %co_province%
			ORDER BY CONVERT(`provname` USING tis620) ASC';
		$dbs=mydb::select($stmt)->items;

		$provinceOptions = [
			-1 => '=== เลือกพื้นที่ ===',
			'TH' => '++ ทั้งประเทศ',
			'ระดับภาค' => [
				1 => '++ ภาคกลาง',
				3 => '++ ภาคตะวันออกเฉียงเหนือ',
				5 => '++ ภาคเหนือ',
				8 => '++ ภาคใต้',
			],
		];

		$stmt='SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
		$dbs=mydb::select($stmt);

		foreach ($dbs->items as $rs) {
			$provinceOptions['ระดับจังหวัด'][$rs->provid]=$rs->provname;
		}




		$form=new Form('data',url('project/planning/create'),'project-planning-new','project-planning-new');
		$form->addClass('sg-form');
		$form->addData('checkValid',true);


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
			'title',
			array(
				'type'=>'text',
				'label'=>'ชื่อประเมินระดับแผนงาน',
				'class'=>'-fill',
				'require'=>true,
				'value'=>$data->title,
				'placeholder' => 'ระบุชื่อแผนงานที่ต้องการประเมิน',
			)
		);

		$form->addField(
			'year',
			array(
				'type'=>'radio',
				'label'=>'ประจำปี:',
				'class'=>'-fill',
				'options'=>array(
					date('Y')-1=>date('Y')-1+543,
					date('Y')+543,
					date('Y')+1+543,
					date('Y')+2+543
				),
				'require'=>true,
				'display'=>'inline',
				'value'=>SG\getFirst($data->year,date('Y'))
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
			mydb::where('p.`prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" AND p.`ischild`>0');

			mydb::value('$JOIN$','');
			if (cfg('PROJECT.PLANNING.CREATE_SELECT_PLANNING') == 'ONLY_MEMBERSHIP') {
				mydb::where('tu.`uid` = :uid', ':uid',i()->uid);
				mydb::value('$JOIN$', 'LEFT JOIN %topic_user% tu ON tu.`tpid` = p.`tpid`');
			}

			$form->addField(
				'belowplanname',
				array(
					'type' => 'text',
					'label' => 'ภายใต้ชื่อแผนงาน:',
					'class' => '-fill',
					'placeholder' => 'ระบุชื่อแผนงาน/ชุดโครงการ',
				)
			);

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
				'parent',
				array(
					'type' => $projectSet->_num_rows <= $itemsForRadio ? 'radio' : 'select',
					//'label' => 'ภายใต้แผนงาน/ชุดโครงการ:',
					'class' => '-fill',
					'require' => true,
					'options' => $projectOptions,
					'value' => $projectSet->_num_rows <= $itemsForRadio ? SG\getFirst($data->parent,'top') : $data->parent
				)
			);
		}
		/*
		$stmt='SELECT
			  t.`tpid`, t.`title`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE (p.`prtype` IN ("แผนงาน","ชุดโครงการ") || p.`ischild` > 0) AND p.`project_status`="กำลังดำเนินโครงการ" AND t.`uid` = :uid
			ORDER BY CONVERT(`title` USING tis620) ASC
			';
		$projectSet=mydb::select($stmt, ':uid',i()->uid);

		$projectOptions=array();
		$projectOptions[0]='=== เลือกแผนงาน ===';
		$projectOptions['top']='แผนงานระดับบนสุด';
		if ($projectSet->items) {
			$projectOptions['sep']='--- ภายใต้ ---';
			foreach ($projectSet->items as $rs) {
				$projectOptions[$rs->tpid]=$rs->title;
			}
		}
		$form->addField(
			'projectset',
			array(
				'type'=>'select',
				'label'=>'ภายใต้แผนงาน/ชุดโครงการ:',
				'class'=>'-fill',
				'require'=>true,
				'options'=>$projectOptions,
				'value'=>$data->projectset
			)
		);

		*/




		$form->addField(
			'areacode',
			array(
				'type'=>'select',
				'label'=>'พื้นที่ดำเนินการ:',
				'class'=>'-fill',
				'require'=>true,
				'options'=>$provinceOptions,
				'value'=>$data->areacode
			)
		);

		$form->addField(
			'save',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>สร้างประเมินระดับแผนงาน</span>',
				'container' => array('class'=>'-sg-text-right'),
			)
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประเมินระดับแผนงาน',
				'navigator' => [
					R::View('project.nav.my'),
				],
			]),
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>รายละเอียดประเมินระดับแผนงาน</h3></header>',
				new Form([
					'action' => url('project/planning/create'),
					'variable' => 'data',
					'id' => 'project-planning-new',
					'class' => 'sg-form project-planning-new',
					'checkValid' => true,
					'rel' => 'notify',
					'done' => 'reload:'.url('project/planning/last'),
					'children' => [
						'title' => [
							'type'=>'text',
							'label'=>'ชื่อประเมินระดับแผนงาน',
							'class'=>'-fill',
							'require'=>true,
							'value'=>$data->title,
							'placeholder' => 'ระบุชื่อแผนงานที่ต้องการประเมิน',
						],
						'year' => [
							'type'=>'radio',
							'label'=>'ประจำปี:',
							'class'=>'-fill',
							'options'=>array(
								date('Y')-1=>date('Y')-1+543,
								date('Y')+543,
								date('Y')+1+543,
								date('Y')+2+543
							),
							'require'=>true,
							'display'=>'inline',
							'value'=>SG\getFirst($data->year,date('Y'))
						],
						'areacode' => [
							'type'=>'select',
							'label'=>'พื้นที่ดำเนินการ:',
							'class'=>'-fill',
							'require'=>true,
							'options'=>$provinceOptions,
							'value'=>$data->areacode
						],
						'save' => [
							'type'=>'button',
							'value'=>'<i class="icon -save -white"></i><span>สร้างประเมินระดับแผนงาน</span>',
							'container' => array('class'=>'-sg-text-right'),
						],
					],// children
				]), // Form
				// $form,
			],
		]);
	}
}
?>