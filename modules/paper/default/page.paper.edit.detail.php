<?php
/**
* Paper Edit Detail
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_detail($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	load_lib('class.editor.php','lib');

	if ($topicInfo->property->input_format=='php' && !user_access('input format type php')) return message('error','Access denied:ไม่สามารถแก้ไขหัวข้อที่มีรูปแบบข้อมูลเป็นโปรแกรมได้');

	// if (post('preview')) {
	// 	$post=(object)post('topic',_TRIM+_STRIPTAG);
	// 	$topic->title=$post->title;
	// 	$topic->body=$post->body;
	// 	if ($post->title) $this->theme->title=$post->title;
	// 	$ret .= '<div id="topic-preview" class="preview">'._NL;
	// 	$ret .= '<h3>Edit preview : หัวข้อนี้ยังไม่มีการบันทึกจนกว่าท่านจะเลือก Save เพื่อบันทึกข้อมูล</h3>';
	// 	$ret .= sg_text2html($post->body);
	// 	$ret .= '<div style="clear:both;"></div>'._NL;
	// 	$ret .= '</div><!--topic-preview-->'._NL;
	// } else if (post('save')) {
	// 	$data->topic = (object)post('topic',_TRIM+_STRIPTAG);
	// 	$data->detail = sg_clone($data->topic);
	// 	$topic->title = $data->detail->title;
	// 	$topic->body = $data->detail->body;
	// 	if (sg::is_spam_word($data->detail)) {
	// 		$ret .= message('error','มีข้อความที่ไม่เหมาะสมอยู่ในสิ่งที่ป้อนมา');
	// 	} else {
	// 		$result = paper_model::_edit_update($topic,$para,$data);
	// 		return $result;
	// 	}
	// } else if ($_POST['cancel']) {
	// 	location('paper/'.$topic->tpid);
	// }




	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>แก้ไขรายละเอียด</h3></header>';

	$type = model::get_topic_type($topicInfo->info->type);

	$form = new Form([
		'variable' => 'topic',
		'action' => url('paper/info/api/'.$tpid.'/update'),
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => 'notify',
		'done' => 'load | close',
		'children' => [
			'title' => $type->has_title ? [
				'type' => 'text',
				'name' => 'topic[title]',
				'label' => $type->title_label,
				'class' => '-fill',
				'maxlength' => 150,
				'require' => true,
				'value' => $topicInfo->title,
			] : NULL,
			'body' => $type->has_body ? [
				'type' => 'textarea',
				'name' => 'detail[body]',
				'label' => $type->body_label,
				'class' => '-fill',
				'rows' => 16,
				'value' => $topicInfo->info->body,
				'pretext' => editor::softganz_editor('edit-detail-body'),
				'description' => 'คำแนะนำ : เนื่องจากได้มีการเปลี่ยนแปลงวิธีการขึ้นบรรทัดใหม่ ซึ่งมีรายละเอียดดังนี้<ul><li>วิธีการขึ้นบรรทัดใหม่โดยไม่เว้นช่องว่างระหว่างบรรทัด ให้เคาะเว้นวรรค (Space bar) ที่ท้ายบรรทัดจำนวนหนึ่งครั้ง</li><li>วิธีการขึ้นย่อหน้าใหม่ซึ่งจะมีการเว้นช่องว่างห่างจากบรรทัดด้านบนเล็กน้อย ให้เคาะ Enter จำนวน 2 ครั้ง</li><li>หากข้อความของท่านยาวเกินไป จะทำให้ไม่สามารถนำข้อความทั้งหมดไปแสดงในหน้าแรก ให้ใส่ &lt;!--break--&gt แทรกไว้ในตำแหน่งที่ต้องการให้ตัดไปแสดงผล</li></ul>',
			] : NULL,
			$topicInfo->photos ? (function($topicInfo) {
				$photo_list = _NL.'<div id="edit-topic-body-control-photo" class="editor" title="edit-detail-body">';
				foreach ($topicInfo->photos as $photo) {
					$photo_title = tr('Photo name').' : '.SG\getFirst($photo->pic_name,substr($photo->file,0,strrpos($photo->file,'.')));
					$photo_desc = tr('Photo Description').' : '.SG\getFirst($photo->pic_name,substr($photo->file,0,strrpos($photo->file,'.')));
					if ($topicInfo->property->input_format == 'markdown') {
						$onclick = 'editor.insert("![ '.$photo_desc.' ]('.$photo->_url.' \"'.$photo_title.'\" '.(cfg('topic.photo.detail.class')?' class=\"'.cfg('topic.photo.detail.class').'\"':'').')");return false';
					} else {
						$onclick = 'editor.insert("<img src=\"'.$photo->_url.'\" alt=\"'.htmlspecialchars($photo_desc).'\" title=\"'.htmlspecialchars($photo_title).'\"'.(cfg('topic.photo.detail.class')?' class=\"'.cfg('topic.photo.detail.class').'\"':'').' />");return false';
					}
					$photo_list .= '<img src="'.$photo->_src.'" class="photo" onclick=\''.$onclick.'\' alt="'.$photo->file.'" title="'.sg_client_convert('คลิกเพื่อวางภาพนี้').' -> '.$photo->file.' ('.$photo->_size->width.'x'.$photo->_size->height.' pixels '.number_format($photo->_filesize).' bytes)" height="50" /> ';
				}
				$photo_list .= '</div>'._NL;

				return $photo_list;
			})($topicInfo) : NULL,

			(function($tpid) {
				$doc_list = '';
				$docs = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "doc"',':tpid', $tpid);

				if ($docs->_num_rows) {
					$doc_list .= _NL.'<div id="edit-topic-body-control-docs" class="editor" title="edit-detail-body">';
					foreach ($docs->items as $doc) {
						$docDesc = preg_replace('/[\"\']/','','"'.SG\getFirst($doc->title,$doc->file).'"');
						$docUrl = cfg('files.log') ? url('files/'.$doc->fid) : cfg('url').'upload/forum/'.sg_urlencode($doc->file);
						if ($topicInfo->property->input_format == 'markdown') {
							$onclick = 'editor.insert("['.$docDesc.']('.$docUrl.')");return false';
						} else {
							$onclick = 'editor.insert("<a href=\"'.$docUrl.'\" title=\"'.htmlspecialchars($docDesc).'\">'.$docDesc.'</a>");return false';
						}
						$doc_list .= '<a class="btn -link" href="javascript:void(0)" onclick=\''.$onclick.'\' title="คลิกเพื่อวางไฟล์นี้">'.$docDesc.'</a> , ';
					}
					$doc_list = rtrim($doc_list,' , ');
					$doc_list .= '</div>'._NL;
				}
				return $doc_list ? $doc_list : NULL;
			})($tpid),

			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);


	$ret .= $form->build();

	//$ret .= print_o($topicInfo,'$topicInfo');
	return $ret;
}
?>