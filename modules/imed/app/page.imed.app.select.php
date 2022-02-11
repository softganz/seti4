<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ImedAppSelect extends Page {
	function build() {
		$selectApp = post('app');
		$_SESSION['imedapp'] = $selectApp;
		switch ($selectApp) {
		 	case 'home':
		 		location('imed/app',['back'=>'yes']);
		 		break;

		 	case 'psyc':
		 		location('imed/psyc');
		 		break;

		 	case 'care':
		 		location('imed/care');
		 		break;
		 }
	}
}
?>