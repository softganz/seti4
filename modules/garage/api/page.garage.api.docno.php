<?php
/**
 * Search from repair code
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function garage_api_docno() {
	sendheader('text/html');
	$shopId = SG\getFirst(post('shop'));
	$docName = SG\getFirst(post('name'));
	$type = SG\getFirst(post('type'));

	$stmt = 'SELECT * FROM %garage_lastno% WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
	$lastNo = mydb::select($stmt, ':shopid', $shopId, ':docname', $docName);

	$result = NULL;
	$result->shop = $shopId;
	$result->name = $docName;
	$result->type = $type;
	$result->last = $lastNo->lastno;
	$result->next = R::Model('garage.nextno', $shopId, $docName)->nextNo;

	return json_encode($result);
}
?>