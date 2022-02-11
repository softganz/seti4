<?php
function ibuy_admin_category($self) {
	$self->theme->title='หมวดสินค้า';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','category');
	$vid=cfg('ibuy.vocab.category');


	$ret.='<div class="widget " widget-request="admin/content/taxonomy/list/'.$vid.'" data-option-replace="yes"></div>';
	return $ret;
}
?>