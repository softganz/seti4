<?php
function ibuy_app_hatyaigogreen($self) {
	$para=$para=para(func_get_args(),'field=detail,photo','type=ibuy','items='.SG\getFirst(cfg('ibuy.items'),$self->items));
	$types=model::get_topic_type('ibuy');
	$q=SG\getFirst(post('q'),$_REQUEST['q']);
	$show=post('show');
	if ($show=='hot') $orderby='`view` DESC';
	else if ($show=='new') $orderby='t.`tpid` DESC';
	else if ($show=='good') {$orderby='CONVERT(t.`title` USING tis620) ASC';$para->items=10000;}
	else if ($q) $orderby='CONVERT(t.`title` USING tis620) ASC';
	else $orderby='t.`tpid` DESC';

	R::View('ibuy.toolbar',$self,'เครือข่ายผู้ผลิต','app.hatyaigogreen');

	$detail='';


	$where=array();
	$where=sg::add_condition($where,'t.`type`="ibuy" AND `outofsale` IN ("N","O")');
	$where=sg::add_condition($where,'(p.`listprice`>0 OR p.`retailprice`>0 OR p.`resalerprice`>0)');
	if (i()->am=='' && cfg('ibuy.showfor.public')=='PUBLIC') {
		$where=sg::add_condition($where,'p.`showfor`="PUBLIC"');
	}
	if ($show=='good') $where=sg::add_condition($where,'t.`sticky`=1');
	if ($show=='new' || empty($q)) $where=sg::add_condition($where,'p.`isnew`=1');
	if ($q) {
		$q=preg_replace('/\s+/', ' ', $q);
		if (preg_match('/^code:(\w.*)/',$q,$out)) {
			$where=sg::add_condition($where,'t.`tpid`=:q','q',$out[1]);
		} else {
			$searchList=explode('+',$q);
			$qLists=array();
			foreach ($searchList as $key=>$str) {
				$str=trim($str);
				if ($str=='') continue;
				$qLists[]='(t.title RLIKE :q'.$key.' OR p.`forbrand` RLIKE :q'.$key.')';

				//$str=mysqli_real_escape_string($str);
				$str=preg_replace('/([.*?+\[\]{}^$|(\)])/','\\\\\1',$str);
				$str=preg_replace('/(\\\[.*?+\[\]{}^$|(\)\\\])/','\\\\\1',$str);

				// this comment for correct sublimetext syntax highlight
				// $str=preg_replace('/(\\[.*?+\[\]{}^$|(\)\\])/','\\\\\1',$str);

				$where=sg::add_condition($where,'','q'.$key,str_replace(' ', '|', $str));
			}
			if ($qLists) $where=sg::add_condition($where,'('.(is_numeric($q)?'t.`tpid`=:q OR ':'').implode(' AND ', $qLists).')','q',$q);
		}
	}
	//					'.($_REQUEST['q']?'':'AND isnew=1').'
	$whereCond=implode(' AND ',$where['cond']);
	$stmt="SELECT
					t.`tpid`, t.`title` ,
					p.* ,
					ph.`file` photo,
					t.`view`
					FROM %topic% t
						LEFT JOIN %ibuy_product% p ON p.`tpid`=t.`tpid`
						LEFT JOIN %topic_files% ph ON ph.`tpid`=t.`tpid` AND ph.`fid`
					WHERE
						$whereCond
					GROUP BY t.`tpid`
					ORDER BY ".$orderby."
					".($q?"":"LIMIT ".$para->items);
	$dbs=mydb::select($stmt,$where['value']);

	//$detail.=mydb()->_query;

	if ($show=='hot') $self->theme->title='สินค้ายอดนิยม';
	else if ($show=='new') $self->theme->title='สินค้ามาใหม่';
	else if ($show=='good') $self->theme->title='สินค้าแนะนำ';
	else if ($q) $self->theme->title='ผลการค้นหา "'.$q.'" จำนวน '.$dbs->_num_rows.' รายการ';

	if ($dbs->_empty) {
		$detail.='ไม่มีรายการสินค้า "'.$q.'"';
	} else if ($q) {
		$detail.=ibuy_model::product_listing($dbs);
	} else {
		$detail.=R::View('ibuy.app.product.listing',$dbs);
	}
	$ret.=$detail;
	return $ret;
}
?>