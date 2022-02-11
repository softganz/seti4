// TODO: map.showInfoWindows() is error, why?

var $mapCanvas;
var map;
var markers=new Array();
var mapCount=0;
var currentLoc={};
var alwayShow=true;
var addEnable=true;
var addMarker;
var imgSize;
var editZoom=16;
var gis=new Array();
var mapGroup;
var addUrl;
var welcomeMarker;

var query=decodeURIComponent(this.location.search.substring(1));
var get=[];
if (query.length > 0){
    var params=query.split("&");
    for (var i=0 ; i < params.length ; i++){
        var pos = params[i].indexOf("=");
        var name = params[i].substring(0, pos);
        var value = params[i].substring(pos + 1);
        get[name]=value;
    }
}

/*
// คลองหลา
gis.lat=6.903335922279534;
gis.lng=100.36517143249512;
// ทุ่งลาน
gis.lat=6.897200874437799;
gis.lng=100.42181968688965

gis.zoom=18;
*/
gis.markers=new Array();

function showMarker(mapID) {
	var marker=markers[mapID];
	if (gis.markers[mapID]!=undefined) {
		map.setCenter(gis.markers[mapID].lat,gis.markers[mapID].lng);
		map.hideInfoWindows();
		//map.showInfoWindow(marker);
	}
}

function createMarker(marker) {
	var iconSize;
	if (marker.iconSize!=undefined) {
		iconSize=new google.maps.Size(marker.iconSize[0], marker.iconSize[1]);
	} else {
		iconSize=imgSize;
	}
	var isDragable=marker.draggable==undefined ? false : marker.draggable;
	newMarker=map.addMarker({
		lat: marker.lat,
		lng: marker.lng,
		icon : new google.maps.MarkerImage(marker.icon, iconSize, null, null, iconSize),
		draggable: isDragable,
		infoWindow: {content: marker.content,closeclick: function() {alwayShow=false;}},
		click: function(e) {alwayShow=true; /* map.showInfoWindow(this);*/},
		dblclick: function(e) {notify("Edit");},
		dragend: function(e) { mapUpdate(e); },
		/*
		mouseover: function(e) {if (!alwayShow) {map.showInfoWindow(this);}},
		mouseout: function(e) {if (!alwayShow) map.hideInfoWindows();},
		*/
	});
	return newMarker;
}

function createWelcomeMarker(lat, lng, title) {
	var icon='https://maps.google.com/mapfiles/arrow.png';
	var iconSize=new google.maps.Size(39, 34);
	var content='<h3>'+title+'</h3><div id="map-welcomemarker"><p>ช่วยปักหมุดบอกฉันหน่อยซิคะว่าบริเวณนี้มีอะไรที่เกี่ยวข้องกับ <strong>"'+$mapCanvas.data('mapname')+'"</strong> บ้าง</p><p>ด้วยการลากหมุดไปยังตำแหน่งดังกล่าวแล้ว<a href="'+addUrl+'" onclick="createNewMarker(currentLoc.lat,currentLoc.lng);return false;">คลิกที่นี่</a> หรือดับเบิ้ลคลิกบนแผนที่เพื่อปักหมุด</p>';

	currentLoc.lat=lat;
	currentLoc.lng=lng;
	var newMarker=map.addMarker({
		lat: lat,
		lng: lng,
		icon : new google.maps.MarkerImage(icon, iconSize, null, null, iconSize),
		draggable: true,
		infoWindow: {content: content,closeclick: function() {alwayShow=false;}},
		click: function(e) {alwayShow=true;},
		dblclick: function(e) {createNewMarker(currentLoc.lat,currentLoc.lng);},
		dragend: function(e) {
				currentLoc.lat=e.latLng.lat().toFixed(6);
				currentLoc.lng=e.latLng.lng().toFixed(6);
				$("#fq").val(currentLoc.lat+","+currentLoc.lng);
				alwayShow=true;
			},
		mouseover: function(e) {if (!alwayShow) {/*map.showInfoWindow(this);*/}},
		mouseout: function(e) {if (!alwayShow) map.hideInfoWindows();},
	});
	//console.log(map)
	//map.showInfoWindow(newMarker);
	map.hideInfoWindows()
	return newMarker;
}

function mapUpdate(e) {
	$("#fq").val(e.latLng.lat().toFixed(6)+","+e.latLng.lng().toFixed(6));
}

function setToMyLocation(location) {
	map.removeMarker(welcomeMarker);
	welcomeMarker=createWelcomeMarker(location.coords.latitude, location.coords.longitude,'บ้านคุณอยู่ตรงนี้ใช่ไหม ?');
	map.setCenter(location.coords.latitude, location.coords.longitude);
	setTimeout(function() {map.setZoom(gis.zoom+2);}, 1000);
	notify('กำหนดตำแหน่งปัจจุบันเรียบร้อย',5000);
}

function positionError(err) {
	var msg;
	switch(err.code) {
	  case err.UNKNOWN_ERROR:
	    msg = "Unable to find your location";
	    break;
	  case err.PERMISSION_DENINED:
	    msg = "Permission denied in finding your location";
	    break;
	  case err.POSITION_UNAVAILABLE:
	    msg = "Your location is currently unknown";
	    break;
	  case err.BREAK:
	    msg = "Attempt to find location took too long";
	    break;
	  default:
	    msg = "Location detection not supported in browser";
	}
	notify(msg,5000);
	//	welcomeMarker=createWelcomeMarker(gis.lat, gis.lng,'บ้านคุณอยู่ตรงไหนบนแผนที่หรือคะ ?');
	//	map.setCenter(gis.lat, gis.lng);
	setTimeout(function() {map.setZoom(gis.zoom+2);}, 1000);
}

function createNewMarker(lat, lng) {
	if (addEnable) {
		notify("กำลังเพิ่มตำแหน่งใหม่ กรุณารอสักครู่",30000);
		$.get(addUrl, {mapgroup:mapGroup}, function(data) {
			notify();
			$("#map-box").show().html(data);
			addMarker=map.addMarker({
				lat: lat,
				lng: lng,
				draggable: true,
				dragend: function(e) { $("#edit-mapping-latlng").val(e.latLng.lat()+","+e.latLng.lng()); },
			});
			$("#edit-mapping-latlng").val(lat+","+lng);
			$("#edit-mapping-who").focus();
			addEnable=false;
			if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', addUrl]);
		});
	}
}

$(document).ready(function() {

	$( window ).resize(function() {
		//var h=$(window).height()-$(".org-seedfund-main").height();
		var h=$(window).height()-$("#map-nav").height();
		//$("#map_canvas").width($(".org-seedfund-main").width()+"px").height(h+"px");
		$("#map-crowd #map_canvas").width($(window).width()+"px").height(h+"px");
		//		notify($(window).width()+","+$(window).height()+","+$("#map_canvas").width()+","+$("#map_canvas").height());
	});

	// Setup navigator bar
	$("#map-nav>ul>li>a").click(function() {
		var $this=$(this);
		var currentTab = $this.attr("href");
		if ($this.hasClass("disabled")) return false; // Do something else in here if required
		if ($this.data("action")=="refresh") return true;
		$("#map-nav>ul>li").removeClass("active");
		$(this).parent().addClass("active");
		if ($this.attr("id")=="getMyLocation") return true;
		if ($this.data("rel")) {
			return;
		} else if (currentTab.substring(0,1)=="#") {
			$(currentTab).show();
		} else {
			notify("Loading...");
			var target=$this.attr("rel-target")==undefined?"#map-box":$this.attr("rel-target");
			$(target).hide();
			var para={gr: mapGroup};
			$.get(this.href, para, function(html) {
					$(target).empty().append(html).show()
					notify();
					if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', $this.attr("href")]);
			});
		}
		return false;
	});

	// Set parameter from map_canvas
	//imgSize = new google.maps.Size(24, 48);
	$mapCanvas=$("#map_canvas");
	mapGroup=$mapCanvas.data('group');
	addUrl=$mapCanvas.data('addurl');

	var centerGis=get["c"] ? get["c"] : $mapCanvas.data("center");
	var coors = centerGis.split(',');
	gis.lat=coors[0];
	gis.lng=coors[1];
	gis.zoom =  parseInt(get["z"] ? get["z"] : $mapCanvas.data("zoom"));

	//gis.lat=12.5000;
	//gis.lng=101.9000;
	//console.log(gis.zoom);
	//gis.zoom=9;
	//height 978px

	var mapCanvasHeight = $( document ).height() - $("#map-nav").height();
	$("#map-crowd #map_canvas")
	.width($(window).width()+"px")
	.height(mapCanvasHeight + "px");
	//$("#map-box").height(mapCanvasHeight + "px");
	//$("#map-crowd #map_canvas").width($(window).width()+"px").height($(window).height()-$("#map-nav").height()+"px");

	var $mapBox = $("#map-box")
	var mapBoxPadding = parseInt($mapBox.css("padding-top")) + parseInt($mapBox.css("padding-top"))
	$mapBox.height(mapCanvasHeight+"px").css({"max-height":($mapCanvas.height() - mapBoxPadding)+"px"});

	//alert(gis.lat+" , "+gis.lng+" , "+gis.zoom);
	/*
	map = new GMaps({
		el: "#map_canvas",
		lat: gis.lat,
		lng: gis.lng,
		zoom: gis.zoom-1,
		disableDoubleClickZoom: false,
		//dblclick: function(e) {createNewMarker(e.latLng.lat(), e.latLng.lng());}
	});
	*/
	//return;

	map = new GMaps({
		el: "#map_canvas",
		lat: gis.lat,
		lng: gis.lng,
		zoom: gis.zoom-1,
		disableDoubleClickZoom: true,
		zoomControlOptions: {
              position: google.maps.ControlPosition.RIGHT_TOP
          },
		dblclick: function(e) {createNewMarker(e.latLng.lat(), e.latLng.lng());}
	});

	// draw ขอบเขตลุ่มน้ำ.
	var request = $.getJSON(url+"map/gis/utp", function (paths) {
		/*
		$.each(paths, function (name, path) {
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
	  */
	});

	var para={};
	para.gr=mapGroup;
	para.layer=$mapCanvas.data('layer');

	//console.log($mapCanvas.data("url"),para)

	$.get($mapCanvas.data("url"), para, function(data) {
		if (data.markers) {
			
			//alert(data.markers[1].content)
			$.each( data.markers, function(i, marker) {
				markers[marker.mapid]=createMarker(marker);
				gis.markers[marker.mapid]=marker;
			});

			if (get['id']) showMarker(get['id']);
			else 	welcomeMarker=createWelcomeMarker(gis.lat, gis.lng,'บ้านคุณอยู่ตรงไหนบนแผนที่หรือคะ ?');
			//map.setCenter(gis.lat, gis.lng)
			setTimeout(function() {map.setZoom(gis.zoom+1)}, 1000)

	    if (navigator.geolocation) {
				notify("กำลังหาตำแหน่งปัจจุบัน",5000);
	      navigator.geolocation.getCurrentPosition(setToMyLocation, positionError);
	    } else {
	      positionError(-1);
	    }
			//navigator.geolocation.getCurrentPosition(setToMyLocation);
			//setToMyLocation();
			
		}
	},"json");

	$("#getMyLocation").click(function() {
		navigator.geolocation.getCurrentPosition(GetLocation);
		return false;
	});

	$("#usegis").click(function() {
		var latlng = new google.maps.LatLng(currentLoc.x, currentLoc.y);
		marker.setPosition(latlng);
		partientUpdate($this=$("[fld=\'latlng\']"),currentLoc.x+","+currentLoc.y);
	});

	$("#getroute").click(function() {
		map.drawRoute({
			origin: [currentLoc.x, currentLoc.y],
			destination: [gis.marker.lat, gis.marker.lng],
			travelMode: "driving",
			strokeColor: "#009900",
			strokeOpacity: 0.6,
			strokeWeight: 6
		});
	});

	function GetLocation(location) {
		currentLoc.x=location.coords.latitude;
		currentLoc.y=location.coords.longitude;
		currentLoc.marker=map.addMarker({
			lat: currentLoc.x,
			lng: currentLoc.y,
			icon: "https://maps.google.com/mapfiles/arrow.png",
		});
		map.setCenter(currentLoc.x, currentLoc.y);
		$("#fq").val(currentLoc.x+","+currentLoc.y);
	}

	function searchLocation(locationStr) {
			GMaps.geocode({
		  address: locationStr,
		  callback: function(results, status) {
		    if (status == 'OK') {
		      var latlng = results[0].geometry.location;
		      map.setCenter(latlng.lat(), latlng.lng());
		      currentLoc.marker=map.addMarker({
					lat: latlng.lat(),
					lng: latlng.lng(),
					icon: "https://maps.google.com/mapfiles/arrow.png",
					infoWindow: {content: locationStr},
					});
				}
		  }
		});
	}

	$("body").on("click","[data-action]",function() {
		var $this=$(this);
		var action=$this.attr("data-action");
		//		alert("Action "+action+" id="+$this.attr("data-id"));
		if (action=="showmap") {
			var mapID=$this.attr("data-id");
			if (markers[mapID]==undefined) {
				$.get("map/getwho/"+mapID,function(data) {
					markers[mapID]=createMarker(data);
					gis.markers[mapID]=data;
					map.setCenter(data.lat, data.lng);
					$("#fq").val($this.attr("data-who"));
					showMarker(mapID);
				//					alert($this.attr("href"));
				}, "json");
			} else {
				$("#fq").val($this.attr("data-who"));
				showMarker(mapID);
			}
			if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', $this.attr("href")]);
		} else if (action=="clear-map") {
			notify("ล้างแผนที่เรียบร้อย",5000);
			$.each( markers, function(i, marker) {
				map.removeMarker(marker);
			});
			markers={};
		} else if (action=="load-layer") {
			$.get($this.attr('href'), function(data) {
				if (data.markers) {
					$.each( data.markers, function(i, marker) {
						if (markers[marker.mapid]==undefined) {
							markers[marker.mapid]=createMarker(marker);
						}
					});
				}
			},"json");
		} else if (action=="add-cancel") {
			notify();
			$("#map-box").hide();
			if (addMarker) {
				if ($this.attr("data-id")) {
					var mapID=$this.attr("data-id");
						map.removeMarker(addMarker);
						if (mapID && gis.markers[mapID]) markers[marker.mapid]=createMarker(gis.markers[mapID]);
				} else {
					map.removeMarker(addMarker);
				}
			}
			addEnable=true;
		} else if (action=="box-close") {
			$("#map-box").hide();
		} else if (action=="lock") {
			$.get($this.attr("href"), function(html) {$this.html(html);});
		} else if (action=="delphoto") {
			if (confirm("ยืนยันว่าจะลบภาพนี้จริง?")) {
				notify("กำลังลบภาพ กรุณารอสักครู่...");
				$.get($this.attr("href"),function(data) {
					$this.parent().parent().remove();
					notify(data,50000);
					// Update map
					var mapid=$this.data("mapid");
					$.get("map/getwho/"+mapid,function(data) {
						//			notify("mapID="+data.id+" , "+data.marker.lat+data.marker.lng+data.marker.content);
						markers[mapid]=createMarker(data);
						gis.markers[mapid]=data;
					}, "json");
				});
			}
		} else {
			return true;
		}
		return false;
	});

	$("#fq")
	.click(function() {
		$("#map-box").hide()
		if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', "/map/search"])
	})
	.keypress(function(e) {
		if (e.which == 13) {
			searchLocation($("#fq").val())
			if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', "/map/search/location"])
			e.stopPropagation()
			return false
		}
	})
	.autocomplete({
		source: function(request, response) {
			notify("กำลังค้นหา...");
			$.get($("#mapSearch").attr("action"),{mapgroup:mapGroup, n:50, q:request.term}, function(data){
				notify();
				response(data)
			}, "json");
		},
		minLength: 2,
		dataType: "json",
		cache: false,
		select: function(event, ui) {
			// Do something with id
			$("#fq").val(ui.item.label);
			mapID=ui.item.value;
			if (markers[mapID]==undefined) {
				$.get("map/getwho/"+mapID,function(data) {
					markers[mapID]=createMarker(data);
					gis.markers[mapID]=data;
					map.setCenter(data.lat, data.lng);
					showMarker(mapID);
				}, "json");
			} else {
				showMarker(ui.item.value);
			}
			if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', "/map?id="+mapID]);
			return false;
		}
	});

		// Send new photo
	$("body").on("change", ".inline-upload", function() {
		var $this=$(this);
		var $target=$(this).closest("div.photo").find("ul.photo");
		var mapid=$this.data('mapid');

		notify('<div class="progress"><div class="bar"></div ><div class="percent">0%</div ></div><div id="status"></div>');
		var bar = $('.bar');
		var percent = $('.percent');
		var status = $('#status');
		$this.closest("form").ajaxForm({
			beforeSend: function() {
				status.empty();
				var percentVal = '0%';
				bar.width(percentVal)
				percent.html(percentVal);
			},
			uploadProgress: function(event, position, total, percentComplete) {
				var percentVal = percentComplete + '%';
				bar.width(percentVal)
				percent.html(percentVal);
			},
			complete: function(data) {
				$target.append("<li>"+data.responseText+"</li>");
				notify("ดำเนินการเสร็จแล้ว.",5000);
				$this.val("");
				$this.replaceWith($this.clone(true));
				// Update map
				$.get("map/getwho/"+mapid,function(data) {
					markers[mapid]=createMarker(data);
					gis.markers[mapid]=data;
				}, "json");
				//		status.html(xhr.responseText);
			},
			success: function(data) {
				var percentVal = '100%';
				bar.width(percentVal)
				percent.html(percentVal);
			}
		}).submit();
		if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', $this.closest("form").attr("action")]);
	});

	$("body").on("click","a[group]" , function() {
		var $this=$(this);
		var group=$this.attr("group");
		//		notify("group="+group);
		$this.colorbox({rel:group});
	});

	var lastRefresh;
	(function requestNewPin() {
		if ( typeof lastRefresh=='undefined') {
			lastRefresh=Math.round(new Date().getTime()/1000);
		} else {
			$.getJSON(url+'map/new',{gr:mapGroup, t:lastRefresh},function(data) {
				//				notify("return time="+lastRefresh+' : '+data.time+' Count='+data.count);
				lastRefresh=data.time;
				$("#map-hits").html(data.hits);
				$("#map-totals").html(data.totals);
				if (data.markers) {
					$.each( data.markers, function(i, marker) {
						if (typeof markers[marker.mapid]!=undefined) {
							map.removeMarker(markers[marker.mapid]);
						}
						markers[marker.mapid]=createMarker(marker);
						gis.markers[marker.mapid]=marker;
					});
				}
			});
		}
		setTimeout(requestNewPin, 60000);  //second
	})(); //self Executing anonymous function
})

$(document).on("click",".sg-action",function(){
	if ($(this).data("rel")=="map-box") $("#map-box").show()
})

$(document).on("submit","#map-edit",function() {
	var $this=$(this);
	if ($("#edit-mapping-who").val()=="") {
		notify("กรุณาป้อน ใคร - Who ?");
		$("#edit-mapping-who").focus();
		return false;
	}
	notify("กำลังบันทึก");
	//		notify("Form submit "+$this.attr("action"));
	$.post($this.attr("action"),$this.serialize(), function(data) {
		//			notify("mapID="+data.id+" , "+data.marker.lat+data.marker.lng+data.marker.content);
		$("#map-box").hide();
		map.removeMarker(addMarker);
		if (data.id) {
			gis.markers[data.id]=data.marker;
			markers[data.id]=createMarker(data.marker);
		}
		alwayShow=false;
		addEnable=true;
		notify();
		if (isRunOnHost && typeof _gaq != 'undefined') _gaq.push(['_trackPageview', $this.attr("action")]);
	},"json");
	return false;
})

$(document).on("keypress","#map-edit .form-text,#map-edit  .form-checkbox",function(e) {
	if (e.which == 13) return false;
})

// Edit address
$(document).on('focus', '#edit-mapping-dowhat', function(e) {
	$(this)
	.autocomplete({
		source: function(request, response){
			$.get(url+"map/searchdowhat?q="+encodeURIComponent(request.term), function(data){
				response(data)
			}, "json");
		},
		minLength: 2,
		dataType: "json",
		cache: false,
		select: function(event, ui) {
			var sp=$(this).val().split(",");
			sp.pop(-1);
			sp.push(ui.item.label+",");
			var str=sp.join();
			$(this).val(str);
			return false;
		}
	})
})