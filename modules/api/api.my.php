<?php
/**
* API     :: My Information
* Created :: 2022-07-11
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/my
*/

import('model:user.php');

class MyApi extends PageApi {
	function build() {
		$result = i();

		return $result;
	}
}
?>