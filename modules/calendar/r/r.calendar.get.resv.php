<?php
	/**
	 * Get meeting room reservation info
	 *
	 * @param $resvid
	 * @return Record Set
	 */
	function r_calendar_get_resv($resvid) {
		$stmt='SELECT r.`resvId`, r.* ,tg.`name` as `room_name`, u.`name` AS `resv_name`
		FROM %calendar_room% r
			LEFT JOIN %users% u USING (uid)
			LEFT JOIN %tag% tg ON r.roomid=tg.tid
		WHERE r.resvid=:resvid LIMIT 1';

		$rs = mydb::select($stmt,':resvid',$resvid);

		return $rs;
	}
?>