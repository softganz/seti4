<?php
/**
* Move camera photo from file/fl/camera_name to floodphoto/cam/camera_name
*
* @param Object $self
* @return String
*/

$debug = true;

function flood_admin_move_photo($self) {
	set_time_limit(0);

	$moveDate = post('d');

	$ret = '';

	if (!$moveDate) {
		$ret = '<h2>Move Flood Photo to new folder</h2>';
		for ($i = 2020; $i >= 2018; $i--) {
			for ($j=12; $j>=1; $j--) {
				$ret .= '<a class="sg-action btn -link" href="'.url('flood/admin/move/photo',array('d'=>$i.'-'.sprintf('%02d',$j))).'" data-rel="#flood-move-cache-result" onClick=\'$(this).addClass("-active")\'>'.$i.'-'.sprintf('%02d',$j).'</a> ';
			}
			$ret .= '<br />';
		}
		$ret .= '<div id="flood-move-cache-result">';
		$ret .= '</div>';
		$ret .= '<style>
		.btn {margin-bottom: 4px;}
		a.btn.-active {background:green; color:#fff;}
		</style>';
		return $ret;
	}


	mydb::where('FROM_UNIXTIME(p.`created`,"%Y-%m") = :date', ':date', $moveDate);
	$stmt = 'SELECT p.`photo`, c.`name`, p.`created` `atdate`
					FROM %flood_photo% p
						LEFT JOIN %flood_cam% c USING(`camid`)
					%WHERE%';
	$dbs = mydb::select($stmt);

	$ret .= '<h3>MOVE photo of month '.$moveDate.' amount '.number_format($dbs->_num_rows).' files.</h3>';

	foreach ($dbs->items as $rs) {
		// Create subfolder
		$photoFile = flood_model::photo_loc($rs);
		$photoFileFolder = dirname($photoFile);
		if (!is_dir($photoFileFolder)) {
			mkdir($photoFileFolder, 0777, true);
		}
		if (!file_exists($photoFileFolder)) {
			mkdir($photoFileFolder, 0777, true);
		}

		$srcFile = _FLOOD_UPLOAD_FOLDER_OLD.$rs->name.'/'.$rs->photo;
		$destFile = $photoFile;

		$result = rename($srcFile, $destFile);

		$ret .= 'Result = '.$result.' => '.$rs->name.'/'.$rs->photo.'<br />';

		//$ret .= 'photoFileFolder = '.$photoFileFolder.'<br />';
		//$ret .= 'srcFile = '.$srcFile.'<br />';
		//$ret .= 'destFile = '.$destFile.'<br /><br />';
	}

	//$ret .= print_o($dbs,'$dbs');

	return $ret;
}
?>