<?php
/**
* API :: User Sign Out
* Created 2022-07-11
* Modify  2022-07-12
*
* @return JSON
*
* @usage api/signout
*/

import('model:user.php');

class ApiSignout extends PageApi {
	function build() {
		$result = UserModel::signOutProcess();

		return $result;
	}
}
?>