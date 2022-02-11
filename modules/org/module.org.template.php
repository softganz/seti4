<?php
/**
* Org :: Template
* Created 2021-08-10
* Modify  2021-08-10
*
* @param Int $orgId
* @return Object
*
* @usage 
*/

function module_org_template($orgId) {
	$template = array();

	$orgId = is_object($orgId) ? $orgId->orgId : $orgId;

	$stmt = 'SELECT
		o.`orgid`, p.`orgid` `parentId`, o.`template`, p.`template` `parentTemplate`
		FROM %db_org% o
			LEFT JOIN %db_org% p ON p.`orgid` = o.`parent`
		WHERE o.`orgid` = :orgId
		LIMIT 1';

	$result = mydb::select($stmt, ':orgId', $orgId);
	// debugMsg(mydb()->_query);

	$result = mydb::clearprop($result);

	if ($result->parentId) page_class('-org-'.$result->parentId);
	page_class('-org-'.$orgId);

	if ($result->template) $template = array_merge($template,explode(';', $result->template));
	if ($result->parentTemplate) $template = array_merge($template,explode(';', $result->parentTemplate));

	if ($template) {
		// Use project org project set template only

		$template = array_unique($template);

		//debugMsg($template,'$template');

		cfg('template', implode(';', $template));
	}
	//debugMsg('Template='.cfg('template'));


	$initCmd = property('org:INIT:'.$orgId);
	if ($initCmd) eval_php($initCmd);
	if ($result->parentId) $setInitCmd = property('org:INIT:'.$result->parentId);
	if ($setInitCmd) eval_php($setInitCmd);

	//debugMsg('$initCmd='.htmlspecialchars($initCmd));
	//debugMsg(htmlspecialchars($setInitCmd));

	//debugMsg($result,'$result');
	return $result;
}
?>