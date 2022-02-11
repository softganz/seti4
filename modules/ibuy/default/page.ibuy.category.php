<?php
/**
 * Listing product item by category
 *
 * @param Argument list in parameter format
 * @return String
 */
function ibuy_category($self,$catid) {
	if (isset($_GET['item'])) {
		$item=$_GET['item'];
		setcookie('item',$item,time()+60*60*24*365*10,'/');
	} else if ($_COOKIE['item']) {
		$item=$_COOKIE['item'];
	} else {
		$item=30;
	}

	if (cfg('ibuy.showshoptoolbar')) R::Page('ibuy.shop.toolbar',$self,$shopId);

	$getPage = SG\getFirst(post('p'),1);
	$types=model::get_topic_type('ibuy');
	$category=model::get_taxonomy($catid,true);

	//		if (empty($category->parent)) $item=30;

	$self->theme->header->text=$types->name;
	$self->theme->header->description=$types->description;
	$self->theme->class='content-paper';
	$self->theme->class.=' paper-content-'.$types->type;
	$self->theme->title=$category_name=mydb::select('SELECT `name` FROM %tag% WHERE `tid`=:tid LIMIT 1',':tid',$catid)->name;
	$detail='';

	//content('type','ibuy');
	//$ret.=print_o($category,'$category');
	//		if (empty($category->parent)) $child_categorys=model::get_taxonomy_tree();

	$catlists[]=intval($catid);
	if ($category->child) $catlists=array_merge($catlists,array_keys($category->child));

	//		$ret.=print_o($catlists,'$catlists');
	mydb::where('t.`type` = :type AND `outofsale` IN ("N","O")',':type','ibuy');
	mydb::where('tp.`tid` IN (:category)',':category','SET:'.implode(',',$catlists));
	if (i()->am == '' && cfg('ibuy.showfor.public') == 'PUBLIC') {
		mydb::where('p.`showfor` = "PUBLIC"');
	}
	if ($_REQUEST['o']) mydb::where('p.`isnew` = 1');
	//		$where=sg::add_condition($where,'p.`available`=1');
	mydb::where('(p.`listprice` > 0 OR p.`retailprice` > 0 OR p.`resalerprice` > 0)');
	if ($_REQUEST['q']) mydb::where('t.title LIKE :q',':q','%'.$_REQUEST['q'].'%');


	$order = SG\getFirst($_REQUEST['o'],'name');
	$orders = array('new'=>'t.tpid','name'=>'t.title','brand'=>'brandname','hot'=>'t.view');

	mydb::value('$VID$', cfg('ibuy.vocab.brand'));
	mydb::value('$ORDER$', $orders[$order]);
	mydb::value('$SORT$', in_array($order,array('new','hot')) ? 'DESC' : 'ASC');
	mydb::value('$LIMIT$', $item != 'All' ? 'LIMIT '.(($getPage-1)*$item).' , '.$item : '');
	$resetValue = $getPage == 1 ? 'false' : 'true';


	$stmt = 'SELECT
						t.`tpid`
					, t.`title`
					, p.*
					, ph.`file` photo
					, t.`view`
					, (SELECT brt.`name` FROM %tag_topic% br LEFT JOIN %tag% brt ON brt.`tid`=br.`tid` WHERE tpid=t.`tpid` AND br.`vid` = $VID$ LIMIT 1) `brandname`
					FROM %topic% t
						LEFT JOIN %ibuy_product% p USING(`tpid`)
						LEFT JOIN %topic_files% ph ON ph.`tpid` = t.`tpid` AND ph.`fid`
						LEFT JOIN %tag_topic% tp ON tp.`tpid` = t.`tpid`
					%WHERE%
					GROUP BY t.`tpid`
					ORDER BY $ORDER$ $SORT$
					$LIMIT$;
					-- {reset: '.$resetValue.'}
					';
	$productDbs = mydb::select($stmt,$where['value']);
	//$ret .= mydb()->_query.'<br />';


	if ($getPage == 1) {
		mydb::where('t.`sticky` = '._IBUY_STICKY);

		$stmt = 'SELECT t.`tpid`, t.`title` , p.*, ph.`file` photo,
							(SELECT brt.`name` FROM %tag_topic% br LEFT JOIN %tag% brt ON brt.`tid`=br.`tid` WHERE tpid=t.`tpid` AND br.`vid` = $VID$ LIMIT 1) brandname
						FROM %topic% t
							LEFT JOIN %ibuy_product% p ON p.tpid=t.tpid
							LEFT JOIN %topic_files% ph ON ph.tpid=t.tpid AND ph.fid
							LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid
						%WHERE%
						GROUP BY t.`tpid`
						ORDER BY `title` ASC';

		$stickyDbs = mydb::select($stmt);
		//$ret .= mydb()->_query.'<br />';

		if ($stickyDbs->_num_rows) {
			$hotproduct = '<div class="ibuy-product-featured">'._NL
									. '<header class="header -ibuy"><h2>สินค้าแนะนำของหมวด '.$category_name.'</h2></header>'._NL
									. ibuy_model::product_listing($stickyDbs)._NL
									. '</div>';
		}
	}



	//		$ret.='<p>'.$stmt.'</p>';
	//		$ret.=mydb()->_query;
	//		$ret.=print_o($where,'$where');
	//		$ret.=print_o($productDbs,'$productDbs');

	if ($_REQUEST['q']) $self->theme->title='ผลการค้นหา';

	$page_nv = 'เรียงตาม '
			. '<a class="btn -link" href="'.url(q()).'">ชื่อสินค้า</a> '
			. '<a class="btn -link" href="?o=new">สินค้ามาใหม่</a> '
			. '<a class="btn -link" href="?o=hot">ยอดนิยม</a> '
			. '<a class="btn -link" href="?o=brand">ยี่ห้อ</a> '
			. 'Page : '
			. '<a class="btn -link" href="'.url(q(),$_REQUEST['o']?'o='.$order:NULL).'">First</a> '
			. ($getPage > 1 ? '<a class="btn -link" href="'.url(q(),($_REQUEST['o']?'o='.$order.'&':'').'p='.($getPage-1)).'">Previous</a> ' : 'Previous ')
			. '( <strong>'.$getPage.'</strong> )'
			. ($productDbs->_num_rows==$item?' <a class="btn -link" href="'.url(q(),($_REQUEST['o']?'o='.$order.'&':'').'p='.($getPage+1)).'">Next</a>':'').' '
			. '<form style="display:inline-block;">'.($_REQUEST['o']?'<input type="hidden" name="o" value="'.$order.'">':'').'<select class="form-select" name="item" onchange="this.form.submit()">';
	foreach (array(10,15,30,60,'All') as $v) {
		$page_nv.='<option value="'.$v.'"'.($item==$v?' selected="selected""':'').'>'.$v.' items per page</option>';
	}
	$page_nv .= '</select></form>'._NL;

	if ($productDbs->_empty) {
		$detail.='ไม่มีรายการสินค้า';
	} else if ($_REQUEST['q']) {
		$detail.=ibuy_model::product_listing($productDbs,cfg('ibuy.search.style'),$page_nv);
	} else {
		$detail.=$hotproduct;
		$detail.='<header class="header -ibuy"><h2>หมวดสินค้า : '.$category_name.'</h2></header>'._NL;
		$detail.=ibuy_model::product_listing($productDbs,NULL,$page_nv);
	}

	$ret.='<div class="ibuy-product-side">'._NL.'<h3>Product Category</h3>'._NL.R::View('ibuy.category',$catid)._NL.'</div>'._NL;
	$ret.='<div class="ibuy-product-main">'._NL.$detail._NL.'</div>'._NL;
	return $ret;
}
?>