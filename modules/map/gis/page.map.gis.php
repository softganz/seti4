<?php
/**
* Map shape main page
*
* @param Object $self
* @return String
*/
include_once('class.shpparser.php');

function map_gis($self) {
	$requestLayer = post('layer');
	$shapeFolder=cfg('map.gis.folder');
	$self->theme->title=NULL;
	$toolbar='<ul><li><a href="">View</a></li></ul>';
	//$self->theme->toolbar=$toolbar;

	$fileList=__map_gis_file($shapeFolder);
	$nav=__map_gis_nav($fileList);

	//$ret.=print_o($fileList,'$fileList');
	//$ret.=print_o(__map_gis_nav_file(),'$allFile');
	//$nav.=$shapeFolder;
	$ret.='<div class="map-gis-info"></div><div class="map-gis-nav"><h2><a href="'.url('map/gis').'">Map Shape</a></h2>'._NL.$nav.'</div>';
	$ret.='<div class="map-gis-map" id="mapcanvas"></div>';

	$ret.='<br clear="all" />';
	if ($requestLayer) {
		$allFile=__map_gis_nav_file();
		//print_o($allFile,'$allFile',1);
		$shpFile=$allFile[$requestLayer];
		//echo $shpFile;
		//$shapeFolder.'/basin/basin.shp';
		//$shpFile=$shapeFolder.'/4.ข้อมูลทรัพยากรธรรมชาติ/2.แหล่งหญ้าทะเล/Seagrass.shp';
		//$shpFile=$shapeFolder.'/1.ข้อมูลพื้นฐาน/1.พื้นที่ศึกษา/studyareas.shp';

		// $data=R::Page('map.shape.readshpdbf',$self,$shpFile);
		//$data=R::Page('map.shape.read',$self,$shpFile);

		if (post('type') == 'json') {
			//debugMsg($shpFile);
			$data = file_get_contents($shpFile);
			return $data;
		} else if (post('fn')==1) {
			$data=R::Page('func.map.gis.read',$self,$shpFile);
		} else {
			$data=R::Page('map.gis.readshpdbf',$self,$shpFile);
			//print_o($data,'$data',1);
			//echo htmlspecialchars($data);
			if (is_string($data)) {
				$error=$data;
				$data=R::Page('map.gis.read',$self,$shpFile);
			}
		}
		return json_encode($data);
		return $data;
	}

	// Load js
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');
	head('map-gis.js','<script type="text/javascript" src="map/js.map-gis.js"></script>');
	
	return $ret;
}

function __map_gis_file($folder,$level=1) {
	$isTis620Filename=cfg('tis620filename');
	$fileList=array();
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;
			$subFolder=$folder.'/'.$entry;
			$entryShow=$isTis620Filename?sg_tis620_to_utf8($entry):$entry;
			if (is_dir($subFolder)) {
				$fileList[$entryShow]=__map_gis_file($subFolder,$level+1);
			} else if (is_file($subFolder)) {
				$file=sg_explode_filename($subFolder);
				if (in_array($file->ext,array('shp','json'))) {
					$fileList[$file->basename]=$subFolder;
				}
				//print_o($file,'$file',1);
				//$fileList[$entryShow]=$subFolder;
			}
		}
		closedir($handle);
	}
	ksort($fileList);
	return $fileList;
}

function __map_gis_nav($fileList,$level=1) {
	$ret.='<ul class="l-'.$level.($level!=1?' -expand':'').'">'._NL;

	foreach ($fileList as $folder=>$fileName) {
		if (is_string($fileName)) continue;
		$ret.='<li><!-- folder -->'._NL;
		if (is_array($fileName)) {
			$ret.='<a class="map-gis-folder -expand" href="javascript:void(0)">'.$folder.'</a>'._NL;
			$ret.=__map_gis_nav($fileName,$level+1);
		}
		$ret.='</li><!-- end of folder -->'._NL;
	}
	$ret.=__map_gis_nav_file($fileList);
	$ret.='</ul><!-- end of level '.$level.' -->'._NL;
	return $ret;
}

function __map_gis_nav_file($fileList = NULL) {
	static $mapNo=0;
	static $allFile=array();
	if (!$fileList) return $allFile;
	$i=6;
	$layerColor[$i++]="#83D157";
	$layerColor[$i++]="#597FB3";
	$layerColor[$i++]="#4E4BD0";
	$layerColor[$i++]="#A0424F";
	$layerColor[$i++]="#DCBE59";
	$layerColor[$i++]="#7ABCDD";
	$i=15;
	$layerColor[$i++]="#4F59CE";
	$layerColor[$i++]="#84D2B3";
	$layerColor[$i++]="#6189CE";
	$layerColor[$i++]="#6EA4D2";
	$layerColor[$i++]="#BA4BB6";
	$layerColor[$i++]="#DFC35B";
	$layerColor[$i++]="#B94BBA";
	$layerColor[$i++]="#C4D456";
	$layerColor[$i++]="#85D561";
	$layerColor[$i++]="#5C7ED1";

	$i=25;
	$layerColor[$i++]="#0B00ED";
	$layerColor[$i++]="#048400";

	$i=27;
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-payoon.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-whale.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-turtle.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-dolphin.png";

	$i=31;
	$layerColor[$i++]="#00FF40";
	$layerColor[$i++]="#A5FF4B";
	$layerColor[$i++]="#FFE51E";
	$layerColor[$i++]="#FFBF00";
	$layerColor[$i++]="#35FFA6";
	$layerColor[$i++]="#BBFFA2";
	$layerColor[$i++]="#FFCC6F";
	$layerColor[$i++]="#FF8F35";

	$i=69;
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-seawater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-underwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-groundwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-recycle.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-badwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-freshair.png";

	$i=75;
	$layerColor[$i++]="#0FFF5B";
	$layerColor[$i++]="#45D4FF";
	$layerColor[$i++]="#CFCDFF";
	$layerColor[$i++]="#009100";
	$layerColor[$i++]="#1E92FF";
	$layerColor[$i++]="#00CE14";
	

	if ($fileList) {
		foreach ($fileList as $item) {
			if (is_array($item)) continue;
			$mapNo++;
			$allFile[$mapNo]=$item;
			$file=sg_explode_filename($item);
			$fileName=$file->basename;
			$ret.='<li>';
			$ret.='<input class="map-gis-layer" id="shp-'.$mapNo.'" type="checkbox" name="'.$fileName.'" value="" data-mapno="'.$mapNo.'" data-type="'.$file->ext.'" />';
			$ret.='<label for="shp-'.$mapNo.'" title="Layer '.htmlspecialchars($fileName).' ('.$mapNo.')"> ';
			if ($layerColor[$mapNo]) {
				$mapColor=$layerColor[$mapNo];
				if (substr($mapColor,0,1)=="#") {
					$ret.='<span class="map-gis-color" style="'.($layerColor[$mapNo]?'background-color:'.$layerColor[$mapNo].';':'').'" data-color="'.$layerColor[$mapNo].'"></span>';
				} else {
					$ret.='<img class="map-gis-pin" src="'.$mapColor.'" height="24" />';
				}
			}
			$ret.=$fileName.'</label>';
			$ret.='</li>'._NL;
		}
	}

	return $ret;
}

function __map_gis_nav_v1($folder,$level=1) {
	$isTis620Filename=cfg('tis620filename');
	if ($handle = opendir($folder)) {
		$ret.='<ul class="l-'.$level.($level!=1?' -expand':'').'">'._NL;

		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;
			$subFolder=$folder.'/'.$entry;
			if (is_file($subFolder)) continue;
			$ret.='<li><!-- folder -->'._NL;
			if (is_dir($subFolder)) {
				$entryShow=$isTis620Filename?sg_tis620_to_utf8($entry):$entry;
				$ret.='<a class="map-gis-folder -expand" href="javascript:void(0)">'.$entryShow.'</a>'._NL;
				$ret.=__map_gis_nav($subFolder,$level+1);
			}
			$ret.='</li><!-- end of folder -->'._NL;
		}
		$ret.=__map_gis_nav_file($folder);
		$ret.='</ul><!-- end of level '.$level.' -->'._NL;
		closedir($handle);
	}
	return $ret;
}

function __map_gis_nav_file_v1($folder) {
	static $mapNo=0;
	static $allFile=array();
	if (!$folder) return $allFile;
	$i=6;
	$layerColor[$i++]="#83D157";
	$layerColor[$i++]="#597FB3";
	$layerColor[$i++]="#4E4BD0";
	$layerColor[$i++]="#A0424F";
	$layerColor[$i++]="#DCBE59";
	$layerColor[$i++]="#7ABCDD";
	$i=15;
	$layerColor[$i++]="#4F59CE";
	$layerColor[$i++]="#84D2B3";
	$layerColor[$i++]="#6189CE";
	$layerColor[$i++]="#6EA4D2";
	$layerColor[$i++]="#BA4BB6";
	$layerColor[$i++]="#DFC35B";
	$layerColor[$i++]="#B94BBA";
	$layerColor[$i++]="#C4D456";
	$layerColor[$i++]="#85D561";
	$layerColor[$i++]="#5C7ED1";

	$i=25;
	$layerColor[$i++]="#0B00ED";
	$layerColor[$i++]="#048400";

	$i=27;
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-payoon.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-whale.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-turtle.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-dolphin.png";

	$i=31;
	$layerColor[$i++]="#00FF40";
	$layerColor[$i++]="#A5FF4B";
	$layerColor[$i++]="#FFE51E";
	$layerColor[$i++]="#FFBF00";
	$layerColor[$i++]="#35FFA6";
	$layerColor[$i++]="#BBFFA2";
	$layerColor[$i++]="#FFCC6F";
	$layerColor[$i++]="#FF8F35";

	$i=69;
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-seawater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-underwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-groundwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-recycle.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-badwater.png";
	$layerColor[$i++]="http://softganz.com/library/img/geo/pin-freshair.png";

	$i=75;
	$layerColor[$i++]="#0FFF5B";
	$layerColor[$i++]="#45D4FF";
	$layerColor[$i++]="#CFCDFF";
	$layerColor[$i++]="#009100";
	$layerColor[$i++]="#1E92FF";
	$layerColor[$i++]="#00CE14";
	
	/*
	#A0424F
	#DCBE59
	#8BDE9C
	#7ABCDD
	#4F59CE
	#84D2B3
	#6189CE
	#6EA4D2
	#85D46A
	#BA4BB6
	#DFC35B
	#B94BBA
	#C4D456
	#85D561
	#5C7ED1
	#597FB3
	#4E4BD0
	#81819A
	#7642B3
	#4E67B3
	#577EB3
	#5679B5
	#72B5B4
	#6789A0
	#EF91B7
	#D391EF
	#B0A9F0
	#25D997
	#6EC3F2
	*/
	$isTis620Filename=cfg('tis620filename');
	$fileList=array();
	if ($handle = opendir($folder)) {
		while (false !== ($entry = readdir($handle))) {
			if (in_array($entry,array('.','..'))) continue;
			$filename=$folder.'/'.$entry;
			if (is_file($filename)) {
				//echo $filename.'<br />';
				$file=sg_explode_filename($filename);
				if ($file->ext != 'shp') continue;
				$fileList[$file->basename]=$file->basename;
			}
		}
		closedir($handle);
	}
	if ($fileList) {
		foreach ($fileList as $filename) {
			$mapNo++;
			$allFile[$mapNo]=$folder.'/'.$filename.'.shp';
			$filenameShow=$isTis620Filename?sg_tis620_to_utf8($filename):$filename;
			$ret.='<li>';
			$ret.='<input class="map-gis-layer" id="shp-'.$mapNo.'" type="checkbox" name="'.$filename.'" value="" data-mapno="'.$mapNo.'" />';
			$ret.='<label for="shp-'.$mapNo.'" title="Layer '.htmlspecialchars($filenameShow).' ('.$mapNo.')"> ';
			if ($layerColor[$mapNo]) {
				$mapColor=$layerColor[$mapNo];
				if (substr($mapColor,0,1)=="#") {
					$ret.='<span class="map-gis-color" style="'.($layerColor[$mapNo]?'background-color:'.$layerColor[$mapNo].';':'').'" data-color="'.$layerColor[$mapNo].'"></span>';
				} else {
					$ret.='<img class="map-gis-pin" src="'.$mapColor.'" height="24" />';
				}
			}
			$ret.=$filenameShow.'</label>';
			$ret.='</li>'._NL;
		}
	}

	return $ret;
}
?>