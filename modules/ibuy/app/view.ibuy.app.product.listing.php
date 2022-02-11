<?php
/**
 * Listing product
 * 
 * @param $dbs
 * @return String
 */
function view_ibuy_app_product_listing($dbs,$style='full',$page_nv=NULL) {
	$brandname='';
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<ul class="ibuy-product-list ibuy-product-list-'.$style.'">'._NL;
	if ($page_nv) $ret.='<li class="page_nv">'.$page_nv.'</li>';
	foreach ($dbs->items as $rs) {
		$url='<a href="'.url('ibuy/'.$rs->tpid).'" title="'.htmlspecialchars($rs->title).'">';
		if ($style=='short') {
			$ret.='<li>'.$url.$rs->title.'</a><span>'.substr($rs->body,0,200).'</span></li>'._NL;
		} else {
			if ($rs->brandname!=$brandname) {
				$brandname=$rs->brandname;
				$ret.='<li class="brand"><h3 class="'.$brandname.'">'.$brandname.'</h3></li>'._NL;
			}
			$ret.='<li'.($rs->brandname?' class="brand-'.$rs->brandname.'"':'').'>'._NL;
			$ret.='<div class="photo">'.$url;
			if ($rs->photo) {
				$photo=model::get_photo_property($rs->photo);
				$ret.='<img src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
			} else $ret.='<img class="nophoto" src="/library/img/none.gif" alt="" />'._NL;
			$ret.='</a></div>'._NL;
			$ret.='<h3>'.$url.$rs->title.'</a></h3>'._NL;
			$ret.='<div class="productcode">รหัสสินค้า : '.$rs->tpid.'</div>';
			$ret.='<div class="summary"><p>'.$rs->title.'</p><p><a href="'.url('ibuy/'.$rs->tpid).'">'.tr('Details').'</a></p></div>'._NL;
			// Create product price and sale label
			$ret .= R::View('ibuy.price.label',$rs)._NL;
			$ret .= R::View('ibuy.sale.label',$rs,NULL,true)._NL;
			//$ret.=print_o($photo,'$photo');
			$ret.='</li>'._NL;
		}
	}
	if ($page_nv) $ret.='<li class="page_nv">'.$page_nv.'</li>';
	$ret.='</ul>'._NL;
	$ret.=ibuy_model::ajax_buy();
	return $ret;
}
?>