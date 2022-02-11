<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_project_right_fund($fundInfo, $access = NULL) {
	$right = new stdClass();

	$right->admin = is_admin();
	$right->projectAdmin = is_admin('project');
	$right->fundAdmin = $fundInfo->is->fundAdmin;
	$right->owner = $fundInfo->is->owner;
	$right->member = $fundInfo->is->membership;
	$right->trainer = $fundInfo->is->trainer;
	$right->coreTrainer = i()->ok && in_array('coretrainer', i()->roles);

	$right->edit = $fundInfo->is->edit;

	$right->accessMember = $right->admin || $right->fundAdmin || $right->trainer || $right->coreTrainer;
	$right->createMember = $right->admin || $right->fundAdmin || $right->trainer || $right->coreTrainer;
	$right->viewMemberProfile = $right->admin || $right->member;

	$right->editFinancial = $right->admin || $right->owner || $right->projectAdmin;
	$right->accessFinancial = $right->editFinancial || $right->trainer || user_access('access full expense');

	$right->createProposal = $right->edit || $right->member;

	$right->createFollow = $right->edit || ($right->trainer && cfg('project.trainer.canaddproject'));

	$right->addPopulation = $right->edit;

	if ($access) {
		return $right->{$access};
	} else {
		return $right;
	}
}
?>