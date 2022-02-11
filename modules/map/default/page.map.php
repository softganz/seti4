<?php
/**
 * Method _home
 * Home page of package gis
 *
 * @param Object $self
 * @param Int $mapGroup
 * @return String
 */
function map($self, $mapGroup = 1) {
	$mapGroup = SG\getFirst($mapGroup,1);

	$is_api = false;

	if ($_SERVER["HTTP_REFERER"]) {
		preg_match('/hatyaicityclimate/i',$_SERVER["HTTP_REFERER"],$out);
		if (!$out) {
			$is_api=true;
		}
	}

	mydb::where((is_numeric($mapGroup) ? '`mapgroup`' : '`mapalias`').' = :mapgroup', ':mapgroup', $mapGroup);

	$stmt = 'SELECT * FROM %map_name% %WHERE% LIMIT 1';

	$mapRs = mydb::select($stmt);

	if ($mapRs->_empty) return message('error','ไม่มีแผนที่ตามระบุ ('.$mapGroup.')');

	$mapGroup = $mapRs->mapgroup;

	head('<meta property="og:title" content="'.$mapRs->mapname.'" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="'.cfg('domain').'/map" />
	<meta property="og:image" content="'.cfg('domain').'/upload/pics/crowdmap.jpg" />
	<meta property="og:description" content="คราวด์ซอร์สซิง คือ การที่มวลชนหรือคนจำนวนมากในเครือข่ายอินเตอร์เน็ตมาช่วยกันทำงานบางอย่างโดยไม่จำกัดจำนวนคนที่มาเข้าร่วม แต่ละคนจะใช้เวลาว่างเพียงเล็กน้อยมาช่วยกันเติมเต็มข้อมูลที่ตนเองรู้
	แผนที่เครือข่าย คือ ข้อมูลที่บอกว่าใคร ทำอะไรหรือเกี่ยวข้องอย่างไร อยู่ที่ไหน กับเรื่องดังกล่าว ในที่นี้ก็คือบุคคล/หน่วยงาน/องค์กร/ฯลฯ ที่มีส่วนเกี่ยวข้อง
	หลักการในการช่วยกันทำข้อมูล คือ การใช้เวลาเพียงเล็กน้อยเข้ามาช่วยกันป้อนข้อมูลที่ประกอบด้วย ชื่อบุคคล/องค์กร/หน่วยงาน/ฯลฯ ภาระกิจ และที่อยู่ ที่ท่านรู้จักหรือได้รับทราบ (หากสามารถระบุพิกัดในแผนที่ได้ก็จะดีมาก ๆ) ใน Facebook Page และจะนำผลของการศึกษารวมทั้งแผนที่เครือข่ายไปเป็นฐานข้อมูลและแสดงผลไว้ในเว็บไซท์ '.cfg('domain').' ต่อไป
	ท่านสนใจจะเข้าร่วมในการทำข้อมูลแผนที่เครือข่ายหรือไม่?" />
	');

	$self->theme->title=$mapRs->mapname;

	cfg_db('crowdsourcing.map.mapping.hits',cfg_db('crowdsourcing.map.mapping.hits')+1);
	/*
	$ret.='<header><h2>'.$mapRs->mapname.'</h2></header>';
	$ret.='<form method="get" id="mapSearch" class="search-box" action="'.url('map/searchwho').'" role="search"><label for="fq">ค้นหา</label><input class="form-text" maxlength="100" accesskey="/" id="fq" name="fq" autocomplete="off" tabindex="" value="" title="พิมพ์ชื่อเพื่อค้นหา" type="text" placeholder="ค้นหาบุคคล หน่วยงาน สถานที่"><button type="submit" title="Search"><span class="hidden_elem">Search</span></button></form>';
	*/
	$ret.='<div id="map-nav" class="nav -map">';
	$ui=new ui();
	$ui->add('<a href="'.url().'" data-action="refresh" title="Goto Home Page">'.tr('Home','หน้าหลัก').'</a>');
	$ui->add('<form method="get" id="mapSearch" class="search-box" action="'.url('map/searchwho').'" role="search"><label for="fq">ค้นหา</label><input class="form-text" maxlength="100" accesskey="/" id="fq" name="fq" autocomplete="off" tabindex="" value="" title="พิมพ์ชื่อเพื่อค้นหา" type="text" placeholder="ค้นหาบุคคล หน่วยงาน สถานที่"><button type="submit" title="Search"><i class="icon -search"></i><span class="hidden_elem">Search</span></button></form>');
	$ui->add('<a href="javascript:void(0)" id="getMyLocation" title="Current GPS Location"><i class="icon -gps-fixed"></i><span class="-hidden">'.tr('My Location','ตำแหน่งปัจจุบัน').'</span></a>');
	$ui->add('<a href="'.url('map'.($mapGroup!=1?'/'.$mapGroup:'')).'" data-action="refresh" title="Reload Map"><i class="icon -pin"></i><span class="-hidden">'.tr('Map','แผนที่').'</span></a>');
	//		$ui->add('<a href="javascript:void(0)">'.tr('Upload Photo','ส่งภาพถ่าย').'</a>');
	$ui->add('<a href="'.url('map/list').'" title="Map Items List"><i class="icon -list"></i><span class="-hidden">'.tr('List','รายการ').'</span></a>');
	$ui->add('<a href="'.url('map/menu').'" title="Map Menu"><i class="icon -dropbox"></i><span class="-hidden">'.tr('Menu','เมนู').'</span></a>');
	$ui->add('<a href="'.url('my').'" title="My Account">'.(i()->ok ? '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="24" height="24" />' : '<i class="icon -person"></i><span class="-hidden">'.tr('Member','สมาชิก')).'</span></a>');
	$ui->add('<a href="'.url('map/help').'" title="Help"><i class="icon -help"></i><span class="-hidden">'.tr('Help','ช่วยเหลือ').'</span></a>');
	//$ui->add('<a href="https://www.facebook.com/CrowdsourcingDisasterNetworksMapping" title="พูดคุย เสนอแนะ แจ้งข่าว แจ้งข้อมูล ได้ที่ เฟสบุ๊ค แผนที่เครือข่ายเฝ้าระวังและช่วยเหลือผู้ประสพภัยพิบัติ" target="_blank" data-action="refresh">'.tr('Facebook','Facebook').'</a>');
	//		$ui->add('<a href="javascript:void(0)">'.tr('Setting','ตั้งค่า').'</a>');
	$ret.=$ui->build('ul');
	$ret.='</div>';
	$ret.='<div id="map-box" class="map-box"></div>'._NL;

	if ($_REQUEST['id']) {
		$stmt='SELECT m.*, CONCAT(X(`latlng`),",",Y(`latlng`)) latlng, X(`latlng`) lat, Y(`latlng`) lnt
						FROM %map_networks% m
						WHERE mapid=:mapid LIMIT 1';
		$rs=mydb::select($stmt,':mapid',$_REQUEST['id']);
	}

	$ret.='<div id="map-crowd" class="app-output">'._NL;
	$ret.='<div id="map_canvas" width="100%" height="100%" data-group="'.$mapGroup.'" data-center="'.SG\getFirst($rs->latlng,$mapRs->center).'" data-zoom="'.($rs->latlng?16:$mapRs->zoom).'" data-layer="'.SG\getFirst($_REQUEST['layer'],'All').'" data-url="'.url('map/layer').'" data-addurl="'.url('map/add').'" data-mapname="'.$mapRs->mapname.'">กำลังโหลดแผนที่!!!!</div>'._NL;
	$ret.='</div>'._NL;
	$ret.='<div class="app-footer">สถิติ <strong id="map-hits">'.cfg_db('crowdsourcing.map.mapping.hits').'</strong> hits จำนวนข้อมูล <strong id="map-totals">'.mydb::select('SELECT COUNT(*) total FROM %map_networks% LIMIT 1')->total.'</strong> รายการ</div>'._NL;

	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');
	head('gmaps.js','<script type="text/javascript" src="/js/gmaps.js"></script>');
	head('map.js','<script type="text/javascript" src="/map/js.map.js"></script>');
	return $ret;
}





function map_1($self) {
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

	$layerColor[27]="https://softganz.com/library/img/geo/pin-during.png";
	$layerColor[28]="https://softganz.com/library/img/geo/pin-forrest.png";
	$layerColor[29]="https://softganz.com/library/img/geo/pin-greenfood.png";
	$layerColor[30]="https://softganz.com/library/img/geo/pin-man.png";

	$layerColor[69]="https://softganz.com/library/img/geo/pin-prepare.png";
	$layerColor[70]="https://softganz.com/library/img/geo/pin-mooban.png";
	$layerColor[71]="https://softganz.com/library/img/geo/pin-after.png";
	$layerColor[72]="https://softganz.com/library/img/geo/elder-social.png";
	$layerColor[73]="https://softganz.com/library/img/geo/elder-seniorsite.png";
	$layerColor[74]="https://softganz.com/library/img/geo/elder-retirement.png";
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