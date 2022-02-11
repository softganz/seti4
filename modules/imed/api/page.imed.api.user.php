<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

class ImedApiUser {
	var $token;

	function __construct() {
		$this->token = post('token');
	}

	function build() {
		$result = (Object) [];

		$rs = mydb::select('SELECT * FROM %cache% WHERE `cid` = :token LIMIT 1', ':token', 'user:'.$this->token);

		$result = SG\json_decode($rs->data);
		// $result->rs = $rs;

		// $result->cookie = $_COOKIE;
		// $result->session = $_SESSION;

		return $result;
	}
}
?>