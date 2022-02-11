<?php
/**
* Organization Docs Save Data
*
* @param Object $data
* @return Object
*/

function r_org_docs_save($data) {
	$result = (Object) [
		'_error' => false,
		'_query' => [],
		'data' => $data,
	];

	if (empty($data->orgid)) return $result;


	if (empty($data->docid)) $data->docid = NULL;
	$data->docdate = sg_date($data->docdate,'Y-m-d');
	if (empty($data->action)) $data->action = NULL;
	if (empty($data->whoaction)) $data->whoaction = NULL;
	$data->uid = i()->uid;
	$data->created = date('U');

	$stmt = 'INSERT INTO %org_doc% (
		`docid`
		, `orgid`
		, `uid`
		, `docno`
		, `docdate`
		, `doctype`
		, `attnorg`
		, `attnname`
		, `title`
		, `action`
		, `whoaction`
		, `detail`
		, `created`
		) VALUES (
		:docid
		, :orgid
		, :uid
		, :docno
		, :docdate
		, :doctype
		, :attnorg
		, :attnname
		, :title
		, :action
		, :whoaction
		, :detail
		, :created
		)
		ON DUPLICATE KEY UPDATE
		`docno` = :docno
		, `docdate` = :docdate
		, `doctype` = :doctype
		, `attnorg` = :attnorg
		, `attnname` = :attnname
		, `title` = :title
		, `action` = :action
		, `whoaction` = :whoaction
		, `detail` = :detail
		';

	mydb::query($stmt, $data);

	$result->_query[] = mydb()->_query;

	if (empty($data->docid)) $data->docid = mydb()->insert_id;

	$result->data = $data;


	//debugMsg($tranData,'$tranData');
	return $result;
}
?>