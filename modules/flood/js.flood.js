$(document).ready(function(){
	//console.log($('.flood-camera-realtime').length)
	var $realTimeCamera = $('.flood-camera-realtime')
	if (firebaseConfig && $realTimeCamera.length) {
		var database = firebase.database()
		var ref = database.ref(firebaseConfig.flood + 'camera')

		var i = 0
		console.log('Start Camera Realtime')

		ref.once("value", (snapshot,error) => {
			snapshot.forEach((snap)=>{
				//console.log(snap.val())
				const cameraInfo = snap.val();
				floodUpdateCameraList(cameraInfo.name,cameraInfo);
			});
		});

		ref
		.on("child_changed",function(snap){
			//console.log(++i + " : " + snap.key)
			//console.log(snap.val())
			floodUpdateCameraList(snap.key, snap.val());
		});

		/*
		var cameraRef = database.ref(firebaseConfig.flood + 'camera');
		function floodUpdateCameraList(cameraName,cameraInfo) {
			var $camera=$("#camera-"+cameraName);
			var $img=$("#"+cameraName);
			$img.attr("src",cameraInfo.thumb+"?"+cameraInfo.timestamp).data("timestamp",cameraInfo.timestamp);
			$camera.find(".date.-date").html(cameraInfo.date);
			$camera.find(".date.-time").html(cameraInfo.time);
			$camera.find(".flood-cam-error.not-update").hide();
			$camera.find(".flood-timestamp").fadeOut().fadeIn();
			return;
		}
		*/
	}

	function floodUpdateCameraList(cameraName,cameraInfo) {
		var $camera = $("#camera-"+cameraName)
		//console.log('Camera Length ', $camera.length)
		if ($camera.length == 0) return
		//console.log('Update camera '+cameraName)
		$camera.find(".-photo").attr("src",cameraInfo.url+'?'+cameraInfo.timestamp)
		$camera.find(".-date").html(cameraInfo.date)
		$camera.find(".-time").html(cameraInfo.time)
		//$camera.find(".flood-cam-error.not-update").hide()
		//$camera.find(".flood-timestamp").fadeOut().fadeIn()
		return
	}



	// Flood Camera View Script

	var $cameraView = $(".flood-camera-view")
	if ($cameraView.length) {
		var $imageView = $cameraView.find('.-photo')
		var $dateView = $cameraView.find('.-date')
		var $timeView = $cameraView.find('.-time')
		var autoPlay = $(".flood-camera-view").data('autoPlay')

		//	$("#flood-camera-photos>ul").height($imageView.height()+"px");
		$("#flood-camera-level").hide();
		$("#flood-camera-info>nav>ul>li>a").click(function() {
			$this=$(this);
			$("#flood-camera-info>div").hide();
			$($this.attr("href")).fadeIn();
			return false;
		});

		setTimeout(function() {$("#flood-camera-info>nav>ul>li:first-child>a").trigger("click");}, 1000);

		$("[fld]").click(function(event) {
			var $container=$(this);
			var fld=$container.attr("fld");
		});

		/*
		$("#photo-last").elevateZoom({
			gallery:"flood-camera-photos",
			galleryActiveClass: "active",
			imageCrossfade: true,
			zoomType : "lens",
			lensShape : "round",
		  cursor: "crosshair",
		  responsive: true,
		 });
		 $(".toolbar>h2").dblclick(function(){notify("disable zoom");$imageView.remove();});
		*/
		var zoomConfig = {
				gallery:"flood-camera-photos",
				galleryActiveClass: "active",
				imageCrossfade: true,
				zoomType : "lens",
				lensShape : "round",
			  cursor: "crosshair",
			  responsive: true,
			 };
		var zoomActive = false;

		$imageView.click(function(){
			zoomActive = !zoomActive;
			if (zoomActive) {
				$imageView.elevateZoom(zoomConfig);//initialise zoom
			} else {
				$.removeData($imageView, "elevateZoom");//remove zoom instance from image
				$(".zoomContainer").remove();// remove zoom container from DOM
			}
		});

		if (autoPlay) $imageView.elevateZoom(zoomConfig);

		var ez = $imageView.data("elevateZoom");

		function updateTimeStamp(date, time) {
			$dateView.text(date)
			$timeView.text(time)
		}

		// Right thumbnail click
		$("body").on("click","#flood-camera-photos>li>a", function() {
		 	if (!zoomActive) {
		 		var $this=$(this)
				$imageView.css("background","#ffffff")
				.fadeOut()
				.attr("src",$this.data("image"))
				.fadeIn()
				updateTimeStamp($this.data('date'), $this.data('time'))
		 	}
		 	return false;
		});


		$("#slider-range-min").slider({
			range: "min",
			value: imgs.length,
			min: 0,
			max: imgs.length,
			change: function(even,ui) {
				var imgSrc = imgs[ui.value].photo
				//console.log("Slide Change")
				$imageView.attr({
					lowsrc:  "'._CACHE_URL.'"+cameraName+"-"+imgSrc,
					src: imgSrc
				});
				updateTimeStamp(imgs[ui.value].date, imgs[ui.value].time)
				if (ui.value==imgs.length) {
					isRefresh=true;
					$( "#photo-desc" ).text(oldPhotoDesc);
				} else {
					isRefresh=false;
					$( "#photo-desc" ).text(imgSrc+" - "+ui.value+"/"+imgs.length);
				}
			},
			slide: function( event, ui ) {
				var imgSrc = imgs[ui.value].photo
				//console.log("Slide Slide")
				$imageView.attr({src: imgSrc});
				updateTimeStamp(imgs[ui.value].date, imgs[ui.value].time)
				if (ui.value==imgs.length) {
					isRefresh=true;
					$( "#photo-desc" ).text(oldPhotoDesc);
				} else {
					isRefresh=false;
					$("#photo-desc").text(imgSrc+" - "+ui.value+"/"+imgs.length);
				}
				//ez.swaptheimage($imageView.attr("src"), $imageView.attr("src"));
			}
		});


		var sval;
		$("#slider-prev").click(function() {
			sval=$("#slider-range-min").slider("value")-1;
			if (sval<0) return;
			$( "#slider-range-min" ).slider("value",sval);
			//ez.swaptheimage($imageView.attr("src"), $imageView.attr("src"));
			return false;
		});

		$("#slider-next").click(function() {
			sval=$("#slider-range-min").slider("value")+1;
			console.log("Photo "+sval+" of "+imgs.length);
			if (sval>imgs.length-1) sval=0;
			//if (sval>$("#slider-range-min" ).slider("max")) return;
			$("#slider-range-min").slider("value",sval);
			//ez.swaptheimage($imageView.attr("src"), $imageView.attr("src"));
			return false;
		});


		if (autoPlay=="ถอยหลัง") {
		setInterval(function() {$("#slider-prev").trigger("click");}, playTime);
		} else if (autoPlay=="ไปหน้า") {
			$( "#slider-range-min" ).slider("value",1)
			setInterval(function() {$("#slider-next").trigger("click");}, playTime);
		}
	}

});
