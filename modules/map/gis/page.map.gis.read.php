<?php
/**
* Map shape read data
*
* @param Object $self
* @return String
*/
include_once('class.shpparser.php');

function map_gis_read_1($self,$shpFile) {
	if (post('f')) $shpFile=post('f');
	//print_o(post(),'post',1);
	//echo $shpFile;
	$ret.='Shape read '.$shpFile.'<br />';
	if (!file_exists($shpFile) || !is_file($shpFile)) return false;
	$shp=new shpParser();
	$shp->load($shpFile);

	$shpData=$shp->getShapeData();
	$shpPoint=array();
	$shpIdx=0;

	foreach ($shpData as $value) {
		$shpIdx++;
		$shpStr=$value['geom']['wkt'];
		$shpType=$value['shapeType']['name'];
  	$polygon_regex = '/^\s*(\w+)\s*\(\s*(.*)\s*\)\s*$/';
  	preg_match($polygon_regex, $shpStr, $matches);
  	//print_o($matches,'$matches',1);
  	if ($matches[1]=='MULTIPOLYGON') continue;
  	$pointList=array();
  	if ($matches) {
  		foreach (explode(',',$matches[2]) as $pointStr) {
  			list($east, $north)=explode(' ', trim($pointStr));
  			$point=sg::UTMtoGeog($east, $north,47);
				$lat=$point['lat'];
				$lng=$point['lon'];

		  	$shpPoint[$shpIdx][]=array($lat,$lng);
  			//$pointList[]=$point;
		  	//$ret.='Point = '.$pointStr.' = '.$east.','.$north.' = '.$lat.','.$lng.'<br />';
  		}
  	}
  	//$ret.=$pointList[0].','.$pointList[1];
  	//$ret.=print_o($pointList,'$pointList');
		//$ret.=print_o($matches,'$matches');
	}
 	//$ret.=print_o($shpPoint,'$shpPoint');
	//$ret.=print_o($shpData,'$shpData',1);

	/*
	$shpPoint=array();
	$shpPoint["1"][]=array(12.5000,101.9000);
	$shpPoint["1"][]=array(12.5000,102.9000);
	$shpPoint["1"][]=array(13.5000,102.9000);
	$shpPoint["1"][]=array(13.5000,101.9000);
	$shpPoint["1"][]=array(12.5000,101.9000);
	*/
	
	return $shpPoint;
}


/**
* gmaps.js from http://hpneo.github.io/gmaps/examples/polylines.html
* The GeoJSON Format Specification :: http://geojson.org/geojson-spec.html
*	RegExpression from http://web.mit.edu/cressica/www/ushahidi/application/libraries/Wkt.php
* Drupal RegExpression from http://cgit.drupalcode.org/geofield/tree/src/Tests/WktGeneratorTest.php?id=f91b34f790f99e3925d54fea735f42b68aabdf99
* Convert UTM to LanLon http://stackoverflow.com/questions/9203295/open-source-php-function-for-converting-utm-coordinates-to-latitude-and-longtitu
*/
function map_gis_read($self,$shpFile) {
	if (post('f')) $shpFile=post('f');
	//print_o(post(),'post',1);
	//echo $shpFile;
	$ret.='Shape read '.$shpFile.'<br />';
	if (!file_exists($shpFile) || !is_file($shpFile)) return false;
	$shp=new shpParser();
	$shp->load($shpFile);

	$shpData=$shp->getShapeData();
	$shpPoint=null;
	$shpIdx=0;
	
	$polygonRegex = '/^\s*(\w+)\s*\(\s*(.*)\s*\)\s*$/';
	$multiPolygonRegex = '/\)\s*\)\s*,\s*\(\s*\(/';


	foreach ($shpData as $shpDataItem) {
		$shpIdx++;
		$shpType=$shpDataItem['shapeType']['name'];
		$shpStr=$shpDataItem['geom']['wkt'];

		preg_match($polygonRegex, $shpStr, $matches);
		if (!$matches) continue;
  	if ($matches[1]=='MULTIPOLYGON') {
  		$str=$matches[2];
			preg_match($multiPolygonRegex, $str, $matches);
			$matches[2]=empty($matches)?array(trim($str)):explode($matches[0], trim($str));
			foreach ($matches[2] as $key => $value) {
				$matches[2][$key]=rtrim(ltrim($value,'('),')');
			}
			//print_o($matches,'$matches',1);
   	} else {
   		$matches[2]=array($matches[2]);
   	}

		$shpPoint->type=$shpType;

		//echo $shpStr.'<hr />';
		foreach ($matches[2] as $shpItem) {
			$pointList=array();
			foreach (explode(',',$shpItem) as $pointStr) {
				list($east, $north)=explode(' ', trim($pointStr));
				$point=sg::UTMtoGeog($east, $north,47);
				$lat=$point['lat'];
				$lng=$point['lon'];

				//$shpPoint[$shpIdx][]=array($lat,$lng);
				$pointList[]=array($lat,$lng);
				//$ret.='Point = '.$pointStr.' = '.$east.','.$north.' = '.$lat.','.$lng.'<br />';
			}
			//$shpPoint->coordinates[]=$pointList;
			$shpPoint->coordinates[]=array(
																'bounding_box'=>array(),
																'dbf'=>array(),
																'path'=>$pointList,
																);

		}
		//$ret.=$pointList[0].','.$pointList[1];
		//$ret.=print_o($pointList,'$pointList');
		//$ret.=print_o($matches,'$matches');
	}
	//print_o($shpData,'$shpData',1);
	/*
	$shpPoint2=json_decode('{ "type": "Polygon",
    "coordinates": [
      [ [100.0, 0.0], [101.0, 0.0], [101.0, 1.0], [100.0, 1.0], [100.0, 0.0] ],
      [ [100.2, 0.2], [100.8, 0.2], [100.8, 0.8], [100.2, 0.8], [100.2, 0.2] ]
      ]
   }');
   */
	//print_o($shpPoint2,'$shpPoint2',1);
 	//$ret.=print_o($shpPoint,'$shpPoint');
	//return $ret;
	return $shpPoint;
}
?>