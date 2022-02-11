<?php
/**
* iBuy Home Page
* Created 2019-05-30
* Modify  2019-05-30
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_home($self) {
	$getShow = SG\getFirst(post('show'), cfg('ibuy.home.orderby'),'lastin');
	$getSearch = SG\getFirst(post('q'),$_REQUEST['q']);

	$ret = '';

	$ret .= '<div class="ibuy-home">';
	$ret .= '<div class="ibuy-product-side">';
	$ret .= R::Page('ibuy.showcat',NULL);
	$ret .= '</div>';

	$ret .= '<div class="ibuy-product-main">';

	if (!($getShow || $getSearch)) {
		$ret .= '<div class="widget ads -ibuy-home" id="ad-ibuy-home" data-loc="ibuy-home" data-items="10" data-debug="true"></div>';
	}

	$ui = new Ui();
	$ui->add('<a class="btn" href="'.url('ibuy',array('show'=>'new')).'">สินค้าแนะนำ</a>');
	$ui->add('<a class="btn" href="'.url('ibuy',array('show'=>'lastin')).'">สินค้ามาใหม่</a>');
	$ret .= '<nav class="nav -page -sg-text-right">'.$ui->build().'</nav>';

	$para = para(func_get_args(),'field=detail,photo','type=ibuy','items='.SG\getFirst(cfg('ibuy.items'),$self->items,100));
	$types=model::get_topic_type('ibuy');

	if ($getShow=='hot') $orderby='`view` DESC';
	else if ($getShow=='new') $orderby='t.`tpid` DESC';
	else if ($getShow=='good') {$orderby='CONVERT(t.`title` USING tis620) ASC';$para->items=10000;}
	else if ($getSearch) $orderby='CONVERT(t.`title` USING tis620) ASC';
	else $orderby='t.`tpid` DESC';

	$self->theme->header->text=$types->name;
	$self->theme->header->description=$types->description;
	$self->theme->class='content-paper';
	$self->theme->class.=' paper-content-'.$types->type;

	// Not show addition title on home page
	//$self->theme->title=$types->name;

	$detail='';

	//$ret .= '$getShow = '.$getShow;
	//content('type','ibuy');

	if (cfg('ibuy.showshoptoolbar')) R::Page('ibuy.shop.toolbar',$self,$shopId);

	mydb::where('t.`type` = "ibuy" AND `outofsale` IN ("N","O")');
	mydb::where('(p.`listprice` > 0 OR p.`retailprice` > 0 OR p.`resalerprice` > 0)');
	if (i()->am == '' && cfg('ibuy.showfor.public') == 'PUBLIC') {
		mydb::where('p.`showfor` = "PUBLIC"');
	}
	if ($getShow == 'good') mydb::where('t.`sticky` = 1');
	else if ($getShow == 'lastin') ;
	else if ($getShow == 'new' || empty($getSearch)) mydb::where('p.`isnew` = 1');

	if ($getSearch) {
		$q = preg_replace('/\s+/', ' ', $getSearch);
		if (preg_match('/^code:(\w.*)/',$q,$out)) {
			mydb::where('t.`tpid` = :q',':q',$out[1]);
		} else {
			$searchList = explode('+',$q);
			$qLists = array();
			foreach ($searchList as $key=>$str) {
				$str = trim($str);
				if ($str == '') continue;
				$qLists[] = '(t.`title` RLIKE :q'.$key.' OR p.`forbrand` RLIKE :q'.$key.')';

				//$str=mysqli_real_escape_string($str);
				$str = preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
				$str = preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

				// this comment for correct sublimetext syntax highlight
				// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

				mydb::where(NULL,':q'.$key, str_replace(' ', '|', $str));
			}
			if ($qLists) mydb::where('('.(is_numeric($q) ? 't.`tpid` = :q OR ' : '').implode(' AND ', $qLists).')', ':q', $q);
		}
	}

	$stmt = "SELECT
					t.`tpid`, t.`title` ,
					p.* ,
					ph.`file` photo,
					t.`view`
					FROM %topic% t
						LEFT JOIN %ibuy_product% p ON p.`tpid`=t.`tpid`
						LEFT JOIN %topic_files% ph ON ph.`tpid`=t.`tpid` AND ph.`fid`
					%WHERE%
					GROUP BY t.`tpid`
					ORDER BY ".$orderby."
					".($getSearch?"":"LIMIT ".$para->items);

	$dbs = mydb::select($stmt);

	//$ret.=mydb()->_query;

	if ($getShow=='hot') $title='สินค้ายอดนิยม';
	else if ($getShow=='new') $title='สินค้ามาใหม่';
	else if ($getShow=='good') $title='สินค้าแนะนำ';
	else if ($getSearch) $title='ผลการค้นหา "'.$getSearch.'" จำนวน '.$dbs->_num_rows.' รายการ';

	$ret .= '<header class="header -ibuy"><h2 class="title">'.$title.'</h2></header>';
	if ($dbs->_empty) {
		$ret.='ไม่มีรายการสินค้า "'.$getSearch.'"';
	} else if ($getSearch) {
		$ret.=ibuy_model::product_listing($dbs);
	} else {
		$ret.=ibuy_model::product_listing($dbs);
	}

	$ret .= '</div><!-- ibuy-product-main -->';
	$ret .= '</div><!-- ibuy-home -->';

	return $ret;
}
?>