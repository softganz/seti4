<?php
/**
* Paper   :: List Paper of user
* Created :: 2023-08-27
* Modify  :: 2023-08-27
* Version :: 1
*
* @param Int $userId
* @return Widget
*
* @usage paper/user/{userId}
*/

import('page:paper.list.php');

class PaperUser extends PaperList {
	function __construct($userId = NULL) {
		parent::__construct();
		$this->userId = $userId;
	}
}
?>