<?php
/**
* Project Follow Mapping
* @param Object $self
* @return String
*/
function project_set_map($self, $projectInfo) {
	$tpid = $projectInfo->tpid;
	$goodGroup=post('g');
	$getSector = post('s');
	$getPlan = post('p');
	$getYear = post('yy');

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$isInnovationProject = $projectInfo->settings->type === 'INNO';


	$ret .= '<div class="mapping -project-present">';

	$gis['center']='13.6044,80.0000';
	$gis['zoom']=6;


	$ui = new Ui();

	$ui->add('<a id="nav-select-maptype" class="btn" href="javascript:void(0)" onclick="setMapType()"><i class="icon -material">place</i><span>Pin Map</span></a>');


	$ret .= '<div class="map-nav">';

	$ret .= '<nav class="">'.$ui->build().'</nav>';

	$form = new Form(NULL, url('project/set/'.$tpid.'/map'), 'map-form');
	//$form->addData('query',url('project/api/marker/follow'));
	//$form->addData('query','https://happynetwork.org/project/api/marker/follow');
	$form->addConfig('method', 'GET');

	$sourceList = array(
		url().'|'.$tpid => 'นวัตกรรม',
		//'http://hsmi2.psu.ac.th/|'.$tpid => 'นวัตกรรม',
	);

	$form->addField(
		'host',
		array(
			'type' => 'select',
			'class' => '-fill -hidden',
			//'style' => 'display: none',
			'options' => $sourceList,
		)
	);

	$form->addField(
					'q',
					array(
						'type' => 'text',
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อ{tr:โครงการ}',
					)
				);


	$yearList = mydb::select('SELECT `pryear` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `parent` = :parent AND `prtype` = "โครงการ" AND `pryear` IS NOT NULL GROUP BY p.`pryear` ORDER BY `pryear` ASC', ':parent', $tpid);

	$options = array(''=>'== ทุกปี ==');
	foreach ($yearList->items as $rs) $options[$rs->pryear] = 'พ.ศ. '.($rs->pryear+543);
	$form->addField(
		'yy',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $options,
			'value' => $getYear,
		)
	);

	$stmt = 'SELECT
		tg.`catid`,tg.`name`
		FROM %tag% tg
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2';

	$issueDbs = mydb::select($stmt);

	$optionsIssue = array('' => '== ทุกประเด็น ==');
	foreach ($issueDbs->items as $item) $optionsIssue[$item->catid] = $item->name;

	$form->addField(
		'issue',
		array(
			'type' => 'select',
			'options' => $optionsIssue,
			'class' => '-fill',
			'value' => $getIssue,
		)
	);

	if ($isInnovationProject) {
		$stmt = 'SELECT
			  tg.`catid`, tg.`catparent`, tg.`name`
			FROM %tag% tg
			WHERE tg.`taggroup` = "project:inno" AND tg.`process` = 1';
		$innoDbs=mydb::select($stmt,':tpid',$tpid);
		//$ret .= print_o($innoDbs, '$innoDbs');

		$optionsInno = array(
					'' => '== ทุก{tr:โครงการ} ==',
				);
		foreach ($innoDbs->items as $key => $value) {
			if ($value->catparent) $optionsInno[$value->catid] = $value->name;
		}

		$form->addField(
			'inno',
			array(
				'type' => 'select',
				'options' => $optionsInno,
				'class' => '-fill',
				'value' => $getInno,
			)
		);
	}

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
	.page.-page {height: 100%;}
	.module-project.-inno .page.-content {height: 100%;}
	.page.-primary {height: 100%;}
	.mapping.-project-present {height: calc( 100% - 96px);}
	.module-project .app-output {height: 100%;}
	.package-footer {display: none;}
	.nav.-page {margin: 0; padding: 2px; background-color: #fff;}
	.mapping {position:relative;}
	.mapping .map-nav {width: 200px; padding:8px;position: absolute; z-index:1; top:60px; left: 10px; border-radius:2px; background-color:#fff; opacity:0.9;}
	.infowindow {width: 240px;}
	.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
	.btn.-active {}
	.notify-main {top: 40px;}
	</style>';


	$ret .= '<script type="text/javascript">
	var host = "http://hsmi2.psu.ac.th/"
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
		if (mapType == "Cluster Map") mapType = "<i class=\"icon -material\">place</i><span>Pin Map</span>"
		else mapType = "Cluster Map"
		$("#nav-select-maptype").html(mapType)
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
		host = res[0]
		para.set = res[1]
		para.yy = $("#edit-yy").val()
		para.inno = $("#edit-inno").val()
		para.issue = $("#edit-issue").val()
		para.rate = $("#edit-rate").val()
		para.q = $("#edit-q").val()
		para.like = $("#edit-like-1").is(":checked") ? 1 : null

		markerUrl = host+queryUrl
		console.log("HOST = "+markerUrl)
		console.log("PARA", para)

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
			console.log(data)

			var locations = []

			for (key in data.markers) {
				// console.log(data.markers[key])
				locations.push(data.markers[key])
			}
			//console.log(locations)

			markers = locations.map(function(location, i) {
				var nodeUrl = host+"project/"+location.tpid
				var html = "<div class=\"infowindow\"><h3><a href=\""+nodeUrl+"\" target=_blank>"+location.title+"</a></h3><p>"+location.name+"</p><div class=\"more-detail -sg-text-right\"><!-- <a class=\"sg-action btn -link\" href=\""+nodeUrl+"/info.short"+"\" data-rel=\"box\" data-width=\"600\">MORE <i class=\"icon -material\">chevron_right</i></a> --></div><nav class=\"nav -page\"><a class=\"btn\" href=\""+nodeUrl+"\" target=_blank><i class=\"icon -material\">pageview</i><span>รายละเอียด{tr:โครงการ}</span></a></nav></div>"
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
?>