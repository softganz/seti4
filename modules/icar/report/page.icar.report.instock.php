<?php
function icar_report_instock($self) {
	$getFromDate = SG\getFirst(post('fd'),date('01/m/Y'));
	$getToDate = SG\getFirst(post('td'),date('d/m/Y'));
	$getCarType = post('ct');
	$getCarLocation = post('lc');

	$shop = icar_model::get_my_shop();

	R::View('icar.toolbar', $self, 'รายงานรถในคลังสินค้า @'.$shop->shopname,NULL,NULL,'{showPrint: true}');

	$isAdmin = user_access('administer icars');

	$post=(object)post('report');

	$form = new Form(NULL,url(q()),'icar-report');
	$form->addConfig('method', 'GET');

	$form->addField(
		'fd',
		array(
			'type'=>'text',
			'label'=>'ซื้อตั้งแต่วันที่',
			'size'=>10,
			'class'=>'sg-datepicker',
			'value'=>sg_date($getFromDate,'d/m/Y'),
		)
	);

	$form->addField(
		'td',
		array(
			'type'=>'text',
			'label'=>'ถึง',
			'size'=>10,
			'class'=>'sg-datepicker',
			'value'=>sg_date($getToDate,'d/m/Y'),
		)
	);

	$form->addField(
		'ct',
		array(
			'type'=>'select',
			'options'=>array(''=>'={tr:ทุกประเภท}=')+icar_model::category('icar:cartype',NULL,NULL,'{key: "catid"}'),
			'value' => $getCarType,
		)
	);

	$formOptions = array(''=>'={tr:ทุกลานจอดรถ}=');
	mydb::where('i.`shopid` = :shopid AND i.`saledate` IS NULL AND i.`stklocname` != ""', ':shopid', $shop->shopid);
		$stmt = 'SELECT
				i.`stklocname`
			FROM %icar% i
			%WHERE%
			GROUP BY `stklocname`
			ORDER BY CONVERT(`stklocname` USING tis620) ASC';

	foreach (mydb::select($stmt)->items as $rs) $formOptions[$rs->stklocname] = $rs->stklocname;

	$form->addField(
		'lc',
		array(
			'type'=>'select',
			'options'=>$formOptions,
			'value' => $getCarLocation,
		)
	);

	$form->addField(
		'view',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -search -white"></i><span>ดูรายงาน</span>',
		)
	);

	$ret .= $form->build();



	if ($getFromDate && $getToDate) {
		if (!$isAdmin) mydb::where('i.`shopid` = :shopid', ':shopid', $shop->shopid);
		mydb::where('i.`saledate` IS NULL');
		mydb::where('i.`buydate` BETWEEN :fromdate AND :todate', ':fromdate', sg_date($getFromDate,'Y-m-d'),':todate',sg_date($getToDate,'Y-m-d'));
		if ($getCarType) mydb::where('i.`cartype` = :cartype', ':cartype',$getCarType);
		if ($getCarLocation) mydb::where('i.`stklocname` = :stklocname', ':stklocname',$getCarLocation);

		$stmt = 'SELECT
				i.*
			, b.`name` `brandname`
			FROM %icar% i
				LEFT JOIN %tag% b ON b.`tid` = i.`brand`
			%WHERE%
			ORDER BY `buydate` ASC';

		$dbs = mydb::select($stmt);
		//$ret .= mydb()->_query;

		$tables = new Table();
		$tables->caption = 'รายงานรถในคลังสินค้า';
		$tables->thead = array('no'=>'ลำดับ','buydate -date'=>'วันที่ซื้อ','เลขที่','ยี่ห้อ','รุ่น','ทะเบียน','ลานจอดรถ','cost -money -hover-parent'=>'ต้นทุน');
		$no=0;
		foreach ($dbs->items as $rs) {
			if (sg_date($rs->buydate,'Y-m')!=$cmonth) {
				$cmonth=sg_date($rs->buydate,'Y-m');
				$tables->rows[]=array('<th colspan="13">'.sg_date($rs->buydate,'ดดด ปปปป').'</th>');
				$no=0;
			}

			$ui = new Ui();
			$ui->add('<a href="'.url('icar/'.$rs->tpid).'" target="_blank"><i class="icon -material">pageview</i></a>');
			$menu = '<nav class="icon -hover -no-print">'.$ui->build().'</nav>';
			$tables->rows[] = array(
				++$no,
				sg_date($rs->buydate,'ว ดด ปปปป'),
				$rs->refno,
				$rs->brandname,
				$rs->model,
				$rs->plate,
				$rs->stklocname,
				number_format($rs->costprice,2)
				.$menu
			);
		}
		$ret .= $tables->build();

		$ret.='<p>รวมทั้งสิ้น '.$dbs->_num_rows.' คัน';
	}
	return $ret;
}
?>