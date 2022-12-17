<?php
/**
* Module Method
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_photo_add($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.url('paper/'.$tpid.'/edit').'" data-rel="close"><i class="icon -material">arrow_back</i></a></nav><h3>UPLOAD PHOTO</h3></header>';
			// Show upload form

	$rule = '<p><strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong></li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>หากต้องการเพิ่มชื่อภาพ , คำอธิบายภาพ หรือ ส่งภาพเพิ่มเติม สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดภาพในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul></p>';

		$ret.='<div class="sg-tabs"><ul class="tabs"><li class="-active"><a href="#single">'.tr('Upload Photo').'</a></li><li><a href="#multiple">'.tr('Upload multiple photos').'</a></li></ul>';
		if ($error) $ret.='<p class="notify">'.$error.'</p>';

		$single = new Form('info',url('paper/info/api/'.$tpid.'/photo.add'),'edit-topic');
		$single->addConfig('enctype','multipart/form-data');
		$single->addData('rel', 'refresh');
		$single->addData('done', 'close');

		$single->addField(
							'photo',
							array(
								'type'=>'file',
								'name'=>'photo',
								'label'=>'<i class="icon -material">attach_file</i>{tr:Select photo file to upload}',
								'size'=>50,
								'container' => array('class' => 'btn -upload'),
								)
							);

		$single->addText('<div id="edit-photo-filename"></div>');

		if (user_access('administer papers')) {
			$single->addField(
					'norename',
					array(
						'type' => 'checkbox',
						'options' => array('1' => 'ไม่เปลี่ยนชื่อไฟล์'),
					)
				);
		}

		$single->addField(
							'title',
							array(
								'type'=>'text',
								'label'=>'{tr:Photo title}',
								'class'=>'-fill',
								'maxlength'=>150,
								'value'=>$photo->title
								)
							);

		$single->addField(
							'description',
							array(
								'type'=>'textarea',
								'label'=>'{tr:Photo description}',
								'class'=>'-fill',
								'rows'=>3,
								'value'=>$photo->description
								)
							);

		$single->addField(
							'submit',
							array(
								'type'=>'button',
								'name'=>'upload',
								'value'=>'<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
								'container' => '{class: "-sg-text-right"}',
								)
							);

		$single->addText($rule);



		$multiple=new Form('topic',url('paper/info/api/'.$tpid.'/photo.add'),'edit-topic');
		$multiple->addConfig('enctype','multipart/form-data');

		$multiple->addField(
							'photo',
							array(
								'type'=>'file',
								'name'=>'photo[]',
								'label'=>'<i class="icon -material">attachment</i>{tr:Select photo file to upload}',
								'size'=>50,
								'multiple'=>true,
								//'container' => array('class' => 'btn -upload'),
								)
							);

		$multiple->addField(
							'submit',
							array(
								'type'=>'button',
								'name'=>'upload',
								'value'=>'<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
								'container' => '{class: "-sg-text-right"}',
								)
							);

		$multiple->addText($rule);

		$ret.='<div id="single">'.$single->build().'</div>';
		$ret.='<div id="multiple" class="-hidden">'.$multiple->build().'</div>';

		$ret.='</div>';

		$ret.='<style type="text/css">
		.sg-tabs>div {margin:0;padding:16px; border: 1px #ccc solid; border-top: none;}
		</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-photo").change(function() {
		var f = $(this).val().replace(/.*[\/\\\\]/, "")
		$("#edit-photo-filename").text("เลือกไฟล์ : "+f)
	})
	</script>';

	/*
	if ($para->delete && SG\confirm()) {
		$result=PaperModel::delete_photo($para->delete);
		//$ret.=print_o($result,'$result');

		if ($result->error) {
			$ret.=message('error',$result->error);
		} else {
			$ret.=notify('Delete photo complete : Photo file <em>'.$result->deleted->name.'</em> has been deleted.');
			$topic=paper_BasicModel::get_topic_by_id($topic->tpid);
		}

		if (user_access('upload photo')) $ret.=paper_edit::__edit_photo_add($topic,$para);
	} else if ($para->modify) {
		$ret.=paper_edit::__edit_photo_modify($topic,$para);
		if (post('topic') && user_access('upload photo')) {
			unset($_POST);
			$ret.=paper_edit::__edit_photo_add($topic);
		}
	} else {
		if (user_access('upload photo')) $ret.=paper_edit::__edit_photo_add($topic,$para);
	}
	*/

	return $ret;
}
?>