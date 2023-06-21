<?php
/********************************************
* Class :: Report
* Report class for create Report
*
* Created 2020-10-01
* Modify  2020-10-01
*
* Property
* config {nav: "nav -icons"}
*
* @usage new Report()
********************************************/

class Report extends Widget {
	var $queryUrl = '';
	var $graphType = 'Bar';
	var $groupUi = NULL;
	var $optionsUi = NULL;
	var $reportType = Array();
	var $output = Array();

	function __construct($queryUrl, $class = NULL) {
		$this->initConfig();
		$this->config->class = 'sg-drawreport';
		$this->config->dataType = 'json';
		$this->groupUi = new Ui();
		$this->optionsUi = new Ui();
		$this->queryUrl = $queryUrl;
		if ($class) $this->config->class = $this->config->class.' '.$class;
	}

	function filter($key, $value) {
		$this->reportType[$key] = $value;
	}

	function output($key, $html = NULL) {
		$this->output[$key] = $html;
	}

	function _render_checkbox($items, $typeValue) {
		$ret = '';
		foreach ($items as $selKey => $selVal) {
			$inputType = \SG\getFirst($typeValue['type'], 'checkbox');
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
				. 'class="-checkbox-'.$filter.' -filter-checkbox" '
				. 'type="'.$inputType.'" '
				. 'name="'.$filter.'[]" '
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

	function build() {
		foreach ($this->reportType as $typeId => $typeValue) {
			//if (empty($typeValue)) continue;
			$groupUiStr = $typeValue['group'] ? '<span class="-group-name"><a class="-submit -submit-group" href="#'.$typeId.'"><span>'.$typeValue['group'].'</span></a></span>' : '';
			if (isset($typeValue['select'])) {
				$checkbox = $this->_render_checkbox($typeValue['select'],$typeValue);
				//if ($checkbox) {
					$groupUiStr .= _NL.'	'.sg_dropbox(
						_NL.'	<nav class="nav -top">ตัวเลือก:<a class="btn -link -hidden">Select all</a> <a class="btn -link -hidden">None</a></nav>'._NL
						. '	<div class="-checkbox">'._NL
						. $checkbox
						. '	</div><!-- checkbox -->'._NL
						. '	<nav class="nav -footer"><a class="btn -primary -submit" onClick="$(\'.sg-dropbox\').children(\'div\').hide()">Apply</a></nav>'._NL.'	',
						'{class: "rightside -not-hide", icon: "material", iconText: "expand_more",text: "'.$typeValue['text'].'"}'
					)._NL;
				//}
			}
			$groupUiStr .= '	<span class="-check-count -hidden"><span class="-amt"></span><span class="-unit">ตัวเลือก</span></span>'._NL;

			$this->groupUi->add(
				$groupUiStr,
				'{class: "'.('-group-'.$typeValue['filter']).($typeValue['active'] ? '-active' : '').($typeValue['group'] ? '' : ' -no-name').'"}'
			);
		}


		$ret = '<div '._NL
			. ($this->id ? '	id="'.$this->id.'"'._NL : '')
			. ' class="'.$this->config->class.'"'._NL
			. ' data-query="'.$this->queryUrl.'"'._NL
			. ' data-options=\'{"dataType":"'.$this->config->dataType.'"}\'';
		foreach ($this->config->data as $key => $value) {
			$ret .= ' '.$key.'="'.$value.'"';
		}
		foreach ($this->output as $key => $html) {
			$ret .= _NL.'	data-show-'.$key.'="#report-output-'.$key.'"';
		}
		$ret .= '>'._NL;

		$ret .= '<form class="form" id="report-form" data-rel="none" method="get" action="">'._NL
			. '<input type="hidden" name="dataType" value="'.$this->config->dataType.'" />'._NL
			. '<input type="hidden" name="r" id="reporttype" value="" />'._NL
			. '<input type="hidden" name="g" id="graphtype" value="'.$this->graphType.'" />'._NL
			. ($this->config->showPage ? '<input id="page" type="hidden" name="page" value="" />'._NL : '')
			. (post('debug') && user_access('access debugging program') ? '<input type="hidden" name="debug" value="yes" />'._NL : '');

		$ret .= '<div class="toolbar -report">';
		$ret .= '<div class="-filter"><span class="-text">{tr:Filter}</span>'
			. '<span id="toolbar-report-filter" class="-select">'
			. ($this->config->filterPretext ? $this->config->filterPretext : '')
			. '<span class="-item"></span>'
			. '</span><!-- toolbar-report-filter -->'
			. '<span class=""><button class="btn -primary -submit" type="submit">'.($this->config->submitIcon ? '' : '<i class="icon -material">search</i>').''.($this->config->submitText ? '<span>ดูแผนงาน</span>' : '').'</button></span>'._NL
			. '</div><!-- -filter -->'._NL;

		if ($this->groupUi->count()) {
			$ret .= '<div class="-group">'._NL;
			if ($this->config->showArrowLeft) $ret .= '<a class="group-nav -left"><i class="icon -material">navigate_before</i></a>';
			$ret .= $this->groupUi->build()._NL;
			if ($this->config->showArrowRight) $ret .= '<a class="group-nav -right"><i class="icon -material">navigate_next</i></a>'._NL;
			$ret .= '</div>'._NL;
		}

		$ret .= $this->optionsUi->count() ? '<div class="-options">'._NL.$this->optionsUi->build()._NL.'</div>'._NL : '';

		$ret .= '</div><!-- toolbar -report -->'._NL;
		$ret .= '</form>'._NL._NL;

		$ret .= '<div id="report-output" class="report-output">'._NL._NL;
		$ret .= '<div id="report-output-debug" class="report-output-debug"></div>'._NL._NL;
		foreach ($this->output as $key => $html) {
			$ret .= '<div id="report-output-'.$key.'" class="report-output-'.$key.'">'.$html.'</div>'._NL._NL;
		}
		$ret .= '</div><!--report-output-->'._NL._NL;

		$ret .= '</div><!-- sg-drawreport -->'._NL._NL._NL;

		return $ret;
	}
} // End of class Toolbar
?>