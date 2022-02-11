<?php
$debug = true;

function module_imed_init() {
	if (R()->appAgent) {
		cfg('navigator',false);
		cfg('web.header',false);
		cfg('web.footer','');
	}

	if (q(0) == 'imed' && in_array(q(1), ['app'])) {
		cfg('web.title', 'iMed@Home');
		cfg('theme.stylesheet', cfg('theme').'style.imed.app.css');
	}

	if (q(0) == 'imed' && in_array(q(1), ['psyc'])) {
		cfg('web.title', 'iMedPsyc');
		cfg('theme.stylesheet', cfg('theme').'style.imed.psyc.css');
	}

	if (q(0) == 'imed' && in_array(q(1), ['care'])) {
		cfg('web.title', 'iMedCare');
		cfg('theme.stylesheet', cfg('theme').'style.imed.care.css');
	}
}
?>