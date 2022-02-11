<?php
/**
* Garage Recieve Money
* Created 2019-10-13
* Modify  2019-10-13
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_rcvmoney($self) {
	$getInsu = post('insu');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	new Toolbar($self,'ใบรับเงิน','finance');

	$shopInfo = R::Model('garage.get.shop');

	R::Model('garage.verify',$self, $shopInfo,'FINANCE');

	$post=(object)post();
	if ($post->newbill && $post->billdate && $post->insurerid) {
		$billid=R::Model('garage.billing.create',$shopInfo->shopid,$post,'{debug:false}');
		if ($billid) location('garage/billing/view/'.$billid);
	}

	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/rcvmoney'),NULL, 'garage-rcvmoney-form -sg-flex');
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

	mydb::where('(j.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);
	if ($getInsu) mydb::where('j.`insurerid` = :insurerid',':insurerid', $getInsu);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		j.*
		, s.`shortname` `shopShortName`
		, SUM(`replyprice`) `replyPrice`
		, i.`insurername`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_insurer% i USING(`insurerid`)
			LEFT JOIN %garage_qt% q USING(`tpid`)
		%WHERE%
		GROUP BY j.`tpid`
		HAVING `replyPrice` > 0
		ORDER BY j.`tpid` DESC
		$LIMIT$
		';

	$dbs = mydb::select($stmt,':shopid',$shopInfo->shopid);
	//$ret.=print_o($dbs,'$dbs');

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

	$tables = new Table();
	$tables->thead = array(
		'jobno -center -nowrap'=>'เลขใบซ่อม',
		'rcvdate -date'=>'วันรับรถ',
		'ทะเบียน','บริษัทประกัน',
		'replyprice -money'=>'ราคาตกลง',
		'rcvyprice -money'=>'รับเงิน',
		'rcvmdate -date'=>'วันรับเงิน',
		'status -center -nowrap'=>'สถานะ',
		''
	);

	foreach ($dbs->items as $rs) {
		$isRecieved = strtoupper($rs->isrecieved) === 'YES';
		$ui = new Ui();
		$ui->add('<a href="'.url('garage/job/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/rcvmoney').'" data-rel="box" data-width="640" data-height="500"><i class="icon -material '.($isRecieved ? '-green' : '-gray').'">'.($isRecieved ? 'done_all' : 'done').'</i></a>');
		$menu = '<nav class="nav -icons">'.$ui->build().'</nav>';

		$tables->rows[] = array(
			$rs->jobno,
			sg_date($rs->rcvdate,'d/m/ปปปป'),
			$rs->plate,
			$rs->insurername,
			$rs->replyPrice > 0 ? number_format($rs->replyPrice,2) : '',
			$rs->rcvmoneyamt > 0 ? number_format($rs->rcvmoneyamt,2) : '',
			$rs->rcvmoneydate ? sg_date($rs->rcvmoneydate,'d/m/ปปปป') : '',
			GarageVar::$jobStatusList[$rs->jobstatus],
			$menu,
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);
	}


	$ret .= $tables->build();

	$ret .= '<div class="pagenv">'.$pageNav->build().'</div>'._NL;


	return $ret;
}