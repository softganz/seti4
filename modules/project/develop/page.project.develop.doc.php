<?php
/**
* Project :: Upload Proposal Document
* Created 2021-01-01
* Modify  2021-05-24
*
* @param Object $self
* @param Int $tpid
* @return String
*
* @usage project/develop/{id}/doc
*/

$debug = true;

function project_develop_doc($self,$tpid) {
	$stmt = 'SELECT t.*,  u.`username`, u.`name`, r.`body`, r.`homepage`, r.`email` prid
			, d.`status`
		FROM %topic% t
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %topic_revisions% r USING (revid)
			LEFT JOIN %project_dev% d ON d.`tpid`=t.`tpid`
		WHERE t.`tpid`=:tpid LIMIT 1';

	$rs=mydb::select($stmt,':tpid',$tpid);

	$isAdmin=user_access('administer projects');
	$isEdit=(in_array($rs->status,array(1,3)) || ($rs->status==2 && $cdate>='2014-04-19 16:00:00' && $cdate<='2014-04-27 16:00:00') ) && (user_access('administer projects','edit own project content',$rs->uid) || project_model::is_trainer_of($tpid));

	$docDb=mydb::select('SELECT * FROM %topic_files% WHERE `tpid`=:tpid AND `type`="doc" AND `cid`=0 AND `tagname`="develop" ',':tpid',$tpid);

	$ret.='<h4>ไฟล์เอกสารประกอบการพัฒนาโครงการ</h4>';

	if ($docDb->_num_rows) {
		$tableDoc = new Table();
		foreach ($docDb->items as $item) {
			$tableDoc->rows[] = [
				++$no,
				$item->title,
				'<a href="'.cfg('url').'upload/forum/'.$item->file.'">ดาวน์โหลด</a>',
				$isEdit || $isAdmin ? '<a href="'.url('project/proposal/'.$tpid.'/info/docs.delete/'.$item->fid).'" class="sg-action" data-rel="notify" data-done="remove:parent tr" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a>':''
			];
		}
		$ret .= $tableDoc->build();
	} else {
		$ret.='<p>ไม่มีไฟล์เอกสารประกอบการพัฒนาโครงการ</p>';
	}
	// Upload document form
	if ($isEdit || $isAdmin) {
		$form = new Form([
			'variable' => 'document',
			'enctype' => 'multipart/form-data',
			'action' => url('project/proposal/'.$tpid.'/info/docs.upload'),
			'id' => 'project-edit-doc',
			'class' => 'sg-form -upload',
			'rel' => 'notify',
			'done' => 'load',
			'children' => [
				//'ret' => ['type' => 'hidden', 'value' => 'project/develop/'.$tpid.'#doc',],
				'tagname' => ['type' => 'hidden', 'value' => 'develop',],
				'title' => [
					'type' => 'select',
					'label' => 'อัพโหลดไฟล์เอกสารประกอบการพัฒนาโครงการ :',
					'options' => [
						'ข้อมูลแผนชุมชน'=>'ข้อมูลแผนชุมชน',
						'ไฟล์พัฒนาโครงการก่อนพิจารณาโครงการ'=>'ไฟล์พัฒนาโครงการก่อนพิจารณาโครงการ',
						'ไฟล์พัฒนาโครงการฉบับสมบูรณ์'=>'ไฟล์พัฒนาโครงการฉบับสมบูรณ์',
					],
				],
				'document' => ['name' => 'document', 'type' => 'file',],
				'save' => ['type' => 'button', 'value' => 'อัพโหลด',],
				'description' => '<strong>ข้อกำหนดในการส่งไฟล์ไฟล์รายละเอียดโครงการ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li></ul>',
			],
		]);

		$ret .= $form->build();
	}
	return $ret;
}
?>