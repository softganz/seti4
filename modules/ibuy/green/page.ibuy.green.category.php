<?php
/**
* GoGreen Spp Shop Goods
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function ibuy_green_category($self, $tpid = NULL, $action = NULL) {
	$ret = '';
	unset($self->theme->toolbar, $self->theme->title);

	switch ($action) {
		case 'create' :
			break;

		default :
			if ($tpid) {
				$ret .= R::View('ibuy.green.app.category.view', $tpid);
			} else {
				$ret .= R::View('ibuy.green.app.category.home');
			}
	}

	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>