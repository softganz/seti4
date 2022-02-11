<?php
/**
* iMed Model :: Poorman Save
* Created 2017-10-23
* Modify  2020-09-28
*
* @param Object $data
* @return Object
*/

$debug = true;

import('model:imed.visit');

function r_imed_poorman_save($data) {
	$data->msg=_NL.'!!!----------'._NL;
	// Create new poorman
	if (empty($data->qtref)) {
		if (empty($data->psnid) && $data->{'qt:PSNL.FULLNAME'}) {
			// Create new person
			$psndata->uid=i()->uid;
			$psndata->prename=$data->{'qt:PSNL.PRENAME'};
			list($psndata->name,$psndata->lname)=sg::explode_name(' ',$data->{'qt:PSNL.FULLNAME'});
			$psndata->cid=$data->{'qt:PSNL.CID'};
			$psndata->created=date('U');

			$stmt='SELECT `psnid` FROM %db_person% WHERE `name`=:name AND `lname`=:lname AND `cid`=:cid LIMIT 1';
			$isDupPerson=mydb::select($stmt,$psndata)->psnid;
			if ($isDupPerson) {
				$result->isDupPerson=true;
				return $result;
			}

			// Explode Address
			$address=SG\explode_address($data->{'qt:PSNL.REGIST.ADDRESS'},$data->{'qt:PSNL.REGIST.AREACODE'});
			$psndata->rhouse=$address['house'];
			$psndata->rvillage=$address['village'];
			$psndata->rtambon=$address['tambonCode'];
			$psndata->rampur=$address['ampurCode'];
			$psndata->rchangwat=$address['changwatCode'];
			$psndata->rzip=$address['zip'];

			$psndata->house=$address['house'];
			$psndata->village=$address['village'];
			$psndata->house=$address['house'];
			$psndata->village=$address['village'];
			$psndata->tambon=$address['tambonCode'];
			$psndata->ampur=$address['ampurCode'];
			$psndata->changwat=$address['changwatCode'];
			$psndata->zip=$address['zip'];

			$data->{'qt:PSNL.HOME.ADDRESS'}=$data->{'qt:PSNL.REGIST.ADDRESS'};
			$data->{'qt:PSNL.HOME.AREACODE'}=$data->{'qt:PSNL.REGIST.AREACODE'};

			$stmt = 'INSERT INTO %db_person%
				(
				  `uid`
				, `prename`
				, `name`
				, `lname`
				, `cid`
				, `house`
				, `village`
				, `tambon`
				, `ampur`
				, `changwat`
				, `zip`
				, `rhouse`
				, `rvillage`
				, `rtambon`
				, `rampur`
				, `rchangwat`
				, `rzip`
				, `created`
				)
				VALUES
				(
				  :uid
				, :prename
				, :name
				, :lname
				, :cid
				, :house
				, :village
				, :tambon
				, :ampur
				, :changwat
				, :zip
				, :rhouse
				, :rvillage
				, :rtambon
				, :rampur
				, :rchangwat
				, :rzip
				, :created
				)';

			mydb::query($stmt,$psndata);

			$data->msg .= mydb()->_query.'<br />'._NL;
			$data->psnid = mydb()->insert_id;

		}

		if ($data->psnid) {
			// Create home visit
			$visit->uid = i()->uid;
			$visit->pid = $data->psnid;
			$visit->service = 'Home Visit';
			$visit->rx = 'เก็บแบบสอบถามคนยากลำบาก';
			$visit->timedata = $visit->created = date('U');

			if (R()->appAgent) {
				$visit->appsrc = R()->appAgent->OS;
				$visit->appagent = R()->appAgent->dev.'/'.R()->appAgent->ver.' ('.R()->appAgent->type.';'.R()->appAgent->OS.')';
			} else if (preg_match('/imed\/app/',$_SERVER["HTTP_REFERER"])) {
				$visit->appsrc = 'Web App';
				$visit->appagent = 'Web App';
			} else {
				$visit->appsrc = 'Web';
				$visit->appagent = 'Web';
			}

			$stmt = 'INSERT INTO %imed_service%
				(`uid`, `pid`, `service`, `appsrc`, `appagent`, `rx`, `timedata`, `created`)
				VALUES
				(:uid, :pid, :service, :appsrc, :appagent, :rx, :timedata, :created)';

			mydb::query($stmt,$visit);

			$data->seq = mydb()->insert_id;
			$data->msg .= mydb()->_query.'<br />'._NL;

			ImedVisitModel::firebaseAdded($data->psnid, $data->seq);

			// Create qt_mast
			$data->uid=i()->uid;
			$data->qtdate=date('Y-m-d');
			$data->created=date('U');
			$stmt='INSERT INTO %qtmast%
						(`qtgroup`, `qtform`, `psnid`, `uid`, `seq`, `qtdate`, `created`)
						VALUES
						(:qtgroup, :qtform, :psnid, :uid, :seq, :qtdate, :created)';
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
		return $data;
	}








	if ($data->qtdate) $data->qtdate=sg_date($data->qtdate,'Y-m-d');

	$psnInfo=mydb::select('SELECT * FROM %db_person% WHERE `psnid`=:psnid LIMIT 1',':psnid',$data->psnid);
	//$data->msg.=print_o($psnInfo,'$psnInfo');

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

	//$data->msg.=print_o($data->birth,'$data->birth');
	if ($data->birth['year']) {
		$data->{'qt:PSNL.BIRTH'}=$data->birth['year'].'-'.($data->birth['month']?$data->birth['month']:'01').'-'.($data->birth['date']?$data->birth['date']:'01');
	} else {
		$data->{'qt:PSNL.BIRTH'}=NULL;
	}

	$data->qtrefno=$data->qtref.'/'.(date('Y')+543);


	// Update person
	// prename,name,lname,cid,sex,birth,house,village,tambon,ampur,changwat,zip,rtambon,rampur,rchangwat,rzip,mstatus,occupa,race,nation,religion,educate,phone
	// and hcode:รหัสประจำบ้าน,rhcode

	$data->msg.='PSNID : '.$data->psnid.' uid : '.$psnInfo->uid._NL;
	$isAdmin=user_access('access administrator pages');
	$isEditPerson=i()->ok && $data->{'qt:PSNL.FULLNAME'} &&  (i()->uid==$psnInfo->uid || $isAdmin);

	if ($isEditPerson && $data->psnid) {
		list($data->{'qt:PSNL.NAME'},$data->{'qt:PSNL.LNAME'})=sg::explode_name(' ',$data->{'qt:PSNL.FULLNAME'});
		unset($psndata);
		$psndata->psnid=$data->psnid;
		$psndata->prename=$data->{'qt:PSNL.PRENAME'};
		$psndata->name=$data->{'qt:PSNL.NAME'};
		$psndata->lname=$data->{'qt:PSNL.LNAME'};
		$psndata->cid=$data->{'qt:PSNL.CID'};
		$psndata->sex=$data->{'qt:PSNL.SEX'};

		$address=SG\explode_address($data->{'qt:PSNL.REGIST.ADDRESS'},$data->{'qt:PSNL.REGIST.AREACODE'});
		$address['commune'] = $data->{'qt:PSNL.REGIST.PLACENAME'};
		$address['zip'] = $data->{'qt:PSNL.REGIST.ZIP'};

		$psndata->rhouse=$address['house'];
		$psndata->rvillage=$address['village'];
		$psndata->rtambon=$address['tambonCode'];
		$psndata->rampur=$address['ampurCode'];
		$psndata->rchangwat=$address['changwatCode'];
		$psndata->rzip = $address['zip'];

		if ($data->{'qt:PSNL.HOME.NOTSAMEADDRESS'}) {
			$address=SG\explode_address($data->{'qt:PSNL.HOME.ADDRESS'},$data->{'qt:PSNL.HOME.AREACODE'});
			$address['zip'] = $data->{'qt:PSNL.HOME.ZIP'};
			$address['commune'] = $data->{'qt:PSNL.HOME.PLACENAME'};
		}
		$psndata->house = $address['house'];
		$psndata->village = $address['village'];
		$psndata->commune = $address['commune'];
		$psndata->tambon = $address['tambonCode'];
		$psndata->ampur = $address['ampurCode'];
		$psndata->changwat = $address['changwatCode'];
		$psndata->zip = $address['zip'];

		$psndata->birth=$data->{'qt:PSNL.BIRTH'}?$data->{'qt:PSNL.BIRTH'}:NULL;

		$stmt='UPDATE %db_person% SET
						`prename` = :prename
					, `name` = :name
					, `lname` = :lname
					, `cid` = :cid
					, `sex` = :sex
					, `birth` = :birth
					, `commune` = :commune
					, `house` = :house
					, `village` = :village
					, `tambon` = :tambon
					, `ampur` = :ampur
					, `changwat` = :changwat
					, `zip` = :zip
					, `rhouse` = :rhouse
					, `rvillage` = :rvillage
					, `rtambon` = :rtambon
					, `rampur` = :rampur
					, `rchangwat` = :rchangwat
					, `rzip` = :rzip
					WHERE `psnid` = :psnid
					LIMIT 1';
		mydb::query($stmt,$psndata);
		$data->msg.='Update person information in table person'._NL;
		//$data->msg.='SQL : '.mydb()->_query._NL;
		//print_o($data,'$data',1);
	}

	$qtitems=mydb::select('SELECT `qtid`,`part`,`value` FROM %qttran% WHERE `qtref`=:qtref; -- {key:"part"}',':qtref',$data->qtref)->items;
	//$data->msg.=mydb()->_query;
	//$data->msg.=print_o($qtitems,'$qtitems');



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

	// OTHR.5.5 : สถานะของที่พักอาศัย
	// ECON.4.4.11.1 : รายละเอียดอาชีพ

	//$data->msg.=json_encode($data)._NL;
	$data->msg.='----------!!!'._NL;
	return $data;
}
?>