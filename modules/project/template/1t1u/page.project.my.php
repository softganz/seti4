<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my($self) {
	$ret = R::Page('project.app.action', $self);
	return $ret;
}
?>