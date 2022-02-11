<?php
function admin_site_path_remove($self,$id) {
	if (SG\confirm() && $id) {
		mydb::query('DELETE FROM %url_alias% WHERE `pid`=:id LIMIT 1',':id',$id);
		mydb::clear_autoid('%url_alias%');
	}
	location('admin/site/path');
}
?>