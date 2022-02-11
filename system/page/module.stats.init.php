<?php
function module_stats_init() {
	user_menu('home','home',url());
	user_menu('stats','stats',url('stats'));
}
?>