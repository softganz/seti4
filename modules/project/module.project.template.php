<?php
function module_project_template($self, $tpid) {
	$template = [];

	$tpid = is_object($tpid) ? $tpid->tpid : $tpid;

	$stmt = 'SELECT
		t.`tpid`, pa.`tpid` `parentId`, t.`template`, pa.`template` `parentTemplate`
		, t.`orgid` `orgId`, po.`orgid` `orgParentId`, o.`template` `orgTemplate`, po.`template` `orgParentTemplate`
		FROM %topic% t
			LEFT JOIN %topic% pa ON pa.`tpid` = t.`parent`
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %db_org% po ON po.`orgid` = o.`parent`
		WHERE t.`tpid` = :tpid
		LIMIT 1';

	$result = mydb::select($stmt,':tpid',$tpid);
	// debugMsg(mydb()->_query);

	$result = mydb::clearprop($result);
	//debugMsg('Project template init '.$tpid);
	//debugMsg($result, '$result');

	page_class('-set-'.$tpid);
	if ($result->parentId) page_class('-set-'.$result->parentId);
	if ($result->orgId) page_class('-org-'.$result->orgId);
	if ($result->orgParentId) page_class('-org-'.$result->orgParentId);

	if ($result->template) $template = array_merge($template,explode(';', $result->template));
	if ($result->parentTemplate) $template = array_merge($template,explode(';', $result->parentTemplate));
	if ($result->orgTemplate) $template = array_merge($template,explode(';', $result->orgTemplate));
	if ($result->orgParentTemplate) $template = array_merge($template,explode(';', $result->orgParentTemplate));

	if ($template) {
		// Use project org project set template only

		//$template=array_merge($template,explode(';', cfg('template')));

		$template = array_unique($template);

		//debugMsg($template,'$template');

		cfg('template', implode(';', $template));
	}


	$initCmd = property('project:INIT:'.$tpid);
	if (!$initCmd && $result->orgId) $initCmd = property('org:INIT:'.$result->orgId);
	if (!$initCmd && $result->orgParentId) $initCmd = property('org:INIT:'.$result->orgParentId);

	if ($initCmd) eval_php($initCmd);
	if ($result->parentId) $setInitCmd = property('project:INIT:'.$result->parentId);
	if ($setInitCmd) eval_php($setInitCmd);

	//debugMsg('$initCmd='.htmlspecialchars($initCmd));
	//debugMsg(htmlspecialchars($setInitCmd));

	$initConfig = (Object) [];
	if ($initConfigJSON = property('project:SETTING:'.$tpid)) {
		$initConfig = SG\json_decode($initConfigJSON);
	} else if ($result->parentId && $initConfigJSON = property('project:SETTING:'.$result->parentId)) {
		$initConfig = SG\json_decode($initConfigJSON);
	} else if ($result->orgId && $initConfigJSON = property('org:SETTING:'.$result->orgId)) {
		$initConfig = SG\json_decode($initConfigJSON)->project;
	} else if ($result->orgParentId && $initConfigJSON = property('org:SETTING:'.$result->orgParentId)) {
		$initConfig = SG\json_decode($initConfigJSON)->project;
	};

	// Merge project config with current config
	cfg('project', SG\json_decode($initConfig, cfg('project')));



	// $jsonValue = SG\json_decode($jsonString, cfg($module));

	// debugMsg(is_object($initConfig));
	// debugMsg($template,'$template');
	// debugMsg('Template='.cfg('template'));
	// debugMsg($initConfig, '$initConfig');
	// debugMsg(cfg('project'), '$config');
	// debugMsg($result,'$result');

	return $result;
}
?>