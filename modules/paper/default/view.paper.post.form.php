<?php
/**
* Paper Post Form
* Created 2019-06-10
* Modify 2019-06-10
*
* @param Object $topic
* @return Object $form
*/

$debug = true;

function view_paper_post_form($topic) {
	load_lib('class.editor.php','lib');

	$cfgAreaCode = cfg('topic.areacode');
	$areaTypeCode = $cfgAreaCode->{$topic->type->type}->area;

	$form = new Form([
		'variable' => 'topic',
		'action' => url(q()),
		'id' => 'edit-topic',
		'class' => 'paper-post',
		'enctype' => 'multipart/form-data',
		'children' => [
			$topic->type->help ? sg_text2html($topic->type->help) : NULL,
			$topic->message ? sg_text2html($topic->message) : NULL,

			'submit1' => post('preview') ? [
				'type' => 'submit',
				'items' => [
					'preview' => tr('Preview'),
					'save' => tr('Save'),
					'text' => i()->ok ? ' หรือ&nbsp; ' : NULL,
					'draft' => i()->ok ? tr('Save as draft') : NULL,
				],
			] : NULL,

			'orgid' => $topic->org ? ['type' => 'hidden', 'value' => $topic->org,] : NULL,

			'daykey' => !i()->ok ? [
				'name' => 'daykey',
				'type' => 'text',
				'label' => tr('Anti-spam word'),
				'size' => 10,
				'require' => true,
				'value' => post('daykey'),
				'posttext' => ' &laquo; <em class="spamword">'.poison::get_daykey(5,true).'</em>',
				'description' => 'หากท่านไม่ได้เป็นสมาชิก ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
			] : NULL,

			'title' => $topic->type->has_title ? [
				'type' => 'text',
				'label' => tr($topic->type->title_label),
				'class' => '-fill',
				'maxlength' => 150,
				'require' => true,
				'value' => $topic->post->title,
			] : NULL,

			'vid' => _vid($topic),

			'areacode' => $topic->type->type && $areaTypeCode ? (function($areaTypeCode) {
				$fields = ['children' => []];
				if ($areaTypeCode != 'all') {
					$stmt = 'SELECT `provid`, `provname` FROM %co_province% WHERE `provid` IN ( :areacode )';
					$areaSelectOptions[''] = '==เลือกพื้นที่==';
					foreach (mydb::select($stmt, ':areacode', 'SET:'.$areaTypeCode)->items as $rs) {
						$areaSelectOptions[$rs->provid] = $rs->provname;
					}
					$fields['children']['areacode'] = [
						'type' => 'select',
						'label' => 'พื้นที่',
						'class' => '-fill',
						'options' => $areaSelectOptions
					];
				} else {
					$fields['children']['areacode'] = ['type' => 'hidden'];
					$fields['children']['areaname'] = [
						'type' => 'text',
						'label' => 'พื้นที่',
						'class' => 'sg-address -fill',
						'attr' => ['data-altfld'=>'edit-topic-areacode'],
						'placeholder' => 'ระบุชื่อตำบล แล้วเลือกจากรายการแสดง',
					];
				}
				return $fields;
			})($areaTypeCode) : NULL,

			'body' => $topic->type->has_body ? [
				'type' => 'textarea',
				'label' => $topic->type->body_label,
				'class' => '-fill',
				'rows' => 12,
				'require' => true,
				'value' => $topic->post->body,
				'pretext' => editor::softganz_editor('edit-topic-body'),
				'description' => 'คำแนะนำ เว็บไซท์นี้สามารถเขียนข้อความในรูปแบบ <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank" title="ดูรายละเอียดรูปแบบการเขียนแบบมาร์คดาวน์ - Markdown">มาร์คดาวน์ - Markdown Syntax</a> : <ul><li>วิธีการขึ้นย่อหน้าใหม่ซึ่งจะมีการเว้นช่องว่างห่างจากบรรทัดด้านบนเล็กน้อย ให้เคาะ Enter จำนวน 2 ครั้ง</li><li>วิธีการขึ้นบรรทัดใหม่โดยไม่เว้นช่องว่างระหว่างบรรทัด ให้เคาะเว้นวรรค (Space bar) ที่ท้ายบรรทัดจำนวนหนึ่งครั้ง</li>	<li>หากข้อความของท่านยาวเกินไป จะทำให้ไม่สามารถนำข้อความทั้งหมดไปแสดงในหน้าแรก ให้ใส่ &lt;!--break--&gt แทรกไว้ในตำแหน่งที่ต้องการให้ตัดไปแสดงผล</li></ul>'
			] : NULL,

			'video' => cfg('topic.video.allow') && user_access('upload video') ? [
				'label' => 'วีดิโอ :: Select FLV video file to upload',
				'name' => 'video',
				'type' => 'file',
				'size' => 50,
				'description' => '<strong>ข้อกำหนดในการส่งไฟล์วีดิโอ</strong><ul><li>ไฟล์ประเภท <strong>flv</strong> ขนาดไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong>. </li><li>หากวีดิโอเป็นไฟล์นามสกุลอื่น จะต้องทำการแปลงให้เป็นนามสกุล .flv ก่อนส่งขึ้นเว็บ</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์วีดิโอที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul>',
			] : NULL,
			[
				[],
				'tabs_start' => '<div class="sg-tabs"><ul class="tabs tabs_input">',
				'tabs_1' => '<li class="-active"><a href="#tabs_1">'.tr('Poster').'</a></li>',
				'tabs_2' => user_access('upload photo')?'<li><a href="#tabs_2">'.tr('Photo').'</a></li>':'',
				'tabs_3' => user_access('upload document')?'<li><a href="#tabs_3">'.tr('Documents').'</a></li>':'',
				'tabs_4' => '<li><a href="#tabs_4">'.tr('Input Format').'</a></li>',
				'tabs_5' => user_access('administer contents')?'<li><a href="#tabs_5">'.tr('Options').'</a></li>':'',
				'tabs_e' => '</ul>',

				'tabs_1_div_s' => '<div id="tabs_1" class="tabs_input" style="display:block;"><h4>{tr:Poster}</h4>',
				'poster' => [
					'type' => 'text',
					'label' => 'Sender'.(i()->ok?' (you are member)':''),
					'class' => '-fill',
					'require' => true,
					'readonly' => i()->ok && !cfg('member.name_alias'),
					'value' => SG\getFirst($topic->post->poster,i()->name),
				],
				'email' => !i()->ok ? [
					'type' => 'text',
					'label' => tr('Email'),
					'class' => '-fill',
					'require' => cfg('topic.require.mail'),
					'value' => $topic->post->email,
				] : NULL,
				'website' => !i()->ok ? [
					'type' => 'text',
					'label' => tr('Website'),
					'class' => '-fill',
					'require' => cfg('topic.require.homepage'),
					'value' => $topic->post->website,
				] : NULL,
				'tabs_1_div_e' => '</div>',


				'tabs_2_div_s' => user_access('upload photo') ? '<div id="tabs_2" class="tabs_input"><h4>{tr:Photo}</h4>' : NULL,
				'photo' => user_access('upload photo') ? [
					'label' => tr('Select photo to upload'),
					'name' => 'photo',
					'type' => 'file',
					'size' => 50,
					'description' => '<strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB ('.number_format(cfg('photo.max_file_size')*1024).' bytes)</strong>. </li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>หากต้องการเพิ่มชื่อภาพ , คำอธิบายภาพ หรือ ส่งภาพเพิ่มเติม สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดภาพในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul>',
				] : NULL,
				'tabs_2_div_e' => user_access('upload photo') ? '</div>' : NULL,

				'tabs_3_div_s' => user_access('upload document') ? '<div id="tabs_3" class="tabs_input"><h4>เอกสารประกอบ</h4>' : NULL,
				'document' => user_access('upload document') ? [
					'label' => tr('Select document to upload'),
					'name' => 'document',
					'type' => 'file',
					'size' => 50,
				] : NULL,
				'document_title' => user_access('upload document') ? [
					'type' => 'text',
					'label' => 'Document title',
					'maxlength' => 150,
					'class' => '-fill',
					'value' => $topic->post->document_title,
				] : NULL,
				'document_description' => user_access('upload document') ? [
					'type' => 'textarea',
					'label' => 'Document description',
					'class' => '-fill',
					'rows' => 3,
					'value' => $topic->post->document_description,
					'pretext' => editor::softganz_editor('edit-document-description'),
					'description' => '<strong>ข้อกำหนดในการส่งไฟล์เอกสารประกอบ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li><li>หากต้องการเพิ่มไฟล์เอกสารประกอบ , แก้ไข หรือ ลบทิ้ง สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดเอกสารประกอบในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์เอกสารประกอบทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul>',
				] : NULL,
				'tabs_3_div_e' => user_access('upload document') ? '</div>' : NULL,

				'tabs_4_div_s' => user_access('upload document') ? '<div id="tabs_4" class="tabs_input"><h4>{tr:Input Format}</h4>' : NULL,
				'input_format' => user_access('upload document') ? [
					'label' => 'Input Format',
					'type' => 'radio',
					'name' => 'topic[property][input_format]',
					'options' => [
						'markdown' => 'HTML & Markdown<div class="description"><ul><li>Lines and paragraphs break automatically.</li><li>Allowed HTML tags: '.htmlspecialchars('<a> <em> <strong> <code> <ul> <ol> <li> <img> <br> <p> <blockquote> <h3> <h4> <summary>').'</li><li>Use &lt;!--break--&gt; to create page breaks.</li><li>You can use <a href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a> to format and style the text.</li><li>For complete details on the Markdown syntax, see the <a href="http://daringfireball.net/projects/markdown/syntax">Markdown documentation</a>.</li></ul></div>',
						'html' => 'HTML Only<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li></ul></div>',
						'php' => user_access('administer contents') ? 'PHP & HTML<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li><li>Allowed PHP Code in detail</li></ul></div>' : NULL,
					],
					'value' => $topic->post->property['input_format'],
				] : NULL,
				'tabs_4_div_e' => user_access('upload document') ? '</div>' : NULL,

				'tabs_5_div_s' => user_access('administer contents') ? '<div id="tabs_5" class="tabs_input"><h4>ตัวเลือก</h4>' : NULL,
				'sticky' => user_access('administer contents') ? [
					'label' => 'Sticky',
					'type' => 'radio',
					'options' => (function() {
						$options = [0 => 'None'];
						foreach (cfg('sticky') as $key => $value) $options[$key] = $value;
						return $options;
					})(),
					'value' => $topic->post->sticky,
					'posttext' => '<label><input type="checkbox" name="clear_sticky" /> Clear all sticky of this section</label>',
				] : NULL,
				'promote' => user_access('administer contents') ? [
					'label' => 'Options',
					'type' => 'checkbox',
					'options' => ['1' => 'Promoted to frontpage'],
					'value' => $topic->post->promote,
				] : NULL,
				'tabs_5_div_e' => user_access('administer contents') ? '</div>' : NULL,

				'tabs_end' => '</div>',
			],

			'submit' => [
				'type'=>'button',
				'items'=> [
					/*
					'preview'=>array(
						'type'=>'submit',
						'class'=>'-secondary',
						'value'=>'<i class="icon -material">find_in_page</i><span>'.tr('Preview').'</span>'
						),
					*/
					'draft' => [
						'type'=>i()->ok?'submit':'text',
						'value'=>i()->ok?'<i class="icon -material">done</i><span>'.tr('Save as draft').'</span>':''
						],
					'sep' => ['type'=>'text', 'value'=>'&nbsp;'],
					'save' => [
						'type'=>'submit',
						'class'=>'-primary',
						'value'=>'<i class="icon -material">done_all</i><span>'.tr('Save').'</span>'
						],
				],
				'pretext' => '<a class="btn -link -cancel" href=""><i class="icon -material -gray">cancel</i><span>'.tr('CANCEL').'</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
			'<style type="text/css">
			.sg-tabs>div {min-height: 250px;}
			</style>'
		], // children
	]);

	return $form;
}

function _vid($topic) {
	$fields = [
		'type' => 'group',
		'children' => [],
	];
	$vocabs = mydb::select(
		'SELECT vt.*
		FROM %vocabulary_types% vt
			LEFT JOIN %vocabulary% v ON v.`vid` = vt.`vid`
		WHERE vt.`type` = :type
		ORDER BY `weight`,`type` ASC',
		':type', $topic->type->type
	);

	foreach ($vocabs->items as $vocab) {
		$vocab = BasicModel::get_vocabulary($vocab->vid);
		if ($vocab->tags) {
			$form = [
				'label' => $vocab->name,
				'require' => $vocab->required ? true : false,
				'name' => 'topic[taxonomy][tags]['.$vocab->vid.']',
				'id' => 'taxonomy-freetags',
				'class' => '-fill',
				'type' => 'text',
				'maxlength' => 255,
				'autocomplete' => 'OFF',
				'value' => $topic->post->taxonomy['tags'][$vocab->vid],
				'description' => SG\getFirst($vocab->help,'A comma-separated list of terms describing this content. Example: funny, bungee jumping, "Company, Inc."'),
				'description' => '<script type="text/javascript">
					var options_xml = {
						script:"'.url('api/tags/'.$vocab->vid,'').'",
						varname:"input"
					};
					var as_xml = new AutoSuggest(\'taxonomy-freetags\', options_xml);
				</script>',
			];
			$fields['children'][$vocab->name] = $form;
			head('<script type="text/javascript" src="/css/autocomplete/bsn.AutoSuggest_c_2.0.js"></script>
				<link rel="stylesheet" href="/css/autocomplete/css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8" />');
		} else {
			$vid = 'vocab_'.$vocab->vid;
			$form = ['label' => $vocab->name];
			if ($vocab->required) $form['require'] = true;
			$form['name'] = $vocab->multiple ? 'topic[taxonomy]['.$vocab->vid.'][]' : 'topic[taxonomy]['.$vocab->vid.']';
			$form['type'] = 'select';
			$form['class'] = '-fill';
			$form['multiple'] = $vocab->multiple;
			if ($vocab->multiple) $form['size'] = 6;
			if ($vocab->help) $form['description'] = $vocab->help;
			if ($vocab->required == 0) $form['options'][0] = '&lt;none&gt;';
			$form['default'] = $topic->post->taxonomy[$vocab->vid];
			$tree = BasicModel::get_taxonomy_tree($vocab->vid);
			foreach ($tree as $term) {
				if ($term->depth == 0) unset($optgroup);
				if ($term->process == -1) {
					// Hidden tag
				} else if ($term->process == 1) {
					$optgroup = $term->name;
				} else {
					if (isset($optgroup)) {
						$form['options'][$optgroup][$term->tid] = str_repeat('--', $term->depth).$term->name.'&nbsp;&nbsp;';
					} else {
						$form['options'][$term->tid] = str_repeat('--', $term->depth).$term->name.'&nbsp;&nbsp;';
					}
				}
			}
			$form['value'] = SG\getFirst($topic->post->taxonomy[$vocab->vid],$topic->tid);
			$fields['children'][$vid] = $form;
		}
	}
	return $fields;
}
?>