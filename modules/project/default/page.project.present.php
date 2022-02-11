<?php
/**
* Project Follow Mapping
* @param Object $self
* @return String
*/
function project_present($self) {

	return R::Page('project.present.situation', $self);


	$goodGroup=post('g');
	$getSector = post('s');
	$getPlan = post('p');
	$getYear = post('yy');

	$action = post('action');

	switch ($action) {
		case 'source':
			return __selectSource();
			break;

		case 'good':
			return __selectGood();
			break;

		default:
			# code...
			break;
	}

	//R::View('project.toolbar', $self, 'แผนที่ติดตามโครงการ', 'map');
	cfg('web.fullpage', true);

	$ui = new Ui();
	$ui->add('<a class="btn -link" href="'.url('project/present/situation').'">สถานการณ์</a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/plan').'">แผนงาน</a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/proposal').'">พัฒนาโครงการ</a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/follow').'">ติดตามประเมินผล</a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/valuation').'">คุณค่าโครงการ</a>');
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$ret .= '<div class="mapping -project-present">';

	$gis['center']='13.6044,80.0000';
	$gis['zoom']=6;


	$ret .= '<div class="map-nav">';
	$form = new Form(NULL, url('project/map/follow'), 'map-form');
	$form->addData('query',url('project/api/marker/follow'));
	$form->addData('query','https://happynetwork.org/project/api/marker/follow');
	$form->addConfig('method', 'GET');
	$form->addText('ตัวเลือก :');

	$form->addField(
					'q',
					array(
						'type' => 'text',
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อโครงการ',
					)
				);

	$sectorList = mydb::select('SELECT DISTINCT o.`sector` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %db_org% o USING(`orgid`) WHERE p.`prtype` = "โครงการ" HAVING `sector` != ""');
	$options = array(''=>'== ทุกองค์กร ==');
	foreach ($sectorList->items as $rs) $options[$rs->sector]=project_base::$orgTypeList[$rs->sector];
	$form->addField(
					's',
					array(
						'type' => 'select',
						'class' => '-fill',
						'options' => $options,
						'value' => $getSector,
					)
				);

	//$yearList = mydb::select('SELECT `pryear` FROM %project% WHERE `prtype` = "โครงการ" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` ASC');
	$options = array(''=>'== ทุกปี ==');
	for ($i = 2014; $i <= date('Y'); $i++) $options[$i] = 'พ.ศ. '.($i+543);
	$form->addField(
					'yy',
					array(
						'type' => 'select',
						'class' => '-fill',
						'options' => $options,
						'value' => $getYear,
					)
				);

	/*
	$stmt='SELECT * FROM %tag% WHERE `taggroup`="project:planning" ORDER BY `weight` ASC, `catid` ASC; -- {key:"catid"}';
	$planningList=mydb::select($stmt);
	$options = array(''=>'== ทุกแผนงาน ==');
	foreach ($planningList->items as $k=>$rs) $options[$rs->catid] = $rs->name;

	$form->addField(
					'p',
					array(
						'type' => 'select',
						'class' => '-fill',
						'options' => $options,
						'value' => $getPlan,
					)
				);
	*/

	$options = array(''=>'== ทุกระดับคะแนน ==');
	for ($i = 5; $i >= 0; $i--) $options[$i] = 'ระดับคะแนน > '.$i;

	$form->addField(
					'rate',
					array(
						'type' => 'select',
						'class' => '-fill',
						'options' => $options,
						'value' => $getRating,
					)
				);

	$form->addField(
					'like',
					array(
						'type' => 'checkbox',
						'options' => array(1=>'มีคนชอบ'),
					)
				);

	$form->addField(
					'submit',
					array('type' => 'button','value'=>'<i class="icon -search -white"></i><span>GO</span>','container'=>array('class'=>'-sg-text-right'))
				);
	$ret .= $form->build();

	$ret .= '</div><!-- map-nav -->';

	$ret.='<div id="map" class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL;

	$ret.='</div><!-- mapping -->';


	$ret.='<style type="text/css">
	html, body, .page.-main, .mapping.-project-present {height: 100%;}
	.module-project .app-output {height: calc( 100% - 36px )}
	.package-footer {display: none;}
	.nav.-page {margin: 0; padding: 2px; background-color: #65ccff;}
	.mapping {position:relative;}
	.mapping .map-nav {width: 200px; padding:8px;position: absolute; z-index:1; top:80px; left: 10px; border-radius:2px; background-color:#fff; opacity:0.9;}
	.infowindow {width: 240px;}
	.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
	.btn.-active {}
	.notify-main {top: 40px;}
	</style>';


	$ret .= '<script type="text/javascript">
	var host = "https://dekthaikamsai.com/"
	var queryUrl = "project/api/marker/follow"
	var projectSet
	var goodType
	var markerUrl = host+queryUrl
	var infoWindow = null
	var activeInfoWindow = null
	var mapType = "Pin Map"
	var map
	var markerCluster
	var markers
	var pinMarkers = {}

	function setMapType() {
		if (mapType == "Cluster Map") mapType = "Pin Map"
		else mapType = "Cluster Map"
		$("#nav-select-maptype").text(mapType)
		$("#map-form").submit()
	}

	$(document).on("click",".btn.-set-project", function() {
		var projectType = $(this).data("type")
		if (projectType == "follow") queryUrl = "project/api/marker/follow"
		else if (projectType == "example") queryUrl = "project/api/marker/example"
		else if (projectType == "good") queryUrl = "project/api/marker/good"
		$(".nav.-page .btn.-set-project").removeClass("-primary")
		$(this).addClass("-primary")
		if (projectType != "good") $("#map-form").submit()
	})

	$("#map-form").submit(function() {
		para = {}
		para.set = projectSet
		para.g = goodType
		para.s = $("#edit-s").val()
		para.yy = $("#edit-yy").val()
		para.p = $("#edit-p").val()
		para.rate = $("#edit-rate").val()
		para.q = $("#edit-q").val()
		para.like = $("#edit-like-1").is(":checked") ? 1 : null
		//console.log(para)

		markerUrl = host+queryUrl
		loadMarker(markerUrl, para)
		return false
	})


	function initMap() {
		map = new google.maps.Map(document.getElementById("map"), {
			zoom: 6,
			center: {lat: 13.000, lng: 100.000}
		});
		$("#map-form").submit()
	}

	function loadMarker(markerUrl, para) {
		// Add some markers to the map.
		// Note: The code uses the JavaScript Array.prototype.map() method to
		// create an array of markers based on a given "locations" array.
		// The map() method here has nothing to do with the Google Maps API.

		$.each( pinMarkers, function(i, marker) {
			//map.removeMarker(marker);
			marker.setMap(null)
		});

		if (markerCluster) markerCluster.clearMarkers();
		markers = null

		notify("LOADING")

		$.get(markerUrl, para, function (data) {
			notify()
			//console.log(data)

			var locations = []

			for (key in data.markers) {
				// console.log(data.markers[key])
				locations.push(data.markers[key])
			}
			//console.log(locations)

			markers = locations.map(function(location, i) {
				var nodeUrl = host+"project/"+location.tpid
				var html = "<div class=\"infowindow\"><h3><a href=\""+nodeUrl+"\" target=_blank>"+location.title+"</a></h3><p>"+location.name+"</p><div class=\"more-detail -sg-text-right\"><a class=\"sg-action btn -link\" href=\""+nodeUrl+"/info.short"+"\" data-rel=\"box\" data-width=\"600\">MORE <i class=\"icon -material\">chevron_right</i></a></div><nav class=\"nav -page\"><a class=\"btn\" href=\""+nodeUrl+"\" target=_blank><i class=\"icon -material\">pageview</i><span>รายละเอียด</span></a></nav></div>"
				//console.log(html)

				var infoWindow = new google.maps.InfoWindow({content: html})

				var marker = new google.maps.Marker({
					position: location,
					title : location.title,
					content : html,
					//label: location.title, //labels[i % labels.length]
				})

				marker.addListener("click", function() {
					activeInfoWindow && activeInfoWindow.close();
					infoWindow.open(map, marker);
					activeInfoWindow = infoWindow
				});

				return marker
			});

			// Add a marker clusterer to manage the markers.
			if (mapType == "Cluster Map") {
				markerCluster = new MarkerClusterer(map, markers,
					{
						imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
						maxZoom : 9,
					});
			} else {
				$.each( markers, function(i, marker) {
						pinMarkers[i] = new google.maps.Marker({
							position: marker.position,
							map: map,
							title: marker.title
						});
						var infowindow = new google.maps.InfoWindow({
							content: marker.content
						});
						pinMarkers[i].addListener("click", function() {
							infowindow.open(map, pinMarkers[i]);
						});
				})

				/*
				markers.forEach(function(marker, index) {
					console.log(marker.position)
					var pinMarkers = new google.maps.Marker({
						position: marker.position,
						map: map,
						title: marker.title
					});
				})
				*/
			}

		},"json");
	}

	$.getScript("https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js", function(data, textStatus, jqxhr) {
		loadGoogleMaps("initMap")
	})
	</script>';

	return $ret;
}

function __selectSource() {
	$ret .= '<header class="header -box"><h3>Select source</h3></header>';

	$sourceList = array(
		'https://dekthaikamsai.com/' => array(
				'title' => 'เด็กไทยแก้มใส',
			),
		'https://happynetwork.org/' => array(
				'title' => 'ชุมชนน่าอยู่ - Happy Network',
				'set' => 101,
			),
		'https://localfund.happynetwork.org/' => array(
				'title' => 'กองทุนตำบล - Local Fund',
			),
		'https://ppi.psu.ac.th/scac/' => array(
				'title' => 'ศวสต.',
				'set' => 74,
			),
		'https://ppi.psu.ac.th/food/' => array(
				'title' => 'แผนงานอาหาร',
				'set' => 214,
			),
	);

	$ui = new Ui(NULL, 'ui-menu -select-source');
	foreach ($sourceList as $key => $value) {
		$ui->add('<a href="javascript:void(0)" data-url="'.$key.'" data-set="'.$value['set'].'">'.$value['title'].'</a>');
	}

	$ret .= $ui->build();

	$ret .= '<script type="text/javascript">
	$(".ui-menu.-select-source a").click(function() {
		console.log($(this).data("url"))
		host = $(this).data("url")
		projectSet = $(this).data("set")
		$("#nav-select-source").text($(this).text())
		$.colorbox.close()
		$("#map-form").submit()
	})
	</script>';
	return $ret;
}

function __selectGood() {
	$ret .= '<header class="header -box"><h3>Select source</h3></header>';

	$sourceList = array(
		'inno.1' => '1. การเกษตรในโรงเรียน',
		'inno.2' => '2. สหกรณ์นักเรียน',
		'inno.3' => '3. การบริหารจัดการอาหารของโรงเรียน',
		'inno.4' => '4. การเฝ้าระวังและติดตามภาวะโภชนาการ และสมรรถภาพทางกายนักเรียน',
		'inno.5' => '5. การพัฒนาสุขนิสัยและการส่งเสริมสุขภาพอนามัยนักเรียน',
		'inno.6' => '6. การพัฒนาอนามัยสิ่งแวดล้อมของโรงเรียนให้ถูกสุขลักษณะ',
		'inno.7' => '7. การจัดบริการสุขภาพนักเรียน',
		'inno.8' => '8. การจัดการเรียนรู้เกษตร สหกรณ์ อาหาร โภชนาการและสุขภาพอนามัย',
		'inno.9' => '9. อื่น ๆ',
		'5.1' => '1. เกิดความรู้ หรือ นวัตกรรมชุมชน',
		'5.2' => '2. เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'5.3' => '3. การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ',
		'5.4' => '4. การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'5.5' => '5. เกิดกระบวนการชุมชน',
		'5.6' => '6. มิติสุขภาวะปัญญา/สุขภาวะทางจิตวิญญาณ',
	);

	$ui = new Ui(NULL, 'ui-menu -select-source');
	foreach ($sourceList as $key => $value) {
		$ui->add('<a href="javascript:void(0)" data-good="'.$key.'">'.$value.'</a>');
	}

	$ret .= $ui->build();

	$ret .= '<script type="text/javascript">
	$(".ui-menu.-select-source a").click(function() {
		goodType = $(this).data("good")
		$.colorbox.close()
		$("#map-form").submit()
	})
	</script>';
	return $ret;
}
?>