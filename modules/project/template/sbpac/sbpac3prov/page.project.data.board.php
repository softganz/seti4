<?php
function project_data_board($self,$tpid=NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, 'โครงสร้างคณะกรรมการหมู่บ้าน', NULL, $projectInfo);


	$ret.='<p class="notify">กำลังดำเนินการ</p>';

	$ui = new Ui('div', 'ui-card -board');

	$cardList = array(
							'ผู้ใหญ่บ้าน/ชื่อ-สกุล<br />โทร<br />ประธานคณะกรรมการหมู่บ้าน',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านอำนวยการ',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านการปกครองและรักษาความปลอดภัย',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านแผนพัฒนาหมู่บ้าน',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านส่งเสริมเศรษฐกิจ',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านสังคม สิ่งแวดล้อม และสาธารณสุข',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านศึกษา ศาสนา และวัฒนธรรม',
							'ชื่อ-สกุล<br />โทร<br />หัวหน้าคณะทำงาน<br />ด้านอื่นๆ',
						);
	foreach ($cardList as $item) {
		$cardItem = '<img src="/library/img/photography.png" width="120">';
		$cardItem .= '<p>'.$item.'</p>';
		$ui->add($cardItem);
	}
	$ret.=$ui->build();

	$ret.='<style type="text/css">
	.ui-card.-board {text-align: center;}
	.ui-card.-board .ui-item {width: 50%; float: left;}
	.ui-card.-board .ui-item:first-child {width: 100%;}
	</style>';
	return $ret;
}
?>