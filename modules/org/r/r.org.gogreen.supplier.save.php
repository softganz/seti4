<?php
function r_org_gogreen_supplier_save($data) {
	$data->msg=_NL.'!!!----------'._NL;

	// Create new poorman on empty qtref
	if (empty($data->qtref)) {
		/*
		if (empty($data->orgid) && $data->{'qt:ORG.NAME'}) {
			// Create new person
			$orgData->uid=i()->uid;
			$orgData->name=$data->{'qt:ORG.NAME'};
			$orgData->created=date('U');

			$stmt='SELECT `orgid` FROM %db_org% WHERE `name`=:name LIMIT 1';
			$isDupPerson=mydb::select($stmt,$orgData)->orgid;
			if ($isDupPerson) {
				$result->isDupPerson=true;
				return $result;
			}

			$orgData->address=$data->{'qt:ORG.ADDRESS'};

			// Explode Address
			$address=SG\explode_address($data->{'qt:ORG.ADDRESS'},$data->{'qt:ORG.AREACODE'});
			$orgData->house=$address['house'];
			$orgData->village=$address['village'];
			$orgData->house=$address['house'];
			$orgData->village=$address['village'];
			$orgData->tambon=$address['tambonCode'];
			$orgData->ampur=$address['ampurCode'];
			$orgData->changwat=$address['changwatCode'];
			$orgData->zipcode=$address['zip'];

			$stmt='INSERT INTO %db_org%
						(
						  `uid`
						, `name`
						, `address`
						, `house`
						, `village`
						, `tambon`
						, `ampur`
						, `changwat`
						, `zipcode`
						, `created`
						)
						VALUES
						(
						  :uid
						, :name
						, :address
						, :house
						, :village
						, :tambon
						, :ampur
						, :changwat
						, :zipcode
						, :created
						)';
			mydb::query($stmt,$orgData);
			$data->msg.=mydb()->_query.'<br />'._NL;
			$data->orgid=mydb()->insert_id;
		}

		if ($data->orgid) {
			// Create qt_mast
			$data->uid=i()->uid;
			$data->qtdate=date('Y-m-d');
			$data->created=date('U');
			$stmt='INSERT INTO %qtmast%
						(`qtgroup`, `qtform`, `orgid`, `uid`, `qtdate`, `created`)
						VALUES
						(:qtgroup, :qtform, :orgid, :uid, :qtdate, :created)';
			mydb::query($stmt,$data);
			$data->msg.=mydb()->_query.'<br />'._NL;
			$data->qtref=mydb()->insert_id;

			foreach ($data as $key => $value) {
				if (substr($key,0,3)=='qt:') {
					$part=substr($key,3);
					if ($value===$qtitems[$part]->value) continue;
					$data->msg.='QT update key='.$part.' value='.$value._NL;
					$qtdata->qtid=$qtitems[$part]->qtid;
					$qtdata->qtref=$data->qtref;
					$qtdata->part=$part;
					$qtdata->value=$value;
					$qtdata->ucreated=i()->uid;
					$qtdata->dcreated=date('U');
					$qtdata->umodify=i()->uid;
					$qtdata->dmodify=date('U');
					$stmt='INSERT INTO %qttran%
								(`qtid`,`qtref`,`part`,`value`,`ucreated`,`dcreated`)
								VALUES
								(:qtid,:qtref,:part,:value,:ucreated,:dcreated)
								ON DUPLICATE KEY UPDATE
								  `value`=:value
								, `umodify`=:umodify
								, `dmodify`=:dmodify
								';
					mydb::query($stmt,$qtdata);
					if (mydb()->_error) $data->msg.=strip_tags(mydb()->_query)._NL;
					$data->msg.=mydb()->_query.'<br />'._NL;
				}
			}
		}
		*/

		// Create qt_mast
		$data->uid=i()->uid;
		$data->qtdate=date('Y-m-d');
		$data->created=date('U');
		$stmt='INSERT INTO %qtmast%
					(`qtgroup`, `qtform`, `uid`, `qtdate`, `created`)
					VALUES
					(:qtgroup, :qtform, :uid, :qtdate, :created)';
		mydb::query($stmt,$data);
		$data->msg.=mydb()->_query.'<br />'._NL;
		$data->qtref=mydb()->insert_id;

		foreach ($data as $key => $value) {
			if (substr($key,0,3)=='qt:') {
				$part=substr($key,3);
				if ($value===$qtitems[$part]->value) continue;
				$data->msg.='QT update key='.$part.' value='.$value._NL;
				$qtdata->qtid=$qtitems[$part]->qtid;
				$qtdata->qtref=$data->qtref;
				$qtdata->part=$part;
				$qtdata->value=$value;
				$qtdata->ucreated=i()->uid;
				$qtdata->dcreated=date('U');
				$qtdata->umodify=i()->uid;
				$qtdata->dmodify=date('U');
				$stmt='INSERT INTO %qttran%
							(`qtid`,`qtref`,`part`,`value`,`ucreated`,`dcreated`)
							VALUES
							(:qtid,:qtref,:part,:value,:ucreated,:dcreated)
							ON DUPLICATE KEY UPDATE
							  `value`=:value
							, `umodify`=:umodify
							, `dmodify`=:dmodify
							';
				mydb::query($stmt,$qtdata);
				if (mydb()->_error) $data->msg.=strip_tags(mydb()->_query)._NL;
				$data->msg.=mydb()->_query.'<br />'._NL;
			}
		}
		return $data;
	}








	if ($data->qtdate) $data->qtdate=sg_date($data->qtdate,'Y-m-d');

	$orgInfo=mydb::select('SELECT * FROM %db_org% WHERE `orgid`=:orgid LIMIT 1',':orgid',$data->orgid);
	//$data->msg.=mydb()->_query.print_o($orgInfo,'$orgInfo');

	// Update poorman
	if ($data->qtdate) {
		$stmt='UPDATE %qtmast% SET `qtdate`=:qtdate WHERE `qtref`=:qtref LIMIT 1';
		mydb::query($stmt,$data);
		//$data->msg.=mydb()->_query._NL;
	}

	if ($data->collectname!='') {
		$stmt='UPDATE %qtmast% SET `collectname`=:collectname WHERE `qtref`=:qtref LIMIT 1';
		mydb::query($stmt,$data);
		//$data->msg.=mydb()->_query._NL;
	}

	$data->qtrefno=$data->qtref.'/'.(date('Y')+543);


	// Update person
	// prename,name,lname,cid,sex,birth,house,village,tambon,ampur,changwat,zip,rtambon,rampur,rchangwat,rzip,mstatus,occupa,race,nation,religion,educate,phone
	// and hcode:รหัสประจำบ้าน,rhcode

	$data->msg.='ORGID : '.$data->orgid.' uid : '.$orgInfo->uid._NL;
	$isEditPerson=i()->ok && i()->uid==$orgInfo->uid && $data->{'qt:ORG.NAME'};

	if ($isEditPerson && $data->orgid) {
		unset($orgData);
		$orgData->orgid=$data->orgid;
		$orgData->name=$data->{'qt:ORG.NAME'};
		$orgData->address=$data->{'qt:ORG.ADDRESS'};
		$orgData->house=$address['house'];
		$orgData->village=$address['village'];
		$orgData->house=$address['house'];
		$orgData->village=$address['village'];
		$orgData->tambon=$address['tambonCode'];
		$orgData->ampur=$address['ampurCode'];
		$orgData->changwat=$address['changwatCode'];
		$orgData->zipcode=$data->{'qt:ORG.ZIP'};
		$orgData->phone=$data->{'qt:ORG.PHONE'};

		$stmt='UPDATE %db_org% SET
					  `name`=:name
					, `address`=:address
					, `house`=:house
					, `village`=:village
					, `tambon`=:tambon
					, `ampur`=:ampur
					, `changwat`=:changwat
					, `zipcode`=:zipcode
					, `phone`=:phone
					WHERE `orgid`=:orgid
					LIMIT 1';
		mydb::query($stmt,$orgData);
		$data->msg.='Update person information in table person'._NL;
		$data->msg.='SQL : '.mydb()->_query._NL;
		//print_o($data,'$data',1);
	}

	$qtitems=mydb::select('SELECT `qtid`,`part`,`value` FROM %qttran% WHERE `qtref`=:qtref; -- {key:"part"}',':qtref',$data->qtref)->items;
	//$data->msg.=mydb()->_query;
	//$data->msg.=print_o($qtitems,'$qtitems');

	if ($data->birth) $data->{'qt:PSNL.BIRTH'}=$data->birth['year'].'-'.$data->birth['month'].'-'.$data->birth['date'];


	foreach ($data as $key => $value) {
		if (substr($key,0,3)=='qt:') {
			$part=substr($key,3);
			if ($value===$qtitems[$part]->value) continue;
			$data->msg.='QT update key='.$part.' value='.$value._NL;
			$qtdata->qtid=$qtitems[$part]->qtid;
			$qtdata->qtref=$data->qtref;
			$qtdata->part=$part;
			$qtdata->value=$value;
			$qtdata->ucreated=i()->uid;
			$qtdata->dcreated=date('U');
			$qtdata->umodify=i()->uid;
			$qtdata->dmodify=date('U');
			$stmt='INSERT INTO %qttran%
						(`qtid`,`qtref`,`part`,`value`,`ucreated`,`dcreated`)
						VALUES
						(:qtid,:qtref,:part,:value,:ucreated,:dcreated)
						ON DUPLICATE KEY UPDATE
						  `value`=:value
						, `umodify`=:umodify
						, `dmodify`=:dmodify
						';
			mydb::query($stmt,$qtdata);
			if (mydb()->_error) $data->msg.='SQL : '.strip_tags(mydb()->_query)._NL;
			//$data->msg.=mydb()->_query._NL;
		}
	}

	//$data->msg.=json_encode($data)._NL;
	$data->msg.='----------!!!'._NL;
	return $data;
}
?>