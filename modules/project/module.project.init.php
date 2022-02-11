<?php
$debug = true;

function module_project_init() {
	$dict['th']['post new comment'] = cfg('project.msg.postnewcomment');
	tr($dict);

	if (R()->appAgent) {
		cfg('navigator',false);
		cfg('web.header',false);
		cfg('web.footer',false);
	}
}
?>