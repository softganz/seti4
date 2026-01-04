<?php
/**
 * Counter :: Counter Model
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2021-11-26
 * Modify  :: 2026-01-04
 * Version :: 16
 *
 * @usage new CounterModel([])
 * @usage CounterModel::function($conditions, $options)
 */

use Softganz\DB;

class CounterModel {
	public static function hit() {
		$today = today();

		Cache::clear_expire();
		$counter = cfg('counter');
		if (isset($counter->online)) unset($counter->online);
		if (cfg('online')) {cfg_db_delete('online');}

		if (is_null($counter)) {
			$counter = self::make($counter);
		}

		$is_counter_ok = is_object($counter);

		if (!$is_counter_ok) return false;

		$real_ip = \SG\getFirst(getenv('REMOTE_ADDR'),'0');
		$ip = ip2long($real_ip);
		$browser = self::getBrowserName($_SERVER['HTTP_USER_AGENT']);
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

		// Delete old online user
		DB::query([
			'DELETE FROM %users_online% WHERE `access` < :checktime',
			'var' => [':checktime' => $checked_online_time]
		]);

		$new_user = empty(DB::select([
			'SELECT `keyId` FROM %users_online% WHERE `keyId` = :keyId LIMIT 1',
			'var' => [':keyId' => $onlinekey]
		])->keyId);

		$counter->hits_count++;
		if ($new_user) $counter->users_count++;

		// update day & hour log
		if (cfg('system')->logDayHit) self::dayLog($today->date,$today->hours,$new_user);

		if ($counter->used_log == 1) self::addLog($today->datetime,$new_user);

		self::updateUserHit();

		//--- update online user information
		if (cfg('system')->logUserOnline) {
			$online = (Object) [
				'keyid' => $onlinekey,
				'host' => NULL,
				'coming' => NULL,
				'ip' => $real_ip,
				'uid' => $user_id,
				'name' => $user_name,
				'access' => $today->time,
				'browser' => $browser,
			];
			if ( $new_user ) {
				$host = gethostbyaddr($real_ip);
				if ( $host === $real_ip ) $host = 'unknown';
				$online->host = $host;
				$online->coming = $today->time;
			}

			try {
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
					'var' => $online,
					// 'options' => ['debug' => true]
				]);
			} catch (Exception $e) {}

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

	private static function getBrowserName($userAgent) {
		$browser = 'Unknown';
 		$platform = 'Unknown';
    $version= '';

		// Make case insensitive.
		list($engine) = explode(' ', $userAgent);

		//First get the platform?
		if (preg_match('/linux/i', $userAgent)) {
			$platform = 'Linux';
		} elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
			$platform = 'Mac';
		} elseif (preg_match('/windows|win32/i', $userAgent)) {
			$platform = 'Windows';
		}

    // Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) { 
			$bname = 'Internet Explorer';
			$ub = 'MSIE';
		} elseif(preg_match('/Firefox/i',$userAgent)) { 
			$bname = 'Mozilla Firefox';
			$ub = 'Firefox';
		} elseif(preg_match('/Chrome/i',$userAgent)) { 
			$bname = 'Google Chrome';
			$ub = 'Chrome';
		} elseif(preg_match('/Safari/i',$userAgent)) { 
			$bname = 'Apple Safari';
			$ub = 'Safari';
		} elseif(preg_match('/Opera/i',$userAgent)) { 
			$bname = 'Opera';
			$ub = 'Opera';
		} elseif(preg_match('/Netscape/i',$userAgent)) { 
			$bname = 'Netscape';
			$ub = 'Netscape';
		} 

		// finally get the correct version number
		$known = ['Version', $ub, 'other'];
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $userAgent, $matches)) {
			// we have no matching number just continue
		}
    
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($userAgent, 'Version') < strripos($userAgent,$ub)){
				$version= $matches['version'][0];
			} else {
				$version= $matches['version'][1];
			}
		} else {
			$version= $matches['version'][0];
		}

		// check if we have a number
		if ($version == null || $version == '') {$version = '?';}

		$agent = strtolower($userAgent);

		// If the string *starts* with the string, strpos returns 0 (i.e., FALSE). Do a ghetto hack and start with a space.
		// "[strpos()] may return Boolean FALSE, but may also return a non-Boolean value which evaluates to FALSE."
		// http://php.net/manual/en/function.strpos.php
		$agent = ' ' . $agent;

		// Humans / Regular Users
		if (strpos($agent, 'opera') || strpos($agent, 'opr/')) $browser = 'Opera';
		elseif (strpos($agent, 'edge')) $browser = 'Edge' ;
		elseif (strpos($agent, 'chrome')) $browser = 'Chrome' ;
		elseif (strpos($agent, 'safari')) $browser = 'Safari' ;
		elseif (strpos($agent, 'firefox')) $browser = 'Firefox' ;
		elseif (strpos($agent, 'msie') || strpos($agent, 'trident/7')) $browser = 'Internet Explorer';

		// Search Engines 
		elseif (strpos($agent, 'google')) $browser = '[Bot] Googlebot' ;
		elseif (strpos($agent, 'bing')) $browser = '[Bot] Bingbot' ;
		elseif (strpos($agent, 'slurp')) $browser = '[Bot] Yahoo! Slurp';
		elseif (strpos($agent, 'duckduckgo')) $browser = '[Bot] DuckDuckBot' ;
		elseif (strpos($agent, 'baidu')) $browser = '[Bot] Baidu' ;
		elseif (strpos($agent, 'yandex')) $browser = '[Bot] Yandex' ;
		elseif (strpos($agent, 'sogou')) $browser = '[Bot] Sogou' ;
		elseif (strpos($agent, 'exabot')) $browser = '[Bot] Exabot' ;
		elseif (strpos($agent, 'msn')) $browser = '[Bot] MSN' ;

		// Common Tools and Bots
		elseif (strpos($agent, 'mj12bot')) $browser = '[Bot] Majestic' ;
		elseif (strpos($agent, 'ahrefs')) $browser = '[Bot] Ahrefs' ;
		elseif (strpos($agent, 'semrush')) $browser = '[Bot] SEMRush' ;
		elseif (strpos($agent, 'rogerbot') || strpos($agent, 'dotbot')) $browser = '[Bot] Moz or OpenSiteExplorer';
		elseif (strpos($agent, 'frog') || strpos($agent, 'screaming')) $browser = '[Bot] Screaming Frog';
		
		// Miscellaneous 
		elseif (strpos($agent, 'facebook')) $browser = '[Bot] Facebook' ;
		elseif (strpos($agent, 'pinterest')) $browser = '[Bot] Pinterest' ;
		
		// Check for strings commonly used in bot user agents 
		elseif (strpos($agent, 'crawler') || strpos($agent, 'api') ||
			strpos($agent, 'spider') || strpos($agent, 'http') ||
			strpos($agent, 'bot')|| strpos($agent, 'archive')|| 
			strpos($agent, 'info') || strpos($agent, 'data')) $browser = '[Bot] Other' ;

		return $browser.' '.$version.' ('. $platform.' '.$engine.')';
	}

	public static function dayLog($date,$hr,$new_user) {
		// Check logDayHit configuration
		if (!cfg('system')->logDayHit) return false;

		// Check logApiHit configuration
		if (!cfg('system')->logApiHit && isApiRequest()) return false;

		// Update day hit count
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

	// Update user hit count
	public static function updateUserHit() {
		if (!i()->ok) return; 
	
		// Check logApiHit configuration
		if (!cfg('system')->logApiHit && isApiRequest()) {
			try {
				DB::query([
					'UPDATE %users%
					SET `lastHitTime` = NOW()
					WHERE uid = :userId LIMIT 1',
					'var' => [':userId' => i()->uid]
				]);
			} catch (Exception $e) {
				if (i()->uid === 1) debugMsg('Update user hit error: '.$e->getMessage());
			}
			return false;
		}

		try {
			DB::query([
				'UPDATE %users%
				SET `hits` = `hits` + 1, `lastHitTime` = NOW()
				WHERE uid = :userId LIMIT 1',
				'var' => [':userId' => i()->uid]
			]);
		} catch (Exception $e) {
			if (i()->uid === 1) debugMsg('Update user hit error: '.$e->getMessage());
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
		$logWaitingStmt = '';

		if (!cfg('system')->logApiCounter && isApiRequest()) return false;

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
			'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL,
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