<?php
/**
* Create New Job
* Created 2018-03-01
* Modify  2019-12-23
*
* @param Object $shopId
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_garage_job_create($shopId, $data, $options = '{}') {
	if (empty($shopId)) return false;

	$defaults = '{value:"repairname",debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$tpid = false;
	if (is_array($data)) $data = (Object) $data;

	// Create member first project
	if ($data->jobno && $data->plate) {
		do {
			$isDup = mydb::select('SELECT `tpid` FROM %garage_job% WHERE `shopid` = :shopid AND `jobno` = :jobno LIMIT 1', ':shopid', $shopId, ':jobno', $data->jobno)->tpid;
			if ($debug) debugMsg('$isDup='.($isDup?'duplicate to topic '.$isDup:'not duplicate').'<br />'.mydb()->_query);
			if ($isDup) {
				$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = "job" LIMIT 1';
				mydb::query($stmt,':shopid', $shopId, ':lastno', $data->jobno);
				$data->jobno = R::Model('garage.nextno', $shopId, 'job');
			}
		} while ($isDup);

		if ($isDup || empty($data->jobno)) return false;

		if ($debug) debugMsg('<b>Create new job with jobno='.$data->jobno.'</b>');

		$data->plate = strtoupper($data->plate);

		$topic = new stdClass();
		$topic->tpid = NULL;
		$topic->revid = NULL;
		$topic->type = 'garage';
		$topic->status = _LOCK;
		$topic->orgid = empty($data->orgid)?NULL:$data->orgid;
		$topic->uid = i()->uid;
		$topic->title = $data->plate;
		$topic->created = $topic->timestamp = date('Y-m-d H:i:s');
		$topic->ip = ip2long(GetEnv('REMOTE_ADDR'));

		$stmt = 'INSERT INTO %topic% (`type`,`status`,`orgid`,`uid`,`title`,`created`,`ip`) VALUES (:type,:status,:orgid,:uid,:title,:created,:ip)';

		mydb::query($stmt,$topic);

		$querys[]= mydb()->_query;

		if (!mydb()->_error) {
			$tpid = $topic->tpid = mydb()->insert_id;

			// Create topic_revisions
			$stmt = 'INSERT INTO %topic_revisions% (`tpid`,`uid`,`title`,`timestamp`) VALUES (:tpid,:uid,:title,:timestamp)';

			mydb::query($stmt,$topic);

			$querys[] = mydb()->_query;

			// Update revid to topic
			$revid = $topic->revid = mydb()->insert_id;
			mydb::query('UPDATE %topic% SET `revid` = :revid WHERE `tpid` = :tpid LIMIT 1', $topic);

			$querys[] = mydb()->_query;

			// Create topic_user
			mydb::query('INSERT INTO %topic_user% (`tpid`,`uid`,`membership`) VALUES (:tpid,:uid,"OWNER")',$topic);

			$querys[] = mydb()->_query;

			// Create project
			$job = new stdClass();
			$job->tpid = $tpid;
			$job->shopid = $shopId;
			$job->jobno = $data->jobno;
			$job->cartype = SG\getFirst($data->cartype);
			$job->plate = $data->plate;
			$job->rcvdate = $data->rcvdate ? sg_date($data->rcvdate,'Y-m-d') : date('Y-m-d');
			$job->carindate = $job->rcvdate;
			$job->templateid = $data->templateid;
			$job->customerid = $data->customerid;
			$job->brandid = $data->brandid;
			$job->rcvby = SG\getFirst($data->rcvby);

			$stmt = 'INSERT INTO %garage_job%
				(`tpid`, `shopid`, `templateid`, `jobno`, `cartype`, `plate`, `rcvdate`, `carindate`, `customerid`, `brandid`, `rcvby`)
				VALUES
				(:tpid, :shopid, :templateid, :jobno, :cartype, :plate, :rcvdate, :carindate, :customerid, :brandid, :rcvby)';

			mydb::query($stmt,$job);

			$querys[] = mydb()->_query;

			// Update lastno
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = "job" LIMIT 1';
			mydb::query($stmt,':shopid', $shopId, ':lastno', $data->jobno);

			if ($data->trans) {
				foreach ($data->trans as $repairId => $damageCode) {
					$tran = new stdClass();
					$tran->repairid = $repairId;
					$tran->damagecode = $damageCode;
					R::Model('garage.job.tr.save', NULL, $job, $tran, '{debug: true}');
				}
			}
		}

	}
	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($topic,'$topic');
		debugMsg($job,'$job');
		debugMsg($querys,'$querys');
	}
	return $tpid;
}
?>