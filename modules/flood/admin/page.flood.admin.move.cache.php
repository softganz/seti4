<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_admin_move_cache($self) {
	set_time_limit(0);

	$moveDate = post('d');

	$ret = '';

	if (!$moveDate) {
		$ret = '<h2>Move Flood Cache Photo to new cache folder</h2>';
		for ($i=2018; $i>=2011; $i--) {
			for ($j=12; $j>=1; $j--) {
				$ret .= '<a class="sg-action btn -link" href="'.url('flood/admin/move/cache',array('d'=>$i.'-'.sprintf('%02d',$j))).'" data-rel="#flood-move-cache-result" onClick=\'$(this).addClass("-active")\'>'.$i.'-'.sprintf('%02d',$j).'</a> ';
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

	$ret .= '<h3>MOVE '.$moveDate.'</h3>';

	mydb::where('FROM_UNIXTIME(p.`created`,"%Y-%m") = :date', ':date', $moveDate);
	$stmt = 'SELECT p.*, c.`name` `camname`
					FROM %flood_photo% p
						LEFT JOIN %flood_cam% c USING(`camid`)
					%WHERE%';
	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$date = $rs->created;
		// Create subfolder
		$cacheFolder = _CACHE_FOLDER.date('Y',$date).'/'.date('m',$date).'/'.date('d',$date).'/';
		if (!file_exists($cacheFolder)) {
			mkdir($cacheFolder, 0777, true);
		}

		$srcFile = _CACHE_FOLDER_OLD.$rs->camname.'-'.$rs->photo;
		$destFile = $cacheFolder.$rs->camname.'-'.$rs->photo;

		$result = rename($srcFile, $destFile);

		$ret .= 'Result = '.$result.' => '.$rs->camname.'-'.$rs->photo.'<br />';

		//$ret .= 'cacheFolder = '.$cacheFolder.'<br />';
		//$ret .= 'srcFile = '.$srcFile.'<br />';
		//$ret .= 'destFile = '.$destFile.'<br /><br />';
	}

	//$ret .= print_o($dbs,'$dbs');

	return $ret;
}
?>