<?php
/**
* Project main activity information
*
* @param Object $self
* @param Integer $area
* @return String
*/
function project_area($self,$area) {
	project_model::set_toolbar($self,'โครงการในพื้นที่');
	return $ret;
}
?>