<?php
function icar_admin_sale($self) {
	$self->theme->title='Sale transaction';

	$shopid=post('shopid');
	R::View('icar.toolbar', $self);

	$shopList=mydb::select('SELECT * FROM %icarshop%')->items;
	foreach ($shopList as $rs) {
		$ret.='<a href="'.url('icar/admin/sale',array('shopid'=>$rs->shopid)).'">'.$rs->shopname.'</a> | ';
	}

	return $ret;
}
?>