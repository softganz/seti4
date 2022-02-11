var $mapCanvas;
var map;
var markers=new Array();
var mapCount=0;
var currentLoc={};
var alwayShow=true;
var addEnable=true;
var addMarker;
var imgSize = new google.maps.Size(24, 48);
var editZoom=16;
var gis=new Array();
var mapGroup;
var addUrl;
var welcomeMarker;
var polygon=new Array(Array(),Array());

var gis={"center":"12.5000,101.9000","zoom":8,"markers":[]};

function loadMap(mapno,element) {
	notify('กำลังโหลดข้อมูล ('+mapno+')...')
	element.prop('disabled',true)
	var backgroundColor='';
	var pin;
	var mapType = element.data('type')

	/*
	var backgroundColor='#'+(Math.random()*0xFFFFFF<<0).toString(16).slice(-3)
	//var backgroundColor='hsla(' + Math.floor(Math.random()*360) + ', 100%, 70%, 1)';
	//alert($('label[for="shp-'+mapno+'"]').attr("for"))
	$('label[for="shp-'+mapno+'"]').css('color',backgroundColor)
	*/
	var $label=element.next().find('.map-gis-color');
	//var $labelSpan=$label.find('.map-gis-color');
	if ($label.data('color')!=undefined) {
		backgroundColor=$label.data('color');
	}
	//alert(backgroundColor);

	var $pin=element.next().find('.map-gis-pin');
	if ($pin.attr('src')!=undefined) {
		pin=$pin.attr('src');
	}

	var request = $.ajax({
		type: "GET",
		url: url+"?map/gis&layer="+mapno+'&type='+mapType,
		dataType: "json",
		timeout: 60*1000,
		success:
			function (paths) {
				var gisProp;
				gisProp=element.next().html()+'<br />';
				gisProp+='Type = '+paths.type+'<br />';
				$('.map-gis-info').html(gisProp);
				//alert('paths.length='+paths.type)
				/*
				alert(paths.coordinates[0][0][0])
				polygon = map.drawPolygon({
				paths: paths,
				useGeoJSON: true,
				strokeOpacity: 1,
				strokeWeight: 3,
				fillColor: '#BBD8E9',
				fillOpacity: 0.6
				});
				*/

				var i= 0;
				var cmapno=mapno
				polygon[mapno]=new Array()
				$.each(paths.coordinates, function(a,rings) {
					//notify(rings.dbf.TAM_NAM_T)
					//$.each(rings.points, function (name,path) {
						var eachBackgroundColor='';
						eachBackgroundColor=backgroundColor!=''?backgroundColor:'#'+(Math.random()*0xFFFFFF<<0).toString(16).slice(-3);

						var show='<a class="sg-action" href="?map/gis&layer='+mapno+'" data-rel="box">Load Pooint</a><table class="item"><tbody>';
						show=show+'<tr><td>Type</td><td>'+paths.type+'</td></tr>';
						$.each(rings.dbf, function(key, element) {
							if (key!='deleted') show=show+ '<tr><td>'+key+'</td><td>' + element+'</td></tr>';
						});
						show=show+'</tbody></table>';
						if (paths.type=='PolyLine') {
							//				$.each(path, function (lname,line) {
								//alert(paths.type+' : '+line)
								polygon[mapno][++i] = map.drawPolyline({
									path: rings.path, // pre-defined polygon shape
									strokeColor: eachBackgroundColor,
									strokeOpacity: 1,
									strokeWeight: 0.5,
									clickable: true,
									click: function(e) {$('.map-gis-info').html(show)},
									//infoWindow: {content: "Hello",closeclick: function() {}},
									//click: function(e) {map.showInfoWindow(this);},
								});
							//				});
						} else if (paths.type=='Point') {
							var p=map.addMarker({
											lat: rings.path[0][0],
											lng: rings.path[0][1],
											title: 'Lima',
											icon: pin,
											click: function(e) {$('.map-gis-info').html(show)},
										});
							polygon[mapno][++i]=p
						} else {
							var p=map.drawPolygon({
								paths: rings.path, // pre-defined polygon shape
								strokeColor: eachBackgroundColor,
								strokeOpacity: 1,
								strokeWeight: 0.5,
								fillColor: eachBackgroundColor,
								fillOpacity: .6,
								//clickable: true,
								//infoWindow: {content: "Hello",closeclick: function() {}},
								click: function(e) {$('.map-gis-info').html(show)},
							});
							//attachPolygonInfoWindow(p, '<strong>Info about this area</strong>');
							polygon[mapno][++i]=p
						}	
					//});
				});
				notify('โหลดเรียบร้อย',5000)
				element.prop('disabled',false)
			},
		 error:
		 	function(x, t, m) {
        if(t==="timeout") {
            notify("อุ๊บ... โหลดข้อมูลนานเกินไปแล้ว");
        } else {
            notify('อุ๊บ... โหลดข้อมูลผิดพลาด ('+t+' : '+m+')');
        }
       },
	});
}

function attachPolygonInfoWindow(polygon, html) {
	polygon.infoWindow = new google.maps.InfoWindow({
		content: html,
	});
	google.maps.event.addListener(polygon, 'mouseover', function(e) {
		var latLng = e.latLng;
		this.setOptions({fillOpacity:0.1});
		polygon.infoWindow.setPosition(latLng);
		polygon.infoWindow.open(map);
	});
	/*
	google.maps.event.addListener(polygon, 'mouseout', function() {
		this.setOptions({fillOpacity:0.35});
		polygon.infoWindow.close();
	});
	*/
}

$(document).on('click','.map-gis-folder', function() {
	var $this=$(this)
	$this.toggleClass('-expand')
	if ($this.hasClass('-expand')) {
		$this.next().show()
	} else {
		$this.next().hide()
	}
	//alert('Click')
});

$(document).on('change','.map-gis-layer', function() {
	var $this=$(this)
	if ($this.is(':checked')) {
		loadMap($this.data('mapno'),$this)
	} else {
		//alert(polygon.strokeColor)
		var mapno=$this.data('mapno')
		for (var poly in polygon[mapno]){
			//alert(poly)
			map.removePolygon(polygon[mapno][poly])
			map.removePolyline(polygon[mapno][poly])
			map.removeMarker(polygon[mapno][poly])
		}
	}
});

$(document).ready(function() {

	$( window ).resize(function() {
		//var h=$(window).height()-$("#map-nav").height();
		//$("#map_canvas").width($(window).width()+"px").height(h+"px");
		notify($('#content-wrapper').width())
//		notify($(window).width()+","+$(window).height()+","+$("#map_canvas").width()+","+$("#map_canvas").height());
	});

	// Set parameter from map_canvas
	$mapCanvas=$("#mapcanvas");

	var coors = gis.center.split(',');
	gis.lat=coors[0];
	gis.lng=coors[1];

	map = new GMaps({
		el: "#mapcanvas",
		lat: gis.lat,
		lng: gis.lng,
		zoom: gis.zoom-1,
		disableDoubleClickZoom: false,
		/* dblclick: function(e) {createNewMarker(e.latLng.lat(), e.latLng.lng());} */
	});

	$.ajax({
		type: "GET",
		url: url+"?map/gis/center",
		dataType: "json",
		timeout: 60*1000,
		success:
			function (coords) {
				console.log(coords)
				map.setCenter(coords.lat, coords.lng);
			},
	});
	// draw ขอบเขตลุ่มน้ำ.
	/*
	var request = $.getJSON(url+"map/gis?layer=3", function (paths) {
		$.each(paths, function (name, path) {
			//alert(name+path)
			polygon = map.drawPolygon({
				paths: path, // pre-defined polygon shape
				strokeColor: '#FF0000',
				strokeOpacity: 1,
				strokeWeight: 1,
				fillColor: '#FFFFFF',
				fillOpacity: 0.0,
				clickable: false,
			});
	  });
	});
	*/

});