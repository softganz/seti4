<?php
function green_supplier_form_approve($self,$qtref) {
	//$ret=print_o(post(),'post()');


	$stmt='UPDATE %qtmast% SET `qtstatus`=:approve WHERE `qtref`=:qtref LIMIT 1';
	mydb::query($stmt,':qtref',$qtref, ':approve',post('approve'));
	//$ret.=mydb()->_query;

	$qtInfo=R::Model('green.qt.get',$qtref);

	$data->qtid=post('qtid');
	$data->qtref=$qtref;
	$data->part='APPROVE.REMARK';
	$data->value=post('approveremark');
	$data->ucreated=$data->umodify=i()->uid;
	$data->dcreated=$data->dmodify=date('U');
	$result=R::Model('imed.qttran.update',$data);
	//$ret.=print_o($result,'$result');

	$orgData=new stdClass;
	$orgData->orgid=$qtInfo->orgid;
	$orgData->uid=i()->uid;
	$orgData->name=$qtInfo->name;
	$orgData->created=date('U');
	$orgData->address=$qtInfo->tr['ORG.ADDRESS']->value;
	$orgData->phone=$qtInfo->tr['ORG.PHONE']->value;

	// Explode Address
	$address=SG\explode_address($orgData->address,$qtInfo->tr['ORG.AREACODE']->value);
	$orgData->house=$address['house'];
	$orgData->village=$address['village'];
	$orgData->house=$address['house'];
	$orgData->village=$address['village'];
	$orgData->tambon=$address['tambonCode'];
	$orgData->ampur=$address['ampurCode'];
	$orgData->changwat=$address['changwatCode'];
	$orgData->zipcode=$qtInfo->tr['ORG.ZIP']->value;

	$stmt='INSERT INTO %db_org%
				(
				  `orgid`
				, `uid`
				, `name`
				, `address`
				, `house`
				, `village`
				, `tambon`
				, `ampur`
				, `changwat`
				, `zipcode`
				, `phone`
				, `created`
				)
				VALUES
				(
				  :orgid
				, :uid
				, :name
				, :address
				, :house
				, :village
				, :tambon
				, :ampur
				, :changwat
				, :zipcode
				, :phone
				, :created
				)
				ON DUPLICATE KEY UPDATE
				  `name`=:name
				, `address`=:address
				, `house`=:house
				, `village`=:village
				, `tambon`=:tambon
				, `ampur`=:ampur
				, `changwat`=:changwat
				, `zipcode`=:zipcode
				, `phone`=:phone
				';
	mydb::query($stmt,$orgData);
	//$ret.=mydb()->_query.'<br />'._NL;

	if (empty($qtInfo->orgid)) {
		$qtInfo->orgid=mydb()->insert_id;
		$stmt='UPDATE %qtmast% SET `orgid`=:orgid WHERE `qtref`=:qtref LIMIT 1';
		mydb::query($stmt,':qtref',$qtref,':orgid',$qtInfo->orgid);
		//$ret.=mydb()->_query.'<br />';
	}

	location('green/app/supplier/list');
	return $ret;
}
?>