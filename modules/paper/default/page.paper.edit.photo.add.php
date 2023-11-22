<?php
/**
* Paper   :: Upload Photo
* Created :: 2019-01-01
* Modify  :: 2023-07-24
* Version :: 2
*
* @param Object $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.photo.add
*/

class PaperEditPhotoAdd extends Page {
	var $nodeId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeInfo' => $nodeInfo,
			'nodeId' => $nodeInfo->tpid,
		]);
	}

	function build() {
		if (!$this->nodeId) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'UPLOAD PHOTO',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new TabBar([
						'class' => 'paper-upload-tabs',
						'attribute' => ['data-width' => 100],
						'children' => [
							[
								'id' => 'single',
								'active' => true,
								'action' => new Button(['href' => '#single', 'text' => tr('Upload photo')]),
								'content' => $this->uploadSingleForm(),
							],
							[
								'id' => 'multiple',
								'action' => new Button(['href' => '#multiple', 'text' => tr('Upload multiple photos')]),
								'content' => $this->uploadMultipleForm(),
							],
						], // children
					]), // TabBar
					$this->script(),
				], // children
			]), // Widget
		]);
	}

	function uploadSingleForm() {
		return new Form([
			'action' => url('api/paper/'.$this->nodeId.'/photo.add'),
			'enctype' => 'multipart/form-data',
			'id' => 'edit-topic',
			'class' => 'sg-form -upload',
			'rel' => 'notify',
			'done' => 'close | load',
			'children' => [
				'photo' => [
					'type' => 'file',
					'label' => '<i class="icon -material">attach_file</i>{tr:Select photo file to upload}',
					'size' => 50,
					'container' => ['class' => 'btn -upload'],
				],
				'<div id="edit-photo-filename"></div>',
				'noRename' => user_access('administer papers') ? [
					'type' => 'checkbox',
					'options' => ['1' => 'ไม่เปลี่ยนชื่อไฟล์'],
				] : NULL,
				'title' => [
					'type' => 'text',
					'label' => '{tr:Photo title}',
					'class' => '-fill',
					'maxlength' => 150,
					'value' => $photo->title
				],
				'description' => [
					'type' => 'textarea',
					'label' => '{tr:Photo description}',
					'class' => '-fill',
					'rows' => 3,
					'value' => $photo->description
				],
				'submit' => [
					'type' => 'button',
					'value' => '<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				$this->rule(),
			], // children
		]);
	}

	function uploadMultipleForm() {
		return new Form([
			'action' => url('api/paper/'.$this->nodeId.'/photo.add'),
			'enctype' => 'multipart/form-data',
			'id' => 'edit-topic',
			'class' => 'sg-form -upload',
			'rel' => 'notify',
			'done' => 'close | load',
			'children' => [
				'photo' => [
					'type' => 'file',
					'name' => 'photo[]',
					'label' => '<i class="icon -material">attachment</i>{tr:Select photo file to upload}',
					'size' => 50,
					'multiple' => true,
					//'container' => array('class' => 'btn -upload'),
				],
				'submit' => [
					'type' => 'button',
					'value' => '<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				$this->rule(),
			], // children
		]); // Form
	}

	function rule() {
		return '<p><strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong></li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>หากต้องการเพิ่มชื่อภาพ , คำอธิบายภาพ หรือ ส่งภาพเพิ่มเติม สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดภาพในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul></p>';
	}

	function script() {
		return '<style type="text/css">
		.sg-tabs>div {margin:0;padding:16px; border: 1px #ccc solid; border-top: none;}
		</style>
		<script type="text/javascript">
		$("#edit-photo").change(function() {
			var f = $(this).val().replace(/.*[\/\\\\]/, "")
			$("#edit-photo-filename").text("เลือกไฟล์ : "+f)
		})
		</script>';
	}
}
?>