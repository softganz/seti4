<?php
/**
* Saveup View Member Information
* Created 2018-10-30
* Modify  2019-05-26
*
* @param Object $self
* @param String $memberId
* @return String
*/

$debug = true;

function saveup_member_view($self, $memberId) {

	$memberInfo = is_object($memberId) ? $memberId : R::Model('saveup.member.get',$memberId);
	$memberId = $memberInfo->mid;


	if (!$memberInfo) return message('error',$self->theme->title='Member id '.$memberId.' not found.');

	$isEdit = user_access('administrator saveups,create saveup content');

	R::View('saveup.toolbar',$self,$memberId.' : '.$memberInfo->info->firstname.' '.$memberInfo->info->lastname,'member',$memberInfo);

	if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="btn -floating -circle48" href="'.url('saveup/member/modify/'.$memberId).'"><i class="icon -edit -white"></i></a>'
			.'</div>';
	}

	$tables = new Table();
	$tables->addClass('member-desc -'.$memberInfo->info->status);
	$tables->caption = 'รายละเอียดสมาชิก';
	$tables->rows[] = [
		'หมายเลขสมาชิก',
		$memberId
		.'<span class="saveup-info-photo">'
		.'<a href="'.url('saveup/member/photo/'.$memberId).'" title="คลิกเพื่อเปลี่ยนภาพถ่าย">'
		.'<img src="'.saveup_model::member_photo($memberId).'" />'
		.'</a>'
		.'</span>'
	];
	$tables->rows[] = array('ชื่อ - สกุล',$memberInfo->info->prename.$memberInfo->info->firstname.' '.$memberInfo->info->lastname.($memberInfo->info->nickname?' ('.$memberInfo->info->nickname.')':''));
	$tables->rows[] = array('เลขประจำตัวประชาชน',$memberInfo->info->idno);
	$tables->rows[] = array('วันเกิด',$memberInfo->info->birth?sg_date($memberInfo->info->birth,'ว ดดด ปปปป'):'');

	$currentAddress = $memberInfo->info->caddress.' อ.'.$memberInfo->info->camphure.' จ.'.$memberInfo->info->cprovince.' '.$memberInfo->info->czip;
	$registerAddress = $memberInfo->info->address.' อ.'.$memberInfo->info->amphure.' จ.'.$memberInfo->info->province.' '.$memberInfo->info->zip;

	$tables->rows[] = array('ที่อยู่ปัจจุบัน',$currentAddress);
	$tables->rows[] = array('โทรศัพท์',$memberInfo->info->phone);
	if ($currentAddress != $registerAddress) {
		$tables->rows[] = array('ที่อยู่ (ทะเบียนบ้าน)', $registerAddress);
	}
	if ($memberInfo->info->line_name) $tables->rows[] = array('สายสัมพันธ์','<a href="'.url('saveup/member/view/'.$memberInfo->info->line_id).'">'.$memberInfo->info->line_name.'</a>');
	$tables->rows[] = array('บุคคลที่ติดต่อได้',($memberInfo->info->contact_id?'<a href="'.url('saveup/member/view/'.$memberInfo->info->contact_id).'">':'').$memberInfo->info->contact_name.($memberInfo->info->contact_id?'</a>':''));
	$mtypes=array('1'=>'นักพัฒนา','2'=>'เพื่อนนักพัฒนา','3'=>'ญาติพี่น้อง');
	$tables->rows[] = array('อาชีพ',$memberInfo->info->occupa);
	$tables->rows[] = array('กลุ่มสมาชิก',$mtypes[$memberInfo->info->mtype]);
	if ($memberInfo->info->date_approve) $tables->rows[] = array('วันที่เริ่มเป็นสมาชิก',sg_date($memberInfo->info->date_approve,'ว ดดด ปปปป'));
	$tables->rows[] = array('ชำระเงินสัจจะราย',$memberInfo->info->savepayperiod.' เดือน');
	if ($memberInfo->info->facebook) $tables->rows[] = array('Facebook','<a href="'.$memberInfo->info->facebook.'" target="_blank">'.$memberInfo->info->facebook.'</a>');
	if ($memberInfo->info->remark) $tables->rows[] = array('หมายเหตุ',nl2br($memberInfo->info->remark));

	$tables->rows[] = array('เงินฝากสัจจะ', number_format($memberInfo->balance["SAVING-DEP"]->balance,2).' บาท <a class="sg-action" href="'.url('saveup/member/'.$memberId.'/card.saving').'" data-rel="box" data-width="800" data-height="640"><i class="icon -material">pageview</i></a>');
	$tables->rows[] = array('เงินฝากพิเศษ', number_format($memberInfo->balance["SAVING-SPECIAL"]->balance,2).' บาท <a class="sg-action" href="'.url('saveup/member/'.$memberId.'/card.saving', array('gl'=>'SAVING-SPECIAL')).'" data-rel="box" data-width="800" data-height="640"><i class="icon -material">pageview</i></a>');

	$ret .= $tables->build();

	$ret .= '<div id="map_canvas"></div>';

	$ret .= R::Page('saveup.treat.year', NULL, $memberId);




	//$ret .= print_o($dbs, '$dbs');

	$stmt = 'SELECT l.*
		, CONCAT(m.`firstname`," ",m.`lastname`) `name`
		, c.`desc`
		FROM %saveup_loan% l
			LEFT JOIN %saveup_member% m USING(`mid`)
			LEFT JOIN %saveup_glcode% c USING(`glcode`)
		WHERE l.`mid`=:mid
		ORDER BY `loanno` DESC';

	$dbs = mydb::select($stmt,':mid',$memberId);

	if ($dbs->_num_rows) {
		$tables = new Table();
		$tables->caption = 'รายละเอียดการกู้เงิน';
		$tables->thead = array('date'=>'วันที่','เลขที่','ประเภทเงินกู้','money total'=>'จำนวนเงิน','money balance'=>'คงเหลือ','tool'=>'');
		foreach ($dbs->items as $item) {
			$tables->rows[] = [
				sg_date($item->loandate,'ว ดด ปปปป'),
				'<a href="'.url('saveup/loan/view/'.$item->loanno).'">'.$item->loanno.'</a>',
				$item->desc,
				number_format($item->total,2),
				number_format($item->balance,2),
				'<a href="'.url('saveup/loan/view/'.$item->loanno).'" title="ดูรายละเอียดใบกู้เงิน">รายละเอียด</a> | <a href="'.url('saveup/loan/rcv','id='.$item->loanno).'">ชำระหนี้</a>',
				'config'=>array('class'=>$item->status=='Cancel'?'cancel':'')
			];
			if ($memberInfo->info->memo) $tables->rows[] = array('','','<td colspan="3">'.$memberInfo->info->memo.'</td>');
		}
		$ret .= $tables->build();
	}

	$gis['center'] = $memberInfo->info->latlng?$memberInfo->info->lat.','.$memberInfo->info->lnt:'7.011666,100.470088';
	$gis['zoom'] = 14;
	if ($memberInfo->info->latlng) {
		$gis['markers'][] = array('latitude'=>$memberInfo->info->lat,
														'longitude'=>$memberInfo->info->lnt,
														);
	}

	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

		$ret .= '
<script type="text/javascript"><!--
$(document).ready(function() {
var pid="'.$memberId.'";
var select='.json_encode($json).';

var memberUpdate=function(fldSave,value) {
	notify("กำลังบันทึก");
	var para={id: pid, save: "yes", fld: fldSave, value: value};
	$.post("'.url('saveup/member/edit').'",para, function(data) {
		if (data=="" || data=="<p>&nbsp;</p>") data="<em>แก้ไข</em>";
		if (fldSave=="name") $("h2.title").text(value);
		notify("บันทึกเรียบร้อย",2000);
	});
};
';
	$ret.='

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
					draggable: true,
				}).click(function() {
				//$("#map_canvas").gmap("openInfoWindow", { "content": marker.content }, this);
				}).dragend(function(event) {
					var latLng=event.latLng.lat()+","+event.latLng.lng();
					memberUpdate("latlng",latLng);
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
						memberUpdate("latlng",latLng);
					}).dragend(function(event) {
						var latLng=event.latLng.lat()+","+event.latLng.lng();
						memberUpdate("latlng",latLng);
					});
				}
				is_point=true;
			});
		}
	});
});
--></script>';

	//$ret.=print_o($memberInfo,'$memberInfo');

	return $ret;
}
?>