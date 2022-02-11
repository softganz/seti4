<?php
function r_imed_zone_right($zones,$changwat,$ampur,$tambon) {
	$right = NULL;

	if ($changwat && $ampur && $tambon && array_key_exists($changwat.$ampur.$tambon,$zones)) {
		$right=$zones[$changwat.$ampur.$tambon];
	} 	else if ($changwat && $ampur && array_key_exists($changwat.$ampur,$zones)) {
		$right=$zones[$changwat.$ampur];
	} else if ($changwat && array_key_exists($changwat,$zones)) {
		$right=$zones[$changwat];
	}
	//		echo $changwat.','.$ampur.','.$tambon.print_o($zones,'$zones',1).print_o($right,'$right',1);
	return $right;
}
?>