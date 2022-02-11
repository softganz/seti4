<?php
/**
* Create Project Paid Document
* Created 2018-08-06
* Modify  2019-10-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_paiddoc_create($projectInfo, $data, $options = '{}') {
	$defaults = '{debug:false}';
	$options = sg_json_decode(options('project'),$defaults);
	$debug = $options->debug;

	if ($debug) debugMsg($options,'$options');

	if ($data->docno && $data->paiddate && $data->amount) {
		$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);

		$currentRefCode = $data->refcode;

		// Get last paiddoc information
		$lastPaidDoc = new stdClass();

		$stmt = 'SELECT `paidid`,`tpid`
			FROM %project_paiddoc% pi
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE t.`orgid` = :orgid AND `positionnayok` IS NOT NULL AND `positionnayok` != ""
			ORDER BY `paidid` DESC
			LIMIT 1';
		$lastPaidDocId = mydb::select($stmt,':orgid',$projectInfo->orgid);

		if ($debug) debugMsg($lastPaidDocId,'$lastPaidDocId');
		if ($debug) debugMsg(mydb()->_query);

		if ($lastPaidDocId->_num_rows) {
			$lastPaidDoc = R::Model('project.paiddoc.get',$lastPaidDocId->tpid,$lastPaidDocId->paidid);
		}

		$fundBalance = R::Model('project.fund.gl.balance', $fundInfo);

		if (empty($data->paidid)) $data->paidid = NULL;

		$actionMsg = $data->paidid ? 'Edit' : 'Add';

		$data->tpid = $projectInfo->tpid;
		$data->uid = i()->uid;
		$data->paiddate = sg_date($data->paiddate,'Y-m-d');
		$data->amount = abs(sg_strip_money($data->amount));
		$data->refcode = $data->refcode ? $data->refcode : R::Model('project.gl.getnextref','PAY',true);
		$data->fundbalance = $fundBalance - $data->amount;
		$data->namenayok = $lastPaidDoc->namenayok;
		$data->positionnayok = $lastPaidDoc->positionnayok;
		$data->namewithdraw = $lastPaidDoc->namewithdraw;
		$data->positionwithdraw = $lastPaidDoc->positionwithdraw;
		$data->namereceive = empty($projectInfo->info->orgnamedo) ? '' : $projectInfo->info->orgnamedo; //empty($projectInfo->info->prowner)?'':$projectInfo->info->prowner;
		$data->nameproof = $lastPaidDoc->nameproof;
		$data->positionproof = $lastPaidDoc->positionproof;
		$data->namefinance = $lastPaidDoc->namefinance;
		$data->positionfinance = $lastPaidDoc->positionfinance;
		$data->namepalad = $lastPaidDoc->namepalad;
		$data->positionpalad = $lastPaidDoc->positionpalad;
		$data->namesign1 = $lastPaidDoc->namesign1;
		$data->positionsign1 = $lastPaidDoc->positionsign1;
		$data->namesign2 = $lastPaidDoc->namesign2;
		$data->positionsign2 = $lastPaidDoc->positionsign2;
		$data->namepaid = $lastPaidDoc->namepaid;
		$data->positionpaid = $lastPaidDoc->positionpaid;
		$data->created = date('U');

		foreach ($data as $key => $value) {
			if ($key == 'paidid') continue;
			if (is_null($value)) $data->{$key} = '';
		}

		$stmt='INSERT INTO %project_paiddoc% (
			  `paidid`, `tpid`, `uid`
			, `paiddate`, `amount`, `docno`, `refcode`
			, `fundbalance`
			, `namenayok`
			, `positionnayok`
			, `namewithdraw`
			, `positionwithdraw`
			, `namereceive`
			, `nameproof`
			, `positionproof`
			, `namefinance`
			, `positionfinance`
			, `namepalad`
			, `positionpalad`
			, `namesign1`
			, `positionsign1`
			, `namesign2`
			, `positionsign2`
			, `namepaid`
			, `positionpaid`
			, `created`
			) VALUES (
			  :paidid, :tpid, :uid
			, :paiddate, :amount, :docno, :refcode
			, :fundbalance
			, :namenayok
			, :positionnayok
			, :namewithdraw
			, :positionwithdraw
			, :namereceive
			, :nameproof
			, :positionproof
			, :namefinance
			, :positionfinance
			, :namepalad
			, :positionpalad
			, :namesign1
			, :positionsign1
			, :namesign2
			, :positionsign2
			, :namepaid
			, :positionpaid
			, :created
			) ON DUPLICATE KEY
			UPDATE
			  `amount` = :amount
			, `paiddate` = :paiddate
			, `docno` = :docno';

		mydb::query($stmt,$data);

		if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');
		if (mydb()->_error) return false;

		if (empty($data->paidid)) {
			$data->paidid = mydb()->insert_id;
		}



		if ($currentRefCode) $glTran = R::Model('project.gl.tran.get',$currentRefCode);


		if ($debug) debugMsg($data,'$data');



		// Greate GL Trabsaction
		$glExpenseCode = '50'.$projectInfo->info->supporttype.'00';
		$gldata->tpid = $projectInfo->tpid;
		$gldata->orgid = $projectInfo->orgid;
		$gldata->actid = $data->paidid;
		$gldata->refcode = $data->refcode;
		$gldata->refdate = $data->paiddate;

		$gldata->items = array(
			array(
				'pglid' => $glTran->items[0]->pglid,
				'glcode' => $glExpenseCode,
				'amount' => $data->amount
			),
			array(
				'pglid' => $glTran->items[1]->pglid,
				'glcode' => $options->bankcode,
				'amount' => -($data->amount)
			),
		);

		if ($debug) debugMsg($gldata,'$gldata');

		$glidResult = R::Model('project.gl.tran.add',$gldata);

		// Trick firebase update
		$firebase = new Firebase('sg-project-man','update');
		$firedata = array(
			'tpid' => $projectInfo->tpid,
			'tags' => 'Project Paiddoc Create',
			'value' => $data->refcode.' Amount '.$data->amount.' THB',
			'url' => _DOMAIN.url('project/'.$projectInfo->tpid.'/info.paiddoc/'.$data->paidid),
			'time' => array('.sv'=>'timestamp')
		);
		$firebase->post($firedata);


		if ($debug) debugMsg('$glExpenseCode='.$glExpenseCode);
		if ($debug) debugMsg($glidResult,'$glidResult');

		R::Model('watchdog.log','project','Paid Doc '.$actionMsg,'Project id '.$projectInfo->tpid.' - Tran '.$data->paidid.'/'.$data->refcode.' Amount '.$data->amount.' by '.i()->name.'('.i()->uid.')', NULL, $projectInfo->tpid);

		R::Model('project.nhso.obt.update', $fundInfo, $data->paiddate);
		
		return $data->paidid;
	}
}
?>