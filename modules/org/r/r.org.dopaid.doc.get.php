<?php
/**
* Get doing information
* Created 2019-06-01
* Modify  2019-07-28
*
* @param Intefer $doid
* @return Object $options
* @return Object Record Set
*/

$debug = true;

function r_org_dopaid_doc_get($dopid, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$stmt = 'SELECT
				d.*
				, do.`tpid`
				, do.`calid`
				, CONCAT(p.`prename`," ",p.`name`," ",p.`lname`) `fullname`
				, p.`cid`
				, do.`paiddocfrom`
				, do.`paiddoctagid`
				, do.`paiddocbyname`
				, do.`options`
		FROM %org_dopaid% d
			LEFT JOIN %org_doings% do USING(`doid`)
			LEFT JOIN %db_person% p USING(`psnid`)
		WHERE d.`dopid` = :dopid LIMIT 1';
	$rs = mydb::select($stmt, ':dopid', $dopid);

	$doOptions = sg_json_decode($rs->options);

	if ($debug) debugMsg($doOptions,'$doOptions');

	if ($rs->formid) {
		$rs->docText = $doOptions->rcvForms->{$rs->formid};
	}

	unset($rs->options);
	$rs->options = $doOptions;

	$stmt = 'SELECT
						d.*
						, tg.`name`
					FROM %org_dopaidtr% d
						LEFT JOIN %tag% tg ON tg.`taggroup` = "project:expcode" AND tg.`catid` = d.`catid`
					WHERE  d.`dopid` = :dopid
					ORDER BY d.`doptrid` ASC;
					-- {key: "doptrid"}
					';
	$dbs = mydb::select($stmt, ':dopid', $dopid);


	$rs->_member_query = $dbs->_query;
	$rs->_member_error = $dbs->_error;
	$rs->_member_error_msg = $dbs->_error_msg;

	$rs->trans = $dbs->items;

	return $rs;
}
?>