<?php
/**
* Search person who join org
*
* @param String $q
* @param Integer/String $orgset
*/
function r_org_getall($q=NULL,$options=NULL) {
	$default='{get:"orgset",order:"o.name",sort:"asc",debug:false}';
	$options=sg_json_decode($options,$default);
	$debug=$options->debug;
	if ($debug) debugMsg($options,'$options');

	$orderList=array('name'=>'o.`name`','member'=>'`members`','type'=>'j.`type`','issue'=>'j.`issue`');
	$q=preg_replace('/[ ]{2,}/',' ',trim($q));

	if (user_access('administrator orgs')) unset($orgset);
	//else if (empty($orgset)) return;

	$where=array();
	if ($options->get=='myorg') $where=sg::add_condition($where,'o.`uid`=:uid','uid',i()->uid);
	if ($orgset) $where=sg::add_condition($where,'j.`orgid` IN (:orgset)','orgset','SET:'.$orgset);
	if (is_numeric($q)) {
		$where=sg::add_condition($where,'o.`phone` LIKE :phone ','phone','%'.$q.'%');
	} else if ($q) {
		$where=sg::add_condition($where,'(o.`name` LIKE :name)','name','%'.$q.'%');
	}
	if ($firstchar) $where=sg::add_condition('p.`name` LIKE :firstchar', 'firstchar',$firstchar.'%');

	$stmt='SELECT 
						  o.*
						-- , i.`name` `issue_name`
						, t.`name` `type_name`
						FROM %db_org% AS o
							-- LEFT JOIN %tag% AS i ON o.`issue`=i.`tid`
							LEFT JOIN %tag% AS t ON t.`taggroup`="org:sector" AND o.`sector`=t.`catid`
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						ORDER BY CONVERT ('.$orderList[$options->order].' USING tis620) '.$options->sort;
	$dbs=mydb::select($stmt,$where['value']);
	if ($debug) debugMsg(mydb()->_query);
	return $dbs;
}
?>