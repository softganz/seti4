<?php
/**
 * saveup class for saveup management
 *
 * @package saveup
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2014-06-27
 * @modify 2014-06-27
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */
 
function saveup_bank($self) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ '.cfg('saveup.version'),'bank');

	$ret.='<div id="saveup-main" class="sg-load" data-url="'.url('saveup/bank/member').'"></div>';

	head('js.bank.js','<script type="text/javascript" src="saveup/js.bank.js"></script>');

	return $ret;
}
?>