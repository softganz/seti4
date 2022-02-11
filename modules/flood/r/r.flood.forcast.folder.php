<?php
/**
* Flood Forcast Folder List
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_flood_forcast_folder($folder = NULL,$getItems = 20) {
	$isTis620Filename=cfg('tis620filename');
	if (!$folder) $folder=dirname(__FILE__);

	$folderList = array();
	$item = 0;

	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;

			//if (count($folderList) >= $getItems) break;

			$subFolder = $folder.'/'.$entry;
			$entryShow = $isTis620Filename?sg_tis620_to_utf8($entry):$entry;

			if (is_dir($subFolder)) {
				if ($subHandle = opendir($subFolder)) {
					while (false !== ($subEntry = readdir($subHandle))) {
						if (in_array($subEntry,array('.','..'))) continue;
						$subEntryShow=$isTis620Filename?sg_tis620_to_utf8($subEntry):$subEntry;
						$folderList[$entryShow][$subEntryShow]=$subEntryShow;
					}
					krsort($folderList[$entryShow]);
				}
				closedir($subHandle);

					//$folderList[$entryShow]=__flood_forcast_subfolder($subFolder,$level+1);
				//$folderList[$entryShow]=$subFolder;

			} else if (is_file($subFolder)) {
				$file=sg_explode_filename($subFolder);
				if ($file->ext != 'shp') continue;
				$fileList[$file->basename]=$subFolder;
				//print_o($file,'$file',1);
				//$fileList[$entryShow]=$subFolder;
			}
		}
		closedir($handle);
	}
	krsort($folderList);
	$folderList = array_slice($folderList, 0, $getItems);

	return $folderList;
}

function __flood_forcast_subfolder($folder,$level=1) {
	$isTis620Filename=cfg('tis620filename');
	$fileList=array();
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;
			$subFolder=$folder.'/'.$entry;
			$entryShow=$isTis620Filename?sg_tis620_to_utf8($entry):$entry;
			if (is_dir($subFolder)) {
				$fileList[$entryShow]=__flood_forcast_subfolder($subFolder,$level+1);
			} else if (is_file($subFolder)) {
				$file=sg_explode_filename($subFolder);
				if ($file->ext != 'shp') continue;
				$fileList[$file->basename]=$subFolder;
				//print_o($file,'$file',1);
				//$fileList[$entryShow]=$subFolder;
			}
		}
		closedir($handle);
	}
	ksort($fileList);
	return $fileList;
}
?>