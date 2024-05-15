<?php
/**
* Widget  :: Report Widget
* Created :: 2020-10-01
* Modify  :: 2024-01-15
* Version :: 3
*
* @param Array $args
* @return Widget
*
* @usage import('widget:report.php')
* @usage new Report([])
*/

class Report extends Widget {
	var $widgetName = 'Report';
	var $tagName = 'div';
	var $version = '0.02';
	var $queryUrl = '';
	var $dataType = 'json';
	var $graphType = 'Bar';
	var $class = 'sg-drawreport';
	var $submitIcon;
	var $submitText;
	var $filterBar;
	var $optionBar = [];
	var $filter = [];
	var $output = [];

	function __construct($queryUrl, $class = NULL) {
		$this->initConfig();

		if (is_array($queryUrl)) {
			parent::__construct($queryUrl);
		} else {
			$this->queryUrl = $queryUrl;
			if ($class) $this->class = $this->class.' '.$class;
		}
	}

	function filter($key, $value) {
		$this->filter[$key] = $value;
	}

	function output($key, $html = NULL) {
		$this->output[$key] = $html;
	}

	function _render_checkbox($items, $typeValue) {
		$ret = '';
		foreach ($items as $selKey => $selVal) {
			$inputType = \SG\getFirst($typeValue['type'], 'checkbox');
			$inputTypeMultiple = $inputType === 'checkbox';
			$selItem = (Object) [];
			if (is_string($selVal)) {
				$selItem->label = $selVal;
			} else if (is_array($selVal)) {
				$selItem = (Object) $selVal;
			} else if (is_object($selVal)) {
				;
			} else {
				continue;
			}

			$filter = \SG\getFirst(is_object($selItem) ? $selItem->filter : NULL, $typeValue['filter']);

			$ret .= '		<abbr class="'.(preg_match('/^\t/', $selItem->label) ? '-level-2' : '').'">'
				. '<label>'
				. '<input '
				. 'id="'.$filter.'_'.$selKey.'" '
				. 'class="-checkbox-'.$filter.' -filter-checkbox'.($typeValue['class'] ? ' '.$typeValue['class'] : '').'" '
				. 'type="'.$inputType.'" '
				. 'name="'.$filter.($inputTypeMultiple ? '[]' : '').'" '
				. 'value="'.$selKey.'" '.sg_implode_attr($selItem->attr).' '
				. '/>'
				. '<span>'.$selItem->label.'</span>'
				. '</label>'._NL;
			if ($selItem->items) {
				//debugMsg($selVal, $selKey);
				$ret .= '<span>'.$this->_render_checkbox($selItem->items, $typeValue).'</span>';
			}
			$ret .= '</abbr>'._NL;
		}
		return $ret;
	}

	// @override
	// function _renderWidgetContainerStart($callbackFunction = NULL) {
	// 	return parent::_renderChildContainerStart(function() {
	// 		return 'aaaa';
	// 	});
	// }

	function toString() {
		$groupUi = new Ui();
		foreach ($this->filter as $typeId => $typeValue) {
			//if (empty($typeValue)) continue;
			if (is_object($typeValue)) {
				$groupUi->add($this->_renderEachChildWidget(NULL, $typeValue));
			} else {
				$groupUiStr = $typeValue['group'] ? '<span class="-group-name"><a class="-submit -submit-group" href="#'.$typeId.'"><span>'.$typeValue['group'].'</span></a></span>' : '';
				if (isset($typeValue['select'])) {
					$checkbox = $this->_render_checkbox($typeValue['select'],$typeValue);
					// $groupUiStr .= _NL.'	'
					// 	. (new Dropbox([
					// 		'text' => 'ตัวกรอง:',
					// 		'children' => [
					// 			$this->_render_checkbox($typeValue['select'],$typeValue)
					// 		],
					// 	// . '	<nav class="nav -top">ตัวกรอง:<a class="btn -link -hidden">Select all</a> <a class="btn -link -hidden">None</a></nav>'._NL
					// 	// . '	<div class="-checkbox">'._NL
					// 	// . $checkbox
					// 	// . '	</div><!-- checkbox -->'._NL
					// 	// . '	<nav class="nav -footer"><a class="btn -primary -submit" onClick="$(\'.sg-dropbox\').children(\'div\').hide()">Apply</a></nav>'._NL.'	',
					// 	// '{class: "rightside -not-hide", icon: "material", iconText: "expand_more",text: "'.$typeValue['text'].'"}'
					// ]))->build()._NL;
					$groupUiStr .= _NL.'	'.sg_dropbox(
						_NL.'	<nav class="nav -top">ตัวกรอง:<a class="btn -link -hidden">Select all</a> <a class="btn -link -hidden">None</a></nav>'._NL
						. '	<div class="-checkbox">'._NL
						. $checkbox
						. '	</div><!-- checkbox -->'._NL
						. '	<nav class="nav -footer"><a class="btn -primary -submit" onClick="$(\'.sg-dropbox\').children(\'div\').hide()">Apply</a></nav>'._NL.'	',
						'{class: "rightside -not-hide", icon: "material", iconText: "expand_more",text: "'.$typeValue['text'].'"}'
						)._NL;
				}
				$groupUiStr .= '	<span class="-check-count -hidden"><span class="-amt"></span><span class="-unit">ตัวกรอง</span></span>'._NL;
				$groupUi->add(
					$groupUiStr,
					'{class: "'.('-group-'.$typeValue['filter']).($typeValue['active'] ? '-active' : '').($typeValue['group'] ? '' : ' -no-name').'"}'
				);
			}
		}


		// $ret = '<div '._NL
		// 	. ($this->id ? '	id="'.$this->id.'"'._NL : '')
		// 	. ' class="widget-report '.$this->class.'"'._NL
		// 	. ' data-query="'.$this->queryUrl.'"'._NL
		// 	. ' data-options=\'{"dataType":"'.$this->dataType.'"}\'';

		// foreach ($this->config->data as $key => $value) {
		// 	$ret .= ' '.$key.'="'.$value.'"';
		// }

		// foreach ($this->output as $key => $html) {
		// 	$ret .= _NL.'	data-show-'.$key.'="#report-output-'.$key.'"';
		// }

		// $ret .= sg_implode_attr($this->attribute);
		// $ret .= '>'._NL;

		// render widget container
		$ret = $this->_renderWidgetContainerStart(function(){
			$attributes = [
				'data-query' => $this->queryUrl,
				'data-callback' => $this->callback,
				'data-options' => [
					'dataType' => $this->dataType,
				],
			];
			if ($this->debug) $attributes['data-options']['debug'] = true;

			foreach ($this->config->data as $key => $value) {
				$attributes[$key] = $value;
			}

			foreach ($this->output as $key => $html) {
				$attributes['data-show-'.$key] = '#report-output-'.$key;
			}

			return sg_implode_attr($attributes, "\r");
		});

		$form = '<form class="form" id="report-form" data-rel="none" method="get" action="">'._NL
			. '<input type="hidden" name="dataType" value="'.$this->dataType.'" />'._NL
			. '<input type="hidden" name="r" id="reporttype" value="" />'._NL
			. '<input type="hidden" name="g" id="graphtype" value="'.$this->graphType.'" />'._NL
			. ($this->config->showPage ? '<input id="page" type="hidden" name="page" value="" />'._NL : '')
			. (post('debug') && user_access('access debugging program') ? '<input type="hidden" name="debug" value="report" />'._NL : '');

		$form .= '<div class="-toolbar">';

		$form .= '<div class="-filter">'
			. '<span class="-title -text">'.$this->_renderEachChildWidget(NULL, SG\getFirst($this->filterBar['title'], '{tr:Filter by}')).'</span>'
			. ($this->filterBar ? (
				function() {
					$form = '';
					foreach ($this->filterBar['children'] as $key => $widget) {
						$form .= '<span class="-item">'.$this->_renderEachChildWidget($key, $widget).'</span>';
					}
					return $form;
				}
			)() : '')
			. '<span id="toolbar-report-filter" class="-select">'
			. ($this->config->filterPretext ? $this->config->filterPretext : '')
			. '<span id="toolbar-report-filter-items" class="toolbar-report-filter-items -item" style="flex: 1;"></span>'
			. '</span><!-- toolbar-report-filter -->'
			. '<span class="">'
			. '<button class="btn -primary -submit" type="submit">'.($this->submitIcon ? $this->submitIcon : '<i class="icon -material">search</i>').''.($this->submitText ? '<span>'.$this->submitText.'</span>' : '').'</button>'
			. '</span>'._NL
			. '</div><!-- -filter -->'._NL;

		if ($groupUi->count()) {
			$form .= '<div class="-group">'._NL;
			if ($this->config->showArrowLeft) $form .= '<a class="group-nav -left"><i class="icon -material">navigate_before</i></a>';
			$form .= $groupUi->build()._NL;
			if ($this->config->showArrowRight) $form .= '<a class="group-nav -right"><i class="icon -material">navigate_next</i></a>'._NL;
			$form .= '</div>'._NL;
		}

		if ($this->optionBar) {
			// $form .= '<div class="-options">'._NL;
			$form .= (new Widget([
				'tagName' => 'div',
				'class' => '-options',
				'children' => $this->optionBar,
			]))->build();
			// $this->optionsUi->build()._NL;
			// $form .= '</div>'._NL;
		}

		$form .= '</div><!-- toolbar -report -->'._NL;
		$form .= '</form>'._NL._NL;

		$this->children['form'] = new Form([
			'class' => 'form',
			'action' => 'javascript:void(0)',
			'children' => [
				'<input type="hidden" name="dataType" value="'.$this->dataType.'" />',
				'<input type="hidden" name="r" id="reporttype" value="" />',
				'<input type="hidden" name="g" id="graphtype" value="'.$this->graphType.'" />',
				'<input type="hidden" name="metric" id="metric" value="" />',
				$this->config->showPage ? '<input id="page" type="hidden" name="page" value="" />' : '',
				post('debug') && user_access('access debugging program') ? '<input type="hidden" name="debug" value="report" />' : '',

				'<div class="-toolbar">',
				'<div class="-filter">'
					. '<span class="-title -text">'.$this->_renderEachChildWidget(NULL, SG\getFirst($this->filterBar['title'], '{tr:Filter by}')).'</span>'
					. ($this->filterBar ? (
						function() {
							$form = '';
							foreach ($this->filterBar['children'] as $key => $widget) {
								$form .= '<span class="-item">'.$this->_renderEachChildWidget($key, $widget).'</span>';
							}
							return $form;
						}
					)() : '')
					. '<span id="toolbar-report-filter" class="-select">'
					. ($this->config->filterPretext ? $this->config->filterPretext : '')
					. '<span id="toolbar-report-filter-items" class="toolbar-report-filter-items -item" style="flex: 1;"></span>'
					. '</span><!-- toolbar-report-filter -->'
					. '<span class="">'
					. '<button class="btn -primary -submit" type="submit">'.($this->submitIcon ? $this->submitIcon : '<i class="icon -material">search</i>').''.($this->submitText ? '<span>'.$this->submitText.'</span>' : '').'</button>'
					. '</span>'._NL
					. '</div><!-- -filter -->'._NL,

				($groupUi->count() ?
					'<div class="-group">'._NL
						. ($this->config->showArrowLeft ? '<a class="group-nav -left"><i class="icon -material">navigate_before</i></a>' : '')
						. $groupUi->build()._NL
						. ($this->config->showArrowRight ? '<a class="group-nav -right"><i class="icon -material">navigate_next</i></a>'._NL : '')
						. '</div>'._NL
					: ''),
				new Row([
					'tagName' => 'div',
					'class' => '-options',
					'children' => $this->optionBar,
				]),
				'</div><!-- toolbar -report -->'._NL,
			]
		]);

		$this->children['output'] = new Container([
			'id' => 'report-output',
			'class' => 'report-output',
			'children' => array_merge(
				['<div id="report-output-debug" class="report-output-debug debug-msg" style="display: none;"></div>'],
				array_map(
					function($html, $key) {
						return '<div id="report-output-'.$key.'" class="report-output-'.$key.'">'.$html.'</div>';
					},
					$this->output, array_keys($this->output)
				)
			),
		]);

		$ret .= $this->_renderChildren($this->children);
		$ret .= $this->_renderWidgetContainerEnd().'<!-- -->';

		return $ret;
	}
} // End of class Toolbar
?>