<?php
/**
* saveup_member class for saveup member management
*
* @package saveup
* @version 0.10a4
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2008-05-21
* @modify 2009-07-15
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

function saveup_member($self, $memberId = NULL, $action = NULL, $tranId = NULL) {
	//$ret = R::Page('saveup.member.list', $self);


	if (empty($action) && empty($memberId)) return R::Page('saveup.member.list', $self);
	else if (empty($action) && $memberId) return R::Page('saveup.member.view',$self, $memberId);

	$memberInfo = R::Model('saveup.member.get', $memberId);
	$memberId = $memberInfo->mid;


	if (empty($memberInfo)) return message('error', 'No Data');

	$isEdit = user_access('administer saveups');

	switch ($action) {

		default :
			if (empty($memberInfo)) $memberInfo = $memberId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'saveup.member.'.$action,
								$self,
								$memberInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	//$ret .= print_o($memberInfo,'memberInfo');
	return $ret;
}
?>