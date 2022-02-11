<?php
/**
* Flood Chat
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;
function flood_chat_form($self) {
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

	$form = new Form(NULL, url('flood/chat/post'), 'flood-chat-post', 'flood-chat-post x-sg-form');
	$form->addData('rel',"#main");
	$form->addData('checkValid', true);
	$form->addConfig('enctype','multipart/form-data');

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

	$form->addField('textfield','<div class="form-item -sg-clearfix"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพ</span><input type="file" name="photoimg" id="flood-event-photoimg"  accept="image/*;capture=camera" capture="camera"  /></span></div>');

	$form->addField('where',
						array(
							'type' => 'text',
							'label' => 'ที่ไหน?',
							'class' => 'sg-address -fill',
							'placeholder' => 'ระบุสถานที่เกิดเหตุการณ์',
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

	$form->addField('save',
						array(
							'type' => 'button',
							'id' => 'flood-event-submit',
							'value' => '<i class="icon -save -white"></i><span>{tr:Post}</span>',
							'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('flood/chat',array('closewebview'=>'YES')).'" data-rel="#main"><i class="icon -cancel"></i><span>{tr:CANCEL}</span></a> ',
							'container' => array('class' => '-sg-text-right'),
						)
					);

	$ret .= $form->build();

	/*
	$ret .= '<form id="flood-event-post" class="sg-form" method="post" action="'.url('flood/event/post').'" data-rel="#main" data-ret="'.url('flood/chat/home').'">'._NL;

	$ret .= '<div id="form-event-msg" class="form-item"><h4>สถานการณ์ฝน-น้ำท่วม</h4>'._NL;
	$ret .= '<div class="form-item"><textarea id="flood-event-msg" name="msg" class="form-textarea -fill" rows="3" cols="20" placeholder="รายละเอียดสถานการณ์ฝนหรือน้ำท่วม"></textarea>'
			. '</div>';
	$ret .= '<span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพ</span><input type="file" name="photoimg" id="flood-event-photoimg"  accept="image/*;capture=camera" capture="camera"  /></span>'
				. '<div id="flood-event-bar"><div class="form-item"><label>ที่ไหน?</label><input type="text" class="sg-autocomplete form-text -fill" name="where" id="flood-event-where" placeholder="สถานที่เกิดเหตุการณ์" data-query="'.url('flood/event/getwhere').'" /></div>'
			. '<div class="form-item"><label>เมื่อไหร่?</label><input type="text" class="form-text -fill" name="when" id="flood-event-when" value="'.date('Y-m-d H:i').'" /></div>'._NL;

	$ret .= '<div class="form-item -sg-text-right">'
			. '<a class="sg-action btn -link" href="'.url('flood/chat',array('closewebview'=>'YES')).'" data-rel="#main">'.tr('Cancel').'</a> '
			. '<button id="flood-event-submit" class="btn -primary"><i class="icon -save -white"></i><span>'.tr('Post').'</span></button>'
			. '</div>'._NL;
	$ret .= '</div>';
	$ret.='</form>'._NL;
	*/

	head('flood.event.js','<script type="text/javascript" src="/flood/js.flood.event.js?v=3"></script>');

	$ret .= '<script type="text/javascript">$("#edit-msg").focus()</script>';
	return $ret;
}
?>