<?php
/**
* Project :: Repair Valuation
* Created 2021-04-11
* Modify  2021-04-11
*
* @param Object $self
* @return String
*
* @usage project/admin/repair/valuation
*/

$debug = true;

function project_admin_repair_valuation($self) {
	// Data Model
	$partMatch = [
		'5.1.1' => 'inno.1',
		'5.1.2' => 'inno.2',
		'5.1.3' => 'inno.3',
		'5.1.4' => 'inno.4',
		'5.1.5' => 'inno.5',
		'5.1.6' => 'inno.6',
		'5.1.7' => 'inno.99',

		'5.2.1' => 'behavior.1',
		'5.2.2' => 'behavior.2',
		'5.2.3' => 'behavior.3',
		'5.2.4' => 'behavior.4',
		'5.2.5' => 'behavior.5',
		'5.2.6' => 'behavior.6',
		'5.2.7' => 'behavior.7',
		'5.2.8' => 'behavior.8',
		'5.2.9' => 'behavior.9',
		'5.3.1' => 'environment.1',
		'5.3.2' => 'environment.2',
		'5.3.3' => 'environment.3',
		'5.3.4' => 'environment.4',
		'5.3.5' => 'environment.9',

		'5.4.1' => 'publicpolicy.1',
		'5.4.2' => 'publicpolicy.2',
		'5.4.3' => 'publicpolicy.3',
		'5.4.4' => 'publicpolicy.4',
		'5.4.5' => 'publicpolicy.5',
		'5.4.6' => 'publicpolicy.6',

		'5.5.1' => 'social.1',
		'5.5.2' => 'social.2',
		'5.5.3' => 'social.3',
		'5.5.4' => 'social.4',
		'5.5.5' => 'social.5',
		'5.5.6' => 'social.6',
		'5.5.7' => 'social.7',

		'5.6.1' => 'spirite.1',
		'5.6.2' => 'spirite.2',
		'5.6.3' => 'spirite.3',
		'5.6.4' => 'spirite.4',
		'5.6.5' => 'spirite.5',
		'5.6.6' => 'spirite.6',
	];


	// View Model
	new Toolbar($self, 'Repair Valuation');

	$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/admin/repair/valuation').'" data-rel="#main" data-title="ซ่อมแซม" data-confirm="กรุณายืนยัน?">START REPAIR VALUATION</a></nav>';

	if (SG\confirm()) {
		foreach ($partMatch as $oldPart => $newPart) {
			mydb::query('UPDATE %project_tr%
				SET `part` = :newPart
				WHERE `formid` IN ("ประเมิน", "valuation") AND `part` = :oldPart',
				':oldPart', $oldPart, ':newPart', $newPart
			);

			$ret .= mydb()->_query.'<br />';
		}
	}


	$stmt = 'SELECT v.`tpid`, t.`title`, v.`formid`, COUNT(*) `total`
		FROM %project_tr% v
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE v.`formid` IN ("ประเมิน", "valuation") AND v.`part` LIKE "5.%"
		GROUP BY `tpid`
		ORDER BY v.`formid` ASC';

	$dbs = mydb::select($stmt);

	//$ret .= mydb()->_query;

	$ret .= mydb::printtable($dbs);

	return $ret;
}
?>