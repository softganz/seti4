<?php
/**
* GoGreen App Goods Home
*
* @return String
*/

$debug = true;

function view_ibuy_green_app_goods_home() {
	$ret = '';

	mydb::where('t.`type`="ibuy" AND `outofsale` IN ("N","O")');
	mydb::value('$ORDER', 't.`tpid` DESC');

	$stmt = 'SELECT
					t.`tpid`, t.`title` ,
					p.* ,
					ph.`file` photo,
					t.`view`
					FROM %topic% t
						LEFT JOIN %ibuy_product% p ON p.`tpid`=t.`tpid`
						LEFT JOIN %topic_files% ph ON ph.`tpid`=t.`tpid` AND ph.`fid`
					%WHERE%
					GROUP BY t.`tpid`
					ORDER BY $ORDER
					LIMIT 50
					;';
	$dbs = mydb::select($stmt);

	$cardUi = new Ui(NULL, 'ui-card -product');

	foreach ($dbs->items as $rs) {
		$url = '<a class="sg-action" href="'.url('ibuy/green/app/goods/'.$rs->tpid.'/view').'" title="'.htmlspecialchars($rs->title).'" data-webview=true data-webview-title="'.$rs->title.'">';
		/*
		if ($rs->brandname!=$brandname) {
			$brandname=$rs->brandname;
			$ret.='<li class="brand"><h3 class="'.$brandname.'">'.$brandname.'</h3></li>'._NL;
		}
		*/
		$cardStr = '<div class="photo">'.$url;
		if ($rs->photo) {
			$photo = model::get_photo_property($rs->photo);
			$cardStr .= '<img class="" src="'.$photo->_url.'" alt="'.htmlspecialchars($rs->title).'" />';
		} else {
			$cardStr .= '<img class="nophoto" src="/library/img/none.gif" alt="" />'._NL;
		}
		$cardStr .= '</a></div>'._NL;
		$cardStr .= '<h3>'.$url.$rs->title.'</a></h3>'._NL;
		$cardStr .= '<div class="price">'
							. '<span class="price-retail">฿'.number_format($rs->retailprice,2).'</span>'
							. '<span class="price-list">'.($rs->listprice != $rs->retailprice ? '฿'.number_format($rs->listprice,2) : '').'</span>';
		//$cardStr .= '<div class="summary"><p>'.$rs->title.'</p><p><a href="'.url('ibuy/'.$rs->tpid).'">'.tr('Details').'</a></p></div>'._NL;
		// Create product price and sale label
		//$ret .= R::View('ibuy.price.label',$rs)._NL;
		//$ret .= R::View('ibuy.sale.label',$rs,NULL,true)._NL;

		$cardUi->add($cardStr);
	}

	$ret .= $cardUi->build();

	//$ret .= mydb()->_query;

	//$ret .= print_o($dbs, '$dbs');


	head('<style type="text/css">
	.toolbar.-main {display:none;}
	.module-org.-app .page.-content {padding-top: 0px; background-color: #eee;}
	.title {background-color:#ccc; margin:32px 0 0 0; padding:32px 0; text-align:center;}
	.nav.-app.-page {background-color: white; font-size: 0.9em;}
	.nav.-app.-page>.ui-action {padding:8px;display: flex; justify-content: space-between;}
	.nav.-app.-page .ui-item {text-align: center;}
	.nav.-app.-page .ui-item>a {padding:8px 12px; display: block; border:1px #eee solid; border-radius: 50%; background-color: #f5f5f5;}
	.nav.-app.-page .icon {display: block; margin:0 auto;}
	.price .price-retail {display: block; font-size: 1.2em; color: #f60;}
	.price .price-list {color: #ccc; font-size: 0.8em; text-decoration: line-through;}
	div.photo {margin:0;}
	div.photo img {border: none; margin:4px auto; display: block; padding: 0; width: 95%; height: auto;}
	.ui-card.-product {margin: 0 8px; padding: 0; list-style-type: none; display: flex; flex-wrap: wrap; justify-content: space-between;}
	.ui-card.-product>.ui-item {margin: 16px 0 0 0; width: 48%; background-color: #fff;}
	.ui-card.-product h3 {font-size: 1.1em; padding: 8px;}
	.ui-card.-product .price {padding:0 8px;}
	</style>');

	return $ret;
}
?>