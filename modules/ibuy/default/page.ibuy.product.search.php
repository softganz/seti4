<?php
/**
 * Product Search
 * 
 * @return String
 */
function ibuy_product_search($self) {
	$self->theme->title='Product search';
	
	$q=$_GET['q'];

	$ret.='<form method="get" action="'.url('ibuy').'" class="form-search" id="ibuy-product-search"><label>ค้นหาสินค้า</label><select class="form-select"><option value="">All Product Category</option></select><input type="text" name="q" value="'.htmlspecialchars($q).'" class="form-text" /><button type="submit" class="btn" value="Go">Go</button></form>'._NL;
	if ($q) {
		$self->theme->title.=' result for "<strong>'.$q.'</strong>" in product name';
		if ($pq) {
			$stmt='SELECT t.tpid , t.title , r.body, p.listprice ,p.retailprice , p.resalerprice , p.available, p.balance, t.created ,
								(select file FROM %topic_files% WHERE tpid=t.tpid AND cid=0 ORDER BY fid ASC LIMIT 1) photo
							FROM %ibuy_product% p 
								LEFT JOIN %topic% t ON t.tpid=p.tpid
								LEFT JOIN %topic_revisions% r ON t.revid=r.revid
							WHERE t.title LIKE :pq 
							ORDER BY t.title ASC';
			$dbs=mydb::select($stmt,':pq','%'.addslashes($q).'%');
			$ret.=ibuy_model::product_listing($dbs,cfg('ibuy.search.style'));
		}
	}
	return $ret;
}
?>