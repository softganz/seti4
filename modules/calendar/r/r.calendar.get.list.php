<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_calendar_get_list() {
	$para = para(func_get_args());

	$is_category = mydb::table_exists('%calendar_category%');


	if ($para->get == '*') {
		// Get all calendar items
	} else if (substr($para->get,0,1) == '*') {
		// Get all calendar items + other condition
		$conditions = array();
		$conditions[] = 'c.`tpid` IS NULL';		
		foreach (explode(',', $para->get) AS $value) {
			if (!preg_match('/(^[a-z])\:(.*)/', $value, $out)) continue;
			if ($out[1] == 't') {
				$conditions[] = 't.`tpid` IN (:tpid)';
				mydb::where(NULL,':tpid', 'SET:'.str_replace(':', ',',$out[2]));
			} else if ($out[1] == 'o') {
				$conditions[] = 't.`orgid` IN (:orgid)';
				mydb::where(NULL,':orgid', 'SET:'.str_replace(':', ',',$out[2]));
			}
		}
		mydb::where('('.implode(' OR ',$conditions).')',$whereValue);
	} else if ($para->orgid) {
		mydb::where('(c.`orgid` = :orgid OR t.`orgid` = :orgid)', ':orgid',$para->orgid);
	} else if ($para->tpid == '*') {
		mydb::where('c.`tpid` IS NOT NULL');
	} else if ($para->tpid) {
		mydb::where('c.`tpid` IN (:tpid)', ':tpid', 'SET:'.$para->tpid);
	} else {
		mydb::where('c.`tpid` IS NULL');		
	}

	if ($para->category) mydb::where('c.`category` = :category', ':category', $para->category);
	if ($para->owner) mydb::where('c.`owner` =: owner', ':owner', $para->owner);
	if ($para->u) mydb::where('c.`owner` IN (:userlist)', ':userlist','SET:'.$para->u);

	if ($para->getMonth) {
		list($year,$month) = explode('-', $para->getMonth);
		mydb::where('( (MONTH(c.`from_date`) = :month AND YEAR(c.`from_date`) = :year) OR (MONTH(c.`to_date`) = :month AND YEAR(c.`to_date`) = :year) )',':month',$month, ':year',$year);
	} else if ($para->date) {
		mydb::where('c.`from_date` <= :date AND c.`to_date` >= :date', ':date', $para->date);
	}
	if ($para->from) mydb::where('c.`from_date` >= :fromdate', ':fromdate', $para->from);
	if ($para->to) mydb::where('c.`to_date` <= :todate', ':todate', $para->to);

	//if ($para->main) mydb::where('c.`tpid` IS NULL');

	if (!i()->ok) {
		// get only public privacy
		mydb::where('c.`privacy` = "public"');
	} else if (user_access('administer contents')) {
		// get all privacy
	} else {
		// get owner and public privacy
		foreach (i()->roles as $role)
			if (!in_array($role,array('admin','member'))) $urole=$role;
		if ($urole) {
			$ruser = mydb::select('SELECT `uid` FROM %users% WHERE `roles` LIKE :urole; -- {reset: false}',':urole','%'.$urole.'%');
			if ($ruser->lists->text)
				$role_sql = '(c.`privacy` = "group" AND c.`owner` IN ('.$ruser->lists->text.'))';
		}
		mydb::where('(c.`privacy` = "public" OR c.`owner` = :myuid)'.($role_sql ? ' OR '.$role_sql:''),':myuid', i()->uid);
	}

	mydb::value('$CATFIELD$', $is_category ? ' , cat.`category_shortname` , cat.`category_name` ' : '');
	mydb::value('$CATJOIN$', $is_category ? '  LEFT JOIN %calendar_category% AS cat ON c.`category` = cat.`category_id` ' : '');
	mydb::value('$ORDER$', $para->order ? $para->order : 'c.`from_date` ASC');

	$stmt = 'SELECT
			c.*
		, TO_DAYS(`to_date`) - TO_DAYS(`from_date`) `day_repeat`
		, t.`title` `topicTitle`
		, u.`username`
		, u.`name` `owner_name`
		$CATFIELD$
		FROM %calendar% c
		LEFT JOIN %topic% t USING(`tpid`)
		LEFT JOIN %users% u ON c.`owner` = u.`uid`
		$CATJOIN$
		%WHERE%
		ORDER BY $ORDER$
		';

	$dbs = mydb::select($stmt);
	//debugMsg('<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>');
	//print_o($para,'$para',1);
	//debugMsg($dbs,'$dbs');
	//return;

	//debugMsg('<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>');

	foreach ($dbs->items as $key => $rs) {
		$dbs->items[$key]->options = json_decode($rs->options);
	}

	$result = array();
	if ($para->date) {
		$result = $dbs;
	} else {
		foreach ($dbs->items as $value) {
			$value = (array) $value;
			if ($value['day_repeat']) {
				list($year,$month,$date) = explode('-', $value['from_date']);
				for ($i = 0; $i <= $value['day_repeat']; $i++) {
					$calendar_date = getdate(mktime(0,0,0,$month,$date+$i,$year));
					$result[$calendar_date['year'].'-'.sprintf('%02d',$calendar_date['mon']).'-'.sprintf('%02d',$calendar_date['mday'])][] = $value;
				}
			} else {
				$result[$value['from_date']][] = $value;
			}
		}
	}

	//debugMsg($dbs,'$dbs');

	if (debug('sql')) debugMsg(mydb()->_query);

	return $result;
}
?>