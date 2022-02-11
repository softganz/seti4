<?php
/**
 * My relate project such as Owner, Trainer
 *
 * @return String
 */
function project_my_all($self) {
	$ret = R::Page('project.app.follow.my', $self);
	return $ret;
}
?>