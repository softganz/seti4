<?php
/**
* Paper   :: Upload Document
* Created :: 2019-06-02
* Modify  :: 2023-12-24
* Version :: 2
*
* @param String $topicInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.docs.add
*/

class PaperEditDocsAdd extends Page {
	var $nodeId;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'topicInfo' => $topicInfo,
			'nodeId' => $topicInfo->nodeId,
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		$maxsize = intval(ini_get('post_max_size')) < intval(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'UPLOAD DOCUMENT',
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' =>'info',
						'action' => url('api/paper/'.$this->nodeId.'/doc.add'),
						'id' => 'edit-topic',
						'enctype' => 'multipart/form-data',
						//$form->addData('rel', 'refresh');
						//$form->addData('done', 'close');
						'children' => [
							'doc' => [
								'name' => 'doc',
								'label' => '<i class="icon -material">attach_file</i>Select document file to upload',
								'type' => 'file',
								'size' => 50,
								'require' => true,
								'container' => ['class' => 'btn -upload'],
							],
							'<div id="edit-document-filename"></div>',
							'noRename' => is_admin('paper') ? [
								'type' => 'checkbox',
								'options' => ['1' => 'ไม่เปลี่ยนชื่อไฟล์'],
							] : NULL,
							'title' => [
								'type' => 'text',
								'label' => 'Document title',
								'class' => '-fill',
								'maxlength' => 150,
								'placeholder' => 'ระบุชื่อเอกสาร',
							],
							'description' => [
								'type' => 'textarea',
								'label' => 'Document description',
								'class' => '-fill',
								'rows' => 3,
								'placeholder' => 'บรรยายรายละเอียดเพิ่มเติม',
							],
							'submit' => [
								'type'=>'button',
								'name'=>'upload',
								'value'=>'<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
								'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/docs').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => '{class: "-sg-text-right"}',
							],
							'ข้อกำหนดในการส่งไฟล์เอกสารประกอบ</strong>
							<ul>
							<li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li>
							<li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li>
							<li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li>
							<li>หากต้องการเพิ่มไฟล์เอกสารประกอบ , แก้ไข หรือ ลบทิ้ง สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดเอกสารประกอบในภายหลัง</li>
							<li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์เอกสารประกอบทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li>
							</ul>',
						], // children
					]), // Form

					'<script type="text/javascript">
					$("#edit-doc").change(function() {
						var f = $(this).val().replace(/.*[\/\\\\]/, "")
						$("#edit-document-filename").text("เลือกไฟล์ : "+f)
						$("#edit-info-title").val(f)
					})
					</script>'
				], // children
			]), // Widget
		]);
	}
}
?>