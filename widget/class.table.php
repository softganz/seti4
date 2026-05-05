<?php
/**
* Widget  :: Table Widget
* Created :: 2020-10-01
* Modify  :: 2026-05-05
* Version :: 7
*
* @param Array $args
* @return Widget
*
* @usage new Table([key => value,...])
*/

class Table extends Widget {
	var $config = [];
	var $showHeader = true;
	var $repeatHeader = 0;
	var $rows = []; // @deprecated

	private $headerKey = [];
	private $headerTag = '';
	private $rowNo = 0;

	function __construct($args = []) {
		if (is_array($args)) {
			parent::__construct($args);
		}
	}

	// @deprecated
	// Add table class
	function addClass($class) {
		$this->class .= ' '.$class;
	}

	// @deprecated
	// Add table config
	function addConfig($key,$value) {
		$this->config->{$key} = $value;
	}

	#[\Override]
	function toString() {
		if (!isset($this->thead) && isset($this->header)) {
			$this->thead = $this->header;
		}

		// Set children value from rows value for backward compatable
		if ($this->rows) {$this->children = $this->rows;}
	 	unset($this->rows);

		// Create table tag
		$ret = '<table '
			. (isset($this->id) ? ' id="' . $this->id . '"' : '')
			. 'class="widget-' . strtolower($this->widgetName) . ' item' . ($this->class ? ' ' . $this->class : '')
			. ($this->mainAxisAlignment ? ' -main-axis-' . strtolower($this->mainAxisAlignment) : '')
			. ($this->crossAxisAlignment ? ' -cross-axis-' . strtolower($this->crossAxisAlignment) : '')
			. '" '
		  . ($this->style ? ' style="' . $this->style . '"' : '')
			. ($this->attribute && is_array($this->attribute) ? ' ' . sg_implode_attr($this->attribute) : '');

		// @deprecated
		if (isset($this->attr)) {
			if (is_string($this->attr)) {
				$ret .= ' ' . $this->attr;
			} else if (is_array($this->attr)) {
				foreach ($this->attr as $k => $v)
					$ret .= ' ' . $k . '="' . $v . '"';
			}
		}

		$ret .= '>' . _NL;

		// Create table caption
		$ret .= self::renderCaption();

		// Create table column group
		$ret .= self::renderColGroup();

		// Create table header
		self::renderHeader();
		$ret .= $this->showHeader ? '<thead>' . $this->headerTag . '</thead>' . _NL : '';

		// Create table rows
		$ret .= self::renderBody($this->headerTag);

		// Create table footer
		$ret .= self::renderFooter();

		$ret .= '</table>' . _NL;

		return $ret;
	}

	// Render table caption
	private function renderCaption() {
		$captionStr = \SG\getFirst($this->caption, $this->config->caption);

		return $captionStr ? '<caption>'.$captionStr.'</caption>'._NL : '';
	}

	// Render table column group
	private function renderColGroup() {
		$ret = '';

		if (isset($this->colgroup) && is_array($this->colgroup)) {
			$ret .= '<colgroup>'._NL;
			$colNo = 1;
			foreach ($this->colgroup as $key => $value) {
				if (is_array($value) || (is_string($value) && substr($value, 0, 1) == '{')) {
					$value = sg_implode_attr(sg_json_decode($value));
				}
				$ret .= '<col '.$value.'/>'._NL;
				$this->headerKey[$colNo] = is_numeric($key) ? '-col-'.$colNo : $key;
				$colNo++;
			}
			$ret .= '</colgroup>'._NL;
		}

		return $ret;
	}

	// Set property headerTag
	private function renderHeader() {
		$this->headerTag = '';

		if (isset($this->thead) && is_string($this->thead)) {
			$this->headerTag = str_replace(
				['<thead>', '</thead>', '<tr'],
				['','','<tr class="header"'],
				$this->thead
			);
		} else if (isset($this->thead) && is_array($this->thead)) {
			$colNo = 1;
			$this->headerTag = '<tr class="header">';
			foreach ($this->thead as $thKey => $thValue) {
				if (is_null($thValue)) continue;

				$thKey = is_numeric($thKey) ? $colNo : $thKey;

				if (!$this->colgroup) {
					$this->headerKey[$colNo] = is_numeric($thKey) ? 'col-'.$thKey : $thKey;
				}

				if (is_array($thValue) || is_object($thValue)) {
					$this->headerTag .= '<th'
						. ' class="header-'.$thKey.($thValue['class'] ? ' '.$thValue['class'] : '').'"'
						. ($thValue['style'] ? ' style="'.$thValue['style'].'"' : '')
						. '>'
						. $thValue['text']
						. '</th>';
				} else if (preg_match('/^<th /i', $thValue)) {
					$this->headerTag .= $thValue;
				} else {
					$this->headerTag .= '<th class="header-'.$thKey.'">'.$thValue.'</th>';
				}
				++$colNo;
			}
			$this->headerTag .= '</tr>';
		} else {
			$this->headerTag = '';
		}
	}

	// Render table row
	private function renderRow($row, $rowKey) {
		if (!isset($row)) return;

		if (is_string($row) && $row === '<header>') {
			return $this->headerTag . _NL;
		}

		$ret = '';
		$rowAttribute = '';
		$colNo = 0;
		$rowConfig = [
			'id' => NULL,
			'class' => NULL,
			'attr' => NULL,
		];

		if ($this->repeatHeader && $this->rowNo && $this->rowNo % $this->repeatHeader == 0) {
			$ret .= $this->headerTag . _NL;
		}

		if (is_array($row) && array_key_exists('config', $row)) {
			$rowConfig = $row['config'];
			if (is_string($rowConfig)) $rowConfig = (Array) \SG\json_decode($rowConfig);
			unset($row['config']);
		}

		if (is_string($row) && strtolower(substr($row, 0, 3)) === '<tr') {
			return $ret . $row . _NL;
		}

		++$this->rowNo;

		if (is_string($rowKey)) $rowConfig['id'] = $rowKey;

		$rowConfig['class'] = 'row -row-' . $this->rowNo
			. (is_string($rowKey) ? ' ' . $rowKey : '')
			. (isset($rowConfig['class']) ? ' ' . $rowConfig['class'] : '');

		if (array_key_exists('attr', $rowConfig)) {
			$rowAttribute = $rowConfig['attr'] . ' ';
			unset($rowConfig['attr']);
		} else {
			$rowAttribute = '';
		}

		foreach ($rowConfig as $configKey => $configValue) {
			$rowAttribute .= $configKey . '="' . $configValue . '" ';
		}
		
		$rowAttribute = trim($rowAttribute);

		$ret .= '<tr ' . $rowAttribute . '>' . _NL;

		foreach ($row as $colKey => $colData) {
			++$colNo;
			if (is_array($colData) || is_object($colData)) {
				// Column data is an Array
				$colValue = '';
				$alreadyClass = false;
				$ret .= '	<td';
				if (is_object($colData) && method_exists($colData, 'build')) {
					$colValue = $colData->build() . '</td>';
				} else if (is_object($colData)) {
					$colValue = '*Object*';
				} else {
					foreach ($colData as $colk => $colv) {
						if (empty($colk)) {
							$colValue = $colv;
							// unset($col[$colk]);
						} else {
							if ($colk == 'class') $alreadyClass = true;
							$ret .= ' ' . $colk . '="' . $colv . '"';
						}
					}
				}
				if (!$alreadyClass) {
					$ret .= $this->headerKey[$colNo] ? ' class="col -' . $this->headerKey[$colNo] . ' col-' . $this->headerKey[$colNo] . '"' : '';
				}
				$ret .= '>' . $colValue . '</td>' . _NL;
			} else if (strtolower(substr($colData, 0, 3)) === '<th') {
				// Column data is TH
				$ret .= $colData._NL;
			} else if (strtolower(substr($colData, 0, 3)) === '<td') {
				// Column data is TD
				$ret .= $colData . _NL;
			} else {
				// Column data is String
				list($colFirstKey) = explode(' ', trim($this->headerKey[$colNo]));
				$ret .= '	<td';
				if (is_string($colKey) && substr($colKey, 0, 1) != '-')
					$ret .= ' class="' . $colKey . '"';
				else
					$ret .= ($this->headerKey[$colNo] ? ' class="col -' . $this->headerKey[$colNo] . ' col-' . $colFirstKey . (is_string($colKey) ? ' ' . $colKey : '') . '"' : '');
				$ret .= '>' . $colData . '</td>' . _NL;
			}
		}
		$ret .= '</tr>' . _NL;

		return $ret;
	}

	// Render table body
	private function renderBody() {
		if (!isset($this->children)) return;

		$ret = '<tbody>' . _NL;
		foreach ($this->children as $rowKey => $row) {
			if (is_object($row) && $row->widgetName === 'Children') {
				foreach ($row->children as $childrenKey => $child) {
					$ret .= $this->renderRow($child, $childrenKey, $this->headerKey);
				}
			} else {
				$ret .= $this->renderRow($row, $rowKey);
			}
		}
		$ret .= '</tbody>' . _NL;

		return $ret;
	}

	// Render table footer
	private function renderFooter() {
		$ret = '';

		if (isset($this->tfoot)) {
			$ret .= '<tfoot>'._NL;
			if (is_string($this->tfoot)) {
				$ret .= $this->tfoot._NL;
			} else if (is_array($this->tfoot)) {
				$colNo = 0;
				foreach ($this->tfoot as $tfoot) {
					if (is_string($tfoot)) {
						$ret .= $tfoot._NL;
					} else if (is_array($tfoot)) {
						$tfootconfig = $tfoot['config'];
						unset($tfoot['config']);
						$ret .= '<tr'.($tfootconfig['style'] ? ' style="'.$tfootconfig['style'].'"' : '').'>'._NL;

						$colNo = 0;
						foreach ($tfoot as $tfoot_text) {
							++$colNo;
							$ret .= '	'.(strtolower(substr($tfoot_text,0,3)) == '<td' ? $tfoot_text : '<td class="col -'.$this->headerKey[$colNo].' col-'.$this->headerKey[$colNo].'">'.$tfoot_text.'</td>');
							$ret .= _NL;
						}
						$ret .= '</tr>'._NL;
					}
				}
			}
			$ret .= '</tfoot>'._NL;
		}

		return $ret;
	}
} // End of class Table
?>