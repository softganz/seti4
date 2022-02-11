<?php
/**
* Forum :: Main Page
* Created 2020-04-03
* Modify  2020-04-03
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $tranId
* @return String
*/

$debug = true;

function forum($self, $forumId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action)) return R::Page('forum.home',$self, $forumId);

	switch ($action) {

		default:

			if (empty($topicInfo)) $topicInfo = $tpid;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PAPER Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'forum.'.$action,
				$self,
				$topicInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= print_o($topicInfo,'$topicInfo');
			break;
	}

	return $ret;
}
?>