<?php
/**
 * Product listing
 *
 * @param Mixed vai1...varn
 * @return String
 */
function ibuy_product_name($self) {
	$self->para=$para=para(func_get_args(),'type=ibuy','field=detail,photo','list-style=table','items='.$self->items);
	
	$stmt='SELECT t.`tpid`, t.`title`
				, p.`listprice`, p.`retailprice`, p.`resalerprice`, `balance`
				FROM %topic% t
					LEFT JOIN %ibuy_product% p USING(`tpid`)
				WHERE t.`type`="ibuy" AND p.`outofsale`="N"
				ORDER BY `title` ASC';
	$topics=mydb::select($stmt);

	$self->theme->class='paper-content-ibuy';

	if (!$para->option->no_page_top) $ret .= $topics->page->show._NL;
	if (user_access('ibuy franchise price')) $usedprice='retailprice';
	elseif (user_access('ibuy resaler price')) $usedprice='resalerprice';
	else $usedprice='listprice';


	$tables = new Table();
	$tables->header=array('รหัสสินค้า','ชื่อสินค้า','money price-retail'=>'ราคาขายหน้าร้าน<br />(listprice)');
	if (user_access('ibuy resaler price,ibuy franchise price')) $tables->header['money price-resaler']='ราคาขายสมาชิก<br />(resalerprice)';
	if (user_access('ibuy franchise price')) $tables->header['money price-franchise']='ราคาเฟรนส์ไชน์<br />(retailprice)';
	foreach ($topics->items as $rs) {
		$rows=array($rs->tpid,'<a href="'.url('ibuy/'.$rs->tpid).'">'.$rs->title.'</a>',$rs->listprice);
	if (user_access('ibuy resaler price,ibuy franchise price')) $rows[]=$rs->resalerprice;
	if (user_access('ibuy franchise price')) $rows[]=$rs->retailprice;
		$tables->rows[]=$rows;
	}

	$ret .= $tables->build();
	
	if (!$para->option->no_page_bottom) $ret .= $topics->page->show._NL;

	return $ret;
}
?>