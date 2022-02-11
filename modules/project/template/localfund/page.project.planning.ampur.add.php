<?php
/**
* Project :: Create Ampur Planning
* Created 2021-05-17
* Modify  2021-05-17
*
* @param Object $self
* @return String
*
* @usage project/planning/ampur/add
*/

$debug = true;

class ProjectPlanningAmpurAdd extends Page {
	function __construct() {
		parent::__construct();
	}

	function build() {
		// Data Model

		// Get province that user is trainer of organization
		if (is_admin('project')) {
			$trainerProvince = mydb::select(
				'SELECT DISTINCT LEFT(`areacode`, 2) `changwat`, `provname`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
				WHERE p.`prtype` IN ("แผนงาน") AND cop.`provname`!=""
				ORDER BY CONVERT(`provname` USING tis620) ASC;
				-- {key: "changwat", value: "provname"}'
			)->items;
		} else {
			$trainerProvince = mydb::select('SELECT o.`areacode`, cop.`provid` `changwat`, cop.`provname`
				FROM %org_officer% of
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
				WHERE of.`uid` = :uid;
				-- {key: "changwat", value: "provname"}',
				':uid', i()->uid
			)->items;
		}

		$isAdd = is_admin('project') || $trainerProvince;

		if (!$isAdd) return message('error', 'Access Denied');



		// Create Ampur Planning when data post
		if ($data = post('data')) {
			$data = (Object) $data;

			$data->prtype = 'แผนงาน';
			$data->pryear = SG\getFirst($data->pryear);
			$data->plan = SG\getFirst($data->plan);
			$data->areacode = $data->changwat.$data->ampur;
			$data->title = (mydb::select('SELECT `name` FROM %tag% WHERE `taggroup` = "project:planning" AND `catid` = :catid LIMIT 1', ':catid', $data->plan)->name)
				. ' ปี '.($data->pryear+543)
				. ' อำเภอ'.mydb::select('SELECT CONCAT(cod.`distname`," จังหวัด",cop.`provname`) `name` FROM %co_district% cod LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(cod.`distid`,2) WHERE cod.`distid` = :ampur LIMIT 1', ':ampur', $data->areacode)->name;
			// $data->changwat = $fundInfo->info->changwat;
			// $data->ampur = $fundInfo->info->ampur;
			// $data->tambon = $fundInfo->info->tambon;

			$result = R::Model('project.create', $data);
			$projectId = $result->tpid;

			$ret .= 'projectId = '.$projectId;

			// // Create planning group
			$stmt = 'INSERT INTO %project_tr%
				(`tpid`, `refid`, `formid`, `part`, `uid`, `created`)
				VALUES
				(:tpid, :refid, "info", "title", :uid, :created)';

			mydb::query($stmt,':tpid',$projectId, ':refid',$data->plan, ':uid',i()->uid, ':created',date('U'));

			// //$ret .= print_o($data,'$data');
			// //$ret.=print_o($fundInfo);
			$_SESSION['mode'] = 'edit';
			location('project/planning/'.$projectId);

			//$ret .= print_o($data, '$data');
			return $ret;
		}


		// View Model

		// Option for select area
		$areaOptions = [];
		if (mydb::table_exists('%project_area%')) {
			foreach (mydb::select(
					'SELECT a.`areaid`, CONCAT("เขต ", a.`areaid`, " ",a.`areaname`) `areaname`, GROUP_CONCAT(DISTINCT f.`changwat` ORDER BY `changwat`) `changwat`
					FROM %project_area% a
						LEFT JOIN %project_fund% f ON f.`areaid` = a.`areaid`
					GROUP BY `areaid`
					ORDER BY CAST(a.`areaid` AS UNSIGNED)'
				)->items as $rs) {
			 	$areaOptions[$rs->areaid] = [
			 		'label' => $rs->areaname,
			 		'attr' => ['data-changwat' => $rs->changwat],
			 	];
			 }
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างแผนงานอำเภอ',
				'class' => '-project',
				'navigator' => [
					'info' => new Ui([
						'class' => 'ui-nav -sg-text-center',
						'children' => [
							['text' => '<a href="'.url('project/planning').'"><i class="icon -material">home</i><span>แผนงาน</span></a>'],
							['text' => '<a href="'.url('project/planning/ampur').'"><i class="icon -material">fact_check</i><span>แผนอำเภอ</span></a>'],
						],
					]),
				], // navigator
			]), // AppBar
			'children' => [
				'<header class="header"><h3>สร้างแผนงานอำเภอ</h3></header>',
				new Form([
					'action' => url('project/planning/ampur/add'),
					'variable' => 'data',
					'class' => 'sg-form',
					'checkValid' => true,
					//'rel' => 'notify',
					// 'done' => 'notify',
					'children' => [
						'plan' => [
							'type' => 'select',
							'label' => 'แผนงาน:',
							'class' => '-fill',
							'require' => true,
							'options' => R::Model('category.get','project:planning','catid', '{selectText: "== เลือกแผนงาน =="}'),
						], // plan
						'area' => [
							'type' => 'select',
							'label' => 'เขต',
							'class' => '-fill',
							'options' => ['' => '== เลือกเขต =='] + $areaOptions,
						],
						'changwat' => [
							'type' => 'select',
							'label' => 'จังหวัด',
							'class' => 'sg-changwat -fill',
							'require' => true,
							'options' => ['' => '== เลือกจังหวัด ==']+$trainerProvince,
						],
						'ampur' => [
							'type' => 'select',
							'label' => 'อำเภอ',
							'class' => 'sg-ampur -fill',
							'require' => true,
							'options' => ['' => '== เลือกอำเภอ =='],
						],
						'pryear' => [
							'type' => 'select',
							'label' => 'ปี พ.ศ.',
							'class' => '-fill',
							'require' => true,
							'options' => [
								'' => '== เลือกปี พ.ศ. ==',
								date(Y)-2 => 'พ.ศ.'.(date(Y)-2+543),
								date(Y)-1 => 'พ.ศ.'.(date(Y)-1+543),
								date(Y) => 'พ.ศ.'.(date(Y)+543),
								date(Y)+1 => 'พ.ศ.'.(date(Y)+1+543),
							],
						],
						'go' => [
							'type' => 'button',
							'value' => '<i class="icon -material">add</i><span>สร้างแผนงาน</span>',
							'container' => '{class: "-sg-text-right"}',
						], // go
						'<script type="text/javascript">
						$("#edit-data-area").change(function() {
							let $this = $(this)
							let changwatId = $this.find(":selected").data("changwat").split(",")
							let $changwatCheckBox = $("#edit-data-changwat option")
							console.log(changwatId)

							// Clear changwat checked and hide changwat not in area
							$("#edit-data-ampur")[0].options.length=1
							$changwatCheckBox
							//.val("")
							//.find("option")
							.each(function(i) {
								let $checkBox = $(this)
								$checkBox.hide()
								console.log($checkBox.val(),$checkBox.text(),changwatId.indexOf($checkBox.val()));
								$checkBox.removeAttr("selected","")
								if ($checkBox.val() == "") {
									$checkBox.show().attr("selected","selected")
									console.log("SHOW DEFAULT")
								} else if (changwatId.indexOf($checkBox.val()) != -1) {
									$checkBox.show()
									console.log("SHOW")
								} else {
									$checkBox.hide()
									console.log("HIDE")
								}
							});
						});
						</script>',
					], // chidren of Form
				]), // Form
			], // children of Widget
		]); // Widget
	}
}
?>