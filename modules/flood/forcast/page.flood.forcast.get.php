<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_forcast_get($self) {

	$colors24hr=array(
							0=>'#FFFFFF',
							0.4=>'#22C597',
							1=>'#22C069',
							2=>'#20C83F',
							5=>'#21BD24',
							7=>'#40BF25',
							10=>'#6DC828',
							15=>'#9CC92B',
							20=>'#D0C92E',
							25=>'#C9B229',
							30=>'#CC9C26',
							40=>'#DC8E26',
							50=>'#E47925',
							70=>'#F06323',
							90=>'#E94621',
							120=>'#CF121D',
							150=>'#F2194D',
							200=>'#E81B7C',
							250=>'#C01B94',
							300=>'#BE21BE',
							350=>'#6C158D',
							400=>'#29094E',
							);

	$colors1hr=array(
							90=>'#29094E',
							80=>'#6C158D',
							70=>'#BE21BE',
							60=>'#C01B94',
							50=>'#E81B7C',
							40=>'#F2194D',
							35=>'#CF121D',
							30=>'#E94621',
							25=>'#F06323',
							20=>'#E47925',
							15=>'#DC8E26',
							10=>'#CC9C26',
							'7.5'=>'#C9B229',
							5=>'#D0C92E',
							'2.5'=>'#9CC92B',
							'1.5'=>'#6DC828',
							1=>'#40BF25',
							'0.8'=>'#21BD24',
							'0.6'=>'#20C83F',
							'0.4'=>'#22C069',
							'0.2'=>'#22C597',
							'0'=>'#FFFFFF',
							);

	$dateForcast = post('d');
	$hr=SG\getFirst(post('hr'),1);
	$hrCount=168;
	if ($hr>$hrCount) $hr=$hrCount;
	if ($hr<1) $hr=1;
	$dateShow=date('Y-m-d H:i',strtotime($dateForcast.' +'.intval($hr-1).' hour'));
	$timeShow=date('H:i',strtotime($dateForcast.' +'.intval($hr-1).' hour'));

	$result['title']='Rain forcast : '.$dateShow.' UTC';

	$file=dirname(__FILE__).'/'.$dateForcast.'/'.$dateForcast.'_12UTC_esri_rain1hr_d02_asc/esri_rain1hr_d02_hour'.sprintf('%03d',$hr).'.asc';
	//$file=dirname(__FILE__).'/day/esri_rain24hr_d02_day'.sprintf('%01d',$hr).'.asc';


	$lines=file($file);

	$data=array_slice($lines, 6);

	$max=0.0;
	foreach ($data as $key => $value) {
		$row=explode(' ', trim($value));
		$rows=array();
		foreach ($row as $v) {
			$rows[]=$v=floatval($v);
			if ($v>$max) $max=$v;
		}
		$cells[]=$rows;
	}
	$rowCount=count($cells);

	$result['xllcenter']=$xllcenter=96.9545;
	$result['yllcenter']=$yllcenter=4.9437;
	$result['cellsize']=$cellsize=0.0813616161616162;

	$result['max']=$max;
	$result['date']=$dateForcast.' +'.intval($hr).' hour is '.$dateShow;
	$result['time']=$timeShow;

	$result['colorbar']='<ul class="colorbar">';
	foreach ($colors1hr as $ci=>$color) {
		$result['colorbar'].='<li><span style="background:'.$color.';">&nbsp;</span>'.$ci.'</li>';
	}
	$result['colorbar'].='</ul>';

	$result['gis']['center']=SG\getFirst($self->property['map.center'],'13.5000,101.4000');
	$result['gis']['zoom']=intval(SG\getFirst($self->property['map.zoom'],6));


	for ($j=$rowCount-1; $j>=0;$j--) {
		$row=$cells[$j];
		$x=$xllcenter;
		$y=$yllcenter+$j*$cellsize;
		foreach ($row as $i => $v) {
			//$level=ceil($v*$scale/$max);
			//if ($level==0) continue;
			if ($v==0) continue;
			foreach ($colors1hr as $ci=>$color) {
				if ($v>=$ci) {
					$color=$colors1hr[$ci];
					break;
				}
			}
			$x=$xllcenter+$i*$cellsize;
			$result['gis']['markers'][]=array('latitude'=>$y,
														'longitude'=>$x,
														'value'=>$v,
														'level'=>$ci,
														'color'=>$color,
														);
		}
	}
	return $result;
}
?>