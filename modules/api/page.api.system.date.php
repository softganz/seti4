<?php
/**
* API :: Return System Date and Time
* Created 2022-07-17
* Modify  2022-07-17
*
* @return String
*
* @usage api/system/date
*/

class ApiSystemDate extends Page {
	function build() {
		return date('Y-m-d H:i:s');
	}
}
?>