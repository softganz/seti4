<?php
$debug = true;

function module_bmc_init() {
	if (R()->appAgent) {
		cfg('navigator',false);
		cfg('web.header',false);
	}
	cfg('web.footer',false);
	if (q(0) == 'bmc') {
		cfg('theme.stylesheet', cfg('theme').'style.green.css');
	}
}
?>