<?php
/**
 * Module Toolbar
 *
 * @param Object $self
 * @param String $title
 * @param String $nav
 * @param Object $info
 * @param Object $options
 * @return String
 */
function view_toolbar($self, $title = NULL, $nav = 'default', $info = NULL, $options = '{}') {
	$defaults = '{menu: ""}';
	$options = sg_json_decode($options);
	if (is_string($info) AND substr($info,0,1) == '{') $info = sg_json_decode($info);
	if (empty($nav)) $nav = 'default';

	$ret = '';

	$self->theme->title = isset($title) ? $title : 'Proejct Management';

	$subnav = R::View($nav.'.nav', $info, $options);
	if ($subnav) {
		$ret .= '<nav class="nav -submodule -'.(str_replace('.', '-', $nav)).'"><!-- nav of '.$nav.'.nav -->';
		$ret .= $subnav;
		$ret .= '</nav><!-- submodule -->';
	} else $ret = '<span style="display: none;"></span>';

	$self->theme->toolbar = $ret;
	return $ret;
}
?>