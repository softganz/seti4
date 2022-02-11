<?php
/**
* GoGreen Spp Shop Goods
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function green_follow($self, $tpid = NULL, $action = NULL) {
	$ret = '';
	unset($self->theme->toolbar, $self->theme->title);

	switch ($action) {
		case 'create' :
			break;

		default :
			if ($tpid) {
				$ret .= R::Page('green.follow.view', $self, $tpid);
			} else {
				$ret .= R::Page('green.follow.home', $self);
			}
	}

	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>