<?php
/**
* On Forum Create Content
* Created 2020-04-02
* Modify  2020-04-02
*
* @return Boolean
*/

$debug = true;

function on_forum_paper_post_permission() {
	return user_access('administer forums,create forum content');
}
?>