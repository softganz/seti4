<?php
function garage_code_customer($self, $action = NULL, $id = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	new Toolbar($self,'ลูกค้า');

	switch ($action) {
		case 'edit':
			$data=R::Model('garage.customer.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_insurer_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=(object)post();
			$saveResult.=__garage_code_insurer_save($shopInfo,$data);
			$ret.=__garage_code_insurer_list($shopInfo,NULL,NULL,$data->customerid);
			//$ret.=$saveResult;
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_insurer_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_insurer_list($shopInfo);
			break;


		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_insurer_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_insurer_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$shopid=$shopInfo->shopid;

	mydb::where('`shopid` IN (:shopid)',':shopid',$shopid);
	if (post('q')) mydb::where('`customername` LIKE :q',':q','%'.post('q').'%');

	$stmt = 'SELECT *
		FROM %garage_customer%
		%WHERE%
		ORDER BY CONVERT(`customername` USING tis620) ASC';

	$dbs = mydb::select($stmt);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/customer/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead=array('description'=>'ชื่อลูกค้า','phone -nowrap'=>'โทรศัพท์','');

	$tables->rows[]=array(
		'<input id="oldid" type="hidden" name="oldid" value="'.$data->customerid.'" />'
		.'<input id="customerid" type="hidden" name="customerid" value="'.$data->customerid.'" />'
		.'<input id="customername" class="form-text sg-autocomplete -fill -require" type="text" name="customername" value="'.$data->customername.'" placeholder="ชื่อลูกค้า" data-query="'.url('garage/api/customer').'" data-select=\'{"oldid":"value","customerid":"value","customername":"label","customerphone":"phone"}\' />'
		.'<button class="search"><i class="icon -search"></i></button>',
		'<td colspan="2"><input id="customerphone" class="form-text -fill" type="text" name="customerphone" value="'.$data->customerphone.'" placeholder="โทรศัพท์" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/customer/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui=new Ui(NULL,'ui-menu');
		$ui->add('<a class="sg-action" href="'.url('garage/code/customer/edit/'.$rs->customerid).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/code/customer/delete/'.$rs->customerid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');
		$menu=sg_dropbox($ui->build());

		$config=array();
		if ($trid && $trid==$rs->customerid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->customername,
			$rs->customerphone,
			$menu,
			'config'=>$config,
		);
	}
	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_insurer_save($shopInfo,$data) {
	//$ret.=print_o($data,'$data');
	$data->shopid=$shopInfo->shopid;
	$data->customerid=strtoupper($data->customerid);
	$stmt = 'INSERT INTO %garage_customer%
		(`shopid`, `customerid`, `customername`, `customerphone`)
		VALUES
		(:shopid, :customerid, :customername, :customerphone)
		ON DUPLICATE KEY UPDATE
		`customername`=:customername, `customerphone`=:customerphone';
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->customerid) {
		// Remove old id
		$stmt='DELETE FROM %garage_customer% WHERE `shopid`=:shopid AND `customerid`=:oldid LIMIT 1';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt='UPDATE %garage_job% SET `customerid`=:customerid WHERE `shopid`=:shopid AND `customerid`=:oldid';
		//mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
	}

	//$ret.=print_o($data,$data);
	return $ret;
}

function __garage_code_insurer_delete($shopInfo,$id) {
	$stmt='DELETE FROM %garage_customer% WHERE `shopid`=:shopid AND `customerid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}
?>