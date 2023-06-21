<?php
function watchdog($self, $watchId = NULL, $action = NULL) {
	$isAdmin = user_access('administer watchdogs');

	$ret = '';

	if ($action) {
		switch ($action) {
			case 'delete' :
				$ret = 'DELETED';
				if ($isAdmin && $watchId && \SG\confirm()) {
					$stmt = 'DELETE FROM %watchdog% WHERE `wid` = :wid LIMIT 1';
					mydb::query($stmt, ':wid', $watchId);
					//$ret .= mydb()->_query;
				}
				break;

			default :
				$self->theme->sidebar = '<nav class="toolbar app-toolbar"><ul><li><a href="'.url('watchdog').'">Home</a></li></ul></nav>';
				$self->theme->sidebar .= R::Page('watchdog.analysis', NULL);
				$ret.=R::Page('watchdog.list', $self);
				break;
		}
	}
	return $ret;
}
?>