<?php
/**
* Project :: Project List Report
* Created 2018-22-10
* Modify  2021-09-24
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/report/name
*/

$debug = true;

class ProjectReportName extends Page {
	var	$projectSet;
	var	$changwat;
	var	$ampur;
	var	$budgetYear;
	var	$projectStatus;
	var	$export;
	var	$search;

	function __construct() {
		$this->projectSet = post('prset');
		$this->changwat = post('prov');
		$this->ampur = post('ampur');
		$this->budgetYear = post('year');
		$this->projectStatus = post('status');
		$this->export = post('export');
		$this->search = post('q');
	}

	function build() {
		$conditions = (Object) ['projectType' => 'โครงการ'];
		$options = (Object) ['items' => 100, 'debug' => false];

		if ($this->projectStatus) $conditions->status = $this->projectStatus;
		if ($this->search) $conditions->title = $this->search;
		if ($this->budgetYear) $conditions->budgetYear = $this->budgetYear;
		if ($this->changwat) $conditions->changwat = $this->changwat;

		if (count((Array) $conditions) > 1) $options->items = '*';

		$followDbs = R::Model('project.follows', $conditions, $options);

		if ($export) {
			// file name for download
			$filename = 'project_name_'.date('Y-m-d H-i').".xls";

			die(R::Model('excel.export',$tables, $filename, '{debug:false}'));
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายชื่อโครงการ '.$followDbs->count().' โครงการ',
				// 'leading' => '<a href="'.url('project/report').'"><i class="icon -material">insights</i></a>',
				'navigator' => [
					'<a href="'.url('project/report').'"><i class="icon -material">insights</i><span>วิเคราะห์</span></a>',
				],
			]),
			'body' => new Widget([
				'children' => [
					$this->formWidget(),
					new ScrollView([
						'child' => new Table([
							'class' => 'project-report-name',
							'thead' => [
								'ปี',
								'รหัสโครงการ',
								'เลขที่ข้อตกลง',
								'ชื่อโครงการ',
								'จังหวัด',
								'พื้นที่',
								'budget -money' => 'งบประมาณ',
								'startdate -date' => 'วันที่เริ่ม',
								'enddate -date' => 'วันที่สิ้นสุด',
								'สถานะ',
								'created -date' => 'วันที่สร้าง',
							], // thead
							'children' => (function($items, &$orgList = []) {
								$rows = [];
								foreach ($items as $rs) {
									if ($rs->orgId) $orgList[$rs->orgId] = 1;
									$rows[] = [
										$rs->pryear+543,
										$rs->prid,
										$rs->agrno,
										$this->export ? $rs->title : '<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>',
										$rs->changwatName,
										SG\getFirst($rs->orgName,$rs->area),
										number_format($rs->budget, 2),
										$rs->date_from?sg_date($rs->date_from,'ว ดด ปปปป'):'',
										$rs->date_end?sg_date($rs->date_end,'ว ดด ปปปป'):'',
										$rs->project_status,
										$rs->created?sg_date($rs->created,'ว ดด ปปปป'):'',
									];
								}
								return $rows;
							})($followDbs->items, $orgList),
							'tfoot' => [
								['', '', 'จำนวน', '<b>'.count($followDbs->items).'</b> โครงการ', '', '<b>'.count($orgList).' องค์กร</b>', number_format($followDbs->sum->budget,2), '', '', '', ''],
							], // tfoot
						]), // Table
					]), // ScrollView
					// new DebugMsg($followDbs, '$followDbs'),
				], // children
			]), // Widget
		]);
	}

	function formWidget() {
		return new Form([
			'action' => url('project/report/name'),
			'id' => 'project-report-name',
			'class' => 'form-report',
			'method' => 'get',
			'container' => '{tag: "nav", class: "nav -page"}',
			'children' => [
				'q' => [
					'type' => 'text',
					'value' => $this->search,
					'placeholder' => 'ระบุชื่อโครงการ',
				],
				'prov' => [
					'type' => 'select',
					'options' => ['' => '== ทุกจังหวัด =='] + mydb::select(
						'SELECT cop.`provid`, cop.`provname` `changwatName`
						FROM %topic% t
							LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
						WHERE t.`type` = "project"
						GROUP BY cop.`provid`
						HAVING `changwatName` != ""
						ORDER BY CONVERT(`changwatName` USING tis620) ASC;
						-- {key: "provid", value: "changwatName"}'
					)->items,
					'value' => $this->changwat,
				],
				'year' => [
					'type' => 'select',
					'options' => ['' => '== ทุกปี =='] + mydb::select('SELECT `pryear`, CONCAT("พ.ศ.",pryear+543) `yearName` FROM %project% GROUP BY `pryear` ORDER BY `pryear` ASC; -- {key: "pryear", value: "yearName"}')->items,
					'value' => $this->budgetYear,
				],
				'go' => [
					'type' => 'button',
					'value' => 'go',
					'text' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
				],
				'export' => [
					'type' => 'button',
					'name' => 'export',
					'class' => '-secondary',
					'value' => 'excel',
					'text' => '<i class="icon -material">cloud_download</i><span>Excel</span>',
				],
			],
		]);
	}
}
?>