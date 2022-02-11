<?php
/**
* iBuy Controller
* Created 2018-12-24
* Modify  2019-05-30
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

/**
 * Home page of package ibuy
 *
 * @return String
 */
function ibuy($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	if (!module_install('ibuy')) return message('error', 'MODULE NOT INSTALL');
	if (!is_numeric($tpid)) {$action = $tpid; unset($tpid);} // Action as tpid and clear

	if (empty($action) && empty($tpid)) return R::Page('ibuy.home',$self);
	if (empty($action) && $tpid) return R::Page('ibuy.view',$self, $tpid);


	if ($tpid) {
		$productInfo = R::Model('ibuy.product.get', $tpid);
	}

	switch ($action) {

		default:
			/*
			// Bug on action/action/action
			$funcName = array();
			foreach (array_slice(func_get_args(),2) as $value) {
				if (is_numeric($value)) break;
				else if (is_string($value)) {
					$funcName[] = $value;
				}
			}
			$argIndex = count($funcName)+2; // Start argument
			*/

			if (empty($productInfo)) $productInfo = $tpid;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'ibuy.'.$action,
								$self,
								$productInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= R::Page('project.'.$action, $self, $tpid);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}

	return $ret;



	$para=para(func_get_args(),'field=detail,photo','type=ibuy','items='.SG\getFirst(cfg('ibuy.items'),$self->items,100));
	$types=model::get_topic_type('ibuy');
	$q=SG\getFirst(post('q'),$_REQUEST['q']);
	$show=post('show');
	if ($show=='hot') $orderby='`view` DESC';
	else if ($show=='new') $orderby='t.`tpid` DESC';
	else if ($show=='good') {$orderby='CONVERT(t.`title` USING tis620) ASC';$para->items=10000;}
	else if ($q) $orderby='CONVERT(t.`title` USING tis620) ASC';
	else $orderby='t.`tpid` DESC';

	$self->theme->header->text=$types->name;
	$self->theme->header->description=$types->description;
	$self->theme->class='content-paper';
	$self->theme->class.=' paper-content-'.$types->type;
	$self->theme->title=$types->name;
	$detail='';

	//content('type','ibuy');

	if (cfg('ibuy.showshoptoolbar')) R::Page('ibuy.shop.toolbar',$self,$shopId);

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
		$detail.=ibuy_model::product_listing($dbs);
	}
	if ($_SESSION['message']) {
		$detail.='<script type="text/javascript">$(document).ready(function(){notify("'.htmlspecialchars($_SESSION['message']).'",5000);});</script>';
		unset($_SESSION['message']);
	}
	if ($_REQUEST['nowidget']) {
		$ret=$detail;
	} else if (cfg('ibuy.widget.home')) {
		$widget=is_string(cfg('ibuy.widget.home'))?cfg('ibuy.widget.home'):implode(_NL,cfg('ibuy.widget.home'));
		$ret=str_replace('<div>Content</div>','<div class="ibuy-product-main'.($q?' query':'').'">'._NL.$detail._NL.'</div>'._NL,$widget);
		$showAd=true;
		if (cfg('ibuy.ad.showmemberonly')) {
			$showAd=i()->am!='';
		}
		if ($q || !$showAd) $ret=str_replace('<div class="widget ads" id="ad-A1" data-loc="A1" data-items="10"></div>', '', $ret);
	} else {
		$ret=$detail;
	}
	return $ret;
}
?>