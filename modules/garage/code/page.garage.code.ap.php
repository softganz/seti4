<?php
function garage_code_ap($self, $action = NULL, $id = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	new Toolbar($self,'เจ้าหนี้');

	switch ($action) {
		case 'edit':
			$data=R::Model('garage.ap.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_ap_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=(object)post();
			$saveResult.=__garage_code_ap_save($shopInfo,$data);
			$ret.=__garage_code_ap_list($shopInfo,NULL,NULL,$data->apid);
			//$ret.=$saveResult;
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_ap_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_ap_list($shopInfo);
			break;


		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_ap_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_ap_list($shopInfo,$data = NULL,$action = NULL,$trid = NULL) {
	$shopid=$shopInfo->shopid;

	$where=array();
	$where=sg::add_condition($where,'`shopid` IN (:shopid)','shopid',$shopid);
	if (post('q')) $where=sg::add_condition($where,'`apname` LIKE :q','q','%'.post('q').'%');
	$stmt='SELECT *
				FROM %garage_ap%
				WHERE '.implode(' AND ',$where['cond']).'
				ORDER BY CONVERT(`apname` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/ap/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead=array('description'=>'ชื่อเจ้าหนี้','phone -nowrap'=>'โทรศัพท์','ยอดค้างชำระ','');

	$tables->rows[]=array(
		'<input id="oldid" type="hidden" name="oldid" value="'.$data->apid.'" />'
		.'<input id="apid" type="hidden" name="apid" value="'.$data->apid.'" />'
		.'<input id="apname" class="form-text sg-autocomplete -fill -require" type="text" name="apname" value="'.$data->apname.'" placeholder="ชื่อเจ้าหนี้" data-query="'.url('garage/api/ap').'" data-select=\'{"oldid":"value","apid":"value","apname":"label","apphone":"phone"}\' />'
		.'<button class="search"><i class="icon -search"></i></button>',
		'<td colspan="2"><input id="apphone" class="form-text -fill" type="text" name="apphone" value="'.$data->apphone.'" placeholder="โทรศัพท์" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/ap/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui=new Ui(NULL,'ui-menu');
		$ui->add('<a class="sg-action" href="'.url('garage/code/ap/edit/'.$rs->apid).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/code/ap/delete/'.$rs->apid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');
		$menu=sg_dropbox($ui->build());

		$config=array();
		if ($trid && $trid==$rs->apid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->apname,
			$rs->apphone,
			'',
			$menu,
			'config'=>$config,
		);
	}
	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_ap_save($shopInfo,$data) {
	//$ret.=print_o($data,'$data');
	$data->shopid=$shopInfo->shopid;
	$data->apid=strtoupper($data->apid);
	$stmt='INSERT INTO %garage_ap%
		(`shopid`, `apid`, `apname`, `apphone`)
		VALUES
		(:shopid, :apid, :apname, :apphone)
		ON DUPLICATE KEY UPDATE
		`apname`=:apname, `apphone`=:apphone';
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->apid) {
		// Remove old id
		$stmt='DELETE FROM %garage_ap% WHERE `shopid`=:shopid AND `apid`=:oldid LIMIT 1';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt='UPDATE %garage_job% SET `apid`=:apid WHERE `shopid`=:shopid AND `apid`=:oldid';
		//mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
	}

	//$ret.=print_o($data,$data);
	return $ret;
}

function __garage_code_ap_delete($shopInfo,$id) {
	$stmt='DELETE FROM %garage_ap% WHERE `shopid`=:shopid AND `apid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}
?>