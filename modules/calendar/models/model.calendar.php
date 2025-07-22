<?php
/**
* Calendar:: Calendar Model
* Created :: 2023-01-15
* Modify  :: 2025-07-19
* Version :: 2
*
* @param Array $args
* @return Object
*
* @usage new CalendarModel([])
* @usage CalendarModel::function($conditions, $options)
*/

use Softganz\DB;

class CalendarModel {
	function __construct($args = []) {
	}

	public static function getById($calendarId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = DB::select([
			'SELECT
			`calendar`.`id` `calId`
			, `calendar`.`id` `calid`
			, `calendar`.`tpid` `nodeId`
			, `calendar`.*
			, `topic`.`title` topic_title
			, `topic`.`type` `topicType`
			, `user`.`username`, `user`.`name` as owner_name
			FROM %calendar% AS `calendar`
				LEFT JOIN %topic% `topic` ON `calendar`.`tpid` = `topic`.`tpid`
				LEFT JOIN %users% AS `user` ON `calendar`.`owner` = `user`.`uid`
			WHERE `calendar`.`id` = :calendarId LIMIT 1',
			'var' => [':calendarId' => $calendarId]
		]);

		if (empty($result->calId)) return;

		$result->options = json_decode($result->options);

		if ($result->from_time) $result->from_time = substr($result->from_time,0,5);
		if ($result->to_time) $result->to_time = substr($result->to_time,0,5);
		$result->areacode = $result->changwat.$result->ampur.$result->tambon;

		$result->property = (object)property('calendar::'.$calendarId);

		if ($debug) debugMsg($result, '$result');

		return $result;

	}

	public static function getEvents($args = []) {
		$args = (Object) $args;
		$is_category = DB::tableExists('%calendar_category%');
		$joins = $fields = [];

		if ($args->get == '*') {
			// Get all calendar items
		} else if (substr($args->get,0,1) == '*') {
			// Get all calendar items + other condition
			$conditions = array();
			$conditions[] = 'c.`tpid` IS NULL';		
			foreach (explode(',', $args->get) AS $value) {
				if (!preg_match('/(^[a-z])\:(.*)/', $value, $out)) continue;
				if ($out[1] == 't') {
					$conditions[] = 't.`tpid` IN (:nodeId)';
					mydb::where(NULL,':nodeId', 'SET:'.str_replace(':', ',',$out[2]));
				} else if ($out[1] == 'o') {
					$conditions[] = 't.`orgId` IN (:orgId)';
					mydb::where(NULL,':orgId', 'SET:'.str_replace(':', ',',$out[2]));
				}
			}
			mydb::where('('.implode(' OR ',$conditions).')',$whereValue);
		} else if ($args->orgId) {
			mydb::where('(c.`orgId` = :orgId OR t.`orgId` = :orgId)', ':orgId',$args->orgId);
		} else if ($args->nodeId == '*') {
			mydb::where('c.`nodeId` IS NOT NULL');
		} else if ($args->nodeId) {
			mydb::where('c.`tpid` IN ( :nodeId )', ':nodeId', 'SET:'.$args->nodeId);
		} else {
			mydb::where('c.`tpid` IS NULL');		
		}

		// if ($args->category) mydb::where('c.`category` = :category', ':category', $args->category);
		// if ($args->owner) mydb::where('c.`owner` =: owner', ':owner', $args->owner);
		// if ($args->u) mydb::where('c.`owner` IN (:userlist)', ':userlist','SET:'.$args->u);

		// if ($args->getMonth) {
		// 	list($year,$month) = explode('-', $args->getMonth);
		// 	$getFromDate = $year.'-'.$month.'-01';
		// 	$getToDate = date("Y-m-t", strtotime($getFromDate)); // End of month date
		// 	mydb::where(
		// 		'
		// 		(
		// 			-- (MONTH(c.`from_date`) = :month AND YEAR(c.`from_date`) = :year) OR (MONTH(c.`to_date`) = :month AND YEAR(c.`to_date`) = :year) OR
		// 			(`c`.`from_date` BETWEEN :getFromDate AND :getToDate) OR (`c`.`to_date` BETWEEN :getFromDate AND :getToDate) OR
		// 			(:getFromDate BETWEEN `c`.`from_date` AND `c`.`to_date`) OR (:getToDate BETWEEN `c`.`from_date` AND `c`.`to_date`)
		// 		)',
		// 		':month', $month, ':year', $year,
		// 		':getFromDate', $getFromDate, ':getToDate', $getToDate
		// 	);
		// } else if ($args->date) {
		// 	mydb::where('c.`from_date` <= :date AND c.`to_date` >= :date', ':date', $args->date);
		// }
		// if ($args->from) mydb::where('c.`from_date` >= :fromdate', ':fromdate', $args->from);
		// if ($args->to) mydb::where('c.`to_date` <= :todate', ':todate', $args->to);

		if ($args->date) {
			$getFromDate = $getToDate = $args->date;
		} else if ($args->from && $args->to) {
			$getFromDate = $args->from;
			$getToDate = $args->to;
		} else if ($args->getMonth) {
			list($year,$month) = explode('-', $args->getMonth);
			$getFromDate = $year.'-'.$month.'-01';
			$getToDate = date("Y-m-t", strtotime($getFromDate)); // End of month date
		}

		if ($getFromDate && $getToDate) {
			mydb::where(
				'
				(
					-- (MONTH(c.`from_date`) = :month AND YEAR(c.`from_date`) = :year) OR (MONTH(c.`to_date`) = :month AND YEAR(c.`to_date`) = :year) OR
					(`c`.`from_date` BETWEEN :getFromDate AND :getToDate) OR (`c`.`to_date` BETWEEN :getFromDate AND :getToDate) OR
					(:getFromDate BETWEEN `c`.`from_date` AND `c`.`to_date`) OR (:getToDate BETWEEN `c`.`from_date` AND `c`.`to_date`)
				)',
				':month', $month, ':year', $year,
				':getFromDate', $getFromDate, ':getToDate', $getToDate
			);
		}

		// if ($getFromDate && $getToDate) {
		// 	mydb::where('(`c`.`from_date` <= :getFromDate AND `c`.`to_date` >= :getToDate)', ':getFromDate', $getFromDate, ':getToDate', $getToDate);
		// }

		//if ($args->main) mydb::where('c.`tpid` IS NULL');

		if ($args->callFrom === 'project') {
			$joins[] = 'LEFT JOIN %project_tr% `activity` ON `activity`.`formId` = "info" AND `activity`.`part` = "activity" AND `activity`.`calId` = `c`.`id`';
			mydb::where('`activity`.`tagName` IS NULL OR `activity`.`tagName` != "group"');
			$fields[] = '`activity`.`tagName`';
		}

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

		if ($is_category) {
			$joins[] = 'LEFT JOIN %calendar_category% AS cat ON c.`category` = cat.`category_id`';
			$fields[] = 'cat.`category_shortname` , cat.`category_name`';
		}

		mydb::value('$FIELD$', ($fields ? ', ' : '') . implode(_NL.'		, ', $fields), false);
		mydb::value('$JOIN$', implode(_NL, $joins), false);
		mydb::value('$ORDER$', $args->order ? $args->order : 'c.`from_date` ASC');

		$stmt = 'SELECT
				c.*
			, TO_DAYS(`to_date`) - TO_DAYS(`from_date`) `day_repeat`
			, t.`title` `topicTitle`
			, u.`username`
			, u.`name` `owner_name`
			$FIELD$
			FROM %calendar% c
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u ON c.`owner` = u.`uid`
			$JOIN$
			%WHERE%
			GROUP BY `c`.`id`
			ORDER BY $ORDER$';

		// mydb()->_debug = true;
		$dbs = mydb::select($stmt);
		// mydb()->_debug = false;
		// debugMsg(R('query'));
		// debugMsg($args,'$args');
		// debugMsg($dbs,'$dbs');
		//return;

		//debugMsg('<pre>'.str_replace("\t", ' ', mydb()->_query).'</pre>');

		foreach ($dbs->items as $key => $rs) {
			$dbs->items[$key]->options = json_decode($rs->options);
		}

		// if ($args->date) return [[$args->date => [$dbs->items[0]]]];

		$result = [];

		foreach ($dbs->items as $value) {
			$value = (Array) $value;
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

		// debugMsg($result,'$result');

		if (debug('sql')) debugMsg(R('query'));

		return $result;
	}

	public static function getAgenda($year='',$month='') {
		$para=para(func_get_args(),2);

		$isAdd=user_access('administer calendars,create calendar content');

		$currentYear = date('Y');
		$currentMon = date('m');
		$currentDay = date('d');

		# if date is not specified
		# assume current date
		if ((!$month || !$year) || $month<1 || $year<1 || $month>12) {
			$year = $currentYear;
			$month = $currentMon;
		}
		$year=intval($year);
		$month=intval($month);

		$last_month_day = getdate (mktime(0, 0, 0, $month+1, 0, $year));

		// get month event list
		$even_para=isset($para->_src)?'/'.$para->_src:'';
		$para->from=date($year.'-'.sprintf('%02d',$month).'-01');
		$monthItems = CalendarModel::getEvents((Array) $para);

		$tables = new Table();
		$tables->addClass('calendar-agenda');
		//		$tables->thead=array('วัน','เวลา','รายการ');
		foreach ($monthItems as $date=>$dayItems) {
			unset($row);
			$row[]='<strong>'.sg_date($date,'ววว j ดด ปป').'</strong>';
			foreach ($dayItems as $i=>$rs) {
				if ($i!=0) {
					unset($row);
					$row[]='';
				}
				$row[]=substr($rs['from_time'],0,5).' น.'.($rs['to_time']!=$rs['from_time']?' - '.substr($rs['to_time'],0,5).' น.':'');
				$row[]='<strong>กิจกรรม : '.$rs['title'].'</strong>'. ($rs['topicTitle']?'<br />โครงการ : '.$rs['topicTitle'].'':'');
				$tables->rows[]=$row;
			}
		}
		$ret .= $tables->build();
		return $ret;
	}

	/**
	 * Get meeting room reservation info
	 *
	 * @param $resvid
	 * @return Record Set
	 */
	public static function getResv($resvid) {
		$stmt='SELECT r.`resvId`, r.* ,tg.`name` as `room_name`, u.`name` AS `resv_name`
		FROM %calendar_room% r
			LEFT JOIN %users% u USING (uid)
			LEFT JOIN %tag% tg ON r.roomid=tg.tid
		WHERE r.resvid=:resvid LIMIT 1';

		$rs = mydb::select($stmt,':resvid',$resvid);

		return $rs;
	}

	public static function update($data, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$data->id = empty($data->id) ? NULL : intval($data->id);

		$isUpdateData = !empty($data->id);
		$isAddData = empty($data->id);

		$result = (Object) [
			'_invalid' => NULL,
			'module_add' => NULL,
			'module_edit' => NULL,
			'data' => $data,
			'_query' => NULL,
		];

		$data->owner = i()->uid;

		$data->from_date = sg_date($data->from_date, 'Y-m-d');
		$data->to_date = sg_date($data->to_date, 'Y-m-d');
		if ($data->from_date > $data->to_date) $result->_invalid[] = 'วันที่เริ่มต้น หรือ วันที่สิ้นสุดผิดพลาด';

		$data->from_time = \SG\getFirst($data->from_time);
		$data->to_time = \SG\getFirst($data->to_time);

		$data->tpid = \SG\getFirst($data->tpid);
		$data->category = \SG\getFirst($data->category);
		$data->reminder = \SG\getFirst($data->reminder,'no');
		$data->repeat = \SG\getFirst($data->repeat,'no');

		$address = \SG\explode_address($data->location, $data->areacode);
		$data->changwat = \SG\getFirst($address['changwatCode'],' ');
		$data->ampur = \SG\getFirst($address['ampurCode'],' ');
		$data->tambon = \SG\getFirst($address['tambonCode'],' ');
		$data->village = \SG\getFirst($address['villageCode'],' ');

		$data->ip = ip2long(GetEnv('REMOTE_ADDR'));
		$data->created_date = date('Y-m-d H:i:s');

		$calendarOptions = (Object) [
			'color' => $data->color,
		];

		$data->options = sg_json_encode($calendarOptions);

		if ($debug) debugMsg($address,'$address');

		$stmt='INSERT INTO %calendar%
			(
				`id`, `tpid`, `owner`, `privacy`, `category`, `title`, `location`, `latlng`
			, `village`, `tambon`, `ampur`, `changwat`
			, `from_date`, `from_time`, `to_date`, `to_time`
			, `detail`, `reminder`, `repeat`
			, `options`
			, `ip`, `created_date`
			) VALUES (
				:id, :tpid, :owner, :privacy, :category, :title, :location, :latlng
			, :village, :tambon, :ampur, :changwat
			, :from_date, :from_time, :to_date, :to_time
			, :detail, :reminder, :repeat
			, :options
			, :ip, :created_date
			)
			ON DUPLICATE KEY UPDATE
				`privacy` = :privacy , `category` = :category , `title` = :title
			, `location` = :location , `latlng` = :latlng
			, `village` = :village , `tambon` = :tambon
			, `ampur` = :ampur , `changwat` = :changwat
			, `from_date` = :from_date , `from_time` = :from_time
			, `to_date` = :to_date , `to_time` = :to_time
			, `detail` = :detail
			, `options` = :options
			';

		mydb::query($stmt,$data);

		$result->_query[] = mydb()->_query;

		if (empty($data->id)) $data->id=mydb()->insert_id;


		list($year,$month)=explode('-',$data->from_date);
		$month=sprintf('%02d',$month);

		if ($data->module && $isAddData) {
			$result->module_add = R::On($data->module.'.calendar.add', $data, $para);
		} else if ($data->module && $isUpdateData) {
			$result->module_edit = R::On($data->module.'.calendar.edit', $data, $para);
		}

		$result->data = $data;
		if ($debug) debugMsg($result, '$result');
		return $result;
	}
}
?>