<?php
/**
 * Listing product item by category
 *
 * @param Argument list in parameter format
 * @return String
 */
function ibuy_showcat($self,$catid=NULL) {
	$ret.='<h3>Product Category</h3>'._NL.R::View('ibuy.category',$catid)._NL;
	return $ret;
}
?>