<?php
/**
 * Get patient information
 *
 * @param Integer $psnId
 * @return Data Set
 */

// @deprecate

import('model:imed.patient');

function r_imed_patient_get($psnId,$options='{}') {
	return PatientModel::get($psnId, $options);
}
?>