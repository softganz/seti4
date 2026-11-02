<?php
/**
 * Forum    :: Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-04-03
 * Modified :: 2026-06-01
 * Version  :: 2
 *
 * @param Object $self
 * @param Int $tpid
 * @param String $action
 * @param Int $tranId
 * @return String
 */

$debug = true;

function forum($self, $forumId = null, $action = null, $tranId = null) {
	if (empty($action)) {
		return R::page('forum.home', $self, $forumId);
	}

	$args = func_get_args();

	switch ($action) {
		default:
			if (empty($topicInfo)) {
				$topicInfo = $tpid;
			}
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PAPER Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$args[$argIndex]);
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::page(
				'forum.' . $action,
				$self,
				$topicInfo,
				$args[$argIndex],
				$args[$argIndex + 1],
				$args[$argIndex + 2],
				$args[$argIndex + 3],
				$args[$argIndex + 4]
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) {
				$ret = 'ERROR : PAGE NOT FOUND';
			}

			//$ret .= print_o($topicInfo,'$topicInfo');
			break;
	}

	return $ret;
}
?>
