<?php
/**
 * Shop information
*
* @param Object $self
 * @param Integer $shopid
* @return String
*/

$debug = true;

function icar_shop($self,$shopid=NULL) {
	$para=para(func_get_args(),'list=thumbnail');
	$style=$para->list;
	$self->theme->title='ร้านค้า';
	$is_edit=user_access('administrator icars');


	R::View('icar.toolbar', $self, $shop->shopname);

	if (is_numeric($shopid)) {
		$stmt='SELECT * FROM %icarshop% WHERE `shopid`=:shopid LIMIT 1';
		$rs=mydb::select($stmt,':shopid',$shopid);
		$self->theme->title=$rs->shopname;
		if ($rs->motto) $ret.='<p class="motto">'.$rs->motto.'</p>'._NL;
		$stmt = 'SELECT t.`title`, i.*, b.`name` `brandname`, p.`file` `photo`
			FROM %icar% i
				LEFT JOIN %topic% t USING(tpid)
				LEFT JOIN %tag% b ON b.`tid` = i.`brand`
				LEFT JOIN %topic_files% p ON p.`tpid` = i.`tpid` AND p.`type` = "photo" AND p.`cover` = "Yes"
			WHERE i.`shopid`=:shopid AND i.`pricetosale` > 0
			ORDER BY i.`buydate` DESC';

		$dbs = mydb::select($stmt,':shopid',$shopid);

		foreach ($dbs->items as $rs) {
			$rs->year=$rs->year>0?$rs->year:'';
			$photo=model::get_photo_property($rs->photo);
			$img='<img class="thumbnail" src="'.($photo->_exists?$photo->_url:'/library/img/none.gif').'" />';
			$href='<a href="'.url('icar/'.$rs->tpid).'" title="ดูรายละเอียด">';
			$rows[]=$href.$img.'</a><h3>'.$href.$rs->title.'</a></h3><p>'.$rs->plate.'</p>';
		}
		$ret.='<ul class="icar-list-'.$style.'">'._NL.'<li>'.implode('</li>'._NL.'<li>',$rows).'</li>'._NL.'</ul>';
		return $ret;
	}




	$stmt = 'SELECT * FROM %icarshop%';
	$dbs=mydb::select($stmt);

	$cardUi = new Ui(NULL, 'ui-card -flex');
	foreach ($dbs->items as $rs) {
		//$photo=model::get_photo_property($rs->photo);

		$cardStr = '<a href="'.url('icar/shop/'.$rs->shopid).'"><span>';
		$cardStr .= '<h3>'.$rs->shopname.'</h3>';
		$cardStr .= '<img src="//img.softganz.com/img/map-1272165_640.png" width="100%" />';
		$cardStr .= '</span></a>';
		$cardStr .= '<nav class="nav -card"><a class="btn -link -fill" href="'.url('icar/shop/'.$rs->shopid).'"><i class="icon -pin"></i><span>View Mapping</span></a></nav>';
		$cardUi->add($cardStr);
	}
	$ret .= $cardUi->build();

	$ret .= '<style type="text/css">
	.ui-card h3 {font-size: 1.4em; position: absolute; top: 0;}
	.ui-card.-flex {display: flex; flex-wrap: wrap; justify-content: space-between;}
	.ui-card.-flex>.ui-item {width: 240px; height: 200px; overflow: hidden; margin: 16px; padding-top: 40px; padding-bottom: 0; position: relative;}
	.ui-card .nav.-card {margin:0; position: absolute; bottom: 0px; width: 100%}
	</style>';

	return $ret;
}
?>