<?php
/**
* Org :: Org Controller
* Created 2015-02-13
* Modify 	2021-10-04
*
* @param Int $mainId
* @param String $action
* @return Widget
*
* @usage org[/{id}/{action}/{tranId}]
*/

import('model:org.php');

class Org extends Page {
	var $orgId;
	var $action;
	var $_args = [];

	function __construct($orgId = NULL, $action = NULL) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $orgInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$orgInfo = is_numeric($this->orgId) ? OrgModel::get($this->orgId, '{initTemplate: true, debug: false}') : NULL;

		if (empty($this->orgId) && empty($this->action)) $this->action = 'home';
		else if ($this->orgId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->mainId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'org.'.$this->action,
			[-1 => $orgInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>
<?php
/**
 * org homepage
 *
 * @package org
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2015-02-13
 * @modify 2015-02-13
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

// import('model:org.php');

function org($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if ($orgId && is_numeric($orgId)) {
		$orgInfo = OrgModel::get($orgId, '{initTemplate: true}');
	}

	if (!is_numeric($orgId)) {$action = $orgId; unset($orgId);} // Action as orgId and clear

	if (empty($action) && empty($orgId)) $action = 'home';
	if (empty($action) && $orgId) $action = 'info.home';

	// R::View('org.toolbar',$self,_ORG_TITLE, NULL, $orgInfo);

	//$ret .= 'Action = '.$action. ' Is create = '.($isCreatable ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($orgInfo, '$orgInfo');

	$argIndex = 3; // Start argument

	//$ret .= 'PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
	//$ret .= print_o(func_get_args(), '$args');

	if (preg_match('/^(info|child|planning|follow|setting)/', $action)) {
		$ret = R::Page(
			'org.'.$action,
			$orgInfo,
			func_get_arg($argIndex),
			func_get_arg($argIndex+1),
			func_get_arg($argIndex+2),
			func_get_arg($argIndex+3),
			func_get_arg($argIndex+4)
		);
	} else {
		$ret = R::Page(
			'org.'.$action,
			$self,
			$orgInfo,
			func_get_arg($argIndex),
			func_get_arg($argIndex+1),
			func_get_arg($argIndex+2),
			func_get_arg($argIndex+3),
			func_get_arg($argIndex+4)
		);
	}
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}
?>