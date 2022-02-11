<?php
/**
* Garage Model :: Check User Right
* Created 2019-11-01
* Modify  2020-10-08
*
* @param Object $shopInfo
* @param String $moduleName
* @return Boolean
*/

$debug = true;

function r_garage_right($shopInfo, $moduleName) {
	$right = false;
	switch (strtoupper($moduleName)) {
		case 'JOB':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
			break;

		case 'CARIN':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING', 'CARIN'));
			break;

		case 'INVENTORY':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING', 'INVENTORY'));
			break;

		case 'FINANCE':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
			break;

		case 'REPORT':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
			break;

		case 'TECHNICIAN':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING', 'TECHNICIAN'));
			break;

		case 'CODE':
			$right = 	in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING','CARIN'));
			break;
	}
	return $right;
}
?>