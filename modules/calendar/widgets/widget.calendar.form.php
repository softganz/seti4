<?php
/**
 * Calendar:: Edit Form
 * Created :: 2007-03-06
 * Modify  :: 2025-07-22
 * Version :: 3
 *
 * @param Array $calendarInfo
 * @param Object $para
 * @return String
 *
 * @usage import('widget:calendar.form.php')
 * @usage new CalendarFormWidget([])
 */

use Softganz\DB;

class CalendarFormWidget extends Widget {
	var $calendarInfo;
	var $para;

	function __construct($calendarInfo = [], $para = NULL) {
		parent::__construct([
			'calendarInfo' => (Object) $calendarInfo,
			'para' => (Object) $para
		]);
	}

	#[\Override]
	function build() {
		if (empty($this->calendarInfo->from_date)) $this->calendarInfo->from_date = date('j/n/').(date('Y'));
		if (empty($this->calendarInfo->to_date)) $this->calendarInfo->to_date = $this->calendarInfo->from_date;
		if (empty($this->calendarInfo->from_time)) $this->calendarInfo->from_time = '09:00';
		if (empty($this->calendarInfo->to_time)) {
			list($hr,$min) = explode(':',$this->calendarInfo->from_time);
			$this->calendarInfo->to_time = sprintf('%02d',$hr+1).':'.$min;
		}
		if (empty($this->calendarInfo->privacy)) $this->calendarInfo->privacy  ='public';

		list(,$month,$year) = explode('/',$this->calendarInfo->from_date);

		$form = new Form([
			'variable' => 'calendar',
			'action' => url('api/calendar/'.($this->calendarInfo->calId ? $this->calendarInfo->calId.'/update' : 'create')),
			'id' => 'edit-calendar',
			'class' => 'sg-form',
			'checkValid' => true,
			'children' => [
				'id' => ['type' => 'hidden','value' => $this->calendarInfo->calId],
				'module' => $this->para->module ? ['type' => 'hidden','value' => $this->para->module] : NULL,

				'tpid' => $this->para->module ? ['type' => 'hidden','value' => $this->calendarInfo->tpid] : ($this->calendarInfo->calId && $this->calendarInfo->tpid ? ['type' => 'hidden','value' => $this->calendarInfo->tpid] : NULL),
				'topsave' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="sg-action btn -back-to-calendar -link -cancel" style="position: absolute; left: 0;" data-rel=".calendar-content"><i class="icon -material -gray">navigate_before</i>กลับสู่หน้าปฏิทิน</a><a class="btn -back-to-calendar -link -cancel"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class:"-sg-text-right"}',
				],
				'title' => [
					'type' => 'text',
					'label' => 'ทำอะไร',
					'maxlength' => 255,
					'class' => '-fill',
					'require' => true,
					'value' => htmlspecialchars($this->calendarInfo->title),
				],

				'from_date' => [
					'type' => 'text',
					'label' => 'เมื่อไหร่',
					'class' => 'sg-datepicker -date',
					'autocomplete' => 'off',
					'value' => $this->calendarInfo->from_date,
					'posttext' => (function($calendarInfo) {
						for ($hr = 7; $hr < 24; $hr++) {
							for ($min = 0; $min < 60; $min += 30) {
								$times[] = sprintf('%02d',$hr).':'.sprintf('%02d',$min);
							}
						}
						$postText = ' <select class="form-select" name="calendar[from_time]" id="edit-calendar-from_time">';
						foreach ($times as $time)
							$postText .= '<option value="'.$time.'"'.($time==$this->calendarInfo->from_time?' selected="selected"':'').'>'.$time.'</option>';
						$postText .= '</select> ถึง <select class="form-select" name="calendar[to_time]" id="edit-calendar-to_time">';
						foreach ($times as $time)
							$postText .= '<option value="'.$time.'"'.($time==$this->calendarInfo->to_time?' selected="selected"':'').'>'.$time.'</option>';
						$postText .= '</select>
						<input type="text" name="calendar[to_date]" id="edit-calendar-to_date" maxlength="10" class="form-text sg-datepicker -date require" style="width:80px;" autocomplete="off" value="'.htmlspecialchars($this->calendarInfo->to_date).'">
						<label class="-inlineblock" style="display: inline-block"><input type="checkbox" name="allday"> ทั้งวัน</label>';

						return $postText;
					})($this->calendarInfo),
					'attr' => ['style' => 'width:80px;', 'data-diff' => 'edit-calendar-to_date'],
				],

				'areacode' => ['type' => 'hidden', 'value' => $this->calendarInfo->areacode],

				'latlng' => ['type' => 'hidden', 'value' => $this->calendarInfo->latlng],

				'location' => [
					'type' => 'text',
					'label' => 'ที่ไหน',
					'class' => 'sg-address -fill',
					'maxlength' => 255,
					'value' => htmlspecialchars($this->calendarInfo->location),
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
						'value' => $this->calendarInfo->category,
				] : NULL,

				'detail' => [
					'type' => 'textarea',
					'label' => 'อย่างไร',
					'class' => '-fill',
					'rows' => 3,
					'value' => $this->calendarInfo->detail,
				],
				'color' => [
					'type' => 'colorpicker',
					'label' => 'สีของกิจกรรม',
					'color' => 'Red, Green, Blue, Black, Purple,DeepSkyBlue,SteelBlue, DodgerBlue, Navy	, Teal, LimeGreen ,Coral, DarkGoldenRod, Olive, Teal, HotPink, DeepPink,RosyBrown, Brown, Maroon,Magenta,BlueViolet,SlateBlue,Indigo',
					'value' => $this->calendarInfo->options->color,
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
					'value' => $this->calendarInfo->privacy,
				],

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -back-to-calendar -link -cancel"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => array('class'=>'-sg-text-right'),
				],
			], // children
		]);

		if ($this->para->module) {
			$moduleForm = R::On($this->para->module.'.calendar.form', $form, $this->calendarInfo, $this->para);
			if ($moduleForm) $form = $moduleForm;
		}

		// $gis['zoom'] = 7;

		// if ($this->calendarInfo->latlng) {
		// 	list($lat,$lng) = explode(',', $this->calendarInfo->latlng);
		// 	$gis['center'] = $this->calendarInfo->latlng;
		// 	$gis['zoom'] = 10;
		// 	$gis['current'] = [
		// 		'latitude' => $lat,
		// 		'longitude' => $lng,
		// 		'title' => $this->calendarInfo->location,
		// 		'content' => '<h4>'.$this->calendarInfo->title.'</h4>'.($this->calendarInfo->topic_title?'<p><strong>'.$this->calendarInfo->topic_title.'</strong></p>':'').($this->calendarInfo->location?'<p>สถานที่ : '.$this->calendarInfo->location.'</p>':''),
		// 	];
		// } else {
		// 	$gis['center'] = property('project:map.center:NULL');
		// }

		// $ret .= '<script type="text/javascript">
		// 	var gis = '.json_encode($gis).'
		// </script>';
		
		return $form;
	}
}
?>