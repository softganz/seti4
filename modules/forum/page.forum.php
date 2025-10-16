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

	$args = func_get_args();

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
				$args[$argIndex],
				$args[$argIndex+1],
				$args[$argIndex+2],
				$args[$argIndex+3],
				$args[$argIndex+4]
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= print_o($topicInfo,'$topicInfo');
			break;
	}

	return $ret;
}
?>