<?php
function garage_code_brand($self, $action = NULL, $id = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	new Toolbar($self, 'ยี่ห้อรถ');

	switch ($action) {
		case 'edit':
			$data=R::Model('garage.brand.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_brand_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=(object)post();
			$saveResult.=__garage_code_brand_save($shopInfo,$data);
			$ret.=__garage_code_brand_list($shopInfo,NULL,NULL,$data->brandid);
			$ret.=$saveResult;
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_brand_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_brand_list($shopInfo);
			break;


		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_brand_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_brand_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$isAdmin = is_admin('garage');
	$shopid = $shopInfo->shopid;

	$stmt = 'SELECT * FROM %garage_brand% WHERE `shopid` = 0 OR `shopid` IN (:shopid) ORDER BY CONVERT(`brandname` USING tis620) ASC';

	$dbs = mydb::select($stmt,':shopid',$shopid);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/brand/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead=array('code'=>'รหัส','description'=>'รายละเอียด','');

	$tables->rows[]=array(
		'<input type="hidden" name="oldid" value="'.$data->brandid.'" />'
		.'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="brandid" value="'.$data->brandid.'" placeholder="รหัส" size="5" maxlength="10" />',
		'<td colspan="2"><input id="brandname" class="form-text -fill -require" type="text" name="brandname" value="'.$data->brandname.'" placeholder="รายละเอียด" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui = new Ui(NULL,'ui-menu');
		$menu = '';
		if ($rs->shopid != 0) {
			$ui->add('<a class="sg-action" href="'.url('garage/code/brand/edit/'.$rs->brandid).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
			$ui->add('<a class="sg-action" href="'.url('garage/code/brand/delete/'.$rs->brandid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');
			$menu = sg_dropbox($ui->build());
		}

		$config=array();
		if ($trid && $trid==$rs->brandid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->brandid,
			$rs->brandname,
			$menu,
			'config'=>$config,
		);
	}
	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_brand_save($shopInfo,$data) {
	$data->shopid=$shopInfo->shopid;
	$data->brandid=strtoupper($data->brandid);
	$stmt='INSERT INTO %garage_brand%
		(`shopid`,`brandid`,`brandname`)
		VALUES
		(:shopid,:brandid,:brandname)
		ON DUPLICATE KEY UPDATE
		`brandname`='.($data->oldid?':brandname':'`brandname`');
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->brandid) {
		// Remove old id
		$stmt='DELETE FROM %garage_brand% WHERE `shopid`=:shopid AND `brandid`=:oldid LIMIT 1';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt='UPDATE %garage_job% SET `brandid`=:brandid WHERE `shopid`=:shopid AND `brandid`=:oldid';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
	}

	//$ret.=print_o($data,$data);
	return $ret;
}

function __garage_code_brand_delete($shopInfo,$id) {
	$stmt='DELETE FROM %garage_brand% WHERE `shopid`=:shopid AND `brandid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}
?>