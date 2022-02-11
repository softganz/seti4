<?php
/**
* Project :: Create New Planning
* Created 2018-09-11
* Modify  2021-08-17
*
* @return Widget
*
* @usage project/planning/create
*/

$debug = true;

class ProjectPlanningCreate extends Page {

	function build() {
		$data = (Object) post('data');
		// $sitid = post('sid');
		// $year = post('year');

		if ($data->orgid) {
			$orgInfo = R::Model('project.org.get', $data->orgid);
			print_o($orgInfo, '$orgInfo',1);
			if (!$orgInfo) {
				return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ไม่มีข้อมูลองค์กรตามที่ระบุ']);
			} else if (!($orgInfo->info->isEdit || is_admin('project') || (i()->ok && isset($orgInfo->officers[i()->uid]) && $orgInfo->officers[i()->uid] === 'OFFICER'))) {
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied:ท่านไม่ได้เป็นเจ้าหน้าที่ขององค์กร']);
			}
		} else if (!user_access('create project planning')) {
			return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied:ท่านไม่ได้เป็นเจ้าหน้าที่ขององค์กร']);
		}

		// R::View('project.toolbar',$self,'แผนงาน - '.$orgInfo->name,'org',$orgInfo);

		// Check have planning in year
		// $stmt = 'SELECT p.`tpid`, t.`orgid`, t.`title`, p.`pryear`, tr.`refid`
		// 	FROM %project% p
		// 		LEFT JOIN %topic% t USING(`tpid`)
		// 		LEFT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid` = "info" AND `part` = "title"
		// 	WHERE t.`orgid` = :orgid AND p.`pryear` = :pryear AND tr.`refid` = :refid
		// 	ORDER BY p.`tpid` ASC
		// 	LIMIT 1';

		// $rs = mydb::select($stmt,':orgid',$orgInfo->orgid, ':pryear',$year, ':refid',$sitid);

		$data->prtype = 'แผนงาน';
		$data->ischild = 1;
		$data->issue = SG\getFirst($data->issue);

		if (!$data->title && $data->issue) {
			$data->title = mydb::select('SELECT `name` FROM %tag% WHERE `taggroup` = "project:planning" AND `catid` = :catid LIMIT 1',':catid',$data->issue)->name;
			$data->title .= ' ปี '.($data->pryear+543).' '.$orgInfo->name;
		}

		$result = R::Model('project.create', $data);

		// Create planning group
		if ($data->projectId = $result->tpid) {
			$data->belowplanname = SG\getFirst($data->belowplanname);
			$data->uid = i()->uid;
			$data->created = date('U');

			$stmt = 'INSERT INTO %project_tr%
				(`tpid`, `refid`, `formid`, `part`, `uid`, `detail1`, `created`)
				VALUES
				(:projectId, :issue, "info", "title", :uid, :belowplanname, :created)';

			mydb::query($stmt, $data);
		}

		// debugMsg($result,'$result');
		// debugMsg($data,'$data');
		// debugMsg(post(),'post()');
		// debugMsg($orgInfo, '$orgInfo');

		return $ret;
	}
}
?>