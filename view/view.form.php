<?php
$debug=false;

function view_form($formId=NULL,$form=NULL,$name=NULL,$returnType='text') {
	$formArray=array();
	if (empty($formId)) $formId=$form->config->id;
	$config=is_array($form->config)?(object)$form->config:$form->config;
	$isReadOnly=$config->readonly;

	if ($config->action) {
		$ret .= _NL.'<!-- sg-form -->'._NL;
		$formStr = '<form id="'.$formId.'" '
			. 'class="form '.($config->class ? $config->class.' ':'').($isReadOnly ? '-readonly' : '').'" '
			. 'method="'.$config->method.'" '
			. ($config->enctype?'enctype="multipart/form-data" ':'')
			. 'action="'.$config->action.'" '
			. (isset($config->checkvalid) && $config->checkvalid ? 'data-checkvalid="true" ' : '')
			. (isset($config->attr) ? ' '.(is_array($config->attr) ? sg_implode_attr($config->attr) : $config->attr) : '')
			. (isset($config->data) ? ' '.(is_array($config->data) ? sg_implode_attr($config->data) : $config->data) : '')
			. ' >';

		$ret .= $formStr._NL._NL;

		$formArray['form'] = $formStr;
	}


	if ($config->title) $ret .= '<h3 class="title">'.$config->title.'</h3>'._NL;
	if ($config->description) $ret .= '<div class="description">'.$config->description.'</div>';
	if ($config->container=='fieldset') {
		$ret .= '<fieldset id="'.SG\getFirst($config->id,$name).'" class="'.($config->class?$config->class.' ':'').($config->collapsible?'collapsible':'').'">'._NL;
		if ($config->label) $ret .= '<legend>'.$config->label.'</legend>'._NL;
		if ($config->collapsible) $ret.='<div id="'.$name.'" style="display: none; height: auto;" class="fieldset-wrapper">'._NL;
	}

	foreach ($form as $fildKey => $item) {
		if ($fildKey === 'config') continue;

		// form item in container
		/*
		if ((is_object($item) && isset($item->config)) || (is_array($item) && isset($item['config']))) {
			if (is_array($item)) $item = (Object) $item;
			if (is_array($item->config)) $item->config = (Object) $item->config;
			$item->config->variable = $config->variable;
			//$ret .= print_o($item,'$item').print_o($config,'$config');
			$ret .= view_form($formId, $item, $fildKey);
			continue;
		}
		*/

		if (is_string($item)) {
			$ret .= $item._NL._NL;
			continue;
		} else if (is_array($item)) {
			$item = (Object) $item;
		}

		if ($item->config) {
			$item->config = sg_json_decode($item->config);
		}

		if ($item->id) {
			$tag_id = $item->id;
		} else {
			$tag_id = $item->name?$item->name:($config->variable?$config->variable.'-':'').$fildKey;
			$tag_id = 'edit-'.preg_replace(array('/([\W]+$)+/','/([\W])+/'),array('','-'),$tag_id);
		}

		if ($item->name === false) {
			$name = '';
		} else {
			$name = $item->name ? $item->name : ($config->variable ? $config->variable.'['.$fildKey.']' : $fildKey);
		}

		if (isset($item->container) && is_object($item->container)) {
			$item->container = (array) $item->container;
		} else if (isset($item->container) && substr($item->container,0,1) == '{') {
			$item->container = (array) sg_json_decode($item->container);
		}
		if ($item->container['type']) {
			switch ($item->container['type']) {
				case 'fieldset' :
					$ret .= '<fieldset class="'.($item->container['collapsible']?'collapsible':'').'">'._NL;
					if ($item->container['legend']) $ret .= '<legend>'.$item->container['legend'].'</legend>'._NL;
					break;
			}
			if ($item->container['collapsible']) $ret.='<div id="'.$tag_id.'" style="display: none; height: auto;" class="fieldset-wrapper">'._NL;
		}

		$containerclass = $item->containerclass;
		if ($item->container) {
			if ($item->container['class']) {
				$containerclass .= trim(' '.$item->container['class']);
			}
		}

		$ret .= '<div id="form-item-'.$tag_id.'" '
				. 'class="form-'.(in_array($item->type,array('','')) ? $item->type : 'item'.' -'.$tag_id).($containerclass?' '.$containerclass:'')
				. ($item->type == 'hidden' ? ' -hidden' : '')
				. '"'
				. ' '.sg_implode_attr($item->container)
				. '>'._NL;
		if ($item->label)
			$ret .= '	<label for="'.$tag_id.'" class="'.($item->config->label == 'hide' ? '-hidden' : '').'">'.$item->label.($item->require?' <span class="form-required" title="This field is required.">*</span>':'').'</label>'._NL;

		$ret .= '<span class="form-group">';

		if ($item->pretext)
			$ret .= $item->pretext;

		if ($item->attr && is_array($item->attr)) {
			$item->attr = sg_implode_attr($item->attr);
		}

		switch ($item->type) {

			case 'textfield' :
				$ret .= '<div id="'.$tag_id.'">'.$item->value.'</div>';
				break;

			case 'hidden':
				$ret .= '<input type="hidden" name="'.$name.'" id="'.$tag_id.'" class="'.($item->require?'-require':'').'" value="'.$item->value.'" />'._NL._NL;
				break;

			case 'text' :
			case 'password' :
				$ret .= '<input '
					.($isReadOnly || $item->readonly ?'readonly="readonly" ' : '')
					.($item->autocomplete ? 'autocomplete="'.$item->autocomplete.'" ' : '')
					.($item->maxlength ? 'maxlength="'.$item->maxlength.'" ' : '')
					.($item->size ? 'size="'.$item->size.'" ' : '')
					. ($name ? ' name="'.$name.'" ' : ' ')
					. 'id="'.$tag_id.'" '
					.'class="form-'.$item->type
						.($item->class ? ' '.$item->class : '')
						.($item->require ? ' -require' : '')
						.($item->readonly ? ' -readonly' : '').'" '
					.'type="'.$item->type.'"'
					.($item->attr ? ' '.$item->attr : '')
					.($item->{"autocomplete-url"} ? ' autocomplete-url="'.$item->{"autocomplete-url"}.'"' : '')
					.' value="'.$item->value.'"'
					.(isset($item->placeholder) ? ' placeholder="'.$item->placeholder.'"' : '')
					.' />';
				break;

			case 'textarea' :
				$ret .= '	<div class="resizable-textarea">'
					.'<textarea '.($isReadOnly || $item->readonly?'readonly="readonly" ':'')
					.'cols="'.($item->cols?$item->cols:'60').'" '
					.'rows="'.($item->rows?$item->rows:10).'" '
					.'name="'.$name.'" '
					.'id="'.$tag_id.'" '
					.'class="form-textarea resizable processed'.($item->require?' -require':'').($item->readonly?' -readonly':'').($item->class?' '.$item->class:'').'" '
					.($item->attr?''.$item->attr:'').' '
					.(isset($item->placeholder)?' placeholder="'.$item->placeholder.'"':'').'>'
					.$item->value
					.'</textarea>'
					.'<div style="margin-right: -4px;" class="grippie"></div>'
					.'</div>';
				break;

			case 'radio' :
			case 'checkbox' :
				//				if (!is_array($item->value)) $item->value=(array)$item->value;
				if (!isset($item->display)) $item->display='block';
				$itemIndex = 0;
				foreach ($item->options as $option_key=>$option_value) {
					$itemIndex++;
					if ($item->container['tag']) {
						$ret .= '<'.$item->container['tag'].' class="'.$item->container['class'].'">';
					}
					if (is_array($option_value)) {
						$ret.='<span class="options-group"><span class="options-group-label">'.$option_key.'</span>'._NL;
						foreach ($option_value as $option_key=>$option_value) {
							//$ret .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>&nbsp;&nbsp;'.$option_value.'</option>'._NL;

							$ret .= '<label class="option -'.$item->display.'" >';
							$ret .= '<input'
								. ($isReadOnly || $item->readonly ? ' readonly="readonly"':'')
								. ' name="'.$name.($item->multiple ? '['.$option_key.']' : '').'"'
								. ' value="'.$option_key.'"';
							if (is_array($item->value)) {
								$option_value_key=array_keys($item->value);
								$ret .= in_array($option_key,array_intersect(array_keys($item->options),$item->value)) ? ' checked="checked"':'';

								//debugMsg('Array option_key='.$option_key.'<br />');
								//debugMsg($item->options, '$item->options');
								//debugMsg('option_value_key='.print_o($item->value,'$item->value').'<br />');
							} else {
								//echo 'Else option_key='.$option_key.'<br />';
								$ret .= $option_key == $item->value ? ' checked="checked"':'';
							}
							$ret .= ' class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require ? ' -require':'').'"'
								. ' type="'.$item->type.'"'
								. ($item->attr?' '.$item->attr:'')
								. ' /> ';
							$ret .= $option_value;

							$ret .= '</label>'._NL;
						}
						$ret.='</span>'._NL;
					} else {
						if ($item->separate) $name=$item->name?$item->name.$option_key:($config->variable?$config->variable.'['.$fildKey.$option_key.']':$fildKey);

						$ret .= '		<label class="option" style="display:'.$item->display.';">';
						if (substr($option_value, 0, 6) == '&nbsp;') {
						} else {
							$ret .= '<input id="'.$tag_id.'-'.$itemIndex.'"'
								. ($isReadOnly || $item->readonly ? ' readonly="readonly" disabled="disabled"' : '')
								. ' name="'.$name.($item->multiple ? '['.$option_key.']' : '').'"'
								. ' value="'.$option_key.'"';
							if (is_array($item->value)) {
								$option_value_key = array_keys($item->value);
								$ret .= in_array($option_key,array_intersect(array_keys($item->options),$item->value)) ? ' checked="checked"':'';
							} else {
								$ret .= $option_key==$item->value ? ' checked="checked"':'';
							}
							$ret .= ' class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require?' -require':'').'"'
								. ' type="'.$item->type.'"'
								. ($item->attr ? ' '.$item->attr:'')
								. ' /> ';
						}
						$ret .= $option_value;
						$ret.='</label>'._NL;
						if ($item->container['tag']) {
							$ret .= '</'.$item->container['tag'].'>';
						}
					}
				}
				break;

			case 'select' :
				if (!is_array($item->value)) $item->value=(array)$item->value;
				$selectStr = '	<select '
					. ($isReadOnly || $item->readonly ? 'readonly="readonly" ' : '').' '
					. ($item->multiple ? 'multiple="multiple" ' : '').($item->size?'size="'.$item->size.'" ':'')
					. ' name="'.$name.'" id="'.$tag_id.'" '
					. 'class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require ? ' -require' : '').($isReadOnly || $item->readonly ? ' -disabled' : '').'"'
					. ($item->attr ? ' '.$item->attr : '')
					. ($item->style ? 'style="'.$item->style.'"' : '')
					. ($item->attribute ? ' '.$item->attribute : '')
					. '>'._NL;
				if (preg_match('/^\</', $item->options)) {
					$selectStr .= $item->options;
				} else {
					if (is_string($item->options)) {
						$selectOptions = array();
						if (strpos($item->options,',')) {
							$selectOptions = explode(',',$item->options);
						} else if (preg_match('/^([0-9\-]+)\.\.([0-9\-]+)/',$item->options,$out)) {
							for ($i = $out[1]; $i<=$out[2]; $i++) {
								$selectOptions[$i] = $i;
							}
						}
						$itemOptions = array();
						foreach ($selectOptions as $v) {
						 	$itemOptions[$v] = $v;
						 }
						 $item->options = $itemOptions;
					}
					foreach ($item->options as $option_key => $option_value) {
						if (is_object($option_value)) $option_value = (Array) $option_value;
						if (is_array($option_value)) {
							$selectStr .= '	<optgroup label="'.$option_key.'">'._NL;
							foreach ($option_value as $option_key=>$option_value) {
								$selectStr .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>&nbsp;&nbsp;'.$option_value.'</option>'._NL;
							}
							$selectStr .= '	</optgroup>'._NL;
						} else {
							if (substr($option_key,0,3) === 'sep') {
								$selectStr .= '<option disabled="disabled">'.$option_value.'</option>';
							} else {
								$selectStr .= '	<option value="'.$option_key.'"'.(in_array($option_key,$item->value)?' selected="selected"':'').'>'.$option_value.'</option>'._NL;
							}
						}
					}
				}
				$selectStr .= '	</select>';
				$ret .= $selectStr;
				$formArray[$tag_id] = $selectStr;
				break;

			case 'file' :
				if ($item->count) {
					for ($i=1;$i<=$item->count;$i++) $ret .= '	<input '.($item->size?'size="'.$item->size.'" ':'').' name="'.$name.'['.$i.']" id="'.$tag_id.'-'.$i.'" class="form-'.$item->type.($item->require?' -require':'').'" type="'.$item->type.'" />';
				} else {
					$ret .= '	<input '.($item->size?'size="'.$item->size.'" ':'').' name="'.$name.'" id="'.$tag_id.'" class="form-'.$item->type.($item->class ? ' '.$item->class : '').($item->require?' -require':'').'" type="'.$item->type.'"'.($item->multiple?'multiple="multiple"':'').' />';
				}
				break;

			case 'button' :
				if (empty($item->items) && !empty($item->value)) {
					$ret .= '	<button type="submit" '.(empty($item->name)?'':'name="'.$name.'"').' class="btn -primary'.($item->class?' '.$item->class:'').'" value="'.htmlspecialchars(strip_tags($item->value)).'" '.($isReadOnly || $item->readonly?'disabled="disabled" ':'').'>'.$item->value.'</button> ';
				} else if (is_array($item->items) && !empty($item->items['value'])) {
					$ret .= '	<button'.(isset($item->items['type'])?' type="'.$item->items['type'].'"':'').' name="'.(isset($item->items['name'])?$item->items['name']:$name).'" class="btn'.($item->items['class']?' '.$item->items['class']:'').'" value="'.htmlspecialchars(strip_tags($item->items['value'])).'" '.($isReadOnly || $item->readonly?'disabled="disabled" ':'').'>'.$item->items['value'].'</button> ';
				} else {
					foreach ($item->items as $key => $button) {
						if (is_null($button)) {
							continue;
						} else if ($button['type'] == 'text') {
							$ret .= $button['value'];
						} else {
							$ret .= '	<button'
								. (isset($button['type'])?' type="'.$button['type'].'"':'')
								. ' name="'.SG\getFirst($button['name'],is_string($key) ? $key : $name).'" '
								. 'class="btn'.($button['class']?' '.$button['class']:'').'" '
								. 'value="'.SG\getFirst($button['btnvalue'],htmlspecialchars(strip_tags($button['value']))).'" '
								. ($isReadOnly || $item->readonly?'disabled="disabled" ':'').'>'
								. $button['value']
								. '</button> ';
						}
					}
				}
				break;

			case 'submit' :
				foreach ($item->items as $key=>$value) {
					if (substr($key,0,4)=='text') $ret .= $value;
					else $ret .= '	<input name="'.$key.'" '.($isReadOnly || $item->readonly?'readonly="readonly" ':'').'class="btn'.($key=='save'?' -primary':'').' -'.$key.($item->class?' '.$item->class:'').'"  type="'.$item->type.'" value="'.$value.'" />';
				}
				break;

			case 'date' :
				$months['BC']=sg_client_convert(array('มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'));
				$months['DC']=array('Jan','Feb','Apr','March','May','June','July','Aug','Sep','Oct','Nov','Dec');

				list($year_from,$year_no,$year_sort)=explode(',',$item->year->range);
				if (empty($year_from)) $year_from=date('Y');
				else if (in_array(substr($year_from,0,1),array('-','+'))) $year_from=date('Y')+$year_from;
				if (empty($year_no)) $year_no=5;
				$ret.='<select id="'.$tag_id.'-date" class="form-select" name="'.$name.'[date]" '.($isReadOnly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
				$ret.='<option value="">'.($item->year->type=='BC'?'วันที่':'Date').'</option>'._NL;
				for ($i=1;$i<=31;$i++) $ret.='<option value="'.sprintf('%02d',$i).'"'.($item->value->date==$i?' selected':'').'>'.sprintf('%02d',$i).'</option>'._NL;
				$ret.='</select>'._NL;
				$ret.='<select id="'.$tag_id.'-month" class="form-select" name="'.$name.'[month]" '.($isReadOnly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
				$ret.='<option value="">'.($item->year->type=='BC'?'เดือน':'Month').'</option>'._NL;
				for ($i=1;$i<=12;$i++) $ret.='<option value="'.sprintf('%02d',$i).'"'.($item->value->month==$i?' selected':'').'>'.sprintf('%02d',$i).'-'.($item->year->type=='BC'?$months['BC'][$i-1]:$months['DC'][$i-1]).'</option>'._NL;
				$ret.='</select>'._NL;
				$ret.='<select id="'.$tag_id.'-year" class="form-select" name="'.$name.'[year]" '.($isReadOnly || $item->readonly?'readonly="readonly" disabled="disabled" ':'').'>'._NL;
				$ret.='<option value="">'.($item->year->type=='BC'?'ปี พ.ศ.':'Year').'</option>'._NL;
				if ($year_sort=='DESC') {
					for ($i=$year_from;$i>$year_from-$year_no;$i--) $ret.='<option value="'.$i.'"'.($item->value->year==$i?' selected':'').'>'.($item->year->type=='BC'?$i+543:$i).'</option>'._NL;
				} else {
					for ($i=$year_from;$i<$year_from+$year_no;$i++) $ret.='<option value="'.$i.'"'.($item->value->year==$i?' selected':'').'>'.($item->year->type=='BC'?$i+543:$i).'</option>'._NL;
				}
				$ret.='</select>'._NL;
				break;

			case 'time' :
				unset($times);
				$start_time=SG\getFirst($item->start,0);
				$end_time=SG\getFirst($item->end,24);
				$step_time=SG\getFirst($item->step,15);
				for ($hr=$start_time;$hr<$end_time;$hr++) {
					for ($min=0;$min<60;$min+=$step_time) {
						$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
					}
				}
				$ret.='<select id="'.$tag_id.'" class="form-select'.($item->class?' '.$item->class:'').'" name="'.$name.'">'._NL;
				foreach ($times as $time) $ret.='<option value="'.$time.'"'.($time==$item->value?' selected="selected"':'').'>'.$time.'</option>';
				$ret.='</select>';
				break;

			case 'hour' :
				unset($times);
				$start_time=SG\getFirst($item->start,0);
				$end_time=SG\getFirst($item->end,24);
				$step_time=SG\getFirst($item->step,15);
				for ($hr=$start_time;$hr<$end_time;$hr++) {
					for ($min=0;$min<60;$min+=$step_time) {
						$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
					}
				}
				$ret.='<select id="'.$tag_id.'" class="form-select" name="'.$name.'[hour]">'._NL;
				for ($hr=$start_time;$hr<$end_time;$hr++) $ret.='<option value="'.sprintf('%02d',$hr).'"'.($hr==$item->value->hour?' selected="selected"':'').'>'.sprintf('%02d',$hr).'</option>';
				$ret.='</select> : ';
				$ret.='<select id="'.$tag_id.'" class="form-select" name="'.$name.'[min]">'._NL;
				for ($min=0;$min<60;$min++) $ret.='<option value="'.sprintf('%02d',$min).'"'.($min==$item->value->min?' selected="selected"':'').'>'.sprintf('%02d',$min).'</option>';
				$ret.='</select>';
				break;

			case 'colorpicker' :
				if (empty($item->color)) $item->color='#ffffff, #cccccc, #c0c0c0, #999999, #666666, #333333, #000000, #ffcccc, #ff6666, #ff0000, #cc0000, #990000, #660000, #330000, #ffcc99, #ff9966, #ff9900, #ff6600, #cc6600, #993300, #663300, #ffff99, #ffff66, #ffcc66, #ffcc33, #cc9933, #996633, #663333, #ffffcc, #ffff33, #ffff00, #ffcc00, #999900, #666600, #333300, #99ff99, #66ff99, #33ff33, #33cc00, #009900, #006600, #003300, #99ffff, #33ffff, #66cccc, #00cccc, #339999, #336666, #003333, #ccffff, #66ffff, #33ccff, #3366ff, #3333ff, #000099, #000066, #ccccff, #9999ff, #6666cc, #6633ff, #6600cc, #333399, #330099, #ffccff, #ff99ff, #cc66cc, #cc33cc, #993399, #663366, #330033';
				foreach (explode(',',$item->color) as $color) {
					$color=trim($color);
					$ret.='<span style="background:'.$color.'; display:inline-block; padding:4px; border-radius: 4px; width: 18px; height: 18px;"><input type="radio" name="'.$name.'" value="'.$color.'"'.($color==$item->value?' checked="checked"':'').' style="outline: none; box-shadow: none; margin: 0 0 0 2px; padding: 0; height: 16px; width: 16px;" /></span>'._NL;
				}
				break;
		}
		if ($item->posttext) $ret .= $item->posttext;

		$ret .= '</span><!-- form-group -->';

		if ($item->description) $ret .= _NL.'<div class="description">'.$item->description.'</div>';
		$ret .= _NL.'</div>';

		if ($item->container['type']) {
			if ($item->container['collapsible']) $ret.=_NL.'</div>';
			$ret .= _NL.'</'.$item->container['type'].'>';
		}
		$ret .= _NL._NL;
	}
	if (isset($config->posttext)) $ret.=$config->posttext;
	if ($config->collapsible) $ret.='</div>'._NL;
	if ($config->container=='fieldset') $ret .= '</fieldset>'._NL;

	if ($config->footer) $ret .= $config->footer;
	if ($config->action) $ret .= '</form>'._NL;
	return $returnType == 'text' ? $ret : $formArray;
}
?>