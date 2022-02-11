<?php
$debug = true;

function module_green_init() {
	if (R()->appAgent) {
		cfg('navigator',false);
		cfg('web.header',false);
	}
	cfg('web.footer',false);
	if (q(0) == 'green') {
		cfg('theme.stylesheet', cfg('theme').'style.green.css?v=1');
	}
}
?>