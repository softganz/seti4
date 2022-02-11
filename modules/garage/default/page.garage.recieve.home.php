<?php
/**
* Garage :: Recieve Controller
* Created 2017-12-28
* Modify  2020-02-18
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_recieve_home($self) {
	$getInsu = post('insu');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	new Toolbar($self,'ใบเสร็จรับเงิน','finance');

	$shopInfo=R::Model('garage.get.shop');

	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/recieve'),NULL, 'sg-form garage-recieve-form -sg-flex');
	$form->addConfig('method','get');
	$form->addData('rel', '#main');

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

	mydb::where('(r.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);
	if ($getInsu) mydb::where('r.`insurerid` = :insurerid',':insurerid', $getInsu);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		r.*
		, s.`shortname` `shopShortName`
		, i.`insurername`
		, SUM(q.`replyprice`) `totalPrice`
		, GROUP_CONCAT(j.`jobno`) `jobNoList`
		FROM %garage_rcv% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`rcvid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
		%WHERE%
		GROUP BY `rcvid`
		ORDER BY `rcvid` DESC
		$LIMIT$
		';

	$dbs = mydb::select($stmt);

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



	$ret .= '<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/info/*/recieve.new').'" data-checkvalid="true">'._NL;


	$tables = new Table();
	$tables->thead = array(
		'เลขที่ใบเสร็จรับเงิน',
		'date -rcvdate'=>'วันที่',
		'บริษัทประกัน',
		'money -totalprice'=>'จำนวนเงิน',
		'status -hover-parent' => 'สถานะ',
	);

	$tables->rows[]=array(
		'<input type="hidden" name="newbill" value="yes" />'
		.'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="rcvno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'recieve')->nextNo.'" placeholder="รหัส" size="5" maxlength="10" readonly="readonly" />',
		'<input id="rcvdate" class="form-text sg-datepicker -fill -require" type="text" name="rcvdate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
		'<input id="insurerid" type="hidden" name="insurerid" value="" /><input id="insurername" class="form-text sg-autocomplete -fill -require" type="text" name="insurername" value="" placeholder="ชื่อบริษัทประกัน" size="7" data-query="'.url('garage/api/insurer').'" data-altfld="insurerid" data-select="label" data-callback="loadInsurerQt" />',
		'<td colspan="3"><button id="recieve-save" class="btn -primary -disabled" type="submit"><i class="icon -material">add</i><span>สร้างใบเสร็จรับเงินใหม่</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	$tables->rows[]='<tr><td colspan="6"><div id="qtlist"></div></td></tr>';

	$tables->rows[]='<header>';
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->rcvno,
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->insurername
			. '<br /><em style="font-size: 0.8em;">ใบสั่งซ่อม '.str_replace(',', ', ', $rs->jobNoList).'</em>',
			$rs->totalPrice?number_format($rs->totalPrice,2):'',

			GarageVar::$jobStatusList[$rs->jobstatus]
			. '<nav class="nav -icons -hover"><a href="'.url('garage/recieve/'.$rs->rcvid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>',
			'config'=>array('class' => '-shop-'.$rs->shopShortName.' status-'.$rs->rcvstatus),
		);
	}
	$ret.=$tables->build();

	$ret.='</form>';

	//$ret.=print_o($dbs,'$dbs');

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;

	$ret.='<script type="text/javascript">
	function loadInsurerQt($this,ui) {
		$.get("'.url('garage/api/insurerqt').'",{id:ui.item.value,cond:"norcv"},function(html) {
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
		if (hasCheck) $("#recieve-save").removeClass("-disabled")
		else $("#recieve-save").addClass("-disabled")
	});
	</script>';
	return $ret;
}
?>