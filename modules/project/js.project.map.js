/*!
 * ProjectMap v0.0.0 by @softganz
 * Copyright 2013 Softganz Group.
 * Licensed under http://www.apache.org/licenses/LICENSE-2.0
 *
 * Designed and built with all the love in the world by @softganz.
 */

if (typeof jQuery === "undefined") { throw new Error("ProjectMap requires jQuery") }

+function ($) { "use strict";

	// BUTTON PUBLIC CLASS DEFINITION
	// ==============================

	var ProjectMap = function (element, options) {
		this.type				=
		this.options		=
		this.enabled		=
		this.map				=
		this.hoverState =
		this.$element   = null


		this.alwayShow=true
		this.markers={}
		this.markers.info=new Array();
		this.markers.pin=new Array();
		this.$mapCanvas=$(".map-canvas");
		this.imgSize = new google.maps.Size(24, 48);

		this.init('projectmap', element, options)
	}

	ProjectMap.DEFAULTS = {
		loadingText: 'loading...',
		mapCanvasId: "#map-canvas",
		centerx: 13.7280458,
		centery: 100.5242157,
		zoom: 7,
		fullscreen: true,
	}

	ProjectMap.prototype.init = function (type, element, options) {
		this.enabled  = true
		this.type     = type
		this.$element = $(element)
		this.options  = this.getOptions(options)
		this.initMap()
		this.initDOM()
	}

	ProjectMap.prototype.createMarker = function (marker) {
		var iconSize;
		if (marker.iconSize!=undefined) {
			iconSize=new google.maps.Size(marker.iconSize[0], marker.iconSize[1]);
		} else {
			iconSize=this.imgSize;
		}
		var isDragable=marker.draggable==undefined ? false : marker.draggable;
		var projectmap=this;
		var newMarker=this.map.addMarker({
			lat: marker.lat,
			lng: marker.lng,
			icon : new google.maps.MarkerImage(marker.icon, iconSize, null, null, iconSize),
			draggable: isDragable,
			infoWindow: {content: marker.content,closeclick: function() {projectmap.alwayShow=false;}},
			click: function(e) {projectmap.alwayShow=true;},
			dblclick: function(e) {notify("Edit");},
			dragend: function(e) { mapUpdate(e); },
			mouseover: function(e) {if (!projectmap.alwayShow) {projectmap.map.showInfoWindow(this);}},
			mouseout: function(e) {if (!projectmap.alwayShow) projectmap.map.hideInfoWindows();},
		});
		return newMarker;
	}

	ProjectMap.prototype.initMap = function () {
		if (this.options.fullscreen) {
			var height=$(window).height()-$('#header-wrapper').height()-$('#footer-wrapper').height();
			$('.package-footer').height(0).hide()
			this.$element.height(height+'px')
		}
		this.$mapCanvas.height(this.$element.height());
		var $mapBox=this.$element.find('.map-box')
		var mapBoxHeight=this.$element.height()-parseInt($mapBox.css('margin-top'))-parseInt($mapBox.css('padding-top'))-parseInt($mapBox.css('padding-top'))
//		alert($mapBox.css('padding-top'))
		$mapBox.height(mapBoxHeight+'px').css('max-height',mapBoxHeight+'px')
//		alert(this.$element.find('.map-box').attr('class')+this.$element.height())
//		$("#map-box").css({"max-height":($mapCanvas.height()-50)+"px"});

		this.map = new GMaps({
			el: this.options.mapCanvasId,
			lat: this.options.centerx,
			lng: this.options.centery,
			zoom: this.options.zoom,
			disableDoubleClickZoom: true,
			dblclick: function(e) {createNewMarker(e.latLng.lat(), e.latLng.lng());},
			click: function(e) {$('.map-box').slideUp()}
		});

		var thisMap=this;

		var para={};
		var lastMap;

		$.get(this.$element.data("url")+'/shape', para, function(data) {
			if (data.markers) {
				$.each( data.markers, function(i, marker) {
					thisMap.markers.info[marker.mapid]=marker
					thisMap.markers.pin[marker.mapid]=thisMap.createMarker(marker)
				});
			}
		},"json");

		$.get(this.$element.data("url")+'/markers', para, function(data) {
			if (data.markers) {
				$.each( data.markers, function(i, marker) {
					thisMap.markers.info[marker.mapid]=marker
					lastMap=thisMap.markers.pin[marker.mapid]=thisMap.createMarker(marker)
				});
			}
		},"json");

		$.get(this.$element.data("url")+'/sign', function(html) {
			$("#map-sign").html(html)
		});

	}

	ProjectMap.prototype.initDOM = function () {
		var thisMap=this;

		// Setup navigator bar
		$(document).on('click', '.nav-box>ul>li>a', function() {
			var $this=$(this);
			var currentTab = $this.attr("href");
			if ($this.hasClass("disabled")) return false; // Do something else in here if required
			if ($this.data("action")=="refresh") return true;
			$this.closest('ul').find('li').removeClass("active");
			$(this).parent().addClass("active");
			if ($this.attr("id")=="getMyLocation") return true;
			if ($this.attr("rel")) {
				return;
			} else if (currentTab.substring(0,1)=="#") {
				$(currentTab).show();
			} else {
				notify("Loading...");
				var target=$this.attr("rel-target")==undefined?".map-box":$this.attr("rel-target");
				$(target).hide();
				var para={} //gr: mapGroup};
				$.get(this.href, para, function(html) {
						$(target).empty().append(html).show();
						notify();
				});
			}
			return false;
		});

		$(document).on('click.sg.projectmap.data-api', '[data-action="load"]', function (e) {
			var $this=$(this)
			notify("Loading...");
			var target=$this.attr("rel-target")==undefined?".map-box":$this.attr("rel-target");
			$(target).hide();
			var para={} //gr: mapGroup};
			$.get(this.href, para, function(html) {
					$(target).empty().append(html).show( "slide", {}, 500 );
					notify();
			});
			return false;
		})


		$(document).on('click.sg.projectmap.data-api', '[data-action="showmap"]', function (e) {
			var $this=$(this)
			var mapID=$this.attr("data-id");
			if (thisMap.markers.pin[mapID]==undefined) {
				notify('ไม่มีพิกัดบนแผนที่',5000)
				/*
				$.get(thisMap.$element.data('url')+'getwho/'+mapID,function(data) {
					alert('Get data '+data)
					markers[mapID]=createMarker(data);
					gis.markers[mapID]=data;
					map.setCenter(data.lat, data.lng);
					$("#fq").val($this.attr("data-who"));
					showMarker(mapID);
				}, "json");
				*/
			} else {
			//			$("#fq").val($this.attr("data-who"));
				thisMap.showMarker(mapID);
			}
			return false;
		})

	$("#fq")
	.autocomplete({
		source: function(request, response) {
			notify("กำลังค้นหา...");
			$.get($("#mapSearch").attr("action"),{n:50, q:request.term}, function(data){
				notify();
				var html=data
				var target='.map-box'
				$(target).hide()
				$(target).empty().append(html).show( "slide", {}, 500 )
				return false
			});
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
			return false;
		}
	})

	}

	ProjectMap.prototype.getDefaults = function () {
		return ProjectMap.DEFAULTS
	}

	ProjectMap.prototype.getOptions = function (options) {
		options = $.extend({}, this.getDefaults(), this.$element.data(), options)

		if (options.delay && typeof options.delay == 'number') {
			options.delay = {
				show: options.delay
				, hide: options.delay
			}
		}

		return options
	}

	ProjectMap.prototype.showMarker = function (mapID) {
		var marker=this.markers.pin[mapID];
		var info=this.markers.info[mapID];
		if (marker!=undefined) {
			this.map.setCenter(info.lat,info.lng);
			this.map.hideInfoWindows();
			this.map.showInfoWindow(marker);
		}
	}


  ProjectMap.prototype.setState = function (state) {
    var d    = 'disabled'
    var $el  = this.$element
    var val  = $el.is('input') ? 'val' : 'html'
    var data = $el.data()

    state = state + 'Text'

    if (!data.resetText) $el.data('resetText', $el[val]())

    $el[val](data[state] || this.options[state])

    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d);
    }, 0)
  }

  ProjectMap.prototype.toggle = function () {
    var $parent = this.$element.closest('[data-toggle="buttons"]')

    if ($parent.length) {
      var $input = this.$element.find('input')
        .prop('checked', !this.$element.hasClass('active'))
        .trigger('change')
      if ($input.prop('type') === 'radio') $parent.find('.active').removeClass('active')
    }

    this.$element.toggleClass('active')
  }


  // BUTTON PLUGIN DEFINITION
  // ========================

  var old = $.fn.button

  $.fn.projectmap = function (option) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('sg.projectmap')
      var options = typeof option == 'object' && option

      if (!data) $this.data('sg.projectmap', (data = new ProjectMap(this, options)))

//      if (option == 'toggle') data.toggle()
//      else if (option) data.setState(option)

      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.projectmap.Constructor = ProjectMap

  // PROJECTMAP NO CONFLICT
  // ==================

  $.fn.projectmap.noConflict = function () {
    $.fn.projectmap = old
    return this
  }

  $(document).on('click.sg.projectmap.data-api', '[data-action="box-close"]', function (e) {
			e.preventDefault()
			$(".map-box").slideUp();
  })



}(jQuery);

var mapP;

$(document).ready(function() {
	mapP=$('.sg-project-map').projectmap({
		mapCanvasId: '#map-canvas',

	})


})