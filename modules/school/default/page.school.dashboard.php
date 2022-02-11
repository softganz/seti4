<?php
function school_dashboard($self,$orgid) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,'Dashboard : '.$schoolInfo->name,NULL,$schoolInfo);

	$sidebar.=R::View('school.kids.menu',$orgid);
	$self->theme->sidebar.=$sidebar;

	$ret.='<h2>ข้อมูลโรงเรียน</h2>';
	$ret.='<p>ชื่อโรงเรียน : '.$schoolInfo->name.'</p>';
	$ret.='<p>ที่อยู่ : '.$schoolInfo->info->address.'</p>';

	$address=SG\explode_address($schoolInfo->info->address);
	$ret.='<p>ที่อยู่ : <span id="address">ตำบล'.$address['tambon'].' อำเภอ'.$address['ampur'].' จังหวัด'.$address['changwat'].'</span></p>';

	$ret.='<p>(แสดงรายละเอียดโรงเรียน แก้ไขรายละเอียดโรงเรียน)</p>';

	$tables = new Table();
	$tables->thead=array('no'=>'','ผู้ใช้งาน','date -in'=>'วันที่เริ่มใช้งาน','date -last'=>'วันที่เข้าสู่ระบบล่าสุด');
	$tables->rows[]=array(1,i()->name,'5 ต.ค. 2559',sg_date('ว ดด ปปปป'));
	$ret.=$tables->build();
	$ret.='<p>(แสดงรายละเอียดผู้ใช้งาน เพิ่ม/แก้ไขรายละเอียดผู้ใช้งาน)</p>';

	$ret.='<div id="map-canvas" class="map-canvas" style="width:100%;height:400px;">แผนที่แสดงที่ตั้งโรงเรียน</div>';

	//$ret.=print_o($schoolInfo,'$schoolInfo');



	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript">
var geocoder;
var map;
function initialize() {
  geocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(13, 100.644);
  var mapOptions = {
    zoom: 5,
    center: latlng
  }
  map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
  codeAddress();
}

function codeAddress() {
  var address = $("#address").text();
  geocoder.geocode( { "address": address}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);
      var marker = new google.maps.Marker({
          map: map,
          position: results[0].geometry.location
      });
    } else {
      console.log("Geocode was not successful for the following reason: " + status);
    }
  });
}

google.maps.event.addDomListener(window, "load", initialize);

	</script>';
	return $ret;
}
?>