<?php
/**
* ibuy_status class for shop on web
*
* @package ibuy
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-06-22
* @modify 2009-12-09
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

function ibuy_status($self) {
	$is_operator = user_access('administer ibuys');
	$self->theme->title = tr('Status');
	$no = 0;
	$ret .= '<div class="sg-tabs"><ul id="status" class="tabs">
	'.($is_operator?'<li class="-active"><a class="sg-action" href="'.url('ibuy/status/monitor').'"  data-rel="#ibuy-status-main">Order and Claim Monitor</a></li>':'').'
	<li><a class="sg-action" href="'.url('ibuy/status/order').'" data-rel="#ibuy-status-main">My Order Status</a></li>
	<li><a class="sg-action" href="'.url('ibuy/status/claim').'" data-rel="#ibuy-status-main">My Claim Status</a></li>
	<li><a class="sg-action" href="'.url('ibuy/cart').'" data-rel="#ibuy-status-main">My Shoping Cart</a></li>
	</ul>';

	// Show order and claim monitor
	$ret .= '<div id="ibuy-status-main">';
	$ret .= R::Page('ibuy.status.'.($is_operator?'monitor':'order'), NULL);
	$ret .= '</div><!-- ibuy-status-main -->';
	$ret .= '</div><!-- sg-tabs -->';
	return $ret;
}
?>