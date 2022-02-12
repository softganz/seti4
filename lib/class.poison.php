<?php
/**
* SOFTGANZ :: class.poison Version 0.01
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

--- Created 2007-01-06
--- Modify   2007-01-06
*/

define('_SGZ_BLOCK_TIME',1000); // 10 second
define('_SGZ_BLOCK_KEYLIFE',60000); // 600 second

/************************
Class  :: Poison
*************************/

class Poison {

public static function generate_key($length,$numeric=false){
	$extrakey="";
	for($j=0;$j<$length;$j++){
		while(true){
			mt_srand((double)microtime()*1000000);
			$zufall = $numeric ? mt_rand(48,57) : mt_rand(48,122);
			if(($zufall>=48 && $zufall<=57) ||
			   ($zufall>=65 && $zufall<=90) ||
			   ($zufall>=97 && $zufall<=122)){
				$extrakey.=chr($zufall);
				break;
			}
		}
	}
	return $extrakey;
}

public static function generate_daykey(){
	$sql_cmd ='SELECT id FROM %block_daykey% WHERE generate_on>(NOW()-'._SGZ_BLOCK_TIME.');';
	$remain_key = mydb::select($sql_cmd);
	// if no table then generate and query again
	//if (mydb::table_exists()) poison::createtable();
	//if (!$remain_key) $remain_key = mydb::select($sql_cmd);
	if ($remain_key->_empty) {
		$key1=Poison::generate_key(5);
		$key2=Poison::generate_key(5);
		$key3=Poison::generate_key(5);
		$key4=Poison::generate_key(5);
		$key5=Poison::generate_key(4,true);
		mydb::query("INSERT INTO %block_daykey% (key1,key2,key3,key4,key5,generate_on) VALUES('$key1','$key2','$key3','$key4','$key5',NOW());");
		mydb::query("DELETE FROM %block_daykey% WHERE generate_on<(NOW()-"._SGZ_BLOCK_KEYLIFE.");");
		//mydb::query("OPTIMIZE TABLE %block_daykey%;");
	}
}

public static function get_daykey($index,$generate=false){
	$sql_cmd='SELECT key'.$index.' `daykey` FROM %block_daykey% WHERE generate_on>(NOW()-'._SGZ_BLOCK_TIME.') ORDER BY id DESC LIMIT 1';
	$daykey=mydb::select($sql_cmd)->daykey;
	if (0||empty($daykey)) {
		poison::generate_daykey();
		$daykey=mydb::select($sql_cmd)->daykey;
	}
	return $daykey;
}

public static function exist_daykey($index,$key){
	$day_key=mydb::select("SELECT id FROM %block_daykey% WHERE key$index='$key';");
	$exist=$day_key->_num_rows?true:false;
	return $exist;
}

public static function createtable() {
	if ( !db_table_exists('%block_log%') ) {
		$sql_cmd = "CREATE TABLE `%block_log% ( ";
		$sql_cmd .= "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT , ";
		$sql_cmd .= "`package` VARCHAR( 30 ) NOT NULL , ";
		$sql_cmd .= "`keyword` VARCHAR( 100 ) NOT NULL , ";
		$sql_cmd .= "`message` TEXT NOT NULL , ";
		$sql_cmd .= "`log_date` DATETIME NOT NULL , ";
		$sql_cmd .= "PRIMARY KEY ( `id` ) ";
		$sql_cmd .= ") ENGINE = MYISAM";
		mydb::query($sql_cmd);
	}

	if ( !db_table_exists('%block_daykey%') ) {
		$sql_cmd = "create table %block_daykey% ( ";
		$sql_cmd .= "  `id` int(10) unsigned NOT NULL auto_increment, ";
		$sql_cmd .= "  `key1` char(10), ";
		$sql_cmd .= "  `key2` char(10), ";
		$sql_cmd .= "  `key3` char(10), ";
		$sql_cmd .= "  `key4` char(10), ";
		$sql_cmd .= "  `key5` char(10), ";
		$sql_cmd .= "  `generate_on` datetime, ";
		$sql_cmd .= "  PRIMARY KEY (`id`) ";
		$sql_cmd .= ") ENGINE=MyISAM";
		mydb::query($sql_cmd);
	}
}

public static function log($module,$keyword,$message) {
		Poison::createtable();
		$keyword = addslashes($keyword);
		$message = addslashes($message);
		$sql_cmd = "INSERT INTO %block_log% ";
		$sql_cmd .= "(package,keyword,message,log_date) ";
		$sql_cmd .= " VALUES ";
		$sql_cmd .= "('$module' , '$keyword' , '$message' , NOW() )";
		mydb::query($sql_cmd);
}
} // end of class Poison
?>