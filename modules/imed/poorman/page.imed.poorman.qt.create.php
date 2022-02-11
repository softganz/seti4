<?php
/**
* iMed :: Create Poorman QT
* Created 2020-06-19
* Modify  2020-06-19
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_poorman_qt_create($self, $psnId = NULL) {
	$getRef = post('ref');

	$data = new stdClass();

	$data->qtgroup = 4;
	$data->qtform = 4;

	if ($psnId) {
		$personInfo = R::Model('imed.patient.get', $psnId);
		$data->psnid = $psnId;
		$data->{'qt:PSNL.PRENAME'} = $personInfo->info->prename;
		$data->{'qt:PSNL.FULLNAME'} = $personInfo->realname;
	}

	$result = R::Model('imed.poorman.save',$data);

	location('imed/app/poorman/form/'.$result->qtref.'/edit', array('ref' => $getRef));

	return $result;
}
?>