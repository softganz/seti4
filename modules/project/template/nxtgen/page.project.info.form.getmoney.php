<?php
/**
* Project :: Follow Students List Information
* Created 2021-11-10
* Modify  2021-11-10
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/proposal/{id}/info.student
*/

$debug = true;

import('widget:project.follow.nav.php');

class ProjectInfoFormGetmoney extends Page {
	var $projectId;
	var $serieNo;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $serieNo) {
		$this->projectId = $projectInfo->projectId;
		$this->serieNo = $serieNo;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'navigator' => new ProjectFollowNavWidget($this->projectInfo),
			]),
			'body' => new Container([
				'children' => [
					new Column([
						'class' => '-forprint',
						'children' => [
							new Row([
								'class' => '-sg-paddingmore',
								'mainAxisAlignment' => 'spacebetween',
								'children' => [
									'ที่ .............',
									'',
									$this->projectInfo->info->orgName.'<br />ที่อยู่ .........',
								],
							]),
							new Container(['class' => '-sg-text-center','child' => 'วันที่ .................']),
							new Column([
								'children' => [
									'เรื่อง ส่งข้อมูลจำนวนนักศึกษาและแบบตอบรับการจัดสรรงบประมาณ',
									'เรียน ปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม',
									'อ้างถึง หนังสือสำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม<br />ด่วนที่สุด ที่ อว ๐๒๐๘.ว ๖๑๕๓ ลงวันที่ ๗ มิถุนายน ๒๕๖๔',
									new Row([
										'children' => [
											'สิ่งที่ส่งมาด้วย',
											'๑. ข้อมูลจำนวนนักศึกษา จำนวน ๑ ฉบับ<br />'
											. '๒. แบบขอรับการจัดสรรงบประมาณ จำนวน ๑ ฉบับ',
										], // children
									]), // Row
								],
							]), // Column
							new Column([
								'children' => [
									'ตามหนังสือที่อ้างถึงสำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม ขอให้สถาบันอุดมศึกษาที่เข้าร่วมโครงการผลิตบัณฑิตพันธุ์ใหม่ ส่งรายชื่อนักศึกษารับใหม่ รุ่นปีการศึกษา .... และนักศึกษาที่เลื่อนชั้น ในปีการศึกษา .... และส่งแบบขอรับการจัดสรรงบประมาณโครงการผลิตบัณฑิตพันธุ์ใหม่สำหรับนักศึกษารุ่นปีการศึกษา ..... - ..... นั้น',
									'ในการนี้ '.$this->projectInfo->info->orgName.' นำส่งข้อมูลจำนวนนักศึกษาและแบบตอบรับการจัดสรรงบประมาณสำหรับนักศึกษา '.$this->projectInfo->title.' รุ่นปีการศึกษา .... จำนวน ... คน รุ่นปีการศึกษา .... จำนวน ... คน และรุ่นปีการศึกษา .... จำนวน ... คน เพื่อขออนุมัติงบประมาณจากสำนักงานปลัดกระทรวงการอุดมศึกษา วิทยาศาสตร์ วิจัยและนวัตกรรม',
									'จึงเรียนมาเพื่อโปรดพิจารณา',
								], // children
							]), // Column
							new Column([
								'children' => [
									'ขอแสดงความนับถือ',
									'......',
									'(.........)',
									'อธิการบดี',
								],
							]),
							new Column([
								'children' => [
									'ชื่อหน่วยงาน',
									'โทรศัพท์',
								],
							]),
						], // children
					]), // Column

					new Column([
						'class' => '-forprint -sg-text-center',
						'children' => [
							$this->projectInfo->info->orgName,
							'ข้อมูลจำนวนนักศึกษาใน '.$this->projectInfo->info->parentTitle.' '.$this->projectInfo->title,
							'รุ่นปีการศึกษา ..... - .....',
							new Table([
								'thead' => ['กลุ่มอุตสาหกรรม','ชื่อหลักสูตร','ประเภทหลักสูตร','จำนวนนักศึกษา',],
								'children' => [
									['',$this->projectInfo->title,'','','','',],
								],
							]),
						],
					]),

					new Column([
						'class' => '-forprint',
						'children' => [
							'แบบขอรับการจัดสรรงบประมาณ'.$this->projectInfo->info->parentTitle.' นักศึกษาใหม่ รุ่นปีการศึกษา ...',
							'เพื่อเป็นค่าใช้จ่ายสำหรับนักศึกษาใหม่ ปีงบประมาณ .... (ระหว่างเดือน ..... - .....)',
							new ScrollView([
								'child' => new Table([
									'thead' => ['no -nowrap' => 'ลำดับที่', 'code -center' => 'รหัสประจำตัวนักศึกษา', 'ชื่อ - สกุล', 'cid -center' => 'เลขประจำตัวประชาชน'],
									'children' => (function() {
										$dbs = mydb::select(
											'SELECT
											s.*, p.`prename`, p.`name`, p.`lname`, p.`cid`
											FROM %lms_student% s
												LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
											WHERE s.`projectId` = :projectId AND `serieNo` = :serieNo AND s.`status` IN ("Active")',
											[':projectId' => $this->projectId, ':serieNo' => $this->serieNo]
										);
										$rows = [];
										$no = 0;
										foreach ($dbs->items as $item) {
											$rows[] = [
												++$no,
												$item->studentCode,
												$item->prename.$item->name.' '.$item->lname,
												$item->cid,
											];
										}
										return $rows;
									})(), // children
								]), // Table
							]), // ScrollView
						],
					]),

					// new DebugMsg($this->projectInfo, '$this->projectInfo'),
				], // children
			]), // Container,
		]);
	}
}
?>