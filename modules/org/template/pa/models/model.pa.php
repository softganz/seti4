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
* @usage PaModel::function($conditions, $options)
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
		$isCreatable = user_access('create org content');
		if (!$isCreatable) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);
		if($name)
		{
			mydb::query(
				'INSERT INTO %db_org%
				(`name`,`uid`,`created`)
				VALUES
				(:name_ , :uid, :created)
				',
				[
					':name_' => $name,
					':uid' => i()->uid,
					'created' => date('U'),
				]
			);
		} else {
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่สมบูรณ์']);
		}
		return mydb()->_query ;

    }
	public static function getSubject($orgId)
	{
		$dbs = mydb::select(
		'SELECT * FROM %org_subject% where `orgid` = :orgid',
		[
			':orgid' => $orgId,
		]);
		return $dbs->items ;
	}
	public static function orgDel($orgId)
	{
		if($orgId)
		{
			mydb::query(
				'DELETE from %db_org%
				where `orgid` = :orgid',
				[
					':orgid' => $orgId,
				]
			);
		} else {
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่สมบูรณ์']);
		}
	return mydb()->_query ;

	}
}
?>