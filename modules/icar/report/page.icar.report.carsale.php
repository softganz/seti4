<?php
function icar_report_carsale($self) {
	$shop=icar_model::get_my_shop();
	$self->theme->title='รายงานการขายรถ '.$shop->shopname;

	R::View('icar.toolbar', $self, $shop->shopname);

	$post=(object)post('report');


	$brandList=array(''=>'*** ทุกยี่ห้อ ***')+R::Model('icar.shop.getbrand');
	$partnetList=array(''=>'*** ทุกผู้ร่วมทุน ***')+R::Model('icar.shop.getpartner',$shop->shopid);
	
	$form = new Form('report', url(q()), 'icar-report');
	
	$form->addField(
		'fromdate',
		array(
			'type'=>'text',
			'label'=>'ตั้งแต่วันที่',
			'size'=>10,
			'class'=>'sg-datepicker',
			'value'=>SG\getFirst($post->fromdate?sg_date($post->fromdate,'d/m/Y'):NULL,date('01/m/Y')),
			'autocomplete'=>'off'
		)
	);

	$form->addField(
		'todate',
		array(
			'type'=>'text',
			'label'=>'ถึง',
			'size'=>10,
			'class'=>'sg-datepicker',
			'value'=>SG\getFirst($post->todate?sg_date($post->todate,'d/m/Y'):NULL,date('d/m/Y')),
			'autocomplete'=>'off'
		)
	);

	$form->addField(
		'brand',
		array(
			'type'=>'select',
			'options'=>$brandList,
			'value'=>$post->brand
		)
	);


	$form->addField(
		'partner',
		array(
			'type'=>'select',
			'options'=>$partnetList,
			'value'=>$post->partner
		)
	);

	$form->addField(
		'view',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
		)
	);

	$ret .= $form->build();
	//$ret.=print_o($shop,'$shop').print_o($post,'$post');
	
	if ($post->fromdate && $post->todate) {
		mydb::where('i.`shopid` = :shopid',':shopid',$shop->shopid);
		mydb::where('i.`saledate` BETWEEN :fromdate AND :todate',':fromdate',sg_date($post->fromdate,'Y-m-d'),':todate',sg_date($post->todate,'Y-m-d'));
		if ($post->brand) mydb::where('i.`brand` = :brand',':brand',$post->brand);
		if ($post->partner) mydb::where('i.`partner` = :partner',':partner',$post->partner);

		$stmt = 'SELECT i.*, b.`name` brandname, p.`name` `partnerName`
			FROM %icar% i
				LEFT JOIN %tag% b ON b.`tid`=i.`brand`
				LEFT JOIN %icarpartner% p USING(`partner`)
			%WHERE%
			ORDER BY `saledate` ASC;
			-- {sum:"costprice,saleprice,financeprice,saledownprice,saledownpaid"}';

		$dbs = mydb::select($stmt);
		//$ret.=mydb()->_query;

		$tables = new Table();
		$tables->caption='รายงานการขายรถ';
		$tables->thead=array('no'=>'','sale-date -date'=>'วันที่ขาย','เลขที่','ผู้ร่วมทุน','ยี่ห้อ/รุ่น','ทะเบียน','money cost'=>'ต้นทุน','money sale-price'=>'ราคาขาย','money finance-price'=>'จัดไฟแนนส์','money down-price'=>'เงินดาวน์','money down-paid'=>'ชำระเงินดาวน์','money down-unpaid'=>'ค้างเงินดาวน์','ขาย','');
		$no=0;
		foreach ($dbs->items as $rs) {
			if (sg_date($rs->saledate,'Y-m')!=$cmonth) {
				$cmonth=sg_date($rs->saledate,'Y-m');
				$tables->rows[]=array('<th colspan="15">'.sg_date($rs->saledate,'ดดด ปปปป').'</th>');
				$no=0;
			}

			$tables->rows[]=array(
				++$no,
				sg_date($rs->saledate,'ว ดด ปป'),
				$rs->refno,
				$rs->partnerName,
				$rs->brandname.($rs->model?'/'.$rs->model:''),
				$rs->plate,
				number_format($rs->costprice,2),
				number_format($rs->saleprice,2),
				number_format($rs->financeprice,2),
				number_format($rs->saledownprice,2),
				number_format($rs->saledownpaid,2),
				number_format($rs->saledownprice-$rs->saledownpaid,2),
				$rs->sold=='Yes'?'ปิด':($rs->saledate?'ขาย':''),
				'<a class="-no-print" href="'.url('icar/'.$rs->tpid).'" target="_blank"><i class="icon -view"></i></a>',
			);
		}

		$tables->tfoot[]=array(
			'<td></td>','','','','','รวม',
			number_format($dbs->sum->costprice,2),
			number_format($dbs->sum->saleprice,2),
			number_format($dbs->sum->financeprice,2),
			number_format($dbs->sum->saledownprice,2),
			number_format($dbs->sum->saledownpaid,2),
			number_format($dbs->sum->saledownprice-$dbs->sum->saledownpaid,2),
			'','',
		);

		$ret .= $tables->build();
		$ret .= '<p>รวมทั้งสิ้น '.$dbs->_num_rows.' คัน';
	}

	return $ret;
}
?>