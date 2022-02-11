<?php
function r_project_gl_tran_get($refcode = NULL, $pglid = NULL) {
	if (empty($refcode) && $pglid) {
		$refcode=mydb::select('SELECT `refcode` FROM %project_gl% WHERE `pglid` = :pglid LIMIT 1',':pglid',$pglid)->refcode;
	}
	//debugMsg('Get GL Transaction '.$refcode);

	$result = NULL;

	$stmt = 'SELECT tr.* , g.`glname`, cu.`name` `createName`, mu.`name` `modifyName`
		FROM %project_gl% tr
			LEFT JOIN %glcode% g USING(`glcode`)
			LEFT JOIN %users% cu USING(`uid`)
			LEFT JOIN %users% mu ON mu.`uid` = tr.`modifyby`
		WHERE `refcode` = :refcode;';

	$dbs = mydb::select($stmt, ':refcode', $refcode);
	//debugMsg($dbs,'$dbs');

	if ($dbs->_num_rows) {
		$result = new stdClass();
		$rs = $dbs->items[0];
		$result->orgid = $rs->orgid;
		$result->refcode = $rs->refcode;
		$result->refdate = $rs->refdate;
		$result->tpid = $rs->tpid;
		$result->actid = $rs->actid;
		$result->uid = $rs->uid;
		$result->closed = $rs->closed;
		$result->totalDr = 0;
		$result->totalCr = 0;
		$result->glCodes = '';
		$result->created = $rs->created;
		$result->modified = $rs->modified;
		$result->modifyby = $rs->modifyby;
		$result->createName = $rs->createName;
		$result->modifyName = $rs->modifyName;

		$result->items = array();
		foreach ($dbs->items as $rs) {
			$result->items[] = (object) array(
				'pglid' => $rs->pglid,
				'refcode' => $rs->refcode,
				'glcode' => $rs->glcode,
				'glname' => $rs->glname,
				'amount' => $rs->amount,
			);

			if ($rs->amount >= 0) $result->totalDr += $rs->amount;
			if ($rs->amount < 0) $result->totalCr += abs($rs->amount);
			$result->glCodes .= $rs->glcode.',';
		}
		$result->glCodes = trim($result->glCodes, ',');
	}
	return $result;
}
?>