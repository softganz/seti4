/**
 * Calendar:: calendar js module
 * Created :: 2007-03-06
 * Modify  :: 2025-07-22
 * Version :: 2
 */

let $calndarElement = $(".widget-calendar");
let $contentElement = $calndarElement.find('.calendar-content');
let calendarUrl = $calndarElement.data('apiurl')
let isAddable = $calndarElement.data('add') != false

console.log($contentElement)
// $(document).ready(function() {

	// console.log(isAddable)

	//	let cyear=year=$calndarElement.data('year')
	//	let cmonth=month=$calndarElement.data('month')

	let d = new Date()
	let cyear = d.getFullYear()
	let cmonth = d.getMonth()+1
	let currentHash = "month";
	let year = $calndarElement.data('year') ?? cyear;
	let month = $calndarElement.data('month') ?? cmonth;

	console.log("Current year", cyear, "Current month", cmonth, "Current hash", currentHash);


	// Calendar Navigator Link Click
	$(document).on('click','.widget-nav.-calendar a', function() {
		let hash = this.href.split("#")[1];

		if (hash == undefined) return;

		if (hash == "prev") {month--; if (month <= 0) {month = 12; year--;}}
		else if (hash == "next") {month++; if (month > 12) {month = 1; year++;}}
		else if (hash == "today") {year = cyear; month = cmonth;}
		else currentHash = hash;

		console.log("year", year, "month", month, "hash", hash);
		console.log("element", $calndarElement.data('year') + '-' + $calndarElement.data('month'), hash);

		loadMonth(year,month,currentHash);
		return false;
	});



	$(document).on('click', "#edit-calendar .btn.-back-to-calendar", function() {
		// console.log("Back to calendar")
		loadMonth(year, month, currentHash);
		return false;
	});

	if (isAddable) {
		$(document).on('click','.calendar-add', function(event) {
			let $eventTarget = $(event.target)
			if ($eventTarget.hasClass('daybox') || $eventTarget.hasClass('daynum')) {

				let $this = $(this)
				let para = $calndarElement.data()
				para.d = $this.closest('td').attr('id')
				//		if ($calndarElement.data('module')) para.module=$calndarElement.data('module')
				//		if ($calndarElement.data('tpid')) para.tpid=$calndarElement.data('tpid')

				// console.log('CALENDAR Add Start '+$this.prop("tagName"))
				// console.log('Event target id = '+$(event.target).prop("tagName"))
				// console.log('Add url = '+calendarUrl+'/form')
				// console.log(para)
				$.post(calendarUrl+'/form', para, function(data) {
					$contentElement.html(data)
				})
			}
		});
	}

	$(document).on('submit',"#edit-calendar", function() {
		if ($("#edit-calendar-title").val()=="") {
			notify("กรุณาป้อนทำอะไร");
			return false;
		}
		let from=$("#edit-calendar-from_date").val().split("/");
		let to=$("#edit-calendar-to_date").val().split("/");
		let fromDate=new Date(from[2],from[1]-1,from[0]);
		let toDate=new Date(to[2],to[1]-1,to[0]);
		if (fromDate>toDate) {
			notify("วันที่เริ่มต้น หรือ วันที่สิ้นสุดผิดพลาด");
			return false;
		}
		let action=$("#edit-calendar").attr("action");
		notify("Updating.");
		$.post(action,$("#edit-calendar").serialize(),function(data) {
			notify("Updated.");
			//console.log('CALENDAR return',data)
			loadMonth(year,month)
		});
		return false;
	});

	function loadMonth(year, month, hash) {
		let para = $calndarElement.data();
		para.year = year
		para.month = month
		para.hash = hash
		console.log("Load para", para);
		let loadMonthText = thaiMonthName[parseInt(month-1)]+" "+(year+543);
		notify("กำลังโหลดปฏิทินของเดือน " + loadMonthText);

		$.post(calendarUrl, para)
		.done(html => {
			notify();
			// console.log("Load month", year, month, hash, html);

			$calndarElement.data('year', year);
			$calndarElement.data('month', month);
			$calndarElement.data('hash', hash);

			$contentElement.html(html)
			$("#calendar-current-month>span").html($contentElement.find("table").data('currentMonth'));
		})
		.fail(response => {
			notify("ไม่สามารถโหลดปฏิทินได้", 5000);
			// console.error("Error loading calendar month:", response);
		});
		return false;
	}


	$(document).on('click','#calendar-addmap', function() {
		let isCalendarPin=false
		let imgSize = new google.maps.Size(16, 16)
		let $map=$("#calendar-mapcanvas")
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
						let latLng=event.latLng.lat()+","+event.latLng.lng();
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
							let latLng=event.latLng.lat()+","+event.latLng.lng();
							$('#edit-calendar-latlng').val(latLng)
						}).dragend(function(event) {
							let latLng=event.latLng.lat()+","+event.latLng.lng();
							$('#edit-calendar-latlng').val(latLng)
						});
						isCalendarPin=true;
					}
				});
			});
	});

// });