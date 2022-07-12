<?php
/**
* API :: My Information
* Created 2022-07-11
* Modify  2022-07-12
*
* @return JSON
*
* @usage api/signout
*/

import('model:user.php');

class ApiMy extends PageApi {
	function build() {
		$result = i();

		return $result;
	}
}
?>