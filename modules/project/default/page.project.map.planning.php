<?php
/**
* Project :: Map of Planning
* Created 2021-07-28
* Modify  2021-10-03
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/map/planning
*/

$debug = true;

class ProjectMapPlanning extends Page {
	public $title;
	public $group;
	public $sector;
	public $year;
	public $plan;
	public $approve;
	public $rating;
	public $like;

	function __construct() {
		$this->title = post('q');
		$this->group = post('g');
		$this->sector = post('s');
		$this->year = post('yy');
		$this->plan = post('p');
		$this->approve = post('approve');
		$this->rating = post('rate');
		$this->like = post('like');
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่แผนงาน',
			]),
			'body' => new Container([
				'class' => 'mapping -project-planning',
				'children' => [
					new Form([
						'action' => url('project/map/planning'),
						'id' => 'map-form',
						'class' => 'map-form',
						'data-query' => url('project/api/marker/planning'),
						'method' => 'GET',
						'children' => [
							'<a id="nav-select-maptype" class="btn -fill" href="javascript:void(0)" onclick="setMapType()"><i class="icon -material">place</i><span>Pin Map</span></a>',
							'q' => [
								'type' => 'text',
								'class' => '-fill',
								'value' => $this->title,
								'placeholder' => 'ระบุชื่อแผนงาน',
							],
							's' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => [''=>'== ทุกองค์กร =='] + project_base::$orgTypeList,
								'value' => $this->sector,
							],
							'yy' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => [''=>'== ทุกปี =='] + mydb::select('SELECT DISTINCT `pryear`, CONCAT("พ.ศ. ", `pryear` + 543) `yearText` FROM %project% WHERE `prtype` = "แผนงาน" AND `pryear` IS NOT NULL ORDER BY `pryear` DESC; -- {key: "pryear", value: "yearText"}')->items,
								'value' => $this->year,
							],
							'p' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => [''=>'== ทุกแผนงาน =='] + mydb::select('SELECT * FROM %tag% WHERE `taggroup`="project:planning" ORDER BY `weight` ASC, `catid` ASC; -- {key: "catid", value: "name"}')->items,
								'value' => $this->plan,
							],
							'approve' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => ['' => '== ทุกสถานะ ==', 'MASTER' => 'ต้นแบบ', 'USE' => 'นำไปใช้', 'LEARN' => 'เรียนรู้'],
								'value' => $this->approve,
							],
							'rate' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => [''=>'== ทุกระดับคะแนน ==', 5 => 'ระดับคะแนน > 5', 4 => 'ระดับคะแนน > 4', 3 => 'ระดับคะแนน > 3', 2 => 'ระดับคะแนน > 2', 1 => 'ระดับคะแนน > 1'],
								'value' => $this->rating,
							],
							'like' => [
								'type' => 'checkbox',
								'options' => [1 => 'มีคนชอบ'],
								'value' => $this->like,
							],
							'submit' => [
								'type' => 'button',
								'value' => '<i class="icon -material">search</i><span>GO</span>',
								'container' => ['class' => '-sg-text-right'],
							],
						], // children
					]), // Form
					'<div id="map" class="app-output">กำลังโหลดแผนที่!!!!</div>',
					$this->script(),
				], // children
			]), // Container
		]);
	}

	function script() {
		head(
			'<style type="text/css">
			.map-form {width: 200px; padding:8px;position: absolute; z-index:1; top:64px; left: 10px; border-radius:2px; background-color:#fff; opacity:0.9;}
			.map-form .form-item {padding: 8px 0;}
			.map-form .form-text, .map-form .form-select {border-radius: 32px;}
			.mapping {position:relative;}
			.infowindow {width: 240px;}
			.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
			</style>'
		);

		return '<script type="text/javascript">
		var markerUrl = $("#map-form").data("query")
		var infoWindow = null
		var activeInfoWindow = null
		var mapType = "Pin Map"
		var map
		var markerCluster
		var markers
		var pinMarkers = {}

		function setMapType() {
			if (mapType == "Cluster Map") {
				mapType = "Pin Map"
				$("#nav-select-maptype").html("<i class=\"icon -material\">place</i><span>"+mapType+"</span>")
			} else {
				mapType = "Cluster Map"
				$("#nav-select-maptype").html("<i class=\"icon -material\">gps_fixed</i><span>"+mapType+"</span>")
			}
			showMarker()
		}

		$("#map-form").submit(function() {
			para = {}
			para.s = $("#edit-s").val()
			para.yy = $("#edit-yy").val()
			para.p = $("#edit-p").val()
			para.rate = $("#edit-rate").val()
			para.q = $("#edit-q").val()
			para.like = $("#edit-like-1").is(":checked") ? 1 : null
			para.approve = $("#edit-approve").val()
			// console.log(para)
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
		}

		function loadMarker(markerUrl, para) {
			// Add some markers to the map.
			// Note: The code uses the JavaScript Array.prototype.map() method to
			// create an array of markers based on a given "locations" array.
			// The map() method here has nothing to do with the Google Maps API.

			const icons = {
				MASTER: "https://maps.google.com/mapfiles/kml/paddle/grn-stars.png",
				USE: "http://maps.google.com/mapfiles/kml/paddle/ylw-diamond.png",
				LEARN: "https://maps.google.com/mapfiles/kml/paddle/wht-circle.png",
			};

			markers = null

			notify("LOADING")

			$.get(markerUrl, para, function (data) {
				notify()
				var locations = []

				for (key in data.markers) {
					locations.push(data.markers[key])
				}
				//console.log(locations)

				markers = locations.map(function(location, i) {
					var nodeUrl = "'.url('project').'/"+location.tpid
					var html = "<div class=\"infowindow\"><h3><a href=\""+nodeUrl+"\" target=_blank>"+location.title+"</a></h3><p>"+location.name+"</p><div class=\"more-detail -sg-text-right\"><a class=\"sg-action btn -link\" href=\""+nodeUrl+"/info.short"+"\" data-rel=\"box\" data-width=\"600\"><span>MORE INFO </span><i class=\"icon -material\">chevron_right</i></a></div><nav class=\"nav -map -sg-text-right\"><a class=\"btn -link\" href=\""+nodeUrl+"\" target=_blank><i class=\"icon -material\">find_in_page</i><span>รายละเอียดโครงการ</span></a></nav></div>"
					// console.log(html)

					infoWindow = new google.maps.InfoWindow({content: html})

					let marker = new google.maps.Marker({
						position: location,
						title : location.title,
						content: html,
						// icon: icons[location.approve],
						icon: {"url": icons[location.approve], scaledSize: new google.maps.Size(30, 30)},
						// icon: new google.maps.MarkerImage(icons[location.approve], new google.maps.Size(2, 2)),
						// label: location.title, //labels[i % labels.length]
					})

					marker.addListener("click", function() {
						activeInfoWindow && activeInfoWindow.close();
						infoWindow.open(map, marker);
						activeInfoWindow = infoWindow
					});

					return marker
				});

				showMarker()
			},"json");
		}

		function showMarker() {
			if (!map) return
			// Add a marker clusterer to manage the markers.
			$.each(pinMarkers, function(i, marker) {
				marker.setMap(null)
			});

			if (markerCluster) markerCluster.clearMarkers();

			if (mapType == "Cluster Map") {
				markerCluster = new MarkerClusterer(map, null, {
					imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
					maxZoom : 9,
				});
				markers.map((marker, i) => {
					markerCluster.addMarker(marker)
				});
			} else {
				markers.map((marker, i) => {
					pinMarkers[i] = new google.maps.Marker({
						position: marker.position,
						map: map,
						title: marker.title,
						icon: marker.icon,
					});
					infoWindow = new google.maps.InfoWindow({content: marker.content})
					pinMarkers[i].addListener("click", function() {
						activeInfoWindow && activeInfoWindow.close();
						infoWindow.open(map, pinMarkers[i]);
						activeInfoWindow = infoWindow
					});
				})
			}
		}

		$.getScript("https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js", function(data, textStatus, jqxhr) {
			loadGoogleMaps("initMap")
		})
		</script>';
	}
}
?>
