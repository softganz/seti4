<?php
/**
* View Paper Edit Comment
*
* @param Object $topicInfo
* @param Int $commentId
* @return String
*/

$debug = true;

function view_paper_edit_editcomment($topicInfo, $commentId) {
	load_lib('class.editor.php','lib');

	$comment = PaperModel::get_comment_by_id($commentId);

	if (post('cancel')) {
		$comment = PaperModel::get_comment_by_id($commentId);
		$ret .= R::View('paper.comment.render', $comment);
		return $ret;
	} else if (post('comment')) {
		$post = (object) post('comment', _TRIM+_STRIPTAG);
		$post->cid = $commentId;

		$stmt = mydb::create_update_cmd('%topic_comments%', $post, 'cid = :cid');
		mydb::query($stmt, $post);

		//$ret .= mydb()->_query;
		//$ret .= print_o($post,'$post');

		if ($_POST['delete_photo']) {
			$photo = mydb::select('SELECT * FROM %topic_files% WHERE tpid='.$comment->tpid.' AND cid='.$commentId.' AND `type`="photo" LIMIT 1');
			if ($photo->file) {
				$oldfile=cfg('paper.upload.photo.folder').$photo->file;
				if (file_exists($oldfile) && is_file($oldfile)) unlink($oldfile);
			}
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$photo->fid.' LIMIT 1');
		}

		//$ret .= print_o($_FILES,'$_FILES');
		// save upload photo
		if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
			$photo = mydb::select('SELECT * FROM %topic_files% WHERE tpid='.$comment->tpid.' AND cid='.$commentId.' AND `type`="photo" LIMIT 1');
			$folder=cfg('paper.upload.photo.folder');

			$upload=new classFile($_FILES['photo'],$folder,cfg('photo.file_type'));
			if (!$upload->valid_format()) $error[]='Invalid upload file format';

			if (!user_access('administer contents,administer papers') && !$upload->valid_size(cfg('photo.max_file_size')*1024)) $error[]='Invalid upload file size :maximun file size is '.cfg('photo.max_file_size').' KB.';
			if ($photo->_num_rows) {
				$oldfile=cfg('paper.upload.photo.folder').$photo->file;
				$ret .= 'remove '.$oldfile;
				if (file_exists($oldfile) && is_file($oldfile)) unlink($oldfile);
			}
			if ($upload->duplicate()) $upload->generate_nextfile();
			if (!$upload->copy()) $error[]='Save file error (unknown error)';
			if ($error) return $ret.message('error',$error);
			$photo_upload=$upload->filename;
			if ($photo->_num_rows) {
				mydb::query('UPDATE %topic_files% SET file="'.$photo_upload.'" WHERE fid='.$photo->fid.' LIMIT 1');
			} else {
				$photo->tpid=$comment->tpid;
				$photo->cid=$comment->cid;
				$photo->type='photo';
				if (i()->ok) $photo->uid=i()->uid;
				$photo->file=$photo_upload;
				$photo->timestamp='func.NOW()';
				$photo->ip=ip2long(GetEnv('REMOTE_ADDR'));
				mydb::query(mydb::create_insert_cmd('%topic_files%',$photo),$photo);
			}
		}


		BasicModel::watch_log('paper','Paper comment edit','Edit comment id '.$comment->cid.' of <a href="'.url('paper/'.$comment->tpid.'#comment-'.$comment->cid).'">paper/'.$comment->tpid.'</a>');

		$comment = PaperModel::get_comment_by_id($commentId);
		$ret .= R::View('paper.comment.render', $comment);
		return $ret;
	}

	$ret .= '<div id="test" data-url="'.url('paper/'.$comment->tpid).'"></div>';

	$form = new Form('comment', url(q()), 'edit-comment', 'sg-form');
	$form->header('<h3>Edit comment</h3>');
	$form->addConfig('enctype', 'multipart/form-data');
	//$form->addData('checkValid', true);
	//$form->addData('complete', 'closebox');
	$form->addData('rel', 'parent:.message-body');

	if (cfg('member.name_alias')) {
		$form->addField('name',
						array(
							'type' => 'text',
							'label' => 'ชื่อผู้แสดงความคิดเห็น',
							'class' => '-fill',
							'require' => true,
							'value' => $comment->name,
						)
					);
	}

	$form->addField('comment',
					array(
						'type' => 'textarea',
						'label' => 'ข้อความ',
						'class' => '-fill',
						'rows' => 6,
						'require' => true,
						'value' => $comment->comment,
						'pretext' => editor::softganz_editor('edit-comment-comment'),
					)
				);

	if ($comment->photo) {
		$form->delete_photo->name='delete_photo';
		$form->delete_photo->type='checkbox';
		$form->delete_photo->options['yes']='<strong>'.tr('Delete photo').'</strong>';
	}

	if (user_access('upload photo')) {
		$form->addField('photo',
						array(
							'name' => 'photo',
							'label' => 'ภาพประกอบ',
							'type' => 'file',
							'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน '.cfg('photo.max_file_size').'KB </li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพที่อยู่ในความคิดเห็นของหัวข้อนั้น ๆ จะถูกลบทิ้งด้วย</li></ul>',
						)
					);
	}

	$form->addField('sumbit',
					array(
						'type' => 'button',
						'container' => array('class' => '-sg-text-right'),
						'value' => '<i class="icon -material">done_all</i><span>'.tr('Save').'</span>',
						'pretext' => '<a class="btn -link sg-action" href="'.url('paper/edit/'.$topicInfo->tpid.'/editcomment/'.$commentId,array('cancel'=>'yes')).'" data-rel="#message-id-'.$commentId.' .message-body"><i class="icon -material -gray">cancel</i><span>'.tr('Cancel').'</span></a>',
					)
				);

	$ret .= $form->build();

	//$ret .= print_o(post(),'post()');
	return $ret;
}
?>