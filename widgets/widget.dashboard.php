<?php
/**
* Dashboard :: Dashboard Widget
* Created   :: 2023-12-16
* Modify    :: 2023-12-16
* Version   :: 1
*
* @param Array $args
* @return Widget
*
* @usage import('widget:dashboard.php')
* @usage new DashboardWidget([])
*/

class DashboardWidget extends Widget {
	var $widgetName = 'Dashboard';
	var $tagName = 'div';
	var $more;
	var $total;
	var $orgId;

	function __construct($args = []) {
		parent::__construct($args);
	}

	// @override
	function _renderEachChildWidget($key, $widget, $callbackFunction = []) {
		// debugMsg($widget, '$widget');
		return parent::_renderEachChildWidget(
			$key,
			$widget,
			[
				'array' => function($key, $widget) {
					return $this->_renderChildType($key, (Object) $widget);
				},
				'text' => function($key, $text) {
					return $text._NL;
				}
			]
		);
	}

	private function _renderChildType($key, $widget = '{}') {
		return (new Container([
			'class' => $widget->class,
			'children' => [
				$widget->title ? '<span class="-title">'.$widget->title.'</span>' : NULL,
				isset($widget->value) ? '<span class="-value">'.$widget->value.'</span>' : NULL,
				$widget->unit ? '<span class="-unit">'.$widget->unit.'</span>' : NULL,
				$widget->chart ? $this->drawChart($widget) : NULL,
				// new DebugMsg($widget, '$widget')
			], // children
		]))->build();

		switch ($widget->type) {
			// case 'textfield': $ret .= $this->_renderTypeTextField($text); break;
			// case 'radio':
			// case 'checkbox': $ret .= $this->_renderTypeRadio($widget); break;
			// case 'select': $ret .= $this->_renderTypeSelect($text); break;
			default: $ret .= $this->_renderTypeText($text, $widget); break;
		}

		return $ret;
	}

	function xbuild() {
		// $admitCount = mydb::select('SELECT COUNT(*) `amt` FROM %db_person% WHERE `admit` = "YES" LIMIT 1')->amt;

		// $joins = [];

		// mydb::where('m.`qtform` = "SMIV"');
		// if ($this->orgId) {
		// 	mydb::where('sp.`orgId` = :orgId', ':orgId', $this->orgId);
		// 	$joins[] = 'LEFT JOIN %imed_socialpatient% sp ON sp.`orgid` = ":orgId" AND sp.`psnid` = m.`psnid`';
		// }

		// mydb::value('$JOIN$', implode(_NL, $joins), false);
		// $patientCount = mydb::select(
		// 	'SELECT
		// 	COUNT(IF(`value` <= 9, 1, NULL)) `green`
		// 	, COUNT(IF(`value` BETWEEN 10 AND 18, 1, NULL)) `yellow`
		// 	, COUNT(IF(`value` >= 19, 1, NULL)) `red`
		// 	FROM (
		// 		SELECT m.`psnid`, m.`value`
		// 		FROM %qtmast% m
		// 			$JOIN$
		// 		%WHERE%
		// 		GROUP BY m.`psnid`
		// 		ORDER BY m.`qtdate` DESC
		// 	) a
		// 	LIMIT 1
		// 	'
		// );
		// debugMsg($patientCount, '$patientCount');

		// if (isset($this->total)) {
		// 	$patientCount->green = $this->total - $patientCount->yellow - $patientCount->red;
		// }
		return new Widget([
			'tagName' => 'div',
			'style' => $this->style,
			'children' => [
				$this->title,

				new ScrollView([
					'child' => new Widget([
						'class' => '',
						'children' => array_map(
							function($child) {
								// switch ($child['type']) {
								// 	case 'chart':
								// 		// code...
								// 		break;

								// 	default:
								// 		// code...
								// 		break;
								// }
								return new Container([
									'class' => $child['class'],
									'children' => [
										$child['title'] ? '<span class="-title">'.$child['title'].'</span>' : NULL,
										isset($child['value']) ? '<span class="-value">'.$child['value'].'</span>' : NULL,
										$child['unit'] ? '<span class="-unit">'.$child['unit'].'</span>' : NULL,
										$child['chart'] ? $this->drawChart($child) : NULL,
										new DebugMsg($child, '$child')
									], // children
								]);
							},
							$this->children
						)
					]), // Row
				]), // ScrollView
				new DebugMsg($this, '$this'),
			], // children
		]);
						// [
							// new Container([
							// 	'class' => ($this->orgId && $patientCount->green > 0 ? 'sg-action ' : '').'-green',
							// 	'href' => $this->orgId && $patientCount->green > 0 ? url('imed/psyc/group/'.$this->orgId.'/patient.status.green') : NULL,
							// 	'rel' => $this->orgId ? 'box' : NULL,
							// 	'boxWidth' => 480,
							// 	'webview' => 'ผู้ป่วยอาการปกติ',
							// 	'children' => [
							// 		'<span>อาการปกติ</span>',
							// 		'<span class="-number">'.$patientCount->green.'</span>',
							// 		'<span style="position: absolute; top: 3.2rem; right: 8px;">คน</span>',
							// 	],
							// ]),
							// new Container([
							// 	'class' => ($this->orgId && $patientCount->yellow > 0 ? 'sg-action ' : '').'-yellow',
							// 	'href' => $this->orgId && $patientCount->yellow ? url('imed/psyc/group/'.$this->orgId.'/patient.status.yellow') : NULL,
							// 	'rel' => $this->orgId ? 'box' : NULL,
							// 	'boxWidth' => 480,
							// 	'webview' => 'ผู้ป่วยเฝ้าระวัง',
							// 	'children' => [
							// 		'<span>เฝ้าระวัง</span>',
							// 		'<span class="-number">'.$patientCount->yellow.'</span>',
							// 		'<span style="position: absolute; top: 3.2rem; right: 8px;">คน</span>',
							// 	],
							// ]),
							// new Container([
							// 	'class' => ($this->orgId && $patientCount->red > 0 ? 'sg-action ' : '').'-red',
							// 	'href' => $this->orgId && $patientCount->red ? url('imed/psyc/group/'.$this->orgId.'/patient.status.red') : NULL,
							// 	'rel' => $this->orgId ? 'box' : NULL,
							// 	'boxWidth' => 480,
							// 	'webview' => 'ผู้ป่วยติดตามใกล้ชิด',
							// 	'children' => [
							// 		'<span>ติดตามใกล้ชิด</span>',
							// 		'<span class="-number">'.$patientCount->red.'</span>',
							// 		'<span style="position: absolute; top: 3.2rem; right: 8px;">คน</span>',
							// 	],
							// ]),
							// new Container([
							// 	'class' =>  ($this->orgId && $admitCount > 0 ? 'sg-action ' : '').'-admit',
							// 	'href' => $this->orgId && $admitCount > 0 ? url('imed/psyc/group/'.$this->orgId.'/patient.status.admit') : NULL,
							// 	'rel' => $this->orgId && $admitCount > 0 ? 'box' : NULL,
							// 	'boxWidth' => 480,
							// 	'webview' => 'ผู้ป่วย Admit',
							// 	'children' => [
							// 		'<span>ADMIT</span>',
							// 		'<span class="-number">'.$admitCount.'</span>',
							// 		'<span style="position: absolute; top: 3.2rem; right: 8px;">คน</span>',
							// 	],
							// ]),
						// ], // children
	}

}
?>