/*
* sg-calendar version 3.10 rev 2018-10-12
* Modify :: 2025-07-19
* Version :: 2
*/

$(document).ready(function() {
	var $calendarEle = $("#calendar-body")
	var calendarUrl = $calendarEle.data('url')
	var isAddable = $calendarEle.data('add') != false

	// console.log(isAddable)

	//	var cyear=year=$calendarEle.data('year')
	//	var cmonth=month=$calendarEle.data('month')

	var d = new Date()
	var cyear = year = d.getFullYear()
	var cmonth = month = d.getMonth()+1
	var currentHash="month";



	// Calendar Navigator Link Click
	$("body").on('click','.widget-nav.-calendar a', function() {
		hash = this.href.split("#")[1];
		console.log(hash)
		if (hash == undefined) return;

		if (hash == "prev") {month--; if (month <= 0) {month = 12; year--}}
		else if (hash == "next") {month++; if (month > 12) {month = 1;year++}}
		else if (hash == "today") {year = cyear; month = cmonth;}
		else currentHash = hash;

		notify("Loading"+currentHash);
		$calendarEle.data('year', year)
		$calendarEle.data('month', month)

		//console.log($calendarEle.data('year') + '-' + $calendarEle.data('month'))

		loadMonth(year,month,currentHash);
		return false;
	});



	$("body").on('click', "#edit-calendar .btn.-back-to-calendar", function() {
		// console.log("Back to calendar")
		loadMonth(year,month);
		return false;
	});

	if (isAddable) {
		$("body").on('click','.calendar-add', function(event) {
			var $eventTarget = $(event.target)
			if ($eventTarget.hasClass('daybox') || $eventTarget.hasClass('daynum')) {

				var $this = $(this)
				var para = $calendarEle.data()
				para.d = $this.closest('td').attr('id')
				//		if ($calendarEle.data('module')) para.module=$calendarEle.data('module')
				//		if ($calendarEle.data('tpid')) para.tpid=$calendarEle.data('tpid')

				// console.log('CALENDAR Add Start '+$this.prop("tagName"))
				// console.log('Event target id = '+$(event.target).prop("tagName"))
				// console.log('Add url = '+calendarUrl+'/form')
				// console.log(para)
				$.post(calendarUrl+'/form', para, function(data) {
					$calendarEle.html(data)
				})
			}
		})
	}

	$("body").on('submit',"#edit-calendar", function() {
		if ($("#edit-calendar-title").val()=="") {
			notify("กรุณาป้อนทำอะไร");
			return false;
		}
		var from=$("#edit-calendar-from_date").val().split("/");
		var to=$("#edit-calendar-to_date").val().split("/");
		var fromDate=new Date(from[2],from[1]-1,from[0]);
		var toDate=new Date(to[2],to[1]-1,to[0]);
		if (fromDate>toDate) {
			notify("วันที่เริ่มต้น หรือ วันที่สิ้นสุดผิดพลาด");
			return false;
		}
		var action=$("#edit-calendar").attr("action");
		notify("Updating.");
		$.post(action,$("#edit-calendar").serialize(),function(data) {
			notify("Updated.");
			//console.log('CALENDAR return',data)
			loadMonth(year,month)
		});
		return false;
	});

	function loadMonth(year,month,hash) {
		//	var withoutHash = href.indexOf("#")>0?href.substr(0,href.indexOf("#")):href;
		para = $calendarEle.data();
		para.year = year
		para.month = month
		para.hash = hash
		notify("กำลังโหลดปฏิทินของเดือน "+thaiMonthName[parseInt(month-1)]+" "+(year+543));
		$.post(calendarUrl, para, function(html) {
			notify();
			$calendarEle.html(html)
			$("#calendar-current-month").html($('.calendar-main').data('month'));

		});
		return false;
	}


	$(document).on('click','#calendar-addmap', function() {
		var isCalendarPin=false
		var imgSize = new google.maps.Size(16, 16)
		var $map=$("#calendar-mapcanvas")
		$('#calendar-mapcanvas').toggle();
		$map.gmap({
				center: gis.center,
				zoom: gis.zoom,
				scrollwheel: true
			})
			.bind("init", function(event, map) {
				if (gis.current) {
					marker=gis.current;
					isCalendarPin=true;
					$map.gmap("addMarker", {
						position: new google.maps.LatLng(marker.latitude, marker.longitude),
						draggable: true,
					}).click(function() {
						$map.gmap("openInfoWindow", { "content": "ลากหมุดเพื่อเปลี่ยนตำแหน่ง" }, this);
					}).mouseover(function() {
					}).dragend(function(event) {
						var latLng=event.latLng.lat()+","+event.latLng.lng();
							$('#edit-calendar-latlng').val(latLng)
					});
				}

				$(map).click(function(event, map) {
					if (!isCalendarPin) {
						$map.gmap("addMarker", {
							position: event.latLng,
							draggable: true,
							bounds: false
						}, function(map, marker) {
							// After add point
							var latLng=event.latLng.lat()+","+event.latLng.lng();
							$('#edit-calendar-latlng').val(latLng)
						}).dragend(function(event) {
							var latLng=event.latLng.lat()+","+event.latLng.lng();
							$('#edit-calendar-latlng').val(latLng)
						});
						isCalendarPin=true;
					}
				});
			});
	})

})