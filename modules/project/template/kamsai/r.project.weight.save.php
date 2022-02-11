<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_weight_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	/*
	if (!($tpid && $data->year && $data->termperiod && $data->postby && $data->dateinput)) {
		return message('error','ข้อมูลไม่ครบถ้วน');
	} else if (__project_form_weight_duplicate($tpid,$data->trid,$data->year,$data->termperiod)) {
		return message('error','ข้อมูลของปีการศึกษา '.($data->year+543)." ภาคการศึกษานี้ ได้มีการบันทึกข้อมูลไว้แล้ว ไม่สามารถบันทึกซ้ำได้!!!");
	};
	*/

	$data->tpid = SG\getFirst($data->tpid);
	$data->orgid = SG\getFirst($data->orgid);
	$data->trid = SG\getFirst($data->trid);
	$data->formid = _KAMSAIINDICATOR;
	list($data->term, $data->period) = explode(':', $data->termperiod);
	$data->uid = i()->uid;
	$data->dateinput = sg_date($data->dateinput,'Y-m-d 00:00:00');
	$data->order = mydb::select('SELECT MAX(`sorder`) `maxorder` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :formid AND `part` = "title" LIMIT 1',':tpid',$tpid,':formid',_KAMSAIINDICATOR)->maxorder+1;
	if ($debug) debugMsg(mydb()->_query);

	$data->created = date('U');


	$stmt = 'INSERT INTO %project_tr%
					(
					`trid`, `tpid`, `orgid`, `uid`, `formid`, `part`, `sorder`
					, `detail1`, `detail2`, `period`, `detail4` , `date1`
					, `created`
					)
					VALUES
					(
					:trid, :tpid, :orgid, :uid, :formid, "title", :order
					, :year, :term, :period, :postby, :dateinput
					, :created
					)
					ON DUPLICATE KEY UPDATE 
					`detail1` = :year
					, `detail2` = :term
					, `period` = :period
					, `detail4` = :postby
					, `date1` = :dateinput';

	mydb::query($stmt,$data);

	if (!$data->trid) $data->trid = mydb()->insert_id;
	if ($debug) debugMsg(mydb()->_query);

	$weightTrans = array();
	$stmt='SELECT `trid`,`sorder`,`part`
					FROM %project_tr%
					WHERE `parent` = :trid
						AND `formid` = :formid AND `part` = :formid
					ORDER BY `sorder` ASC';
	foreach (mydb::select($stmt, ':trid', $data->trid, ':formid', _KAMSAIINDICATOR)->items as $item) {
		$weightTrans[$item->sorder]=$item->trid;
	}
	if ($debug) debugMsg(mydb()->_query);
	if ($debug) debugMsg($weightTrans,'$weightTrans');

	foreach ($data->weight as $qtno => $qtarray) {
		unset($qtvalue);
		$qtvalue->trid=$weightTrans[$qtno];
		$qtvalue->tpid = $data->tpid;
		$qtvalue->orgid = $data->orgid;
		$qtvalue->parent = $data->trid;
		$qtvalue->uid=i()->uid;
		$qtvalue->sorder=$qtno;
		$qtvalue->formid=_KAMSAIINDICATOR;
		$qtvalue->part=_KAMSAIINDICATOR;
		$qtvalue->total=$qtarray['total'];
		$qtvalue->getweight=$qtarray['thin']+$qtarray['ratherthin']+$qtarray['willowy']+$qtarray['plump']+$qtarray['gettingfat']+$qtarray['fat'];
		$qtvalue->choice1=$qtarray['thin'];
		$qtvalue->choice2=$qtarray['ratherthin'];
		$qtvalue->choice3=$qtarray['willowy'];
		$qtvalue->choice4=$qtarray['plump'];
		$qtvalue->choice5=$qtarray['gettingfat'];
		$qtvalue->choice6=$qtarray['fat'];
		$qtvalue->created=date('U');
		$stmt='INSERT INTO %project_tr%
					(
						`trid`, `tpid`, `orgid`, `parent`, `uid`, `sorder`, `formid`, `part`,
						`num1`, `num2`, `num5`, `num6`, `num7`, `num8`, `num9`, `num10`, `created`
					)
					VALUES
					(
						:trid, :tpid, :orgid, :parent, :uid, :sorder, :formid, :part,
						:total, :getweight, :choice1, :choice2, :choice3, :choice4, :choice5, :choice6, :created
					)
					ON DUPLICATE KEY UPDATE
						`num1`=:total, `num2`=:getweight,
						`num5`=:choice1, `num6`=:choice2, `num7`=:choice3,
						`num8`=:choice4, `num9`=:choice5, `num10`=:choice6';
		mydb::query($stmt,$qtvalue);
		if ($debug) debugMsg(mydb()->_query);
	}

	$heightTrans=array();
	$stmt='SELECT `trid`,`sorder`,`part`
					FROM %project_tr%
					WHERE `parent` = :trid
						AND `formid` = :formid AND `part` = :formid
					ORDER BY `sorder` ASC';
	foreach (mydb::select($stmt,':tpid',$tpid, ':trid',$data->trid,':formid',_INDICATORHEIGHT)->items as $item) {
		$heightTrans[$item->sorder]=$item->trid;
	}

	if ($debug) debugMsg(mydb()->_query);
	if ($debug) debugMsg($heightTrans,'$heightTrans');

	foreach ($data->height as $qtno => $qtarray) {
		unset($qtvalue);
		$qtvalue->trid=$heightTrans[$qtno];
		$qtvalue->tpid = $data->tpid;
		$qtvalue->orgid = $data->orgid;
		$qtvalue->parent=$data->trid;
		$qtvalue->uid=i()->uid;
		$qtvalue->sorder=$qtno;
		$qtvalue->formid=_INDICATORHEIGHT;
		$qtvalue->part=_INDICATORHEIGHT;
		$qtvalue->total=$qtarray['total'];
		$qtvalue->getheight=$qtarray['short']+$qtarray['rathershort']+$qtarray['standard']+$qtarray['ratherheight']+$qtarray['veryheight'];
		$qtvalue->choice1=$qtarray['short'];
		$qtvalue->choice2=$qtarray['rathershort'];
		$qtvalue->choice3=$qtarray['standard'];
		$qtvalue->choice4=$qtarray['ratherheight'];
		$qtvalue->choice5=$qtarray['veryheight'];
		$qtvalue->created=date('U');
		$stmt='INSERT INTO %project_tr%
					(
						`trid`, `tpid`, `orgid`, `parent`, `uid`, `sorder`, `formid`, `part`,
						`num1`, `num2`, `num5`, `num6`, `num7`, `num8`, `num9`, `created`
					)
					VALUES
					(
						:trid, :tpid, :orgid, :parent, :uid, :sorder, :formid, :part,
						:total, :getheight, :choice1, :choice2, :choice3, :choice4, :choice5, :created
					)
					ON DUPLICATE KEY UPDATE
						`num1`=:total, `num2`=:getheight,
						`num5`=:choice1, `num6`=:choice2, `num7`=:choice3,
						`num8`=:choice4, `num9`=:choice5';
		mydb::query($stmt,$qtvalue);
		if ($debug) debugMsg(mydb()->_query);
	}

	return $result;
}
?>