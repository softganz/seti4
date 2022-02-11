<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_aprcv_home($self){
	$getSearch = post('search');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	$shopInfo = R::Model('garage.get.shop');

	mydb::where('(r.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);
	if ($getSearch) mydb::where('r.`rcvno` LIKE :search', ':search', '%'.$getSearch.'%');

	if ($getItemPerPage == '*') {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $getPage > 1 ? ($getPage - 1) * $getItemPerPage : 0;
		mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$getItemPerPage);
	}

	mydb::value('$ORDER$', 'ORDER BY '.$orders[$getOrder][1].' '.(strtoupper($getSort) == 'A' ? 'ASC' : 'DESC'));

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		r.*
		, s.`shortname` `shopShortName`
		, a.`apname`
		FROM %garage_apmast% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_ap% a USING(`apid`)
		%WHERE%
		ORDER BY r.`rcvid` DESC
		$LIMIT$';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$pagePara['insu'] = $getInsu;
	$pagePara['search'] = $getSearch;
	$pagePara['order'] = $getOrder;
	$pagePara['sort'] = $getSort;
	$pagePara['item'] = $getItemPerPage != 100 ? $getItemPerPage : NULL;
	$pagePara['page'] = $getPage;
	$pageNav = new PageNavigator($getItemPerPage, $getPage, $dbs->_found_rows, q(), false, $pagePara);
	$itemNo = $pageNav ? $pageNav->FirstItem() : 0;


	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/aprcv'),"aprcv-search", 'garage-aprcv-home-form -sg-flex');
	$form->addConfig('method','get');

	/*
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
	*/

	$form->addField(
		'search',
		array(
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($getSearch),
			'placeholder' => 'เลขที่',
			'pretext' => '<div class="input-prepend"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-search").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= '<script type="text/javascript">
	$(document).on("click","#orglist a",function() {
		//console.log("insu change")
		$("#aprcv-search").submit()
	})
	</script>';

	/*
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
	*/

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">search</i>','container'=>'{class: "-hidden"}'));

	$ui = new Ui(NULL, 'ui-action -sg-flex');
	$ui->add($form->build());
	$ui->add($pageNav->build());
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$ret.='<form id="garage-master-form" class="sg-form" method="post" action="'.url('garage/info/*/aprcv.create').'" data-checkvalid="true">'._NL;


	$tables = new Table();
	$tables->thead=array('docno -center'=>'เลขที่','date -rcvdate'=>'วันที่','name'=>'บริษัท','เลขที่อ้างอิง','money -totalprice'=>'จำนวนเงิน','money -remain'=>'ค้างชำระ','');

	$tables->rows[]=array(
		'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="rcvno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'ApMast','AP')->nextNo.'" placeholder="เลขที่" size="5" maxlength="10" readonly="readonly" style="width: 5em;" />',
		'<input id="rcvdate" class="form-text sg-datepicker -fill -require" type="text" name="rcvdate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" style="width: 6em;" />',
		'<input id="apid" type="hidden" name="apid" value="" /><input id="apname" class="form-text sg-autocomplete -fill -require" type="text" name="apname" value="" placeholder="ชื่อผู้จำหน่าย" size="7" data-query="'.url('garage/api/ap').'" data-altfld="apid" data-select="label" />',
		'<input id="refno" class="form-text -fill -uppercase" type="text" name="refno" value="" placeholder="เลขที่อ้างอิง" size="5" maxlength="30" />',
		'<td>Vat <input class="form-text" type="text" name="vatrate" size="3" placeholder="%" value="'.$shopInfo->vatrate.'" /> <label>ชำระ <input type="checkbox" name="paidcash" value="1" /> เงินสด</label></td>',
		'<td colspan="2"><button class="btn -primary" type="submit"><i class="icon -material">add</i><span>สร้างใบรับของใหม่</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	$tables->rows[]='<header>';
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->rcvno,
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->apname,
			$rs->refno,
			$rs->grandtotal?number_format($rs->grandtotal,2):'0',
			$rs->ispaid==-1?'{tr:Cash}':number_format($rs->grandtotal-$rs->ispaid,2),
			'<a href="'.url('garage/aprcv/'.$rs->rcvid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>',
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>