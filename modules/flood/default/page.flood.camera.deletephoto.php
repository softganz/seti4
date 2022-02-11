<?php
function flood_camera_deletephoto($self, $id) {
	if ($id && user_access('administrator floods,operator floods')) {
		$stmt = 'SELECT p.*, c.`name`, p.`created` `atdate` FROM %flood_photo% p LEFT JOIN %flood_cam% c USING (camid) WHERE aid=:aid LIMIT 1';
		$delete_rs = mydb::select($stmt,':aid',$id);


		if ($delete_rs->_num_rows) {
			unlink(flood_model::photo_loc($delete_rs));
			unlink(flood_model::thumb_loc($delete_rs));

			mydb::query('DELETE FROM %flood_photo% WHERE aid=:aid LIMIT 1',':aid',$id);

			$last_photo = mydb::select('SELECT `photo`,`created` FROM %flood_photo% WHERE `camid` = :camid ORDER BY aid DESC LIMIT 1',':camid',$delete_rs->camid);

			mydb::query('UPDATE %flood_cam% SET `last_photo`=:last_photo, `last_updated`=:last_updated WHERE `camid` = :camid LIMIT 1',':camid',$delete_rs->camid,':last_photo',$last_photo->photo,':last_updated',$last_photo->created);
		}
	}
	return $ret;
}
?>