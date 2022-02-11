<?php
/**
* Project :: Proposal Docs Information
* Created 2021-11-05
* Modify  2021-11-05
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/{id}/info.docs
*/

// import('widget:project.follow.nav.php');

$debug = true;

class ProjectProposalInfoDocs extends Page {
	var $projectId;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->right = (Object) [
			'admin' => $this->proposalInfo->RIGHT & _IS_ADMIN,
			'edit' => $this->proposalInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$docDb = mydb::select(
			'SELECT f.*, u.`name` poster
			FROM %topic_files% f
				LEFT JOIN %users% u USING(`uid`)
			WHERE `tpid` = :projectId AND `type` = "doc" AND `tagname` = "project-proposal-docs" AND (`cid` IS NULL OR `cid` = 0)
			ORDER BY `fid`',
			[':projectId' => $this->projectId]
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
				// 'navigator' => new ProjectFollowNavWidget($this->proposalInfo),
			]),
			'body' => new Container([
				'id' => 'project-info-docs',
				'class' => 'project-info-docs',
				'children' => [
					$docDb->_num_rows ? new Table([
						'thead' => [
							'no' => '',
							'ชื่อเอกสาร',
							'ผู้ส่ง',
							//'วันที่ส่งเอกสาร',
							'icons -nowrap -hover-parent' => ''
						], // thead
						'children' => array_map(function($item) {
							static $no = 0;
							static $propersalNo = 0;

							if ($item->title == "ไฟล์ข้อเสนอโครงการ") ++$propersalNo;

							$ui = new Ui('span');
							$ui->add('<a href="'.cfg('url').'upload/forum/'.$item->file.'" target="_blank"><i class="icon -material">cloud_download</i></a>');
							if ($this->right->edit) {
								if ((strtotime($item->timestamp) > strtotime('-30 day')) || $this->right->admin) {
									$ui->add('<a class="sg-action" href="'.url('project/proposal/api/'.$this->projectId.'/docs.delete/'.$item->fid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบไฟล์" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
								} else {
									$ui->add('<a class="-disabled -hover"><i class="icon -material -gray">cancel</i></a>');
								}
							}
							$nav = '<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';

							return [
								++$no,
								'<a href="'.cfg('url').'upload/forum/'.$item->file.'" target="_blank">'.$item->title.($item->title=="ไฟล์ข้อเสนอโครงการ"?' ครั้งที่ '.$propersalNo:'').' (.'.sg_file_extension($item->file).')'.'</a>',
								$item->poster,
								//sg_date($item->timestamp,'ว ดด ปปปป'),
								$nav,
							];
						}, $docDb->items),
					]) : NULL, // Table

					$this->right->edit ? new Form([
						'variable' => 'document',
						'action' => url('project/proposal/api/'.$this->projectId.'/docs.upload'),
						'id' => 'project-edit-doc',
						'class' => 'sg-form -upload -no-print project-form-upload-docs',
						'enctype' => 'multipart/form-data',
						'rel' => 'notify',
						'done' => 'load->replace:#project-info-docs:'.url('project/proposal/'.$this->projectId.'/info.docs'),
						'children' => [
							'prename' => ['type'=>'hidden','value'=>'project_'.$this->projectId.'_'],
							'tagname' => ['type'=>'hidden','value'=>'project-proposal-docs'],
							'title' => [
								'type' => 'select',
								'label' => 'อัพโหลดไฟล์ประกอบ:',
								'options' => (function(){
									$options = [];
									$docsList = cfg('project')->docs;
									foreach(explode(',', cfg('project')->proposal->docsUse) as $docKey => $docTitle) {
										$docTitle = trim($docTitle);
										if ($docsList->{$docTitle}) $options[$docTitle] = $docsList->{$docTitle};
									}
									return $options;
								})(),
							],
							'document' => [
								'type' => 'file',
								'name' => 'document',
								'label' => '<i class="icon -material">attach_file</i><span>เลือกไฟล์สำหรับอัพโหลด</span>',
								'container' => ['class' => 'btn -upload'],
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">cloud_upload</i><span>อัพโหลดไฟล์</span>',
							],

							// $maxsize = intval(ini_get('post_max_size')) < intval(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

							'<div class="-condition"><strong>ข้อกำหนดในการส่งไฟล์ประกอบ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li></ul></div>',

							'<script type="text/javascript">
							$("#edit-document").change(function() {
								let fileName = $(this).val().replace(/.*[\/\\\\]/, "")
								let period = fileName.lastIndexOf(".")
								let fileExtension = fileName.substring(period)
								fileName = fileName.substring(0, period)
								let length = fileName.length
								fileName = (length > 20 ? fileName.substring(0,10) + "..." + fileName.substring(length-10,length) : fileName) + fileExtension
								$("#form-item-edit-document>label>span").text(fileName)
							})
							</script>'
						], // children
					]) : NULL, // Form
				], // children
			]), // Widget
		]);
	}
}
?>