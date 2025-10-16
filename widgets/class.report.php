<?php
/**
 * Widget  :: Report Widget
 * Created :: 2020-10-01
 * Modify  :: 2025-08-26
 * Version :: 12
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
	var $showArrowLeft;
	var $showArrowRight;
	var $filterPretext;
	var $showPage = false;
	var $debug = false;
	var $input = [];
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

	function _render_checkbox($items, $filterValue) {
		$inputs = [];
		foreach ($items as $selKey => $selVal) {
			$inputType = \SG\getFirst($filterValue['type'], 'checkbox');
			$inputTypeMultiple = $inputType === 'checkbox';
			$selItem = (Object) [];
			if (is_string($selVal)) {
				$selItem->label = $selVal;
			} else if (is_array($selVal)) {
				$selItem = (Object) $selVal;
			} else if (is_object($selVal)) {
				// Do nothing
			} else {
				continue;
			}

			$filter = \SG\getFirst(is_object($selItem) ? $selItem->filter : NULL, $filterValue['name'], $filterValue['filter']);

			$renderItem = '<abbr class="'.(preg_match('/^\t/', $selItem->label) ? '-level-2' : '').'">'
				. '<label>'
				. '<input '
				. 'id="'.$filter.'_'.$selKey.'" '
				. 'class="-checkbox-'.$filter.' -filter-checkbox'.($filterValue['class'] ? ' '.$filterValue['class'] : '').'" '
				. 'type="'.$inputType.'" '
				. 'name="'.$filter.($inputTypeMultiple ? '[]' : '').'" '
				. 'value="'.$selKey.'" '
				. sg_implode_attr($selItem->attribute).' '
				. '/>'
				. '<span>'.$selItem->label.'</span>'
				. '</label>';
			$renderItem .= '</abbr>';
			$inputs[] = $renderItem;
		}
		return $inputs;
	}

	function toString() {
		// Render filter
		$groupUi = new Ui();
		foreach ($this->filter as $filterKey => $filterValue) {
			if (is_null($filterValue)) continue;

			if (is_object($filterValue)) {
				$groupUi->add($this->_renderEachChildWidget(NULL, $filterValue));
			} else if (is_array($filterValue)) {
				$groupUiStr = $filterValue['group'] ? '<span class="-group-name"><a class="-submit -submit-group" href="#'.$filterKey.'"><span>'.$filterValue['group'].'</span></a></span>' : '';
				$groupUiStr .= (new Dropbox([
					'text' => $filterValue['text'],
					'position' => 'right',
					'childrenContainer' => ['tagName' => 'ul', 'class' => '-checkbox'],
					'children' => $this->_render_checkbox($filterValue['choice'],$filterValue),
					'footer' => new Widget([
						'children' => [
							'<nav class="nav -footer"><a class="btn -primary -submit" onClick="$(\'.sg-dropbox\').children(\'div\').hide()"><i class="icon -material">done</i><span>Apply</span></a></nav>',
						],
					]),
				]))->build();
				$groupUiStr .= '<span class="-check-count -hidden"><span class="-amt"></span><span class="-unit">ตัวกรอง</span></span>'._NL;
				$groupUi->add(
					$groupUiStr,
					'{class: "'.('-group-'.$filterValue['filter']).($filterValue['active'] ? '-active' : '').($filterValue['group'] ? '' : ' -no-name').'"}'
				);
			} else {
				$groupUi->add($filterValue);
			}
		}

		// Render widget container
		$ret = $this->_renderWidgetContainerStart(function() {
			$attributes = [
				'data-query' => $this->queryUrl,
				'data-callback' => $this->callback,
				'data-options' => [
					'dataType' => $this->dataType,
				],
			];
			if ($this->debug) $attributes['data-options']['debug'] = true;

			foreach ($this->data as $key => $value) {
				$attributes[$key] = $value;
			}

			foreach ($this->output as $key => $html) {
				$attributes['data-show-'.$key] = '#report-output-'.$key;
			}

			return sg_implode_attr($attributes, "\r");
		});

		// Render toolbar form
		$this->children['form'] = new Form([
			'class' => 'form',
			'action' => 'javascript:void(0)',
			'children' => [
				'<input type="hidden" name="dataType" value="'.$this->dataType.'" />',
				'<input type="hidden" name="r" id="reporttype" value="" />',
				'<input type="hidden" name="g" id="graphtype" value="'.$this->graphType.'" />',
				'<input type="hidden" name="metric" id="metric" value="" />',
				new Children(['children' => (Array) $this->input]),
				// ...$this->input, // error on php 7.2
				$this->showPage ? '<input id="page" type="hidden" name="page" value="" />' : '',
				$this->debug && user_access('access debugging program') ? '<input type="hidden" name="debug" value="report" />' : '',

				'<div class="-toolbar">',
				// Metric
				'<div class="-metric">'.$this->_renderChildren($this->metric).'</div><!-- -metric -->',
				// filter bar & submit button
				'<div class="-filter">'
					. '<span class="-title -text">'
					. (is_array($this->filterBar) ? $this->_renderEachChildWidget(NULL, SG\getFirst($this->filterBar['title'], '{tr:Filter by}')) : SG\getFirst($this->filterBar, '{tr:Filter by}'))
					. '</span>'
					. '<span id="toolbar-report-filter" class="-select">'
					. ($this->filterPretext ? $this->_renderEachChildWidget(NULL, $this->filterPretext) : '')
					. '<span id="toolbar-report-filter-items" class="toolbar-report-filter-items -item"></span>'
					. '</span><!-- toolbar-report-filter -->'
					. '<span class="">'
					. '<button class="btn -primary -submit" type="submit">'.($this->submitIcon ? $this->submitIcon : '<i class="icon -material">search</i>').''.($this->submitText ? '<span>'.$this->submitText.'</span>' : '').'</button>'
					. '</span>'._NL
					. '</div><!-- -filter -->'._NL,

				// Filter & Metric
				($groupUi->count() ?
					'<div class="-group">'._NL
						. ($this->showArrowLeft ? '<a class="group-nav -left"><i class="icon -material">navigate_before</i></a>' : '')
						. $groupUi->build()._NL
						. ($this->showArrowRight ? '<a class="group-nav -right"><i class="icon -material">navigate_next</i></a>'._NL : '')
						. '</div>'._NL
					: ''),
				// Option bar
				new Row([
					'tagName' => 'div',
					'class' => '-options',
					'children' => $this->optionBar,
				]),
				'</div><!-- toolbar -report -->'._NL,
			]
		]);

		// Render output element
		$this->children['output'] = new Container([
			'id' => 'report-output',
			'class' => 'report-output',
			'children' => array_replace(
				[
					'debug' => '<div id="report-output-debug" class="report-output-debug debug-msg"></div>'
				],
				array_map(
					function($html, $key) {
						return '<div id="report-output-'.$key.'" class="report-output-'.$key.'">'.$html.'</div>';
					},
					$this->output, array_keys($this->output)
				)
			),
		]);

		$ret .= $this->_renderChildren($this->children());
		$ret .= $this->_renderWidgetContainerEnd().'<!-- -->';

		return $ret;
	}
} // End of class Toolbar
?>