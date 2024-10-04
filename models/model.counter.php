<?php
/**
* Counter :: Model
* Created :: 2021-11-26
* Modify  :: 2024-10-04
* Version :: 4
*
* @usage new CounterModel([])
* @usage CounterModel::function($conditions, $options)
*/

use Softganz\DB;

class CounterModel {
	public static function hit() {
		// debugMsg('COUNTER HIT');
		$today = today();

		$counter = cfg('counter');
		if (isset($counter->online)) unset($counter->online);
		if (cfg('online')) {cfg_db_delete('online');}

		if (is_null($counter)) {
			$counter = CounterModel::make($counter);
		}

		$is_counter_ok = is_object($counter);

		if (!$is_counter_ok) return false;

		$real_ip = \SG\getFirst(getenv('REMOTE_ADDR'),'0');
		$ip = ip2long($real_ip);
		$browser = addslashes($_SERVER['HTTP_USER_AGENT']);
		$new_user = false;
		$user_id = i()->uid ? i()->uid : NULL;
		$user_name = i()->name;

		switch (cfg('counter.new_user_method')) {
			case 'session' :  $onlinekey = $_COOKIE['PHPSESSID']; break;
			default : $onlinekey = $real_ip; break;
		}

		//debugMsg('Online Key = '.$onlinekey.' '.$real_ip);

		//--- remove old online user
		$checked_online_time = $today->time - cfg('counter.online_time') * 60;

		//$checked_online_time = $today->time - 1 * 60;

		$stmt = 'DELETE FROM %users_online% WHERE `access` < :checktime';
		mydb::query($stmt, ':checktime', $checked_online_time);
		//debugMsg(mydb()->_query);

		// Create user online table if table not exists
		if (mydb()->_error) {
			mydb::query(
				'CREATE TABLE %users_online% (
					`keyid` varchar(100) NOT NULL,
					`uid` int(11) DEFAULT NULL,
					`name` varchar(255) DEFAULT NULL,
					`coming` bigint(20) DEFAULT NULL,
					`access` bigint(20) DEFAULT NULL,
					`hits` int(11) DEFAULT 0,
					`ip` varchar(50) DEFAULT NULL,
					`host` varchar(255) DEFAULT NULL,
					`browser` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`keyid`),
					KEY `uid` (`uid`),
					KEY `coming` (`coming`),
					KEY `access` (`access`)
				);'
			);
			//debugMsg(mydb()->_query);
		}


		$new_user = !mydb::select('SELECT `keyid` FROM %users_online% WHERE `keyid` = :keyid LIMIT 1', ':keyid', $onlinekey)->keyid;
		//debugMsg($new_user ? 'NEW USER' : 'OLD USER');
		//debugMsg(mydb()->_query);

		$counter->hits_count++;
		if ($new_user) $counter->users_count++;

		// update day & hour log
		if (cfg('system')->logDayHit) CounterModel::dayLog($today->date,$today->hours,$new_user);

		if ( $counter->used_log == 1 ) CounterModel::addLog($today->datetime,$user_id,$new_user);

		if (cfg('system')->logUserOnline) {
			CounterModel::addOnlineUser();

			$online = (Object) [
				'keyid' => $onlinekey,
				'host' => NULL,
				'coming' => NULL,
				'ip' => $real_ip,
				'uid' => $user_id,
				'name' => $user_name,
				'access' => $today->time,
				'browser' => NULL,
			];
			if ( $new_user ) {
				$host = gethostbyaddr($real_ip);
				if ( $host === $real_ip ) $host = 'unknown';
				$online->host = $host;
				$online->coming = $today->time;
			}
			$online->hits++;
			list($online->browser) = str_replace('"', '', explode(' ',$browser));

			mydb::query(
				'INSERT INTO %users_online%
					(`keyid`, `uid`, `name`, `coming`, `access`, `ip`, `host`, `browser`)
					VALUES
					(:keyid, :uid, :name, :coming, :access, :ip, :host, :browser)
					ON DUPLICATE KEY UPDATE
					`uid` = :uid
					, `name` = :name
					, `hits` = `hits` + 1
					, `access` = :access
					, `browser` = :browser',
				$online
			);
			//debugMsg(mydb()->_query);

			//--- add/update online user information
			mydb::query('SET @@group_concat_max_len = 100000;');

			$stmt = 'SELECT
					COUNT(*) `online_count`
					, COUNT(`uid`) `online_members`
					, GROUP_CONCAT(`name`) `online_name`
					FROM %users_online%
					LIMIT 1';

			$dbs = mydb::select($stmt);

			$counter->online_members = $dbs->online_members;
			$counter->online_name = $dbs->online_name;
			$counter->online_count = $dbs->online_count;

			//foreach ($online->items as $item) if ($item->name) $online_name[] = $item->name;

			//debugMsg($counter,'$counter');

			if (cfg('counter.enable') && $is_counter_ok) {
				cfg_db('counter',$counter);
			}
		}

		return $counter;
	}

	public static function dayLog($date,$hr,$new_user) {
		$hr = sprintf('%02d', $hr);
		$data['date'] = $date;
		if ($new_user) {
			$data['users'] = 1;
			$data['todayHits'] =1;
			$data['todayUsers'] = 1;
		} else {
			$data['users'] = 0;
			$data['todayHits'] = 0;
			$data['todayUsers'] = 0;
		}

		DB::query([
			'INSERT INTO %counter_day%
			(`log_date`, `hits`, `users`, `h_'.$hr.'`, `u_'.$hr.'`)
			VALUES
			(:date , 1 , :users, :todayHits, :todayUsers)
			ON DUPLICATE KEY UPDATE
				`hits`=`hits`+1
				, '.($new_user ? '`users` = `users`+1,' : '').' `h_'.$hr.'` = `h_'.$hr.'`+1'.($new_user ? ', `u_'.$hr.'`=`u_'.$hr.'`+1' : ''),
			'var' => [
				':date' => $data['date'],
				':users' => $data['users'],
				':todayHits' => $data['todayHits'],
				':todayUsers' => $data['todayUsers']
			]
		]);
	}

	public static function addOnlineUser(){
		// update user hit count
		if ( i()->ok ) {
			mydb::query(
				'UPDATE %users%
				SET `hits` = `hits` + 1, `lastHitTime` = NOW()
				WHERE uid = :userId LIMIT 1',
				[':userId' => i()->uid]
			);
		}
		debugMsg(mydb()->_query);
	}

	/**
	* Add Counter Log
	*
	* @param String $date
	* @param Int $userId
	* @param Boolean $newUser
	* @return Object Data Set
	*/

	public static function addLog($date, $userId, $newUser) {
		$debug = false; //i()->username == 'softganz';

		// Not insert log on counter_log is table lock
		$isCounterTableLock = mydb::table_is_lock('%counter_log%');
		if ($isCounterTableLock && user_access('access administrator pages')) {
			cfg('web.message','<p class="notify" style="position: absolute; top: 0; right: 0; z-index: 999999;">Table <strong>counter_log</strong> was locked!!!.</p>');
			return false;
		}

		if ( preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ) {
			$request_url = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
		} else {
			$request_url = $_SERVER['REQUEST_URI'];
		}


		// insert counter log
		$log = new sgClass();
		$log->date = $date;
		$log->uid = $userId;
		$log->ip = ip2long(getenv('REMOTE_ADDR'));
		$log->new_user = $newUser?1:NULL;
		$log->url = $request_url;
		$log->referer = $_SERVER['HTTP_REFERER'];
		$log->browser = $_SERVER['HTTP_USER_AGENT'];

		$stmt = 'INSERT INTO %counter_log% (
			 log_date
			, user
			, ip
			, new_user
			, url
			, referer
			, browser
			) VALUES (
			 :date
			, :uid
			, :ip
			, :new_user
			, :url
			, :referer
			, :browser
			);';

		$logWaitingFile = cfg('paper.upload.folder ').'upload/log.waiting.txt';
		$waitingLogMethod = 'file';
		$deleteAfterWriting = true;
		$logWritingToDb = cfg('log.writing');

		if ($isCounterTableLock) {
			$stmt = mydb::prepare_stmt(NULL,$stmt,array($log))._NL;
			// Statement separator
			$stmt .= '-- End of statement'._NL;
			if ($waitingLogMethod == 'file') {
				$fp = fopen($logWaitingFile,'a+');
				if ($fp) {
					fwrite($fp,$stmt);
					fclose($fp);
				}
			} else {
				cfg_db('log.waiting',cfg('log.waiting')._NL.$stmt);
				if ($debug) debugMsg('cfg(log.waiting)='.cfg('log.waiting'));
			}
			//echo '<h2>$stmt='.$stmt.'</h2>';
			return;
		}

		if ($debug) debugMsg('Write log to database');
		if ($waitingLogMethod == 'file') {
			if (file_exists($logWaitingFile) && !$logWritingToDb) {
				// Mark flag for this process only
				cfg_db('log.writing',1);

				$logWaitingStmt = '';
				// Read log waiting statement
				$fp = fopen($logWaitingFile, "r");
				if ($fp) {
					while (!feof($fp)) {
						$logWaitingStmt .= fgets($fp, 4096);
					}
					fclose($fp);
				}
				// Delete log waiting file
				if ($deleteAfterWriting) unlink($logWaitingFile);
			}
		} else {
			$logWaitingStmt = cfg('log.waiting');
			if ($deleteAfterWriting) cfg_db_delete('log.waiting');
		}

		if ($logWaitingStmt) {
			mydb()->setMultiQuery(true);

			$i=0;
			// Split and write each statement to counter_log table
			foreach (explode('-- End of statement', $logWaitingStmt) as $logWaitingStmtItem) {
				$logWaitingStmtItem = trim($logWaitingStmtItem);
				if ($logWaitingStmtItem) mydb::query($logWaitingStmtItem);
			}
			cfg_db_delete('log.writing');
			if ($debug) debugMsg('Write log waiting<br />'.$logWaitingStmt);
		}

		// Write current log into table
		mydb::query($stmt,$log);
		if ($debug) debugMsg('Write current log => '.mydb()->_query.'');
	}

	/**
	* Remake Counter Object and write into database
	* Created 2016-12-28
	* Modify  2020-10-29
	*
	* @param Object $counter
	* @return Object
	*/

	public static function make($counter = NULL) {
		if (is_object($counter)) {
			if (!isset($counter->users_count)) $counter->users_count=0;
			if (!isset($counter->hits_count)) $counter->hits_count=0;
			if (!isset($counter->used_log)) $counter->used_log=1;
			if (!isset($counter->clear_period)) $counter->clear_period=0;
			if (!isset($counter->created_date)) $counter->created_date=date('Y-m-d H:i:s');
		} else {
			$rs=mydb::select('SELECT MIN(log_date) created, SUM(hits) total_hits, SUM(users) total_users FROM %counter_day% LIMIT 1');
			$counter = (Object) [
				'users_count' => $rs->total_users,
				'hits_count' => $rs->total_hits,
				'used_log' => 1,
				'clear_period' => 0,
				'created_date' => $rs->created,
			];
		}
		$counter->members=mydb::select('SELECT COUNT(*) `total` FROM %users% LIMIT 1')->total;
		return $counter;
	}

	public static function onlineCount() {
		return mydb::select('SELECT COUNT(*) `total` FROM %users_online% LIMIT 1')->total;
	}

	public static function onlineUsers($conditions = []) {
		if ($conditions['type'] == 'user') mydb::where('o.`host` NOT LIKE "%bot%" AND o.`host` NOT LIKE "%craw%"');
		else if ($conditions['type'] == 'member') mydb::where('o.`uid` IS NOT NULL');
		else if ($conditions['type'] == 'bot') mydb::where('(o.`host` LIKE "%bot%" OR o.`host` LIKE "%craw%")');

		$dbs = mydb::select(
			'SELECT o.*, u.`username`
			FROM %users_online% o
				LEFT JOIN %users% u ON u.`uid` = o.`uid`
			%WHERE%
			ORDER BY o.`access` DESC'
		);
		return $dbs->items;
	}
}
?>