<?php
/**
* Paper Document Upload
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_docs_add($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;


	$ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.url('paper/'.$tpid.'/edit').'" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>UPLOAD DOCUMENT</h3></header>';

		$form = new Form('info',url('paper/info/api/'.$tpid.'/doc.upload'),'edit-topic');
		$form->addConfig('enctype','multipart/form-data');
		//$form->addData('rel', 'refresh');
		//$form->addData('done', 'close');

		$form->addField(
				'doc',
				array(
					'name' => 'doc',
					'label' => '<i class="icon -material">attach_file</i>Select document file to upload',
					'type' => 'file',
					'size' => 50,
					'require' => true,
					'container' => array('class' => 'btn -upload'),
				)
			);

		$form->addText('<div id="edit-document-filename"></div>');

		if (user_access('administer papers')) {
			$form->addField(
					'norename',
					array(
						'type' => 'checkbox',
						'options' => array('1' => 'ไม่เปลี่ยนชื่อไฟล์'),
					)
				);
		}

		$form->addField(
				'title',
				array(
					'type' => 'text',
					'label' => 'Document title',
					'class' => '-fill',
					'maxlength' => 150,
					'placeholder' => 'ระบุชื่อเอกสาร',
				)
			);

		$form->addField(
				'description',
				array(
					'type' => 'textarea',
					'label' => 'Document description',
					'class' => '-fill',
					'rows' => 3,
					'placeholder' => 'บรรยายรายละเอียดเพิ่มเติม',
				)
			);

		$maxsize = intval(ini_get('post_max_size')) < intval(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

		$form->addField(
							'submit',
							array(
								'type'=>'button',
								'name'=>'upload',
								'value'=>'<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
								'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$tpid.'/docs').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => '{class: "-sg-text-right"}',
								)
							);

		$form->addText('ข้อกำหนดในการส่งไฟล์เอกสารประกอบ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li>
			<li>หากต้องการเพิ่มไฟล์เอกสารประกอบ , แก้ไข หรือ ลบทิ้ง สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดเอกสารประกอบในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์เอกสารประกอบทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul>');

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
	$("#edit-doc").change(function() {
		var f = $(this).val().replace(/.*[\/\\\\]/, "")
		$("#edit-document-filename").text("เลือกไฟล์ : "+f)
		$("#edit-info-title").val(f)
	})
	</script>';
	return $ret;
}
?>