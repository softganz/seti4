<?php
/**
* Flood Monitor : water
*
* @param Object $self
* @return String
*/
function flood_forcast_show($self) {
	$self->theme->title='Rain Forcast';

	$colors=array(
							0=>'#FFFFFF', 1=>'#22C597', 2=>'#22C069', 3=>'#20C83F', 4=>'#21BD24',
							5=>'#40BF25', 6=>'#6DC828', 7=>'#9CC92B', 8=>'#D0C92E', 9=>'#C9B229',
							10=>'#CC9C26', 11=>'#DC8E26', 12=>'#E47925', 13=>'#F06323', 14=>'#E94621',
							15=>'#CF121D', 16=>'#F2194D', 17=>'#E81B7C', 18=>'#C01B94', 19=>'#BE21BE',
							20=>'#6C158D', 21=>'#29094E',
							);

	/*
	$max=0.0;
	for ($i=1;$i<=168;$i++) {
		$file=dirname(__FILE__).'/data/esri_rain1hr_d02_hour'.sprintf('%03d',$i).'.asc';
		$lines=file($file);

		$data=array_slice($lines, 6);

		$max=0.0;
		foreach ($data as $key => $value) {
			$row=explode(' ', trim($value));
			$cells[]=$row;
			foreach ($row as $v) {
				$v=floatval($v);
				if ($v>$max) $max=$v;
			}
		}
		echo $file.' Max='.$max.'<br />';
	}
	$ret.='Max='.$max;
	*/

	$dateForcast='2016-04-28';
	$hr=SG\getFirst(post('hr'),1);
	$hrCount=168;
	if ($hr>$hrCount) $hr=$hrCount;
	if ($hr<1) $hr=1;
	$dateShow=date('Y-m-d H:i',strtotime($dateForcast.' +'.intval($hr).' hour'));
	$self->theme->title.=' : '.$dateShow.' UTC';
	$file=dirname(__FILE__).'/data/esri_rain1hr_d02_hour'.sprintf('%03d',$hr).'.asc';
	$file=dirname(__FILE__).'/2016-04-28/2016-04-28_00UTC_esri_rain1hr_d02_asc/esri_rain1hr_d02_hour'.sprintf('%03d',$hr).'.asc';
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
	if ($max<=10) $scale=2;
	else $scale=1;

	$ret.='<div class="info">';
	$ret.='<h2>'.$self->theme->title.'</h2>';
	$self->theme->title='';
	$self->theme->option->header=false;
	$self->theme->option->title=false;
	$ret.='<div><a class="button" href="'.url('flood/forcast/show',array('hr'=>($hr>1?$hr-1:null))).'"> &LT; </a> <a class="button" href="'.url('flood/forcast/show',array('hr'=>($hr<$hrCount?$hr+1:$hrCount))).'"> &gt; </a></div>';
	//$ret.=$file.'<br />';
	//$ret.=print_o($cells,'$cells');
	$ret.='Date forcast '.$dateForcast.' +'.intval($hr).' hour is '.$dateShow.'<br />';

	$ret.='Max value = '.$max.'<br />';
	$ret.='Scale value = '.$scale.'<br />';

	$ui=new ui();
	$ui->add('<a href="'.url('flood/forcast').'">Rain forcast : หน้าหลัก</a>');
	$ui->add('<a href="'.url('flood/forcast/show',array('hr'=>$hr)).'">Rain forcast : ภาพถ่าย</a>');
	$ui->add('<a href="'.url('flood/forcast/gmap',array('hr'=>$hr)).'">Rain forcast : Google Map</a>');
	$ret.=$ui->build('ul');

	$ret.='<ul class="colorbar">';
	for ($i=count($colors)-1; $i>=0; $i--) {
		$ret.='<li><span style="background:'.$colors[$i].';">&nbsp;</span>'.floor($i/$scale).'</li>';
	}
	$ret.='</ul>';

	$ret.='</div>';

	$ret.='<div class="result">';
	$ret.='<img class="map" src="http://www.nadrec.psu.ac.th/upload/thailand.png" alt="" />';
	for ($j=$rowCount-1; $j>=0;$j--) {
		$row=$cells[$j];
		$ret.='<div class="row">';
		foreach ($row as $i => $v) {
			$level=ceil($v*$scale/$max);
			$ret.='<span class="pixel -p'.$level.'" style="background:'.$colors[$level].'" title="'.$level.'"></span>';
		}
		$ret.='</div>'._NL;
	}
	$ret.='</div>';

	//$ret.='Lines='.print_o($data,'$data');
	$ret.='<style type="text/css">
	body#flood #main {margin:0;}
	.toolbar {margin-bottom:20px;}
	.result {width:500px;float:left;}
	.info {width:300px; float:left;}
	.map {width:434px; position:absolute;border:1px #333 solid;opacity:.5;pointer-events: none;}
	.row {clear:both; background:#fff; white-space:nowrap;}
	.pixel {display:block; float:left; width:4px; height:4px; overflow:hidden; background:#3B007F;}
	.-p0 {background:#197F00;}
	.-p1 {background:#CCFF00;}
	.-p2 {background:#FFF600;}
	.-p3 {background:#FFFA00;}
	.-p4 {background:#FFD000;}
	.-p5 {background:#FFA100;}
	.-p6 {background:#FF6600;}
	.-p7 {background:#FF4800;}
	.-p8 {background:#FF0400;}
	.colorbar {margin:0; padding:0; list-style-type:none;}
	.colorbar>li>span {width:20px;height:20px;margin:0 10px 0 0;border:1px #999 solid;display:inline-block;border-bottom:none;}
		.colorbar>li:last-child>span {border-bottom:1px #999 solid;}
	</style>';
	return $ret;
}
?>