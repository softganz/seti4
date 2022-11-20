<?php
/**
* API     :: User Sign Out
* Created :: 2022-07-11
* Modify  :: 2022-11-19
* Version :: 2
*
* @return JSON
*
* @usage api/signout
*/

import('model:user.php');

class SignoutApi extends PageApi {
	function build() {
		$result = UserModel::signOutProcess();

		return $result;
	}
}
?>