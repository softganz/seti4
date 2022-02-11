<?php
/**
* Project Add Province
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_addprov($self, $topic, $para = NULL, $body = NULL) {
		$tpid=$topic->tpid;
		if (!$tpid) return;

		$isEdit=user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
		if ($isEdit) {
			if ($areacode=post('areacode')) {
				//$ret.=$areacode.post('address');
				$addr=SG\explode_address(post('address'));
				if ($addr['village']) $addr['village']=sprintf('%02d',$addr['village']);
				$addr['changwat']=substr($areacode,0,2);
				$addr['ampur']=substr($areacode,2,2);
				$addr['tambon']=substr($areacode,4,2);
				//$ret.=print_o($addr,'$addr');
				mydb::query('INSERT INTO %project_prov% (`tpid`,`house`,`village`,`tambon`,`ampur`,`changwat`) VALUES (:tpid, :house, :village, :tambon, :ampur, :changwat)',':tpid',$tpid, $addr);
				//$ret.=mydb()->_query;
				$autoid=mydb()->insert_id;
				$ret.=__project_form_addprov_listprov($tpid,$isEdit);
				return $ret;
			} else if ($changwat=post('changwat')) {
				$addr['house']=trim(post('address'));
				$addr['village']=post('village')?sprintf('%02d',post('village')):'';
				$addr['tambon']=post('tambon')?post('tambon'):'';
				$addr['ampur']=post('ampur')?post('ampur'):'';
				$addr['changwat']=post('changwat')?post('changwat'):'';
				if (empty($addr['house'])) {
					$village=mydb::select('SELECT `villname` FROM %co_village% WHERE `villid`=:villid LIMIT 1',':villid',$addr['changwat'].$addr['ampur'].$addr['tambon'].$addr['village'])->villname;
					if ($village) $addr['house']='บ้าน'.$village;
				}
				mydb::query('INSERT INTO %project_prov% (`tpid`,`house`,`village`,`tambon`,`ampur`,`changwat`) VALUES (:tpid, :house, :village, :tambon, :ampur, :changwat)',':tpid',$tpid, $addr);
				//mydb::query('INSERT INTO %project_prov% (`tpid`,`changwat`) VALUES (:tpid, :provid)',':tpid',$tpid, ':provid',$provid);
				//$ret.=mydb()->_query;
				$autoid=mydb()->insert_id;
				$ret.=__project_form_addprov_listprov($tpid,$isEdit);
				return $ret;
			} else if ($autoid=post('delete')) {
				mydb::query('DELETE FROM %project_prov% WHERE `autoid`=:autoid LIMIT 1',':autoid',$autoid);
			} else if (post('form')) {
				$dbs=mydb::select('SELECT * FROM %co_province% ORDER BY CASE WHEN `provid`<="99" THEN CONCAT(0,CONVERT(`provname` USING tis620)) WHEN `provid`>"99" THEN 9 END ASC');
				$ret.='<div style="margin:10px 0;"><form class="sg-form" action="'.url('project/form/'.$tpid.'/addprov').'" data-rel="#project-provlist" data-done="remove" style="display:block;width:100%;">'._NL;
				$ret.='<div class="form-item"><label>เลือกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน จากช่องเลือก</label><select name="changwat" id="changwat" class="form-select">'._NL;
				$ret.='<option value="">==เลือกจังหวัด==</option>'._NL;
				foreach ($dbs->items as $rs) {
					$ret.='<option value="'.$rs->provid.'">'.$rs->provname.'</option>'._NL;
				}
				$ret.='</select>'._NL;
				$ret.='<select name="ampur" id="ampur" class="form-select -hidden"><option value="">==เลือกอำเภอ==</option></select>'._NL;
				$ret.='<select name="tambon" id="tambon" class="form-select -hidden"><option value="">==เลือกตำบล==</option></select>'._NL;
				$ret.='<select name="village" id="village" class="form-select -hidden"><option value="">==เลือกหมู่บ้าน==</option></select></div>'._NL;
				$ret.='<div class="form-item"><input type="submit" class="floating" value="บันทึก" /> <a href="#" class="sg-action" data-removeparent="form" data-rel="this">ยกเลิก</a></div>'._NL;
					$ret.='<div class="form-item"><label>หรือ ระบุบ้าน หมู่ที่ ตำบล อำเภอ จังหวัด ในช่องด้านล่างและเลือกจากรายการแสดง</label><input type="hidden" name="areacode" id="areacode" /><input name="address" tyepe="text" id="project-addtambon" class="sg-autocomplete form-text w-9" data-query="'.url('api/address').'" data-altfld="areacode" data-callback="submit" placeholder="ระบุ บ้าน หมู่ที่ ตำบล และเลือกอำเภอ/จังหวัดจากรายการที่แสดง" /></div>'._NL;
				$ret.='</form></div>'._NL;
			} else {
			$ret.=__project_form_addprov_listprov($tpid,$isEdit);
			}
		} else {
			$ret.=__project_form_addprov_listprov($tpid);
		}

/*
		$ret.='<h3>จังหวัดดำเนินการ</h3>';
		$dbs=mydb::select('SELECT * FROM %co_province%');
		$ret.='<form method="post">';
		$ret.='<ul class="project-provlist">';
		foreach ($dbs->items as $rs) {
			$ret.='<li><input type="checkbox" name="prov['.$rs->provid.']" value="'.$rs->provid.'">'.$rs->provname.'</li>';
		}
		$ret.='</ul>';
		$ret.='<input type="submit" value="บันทึก" class="button" /></form>';
		$ret.=print_o($post,'$post');
		$ret.='<style type="text/css">
		.project-provlist {margin:0;padding:0;list-style-type:none;}
		.project-provlist>li {width:160px; display:inline-block;}
		</style>';
		*/
		return $ret;
}

function __project_form_addprov_listprov($tpid,$isEdit=false,$autoid=NULL) {
	$stmt='SELECT pv.*, cot.`subdistname`, coa.`distname`, cop.`provname`
					FROM %project_prov% pv
						LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
						LEFT JOIN %co_district% coa ON coa.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
						LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
					WHERE `tpid`=:tpid'.($autoid?' AND `autoid`=:autoid':'');
	$provList=mydb::select($stmt,':tpid',$tpid,':autoid',$autoid);
	$gis['address']=array();
	if (!$autoid) $ret.='<ul id="project-provlist">';
	foreach ($provList->items as $item) {
		if ($item->village) $item->village=intval($item->village);
		$address=SG\implode_address($item);
		$ret.='<li>'.$address.($isEdit?' <a href="'.url('project/form/'.$tpid.'/addprov',array('delete'=>$item->autoid)).'" class="sg-action" data-rel="this" data-removeparent="li" data-confirm="ลบจังหวัดนี้?">X</a>':'').'</li>';
		$gis['address'][]='ต.'.$item->subdistname.' อ.'.$item->distname.' จ.'.$item->provname;
	}
	if (!$autoid) $ret.='</ul>';
	//$ret.=print_o($provList,'$provList');

		$ret.='<script>
			var gis='.json_encode($gis).';
			var latlng
					if (gis.address) {
						var geocoder = new google.maps.Geocoder();
						var center
						$.each( gis.address, function(i, address) {
							geocoder.geocode( { "address": address}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									latlng = results[0].geometry.location;
									$map.gmap("addMarker", {
										position: latlng,
										draggable: false,
										icon: "https://softganz.com/library/img/geo/circle-green.png",
									}).click(function() {
										$map.gmap("openInfoWindow", { "content": address }, this);
									});
									$map.gmap("get","map").setOptions({"center":latlng});
									//alert(latlng.lat()+","+latlng.lng())
								}
							});
						});
					}
	</script>';
	
	return $ret;
}
?>