<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_knet_weight_get($tranId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$stmt = 'SELECT
					`trid`, `tpid`, `sorder`, `detail1` `year`
					, `detail2` `term`, `period`
					, `detail3` `area`
					, `detail4` `postby`
					, `date1` `dateinput`
					FROM %project_tr%
					WHERE `trid` = :trid
					LIMIT 1';

	$result = NULL;

	$rs = mydb::select($stmt,':trid',$tranId);

	//if ($rs->_empty) return $result;

	if ($rs->_num_rows) $rs->termperiod = $rs->term.':'.$rs->period;

	$result = mydb::clearprop($rs);


	// Get weight
	$stmt = 'SELECT
				  qt.`question`
				, qt.`qtgroup`
				, qt.`qtno`
				, tr.`parent`
				, tr.`part`
				, tr.`sorder`
				, tr.`num1` total
				, tr.`num2` getweight
				, tr.`num5` thin
				, tr.`num6` ratherthin
				, tr.`num7` willowy
				, tr.`num8` plump
				, tr.`num9` gettingfat
				, tr.`num10` fat
				, qt.`description`
			FROM %qt% qt
				LEFT JOIN %project_tr% tr
					ON tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
						AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
			WHERE `qtgroup`="schoolclass"
			ORDER BY `qtgroup` ASC, `qtno` ASC';
	$result->weight = mydb::select($stmt,':trid',$tranId,':formid',_KAMSAIINDICATOR)->items;

	// Get height
	$stmt = 'SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` getheight
					, tr.`num5` short
					, tr.`num6` rathershort
					, tr.`num7` standard
					, tr.`num8` ratherheight
					, tr.`num9` veryheight
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$result->height = mydb::select($stmt,':trid',$tranId,':formid',_INDICATORHEIGHT)->items;
	return $result;
}
?>