<?php
/**
 * Report total customer buy
 *
 * @return String
 */
function ibuy_admin_report_customerbuy($self, $month = NULL) {
	$self->theme->title='รายงานยอดซื้อสินค้า';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');

	if (!user_access('access administrator pages')) return message('error','access denied');

	$action=post('act');
	$datefrom=sg_date(SG\getFirst(post('df'),date('Y-01-01')),'Y-m-d');
	$dateto=sg_date(SG\getFirst(post('dt'),date('Y-12-31')),'Y-m-d');
	$order=SG\getFirst($para->order,post('o'),'total');
	$sort=SG\getFirst($para->sort,post('s'),2);
	$itemPerPage=SG\getFirst(post('i'),100);

	$orders=array(
						'total'=>array('ยอดซื้อสินค้า','`totals`'),
						'bill'=>array('จำนวนรายการซื้อ','`bills`'),
						'name'=>array('ชื่อร้าน','CONVERT(`custname` USING tis620)'),
						);
	$yearList=mydb::select('SELECT DISTINCT FROM_UNIXTIME(`orderdate`,"%Y") `year` FROM %ibuy_order% ORDER BY `year` ASC');

	$navbar.='<header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('ibuy/admin/report/customerbuy').'">'._NL;
	$navbar.='<ul>'._NL;
	$navbar.='<li>เงื่อนไข ';
	$navbar.='<label></label>';
	$navbar.=' วันที่ <input type="text" name="df" value="'.sg_date($datefrom,'d/m/Y').'" class="sg-datepicker" size="10" />';
	$navbar.=' - <input type="text" name="dt" value="'.sg_date($dateto,'d/m/Y').'" class="sg-datepicker" size="10" />';
	$navbar.=' <input type="submit" class="button" value="แสดง" />'._NL;
	$navbar.='</li>'._NL;
	$navbar.='</ul>'._NL;
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key===$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;
	$self->theme->navbar=$navbar;

	$backUrl='#close';


	if ($action=='order' && strlen($month)==10) $backUrl=url('ibuy/admin/report/totalsale',array('gr'=>'date','mo'=>sg_date($month,'Y-m')));
	else if ($groupBy=='date' && strlen($month)==10) $backUrl=url('ibuy/admin/report/totalsale',array('gr'=>'date','mo'=>sg_date($month,'Y-m')));

	if ($action=='order' && $month) {
		$stmt='SELECT o.*, f.custname
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
					WHERE o.status>=0 && FROM_UNIXTIME(o.orderdate,"'.(strlen($month)==7?'%Y-%m':'%Y-%m-%d').'")=:month ORDER BY o.oid ASC';
		$dbs=mydb::select($stmt,':month',$month);
		user_menu('back','Back to report',url('ibuy/report/totalsale/bymonth'));


		$tables = new Table();
		$tables->header=array('ใบสั่งซื้อ','date'=>'วันที่','ร้านค้า','money subtotal'=>'จำนวนเงิน','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');
		$subtotal=$discount=$total=0;
		foreach ($dbs->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array($rs->oid,
												date('d-m-Y',$rs->orderdate),
												$rs->custname,
												number_format($rs->subtotal,2),
												number_format($rs->discount,2),
												number_format($rs->total,2),
												'<a href="'.url('ibuy/report/order/'.$rs->oid).'">รายละเอียด</a>',
												'config'=>array('class'=>'status-'.$rs->status)
												);
			$subtotal+=$rs->subtotal;
			$discount+=$rs->discount;
			$total+=$rs->total;
		}
		$tables->rows[]=array('','','รวมทั้งสิ้น',number_format($subtotal,2),number_format($discount,2),number_format($total,2));

		$ret .= $tables->build();

		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}

	$where=array();
	$where=sg::add_condition($where,'o.`status`>=0');
	$where=sg::add_condition($where,'FROM_UNIXTIME(o.`orderdate`,"%Y-%m-%d") BETWEEN :datefrom AND :dateto','datefrom',$datefrom,'dateto',$dateto);
	if ($month) $where=sg::add_condition($where,'FROM_UNIXTIME(o.`orderdate`,"%Y-%m")=:month','month',$month);
	$groupByStr='FROM_UNIXTIME(o.orderdate,"%Y-%m")';
	if ($groupBy=='date') $groupByStr='FROM_UNIXTIME(o.orderdate,"%Y-%m-%d")';
	$stmt='SELECT `uid`, f.`custname`, COUNT(*) `bills`, SUM(o.`subtotal`) `subtotals` , SUM(o.`discount`) `discounts` , SUM(o.`total`) `totals`, SUM(o.`franchisorvalue`) `franchisorvalues` ,FROM_UNIXTIME(o.`orderdate`,"%Y") `years`
					FROM %ibuy_order% o
						LEFT JOIN %ibuy_customer% f USING (`uid`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY `uid`
					ORDER BY '.$orders[$order][1].($sort==1?'ASC':'DESC').'
					'.$limit;
	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=mydb()->_query;
	$amts=$totals=0;


	$tables = new Table();
	$tables->header=array('ชื่อร้าน','amt'=>'รายการซื้อ(ครั้ง)','money subtotal'=>'รวม(บาท)','money discount'=>'ส่วนลด(บาท)','money total'=>'รวมยอดขาย(บาท)','money point'=>'แต้ม','&nbsp;');
	foreach ($dbs->items as $rs) {
		$point=floor($rs->totals/100)*2;
		$tables->rows[]=array(
											'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box">'.$rs->custname.'</a>',
											$rs->bills,
											number_format($rs->subtotals,2),
											number_format($rs->discounts,2),
											number_format($rs->totals,2),
											number_format($point,0),
											//'<a class="sg-action" href="'.url('ibuy/admin/report/totalsale',array('act'=>'order','mo'=>$rs->label)).'" data-rel="box">ใบสั่งสินค้า</a>',
										);
		$subtotals+=$rs->subtotals;
		$discounts+=$rs->discounts;
		$totals+=$rs->totals;
	}
	$tables->rows[]=array('รวมทั้งสิ้น',number_format($subtotals,2),number_format($discounts,2),number_format($totals,2));

	$ret .= $tables->build();

	//$ret.=mydb()->_query;
	return $ret;
}
?>