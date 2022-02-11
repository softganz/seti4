<?php
/**
* Flood Chat
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;
function flood_climate_chat_form($self) {
	$self->theme->title = 'แจ้งสถานการณ์';

	if (!i()->ok) {
		$ret = R::View('signform', '{time:-1}');
		$ret .= '<style type="text/css">
		.toolbar.-main.-imed h2 {text-align: center;}
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login {border: none; background-color: transparent;}
		.login.-normal h3 {display: none;}
		.form-item.-edit-cookielength {display: none;}
		.form.signform .ui-action>a {display: block;}
		</style>';
		return $ret;
	}
	/*
	$ret .= '<div id="public-home-send" class="ui-card public-home-send"><div class="ui-item"><img src="'.model::user_photo(i()->username).'" width="32" height="32" />'.i()->name;
	$ret .= '<textarea class="form-textarea -fill" rows="5" placeholder="รายละเอียดแจ้งเหตุ" /></textarea>';

	$ret .= 'ประเภท<br />';
	$ret .= 'ความเร่งด่วน<br />';
	$ret .= 'ภาพถ่าย<br />';
	$ret .= 'พิกัด<br />';
	$ret .= 'วันที่<br />';
	$ret .= 'ผู้แจ้ง<br />';
	$ret .= '</div>';
	$ret .= '</div>';
	*/

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>แจ้งสถานการณ์</h3></header>';
	$form = new Form(NULL, url('flood/chat/post'), 'flood-chat-post', 'flood-chat-post sg-form -upload');
	$form->addData('rel',"none");
	$form->addData('checkValid', true);
	$form->addConfig('enctype','multipart/form-data');
	$form->addData('done', 'close');

	// data-ret="'.url('flood/chat/home')
	$form->addField('msg',
		array(
			'type' => 'textarea',
			'label' => 'รายละเอียดสถานการณ์ฝนหรือน้ำท่วม',
			'class' => '-fill',
			'require' => true,
			'rows' => 3,
			'placeholder' => 'ระบุรายละเอียดสถานการณ์ฝนหรือน้ำท่วม',
		)
	);

	$form->addField('where',
		array(
			'type' => 'text',
			'label' => 'ที่ไหน?',
			'class' => 'sg-address -fill',
			'placeholder' => 'ระบุสถานที่เกิดเหตุการณ์',
		)
	);

	$form->addField('location',
		array(
			'type' => 'text',
			'label' => 'พิกัด?',
			'class' => '-fill',
			'placeholder' => 'ระบุพิกัด เช่น 7.0000,100.0000',
		)
	);

	$form->addField('when',
		array(
			'type' => 'text',
			'label' => 'เมื่อไหร่?',
			'class' => '-fill',
			'value' => date('Y-m-d H:i'),
		)
	);

	$form->addText('<div class="form-item -sg-clearfix"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพ</span><input type="file" name="photoimg" id="flood-event-photoimg"  accept="image/*;capture=camera" capture="camera"  /></span> <a class="sg-action btn" href="'.url('flood/climate/chat/loc').'" data-rel="box" data-width="100%" data-height="100%"><i class="icon -material">room</i></a></div>');

	$form->addField('save',
		array(
			'type' => 'button',
			'id' => 'flood-event-submit-x',
			'value' => '<i class="icon -save -white"></i><span>{tr:Post}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="none" data-done="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a> ',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js?v=3"></script>');

	$ret .= '<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refresh: false, permission: "ACCESS_FINE_LOCATION"}
		return options
	}
	$(document).ready(function() {
		$("#edit-msg").focus()
	})
	</script>';
	return $ret;
}
?>