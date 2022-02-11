<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param 
* @return String
*/

$debug = true;

function view_ibuy_render_card($rs, $options = '{}') {
	$defaults = '{debug: false, url: null, showPriceLabel: true, showSaleLabel: true}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$ret = '';

	$containerClass = '';

	if ($options->link) {
		$url = '<a '.sg_implode_attr($options->link).'>';
		//$ret .= htmlspecialchars($url);
	} else {
		$url = '<a href="'.SG\getFirst($options->url,url('ibuy/'.$rs->tpid)).'" title="'.htmlspecialchars($rs->title).'">';
	}

	//$ret .= print_o($options, '$options');
	if ($style == 'short') {
		$ret = $url.$rs->title.'</a><span>'.substr($rs->body,0,200).'</span>';
	} else {
		if ($rs->brandname!=$brandname) {
			$brandname=$rs->brandname;
			//$ret.='<li class="brand"><h3 class="'.$brandname.'">'.$brandname.'</h3></li>'._NL;
		}
		if ($rs->brandname) {
			$containerClass .= 'brand-'.$rs->brandname;
		}

		$ret .= '<h3>'.$url.$rs->title.'</a></h3>'._NL;

		if ($rs->photo) $photo = model::get_photo_property($rs->photo);
		$ret .= '<div class="photo-th'.($photo->_url ? '' : ' -no-photo').'">';
		if ($photo->_url) {
			$ret .= $url.'<img class="photo" src="'.$photo->_url.'" height="140" /></a>';
		}
		/*
		$ret .= '<div class="photo">'.$url;
		if ($rs->photo) {
			$photo = model::get_photo_property($rs->photo);
			$ret .= '<img src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
		}
		$ret .= '</a></div>';
		*/
		$ret .= '</div>'._NL;

		$ret .= '<div class="productcode">รหัสสินค้า : '.$rs->tpid.'</div>';
		// Create product price and sale label
		if ($options->showPriceLabel) $ret .= R::View('ibuy.price.label',$rs)._NL;
		if ($options->showSaleLabel) $ret .= R::View('ibuy.sale.label', $rs, NULL, true)._NL;

		$ret .= '<div class="summary"><p>'.$rs->title.'</p><p><a class="btn -link" href="'.url('ibuy/'.$rs->tpid).'">'.tr('Details').'</a></p></div>'._NL;
	}
	return $ret;
}
?>