<?php
/**
* Map shape main page
*
* @param Object $self
* @return String
*/
include_once('class.shpparser.php');

function map_crowd($self) {
	$shapeFolder=cfg('map.shape.folder');
	$nav=__map_shape_nav($shapeFolder);

	//$nav.=$shapeFolder;
	$ret.='<div class="map-shape-info"></div><div class="map-shape-nav"><h2><a href="'.url('map/shape').'">Map Shape</a></h2>'._NL.$nav.'</div>';
	$ret.='<div class="map-shape-map" id="mapcanvas"></div>';

	$ret.='<br clear="all" />';
	if ($layer=post('layer')) {
		$allFile=__map_shape_nav_file();
		$shpFile=$allFile[$layer];
		//$shapeFolder.'/basin/basin.shp';
		//$shpFile=$shapeFolder.'/4.ข้อมูลทรัพยากรธรรมชาติ/2.แหล่งหญ้าทะเล/Seagrass.shp';
		//$shpFile=$shapeFolder.'/1.ข้อมูลพื้นฐาน/1.พื้นที่ศึกษา/studyareas.shp';

		// $data=R::Page('map.shape.readshpdbf',$self,$shpFile);
		//$data=R::Page('map.shape.read',$self,$shpFile);

		
		if (post('fn')==1) $data=R::Page('map.shape.read',$self,$shpFile);
		else {
			$data=R::Page('map.shape.readshpdbf',$self,$shpFile);
			//print_o($data,'$data',1);
			//echo htmlspecialchars($data);
			if (is_string($data)) {
				$error=$data;
				$data=R::Page('map.shape.read',$self,$shpFile);
			}
		}
		
		return $data;
	}

	// Load js
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');
	head('map-shape.js','<script type="text/javascript" src="map/js.map-shape.js"></script>');

	return $ret;
}

function __map_shape_nav($folder,$level=1) {
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
				$ret.='<a class="map-shape-folder -expand" href="javascript:void(0)">'.$entryShow.'</a>'._NL;
				$ret.=__map_shape_nav($subFolder,$level+1);
			}
			$ret.='</li><!-- end of folder -->'._NL;
		}
		$ret.=__map_shape_nav_file($folder);
		$ret.='</ul><!-- end of level '.$level.' -->'._NL;
		closedir($handle);
	}
	return $ret;
}

function __map_shape_nav_file($folder) {
	static $mapNo=0;
	static $allFile=array();
	if (!$folder) return $allFile;

	$layerColor[6]="#83D157";
	$layerColor[7]="#597FB3";
	$layerColor[8]="#4E4BD0";
	$layerColor[9]="#A0424F";
	$layerColor[10]="#DCBE59";
	$layerColor[11]="#7ABCDD";
	$layerColor[15]="#4F59CE";
	$layerColor[16]="#84D2B3";
	$layerColor[17]="#6189CE";
	$layerColor[18]="#6EA4D2";
	$layerColor[19]="#BA4BB6";
	$layerColor[20]="#DFC35B";
	$layerColor[21]="#B94BBA";
	$layerColor[22]="#C4D456";
	$layerColor[23]="#85D561";
	$layerColor[24]="#5C7ED1";

	$layerColor[25]="#81819A";
	$layerColor[26]="#4E67B3";

	$layerColor[27]="http://softganz.com/library/img/geo/pin-during.png";
	$layerColor[28]="http://softganz.com/library/img/geo/pin-forrest.png";
	$layerColor[29]="http://softganz.com/library/img/geo/pin-greenfood.png";
	$layerColor[30]="http://softganz.com/library/img/geo/pin-man.png";

	$layerColor[69]="http://softganz.com/library/img/geo/pin-prepare.png";
	$layerColor[70]="http://softganz.com/library/img/geo/pin-mooban.png";
	$layerColor[71]="http://softganz.com/library/img/geo/pin-after.png";
	$layerColor[72]="http://softganz.com/library/img/geo/elder-social.png";
	$layerColor[73]="http://softganz.com/library/img/geo/elder-seniorsite.png";
	$layerColor[74]="http://softganz.com/library/img/geo/elder-retirement.png";
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
			$ret.='<input class="map-shape-layer" id="shp-'.$mapNo.'" type="checkbox" name="'.$filename.'" value="" data-mapno="'.$mapNo.'" />';
			$ret.='<label for="shp-'.$mapNo.'" title="Layer '.htmlspecialchars($filenameShow).' ('.$mapNo.')"> ';
			if ($layerColor[$mapNo]) {
				$mapColor=$layerColor[$mapNo];
				if (substr($mapColor,0,1)=="#") {
					$ret.='<span class="map-shape-color" style="'.($layerColor[$mapNo]?'background-color:'.$layerColor[$mapNo].';':'').'" data-color="'.$layerColor[$mapNo].'"></span>';
				} else {
					$ret.='<img class="map-shape-pin" src="'.$mapColor.'" height="24" />';
				}
			}
			$ret.=$filenameShow.'</label>';
			$ret.='</li>'._NL;
		}
	}

	return $ret;
}
?>