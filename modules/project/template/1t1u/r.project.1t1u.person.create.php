<?php
/**
* Project Model :: 1T1U :: Create Person Data
* Created 2021-08-27
* Modify  2021-08-27
*
* @param Object $data
*
* @usage R::Model('project.1t1u.person.create', $data)
*/

$debug = true;

function r_project_1t1u_person_create($data) {
	// Create new person
	list($name,$lname) = sg::explode_name(' ',$data->projectTitle);
	$newPerson = (Object) [
		'uid' => $data->parentUid,
		'userId' => $data->uid,
		'name' => $name,
		'lname' => $lname,
		'created' => date('U'),
	];

	mydb::query(
		'INSERT INTO %db_person% (`uid`, `userid`, `name`, `lname`, `created`) VALUES (:uid, :userId, :name, :lname, :created)',
		$newPerson
	);

	$newPsnId = mydb()->insert_id;
	// debugMsg(mydb()->_query);
	// debugMsg($newPerson, '$newPerson');

	// Update topic revision property, add json key psnId
	$topicProperty = SG\json_decode($data->topicProperty);
	$topicProperty->psnId = $newPsnId;
	$revisionData = (Object) [
		'tpid' => $data->tpid,
		'revid' => $data->revid,
		'property' => SG\json_encode($topicProperty)
	];

	mydb::query(
		'UPDATE %topic_revisions% SET `property` = :property WHERE `tpid` = :tpid AND `revid` = :revid LIMIT 1',
		$revisionData
	);
	// debugMsg(mydb()->_query);
	// debugMsg($revisionData,'$revisionData');
}
?>