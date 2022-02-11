<?php
/**
* Get Project Paid Document
* Created 2016-12-17
* Modify  2019-10-01
*
* @param Int $tpid
* @param Int $trid
* @param Mixed $cond
* @param Object $options
* @return Object
*/

$debug = true;

function r_project_paiddoc_get($tpid, $trid, $cond = NULL, $options = '{}') {
	$defaults = '{clearProp:true, getAllRecord:false, debug:false}';
	$options = sg_json_decode($options,$defaults);
	$result = new stdClass();

	if ($options->getAllRecord) {
		$stmt = 'SELECT
			  "PAID" `paidtype`
			, pd.`paidid`
			, pd.`tpid`
			, pd.`uid`
			, pd.`docno`
			, pd.`paiddate`
			, pd.`amount`
			, pd.`refcode`
			, pd.`created`
			FROM %project_paiddoc% pd
			WHERE pd.`tpid` = :tpid
			UNION
			-- Get Money Back Transaction
			SELECT 
			  "RET"
			, mb.`trid` `paidid`
			, mb.`tpid`
			, mb.`uid`
			, mb.`detail1` `docno`
			, mb.`date1` `paiddate`
			, - mb.`num1` `amount`
			, mb.`detail2` `refcode`
			, mb.`created`
			FROM %project_tr% mb
			WHERE mb.`tpid` = :tpid AND mb.`formid` = "info" AND mb.`part` = "moneyback"

			ORDER BY `paiddate` ASC, `paidid` ASC;
			-- {key:"paidid"}';

		$dbs = mydb::select($stmt,':tpid',$tpid);

		$result = $dbs->items;
	} else {
		mydb::where('p.`tpid` = :tpid', ':tpid', $tpid);
		if ($trid) mydb::where('p.`paidid` = :trid', ':trid', $trid);
		$stmt = 'SELECT p.*, t.`orgid`
			FROM %project_paiddoc% p
				LEFT JOIN %topic% t USING(`tpid`)
			%WHERE%
			ORDER BY `paiddate` ASC
			'.(empty($trid) || $options->getAllRecord ? '; -- {key:"paidid"}' : 'LIMIT 1');

		$rs = mydb::select($stmt);

		if ($rs->_num_rows) {
			$result = $options->clearProp ? mydb::clearprop($rs) : $rs;
		} else {
			$result = NULL;
		}
	}

	if ($options->debug) {
		debugMsg($options,'$options');
		debugMsg($rs,'$rs');
		debugMsg($dbs,'$dbs');
	}
	return $result;
}
?>