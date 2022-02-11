<?php
/**
 * Show all activity
 *
 * @return String
 */
function project_activity($self,$tpid=NULL,$action=NULL,$actid=NULL) {
	project_model::set_toolbar($self,'Project Activities');

	switch ($action) {
		case 'add':
			$ret.=R::View('project.activity.form',$tpid,$actid,$data);
			break;
	}
	return $ret;
}
?>