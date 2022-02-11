<?php
/**
* Garage Invloce
* Created 2019-10-13
* Modify  2019-10-13
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_invoice($self, $invoiceId = NULL, $action = NULL, $tranId = NULL) {
	$getInsu = post('insu');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	R::Model('garage.verify',$self, $shopInfo,'FINANCE');

	if (!$shopId) return message('error', 'PROCESS ERROR');

	if ($invoiceId) return R::Page('garage.invoice.'.SG\getFirst($action,'view'), $self, $invoiceId, $tranId);

	new Toolbar($self,'ใบแจ้งหนี้','finance');


	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/invoice'),NULL, 'garage-invoice-form -sg-flex');
	$form->addConfig('method','get');

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
			'pretext' => '<div class="input-prepend"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-insu").val("");$("#edit-insuname").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
			'posttext' => '<div class="input-append"><span><a><i class="icon -material -gray">search</i></a></span></div>',
			'container' => '{class: "-group"}',
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

	mydb::where('(v.`shopid` = :shopid OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopId);
	if ($getInsu) mydb::where('v.`insurerid` = :insurerid',':insurerid', $getInsu);

	$stmt='SELECT SQL_CALC_FOUND_ROWS
		v.*
		, s.`shortname` `shopShortName`
		, i.`insurername`
		, SUM(q.`replyprice`) `totalPrice`
		, GROUP_CONCAT(j.`jobno`) `jobNoList`
		FROM %garage_invoice% v
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`invoiceid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
		%WHERE%
		GROUP BY `invoiceid`
		ORDER BY `invoiceid` DESC
		$LIMIT$
		';

		/*
	$stmt = 'SELECT
			v.*
			, i.`insurername`
			, SUM(q.`replyprice`) `totalPrice`
			, GROUP_CONCAT(j.`jobno`) `jobNoList`
		FROM %garage_invoice% v
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`invoiceid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE v.`shopid` = :shopid
		GROUP BY `invoiceid`
		ORDER BY `invoiceid` DESC
		';
		*/
	$dbs = mydb::select($stmt);
	//$ret . =print_o($dbs,'$dbs');

	$pagePara['insu'] = $getInsu;
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


	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/job/*/invoice.create').'" data-checkvalid="true">'._NL;

	$tables = new Table();
	$tables->thead = array(
		'เลขที่ใบแจ้งหนี้',
		'date -invoicedate' => 'วันที่',
		'บริษัทประกัน',
		'money -totalprice -hover-parent' => 'จำนวนเงิน',
	);

	$tables->rows[] = array(
		'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="no" value="'.R::Model('garage.nextno',$shopInfo->shopid,'invoice')->nextNo.'" placeholder="รหัส" size="5" maxlength="10" readonly="readonly" />',
		'<input id="date" class="form-text sg-datepicker -fill -require" type="text" name="date" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
		'<td colspan="">'
		. '<div class="form-item -group">'
		. '<span class="form-group">'
		. '<input id="insurerid" type="hidden" name="insurerid" value="" />'
		. '<input id="insurername" class="form-text sg-autocomplete -fill -require" type="text" name="insurername" value="" placeholder="ชื่อบริษัทประกัน" size="7" data-query="'.url('garage/api/insurer').'" data-altfld="insurerid" data-select="label" data-callback="loadInsurerQt" />'
		. '<div class="input-append">'
		//. '<span><a class="sg-action" href="'.url('garage/api/insurername', array('id' => 'insurerid', 'name' => 'insurername')).'" data-rel="box" data-width="480"><i class="icon -material -gray">keyboard_arrow_down</i></a></span>'
		. '<span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#insurerid").val("");$("#insurername").val("");$("#insurername").focus();$("#invoice-save").addClass("-disabled");$("#qtlist").empty();\'><i class="icon -material -gray -sg-16">clear</i></a></span>'
		. '</span>'
		. '</div>'
		. '</div>'
		. '</td>',
		'<td colspan="2"><button id="invoice-save" class="btn -primary -disabled" type="submit"><i class="icon -material">add</i><span>สร้างใบแจ้งหนี้</span></button>'
		.'</td>',
		'config'=>array('class' => '-input -no-print'),
	);
	$tables->rows[] = '<tr><td colspan="6"><div id="qtlist"></div></td></tr>';

	$tables->rows[] = '<header>';

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->docno,
			sg_date($rs->docdate,'d/m/ปปปป'),
			$rs->custname
			. '<br /><em style="font-size: 0.8em;">ใบสั่งซ่อม '.str_replace(',', ', ', $rs->jobNoList).'</em>',
			($rs->totalPrice ? number_format($rs->totalPrice,2) : '')
			.'<nav class="nav -icons -hover"><a href="'.url('garage/invoice/'.$rs->invoiceid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>',
			'config' => array('class' => '-shop-'.$rs->shopShortName.($rs->docstatus < 1 ? ' -cancel' : '')),
		);
	}

	$ret .= $tables->build();

	$ret .= '</form>';

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	$ret .= '<script type="text/javascript">
	function loadInsurerQt($this,ui) {
		$.get("'.url('garage/api/insurerqt').'",{id:ui.item.value,cond:"noinvoice"},function(html) {
			$("#qtlist").html(html);
		});
	}

	$("body").on("click","#qtlist .item td",function() {
		var $parent=$(this).closest("tr")
		var $checkBox=$parent.find("input")
		var hasCheck = false

		$checkBox.prop("checked", !$checkBox.prop("checked"))
		if ($checkBox.prop("checked")) {
			$parent.find(".qtcheck").removeClass("-gray").addClass("-green")
		} else {
			$parent.find(".qtcheck").removeClass("-green").addClass("-gray")
		}
		$("#qtlist input[type=checkbox]").each(function() {
			if ($(this).prop("checked")) hasCheck = true
		})
		if (hasCheck) $("#invoice-save").removeClass("-disabled")
		else $("#invoice-save").addClass("-disabled")
	});
	</script>';
	return $ret;
}
?>