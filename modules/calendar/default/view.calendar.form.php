<?php
/**
 * Calendar:: Edit Form
 * Modify  :: 2025-07-18
 * Version :: 2
 *
 * @param Array $calInfo
 * @param Object $para
 * @return String
 */

use Softganz\DB;

$debug = true;

function view_calendar_form($calInfo = [], $para = NULL) {
	if (is_array($calInfo)) $calInfo = (Object) $calInfo;

	if (empty($calInfo->from_date)) $calInfo->from_date = date('j/n/').(date('Y'));
	if (empty($calInfo->to_date)) $calInfo->to_date = $calInfo->from_date;
	if (empty($calInfo->from_time)) $calInfo->from_time = '09:00';
	if (empty($calInfo->to_time)) {
		list($hr,$min) = explode(':',$calInfo->from_time);
		$calInfo->to_time = sprintf('%02d',$hr+1).':'.$min;
	}
	if (empty($calInfo->privacy)) $calInfo->privacy  ='public';

	list(,$month,$year) = explode('/',$calInfo->from_date);

	$form = new Form([
		'variable' => 'calendar',
		'action' => url('calendar/'.($calInfo->calId ? $calInfo->calId.'/update' : 'create')),
		'id' => 'edit-calendar',
		'class' => 'sg-form',
		'checkValid' => true,
		'children' => [
			'id' => ['type' => 'hidden','value' => $calInfo->calId],
			'module' => $para->module ? ['type' => 'hidden','value' => $para->module] : NULL,

			'tpid' => $para->module ? ['type' => 'hidden','value' => $calInfo->tpid] : ($calInfo->calId && $calInfo->tpid ? ['type' => 'hidden','value' => $calInfo->tpid] : NULL),
			'topsave' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -back-to-calendar -link -cancel" style="position: absolute; left: 0;" data-rel="#calendar-body"><i class="icon -material -gray">navigate_before</i>กลับสู่หน้าปฏิทิน</a><a class="btn -back-to-calendar -link -cancel"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class:"-sg-text-right"}',
			],
			'title' => [
				'type' => 'text',
				'label' => 'ทำอะไร',
				'maxlength' => 255,
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($calInfo->title),
			],

			'from_date' => [
				'type' => 'text',
				'label' => 'เมื่อไหร่',
				'class' => 'sg-datepicker -date',
				'autocomplete' => 'off',
				'value' => $calInfo->from_date,
				'posttext' => (function($calInfo) {
					for ($hr = 7; $hr < 24; $hr++) {
						for ($min = 0; $min < 60; $min += 30) {
							$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}
					$postText = ' <select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
					foreach ($times as $time)
						$postText .= '<option value="'.$time.'"'.($time==$calInfo->from_time?' selected="selected"':'').'>'.$time.'</option>';
					$postText .= '</select> ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
					foreach ($times as $time)
						$postText .= '<option value="'.$time.'"'.($time==$calInfo->to_time?' selected="selected"':'').'>'.$time.'</option>';
					$postText .= '</select>
					<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="form-text sg-datepicker -date require" style="width:80px;" autocomplete="off" value="'.htmlspecialchars($calInfo->to_date).'">
					<label class="-inlineblock" style="display: inline-block"><input type="checkbox" name="allday"> ทั้งวัน</label>';

					return $postText;
				})($calInfo),
				'attr' => ['style' => 'width:80px;', 'data-diff' => 'edit-calendar-to_date'],
			],

			'areacode' => ['type' => 'hidden', 'value' => $calInfo->areacode],

			'latlng' => ['type' => 'hidden', 'value' => $calInfo->latlng],

			'location' => [
				'type' => 'text',
				'label' => 'ที่ไหน',
				'class' => 'sg-address -fill',
				'maxlength' => 255,
				'value' => htmlspecialchars($calInfo->location),
				'attr' => array('data-altfld' => 'edit-calendar-areacode'),
				'posttext' => '<a href="javascript:void(0)" id="calendar-addmap"><i class="icon -material" style="position: absolute; right: 0; margin-top: 4px;">place</i></a><div id="calendar-mapcanvas" class="-hidden"></div>',
			],

			'category' => DB::tableExists('%calendar_category%')
				&& ($categorys = mydb::select('SELECT category_id,category_name FROM %calendar_category%')->items) ? [
					'type' => 'select',
					'label' => 'ปฏิทิน',
					'options' => (function($categorys) {
						$options = [];
						foreach ($categorys as $item) {
							$options[$item->category_id] = $item->category_name;
						}
						return $options;
					})($categorys),
					'value' => $calInfo->category,
			] : NULL,

			'detail' => [
				'type' => 'textarea',
				'label' => 'อย่างไร',
				'class' => '-fill',
				'rows' => 3,
				'value' => $calInfo->detail,
			],
			'color' => [
				'type' => 'colorpicker',
				'label' => 'สีของกิจกรรม',
				'color' => 'Red, Green, Blue, Black, Purple,DeepSkyBlue,SteelBlue, DodgerBlue, Navy	, Teal, LimeGreen ,Coral, DarkGoldenRod, Olive, Teal, HotPink, DeepPink,RosyBrown, Brown, Maroon,Magenta,BlueViolet,SlateBlue,Indigo',
				'value' => $calInfo->options->color,
			],

			'privacy' => [
				'type' => 'radio',
				'label' => 'ความเป็นส่วนตัว',
				'options' => [
					'private' => 'ให้ฉันเห็นเพียงคนเดียว',
					'group' => 'ให้มองเห็นเฉพาะในกลุ่ม',
					'public' => 'ให้ทุกคนมองเห็นได้',
				],
				'display' => 'inline',
				'value' => $calInfo->privacy,
			],

			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -back-to-calendar -link -cancel"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => array('class'=>'-sg-text-right'),
			],
		], // children
	]);

	if ($para->module) $form = R::On($para->module.'.calendar.form', $form, $calInfo, $para);

	$ret .= $form->build();

	$gis['zoom'] = 7;

	if ($calInfo->latlng) {
		list($lat,$lng) = explode(',', $calInfo->latlng);
		$gis['center'] = $calInfo->latlng;
		$gis['zoom'] = 10;
		$gis['current'] = [
			'latitude' => $lat,
			'longitude' => $lng,
			'title' => $calInfo->location,
			'content' => '<h4>'.$calInfo->title.'</h4>'.($calInfo->topic_title?'<p><strong>'.$calInfo->topic_title.'</strong></p>':'').($calInfo->location?'<p>สถานที่ : '.$calInfo->location.'</p>':''),
		];
	} else {
		$gis['center'] = property('project:map.center:NULL');
	}

	//$ret.=print_o($calInfo,'$calInfo').print_o($para,'$para');

	$ret .= '<script type="text/javascript">
		var gis = '.json_encode($gis).'
	</script>';

	return $ret;
}
?>