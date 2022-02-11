<?php
/**
* DEPLICATE :: Move to r.project.proposal.get.php
* Get Development Project Information
*
* @param String $tpid
* @return Object $options
*/

import('model:project.proposal.php');

function r_project_develop_get($tpid, $options = '{}') {
	return ProjectProposalModel::get($tpid, $options);
}
?>