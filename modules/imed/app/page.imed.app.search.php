<?php
/**
* iMed :: App Patient Search
* Created 2020-08-01
* Modify  2021-09-25
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage imed/app/search
*/

$debug = true;

import('page:imed.search');

class ImedAppSearch extends ImedSearch {
	function __construct() {
		parent::__construct('app');
	}
}
?>