<?php
/**
* GoGreen Spp Shop Goods
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function green_category($self, $catId = NULL, $action = NULL) {
	$ret = '';
	unset($self->theme->toolbar, $self->theme->title);

	switch ($action) {
		case 'create' :
			break;

		default :
			if ($catId) {
				$ret .= R::Page('green.category.home', NULL, $catId);
			} else {
				$ret .= R::Page('green.category.home');
			}
	}

	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>