<?php
/**
* Project :: Map of Follow
* Created 2021-07-28
* Modify  2021-10-02
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/map/follow
*/

$debug = true;

class ProjectMapFollow extends Page {
	public $title;
	public $group;
	public $sector;
	public $year;
	public $plan;
	public $type;
	public $approve;
	public $rating;
	public $like;

	function __construct() {
		$this->title = post('q');
		$this->group = post('g');
		$this->sector = post('s');
		$this->year = post('yy');
		$this->plan = post('p');
		$this->type = post('type');
		$this->approve = post('approve');
		$this->rating = post('rate');
		$this->like = post('like');
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่ติดตามโครงการ',
			]),
			'body' => new Container([
				'class' => 'mapping -project-follow',
				'children' => [
					new Form([
						'action' => url('project/map/follow'),
						'id' => 'map-form',
						'class' => 'map-form',
						'method' => 'GET',
						'data-query' => url('project/api/marker/follow'),
						'children' => [
							'<a id="nav-select-maptype" class="btn -fill" href="javascript:void(0)" onclick="setMapType()"><i class="icon -material">place</i><span>Pin Map</span></a>',
							'q' => [
								'type' => 'text',
								'class' => '-fill',
								'value' => $this->title,
								'placeholder' => 'ระบุชื่อโครงการ',
							],
							'type' => [
								'type' => 'select',
								'class' => '-fill',
								'options' => array('โครงการ' => 'โครงการ', 'ชุดโครงการ' => 'ชุดโครงการ', 'แผนงาน' => 'แผนงาน'),
								'value' => $this->type,
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
								'options' => [''=>'== ทุกปี =='] + mydb::select('SELECT DISTINCT `pryear`, CONCAT("พ.ศ. ", `pryear` + 543) `yearText` FROM %project% WHERE `prtype` = "โครงการ" AND `pryear` IS NOT NULL ORDER BY `pryear` DESC; -- {key: "pryear", value: "yearText"}')->items,
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
					// $this->mapDoc(),
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
			/* .gm-style>div>div>div>div>div>div img {width: 24px !important; height: 24px !important;} */
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
			para.type = $("#edit-type").val()
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

	function mapDoc() {
		return '<title>MarkerClusterer for Google Maps v3 version 1.0 Reference</title>
			<style>
			  body {
			    font-family: arial, sans-serif;
			    background-color: #fff;
			    font-size: small;
			    margin: 24px 8px 8px;
			    color: #000;
			  }
			  h1, h2, h3, h4, h5 {
			    font-weight: bold;
			    margin-bottom: 0;
			  }
			  h2, h3, h4, h5 {
			    margin-left: 25px;
			  }
			  h1 {
			    font-size: 130%;
			    margin: 2em 0 0 10px;
			    padding: 0 3px 0 3px;
			    border-top: 1px solid;
			    background-color: #e5ecf9;
			    border-color: #3366CC;
			  }
			  h2 {
			    font-size: 120%;
			    margin-top: 1.5em;
			    border-bottom: 1px solid;
			    border-color: #3366CC;
			  }
			  h3 {
			    font-size: 110%;
			    margin-top: 0.7em;
			    position: relative;
			    left: 0;
			    top: 0.7em;
			    z-index: 5; /*to avoid falling behind other elements due to lowered position*/
			  }
			  h4 {
			    margin-top: .5em;
			    font-size: 100%;
			    font-weight: bold;
			    position: relative;
			    left: 0;
			    top: 0.8em;
			    z-index: 5; /*to avoid falling behind other elements due to lowered position*/
			  }
			  h5 {
			    margin-top: 0.4em;
			    font-size: 100%;
			    font-weight: 100;
			    font-style: italic;
			    text-decoration: underline;
			    position: relative;
			    left: 0;
			    top: 0.8em;
			    z-index: 5; /*to avoid falling behind other elements due to lowered position*/
			  }
			  p {
			    margin: 1em 0 0 25px;
			    padding: 0;
			  }
			  table {
			    border: 1px solid;
			    border-color: #3366CC;
			    border-spacing:0;
			    margin: 1em 0 0 26px;
			    border-collapse: collapse;
			    clear: right;
			  }
			  pre {
			    margin-left: 2em;
			  }
			  ol, ul {
			    margin-left: 2em;
			  }
			</style>
			<h1>MarkerClusterer for Google Maps v3</h1>
			<p>
			The library creates and manages per-zoom-level clusters for large amounts of
			markers.
			<br/>
			This is a v3 implementation of the
			<a href="http://gmaps-utility-library-dev.googlecode.com/svn/tags/markerclusterer/">v2 MarkerClusterer</a>.</p>
			<p>For a description and examples of how to use this library, check out the <a href="examples.html">how-to</a>.</p>

			  <h2><a name="MarkerClusterer"></a>class MarkerClusterer</h2>
			  <p>  This class extends <code>google.maps.OverlayView</code>.</p>

			    <h3>Constructor</h3>
			    <table summary="class MarkerClusterer - Constructor" width="90%">

			      <tbody>
			        <tr>
			          <th>Constructor</th>

			          <th>Description</th>
			        </tr>

			          <tr class="odd">
			            <td><code>MarkerClusterer(<span class="type">map:google.maps.Map</span>, <span class="type">opt_markers:Array.&lt;google.maps.Marker&gt;</span>, <span class="type">opt_options:Object</span>)</code></td>

			            <td>A Marker Clusterer that clusters markers.</td>
			          </tr>

			      </tbody>
			    </table>

			    <h3>Methods</h3>
			    <table summary="class MarkerClusterer - Methods" width="90%">

			      <tbody>
			        <tr>
			          <th>Methods</th>


			              <th>Return&nbsp;Value</th>


			          <th>Description</th>
			        </tr>

			          <tr class="odd">
			            <td><code>addMarker(<span class="type">marker:google.maps.Marker</span>, <span class="type">opt_nodraw:boolean</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Adds a marker to the clusterer and redraws if needed.</td>
			          </tr>

			          <tr class="even">
			            <td><code>addMarkers(<span class="type">markers:Array.&lt;google.maps.Marker&gt;</span>, <span class="type">opt_nodraw:boolean</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Adds an array of markers to the clusterer.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>clearMarkers()</code></td>


			                <td><code>None</code></td>


			            <td>Clears all clusters and markers from the clusterer.</td>
			          </tr>

			          <tr class="even">
			            <td><code>getCalculator()</code></td>


			                <td><code>function(Array|number)</code></td>


			            <td>Gets the calculator function.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>getExtendedBounds(<span class="type">bounds:google.maps.LatLngBounds</span>)</code></td>


			                <td><code>google.maps.LatLngBounds</code></td>


			            <td>Extends a bounds object by the grid size.</td>
			          </tr>

			          <tr class="even">
			            <td><code>getGridSize()</code></td>


			                <td><code>number</code></td>


			            <td>Gets the size of the grid.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>getMap()</code></td>


			                <td><code>google.maps.Map</code></td>


			            <td>Gets the google map that the clusterer is associated with.</td>
			          </tr>

			          <tr class="even">
			            <td><code>getMarkers()</code></td>


			                <td><code>Array.<google.maps.Marker></code></td>


			            <td>Gets the array of markers in the clusterer.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>getMaxZoom()</code></td>


			                <td><code>number</code></td>


			            <td>Gets the max zoom for the clusterer.</td>
			          </tr>

			          <tr class="even">
			            <td><code>getStyles()</code></td>


			                <td><code>Object</code></td>


			            <td>Gets the styles.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>getTotalClusters()</code></td>


			                <td><code>number</code></td>


			            <td>Gets the number of clusters in the clusterer.</td>
			          </tr>

			          <tr class="even">
			            <td><code>getTotalMarkers()</code></td>


			                <td><code>Array.<google.maps.Marker></code></td>


			            <td>Gets the array of markers in the clusterer.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>isZoomOnClick()</code></td>


			                <td><code>boolean</code></td>


			            <td>Whether zoom on click is set.</td>
			          </tr>

			          <tr class="even">
			            <td><code>redraw()</code></td>


			                <td><code>None</code></td>


			            <td>Redraws the clusters.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>removeMarker(<span class="type">marker:google.maps.Marker</span>)</code></td>


			                <td><code>boolean</code></td>


			            <td>Removes a marker from the cluster.</td>
			          </tr>

			          <tr class="even">
			            <td><code>resetViewport()</code></td>


			                <td><code>None</code></td>


			            <td>Clears all existing clusters and recreates them.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>setCalculator(<span class="type">calculator:function(Array|number)</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Sets the calculator function.</td>
			          </tr>

			          <tr class="even">
			            <td><code>setGridSize(<span class="type">size:number</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Sets the size of the grid.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>setMap(<span class="type">map:google.maps.Map</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Sets the google map that the clusterer is associated with.</td>
			          </tr>

			          <tr class="even">
			            <td><code>setMaxZoom(<span class="type">maxZoom:number</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Sets the max zoom for the clusterer.</td>
			          </tr>

			          <tr class="odd">
			            <td><code>setStyles(<span class="type">styles:Object</span>)</code></td>


			                <td><code>None</code></td>


			            <td>Sets the styles.</td>
			          </tr>

			      </tbody>
			    </table>
			';
	}
}
?>
