<?php
/**
* iBuy :: Customer Home Page
* Created 2019-11-15
* Modify  2020-01-31
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_customer_home($self) {
	$getStatus = post('tk');

	$ret = '<header class="header"><h3>ลูกค้า</h3></header>';

	$isAdmin = user_access('administer ibuys');
	$isOfficer = $isAdmin || user_access('access ibuys customer');

	if (!$isOfficer) return message('error', 'Access Denied');

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	if ($getStatus == '*') {
		$headerNav->add('<a class="btn -uppercase" href="'.url('ibuy/customer').'"><i class="icon -material">alarm</i><span>Open</span></a>');
	} else {
		$headerNav->add('<a class="btn -uppercase" href="'.url('ibuy/customer', array('tk'=>'*')).'"><i class="icon -material">alarm</i><span>Ticket</span></a>');		
	}

	$ret = '<header class="header -box"><h3>ลูกค้า</h3>'.$headerNav->build().'</header>';

	$form = new Form(NULL, url(), NULL, '');
	$form->addField(
		'custname',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete -fill',
			'placeholder' => 'ค้นชื่อลูกค้า,เบอร์โทร, S/N',
			'attr' => array(
				'data-altfld'=>'edit-org-parent',
				'data-query'=>url('ibuy/api/customer',array('n'=>20, 'serial'=>'yes')),
				'data-target' => '#orglist',
				'data-minLength' => 2,
				'data-render-start' => 'customerRenderStart',
				'data-render-item' => 'customerRenderItem',
			),
			'posttext' => '<div class="input-append"><span><a id="search-customer" class="btn -link" href="javascript:void(0)"><i class="icon -material -gray">search</i></a></span><span><a id="add-customer" class="sg-action btn -link" href="'.url('ibuy/customer/create').'" data-rel="box" data-width="640" data-webview="เพิ่มข้อมูลลูกค้า"><i class="icon -material -gray">add_circle</i></a></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= '<nav class="nav -page" style="padding: 0 4px;">'.$form->build().'</nav>';

	$ret .= '<script type="text/javascript">
	function customerRenderStart($this, ui) {
		//console.log("START $THIS ",$this)
		//console.log("UI", ui)
		//console.log("TARGET ", $this.data("target"))
		$($this.data("target")).empty().show()
	}

	function customerRenderItem($this, ul, item) {
		var target = document.getElementById("orglist")
		//console.log("RENDER ITEM ",item.label)
		var detail = ""
		if (item.value == "...") {
			detail = item.label
		} else {
			detail = \'<div class="header"><h5>\'+item.label+\'</h5><nav class="nav -header"><i class="icon -material -customer-pin\'+(item.latlng ? \' -active\' : \'\')+\'">person_pin</i></nav></div><div class="detail">\'+item.desc+\'</div></div>\'
		}
		return $("<a id=\"ibuy-customer-"+item.value+"\" class=\"ui-item sg-action\" href=\"" + rootUrl + "ibuy/customer/"+item.value+"\" data-orgid=\""+item.value+"\" data-rel=\"box\" data-width=\"640\" data-height=\"90%\" data-webview=\"true\" data-webview-title=\""+item.label+"\"></a>")
		.append(detail)
		.appendTo( target );
	}
	</script>';


	$ret .= '<div class="ibuy-customer-home sg-view -co-2">';

	$ticketPara = new stdClass();
	if (post('tk') != '*') $ticketPara->status = 'Open';
	$ticketList = R::Model('ibuy.ticket.get', $ticketPara, '{debug: false, limit: "*"}');


	$ticketCard = new Ui('div', 'ui-card -sg-view ibuy-customer-ticket');
	$ticketCard->header('<h3>รายการรอรับบริการ</h3>', '{}');

	foreach ($ticketList as $rs) {
		$ticketCard->add(
			'<div class="header"><span class="profile"><b>'.$rs->problem.'</b>'.'<br />'
			. $rs->custname. ' by '.$rs->posterName
			. ' @'.sg_date($rs->created, _DATE_FORMAT)
			. '</span>'
			. '<nav class="nav -header"><span class=""><i class="icon -material '.($rs->status == 'Open' ? '-red' : '-green').'">check_circle_outline</i></span></nav>'
			. '</div>'
			. '<div class="detail">'
			. ($rs->productname ? '<h5>สินค้า : '.$rs->productname.'</h5>' : '')
			//. nl2br($rs->detail)
			. '</div>',
			array(
				'id' => 'ticket-'.$rs->tickid,
				'class' => 'sg-action -'.($rs->status),
				'href' => url('ibuy/customer/'.$rs->custid.'/info.ticket/'.$rs->tickid),
				'data-rel' => 'box',
				'data-width' => 640,
				'data-height' => '90%',
				'data-webview' => $rs->problem,
				'onclick' => '',
			)
		);
	}

	$ret .= $ticketCard->build();

	$ui = new Ui('div', 'ui-card -sg-view -orglist -hidden');
	$ui->addClass('-orglist');
	$ui->addId('orglist');
	$ui->header('<h3>รายชื่อลูกค้า</h3>');

	$ret .= $ui->build(true);

	$ret .= '</div>';
	//$ret .= print_o($ticketList, '$ticketList');


	$ret .= '<style type="text/css">
	#ui-id-1 {display: none; border: none; padding: 0;}
	.ui-menu.-orglist .ui-item label {display: block; text-align: left;}
	.ui-menu.-orglist .-has-child {font-weight: bold; font-size: 1.1em;}
	.icon.-customer-pin {color: gray;}
	.icon.-customer-pin.-active {color: green;}
	.ibuy-customer-home>.ui-card.-sg-view.-orglist {flex:0 0 50%;}
	.ibuy-customer-home>.ui-card.-orglist>.ui-item {border: none; margin-bottom: 16px;}
	</style>';

	return $ret;
}
?>