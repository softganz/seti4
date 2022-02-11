<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_knet_host_create($self, $tpid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	if ($projectInfo->orgid) location('project/knet/'.$projectInfo->orgid);


	$isAdmin = user_access('administer projects');
	$isEdit = $isAdmin || ($projectInfo->RIGHT & _IS_EDITABLE);

	$ret = '';


	if ($isEdit) {
		$data = new stdClass;
		$data->name = $projectInfo->title;
		$data->sector = 2;
		$data->uid = i()->uid;

		$address = SG\explode_address($projectInfo->info->area, $projectInfo->info->areacode);

		$data->house = $address['house'];
		$data->zip = $address['zip'];
		$data->areacode = $projectInfo->info->areacode;
		$data->location = $projectInfo->info->lat && $projectInfo->info->lnt ? $projectInfo->info->lat.','.$projectInfo->info->lnt : NULL;
		$data->networktype = 1;
		$data->tpid = $tpid;
		$data->studentamt = $projectInfo->info->studentjoin;
		$data->managername = $projectInfo->info->prowner;
		$data->contactname = $projectInfo->info->prcoowner1;
		$data->created = date('U');

		$stmt = 'SELECT GROUP_CONCAT(`classlevel`) `classlevel`
						FROM (
							SELECT
								CASE
									WHEN SUBSTR(`sorder`,1,1) = 1 THEN "อนุบาล"
									WHEN SUBSTR(`sorder`,1,1) = 2 THEN "ประถม"
									WHEN SUBSTR(`sorder`,1,1) = 3 THEN "มัธยมต้น"
								END `classlevel`
							FROM `sgz_project_tr` 
							WHERE `tpid` = :tpid AND `formid` = "weight" AND `part` = "weight" AND `num1` > 0
							GROUP BY `classlevel`
						) a
						LIMIT 1';
		$classLevel = mydb::select($stmt,':tpid',$tpid)->classlevel;
		$data->classlevel = $classLevel ? $classLevel : NULL;
		//$ret .= $classLevel.'<br />';


		$stmt = 'INSERT INTO %db_org%
						(`name`, `sector`, `uid`, `house`, `areacode`, `managername`, `contactname`, `location`, `created`)
						VALUES
						(:name, :sector, :uid, :house, :areacode, :managername, :contactname, :location, :created)';
		mydb::query($stmt, $data);

		//$ret .= mydb()->_query.'<br />';

		if (!mydb()->error) {
			$data->orgid = $newOrgId = mydb()->insert_id;
			$stmt = 'UPDATE %topic% SET `orgid` = :orgid WHERE `tpid` = :tpid LIMIT 1';
			mydb::query($stmt,$data);
			//$ret .= mydb()->_query.'<br />';

			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) SELECT :orgid, `uid`, UPPER(`membership`) FROM %topic_user% WHERE `tpid` = :tpid';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query.'<br />';

			$stmt = 'INSERT INTO %school% (`orgid`, `uid`, `networktype`, `studentamt`, `classlevel`, `created`) VALUES (:orgid, :uid, :networktype, :studentamt, :classlevel, :created)';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query.'<br />';

			if (!is_null($self)) location('project/knet/'.$newOrgId);
		}
		//$ret .= print_o($data,'$data');
	} else {
		$ret .= $tpid.' NOT EDITABLE<br />';
	}
	//$ret .= print_o($projectInfo,'$projectInfo');

	return $ret;
}
?>