<?php
/**
* SOFTGANZ :: class.poison
*
* Copyright (c) 2000-2002 The SoftGanz Group By Panumas Nontapun
* Authors: Panumas Nontapun <webmaster@softganz.com>
* http://www.softganz.com
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*
--- class poison for block spam messege

--- Created :: 2007-01-06
--- Modify  :: 2023-10-04
--- Version :: 2
*/

define('_SGZ_BLOCK_TIME'		, 1000); // 10 second
define('_SGZ_BLOCK_KEYLIFE'	, 60000); // 600 second

/************************
Class  :: Poison
*************************/

class Poison {

	// Public call method
	public static function getDayKey($index, $generate = false) {
		$dayKey = Poison::getKeyByIndex($index);

		if (empty($dayKey)) {
			Poison::generateDayKey();
			$dayKey = Poison::getKeyByIndex($index);
		}
		return $dayKey;
	}

	public static function existDayKey($index, $key){
		$index = intval($index);
		if (preg_match('/ /', $key)) return false;
		$dayKey = mydb::select(
			'SELECT `id` FROM %block_daykey% WHERE `key:index` = :key LIMIT 1',
			[':index' => $index, ':key' => $key]
		);
		$exist = $dayKey->id ? true : false;
		return $exist;
	}



	// Self call method
	public static function generateDayKey(){
		$remainKey = mydb::select(
			'SELECT `id` FROM %block_daykey% WHERE `generate_on` > :expire',
		  [':expire' => Poison::expireTime()]
		);

		if ($remainKey->_empty) {
			mydb::query(
				'INSERT INTO %block_daykey%
				(`key1`, `key2`, `key3`, `key4`, `key5`, `generate_on`)
				VALUES
				(:key1, :key2, :key3, :key4, :key5, :expire)',
				[
					':key1' => Poison::generateKey(5),
					':key2' => Poison::generateKey(5),
					':key3' => Poison::generateKey(5),
					':key4' => Poison::generateKey(5),
					':key5' => Poison::generateKey(4,true),
					':expire' => date('Y-m-d-H-i-s')
				]
			);
			Poison::deleteExpire();
		}
	}

	public static function deleteExpire() {
		mydb::query(
			'DELETE FROM %block_daykey% WHERE `generate_on` < :expire',
		  [':expire' => Poison::expireTime()]
		);
	}

	public static function getKeyByIndex($index) {
		$index = intval($index);

		return mydb::select(
			'SELECT
			`key:index` `dayKey`
			FROM %block_daykey%
			WHERE `generate_on` > :expire
			ORDER BY `id` DESC
			LIMIT 1',
			[
				':index' => $index,
				':expire' => Poison::expireTime(),
			]
		)->dayKey;
	}

	public static function generateKey($length, $numeric = false){
		$extraKey = "";
		for($j = 0; $j < $length; $j++){
			while(true){
				mt_srand((double)microtime()*1000000);
				$zufall = $numeric ? mt_rand(48,57) : mt_rand(48,122);
				if(($zufall >= 48 && $zufall <= 57) ||
				   ($zufall >= 65 && $zufall <= 90) ||
				   ($zufall >= 97 && $zufall <= 122)){
					$extraKey .= chr($zufall);
					break;
				}
			}
		}
		return $extraKey;
	}

	public static function expireTime() {
		return date('Y-m-d H:i:s', date('U') - _SGZ_BLOCK_KEYLIFE);
	}

} // end of class Poison
?>