<?php
/**
* View Paper Comment Form
*
* @param Object $topicInfo
* @return String
*/

$debug = true;

function view_paper_comment_form($topicInfo) {
	$tpid = $topicInfo->tpid;

	if (cfg('web.readonly')) return message('status', cfg('web.readonly_message'));;

	load_lib('class.editor.php','lib');


	// clear comment post value on comment post was save complete
	$comment = $para->comment_post_complete ? (object)null : (object)post('comment',_TRIM+_STRIPTAG);

	if ($terms_of_service && cfg('comment.terms_of_service.location') == 'above')
		$ret .= $terms_of_service;


	$ret .= '<a name="form"></a>'._NL;
	if ($_POST['preview']) {
		$ret .= '<div id="comment-preview" class="preview">'._NL;
		$ret .= '<h3>Post preview</h3>';
		$ret .= R::View('paper.comment.render', (object)$_POST['comment']);
		$ret .= '</div><!--comment-preview-->'._NL;
	}

	if (!user_access('post comments')) return $ret;

	if (isset($para->_comment_post))
		$ret .= $para->_comment_post;

	if ($_GET['quote']) {
		$quote = PaperModel::get_comment_by_id($_GET['quote']);
		$comment->comment = '[quote author='.SG\getFirst($quote->name,$quote->owner).' link=paper/'.$quote->tpid.'#comment-10831 date='.sg_date($quote->timestamp,'U').']'._NL;
		$comment->comment .= trim(strip_tags(sg_text2html($quote->comment),'<p><ul><ol><li>,<strong><em><u>'))._NL;
		$comment->comment .= '[/quote]'._NL._NL;
	}



	$ret .= '<!--Comment form -->'._NL._NL;

	$form = new Form([
		'variable' => 'comment',
		'action' => url('paper/'.$tpid.'/comment.post'),
		'id' => 'edit-comment',
		'class' => 'sg-form -upload',
		'enctype' => 'multipart/form-data',
		'rel' => 'none',
		'done' => 'load',
		'checkValid' => true,
		'title' => '<h3>{tr:Post new comment}</h3>',
		'children' => [
			'pid' => $para->comment ? ['type' => 'hidden', 'value' => $para->comment] : NULL,

			'daykey' => !i()->ok ? [
				'name' => 'daykey',
				'type' => 'text',
				'label' => tr('Anti-spam word'),
				'size' => 10,
				'require' => true,
				'posttext' => (function() {
					$daykey_text = '<span class="daykey-0" style="display:none;">'.rand(1,9).'</span>';
					$dkeys = poison::get_daykey(5,true);
					for ($i = 0; $i < strlen($dkeys); $i++) {
						$daykey_text .= '<span class="daykey-'.($i+1).'">'.$dkeys[$i].'</span>';
					}
					return ' &laquo; <em class="spamword">'.$daykey_text.'</em>';
				})(),
				'description' => 'หากท่านไม่ได้เป็นสมาชิก ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
			] : NULL,

			'name' => [
				'type' => 'text',
				'label' => tr('Your name').(i()->ok?' ('.tr('Your are member').')':''),
				'class' => '-fill',
				'require' => true,
				'readonly' => i()->ok && !cfg('member.name_alias'),
				'value' => SG\getFirst($comment->name,i()->name,$_COOKIE['sg']['name']),
			],

			'mail' => !i()->ok ? [
				'type' => 'text',
				'label' => tr('E-mail'),
				'class' => '-fill',
				'require' => cfg('comment.require.mail'),
				'value' => SG\getFirst($comment->mail,$_COOKIE['sg']['mail']),
				'description' => 'The content of this field is kept private and will not be shown publicly. This mail use for contact via email when someone want to contact you.',
			] : NULL,
			'homepage' => !i()->ok ? [
				'type' => 'text',
				'label' => tr('Homepage'),
				'class' => '-fill',
				'require' => cfg('comment.require.homepage'),
				'value' => SG\getFirst($comment->homepage,$_COOKIE['sg']['homepage']),
			] : NULL,


			// (function($comment) {
			// 	$children = [];
			// 	if (user_access('post comments')) {
			// 		if (!i()->ok) {
			// 			$children['daykey'] = [
			// 				'name' => 'daykey',
			// 				'type' => 'text',
			// 				'label' => tr('Anti-spam word'),
			// 				'size' => 10,
			// 				'require' => true,
			// 				'posttext' => (function() {
			// 					$daykey_text='<span class="daykey-0" style="display:none;">'.rand(1,9).'</span>';
			// 					$dkeys=poison::get_daykey(5,true);
			// 					for ($i=0;$i<strlen($dkeys);$i++) {
			// 						$daykey_text.='<span class="daykey-'.($i+1).'">'.$dkeys[$i].'</span>';
			// 					}
			// 					return ' &laquo; <em class="spamword">'.$daykey_text.'</em>';
			// 				})(),
			// 				'description' => 'หากท่านไม่ได้เป็นสมาชิก ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
			// 			];
			// 		}

			// 		$children['name'] = [
			// 			'type' => 'text',
			// 			'label' => tr('Your name').':'.(i()->ok?' ('.tr('Your are member').')':''),
			// 			'class' => '-fill',
			// 			'require' => true,
			// 			'readonly' => i()->ok && !cfg('member.name_alias'),
			// 			'value' => SG\getFirst($comment->name,i()->name,$_COOKIE['sg']['name']),
			// 		];

			// 		if (!i()->ok) {
			// 			$children['mail'] = [
			// 				'type' => 'text',
			// 				'label' => tr('E-mail'),
			// 				'class' => '-fill',
			// 				'require' => cfg('comment.require.mail'),
			// 				'value' => SG\getFirst($comment->mail,$_COOKIE['sg']['mail']),
			// 				'description' => 'The content of this field is kept private and will not be shown publicly. This mail use for contact via email when someone want to contact you.',
			// 			];

			// 			$children['homepage'] = [
			// 				'type' => 'text',
			// 				'label' => tr('Homepage'),
			// 				'class' => '-fill',
			// 				'require' => cfg('comment.require.homepage'),
			// 				'value' => SG\getFirst($comment->homepage,$_COOKIE['sg']['homepage']),
			// 			];
			// 		}
			// 	} else {
			// 		$children['username'] = [
			// 			'name' => 'username',
			// 			'type' => 'text',
			// 			'label' => tr('Username'),
			// 			'size' => 15,
			// 			'require' => true,
			// 		];

			// 		$children['password'] = [
			// 			'name' => 'password',
			// 			'type' => 'password',
			// 			'label' => tr('Password'),
			// 			'size' => 15,
			// 			'require' => true,
			// 			'description' => 'กรุณาป้อน Username / Password ที่ท่านได้ลงทะเบียนไว้กับเว็บไซท์แห่งนี้ หรือ <a href="'.url('user/register').'">สมัครเป็นสมาชิกของเว็บไซท์</a>',
			// 		];
			// 	}
			// 	debugMsg($children, '$children');
			// 	return ['children' => $children];
			// })($comment),

	// if (user_access('post comments')) {
	// 	if (!i()->ok) {
	// 		$form->addField(
	// 			,
	// 			[
	// 				'name' => 'daykey',
	// 				'type' => 'text',
	// 				'label' => tr('Anti-spam word'),
	// 				'size' => 10,
	// 				'require' => true,
	// 				'posttext' => (function() {
	// 					$daykey_text='<span class="daykey-0" style="display:none;">'.rand(1,9).'</span>';
	// 					$dkeys=poison::get_daykey(5,true);
	// 					for ($i=0;$i<strlen($dkeys);$i++) {
	// 						$daykey_text.='<span class="daykey-'.($i+1).'">'.$dkeys[$i].'</span>';
	// 					}
	// 					return ' &laquo; <em class="spamword">'.$daykey_text.'</em>';
	// 				})(),
	// 				'description' => 'หากท่านไม่ได้เป็นสมาชิก ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
	// 			]
	// 		);
	// 	}

	// 	$form->addField(
	// 			'name',
	// 			array(
	// 				'type' => 'text',
	// 				'label' => tr('Your name').':'.(i()->ok?' ('.tr('Your are member').')':''),
	// 				'class' => '-fill',
	// 				'require' => true,
	// 				'readonly' => i()->ok && !cfg('member.name_alias'),
	// 				'value' => SG\getFirst($comment->name,i()->name,$_COOKIE['sg']['name']),
	// 			)
	// 		);

	// 	if (!i()->ok) {
	// 		$form->addField(
	// 				'mail',
	// 				array(
	// 					'type' => 'text',
	// 					'label' => tr('E-mail'),
	// 					'class' => '-fill',
	// 					'require' => cfg('comment.require.mail'),
	// 					'value' => SG\getFirst($comment->mail,$_COOKIE['sg']['mail']),
	// 					'description' => 'The content of this field is kept private and will not be shown publicly. This mail use for contact via email when someone want to contact you.',
	// 				)
	// 			);

	// 		$form->addField(
	// 				'homepage',
	// 				array(
	// 					'type' => 'text',
	// 					'label' => tr('Homepage'),
	// 					'class' => '-fill',
	// 					'require' => cfg('comment.require.homepage'),
	// 					'value' => SG\getFirst($comment->homepage,$_COOKIE['sg']['homepage']),
	// 				)
	// 			);
	// 	}

	// } else {
	// 	$form->addField(
	// 			'username',
	// 			array(
	// 				'name' => 'username',
	// 				'type' => 'text',
	// 				'label' => tr('Username'),
	// 				'size' => 15,
	// 				'require' => true,
	// 			)
	// 		);

	// 	$form->addField(
	// 			'password',
	// 			array(
	// 				'name' => 'password',
	// 				'type' => 'password',
	// 				'label' => tr('Password'),
	// 				'size' => 15,
	// 				'require' => true,
	// 				'description' => 'กรุณาป้อน Username / Password ที่ท่านได้ลงทะเบียนไว้กับเว็บไซท์แห่งนี้ หรือ <a href="'.url('user/register').'">สมัครเป็นสมาชิกของเว็บไซท์</a>',
	// 			)
	// 		);
	// }
			'comment' => [
				'type' => 'textarea',
				'label' => tr('Comment'),
				'class' => '-fill',
				'rows' => 6,
				'require' => true,
				'value' => $comment->comment,
				'pretext' => editor::softganz_editor('edit-comment-comment'),
				'description' => 'คำแนะนำ เว็บไซท์นี้สามารถเขียนข้อความในรูปแบบ <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank" title="ดูรายละเอียดรูปแบบการเขียนแบบมาร์คดาวน์ - Markdown">มาร์คดาวน์ - Markdown Syntax</a>: <ul><li>วิธีการขึ้นบรรทัดใหม่โดยไม่เว้นช่องว่างระหว่างบรรทัด ให้เคาะเว้นวรรค (Space bar) ที่ท้ายบรรทัดจำนวนหนึ่งครั้ง</li>	<li>วิธีการขึ้นย่อหน้าใหม่ซึ่งจะมีการเว้นช่องว่างห่างจากบรรทัดด้านบนเล็กน้อย ให้เคาะ Enter จำนวน 2 ครั้ง</li></ul>',
			],
			'photo' => user_access('upload photo') ? [
				'name' => 'photo',
				'label' => tr('Upload photo'),
				//'container' => '{type: "fieldset", collapsible: false; legend: "'.tr('Upload photo').'"}',
				'type' => 'file',
				'size' => 30,
				'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB ('.number_format(cfg('photo.max_file_size')*1024).' bytes)</strong>. </li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพที่อยู่ในความคิดเห็นของหัวข้อนั้น ๆ จะถูกลบทิ้งด้วย</li></ul>',
				'container' => '{class: "-fieldset"}',
			] : NULL,
			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>'.tr('POST COMMENT').'</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	if ($terms_of_service && cfg('comment.terms_of_service.location') == 'below')
		$ret .= $terms_of_service;

	//$ret .= print_o($topicInfo,'$topicInfo');

	return $ret;
}
?>