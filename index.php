<?php
// Move to seti4.0
$include_path=array('core',ini_get('include_path'));
ini_set('include_path',implode(PATH_SEPARATOR,$include_path));

require 'class.core.php';

process_request(true);
?>
