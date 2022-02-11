<?php
/**
* Project Proposal Mapping
* @param Object $self
* @return String
*/
function project_map_proposal($self) {
	$goodGroup=post('g');
	$getSector = post('s');
	$getPlan = post('p');
	$getYear = post('yy');

	R::View('project.toolbar', $self, 'แผนที่พัฒนาโครงการ', 'map');

	$ret='<div class="mapping -project-proposal">';

	$gis['center']='13.6044,80.0000';
	$gis['zoom']=6;


	$ret .= '<div class="map-nav">';
	$form = new Form(NULL, url('project/map/proposal'), 'map-form');
	$form->addData('query',url('project/api/marker/proposal'));
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

	$sectorList = mydb::select('SELECT DISTINCT o.`sector` FROM %project_dev% p LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %db_org% o USING(`orgid`) HAVING `sector` != ""');
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

	$yearList = mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% WHERE `pryear` IS NOT NULL ORDER BY `pryear` ASC');
	$options = array(''=>'== ทุกปี ==');
	foreach ($yearList->items AS $rs) $options[$rs->pryear] = 'พ.ศ. '.($rs->pryear+543);
	$form->addField(
					'yy',
					array(
						'type' => 'select',
						'class' => '-fill',
						'options' => $options,
						'value' => $getYear,
					)
				);

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
	.mapping {position:relative;}
	.mapping .map-nav {width: 200px; padding:8px;position: absolute; z-index:1; top:64px; left: 10px; border-radius:2px; background-color:#fff; opacity:0.9;}
	.infowindow {width: 240px;}
	.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
	</style>';


	$ret .= '<script type="text/javascript">
	var markerUrl = $("#map-form").data("query")
	var infoWindow = null
	var activeInfoWindow = null
	var map
	var markerCluster
	var markers

	$("#map-form").submit(function() {
		para = {}
		para.s = $("#edit-s").val()
		para.yy = $("#edit-yy").val()
		para.p = $("#edit-p").val()
		para.rate = $("#edit-rate").val()
		para.q = $("#edit-q").val()
		para.like = $("#edit-like-1").is(":checked") ? 1 : null
		//console.log(para)
		loadMarker(markerUrl, para)
		return false
	})


	function initMap() {
		map = new google.maps.Map(document.getElementById("map"), {
			zoom: 6,
			center: {lat: 13.000, lng: 100.000}
		});
	}

	function loadMarker(markerUrl, para) {
		// Add some markers to the map.
		// Note: The code uses the JavaScript Array.prototype.map() method to
		// create an array of markers based on a given "locations" array.
		// The map() method here has nothing to do with the Google Maps API.


		$.each( markers, function(i, marker) {
			map.removeMarker(marker);
		});
		if (markerCluster) markerCluster.clearMarkers();

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

			var markers = locations.map(function(location, i) {
				var nodeUrl = url+"project/develop/"+location.tpid
				var html = "<div class=\"infowindow\"><h3><a href=\""+nodeUrl+"\" target=_blank>"+location.title+"</a></h3><p>"+location.name+"</p><div class=\"more-detail -sg-text-right\"><a class=\"sg-action btn -link\" href=\""+nodeUrl+"/info.short"+"\" data-rel=\"box\" data-width=\"600\">MORE <i class=\"icon -material\">chevron_right</i></a></div><nav class=\"nav -page\"><a class=\"btn\" href=\""+nodeUrl+"\" target=_blank><i class=\"icon -material\">pageview</i><span>รายละเอียด</span></a></nav></div>"
				//console.log(html)
				var infoWindow = new google.maps.InfoWindow({content: html})
				var marker = new google.maps.Marker({
					position: location,
					title : location.title,
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
			markerCluster = new MarkerClusterer(map, markers,
				{
					imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m",
					maxZoom : 9,
				});

		},"json");
	}

	$.getScript("https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js", function(data, textStatus, jqxhr) {
		loadGoogleMaps("initMap")
	})
	</script>';

	return $ret;
}
?>