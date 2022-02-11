<?php
/**
* Organization Mapping
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_mapping($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	if (empty($action)) return R::Page('org.mapping.search',$self, $orgInfo);

	R::View('org.toolbar',$self, 'Mapping', 'mapping', $orgInfo);

	$isEditable = $orgInfo->RIGHT & (_IS_ADMIN | _IS_OFFICER);

	$concatMapWho = true;
	$concatMapWhoCount = 1;

	$ret = '';


	switch ($action) {
		case 'edit':
			//$ret .= 'EDIT '.$tranId;
			if ($isEditable) $ret .= R::Page('org.mapping.view',$orgInfo,'edit',$tranId);
			break;
		
		case 'delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `keyname` = "map" AND `keyid` = :mapId';
				mydb::query($stmt, ':mapId', $tranId);
				//$ret .= mydb()->_query;
				$stmt = 'DELETE FROM %map_networks% WHERE `mapid` = :mapId LIMIT 1';
				mydb::query($stmt, ':mapId', $tranId);
				//$ret .= mydb()->_query;
				$ret = 'DELETED';
			}
			break;

		case 'subject.add':
			//$ret .= 'Add Subject';
			// Save
			if ($isEditable && $tranId && post('subject')) {
				$stmt = 'INSERT INTO %bigdata%
							(`keyname`, `keyid`, `fldname`, `flddata`, `ucreated`, `created`)
							VALUES
							("map", :keyid, "subject", :subject, :uid, :created)
							';
				mydb::query($stmt,':keyid', $tranId, ':subject',post('subject'), ':uid',i()->uid, ':created', date('U'));
				//$ret .= mydb()->_query;
				//$ret .= __org_subject_list($orgInfo);
				return $ret;
			}
			break;

		case 'subject.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "subject" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';
			}
			break;

		case 'project.add':
			if ($isEditable && $projectname = post('projectname')) {
				$stmt = 'INSERT INTO %bigdata%
								(`keyname`, `keyid`, `fldname`, `flddata`, `ucreated`, `created`)
								VALUES
								("map", :keyid, "project", :projectname, :uid, :created)
								';
				mydb::query($stmt,':keyid', $tranId, ':projectname',$projectname, ':uid',i()->uid, ':created', date('U'));
				$bigId = mydb()->insert_id;
				$editText = view::inlineedit(array('group'=>'bigdata:map','fld'=>'flddata','tr'=>$bigId, 'keyid'=>$tranId,'class'=>'-fill'),$projectname,true,'textarea');
				$result = (object) array('orgId' => $orgId, 'bigid' => $bigId, 'projectname' => htmlspecialchars($projectname), 'editText' => $editText);
				return $result;
			}
			break;

		case 'project.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "project" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';
			}
			break;

		case 'mechanism.add':
			$ret .= 'Addd '.post('mechanism');
			if ($isEditable && $tranId && $mechanism = post('mechanism')) {
				$stmt = 'INSERT INTO %bigdata%
							(`keyname`, `keyid`, `fldname`, `flddata`, `ucreated`, `created`)
							VALUES
							("map", :keyid, "mechanism", :mechanism, :uid, :created)
							';
				mydb::query($stmt,':keyid', $tranId, ':mechanism',$mechanism, ':uid',i()->uid, ':created', date('U'));
				$bigid = mydb()->insert_id;
				$ret .= $mechanism.'<a hre="'.url('org/'.$orgId.'/mapping/mechanism.delete/'.$bigid).'"><i class="icon -remove -gray"></i></a>';
				//$ret .= mydb()->_query;
				//$ret .= __org_subject_list($orgInfo);
				return $ret;
			}

		case 'mechanism.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "mechanism" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';
			}
			break;

		case 'org.add':
			$ret .= 'Add '.post('orgname').'<br />';
			$result = (object) NULL;
			if ($isEditable && $tranId) {
				$refOrg = post('reforg');
				$orgName = post('orgname');
				if (empty($refOrg) && $orgName) {
					// Create new organization
					mydb::query('INSERT INTO %db_org% (`name`, `uid`, `created`) VALUES (:name, :uid, :created)', ':name', $orgName, ':uid',i()->uid, ':created', date('U'));
					//$ret .= mydb()->_query.'<br />';
					$refOrg = mydb()->insert_id;
				}
				if ($refOrg) {
					$stmt = 'INSERT INTO %bigdata%
								(`keyname`, `keyid`, `fldname`, `fldref`, `ucreated`, `created`)
								VALUES
								("map", :keyid, "orgid", :reforg, :uid, :created)
								';
					mydb::query($stmt,':keyid', $tranId, ':reforg',$refOrg, ':uid',i()->uid, ':created', date('U'));
					//$ret .= mydb()->_query.'<br />';

					$bigId = mydb()->insert_id;
					$ret .= $orgName.'<a hre="'.url('org/'.$orgId.'/mapping/org.delete/'.$bigId).'"><i class="icon -remove -gray"></i></a>';
					$result = (object) array('orgId' => $orgId, 'bigid' => $bigId, 'orgname' => htmlspecialchars($orgName));

					if ($concatMapWho) {
						__org_mapping_mergeorg($tranId, $concatMapWhoCount);
						$result->update = mydb()->_query;
					}
				}
				return $result;
			}
			break;

		case 'org.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$mapId = mydb::select('SELECT `keyid` FROM %bigdata% WHERE `bigid` = :tranId LIMIT 1',':tranId',$tranId)->keyid;
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "orgid" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';

				// UPDATE who in map_networks
				if ($concatMapWho)
					__org_mapping_mergeorg($mapId, $concatMapWhoCount);
			}
			break;

		case 'person.add':
			$ret .= 'Add '.post('fullname').'<br />';
			$result = (object) NULL;
			if ($isEditable && $tranId) {
				$psnId = post('psnid');
				$fullName = post('fullname');
				if (empty($psnId) && $fullName) {
					// Create new organization
					list($firstname,$lastname) = sg::explode_name(' ',$fullName);
					// Check duplicate name
					$dupPerson = mydb::select('SELECT `psnid` FROM %db_person% WHERE `name` = :name AND `lname` = :lname LIMIT 1', ':name', $firstname, ':lname', $lastname)->psnid;

					// If dupplicate name then use old, If not then create new 
					if ($dupPerson) {
						$psnId = $dupPerson;
					} else {
						mydb::query('INSERT INTO %db_person% (`name`, `lname`, `uid`, `created`) VALUES (:name, :lname, :uid, :created)', ':name', $firstname, ':lname',$lastname, ':uid',i()->uid, ':created', date('U'));
						//$ret .= mydb()->_query.'<br />';
						$psnId = mydb()->insert_id;
					}
				}
				if ($psnId) {
					$stmt = 'INSERT INTO %bigdata%
								(`keyname`, `keyid`, `fldname`, `fldref`, `ucreated`, `created`)
								VALUES
								("map", :keyid, "psnid", :psnid, :uid, :created)
								';
					mydb::query($stmt,':keyid', $tranId, ':psnid',$psnId, ':uid',i()->uid, ':created', date('U'));
					//$ret .= mydb()->_query.'<br />';

					$bigId = mydb()->insert_id;
					$ret .= $fullName.'<a hre="'.url('org/'.$orgId.'/mapping/person.delete/'.$bigId).'"><i class="icon -remove -gray"></i></a>';
					$result = (object) array('orgId' => $orgId, 'bigid' => $bigId, 'psnid' =>$psnId, 'fullname' => htmlspecialchars($fullName));
				}
				return $result;
			}
			break;

		case 'person.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "psnid" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';
			}
			break;

		case 'area.add':
			if ($isEditable && $tranId && $areacode = post('areacode')) {
				$address = SG\explode_address(post('address'), $areacode);
				$values['house'] = $address['house'];
				$values['village'] = $address['village'];
				$values['tambon'] = $address['tambonCode'];
				$values['ampur'] = $address['ampurCode'];
				$values['changwat'] = $address['changwatCode'];
				$fieldUpdate = 'house,village,tambon,ampur,changwat';

				$stmt = 'INSERT INTO %bigdata%
							(`keyname`, `keyid`, `fldname`, `fldref`, `flddata`, `ucreated`, `created`)
							VALUES
							("map", :keyid, "areacode", :areacode, :house, :uid, :created)
							';
				mydb::query($stmt,':keyid', $tranId, ':areacode',$areacode, ':house',$address['house'], ':uid',i()->uid, ':created', date('U'));
				$bigid = mydb()->insert_id;
				$result = (object) array('orgId' => $orgId, 'bigid' => $bigId, 'areacode' =>$areacode, 'address' => htmlspecialchars(SG\implode_address($address)));
				//$ret .= mydb()->_query;
				$result->values = $address;
				//$result->address = $address;
				return $result;
			}
			break;

		case 'area.delete':
			if ($isEditable && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :tranId AND `keyname` = "map" AND `fldname` = "areacode" LIMIT 1';
				mydb::query($stmt,':tranId', $tranId);
				$ret .= 'DELETED';
				//$ret .= mydb()->_query;
			}
			break;

		default:
			$ret = '';
			break;
	}

	return $ret;










	$ret .= '<form class="search-box" method="get" action="'.url('org/'.$orgId.'/mapping').'" style="margin: 8px 0;">';
	$ret .= '<input type="text" class="form-text -fill" name="qmap" placeholder="ป้อนชื่อองค์กรหรืองานต้องการค้นหา" /><button class="btn" type="submit" name="" value=""><i class="icon -search"></i></button>';
	$ret .= '</form>';


	mydb::where('m.`orgid` = :orgid', ':orgid', $orgId);
	if (post('qmap')) mydb::where('(n.`who` LIKE :qmap OR n.`dowhat` LIKE :qmap)', ':qmap', '%'.post('qmap').'%');
	$stmt = 'SELECT *
					FROM %map_name% m
						RIGHT JOIN %map_networks% n USING(`mapgroup`)
					%WHERE%;';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead = array('MapName','Who', 'Do What', 'cdate -date -hover-parent'=>'สร้าง
		');
	foreach ($dbs->items as $rs) {
		$ui = new Ui('span');
		$ui->add('<a class="sg-action" href="'.url('org/'.$orgId.'/mapping.view/'.$rs->mapid).'" data-rel="box" data-width="600"><i class="icon -view"></i></a>');
		$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
												$rs->mapname,
												$rs->who,
												$rs->dowhat,
												($rs->created ? sg_date($rs->created,'ว ดด ปปปป') : '')
												.$menu
											);
	}
	$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');
	return $ret;
}

function __org_mapping_mergeorg($mapId, $concatMapWhoCount = 1) {
	mydb::value('$LIMIT', $concatMapWhoCount);
	$stmt = 'UPDATE %map_networks% n
						LEFT JOIN (
						SELECT `mapid`, GROUP_CONCAT(c.`name` ORDER BY c.`bigid`) `name`
							FROM (
							SELECT b.`bigid`, b.`keyid` `mapid`, o.`name`
							FROM %bigdata% b
								LEFT JOIN %db_org% o ON o.`orgid` = b.`fldref`
							WHERE b.`keyname` = "map" AND b.`fldname` = "orgid" AND b.`keyid` = :mapid
							ORDER BY b.`bigid` ASC
							LIMIT $LIMIT
							) c
						) a USING(`mapid`)
						SET n.`who` = a.`name`
						WHERE n.`mapid` = :mapid';
	mydb::query($stmt, ':mapid', $mapId);
}
?>