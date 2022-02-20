<?php
/**
* Current user shop information
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_my($self) {
	$para=para(func_get_args(),'list=thumbnail');
	$style=$para->list;

	$shopId = post('shop');
	$paraSearch = post('q');
	$paraOrder = SG\getFirst(post('o'),'indate');
	$paraSold = strtoupper(post('sold'));

	if (!user_access('create icar content')) {
		if (i()->ok) {
			$ret.=message('error','access denied');
		} else {
			$ret .= R::View('signform');
		}
		return $ret;
	}

	$shopInfo = icar_model::get_my_shop();

	if (user_access('administrator icars')) {
		// Admin
	} else if ($shopInfo->_empty) {
		return message('error','คุณยังไม่มีหน้าร้าน');
	} else if ($shopInfo->shopstatus != 'ENABLE') {
		return message('error', 'ร้านค้าหมดอายุการใช้งาน กรุณาติดต่อผู้ดูแลระบบเพื่อต่ออายุการใช้งาน');
	}

	$title=user_access('administrator icars')?'Administrator@iCarSmile':$shopInfo->shopname;

	R::View('icar.toolbar', $self, $title);
	//$ret.=print_o($shopInfo,'$shopInfo');

	if (user_access('administrator icars')) {
		$ret .= '<form action='.url('icar/my').'><div class="form-item"><select class="form-select" name="shop" onChange="this.form.submit()" style="position: absolute;">';
		$emptySelect = array();
		if (!$shopId) $emptySelect[''] = (object)array('shopname'=>'*** SELECT ***');
		foreach ($emptySelect+mydb::select('SELECT * FROM %icarshop% ORDER BY `shopname` ASC')->items as $srs)
			$ret .= '<option value="'.$srs->shopid.'" '.($srs->shopid == $shopId ? 'selected="selected"' : '').'>'.$srs->shopname.'</option>';
		$ret .= '</select></div></form>';
		if ($shopId) mydb::where('i.`shopid` = :shopid',':shopid',$shopId);
	} else {
		mydb::where('(i.`shopid` = :shopid OR (p.pshopid = :shopid AND p.share > 0))',':shopid',$shopInfo->shopid);
	}
	if ($paraSearch) {
		mydb::where('(i.`plate` LIKE :q OR b.`name` LIKE :q OR i.`enginno` LIKE :q OR i.`bodyno` LIKE :q)',':q','%'.$paraSearch.'%');
	}
	if ($paraSold === 'YES') mydb::where('`sold` = "Yes"');
	else if ($paraSold === 'NO') mydb::where('`sold` IS NULL');
	$orderList = array('brandname'=>'`brandname`', 'plate'=>'`plate`', 'buydate'=>'`buydate`', 'indate'=>'`tpid`');
	$orderBy = SG\getFirst($orderList[$paraOrder], $orderList['indate']);

	mydb::value('$ORDER$', $orderBy);
	mydb::value('$SORT$', SG\getFirst($_REQUEST['s'],'DESC'));

	if ($shopInfo->motto) $ret.='<p class="motto">'.$shopInfo->motto.'</p>'._NL;


	$page=post('page');
	$itemPerPage=SG\getFirst(post('i'),100);
	if ($itemPerPage==-1) {
	} else {
		$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
		$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
	}
	mydb::value('$LIMIT$', $limit);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		  t.`title`
		, i.*
		, t.`created`
		, b.`name` brandname
		, f.`file` photo
		, p.`pshopid`
		FROM %icar% i
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %icarpartner% p USING (partner)
			LEFT JOIN %tag% b ON b.`tid`=i.`brand`
			LEFT JOIN %topic_files% f ON f.`tpid`=i.`tpid` AND f.`type`="photo" AND f.`cover`="Yes"
		%WHERE%
		ORDER BY $ORDER$ $SORT$
		$LIMIT$';
	$dbs=mydb::select($stmt);
	// $ret .= mydb()->_query;
	//$ret .= print_o($dbs, '$dbs');

	$totals = $dbs->_found_rows;


	$pagePara['shop'] = post('shop');
	$pagePara['q'] = $paraSearch;
	$pagePara['page'] = $page;
	$pagePara['i'] = $itemPerPage;
	$pagePara['o'] = $paraOrder;
	$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);
	$no=$pagenv?$pagenv->FirstItem():0;

	$ui=new ui('span');
	$ui->add('{tr:Sort by,เรียงตาม} : <a class="sg-action'.($paraOrder=='brandname' ? ' -active':'').'" href="'.url(q(),array('shop'=>$shopId,'o'=>'brandname','sold'=>$paraSold,'q'=>$paraSearch)).'" data-rel="#main">{tr:Brand,ยี่ห้อ}</a>');
	$ui->add('<a class="sg-action'.($paraOrder=='plate' ? ' -active':'').'" href="'.url(q(),array('shop'=>$shopId,'o'=>'plate','sold'=>$paraSold,'q'=>$paraSearch)).'" data-rel="#main">{tr:Plate,ทะเบียน}</a>');
	$ui->add('<a class="sg-action'.($paraOrder=='buydate' ? ' -active':'').'" href="'.url(q(),array('shop'=>$shopId,'o'=>'buydate','sold'=>$paraSold,'q'=>$paraSearch)).'" data-rel="#main">{tr:Buy Date,วันที่ซื้อ}</a>');
	$ui->add('<a class="sg-action'.($paraOrder=='indate' ? ' -active':'').'" href="'.url(q(),array('shop'=>$shopId,'o'=>'indate','sold'=>$paraSold,'q'=>$paraSearch)).'" data-rel="#main">{tr:Created,วันที่ป้อน}</a>');
	if ($paraSold === 'YES') {
		$ui->add('<a class="sg-action" href="'.url(q(), ['shop' => $shopId, 'q' => $paraSearch]).'" data-rel="#main">{tr:Sold,ขายแล้ว}</a>');
	} else if ($paraSold === 'NO') {
		$ui->add('<a class="sg-action" href="'.url(q(), ['shop' => $shopId, 'sold' => 'yes', 'q' => $paraSearch]).'" data-rel="#main">{tr:Not Sold,ยังไม่ขาย}</a>');
	} else {
		$ui->add('<a class="sg-action" href="'.url(q(), ['shop' => $shopId, 'sold' => 'no', 'q' => $paraSearch]).'" data-rel="#main">{tr:All cars,รถทั้งหมด}</a>');
	}
	// $ui->add($paraSold === 'YES' ? '<a class="sg-action" href="'.url(q(), ['shop' => $shopId,'q' => $paraSearch]).'" data-rel="#main">{tr:Not Sold,ยังไม่ขาย}</a>' : '<a class="sg-action" href="'.url(q(), ['shop' => $shopId, 'sold' => 'yes', 'q' => $paraSearch]).'" data-rel="#main">{tr:Sold,ขายแล้ว}</a>');
	$ui->add('{tr:Total,ทั้งหมด} <strong>'.number_format($totals).'</strong> {tr:cars,คัน}');
	$ret.='<div class="statusbar">'.$ui->build().'</div>';

	if ($dbs->_empty) {
		$ret .= message('notify','<p>ยังไม่มีรถในร้าน คลิก <a class="btn -primary" href="'.url('icar/buy').'">ซื้อรถเข้าร้าน</a> เพื่อซื้อรถเข้าร้าน</p>');
		return $ret;
	}

	$orows=$prows=array();
	foreach ($dbs->items as $rs) {
		$rs->year=$rs->year>0?$rs->year:'';
		$photo=model::get_photo_property($rs->photo);
		$img='<img class="thumbnail" src="'.($photo->_exists?$photo->_url:'/library/img/none.gif').'" />';
		$href='<a href="'.url('icar/'.$rs->tpid).'" title="ดูรายละเอียด">';
		$date='<div class="date" title="'.($paraOrder=="indate"?'วันที่ป้อนข้อมูล':'วันที่ซื้อรถ').'">'.($paraOrder=="indate"?sg_date($rs->created,'d/m/Y'):sg_date($rs->buydate,'d/m/Y')).'</div>';
		$detail='<div class="detail">ต้นทุน : '.number_format($rs->costprice,2).($rs->pshopid==$shopInfo->shopid?'<br />รถร่วมทุน':'').'</div>';
		if ($rs->shopid==$shopInfo->shopid) {
			$orows[]=$href.$img.'</a>'.$date.'<h3>'.$href.$rs->title.'</a></h3><p>'.$rs->plate.'</p>'.$detail;
		} else {
			$prows[]=$href.$img.'</a>'.$date.'<h3>'.$href.$rs->title.'</a></h3><p>'.$rs->plate.'</p>'.$detail;
		}
	}

	$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;

	if ($orows) $ret.='<h3>รถของร้าน</h3><ul class="icar-list-'.$style.'">'._NL.'<li>'.implode('</li>'._NL.'<li>',$orows).'</li>'._NL.'</ul>';
	$ret.='<br clear="all" />';
	if ($prows) $ret.='<h3>รถร่วมทุน</h3><ul class="icar-list-'.$style.'">'._NL.'<li>'.implode('</li>'._NL.'<li>',$prows).'</li>'._NL.'</ul>';


	$ret .= '<div class="pagenv">'.$pagenv->show.'</div><br /><br />'._NL;

	return $ret;
}
?>