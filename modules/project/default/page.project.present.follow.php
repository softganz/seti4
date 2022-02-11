<?php
/**
* Project Follow Mapping
* @param Object $self
* @return String
*/
function project_present_follow($self) {
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
	$ui->add('<a class="btn -link" href="'.url('project/present/situation').'"><i class="icon -material">trending_up</i><span>สถานการณ์</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/planning').'"><i class="icon -material">dashboard</i><span>แผนงาน</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('project/present/follow').'"><i class="icon -material">directions_run</i><span>ติดตามประเมินผล</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/valuation').'"><i class="icon -material">how_to_reg</i><span>ประเมินคุณค่า</span></a>');

	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$ret .= '<div class="mapping -project-present">';

	$gis['center']='13.6044,80.0000';
	$gis['zoom']=6;



	$ret .= '<div class="map-nav">';
	$form = new Form(NULL, url('project/map/follow'), 'map-form');
	$form->addData('query',url('project/api/marker/follow'));
	$form->addData('query','https://happynetwork.org/project/api/marker/follow');
	$form->addConfig('method', 'GET');

	$sourceList = array(
		'https://dekthaikamsai.com/' => 'เด็กไทยแก้มใส',
		'https://happynetwork.org/|101' => 'ชุมชนน่าอยู่',
		'https://localfund.happynetwork.org/' => 'กองทุนตำบล',
		'https://ppi.psu.ac.th/|74' => 'ศวสต.',
		'https://ppi.psu.ac.th/|214' => 'แผนงานอาหาร',
		'https://ppi.psu.ac.th/|393' => 'อาสาประชารัฐ',
		'https://ppi.psu.ac.th/|396' => 'นวัตกรรมชุมชน',
	);

	$form->addText('<div class="form-item"><a id="nav-select-maptype" class="btn -fill" href="javascript:void(0)" onclick="setMapType()"><i class="icon -material">place</i><span>Pin Map</span></a></div>');

	$form->addField(
		'host',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $sourceList,
		)
	);

	$form->addField(
					'q',
					array(
						'type' => 'text',
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อโครงการ',
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
	html, body, .page.-main {height: 100%;}
	.mapping.-project-present {height: calc( 100% - 40px);}
	.module-project .app-output {height: 100%;}
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
	var mapType = "Pin Map"
	var map
	var markerCluster
	var markers
	var pinMarkers = {}
	var infoWindow = null

	function setMapType() {
		if (mapType == "Cluster Map") mapType = "Pin Map"
		else mapType = "Cluster Map"
		$("#nav-select-maptype").html("<i class=\"icon -material\">"+(mapType == "Pin Map" ? "place" : "donut_small")+"</i><span>"+mapType+"</span>")
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

	$("#map-form .form-select").change(function() {
		$("#map-form").submit()
	})

	$("#map-form").submit(function() {
		para = {}
		var res = $("#edit-host").val().split("|",10)
		console.log(res)
		host = res[0]
		para.set = res[1]
		para.g = goodType
		para.s = $("#edit-s").val()
		para.yy = $("#edit-yy").val()
		para.p = $("#edit-p").val()
		para.rate = $("#edit-rate").val()
		para.q = $("#edit-q").val()
		para.like = $("#edit-like-1").is(":checked") ? 1 : null
		console.log(host)
		console.log(para)

		markerUrl = host+queryUrl
		loadMarker(markerUrl, para)
		return false
	})


	function initMap() {
		map = new google.maps.Map(document.getElementById("map"), {
			zoom: 6,
			center: {lat: 13.000, lng: 100.000}
		});

		infoWindow = new google.maps.InfoWindow()
		google.maps.event.addListener(map, "click", function(event) {
			infoWindow.close()
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
				var html = "<div class=\"infowindow\"><h3><a href=\""+nodeUrl+"\" target=_blank>"+location.title+"</a></h3><p>"+location.name+"</p><div class=\"more-detail -sg-text-right\"><!-- <a class=\"sg-action btn -link\" href=\""+nodeUrl+"/info.short"+"\" data-rel=\"box\" data-width=\"600\">MORE <i class=\"icon -material\">chevron_right</i></a> --></div><nav class=\"nav -map -sg-text-right\"><a class=\"btn -link\" href=\""+nodeUrl+"\" target=_blank><i class=\"icon -material\">find_in_page</i><span>รายละเอียดโครงการ</span></a></nav></div>"
				//console.log(html)


				var marker = new google.maps.Marker({
					position: location,
					title : location.title,
					content : html,
					//label: location.title, //labels[i % labels.length]
				})

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
						pinMarkers[i].addListener("click", function() {
							if (infoWindow) infoWindow.close()
							infoWindow.setContent(marker.content)
							infoWindow.open(map, pinMarkers[i])
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