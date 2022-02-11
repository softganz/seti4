<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function test_sgclass($self) {
	$ret = '';
	$foo = new sgClass;

	$foo->hello = 'Hello World';

	$ret .= '$foo->hello = '.$foo->hello.'<br />';

	$foo->o1 = SG\json_decode('{class: "-fill", color: "red"}');
	$foo->o1->test = "Again";
	$ret .= print_o($foo->o1, '$foo->o1');
	$ret .= '$foo->o1->test = '.$foo->o1->test.'<br />';

	$foo->o = new stdClass;
	$foo->o->a = 'A'; // NOT WORK!!!!

	return $ret;
}
?>