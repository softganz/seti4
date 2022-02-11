<?php
/**
* Map shape read data
*
* @param Object $self
* @return String
*/
require_once('shapefile.php');

/**
* gmaps.js from http://hpneo.github.io/gmaps/examples/polylines.html
* The GeoJSON Format Specification :: http://geojson.org/geojson-spec.html
*	RegExpression from http://web.mit.edu/cressica/www/ushahidi/application/libraries/Wkt.php
* PHP ShapeFile from http://gasparesganga.com/labs/php-shapefile/
* Drupal RegExpression from http://cgit.drupalcode.org/geofield/tree/src/Tests/WktGeneratorTest.php?id=f91b34f790f99e3925d54fea735f42b68aabdf99
* Convert UTM to LanLon http://stackoverflow.com/questions/9203295/open-source-php-function-for-converting-utm-coordinates-to-latitude-and-longtitu
*/
function map_gis_readshpdbf($self,$shpFile) {
	if (post('f')) $shpFile=post('f');

	set_time_limit(5*60);
	//set_time_limit(10);

	$shpIndexKeys=array('Polygon'=>'rings', 'PolyLine'=>'parts');
	//print_o(post(),'post',1);
	//echo $shpFile;
	$ret.='Shape read '.$shpFile.'<br />';
	if (!file_exists($shpFile) || !is_file($shpFile)) return false;
	try {
		$ShapeFile = new ShapeFile($shpFile);
		$shpPoint->type=$ShapeFile->getShapeType(SHAPEFILE::FORMAT_STR);
		while ($record = $ShapeFile->getRecord(SHAPEFILE::GEOMETRY_ARRAY)) {
			if ($record['dbf']['deleted']) continue;

			//print_o($record,'$record',1); continue;

			foreach ($record['dbf'] as $key => $value) {
				unset($record['dbf'][$key]);
				$record['dbf'][sg_tis620_to_utf8($key)]=sg_tis620_to_utf8($value);
			}

			//$shpPoint->bounding_box=$record['shp']['bounding_box'];
			//$shpPoint->dbf=$record['dbf'];
			//$shpPoint->coordinates[]=$record['shp']['parts'][0]['points'];

			// [parts][?][rings] :: Polygon
			// [parts] :: PolyLine
			if ($shpPoint->type=='PolyLine') {
				foreach ($record['shp']['parts'] as $shpItem) {
					$pointList=array();
					foreach ($shpItem['points'] as $pointXY) {
						list($east, $north)=$pointXY;
						$east=$pointXY['x'];
						$north=$pointXY['y'];
						$point=sg::UTMtoGeog($east, $north,47);
						$lat=$point['lat'];
						$lng=$point['lon'];

						//$shpPoint[$shpIdx][]=array($lat,$lng);
						$pointList[]=array($lat,$lng);
						//$ret.='Point = '.$pointStr.' = '.$east.','.$north.' = '.$lat.','.$lng.'<br />';
					}
					//$shpPoint->coordinates[]=$pointList;
					$shpPoint->coordinates[]=array(
																		'bounding_box'=>$record['shp']['bounding_box'],
																		'dbf'=>$record['dbf'],
																		'path'=>$pointList,
																		);

				}
			} else if ($shpPoint->type=='Point') {
				$pointList=array();
				$pointXY=$record['shp'];
				$east=$pointXY['x'];
				$north=$pointXY['y'];
				$point=sg::UTMtoGeog($east, $north,47);
				$lat=$point['lat'];
				$lng=$point['lon'];

				//$shpPoint[$shpIdx][]=array($lat,$lng);
				$pointList[]=array($lat,$lng);
				$shpPoint->coordinates[]=array(
																	'bounding_box'=>$record['shp']['bounding_box'],
																	'dbf'=>$record['dbf'],
																	'path'=>$pointList,
																	);

			} else {
				foreach ($record['shp']['parts'] as $shpParts) {
					//echo 'AAA<br />';
					//print_o($shpParts,'$shpParts',1);
					foreach ($shpParts['rings'] as $shpItem) {
						//print_o($shpItem,'$shpItem',1);
						$pointList=array();
						foreach ($shpItem['points'] as $pointXY) {
							//print_o($pointXY,'$pointXY',1);
							$east=$pointXY['x'];
							$north=$pointXY['y'];
							$point=sg::UTMtoGeog($east, $north,47);
							$lat=$point['lat'];
							$lng=$point['lon'];

							//$shpPoint[$shpIdx][]=array($lat,$lng);
							$pointList[]=array($lat,$lng);
							//echo 'Point = '.$pointStr.' = '.$east.','.$north.' = '.$lat.','.$lng.'<br />';
						}
						//$shpPoint->bounding_box=$record['shp']['bounding_box'];
						//$shpPoint->dbf=$record['dbf'];
						$xyMinPoint=sg::UTMtoGeog($record['shp']['bounding_box']['xmin'], $record['shp']['bounding_box']['ymin'],47);
						$xyMaxPoint=sg::UTMtoGeog($record['shp']['bounding_box']['xmax'], $record['shp']['bounding_box']['ymax'],47);
						//print_o($record['shp']['bounding_box'],1);
						//print_o($xyMinPoint,'$xyMinPoint',1);
						//print_o($xyMaxPoint,'$xyMaxPoint',1);
						$bounding_box=array('xmin'=>$xyMinPoint['lat'],'ymin'=>$xyMinPoint['lon'],
																'xmax'=>$xyMaxPoint['lat'],'ymax'=>$xyMaxPoint['lon']);
						$shpPoint->coordinates[]=array(
																			'bounding_box'=>$bounding_box,
																			'dbf'=>$record['dbf'],
																			'path'=>$pointList,
																			);
						//print_o($shpPoint,'$shpPoint',1);
					}
				}
			}

			// Geometry
			//print_o($record['shp']);
			// DBF Data
			//print_o($record['dbf']);
		}
//echo print_o($shpPoint,'$shpPoint');
	} catch (ShapeFileException $e) {
		return('Error '.$e->getCode().': '.$e->getMessage());
	}
	//print_o($shpPoint,'$shpPoint',1);
	return $shpPoint;
}
?>