<?php
/**
* Project :: Summary Information
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/$id/info.summary
*/

function project_info_summary($self, $projectInfo) {
	$tpid = $projectInfo->tpid;
	$orgId = $projectInfo->orgid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);


	$fundInfo = R::Model('project.fund.get', $orgId);

	$options=options('project');


	$ret .= R::View('project.statusbar', $projectInfo)->build();

	$ui=new Ui(NULL, 'ui-menu');
	$ui->add('<a class="" href="'.url('project/'.$tpid.'/info.result').'"><i class="icon -material">find_in_page</i><span>ผลการดำเนินโครงการ</span></a>');
	$ui->add('<a class="" href="'.url('project/'.$tpid.'/eval.valuation').'"><i class="icon -material">find_in_page</i><span>ประเมินคุณค่า</span></a>');
	$ui->add('<a class="" href="'.url('project/'.$tpid.'/operate').'"><i class="icon -material">find_in_page</i><span>รายงานการเงิน</span></a>');
	$ui->add('<a class="" href="'.url('project/'.$tpid.'/finalreport').'"><i class="icon -material">find_in_page</i><span>รายงานฉบับสมบูรณ์</span></a>');

	if ($fundInfo->right->trainer) {
			$ui->add('<a class="btn" href="'.url('paper/'.$tpid.'/owner/close',array('act'=>'comment')).'"><i class="icon -edit"></i> บันทึกความคิดเห็น</a>');
	}

	$self->theme->sidebar = '<header class="header"><h3>สรุปโครงการ</h3></header>'.$ui->build();

	$self->theme->sidebar .= R::PageWidget('project.info.evalform', [$projectInfo])->build();

	$ret .= '<div class="box">';
	$ret .= '<p><b>สถานะโครงการ</b> '.$projectInfo->info->project_status.'</p>';
	$ret .= __project_info_summary_doc($projectInfo, $fundInfo);
	$ret .= '</div><!-- box -->';


	//$ret.=print_o($topic,'$topic');

	$ret .= '<style type="text/css">
	.button.-adddoc {margin:10px 0 10px 0; position: relative; right:0; display: inline-block;}
	.form-item.btn.-upload.-fill {padding: 4px 8px; width: calc(100% - 48px); margin: 0 auto; display: block;}
	</style>';
	return $ret;
}

function __project_info_summary_doc($projectInfo, $fundInfo) {
	$tpid = $projectInfo->tpid;

	$isRight = user_access('administer projects')
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER','OWNER'))
		|| $fundInfo->right->edit;
	$isEdit = $projectInfo->info->project_status == 'กำลังดำเนินโครงการ' && $isRight;

	$ret .= '<div id="project-info-docs">';

	$docDb=mydb::select('SELECT f.*, u.`name` poster FROM %topic_files% f LEFT JOIN %users% u USING(`uid`) WHERE `tpid` = :tpid AND `type` = "doc" AND (`cid` IS NULL OR `cid` = 0) ORDER BY `fid`',':tpid',$tpid);
	//$ret .= print_o($docDb,'$docDb');

	if ($docDb->_num_rows) {
		$ret .= '<h3>ไฟล์โครงการ (ส่วนที่ 3)</h3>';
		$tableDoc = new Table();
		$tableDoc->thead=array('no'=>'ลำดับ','วันที่ส่งเอกสาร','ชื่อเอกสาร','ผู้ส่ง','','icons -hover-parent'=>'&nbsp;&nbsp;');
		$propersalNo=0;
		$no = 0;
		foreach ($docDb->items as $item) {
			if ($item->title != 'ไฟล์โครงการ (ส่วนที่ 3)') continue;
			if ($item->title=="ไฟล์ข้อเสนอโครงการ") ++$propersalNo;
			//else continue;
			$tableDoc->rows[]=array(
				++$no,
				sg_date($item->timestamp,'ว ดด ปปปป'),
				$item->title.($item->title=="ไฟล์ข้อเสนอโครงการ"?' ครั้งที่ '.$propersalNo:'').' (.'.sg_file_extension($item->file).')',
				$item->poster,
				'<a href="'.cfg('url').'upload/forum/'.$item->file.'" target="_blank"><i class="icon -download"></i></a>',
				($isEdit && strtotime($item->timestamp) > strtotime('-7 day')) || $isAdmin ? '<nav class="nav -icons -hover"><a href="'.url('project/'.$tpid.'/info/docs.delete/'.$item->fid).'" class="sg-action" data-removeparent="tr" data-rel="this" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?"><i class="icon -delete"></i></nav></a>' : ''
			);
		}
		$ret.=$tableDoc->build();
	}

	// Upload document form
	if ($isEdit) {

		$form = new Form([
			'variable' => 'document',
			'action' => url('project/'.$tpid.'/info/docs.upload'),
			'id' => 'project-edit-doc',
			'class' => 'sg-form -upload',
			'enctype' => 'multipart/form-data',
			'rel' => 'notify',
			'done' => 'load',
			'children' => [
				'prename' => ['type'=>'hidden','value'=>'project_'.$tpid.'_'],
				'tagname' => ['type'=>'hidden','value'=>'project-docs'],
				'title' => [
					'type' => 'select',
					'label' => 'อัพโหลดไฟล์ประกอบโครงการ',
					'class' => '-fill',
					'options' => [
						'ไฟล์โครงการ (ส่วนที่ 3)'=>'ไฟล์โครงการ (ส่วนที่ 3)',
						'ไฟล์ข้อเสนอโครงการ'=>'ไฟล์ข้อเสนอโครงการ',
						'ไฟล์โครงการฉบับสมบูรณ์'=>'ไฟล์โครงการฉบับสมบูรณ์',
						'ไฟล์กรอบแนวคิด'=>'ไฟล์กรอบแนวคิด',
						'ไฟล์รายงานการเงินโครงการประจำงวด 1'=>'ไฟล์รายงานการเงินโครงการประจำงวด 1',
						'ไฟล์รายงานการเงินโครงการประจำงวด 2'=>'ไฟล์รายงานการเงินโครงการประจำงวด 2',
						'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 1'=>'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 1',
						'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 2'=>'ไฟล์รายงานความก้าวหน้าโครงการประจำงวด 2',
						'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์'=>'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์',
					],
				],
				'document' => [
					'type' => 'file',
					'name' => 'document',
					'class' => '-fill',
					'label' => '<i class="icon -material">attach_file</i>เลือกไฟล์สำหรับอัพโหลด',
					'container' => ['class' => 'btn -upload -fill'],
				],
				// 'document1' => [
				// 	'name' => 'document',
				// 	'type' => 'file',
				// ],
				'save' => [
					'type'=>'button',
					'value'=>'<i class="icon -upload -white"></i><span>อัพโหลดไฟล์</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				'<strong>ข้อกำหนดในการส่งไฟล์ไฟล์รายละเอียดโครงการ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li></ul>',
			],
		]);

		$ret .= $form->build();
	}

	if ($docDb->_num_rows) {
		$ret .= '<h3>ไฟล์โครงการอื่น ๆ</h3>';
		$tableDoc = new Table();
		$tableDoc->thead=array('no'=>'ลำดับ','วันที่ส่งเอกสาร','ชื่อเอกสาร','ผู้ส่ง','','icons -hover-parent'=>'&nbsp;&nbsp;');
		$propersalNo=0;
		$no = 0;
		foreach ($docDb->items as $item) {
			if ($item->title == 'ไฟล์โครงการ (ส่วนที่ 3)') continue;
			if ($item->title=="ไฟล์ข้อเสนอโครงการ") ++$propersalNo;
			//else continue;
			$tableDoc->rows[]=array(
				++$no,
				sg_date($item->timestamp,'ว ดด ปปปป'),
				$item->title.($item->title=="ไฟล์ข้อเสนอโครงการ"?' ครั้งที่ '.$propersalNo:'').' (.'.sg_file_extension($item->file).')',
				$item->poster,
				'<a href="'.cfg('url').'upload/forum/'.$item->file.'" target="_blank"><i class="icon -download"></i></a>',
				($isEdit && strtotime($item->timestamp) > strtotime('-7 day')) || $isAdmin ? '<nav class="nav -icons -hover"><a href="'.url('project/'.$tpid.'/info/docs.delete/'.$item->fid).'" class="sg-action" data-removeparent="tr" data-rel="this" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?"><i class="icon -delete"></i></nav></a>' : ''
			);
		}
		$ret.=$tableDoc->build();
	}
	$ret .= '</div>';
	//$ret .= print_o($projectInfo).print_o($fundInfo);
	return $ret;
}
?>