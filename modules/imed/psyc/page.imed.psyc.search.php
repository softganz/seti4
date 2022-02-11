<?php
/**
* iMed :: Patient Search
* Created 2021-05-28
* Modify  2021-09-25
*
* @return Widget
*
* @usage imed/psyc/search
*/

$debug = true;

import('page:imed.search');

class ImedPsycSearch extends ImedSearch {
	var $id = 'imed-psyc-search';

	function __construct() {
		parent::__construct('psyc');
	}
}
?>