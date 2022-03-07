<?php
/**
* Create Button Floating
* Created 2019-09-01
* Modify  2019-09-01
*
* @param 
* @return String
*/

$debug = true;

function view_button_floating($url = NULL, $options = '{}') {
	$defaults = '{debug:false, title:"", icon: "-addbig -white"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$links = array();

	if (is_array($url)) {
		$links = $url;
	} else if (substr($url, 0,1) == '<') {
		$links[] = $url;
	} else {
		$links[] = '<a class="btn -floating -circle48" href="'.$url.'" title="'.$options->title.'"><i class="icon '.$options->icon.'"></i></a>';
	}


	$ret .= '<div class="btn-floating -right-bottom">';
	foreach ($links as $item) {
		$ret .= $item;
	}
	$ret .= '</div>';
	return $ret;
}
?>