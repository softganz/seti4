<?php
/**
* Org Pa :: Pa Model
* Created 2022-02-23
* Modify  2022-02-23
*
* @param Array $args
* @return Object
*
* @usage new RoomModel([])
* @usage RoomModel::function($conditions, $options)
*/

$debug = true;
date_default_timezone_set("Asia/Bangkok");
class PaModel {
	function __construct($args = []) {
	}
    public static function orgCreate($name)
    {
		// if($name)
		// {
			mydb::query(
				'INSERT INTO %db_org%
				(`name`,`created`)
				VALUES
				(:name_ , :created)
				',
				[
					':name_' => $name,
					'created' => date('U'),
				]
			);
			return mydb()->_query ;
		// }	
		// else{
		// 	return "name is empty";
		// }

    }
}
?>