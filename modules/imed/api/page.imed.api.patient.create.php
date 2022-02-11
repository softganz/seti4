<?php
/**
* iMed API :: Create New Patient
* Created 2021-08-25
* Modify 	2021-08-25
*
* @param Array $_REQUEST[patient]
* @return Widget
*
* @usage imed/api/patient/create
*/

$debug = true;

import('model:imed.patient');

class ImedApiPatientCreate extends Page {
	function build() {
		$isCreate = user_access('administer imed,create imed at home');
		if (!$isCreate) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access denied']);

		$result = PatientModel::create((Object) post('patient'));

		if ($result['error']) return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => $result['error']]);

		return $result;
	}
}
?>