<?php
/**
* Paper   :: Edit Property
* Created :: 2019-06-02
* Modify  :: 2024-03-20
* Version :: 2
*
* @param String $topicInfo
* @return Widget
*
* @usage module/{id}/method
*/

class PaperEditProp extends Page {
	var $nodeId;
	var $right;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo,
			'right' => $topicInfo->right,
		]);
	}

	function build() {
		if (!$this->nodeId) return message('error', 'PARAMETER ERROR');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');

		$prop = $this->topicInfo->property;

		$form = new Form([
			'variable' => 'prop',
			'action' => url('api/paper/'.$this->nodeId.'/prop.save'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'none',
			// $form->addData('callback', url('paper/'.$this->nodeId.'/edit'));
			'children' => [
				'<div class="sg-tabs"><ul class="tabs"><li class="-active"><a href="#prop-show">การแสดงภาพ</a></li><li><a href="#prop-options">'.tr('Options').'</a></li><li><a href="#prop-format">Input Format</a></li></ul>',
				'<div id="prop-show"><h4>การแสดงภาพ</h4>',
				'show_photo' => [
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
				],
				'slide_width' => [
					'type' => 'text',
					'label' => 'Slide width (pixel)',
					'size' => 8,
					'maxlength' => '4',
					'value' => $prop->slide_width,
					'placeholder' => 'e.g. 400',
				],
				'slide_height' => [
					'type' => 'text',
					'label' => 'Slide height (pixel)',
					'size' => 8,
					'maxlength' => '4',
					'value' => $prop->slide_height,
					'placeholder' => 'e.g. 300',
				],
				'</div>',

				'<div id="prop-options" class="-hidden"><h4>Options</h4>',
				'show_fullpage' => [
					'type' => 'checkbox',
					'name' => 'option[fullpage]',
					'options' => array('1' => 'Show as full page'),
					'value' => $prop->option->fullpage,
				],
				'show_secondary' => [
					'type' => 'checkbox',
					'name' => 'option[secondary]',
					'options' => array(true => 'Show secondary section'),
					'value' => $prop->option->secondary,
				],
				'show_header' => [
					'type' => 'checkbox',
					'name' => 'option[header]',
					'options' => array('1' => 'Show header'),
					'value' => $prop->option->header,
				],
				'show_title' => [
					'type' => 'checkbox',
					'name' => 'option[title]',
					'options' => array('1' => 'Show title'),
					'value' => $prop->option->title,
				],
				'show_ribbon' => [
					'type' => 'checkbox',
					'name' => 'option[ribbon]',
					'options' => array('1' => 'Show ribbon'),
					'value' => $prop->option->ribbon,
				],
				'show_toolbar' => [
					'type' => 'checkbox',
					'name' => 'option[toolbar]',
					'options' => array('1' => 'Show toolbar'),
					'value' => $prop->option->toolbar,
				],
				'show_container' => [
					'type' => 'checkbox',
					'name' => 'option[container]',
					'options' => array('1' => 'Show container'),
					'value' => $prop->option->container,
				],
				'show_timestamp' => [
					'type' => 'checkbox',
					'name' => 'option[timestamp]',
					'options' => array('1' => 'Show timestamp'),
					'value' => $prop->option->timestamp,
				],
				'show_related' => [
					'type' => 'checkbox',
					'name' => 'option[related]',
					'options' => array('1' => 'Show related topics'),
					'value' => $prop->option->related,
				],
				'show_docs' => [
					'type' => 'checkbox',
					'name' => 'option[docs]',
					'options' => array('1' => 'Show documents'),
					'value' => $prop->option->docs,
				],
				'show_footer' => [
					'type' => 'checkbox',
					'name' => 'option[footer]',
					'options' => array('1' => 'Show footer'),
					'value' => $prop->option->footer,
				],
				'show_package' => [
					'type' => 'checkbox',
					'name' => 'option[package]',
					'options' => array('1' => 'Show package footer'),
					'value' => $prop->option->package,
				],
				'show_commentwithphoto' => [
					'type' => 'checkbox',
					'name' => 'option[commentwithphoto]',
					'options' => array('1' => 'Show photo comment'),
					'value' => $prop->option->commentwithphoto,
				],
				'show_social' => [
					'type' => 'checkbox',
					'name' => 'option[social]',
					'options' => array('1' => 'Show social'),
					'value' => $prop->option->social,
				],
				'show_ads' => [
					'type' => 'checkbox',
					'name' => 'option[ads]',
					'options' => array('1' => 'Show ads'),
					'value' => $prop->option->ads,
				],
				'show_video' => [
					'type' => 'checkbox',
					'name' => 'option[show_video]',
					'options' => array('1' => 'Show Video'),
					'value' => $prop->option->show_video,
				],
				'</div>',

				'<div id="prop-format" class="-hidden"><h4>Input Format</h4>',

				//		if ($prop->input_format=='php' && user_access('input format type php')) {

				'input_format' => [
					'type' => 'radio',
					'options' => (function() {
							$formatOptions = [
							'markdown' => 'HTML & Markdown<div class="description"><ul><li>Lines and paragraphs break automatically.</li><li>Allowed HTML  tags: '.htmlspecialchars('<a> <em> <strong> <code> <ul> <ol> <li> <img> <br> <p> <blockquote> <h3> <h4> <summary>').'</li><li>Use &lt;!--break--&gt; to create page breaks.</li><li>You can use <b>Markdown syntax</b> to format and style the text.</li><li>For complete details on the Markdown syntax, see the <a href="http://daringfireball.net/projects/markdown/syntax" target="_blank">Markdown documentation</a>.</li></ul></div>',
							'html' => 'HTML Only<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li></ul></div>',
						];
						if (user_access('input format type php')) {
							$formatOptions['php'] = 'PHP & HTML<div class="description"><ul><li>No lines and paragraphs break.</li><li>Allowed HTML tags like HTML format above.</li><li>Allowed PHP Code in detail</li></ul></div>';
						}
						return $formatOptions;
					})(),
					'value' => $prop->input_format,
				],
				'</div>',
				'</div><!-- tabs -->',
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แก้ไขรายละเอียดอื่น ๆ',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$form,
				], // children
			]), // Widget
		]);
	}
}
?>