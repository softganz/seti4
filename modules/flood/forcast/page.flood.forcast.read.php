<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_forcast_read($self, $file, $polygon = NULL, $shapeBox = array()) {
	static $polygonArray = array();

	$includeZero = false;

	$result['file'] = $file;
	$result['error'] = false;
	$result['count'] = 0;
	$result['areaCount'] = 0;
	$result['max'] = 0;
	$result['sum'] = 0;
	$result['avg'] = 0;
	$result['inblock'] = array();

	if (!file_exists($file)) {
		$result['error']='File '.$file.' not exists.';
		return $result;
	}

	$isNoPolygonArray = $polygon && empty($polygonArray);

	// Read data from file
	$lines = file($file);

	for ($i = 0; $i < 6; $i++) {
		$out = preg_split("/[\s,]+/", trim($lines[$i]));
		$result[$out[0]] = $out[1];
	}

	$data = array_slice($lines, 6);

	$max = 0.0;
	foreach ($data as $key => $value) {
		$row = explode(' ', trim($value));
		$rows = array();
		foreach ($row as $v) {
			$rows[] = $v = floatval($v);
			if ($v > $max) $max = $v;
		}
		$cells[] = $rows;
	}
	$rowCount = count($cells);
	$xllcenter = $result['xllcenter'];
	$yllcenter = $result['yllcenter'];
	$cellsize = $result['cellsize'];

	$result['count'] = $rowCount;
	$result['max'] = $max;
	$result['gis']['markers'] = [];

	$sumRain = 0;
	$areaCount = 0;
	$pointLocation = new pointLocation();

	//echo 'FILE = '.$file.'<br />'.print_o($shapeBox,'$shapeBox');

	for ($j = $rowCount-1; $j >= 0; $j--) {
		//if ($j<50 || $j>100) continue;
		$row = $cells[$j];
		//$y=$yllcenter+($rowCount-1-$j)*$cellsize-$cellsize/2;
		$y = $yllcenter + ($rowCount-1-$j) * $cellsize;
		foreach ($row as $i => $v) {
			//$x=$xllcenter+$i*$cellsize-$cellsize/2;
			$x = $xllcenter + $i*$cellsize;
			if ($polygon) {
				// Check point inside polygon on first call only, If not check from $polygonArray
				if ($isNoPolygonArray) {
					if (__gis_in_block($y,$x,$shapeBox)) {
						//echo 'IN BLOCK '.$x.','.$y.'<br />';
						$result['inblock'][] = $x.','.$y;
						$pointAt = $pointLocation->pointInPolygon($y.' '.$x, $polygon);
						//if ($pointAt=='inside') echo $x.','.$y.' '.$pointAt.' = '.$v.'<br />';
						if ($pointAt == 'inside' || $pointAt == 'boundary') {
							$polygonArray[$j][$i] = true;
						} else {
							continue;
						}
					} else {
						//echo 'OUT BLOCK '.$x.','.$y.' '.$pointAt.' = '.$v.'<br />';
						continue;
					}
				} else {
					if (!$polygonArray[$j][$i]) continue;
				}
			}

			$areaCount++;

			if (!$includeZero && $v == 0) continue;

			$sumRain += $v;

			$result['gis']['markers'][] = array(
																			'latitude' => $y,
																			'longitude' => $x,
																			'value' => $v,
																			);
		}
	}

	//if ($isNoPolygonArray) print_o($polygonArray,'$polygonArray',1);

	$result['sum'] = $sumRain;
	$result['areaCount'] = $areaCount;
	$result['avg'] = $sumRain / $areaCount;
	return $result;
}

function __gis_in_block($x,$y,$shapeBox) {
	$result = false;
	$result = $x >= $shapeBox->xmin && $x <= $shapeBox->xmax && $y >= $shapeBox->ymin && $y <= $shapeBox->ymax;
	return $result;
}
?>