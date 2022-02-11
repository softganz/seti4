<?php
$debug = true;

function module_garage_init() {
	define('_GARAGE_REPAIR_DO',1);
	define('_GARAGE_REPAIR_PART',2);
	define('_GARAGE_REPAIR_WAGE',3);

	if (q(0) == 'garage') {
		head('js.garage.js','<script type="text/javascript" src="/garage/js.garage.js"></script>');
	}
	
	if (R()->appAgent) {
		cfg('navigator',false);
		cfg('web.header',false);
	}

}
?>