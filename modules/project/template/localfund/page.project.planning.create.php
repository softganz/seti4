<?php
/**
* Project :: Create New Planning
* Created 2018-09-11
* Modify  2021-05-17
*
* @return String
*
* @usage project/planning/create
*/

$debug = true;

class ProjectPlanningCreate extends Page {

	function build() {
		$orgId = post('oid');
		$sitid = post('sid');
		$year = post('yr');

		$fundInfo = R::Model('project.fund.get',$orgId);
		$isEdit = $fundInfo->is->edit || $fundInfo->is->trainer;

		if (!$fundInfo->orgId) return message('error','ไม่มีข้อมูลตามที่ระบุ');
		if (!$isEdit) return message('error', 'Access Denied');

		// Check have planning in year
		$stmt = 'SELECT p.`tpid`, t.`orgid`, t.`title`, p.`pryear`, tr.`refid`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid` = "info" AND `part` = "title"
			WHERE t.`orgid` = :orgid AND p.`pryear` = :pryear AND tr.`refid` = :refid
			ORDER BY p.`tpid` ASC
			LIMIT 1';

		$rs = mydb::select($stmt,':orgid',$fundInfo->orgid, ':pryear',$year, ':refid',$sitid);

		if ($rs->tpid) {
			location('project/planning/'.$rs->tpid);
		} else if ($sitid && $year) {
			$data = (Object) [];
			$data->pryear = $year;
			$data->group = $sitid;
			$data->orgid = $orgId;
			$data->title = mydb::select('SELECT `name` FROM %tag% WHERE `taggroup` = "project:planning" AND `catid` = :catid LIMIT 1',':catid',$data->group)->name;
			$data->title .= ' ปี '.($data->pryear+543).' '.$fundInfo->name;
			$data->prtype = 'แผนงาน';
			$data->changwat = $fundInfo->info->changwat;
			$data->ampur = $fundInfo->info->ampur;
			$data->tambon = $fundInfo->info->tambon;
			$data->areacode = $fundInfo->info->changwat.$fundInfo->info->ampur.$fundInfo->info->tambon;

			$result = R::Model('project.create', $data);
			$tpid = $result->tpid;

			// Create planning group
			$stmt = 'INSERT INTO %project_tr% (`tpid`,`refid`,`formid`,`part`,`uid`,`created`) VALUES (:tpid,:refid,"info","title",:uid,:created)';

			mydb::query($stmt,':tpid',$tpid, ':refid',$data->group, ':uid',i()->uid, ':created',date('U'));

			//$ret .= print_o($data,'$data');
			//$ret.=print_o($fundInfo);
			location('project/planning/'.$tpid);
		} else {
			location('project/planning/year/'.$orgId.'/'.$year);
		}

		//$ret.=print_o($fundInfo);

		return $ret;
	}
}
?>