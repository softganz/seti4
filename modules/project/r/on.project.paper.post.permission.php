<?php
/**
* Paper event when paper/post/project (Create Project Paper)
* @param Object $self
*/
function on_project_paper_post_permission($self) {
	location('project/create');
}