<?php
/**
* Counter :: Model
* Created :: 2021-11-26
* Modify  :: 2025-07-18
* Version :: 10
*
* @usage new CounterModel([])
* @usage CounterModel::function($conditions, $options)
*/

use Softganz\DB;

class CounterModel {
	public static function hit() {
		// debugMsg('COUNTER HIT');
		$today = today();
		Cache::clear_expire();
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

		DB::query([
			'DELETE FROM %users_online% WHERE `access` < :checktime',
			'var' => [':checktime' => $checked_online_time]
		]);

		$new_user = !DB::select([
			'SELECT `keyId` FROM %users_online% WHERE `keyId` = :keyId LIMIT 1',
			'var' => [':keyId' => $onlinekey]
			])->keyId;

		$counter->hits_count++;
		if ($new_user) $counter->users_count++;

		// update day & hour log
		if (cfg('system')->logDayHit) CounterModel::dayLog($today->date,$today->hours,$new_user);

		if ($counter->used_log == 1) CounterModel::addLog($today->datetime,$new_user);

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

			DB::query([
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
				'var' => $online
			]);

			//--- add/update online user information
			DB::query(['SET @@group_concat_max_len = 100000']);

			$dbs = DB::select([
				'SELECT
					COUNT(*) `online_count`
					, COUNT(`uid`) `online_members`
					, GROUP_CONCAT(`name`) `online_name`
					FROM %users_online%
					LIMIT 1'
			]);

			$counter->online_members = $dbs->online_members;
			$counter->online_count = $dbs->online_count;
			// Do not save user online name to database config
			unset($counter->online_name);

			if (cfg('counter.enable') && $is_counter_ok) {
				cfg_db('counter',$counter);
			}

			// Set user online name to config
			$counter->online_name = $dbs->online_name;
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
			DB::query([
				'UPDATE %users%
				SET `hits` = `hits` + 1, `lastHitTime` = NOW()
				WHERE uid = :userId LIMIT 1',
				'var' => [':userId' => i()->uid]
			]);
		}
	}

	/**
	* Add Counter Log
	*
	* @param String $date
	* @param Boolean $newUser
	* @return Object Data Set
	*/

	public static function addLog($date, $newUser) {
		$debug = false; //i()->username == 'softganz';

		// Not insert log on counter_log is table lock
		$isCounterTableLock = mydb::table_is_lock('%counter_log%');
		if ($isCounterTableLock && is_admin()) {
			cfg('web.message', '<p class="notify" style="position: absolute; top: 0; right: 0; z-index: 999999; opacity: 0.6; pointer-events: none;">Table <strong>counter_log</strong> was locked!!!.</p>');
			return false;
		}

		if ( preg_match('/IIS/i',$_SERVER['SERVER_SOFTWARE']) ) {
			$request_url = $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
		} else {
			$request_url = $_SERVER['REQUEST_URI'];
		}

		// insert counter log
		$log = (Object) [
			'date' => $date,
			'uid' => i()->uid,
			'ip' => ip2long(getenv('REMOTE_ADDR')),
			'new_user' => $newUser ? 1 : NULL,
			'url' => $request_url,
			'referer' => $_SERVER['HTTP_REFERER'],
			'browser' => $_SERVER['HTTP_USER_AGENT'],
		];

		// For test bot
		// $log->browser = 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36';

		$counterLogTable = preg_match('/'.str_replace(',', '|',cfg('ban')->botContain.'/i'), $log->browser, $out) ? '%counter_bot%' : '%counter_log%';

		// DB::query(['DROP TABLE `sgz_counter_bot`']);
		// debugMsg(R('query'));

		// Create table if not exists
		// TODO: Remove this code when all site was create table ready
		if ($counterLogTable === '%counter_bot%' && !DB::tableExists('%counter_bot%')) {
			debugMsg('CREATE ');
			DB::query(['CREATE TABLE %counter_bot% LIKE %counter_log%']);
			$nextId = DB::select(['SELECT MAX(`id`) `maxId` FROM %counter_log% LIMIT 1'])->maxId + 1;
			DB::query(['ALTER TABLE %counter_bot% AUTO_INCREMENT = :nextId', 'var' => [':nextId' => $nextId]]);
		}

		$stmt = 'INSERT INTO '.$counterLogTable.'
			(log_date, user, ip, new_user, url, referer, browser)
			VALUES
			(:date, :uid, :ip, :new_user, :url, :referer, :browser)';

		$logWaitingFile = cfg('paper.upload.folder ').'upload/log.waiting.txt';
		$waitingLogMethod = 'file';
		$deleteAfterWriting = true;
		$logWritingToDb = cfg('log.writing');

		// Save query statement if table counter is lock
		if ($isCounterTableLock) {
			$stmt = mydb::prepare_stmt(NULL, $stmt, array($log))._NL;
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
			return;
		}

		if ($debug) debugMsg('Write log to database');
		// Write log text to database
		if ($waitingLogMethod === 'file') {
			if (file_exists($logWaitingFile) && !$logWritingToDb) {
				// Mark flag for this process only
				cfg_db('log.writing', 1);
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
			// Clear Mark flag
			cfg_db('log.waiting', 0);
		} else {
			$logWaitingStmt = cfg('log.waiting');
			if ($deleteAfterWriting) cfg_db_delete('log.waiting');
		}

		if ($logWaitingStmt) {
			// Split and write each statement to counter_log table
			foreach (explode('-- End of statement', $logWaitingStmt) as $logWaitingStmtItem) {
				$logWaitingStmtItem = trim($logWaitingStmtItem);
				if (!$logWaitingStmtItem) continue;
				DB::query([$logWaitingStmtItem, 'options' => ['multiple' => true]]);
			}
			cfg_db_delete('log.writing');
			if ($debug) debugMsg('Write log waiting<br />'.$logWaitingStmt);
		}

		// Write current log into table
		DB::query([
			$stmt,
			'var' => $log
		]);
		if ($debug) debugMsg('Write current log => '.R('query'));
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
			$rs = DB::select(['SELECT MIN(`log_date`) `created`, SUM(`hits`) `total_hits`, SUM(`users`) `total_users` FROM %counter_day% LIMIT 1']);
			$counter = (Object) [
				'users_count' => $rs->total_users,
				'hits_count' => $rs->total_hits,
				'used_log' => 1,
				'clear_period' => 0,
				'created_date' => $rs->created,
			];
		}
		$counter->members = DB::select(['SELECT COUNT(*) `total` FROM %users% LIMIT 1'])->total;
		return $counter;
	}

	public static function onlineCount() {
		return DB::select(['SELECT COUNT(*) `total` FROM %users_online% LIMIT 1'])->total;
	}

	public static function onlineUsers($conditions = []) {
		return (Array) DB::select([
			'SELECT o.*, u.`username`
			FROM %users_online% o
				LEFT JOIN %users% u ON u.`uid` = o.`uid`
			%WHERE%
			ORDER BY o.`access` DESC',
			'where' => [
				'%WHERE%' => [
					$conditions['type'] === 'user' ? ['o.`host` NOT REGEXP :bot', ':bot' => str_replace(',', '|',cfg('ban')->botContain)] : NULL,
					$conditions['type'] === 'member' ? ['o.`uid` IS NOT NULL'] : NULL,
					$conditions['type'] === 'bot' ? ['o.`host` REGEXP :bot', ':bot' => str_replace(',', '|',cfg('ban')->botContain)] : NULL,
				]
			]
		])->items;
	}
}
?>