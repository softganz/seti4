<?php
function garage_code_repair($self, $action = NULL, $id = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	new Toolbar($self,'รหัสสั่งซ่อม');

	switch ($action) {
		case 'edit':
			$data=R::Model('garage.repaircode.get',$id,'{debug:false}');
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_repair_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=(object)post();
			$saveResult.=__garage_code_repair_save($shopInfo,$data);
			$ret.=__garage_code_repair_list($shopInfo,NULL,NULL,$data->repairid);
			$ret.=$saveResult;
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_repair_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_repair_list($shopInfo);
			break;


		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">';
			$ret.=__garage_code_repair_list($shopInfo);
			$ret.='</div>';
			break;
	}
	return $ret;
}

function __garage_code_repair_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$shopid = $shopInfo->shopid;

	$stmt = 'SELECT r.*, COUNT(j.`repairid`) `idUsed`
		FROM %garage_repaircode% r
			LEFT JOIN %garage_jobtr% j USING(`repairid`)
		WHERE (r.`shopid` = 0 || r.`shopid` IN (:shopid)) AND r.`repairtype` = 1
		GROUP BY `repairid`
		ORDER BY r.`repaircode` ASC';

	$dbs = mydb::select($stmt,':shopid',$shopid);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/repair/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead=array('รหัสสั่งซ่อม','รายละเอียดสั่งซ่อม','money -A'=>'ราคา A','money -B'=>'ราคา B','money -C'=>'ราคา C','money -D'=>'ราคา D','');

	$tables->rows[]=array(
		'<input type="hidden" name="oldid" value="'.$data->repairid.'" />'
		.'<input id="codeid" class="form-text sg-autocomplete -fill -uppercase -require" type="text" name="repaircode" value="'.$data->repaircode.'" placeholder="รหัสสั่งซ่อม" size="10" maxlength="10" data-query="'.url('garage/api/repaircode').'" />',
		'<input id="repairname" class="form-text sg-autocomplete -fill -require" type="text" name="repairname" value="'.$data->repairname.'" placeholder="รายละเอียดสั่งซ่อม" data-query="'.url('garage/api/repaircode').'" />',
		'<input id="" class="form-text -fill -money" type="text" name="priceA" value="'.$data->priceA.'" placeholder="0.00" size="4" maxlength="10" />',
		'<input id="" class="form-text -fill -money" type="text" name="priceB" value="'.$data->priceB.'" placeholder="0.00" size="4" maxlength="10" />',
		'<input id="" class="form-text -fill -money" type="text" name="priceC" value="'.$data->priceC.'" placeholder="0.00" size="4" maxlength="10" />',
		'<input id="" class="form-text -fill -money" type="text" name="priceD" value="'.$data->priceD.'" placeholder="0.00" size="4" maxlength="10" />',
		'',
		'config'=>array('class'=>'-input -no-print'),
	);

	$tables->rows[]=array(
		'',
		'<input id="repairinsu" class="form-text -fill" type="text" name="repairinsu" value="'.$data->repairinsu.'" placeholder="รายละเอียดสั่งซ่อมสำหรับบริษัทประกัน" />',
		'<td colspan="4"><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/repair/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'',
	'config'=>array('class'=>'-input -no-print'),
		);

	foreach ($dbs->items as $rs) {
		$isDeleteable = empty($rs->idUsed);
		$ui = new Ui(NULL,'ui-menu');
		$menu = '';
		if ($rs->shopid != 0) {
			$ui->add('<a class="sg-action" href="'.url('garage/code/repair/edit/'.$rs->repairid).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
			if ($isDeleteable) {
				$ui->add('<a class="sg-action'.($isDeleteable?'':' -disabled').'" href="'.url('garage/code/repair/delete/'.$rs->repairid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');
			} else {
				$ui->add('<a class="'.($isDeleteable?'':' -disabled').'" href="javascript:void(0)"><i class="icon -delete"></i><span>ลบรายการไม่ได้ (มีการใช้งาน)</span></a>');
			}
			$menu = sg_dropbox($ui->build());
		}

		$config=array();
		if ($trid && $trid==$rs->repairid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->repaircode,
			$rs->repairname.($rs->repairinsu?'<br /><em>'.$rs->repairinsu.'</em>':''),
			number_format($rs->priceA,2),
			number_format($rs->priceB,2),
			number_format($rs->priceC,2),
			number_format($rs->priceD,2),
			$menu,
			'config'=>$config,
		);
	}
	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_repair_save($shopInfo, $data = NULL) {
	if (empty($data->repairid)) $data->repairid=NULL;
	$data->shopid=$shopInfo->shopid;
	$data->repaircode=strtoupper($data->repaircode);
	$data->priceA=sg_strip_money($data->priceA);
	$data->priceB=sg_strip_money($data->priceB);
	$data->priceC=sg_strip_money($data->priceC);
	$data->priceD=sg_strip_money($data->priceD);
	$stmt='INSERT INTO %garage_repaircode%
		(`repairid`, `shopid`, `repairtype`, `repaircode`, `repairname`, `repairinsu`, `priceA`, `priceB`, `priceC`, `priceD`)
		VALUES
		(:repairid, :shopid, 1, :repaircode, :repairname,
		:repairinsu, :priceA, :priceB, :priceC, :priceD)
		ON DUPLICATE KEY UPDATE
		'.($data->oldid ?
		'  `repaircode`=:repaircode
		, `repairname`=:repairname
		, `repairinsu`=:repairinsu
		, `priceA`=:priceA
		, `priceB`=:priceB
		, `priceC`=:priceC
		, `priceD`=:priceD'
		:
		' `repaircode`=`repaircode`');
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->repairid) {

	}

	//$ret.=print_o($data,$data);
	return $ret;
}

function __garage_code_repair_delete($shopInfo,$id) {
	if (!__garage_code_repair_isdeleteable($id)) return false;
	$stmt='DELETE FROM %garage_repaircode% WHERE `shopid`=:shopid AND `repairid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
	$ret.='Delete '.$id.mydb()->_query;
	return $ret;
}

function __garage_code_repair_isdeleteable($id) {
	$stmt='SELECT * FROM %garage_jobtr% WHERE `repairid`=:id LIMIT 1';
	$rs=mydb::select($stmt,':id',$id);
	$isDeleteable=$rs->_empty;
	debugMsg($rs,'$rs');
	debugMsg(mydb()->_query);
	return $isDeleteable;
}
?>