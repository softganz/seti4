<?php
function view_calendar_create_month($data) {
	$sql_cmd = 'SELECT t.tpid,t.title,c.*,TO_DAYS(to_date)-TO_DAYS(from_date) as day_repeat ';
	$sql_cmd .= ' , u.username,u.name as owner_name ';
	if ($is_category) $sql_cmd .= ' , cat.category_shortname , cat.category_name ';
	$sql_cmd .= '  FROM %ical% AS c ';
	$sql_cmd .= '  LEFT JOIN %topic% t ON t.tpid=c.tpid ';
	$sql_cmd .= '  LEFT JOIN %users% u ON t.uid=u.uid ';
	if ($is_category) $sql_cmd .= '  LEFT JOIN %calendar_cat% AS cat ON c.category=cat.category_id ';
	$where=array();
	if (isset($para->category)) $where[]='(c.category=\''.$para->category.'\')';
	if (isset($para->owner)) $where[]='(c.owner=\''.$para->owner.'\')';
	if (isset($para->month)) {
		list($year,$month)=explode('-',$para->month);
		$where[]='( (MONTH(c.from_date)='.$month.' and YEAR(c.from_date)='.$year.') or (MONTH(c.to_date)='.$month.' and YEAR(c.to_date)='.$year.') )';
//			or (c.from_date<"'.$start_date.'" and c.to_date>="'.$start_date.'"))';
	}
	if (isset($para->date)) $where[]='(c.from_date<=\''.$para->date.'\' and c.to_date>=\''.$para->date.'\')';
	if (isset($para->from)) $where[]='(c.from_date>=\''.$para->from.'\')';
	if (isset($para->to)) $where[]='(c.to_date<=\''.$para->to.'\')';
	if (!$user->ok) {
		// get only public privacy
		$where[]='(c.privacy="public")';
	} else if (user_access('administer contents')) {
		// get all privacy
	} else {
		// get owner and public privacy
		foreach ($user->roles as $role) if (!in_array($role,array('admin','member'))) $urole=$role;
		if ($urole) {
			$ruser=db_query_object('SELECT uid FROM %users% WHERE roles LIKE "%'.$urole.'%"');
			if ($ruser->lists->text) $role_sql='(c.privacy="group" and c.owner in ('.$ruser->lists->text.'))';
		}
		$where[]='(c.privacy="public" or c.owner='.$user->uid.''.($role_sql?' or '.$role_sql:'').')';
	}
	if ($where) $sql_cmd .= 'WHERE '.implode(' and ',$where);
	
	$sql_cmd .= ' ORDER BY '.(isset($para->order)?$para->order:$this->order).' '.(isset($para->sort)?$para->sort:$this->sort);
	$source_rs=mydb::select($sql_cmd);
	$result=array();
	if ($para->date) {
		$result=$source_rs;
	} else {
		foreach ($source_rs->items as $value) {
			if ($value->day_repeat) {
				list($year,$month,$date)=explode('-',$value->from_date);
				for ($i=0;$i<=$value->day_repeat;$i++) {
					$calendar_date=getdate(mktime(0,0,0,$month,$date+$i,$year));
					$result[$calendar_date['year'].'-'.sprintf('%02d',$calendar_date['mon']).'-'.sprintf('%02d',$calendar_date['mday'])][]=$value;
				}
			} else {
				$result[$value->from_date][]=$value;
			}
		}
	}
	if (debug('sql')) echo db_query_cmd();

	return $result;
}
?>