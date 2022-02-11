<?php
function icar_report_unpaiddown($self) {
	$shop=icar_model::get_my_shop();
	$self->theme->title='รายงานเงินดาวน์ค้างชำระ '.$shop->shopname;

	R::View('icar.toolbar', $self, $shop->shopname);

	$fromdate=sg_date($post->fromdate,'Y-m-d');
	$todate=sg_date($post->todate,'Y-m-d');
	$stmt='SELECT i.*, b.name brandname FROM %icar% i LEFT JOIN %tag% b ON b.tid=i.brand WHERE i.shopid=:shopid AND i.saledownprice-i.saledownpaid>0 ORDER BY `saledate` ASC';
	$dbs=mydb::select($stmt,':shopid',$shop->shopid);

	$tables = new Table();;
	$tables->caption='รายงานเงินดาวน์ค้างชำระ';
	$tables->thead = array(
		'no'=>'ลำดับ',
		'buydate -date'=>'วันที่ขาย',
		'เลขที่',
		'ยี่ห้อ',
		'รุ่น',
		'ทะเบียน',
		'sale-price -money'=>'ราคาขาย',
		'finance-price -money'=>'จัดไฟแนนส์',
		'down-price -money'=>'เงินดาวน์',
		'down-paid -money'=>'ชำระเงินดาวน์',
		'down-unpaid -money'=>'ค้างเงินดาวน์',
	);

	$no = 0;

	foreach ($dbs->items as $rs) {
		if (sg_date($rs->saledate,'Y-m')!=$cmonth) {
			$cmonth=sg_date($rs->saledate,'Y-m');
			$tables->rows[]=array('<th colspan="13">'.sg_date($rs->saledate,'ดดด ปปปป').'</th>');
			$no=0;
		}
		$tables->rows[] = array(
			++$no,
			sg_date($rs->saledate,'ว ดด ปปปป'),
			$rs->refno,
			$rs->brandname,
			$rs->model,
			$rs->plate,
			number_format($rs->saleprice,2),
			number_format($rs->financeprice,2),
			number_format($rs->saledownprice,2),
			number_format($rs->saledownpaid,2),
			number_format($rs->saledownprice-$rs->saledownpaid,2)
		);
	}
	$ret .= $tables->build();
	$ret .= '<p>รวมทั้งสิ้น '.$dbs->_num_rows.' คัน';
	return $ret;
}
?>