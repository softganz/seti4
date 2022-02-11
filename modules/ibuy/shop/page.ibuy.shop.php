<?php
/**
* Project owner
*
* @param Object $self
* @param Integer $shopId
* @return String
*/
function ibuy_shop($self,$shopId = NULL) {
	$self->theme->title='ร้านค้า';
	R::Page('ibuy.shop.toolbar',$self,$shopId);
	$listType='grid'; // grid or view . Example from http://www.simon.com/mall/great-mall/stores/list

	if ($shopId) {
		$ret.=__ibuy_shop_info($shopId);
	} else {
		$stmt='SELECT * FROM %org_officer% of LEFT JOIN %db_org% o USING(`orgid`) WHERE `membership`="ShopOwner" GROUP BY `orgid` ORDER BY CONVERT(`name` USING tis620) ASC';
		$dbs=mydb::select($stmt);
		$ret.='<ul class="listing ibuy-shop-list -'.$listType.'">';
		foreach ($dbs->items as $rs) {
			$ret.='<li><div class="-header"><h3 class="-title"><a href="'.url('ibuy/shop/'.$rs->orgid).'">'.$rs->name.'</a></h3><a href="'.url('ibuy/shop/'.$rs->orgid).'"><img class="-logo" src="'.ibuy_model::shop_logo($rs->orgid).'" width="96" height="96" /></a></div><p class="-info">'.$rs->address.'<br />โทร : '.$rs->phone.'<br />แฟกซ์ : '.$rs->fax.'</p></li>';
		}
		$ret.='</ul>';
		//$ret.=print_o($dbs,'$dbs');
	}

	return $ret;
}

function __ibuy_shop_info($shopId) {
	$rs=ibuy_model::get_shop($shopId);
	$ret.='<div class="ibuy-shop-banner">';
	$ret.='<h2>'.$rs->name.'</h2>';
	$ret.='<img class="-logo" src="'.ibuy_model::shop_logo($rs->orgid).'" width="96" height="96" />';
	$ret.='<p>ที่อยู่ : '.$rs->address.'<br />โทร : '.$rs->phone.'<br />แฟกซ์ : '.$rs->fax.'</p>';
	$ret.='</div>';
	$ret.=__ibuy_shop_product($shopId);
	//$ret.=print_o($rs,'$rs');
	return $ret;
}

function __ibuy_shop_product($shopId) {
	$para=$para=para(func_get_args(),'field=detail,photo','type=ibuy');
	if (isset($_GET['item'])) {
		$item=$_GET['item'];
		setcookie('item',$item,time()+60*60*24*365*10,'/');
	} else if ($_COOKIE['item']) {
		$item=$_COOKIE['item'];
	} else {
		$item=30;
	}

	$p=SG\getFirst($_REQUEST['p'],1);
	$types=model::get_topic_type('ibuy');

	/*
	$this->theme->header->text=$types->name;
	$this->theme->header->description=$types->description;
	$this->theme->class='content-paper';
	$this->theme->class.=' paper-content-'.$types->type;
	*/
	$detail='';


	$where=array();
	$where=sg::add_condition($where,'t.`type`=:type AND `outofsale` IN ("N","O")','type','ibuy');
	$where=sg::add_condition($where,'t.`orgid`=:orgid','orgid',$shopId);
	if (i()->am=='' && cfg('ibuy.showfor.public')=='PUBLIC') {
		$where=sg::add_condition($where,'p.`showfor`="PUBLIC"');
	}
	if ($_REQUEST['o']) $where=sg::add_condition($where,'p.`isnew`=1');
	$where=sg::add_condition($where,'(p.`listprice`>0 OR p.`retailprice`>0 OR p.`resalerprice`>0)');
	if ($_REQUEST['q']) $where=sg::add_condition($where,'t.title LIKE :q','q','%'.$_REQUEST['q'].'%');

	$stmt='SELECT t.`tpid`, t.`title` , p.*, ph.`file` photo,
						(SELECT brt.`name` FROM %tag_topic% br LEFT JOIN %tag% brt ON brt.`tid`=br.`tid` WHERE tpid=t.`tpid` AND br.`vid`='.cfg('ibuy.vocab.brand').' LIMIT 1) brandname
					FROM %topic% t
						LEFT JOIN %ibuy_product% p ON p.tpid=t.tpid
						LEFT JOIN %topic_files% ph ON ph.tpid=t.tpid AND ph.fid
						LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid
					WHERE t.`sticky`='._IBUY_STICKY.' AND '.implode(' AND ',$where['cond']).'
					GROUP BY t.`tpid`
					ORDER BY `title` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	if ($dbs->_num_rows) $hotproduct='<div class="ibuy-product-featured">'._NL.'<h3>สินค้าแนะนำ</h3>'._NL.ibuy_model::product_listing($dbs)._NL.'</div>';

	$order=SG\getFirst($_REQUEST['o'],'name');
	$orders=array('new'=>'t.tpid','name'=>'t.title','brand'=>'brandname','hot'=>'t.view');
	$stmt='SELECT t.`tpid`, t.`title` , p.*, ph.`file` photo,t.`view`,
						(SELECT brt.`name` FROM %tag_topic% br LEFT JOIN %tag% brt ON brt.`tid`=br.`tid` WHERE tpid=t.`tpid` AND br.`vid`='.cfg('ibuy.vocab.brand').' LIMIT 1) brandname
					FROM %topic% t
						LEFT JOIN %ibuy_product% p ON p.tpid=t.tpid
						LEFT JOIN %topic_files% ph ON ph.tpid=t.tpid AND ph.fid
						LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid
					WHERE '.implode(' AND ',$where['cond']).'
					GROUP BY t.`tpid`
					ORDER BY '.$orders[$order].' '.(in_array($order,array('new','hot'))?'DESC':'ASC').'
					'.($item!='All'?'LIMIT '.(($p-1)*$item).' , '.$item:'');
	$dbs=mydb::select($stmt,$where['value']);
//		$ret.='<p>'.$stmt.'</p>';
//		$ret.=mydb()->_query;
//		$ret.=print_o($where,'$where');
	//$ret.=print_o($dbs,'$dbs');

	if ($_REQUEST['q']) $this->theme->title='ผลการค้นหา';

	$page_nv='เรียงตาม <a href="'.url(q()).'">ชื่อสินค้า</a> | <a href="?o=new">สินค้ามาใหม่</a> | <a href="?o=hot">ยอดนิยม</a> | <a href="?o=brand">ยี่ห้อ</a> | Page : <a href="'.url(q(),$_REQUEST['o']?'o='.$order:NULL).'">First</a> | '.($p>1?'<a href="'.url(q(),($_REQUEST['o']?'o='.$order.'&':'').'p='.($p-1)).'">Previous</a> | ':'Previous | ').'( <strong>'.$p.'</strong> )'.($dbs->_num_rows==$item?' | <a href="'.url(q(),($_REQUEST['o']?'o='.$order.'&':'').'p='.($p+1)).'">Next</a>':'').' | <form style="display:inline-block;">'.($_REQUEST['o']?'<input type="hidden" name="o" value="'.$order.'">':'').'<label>Show </label><select name="item" onchange="this.form.submit()">';
	foreach (array(10,15,30,60,'All') as $v) $page_nv.='<option value="'.$v.'"'.($item==$v?' selected="selected""':'').'>'.$v.'</option>';
	$page_nv.='</select> per page</form>'._NL;

	if ($dbs->_empty) {
		$detail.='ไม่มีรายการสินค้า';
	} else if ($_REQUEST['q']) {
		$detail.=ibuy_model::product_listing($dbs,cfg('ibuy.search.style'),$page_nv);
	} else {
		$detail.=$hotproduct;
		$detail.=ibuy_model::product_listing($dbs,NULL,$page_nv);
	}

	$ret.='<div class="ibuy-shop-product">'.$detail.'</div>'._NL;
	return $ret;
}
?>