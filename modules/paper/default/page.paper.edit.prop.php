<?php
/**
* Paper Edit Property
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_prop($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$prop = $topicInfo->property;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>แก้ไขรายละเอียดอื่น ๆ</h3></header>';

	$form = new Form('prop', url('paper/info/api/'.$tpid.'/prop.save'), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'none');
	$form->addData('callback', url('paper/'.$tpid.'/edit'));

	$form->addText('<div class="sg-tabs"><ul class="tabs"><li class="-active"><a href="#prop-show">การแสดงภาพ</a></li><li><a href="#prop-options">'.tr('Options').'</a></li><li><a href="#prop-format">Input Format</a></li></ul>');
	$form->addText('<div id="prop-show"><h4>การแสดงภาพ</h4>');

	$form->addField(
			'show_photo',
			array(
				'label' => 'แสดงภาพอัตโนมัติ',
				'type' => 'radio',
				'display' => 'inline',
				'options' => array(
											'no' => 'ไม่แสดงภาพ',
											'first' => 'แสดงเฉพาะภาพแรก',
											'all' => 'แสดงทุกภาพ',
											'slide' => 'แสดงภาพด้วยไสลด์<br />',
											'some' => 'แสดงภาพจำนวน <input type="text" name="show_photo_num" size="3" '.(is_numeric($prop->show_photo)?' value="'.$prop->show_photo.'"':'').' placeholder="0"> ภาพ',
										),
				'value' => is_numeric($prop->show_photo) ?	'some' : $prop->show_photo,
			)
		);

	$form->addField(
			'slide_width',
			array(
				'type' => 'text',
				'label' => 'Slide width (pixel)',
				'size' => 8,
				'maxlength' => '4',
				'value' => $prop->slide_width,
				'placeholder' => 'e.g. 400',
			)
		);

	$form->addField(
			'slide_height',
			array(
				'type' => 'text',
				'label' => 'Slide height (pixel)',
				'size' => 8,
				'maxlength' => '4',
				'value' => $prop->slide_height,
				'placeholder' => 'e.g. 300',
			)
		);

	$form->addText('</div>');

	$form->addText('<div id="prop-options" class="-hidden"><h4>Options</h4>');

	$form->addField(
			'show_fullpage',
			array(
				'type' => 'checkbox',
				'name' => 'option[fullpage]',
				'options' => array('1' => 'Show as full page'),
				'value' => $prop->option->fullpage,
			)
		);

	$form->addField(
			'show_secondary',
			array(
				'type' => 'checkbox',
				'name' => 'option[secondary]',
				'options' => array(true => 'Show secondary section'),
				'value' => $prop->option->secondary,
			)
		);

	$form->addField(
			'show_header',
			array(
				'type' => 'checkbox',
				'name' => 'option[header]',
				'options' => array('1' => 'Show header'),
				'value' => $prop->option->header,
			)
		);

	$form->addField(
			'show_title',
			array(
				'type' => 'checkbox',
				'name' => 'option[secondary]',
				'options' => array('1' => 'Show title'),
				'value' => $prop->option->title,
			)
		);

	$form->addField(
			'show_ribbon',
			array(
				'type' => 'checkbox',
				'name' => 'option[ribbon]',
				'options' => array('1' => 'Show ribbon'),
				'value' => $prop->option->ribbon,
			)
		);




	$form->addField(
			'show_toolbar',
			array(
				'type' => 'checkbox',
				'name' => 'option[toolbar]',
				'options' => array('1' => 'Show toolbar'),
				'value' => $prop->option->toolbar,
			)
		);

	$form->addField(
			'show_container',
			array(
				'type' => 'checkbox',
				'name' => 'option[container]',
				'options' => array('1' => 'Show container'),
				'value' => $prop->option->container,
			)
		);

	$form->addField(
			'show_timestamp',
			array(
				'type' => 'checkbox',
				'name' => 'option[timestamp]',
				'options' => array('1' => 'Show timestamp'),
				'value' => $prop->option->timestamp,
			)
		);

	$form->addField(
			'show_related',
			array(
				'type' => 'checkbox',
				'name' => 'option[related]',
				'options' => array('1' => 'Show related topics'),
				'value' => $prop->option->related,
			)
		);

	$form->addField(
			'show_docs',
			array(
				'type' => 'checkbox',
				'name' => 'option[docs]',
				'options' => array('1' => 'Show documents'),
				'value' => $prop->option->docs,
			)
		);

	$form->addField(
			'show_footer',
			array(
				'type' => 'checkbox',
				'name' => 'option[footer]',
				'options' => array('1' => 'Show footer'),
				'value' => $prop->option->footer,
			)
		);

	$form->addField(
			'show_package',
			array(
				'type' => 'checkbox',
				'name' => 'option[package]',
				'options' => array('1' => 'Show package footer'),
				'value' => $prop->option->package,
			)
		);

	$form->addField(
			'show_commentwithphoto',
			array(
				'type' => 'checkbox',
				'name' => 'option[commentwithphoto]',
				'options' => array('1' => 'Show photo comment'),
				'value' => $prop->option->commentwithphoto,
			)
		);

	$form->addField(
			'show_social',
			array(
				'type' => 'checkbox',
				'name' => 'option[social]',
				'options' => array('1' => 'Show social'),
				'value' => $prop->option->social,
			)
		);

	$form->addField(
			'show_ads',
			array(
				'type' => 'checkbox',
				'name' => 'option[ads]',
				'options' => array('1' => 'Show ads'),
				'value' => $prop->option->ads,
			)
		);

	$form->addField(
			'show_video',
			array(
				'type' => 'checkbox',
				'name' => 'option[show_video]',
				'options' => array('1' => 'Show Video'),
				'value' => $prop->option->show_video,
			)
		);

	$form->addText('</div>');

	$form->addText('<div id="prop-format" class="-hidden"><h4>Input Format</h4>');

	//		if ($prop->input_format=='php' && user_access('input format type php')) {

	$formatOptions = array(
		'markdown' => 'HTML & Markdown<div class="description"><ul><li>Lines and paragraphs break automatically.</li><li>Allowed HTML  tags: '.htmlspecialchars('<a> <em> <strong> <code> <ul> <ol> <li> <img> <br> <p> <blockquote> <h3> <h4> <summary>').'</li><li>Use &lt;!--break--&gt; to create page breaks.</li><li>You can use <b>Markdown syntax</b> to format and style the text.</li><li>For complete details on the Markdown syntax, see the <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown documentation</a>.</li></ul></div>',
		'html' => 'HTML Only<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li></ul></div>',
	);
	if (user_access('input format type php')) {
		$formatOptions['php'] = 'PHP & HTML<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li><li>Allowed PHP Code in detail</li></ul></div>';
	}

	$form->addField(
			'input_format',
			array(
				'type' => 'radio',
				'options' => $formatOptions,
				'value' => $prop->input_format,
			)
		);
	//		}

	$form->addText('</div>');
	$form->addText('</div><!-- tabs -->');

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);


	$ret .= $form->build();

	return $ret;
}
?>