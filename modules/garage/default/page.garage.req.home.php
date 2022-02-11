<?php
/**
* Garage : Req Home
* Created 2020-10-20
* Modify  2020-10-20
*
* @param Object $self
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_req_home($self){
	$getSearch = post('search');
	$getItemPerPage = SG\getFirst(post('item'), 100);
	$getPage = post('page');

	$shopInfo = R::Model('garage.get.shop');

	mydb::where('(r.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);
	if ($getSearch) mydb::where('r.`reqno` LIKE :search', ':search', '%'.$getSearch.'%');

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
		, j.`jobno`
		, j.`plate`
		FROM %garage_reqmast% r
				LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_job% j USING(`tpid`)
		%WHERE%
		ORDER BY r.`reqid` DESC
		$LIMIT$';

	$dbs = mydb::select($stmt);



	$pagePara['search'] = $getSearch;
	$pagePara['order'] = $getOrder;
	$pagePara['sort'] = $getSort;
	$pagePara['item'] = $getItemPerPage != 100 ? $getItemPerPage : NULL;
	$pagePara['page'] = $getPage;
	$pageNav = new PageNavigator($getItemPerPage, $getPage, $dbs->_found_rows, q(), false, $pagePara);
	$itemNo = $pageNav ? $pageNav->FirstItem() : 0;


	$insuName = mydb::select('SELECT `insurername` FROM %garage_insurer% WHERE `insurerid` = :insurerid LIMIT 1', ':insurerid', $getInsu)->insurername;
	//$ret .= mydb()->_query;


	$form = new Form(NULL, url('garage/req'), NULL, 'garage-req-home-form -sg-flex');
	$form->addConfig('method','get');

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

	$form->addField('go',array('type'=>'button','value'=>'<i class="icon -material">search</i>','container'=>'{class: "-hidden"}'));

	$ui = new Ui(NULL, 'ui-action -sg-flex');
	$ui->addConfig('nav', '{class: "nav -page"}');
	$ui->add($form->build());
	$ui->add($pageNav->build());
	$ret .= $ui->build();




	$ret .= '<form id="garage-master-form" class="sg-form" method="post" action="'.url('garage/info/*/req.create').'" data-checkvalid="true">'._NL;


	$tables = new Table();
	$tables->thead = array('docno -center'=>'เลขที่','date -reqdate'=>'วันที่','name'=>'ใบสั่งซ่อม','ทะเบียน','icons -c1'=>'');

	$tables->rows[] = array(
		'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="docno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'Requisition','REQ')->nextNo.'" placeholder="เลขที่" size="5" maxlength="10" readonly="readonly" />',
		'<input id="docdate" class="form-text sg-datepicker -fill -require" type="text" name="docdate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
		'<input id="jobid" type="hidden" name="jobid" value="" /><input id="refname" class="form-text sg-autocomplete -fill -require" type="text" name="refname" value="" placeholder="เลขที่ใบสั่งซ่อม" size="7" data-query="'.url('garage/api/job',array('show' => 'notreturned')).'" data-altfld="jobid" data-select="label" />',
		'<td colspan=""><button class="btn -primary" type="submit"><i class="icon -material">add</i><span>สร้างใบเบิกของใหม่</span></button>'
		.($action == 'edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'',
		'config' => array('class' => '-input -no-print'),
	);

	$tables->rows[] = '<header>';
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->reqno,
			sg_date($rs->reqdate,'d/m/ปปปป'),
			$rs->jobno,
			$rs->plate,
			'<a href="'.url('garage/req/'.$rs->reqid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>',
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>