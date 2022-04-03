<?php
/********************************************
* Widget :: Table
* Table widget for create table
*
* Created 2020-10-01
* Modify  2022-04-03
*
* @usage new Table([key => value])
********************************************/

class Table extends Widget {
	var $config = [];
	var $class = 'widget-table item';
	var $showHeader = true;
	var $repeatHeader = 0;
	var $rows = [];

	function __construct($class = NULL) {
		if (is_array($class)) {
			if ($class['class']) $class['class'] = 'widget-table item '.$class['class'];
			parent::__construct($class);
		} else {
			if ($class) $this->class = $class;
		}
	}

	function addClass($class) {
		$this->class .= ' '.$class;
	}

	function addConfig($key,$value) {
		$this->config[$key] = $value;
	}

	function toString() {
		$config = $this->config;
		$colgroups = [];
		$headerkey = [];
		$captionStr = SG\getFirst($this->config->caption,$this->caption);

		// Create table tag
		$ret = '<table';
		if (isset($this->id))
			$ret .= ' id="'.$this->id.'"';
		if (isset($this->class))
			$ret .= ' class="'.$this->class.'"';
		if ($this->style) $ret .= 'style="'.$this->style.'"';
		if (isset($this->attr)) {
			if (is_string($this->attr))
				$ret .= ' '.$this->attr;
			else if (is_array($this->attr))
				foreach ($this->attr as $k => $v)
					$ret .= ' '.$k.'="'.$v.'"';
		}
		$ret .= '>'._NL;

		if (isset($captionStr))
			$ret .= '<caption>'.$captionStr.'</caption>'._NL;

		// Create table column group
		if (isset($this->colgroup) && is_array($this->colgroup)) {
			$colgroups = $this->colgroup;
			$ret .= '<colgroup>'._NL;
			$colNo = 1;
			foreach ($colgroups as $key => $value) {
				if (is_array($value) || (is_string($value) && substr($value, 0, 1) == '{')) {
					$value = sg_implode_attr(sg_json_decode($value));
				}
				$ret .= '<col '.$value.'/>'._NL;
				$headerkey[$colNo] = is_numeric($key) ? '-col-'.$colNo : $key;
				$colNo++;
			}
			$ret .= '</colgroup>'._NL;
		}

		// Create table header
		if (!isset($this->thead) && isset($this->header))
			$this->thead = $this->header;
		if (isset($this->thead) && is_string($this->thead)) {
			$headerTag = $this->thead;
			$headerTag = str_replace(
				['<thead>', '</thead>', '<tr'],
				['','','<tr class="header"'],
				$headerTag
			);
		} else if (isset($this->thead) && is_array($this->thead)) {
			$colNo = 1;
			$headerTag = '<tr class="header">';
			foreach ($this->thead as $thkey => $th) {
				if (is_null($th)) continue;
				$thkey = is_numeric($thkey) ? $colNo : $thkey;
				if (!$colgroups) {
					$headerkey[$colNo] = is_numeric($thkey) ? 'col-'.$thkey : $thkey;
				}
				if (substr($th,0,4) == '<th ') {
					$headerTag .= $th;
				} else {
					$headerTag .= '<th class="header-'.$thkey.'">'.$th.'</th>';
				}
				++$colNo;
			}
			$headerTag .= '</tr>';
		} else {
			$headerTag = '';
		}
		$ret .= $this->showHeader ? '<thead>'.$headerTag.'</thead>'._NL : '';



		// Create table rows

		if ($this->rows) {$this->children = $this->rows;}
	 	unset($this->rows);

		if (isset($this->children)) {
			$rowNo = 0;

			$ret .= '<tbody>'._NL;
			foreach ($this->children as $row) {
				if (is_string($row) && $row == '<header>') {
					$ret .= $headerTag._NL;
					continue;
				}

				if ($this->repeatHeader && $rowNo && $rowNo % $this->repeatHeader == 0)
					$ret .= $headerTag._NL;
				$rowConfig = [];
				if (is_array($row) && array_key_exists('config', $row)) {
					$rowConfig = $row['config'];
					if (is_string($rowConfig)) $rowConfig = (Array) SG\json_decode($rowConfig);
					unset($row['config']);
				}
				if (is_string($row) && strtolower(substr($row,0,3))=='<tr') {
					$ret .= $row._NL;
					continue;
				}

				++$rowNo;

				$rowConfig['class'] = 'row -row-'.$rowNo.(isset($rowConfig['class']) ? ' '.$rowConfig['class'] : '');
				if (array_key_exists('attr', $rowConfig)) {
					$attr = $rowConfig['attr'].' ';
					unset($rowConfig['attr']);
				} else {
					$attr = '';
				}

				foreach ($rowConfig as $config_key => $config_value) {
					$attr .= $config_key.'="'.$config_value.'" ';
				}
				$attr = trim($attr);
				$ret .= '<tr '.$attr.'>'._NL;

				$colNo = 0;

				foreach ($row as $colKey => $colData) {
					++$colNo;
					if (is_object($colData)) $colData = 'Object';
					else if (is_array($colData)) {
						// Column data is an Array
						$already_class = false;
						$ret .= '	<td';
						foreach ($colData as $colk => $colv) {
							$colvalue = '';
							if (empty($colk)) {
								$colvalue = $colv;
								unset($col[$colk]);
							} else {
								if ($colk == 'class') $already_class = true;
								$ret .= ' '.$colk.'="'.$colv.'"';
							}
						}
						if (!$already_class)
							$ret .= $headerkey[$colNo] ? ' class="col '.$headerkey[$colNo].' col-'.$headerkey[$colNo].'"' : '';
						$ret.='>'.$colvalue.'</td>'._NL;
					} else if (strtolower(substr($colData, 0, 3)) == '<th') {
						// Column data is TH
						$ret .= $colData._NL;
					} else if (strtolower(substr($colData, 0, 3)) == '<td') {
						// Column data is TD
						$ret .= $colData._NL;
					} else {
						// Column data is String
						list($colFirstKey) = explode(' ', trim($headerkey[$colNo]));
						$ret .= '	<td';
						if (is_string($colKey) && substr($colKey, 0, 1) != '-')
							$ret .= ' class="'.$colKey.'"';
						else
							$ret .= ($headerkey[$colNo] ? ' class="col -'.$headerkey[$colNo].' col-'.$colFirstKey.(is_string($colKey)?' '.$colKey:'').'"' : '');
						$ret .= '>'.$colData.'</td>'._NL;
					}
				}
				$ret .= '</tr>'._NL;
			}
			$ret .= '</tbody>'._NL;
		}

		// Create table footer
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
							$ret .= '	'.(strtolower(substr($tfoot_text,0,3)) == '<td' ? $tfoot_text : '<td class="col -'.$headerkey[$colNo].' col-'.$headerkey[$colNo].'">'.$tfoot_text.'</td>');
							$ret .= _NL;
						}
						$ret .= '</tr>'._NL;
					}
				}
			}
			$ret .= '</tfoot>'._NL;
		}
		$ret .= '</table>'._NL;
		return $ret;
	}

	// @override
	function build() {return $this->toString();}

} // End of class Table
?>