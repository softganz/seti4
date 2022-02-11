<?php
/**
* Reaction Add
*
* @param Int $tpid
* @param Object $options
*/

$debug = false;

import('model:reaction.php');

function r_reaction_add($refid, $action, $options = '{}') {
	return ReactionModel::add($refid, $action, $options);
}
?>