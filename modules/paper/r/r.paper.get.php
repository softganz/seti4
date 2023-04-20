<?php
/**
* Get Paper Topic Information
*
* @param Object $conditions
* @return Object $options
*/

// @deprecated

import('model:paper.php');

function r_paper_get($conditions, $options = '{}') {
	return PaperModel::get($conditions, $options);
}
?>