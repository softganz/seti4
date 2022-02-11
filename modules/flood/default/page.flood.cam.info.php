<?php
/**
 * Flood Camera Information
 *
 * @param Object $self
 * @param String $camid
 * @return String
 */
function flood_cam_info($self, $camid = NULL) {
	$cameraInfo = R::Model('flood.camera.get',$camid);

	$waterNadRecApi=getapi('http://www.nadrec.psu.ac.th/flood/api/sensor');
	//$ret.=print_o($waterNadRecApi,'$waterNadRecApi');

	$stationNadRecApi=getapi('http://www.nadrec.psu.ac.th/flood/api/station');


	$camMap=array('1'=>'UPT10','3'=>'UPT20','37'=>'UPT20','9'=>'UPT30');
	$station=$stationNadRecApi['result']->{$camMap[$camid]};

	$waterLevels=array();
	if (empty($waterNadRecApi['error'])) {
		$waterNadRec=json_decode($waterNadRecApi['result']);
		foreach ($waterNadRec as $item) {
			if ($item->station!=$camMap[$camid]) continue;
			$waterLevels[sg_date($item->timerec,'H')]=$item;
		}
	}

	$bankLeft=$station->bankheightleft;
	$bankRight=$station->bankheightright;
	$bankHeight=$bankLeft>$bankRight?$bankLeft:$bankRight;
	$bankLower=$bankLeft<$bankRight?$bankLeft:$bankRight;


	//$ret.=print_o($station,'$station');
	//$ret.=print_o($waterNadRec,'$waterNadRec');
	//$ret.=print_o($waterLevels,'$waterLevels');


	$waterHeight=end($waterLevels)->value;
	$waterPercent=($waterHeight-$station->depth)*100/($bankHeight-$station->depth);

	//$ret.='<div class="flood-camera-info">';
	$ret.='<div class="cctv-info">';
	$ret.='<h3 class="title">'.$cameraInfo->title.'</h3>';
	$ret.='<img class="cctv-info-photo" src="'.flood_model::photo_url($cameraInfo).'" alt="CCTV Photo" />'._NL;
	//if ($cameraInfo->overlay_url) $ret.='<img class="flood-camera-overlay" src="'.$cameraInfo->overlay_url.'" alt=""/>';
	$ret.='</div>';

	$ret.='<div class="waterlevel-banner"><span class="">ระดับน้ำ '.$waterHeight.' ม.รทก.</span><span class="'.($waterHeight<$bankLower?'-lower':'-upper').'">'.($waterHeight<$bankLower?'ต่ำกว่าตลิ่ง':'สูงกว่าตลิ่ง').' '.number_format(abs($bankLower-$waterHeight),2).' ม.</span></div>';

	$ret.='<div id="" class="crosssection">';
	$ret.='<img class="crosssection-photo" src="//hatyaicityclimate.org/upload/'.$cameraInfo->name.'.crosssection.png" width="100%" />';
	$ret.='<div class="waterlevel" style="height:'.$waterPercent.'%;">&nbsp;</div>';
	$left=0;
	foreach ($waterLevels as $time => $item) {
		$level=$item->value;
		$levelPercent=($level-$station->depth)*100/($bankHeight-$station->depth);
		$ret.='<div class="timelevel" style="height:'.($levelPercent).'%; left:'.(7+84*$left/24+4.16/2).'%;" data-tooltip="วันที่ '.sg_date($item->timerec,'ว ดด ปป H:i น.').'<br />ระดับน้ำ '.$level.' ม.รทก.">';
		$ret.='<span class="levelvalue">'.number_format($level,2).'</span>';
		$ret.='<span class="timevalue">'.($left % 2==0?$time:'&nbsp;').'</span>';
		$ret.='</div>'._NL;
		$left++;
	}
	$ret.='</div>';
	$ret.='<div class="axis -x">เวลา (ชั่วโมง)</div>';
	//$ret.='</div>';

	//$ret.=print_o($cameraInfo);

	$ret.='<style type="text/css">
	.flood-camera-info {position: relative;}
	.cctv-info .title {position: absolute; bottom:27%; text-align: center; width: 100%; background:green; color:#fff; padding: 8px 0;}
	.cctv-info-photo {width:100%;}
	.crosssection {position:relative; margin:32px 0;}
	.crosssection-photo {position: relative; z-index:2;}
	.waterlevel {position:absolute; background:#00A7EF;height:4px; z-index: 1; width:100%; bottom:0;}
	.timelevel {position: absolute; z-index:3; width:2%; background:blue; bottom:0; opacity:0.5;}
	.timelevel span {color:#fff;font-size:0.7em; display:block;text-align:center;}
	.timelevel .timevalue {position: absolute; bottom:-20px;width: 100%; text-align: center; color:#333;}
	.axis.-x {text-align: center;}
	.waterlevel-banner {position: absolute; top:4px; right:4px; z-index: 2; background:#fff; opacity: 0.7; padding:4px; font-size:1.2em; text-align: center;width:60px;padding:0;}
	.waterlevel-banner span {display: block; padding:4px;}
	.waterlevel-banner .-lower {background: green; color:#fff;}
	.waterlevel-banner .-upper {background: red; color:#fff;}
	</style>';
	return $ret;
}
?>