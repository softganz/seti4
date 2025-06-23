<?php
/**
* Paper   :: Edit comment Form
* Created :: 2019-06-02
* Modify  :: 2025-06-23
* Version :: 4
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

use Paper\Model\PaperModel;

function paper_edit_comment($self, $topicInfo = NULL, $commentId = NULL) {
	if (empty($topicInfo->tpid)) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');
	if (empty($commentId)) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');

	$comment = NodeModel::getCommentById($commentId);

	if (empty($commentId)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่มีข้อมูลความเห็นที่ต้องการลบ');
	if (empty($comment->cid)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่พบความเห็นที่ต้องการลบ');
	if (!($topicInfo->right->edit || (i()->ok && $comment->uid === i()->uid))) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>EDIT COMMENT</h3></header>';

	load_lib('class.editor.php','lib');

	$para=para(func_get_args());

	$ret = '<h2>Edit comment</h2>';

	$form = new Form([
		'variable' => 'comment',
		'action' => url('api/paper/'.$tpid.'/comment.update/'.$commentId),
		'id' => 'edit-topic',
		// 'class' => 'sg-form',
		'rel' => 'none',
		'done' => 'load',
		'enctype' => 'multipart/form-data',
		'children' => [
			'name' => cfg('member.name_alias') ? [
				'type' => 'text',
				'label' => 'ชื่อผู้แสดงความคิดเห็น',
				'class' => '-full',
				'require' => true,
				'value' => $comment->name,
			] : NULL,
			'comment' => [
				'type' => 'textarea',
				'label' => 'ข้อความ',
				'rows' => 6,
				'class' => '-fill',
				'require' => true,
				'value' => $comment->comment,
				'pretext' => editor::softganz_editor('edit-comment-comment'),
			],
			'delete_photo' => $comment->photo ? [
				'name' => 'delete_photo',
				'type' => 'checkbox',
				'options' => ['yes' => '<strong>'.tr('Delete photo').'</strong>'],
			] : NULL,
			'photo' => user_access('upload photo') ? [
				'name' => 'photo',
				'type' => 'file',
				'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน '.cfg('photo.max_file_size').'KB </li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพที่อยู่ในความคิดเห็นของหัวข้อนั้น ๆ จะถูกลบทิ้งด้วย</li></ul>',
			] : NULL,
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>