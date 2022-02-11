<?php
/**
 * Method view franchise shop detail
 * @param String $shopname
 * @return String Shop detail
 */
function ibuy_franchise_view($self, $shopid = NULL) {
	//		$ret.=print_o($_SESSION,'$_SESSION').print_o(i(),'$user');
	if (is_numeric($shopid)) {
		$shopname = mydb::select('SELECT `username` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$shopid)->username;
	} else {
		$shopname = $shopid;
	}

	$shop_tpid = mydb::select('SELECT `tpid` FROM %users% u LEFT JOIN %topic% t ON `t`.`type`="franchise" AND t.uid=u.uid WHERE `username` = :username LIMIT 1', ':username',$shopname)->tpid;

	$shop=mydb::select('SELECT f.* , u.`uid`, u.username , u.datein , p.name province FROM %ibuy_customer% f LEFT JOIN %users% u ON u.uid=f.uid LEFT JOIN %province% p ON p.pid=f.pid WHERE `username`=:shopname LIMIT 1',':shopname',$shopname);

	$self->theme->title=$shop->custname.' - '.($shop->custtype=='franchise'?'เฟรนไชส์':'ตัวแทนจำหน่าย');

	$ret.=R::View('ibuy.franchise.menu','detail',$shop->uid,$shop->username);

	$tables = new Table();
	$tables->caption='รายละเอียดร้าน '.$shop->custname;
	$tables->rows[]=array('ชื่อร้าน',$shop->custname);
	$tables->rows[]=array('ประเภทร้าน',$shop->custtype=='franchise'?'เฟรนไชส์':'ตัวแทนจำหน่าย');
	$tables->rows[]=array('ที่อยู่',$shop->custaddress.' '.$shop->custzip);
	$tables->rows[]=array('โทรศัพท์',user_access('access user profiles') ? $shop->custphone : '**');
	$tables->rows[]=array('เมื่อวันที่',$shop->datein);
	// private information
	if (user_access('administer ibuys','access ibuys',$shop->uid)) {
		$tables->rows[]='<tr><th colspan="2">ข้อมูลส่วนบุคคล</th></tr>';
		$tables->rows[]=array('ชื่อผู้ติดต่อ',$shop->custattn);
		$tables->rows[]=array('ส่วนลดที่ใช้งานได้',number_format($shop->discount,2).' บาท');
		$tables->rows[]=array('ส่วนลดที่ยังไม่สามารถใช้งานได้',number_format($shop->discount_hold<0?0:$shop->discount_hold,2).' บาท ( สถานะ : '.($shop->discount_hold>=0?'ระงับ':'ใช้ได้').' )');
		$tables->rows[]=array('ขนส่งสินค้าโดย',$shop->shippingby);
	}

	$shop_detail .= $tables->build();

	$shopprov=mydb::select('SELECT u.`uid`, u.username , f.custname,f.custtype FROM %ibuy_customer% f LEFT JOIN %users% u ON u.uid=f.uid WHERE f.pid=:pid',':pid',$shop->pid);
	if ($shopprov->_num_rows) {
		$shop_detail.='<p><strong>รายชื่อร้านในจังหวัด'.$shop->province.' : </strong><p>';
		foreach ($shopprov->items as $rs) $shop_detail.='<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="'.ibuy_define::custtype($rs->custtype).'">'.$rs->custname.' ('.strtoupper(substr($rs->custtype,0,1)).')</a> , ';
		$shop_detail=trim($shop_detail,' , ');
		$shop_detail.='</p>';
	}

	/*
	if ($shop_tpid) {
		load_module('class.paper_model.php');
		$topic=paper_model::get_topic_by_id($shop_tpid);
		$shop_view = R::View('paper.content.prepare',$topic);
		$shop_detail.=$shop_view->detail;
	} else {
		$shop_detail.='<p><em>*** ยังไม่เปิดหน้าร้านในเว็บ ***</em></p>';
	}
	*/

	if ($shop->latlng) {
		$gis['center']=$shop->latlng;
		$gis['zoom']=13;
		$gis['dragable']=false;
		list($lat,$lnt)=explode(',',$shop->latlng);
		$gis['markers'][]=array('latitude'=>$lat,
													'longitude'=>$lnt,
													);

	$shop_map='<div id="map_canvas" width="400" height="400" style="width:400px;height:400px;"></div>'._NL;
	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var gis='.json_encode($gis).';
var is_point=false;
$("#map_canvas")
	.gmap({
		center: gis.center,
		zoom: gis.zoom,
		scrollwheel: false
	})
	.bind("init", function(event, map) {
		if (gis.markers) {
			$.each( gis.markers, function(i, marker) {
				$("#map_canvas").gmap("addMarker", {
					position: new google.maps.LatLng(marker.latitude, marker.longitude),
					draggable: gis.dragable,
				}).click(function() {
				$("#map_canvas").gmap("openInfoWindow", { "content": marker.content }, this);
				}).dragend(function(event) {
					var latLng=event.latLng.lat()+","+event.latLng.lng();
					$("#edit-register-latlng").val(latLng);
				});
			});
		} else {
			$(map).click( function(event) {
				if (!is_point) {
					$("#map_canvas").gmap("addMarker", {
						position: event.latLng,
						draggable: true,
						bounds: false
					}, function(map, marker) {
						// After add point
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						$("#edit-register-latlng").val(latLng);
					}).dragend(function(event) {
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						$("#edit-register-latlng").val(latLng);
					});
				}
				is_point=true;
			});
		}
	});
});
--></script>';

//			$shop_map='<iframe width="400" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://softganz.com/gis/point/'.$shop->latlng.'/type/map?title='.$shop->custname.'"></iframe>';
	} else {
		$shop_map=message('error','ยังไม่ได้ระบุตำแหน่งในแผนที่');
	}
	$ret.='<div id="ibuy-shop-detail">'.$shop_detail.'</div>';
	$ret.='<div id="ibuy-shop-map">'.$shop_map.'</div>';
	return $ret;
}
?>