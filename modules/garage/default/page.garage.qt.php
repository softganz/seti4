<?php
/**
* Garage Quotation
* Created 2019-10-13
* Modify  2019-10-14
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_qt($self) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	R::Model('garage.verify',$self, $shopInfo,'JOB');

	$getInsu = post('insu');
	$getSearch = post('searchqt');
	$getShow = post('show');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	if (!$shopId) return message('error', 'PROCESS ERROR');

	new Toolbar($self,'ใบเสนอราคา','finance',$shopInfo);


	/*
	$insurerList = array();
	$dbs = mydb::select('SELECT `insurerid`,`insurername` FROM %garage_insurer% WHERE `shopid`=:shopid ORDER BY CONVERT(`insurername` USING tis620)',':shopid',$shopInfo->shopid);
	foreach ($dbs->items as $rs) $insurerList[$rs->insurerid] = $rs->insurername;
	*/

	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/qt'),"qtsearch", 'garage-qt-form -sg-flex');
	$form->addConfig('method','get');

	/*
	$form->addField(
		'insu',
		array(
			'type'=>'select',
			'options'=>array(''=>'==ทุกบริษัทประกัน==')+$insurerList,
			'value' => $getInsu,
			'style' => 'width: 200px;',
			'attr' => 'onchange="form.submit()"',
		)
	);
	*/

	$form->addField('insu', array('type' => 'hidden', 'value' => htmlspecialchars($getInsu)));

	$form->addField(
		'insuname',
		array(
			'type' => 'text',
			'name' => false,
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/insurer'),
				'data-altfld' => "edit-insu",
				'data-callback' => 'submit',
			),
			'value' => htmlspecialchars($insuName),
			'placeholder' => 'ชื่อบริษัทประกัน',
			'pretext' => '<div class="input-prepend">'
				. '<span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-insu").val("");$("#edit-insuname").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
				. '</div>',
			'posttext' => '<div class="input-append">'
				. '<span><a class="sg-action -sg-16" href="'.url('garage/api/insurername', array('id' => 'edit-insu', 'name' => 'edit-insuname')).'" data-rel="box" data-width="480"><i class="icon -material -gray -sg-16">keyboard_arrow_down</i></a></span>'
				. '</div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'searchqt',
		array(
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($getSearch),
			'placeholder' => 'เลขใบเสนอราคา',
			'pretext' => '<div class="input-prepend"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-searchqt").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= '<script type="text/javascript">
	$(document).on("click","#orglist a",function() {
		//console.log("insu change")
		$("#qtsearch").submit()
	})
	</script>';

	$showOptions = array(''=>'=ทุกเงื่อนไข=','invoiced'=>'แจ้งหนี้แล้ว','notinvoice'=>'ยังไม่แจ้งหนี้','billed'=>'วางบิลแล้ว','notbill' => 'ยังไม่วางบิล','recieved'=>'รับเงินแล้ว','notrecieve'=>'ยังไม่รับเงิน');
	$form->addField(
		'show',
		array(
			'type' => 'select',
			'options' => $showOptions,
			'value' => $getShow,
			'attr' => 'onChange=\'this.form.submit()\''
		)
	);

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">search</i>','container'=>'{class: "-hidden"}'));



	if ($getItemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage - 1) * $getItemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$getItemPerPage);
	}

	mydb::value('$ORDER$', 'ORDER BY '.$orders[$getOrder][1].' '.(strtoupper($getSort) == 'A' ? 'ASC' : 'DESC'));

	mydb::where('(j.`shopid` = :shopid OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopId);
	if ($getInsu) mydb::where('j.`insurerid` = :insurerid',':insurerid', $getInsu);
	if ($getSearch) mydb::where('q.`qtno` LIKE :searchqt', ':searchqt', '%'.$getSearch.'%');
	if ($getShow == 'invoiced') mydb::where('q.`invoiceid` IS NOT NULL');
	else if ($getShow == 'notinvoice') mydb::where('q.`invoiceid` IS NULL');
	else if ($getShow == 'billed') mydb::where('q.`billid` IS NOT NULL');
	else if ($getShow == 'notbill') mydb::where('q.`billid` IS NULL');
	else if ($getShow == 'recieved') mydb::where('q.`rcvid` IS NOT NULL');
	else if ($getShow == 'notrecieve') mydb::where('q.`rcvid` IS NULL');

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		q.*
		, s.`shortName` `shopShortName`
		, j.`jobno`, i.`insurername`
		, (SELECT SUM(`totalsale`) FROM %garage_jobtr% tr WHERE tr.`qtid` = q.`qtid`) `totalPrice`
		FROM %garage_qt% q
			LEFT JOIN %garage_job% j USING(`tpid`)
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i ON q.`insurerid` = i.`insurerid`
		%WHERE%
		ORDER BY q.`qtid` DESC
		$LIMIT$';

	$dbs = mydb::select($stmt);

	//$ret .= mydb()->_query;
	//$ret .= print_o($dbs, '$dbs');

	$pagePara['insu'] = $getInsu;
	$pagePara['searchqt'] = $getSearch;
	$pagePara['order'] = $getOrder;
	$pagePara['sort'] = $getSort;
	$pagePara['item'] = $getItemPerPage != 100 ? $getItemPerPage : NULL;
	$pagePara['page'] = $getPage;
	$pageNav = new PageNavigator($getItemPerPage, $getPage, $dbs->_found_rows, q(), false, $pagePara);
	$itemNo = $pageNav ? $pageNav->FirstItem() : 0;

	$ui = new Ui(NULL, 'ui-action -sg-flex');
	$ui->addClass('-sg-flex');
	$ui->add($form->build());
	$ui->add($pageNav->build());
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	//$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	$tables = new Table();
	$tables->addClass('-qt-list');
	$tables->thead = array(
		'qtno -center' => 'เลขใบเสนอราคา',
		'qtdate -date' => 'วันที่',
		'บริษัทประกัน',
		'totalprice -money' => 'ราคาเสนอ',
		'replyprice -money' => 'ราคาตกลง',
		'inv -center' => 'แจ้งหนี้',
		'bill -center' => 'วางบิล',
		'rcv -center -hover-parent' => 'รับเงิน',
	);
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->qtno,
			$rs->qtdate,
			$rs->insurername,
			$rs->totalPrice,
			$rs->replyprice > 0 ? number_format($rs->replyprice,2) : '',
			$rs->invoiceid ? '<a href="'.url('garage/invoice/'.$rs->invoiceid).'"><i class="icon -material -green">done</i></a>' : ($rs->replyprice > 0 && !($rs->billid || $rs->rcvid) ? '<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/invoice.create/'.$rs->qtid).'" data-title="สร้างใบแจ้งหนี้" data-confirm="ต้องการสร้างใบแจ้งหนี้ กรุณายืนยัน?"><i class="icon -material">add_circle_outline</i></a>' : ''),
			$rs->billid ? '<a class="sg-action" href="'.url('garage/billing/view/'.$rs->billid).'" data-rel="box"><i class="icon -material -green">done</i></a>' : '',
			($rs->rcvid ? '<a class="sg-action" href="'.url('garage/recieve/'.$rs->rcvid).'" data-rel="box"><i class="icon -material -green">done</i></a>' : '')
			. '<nav class="nav -icons -hover"><a href="'.url('garage/job/'.$rs->tpid.'/qt/'.$rs->qtid).'"><i class="icon -material">find_in_page</i></a></nav>',
			'config' => array('class'=>'-shop-'.$rs->shopShortName)
		);
	}

	$ret .= $tables->build();

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>