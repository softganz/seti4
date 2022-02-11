<?php
function map_add($self) {
	$mapID = post('id');
	$mapGroup = SG\getFirst(post('gr'), post('mapgroup'));

	$ret = '<nav class="nav iconset -sg-text-right"><a href="javascript:void(0)" data-action="add-cancel" data-id="'.$mapID.'" title="ยกเลิก คำเตือน : ข้อมูลที่ยังไม่บันทึกจะสูญหาย"><i class="icon -close"></i></a></nav>';

	if (!user_access('create maps')) {
		$ret .= message('error','access denied');
		return $ret;
	}

	if ($mapID) {
		$stmt = 'SELECT
							m.*
						, CONCAT(X(`latlng`),",",Y(`latlng`)) `latlng`
						, X(`latlng`) `lat`, Y(`latlng`) `lnt`
						FROM %map_networks% m
						WHERE `mapid` = :mapid LIMIT 1';
		$mapData = mydb::select($stmt,':mapid',$mapID);
		if ($mapData->mapgroup) $mapGroup = $mapData->mapgroup;
	}

	if ($mapGroup) {
		$mapGroupRs=mydb::select('SELECT * FROM %map_name% WHERE `mapgroup`=:mapgroup LIMIT 1',':mapgroup',$mapGroup);
	}

	$ret.='<h3>'.$mapGroupRs->mapname.'</h3>';
	//$self->theme->title='แผนที่เครือข่ายเฝ้าระวังและช่วยเหลือผู้ประสพภัยพิบัติ จังหวัดสงขลา รุ่นทดสอบ 1';

	$form = new Form('mapping', url('map/update'), 'map-edit');

	$form->addField('mapgroup', array('type' => 'hidden', 'value' => SG\getFirst($mapGroup,$mapData->mapgroup)));

	$form->addField('mapid', array('type' => 'hidden', 'value' => $mapData->mapid));

	$form->addField('areacode', array('type' => 'hidden', 'id' => 'areacode', 'value' => $mapData->areacode));

	$form->addText('<div class="box"><h3>ข้อมูลแผนที่</h3>');

	$form->addField(
					'who',
					array(
						'type' => 'text',
						'label' => SG\getFirst($mapGroupRs->title_who,cfg('map-who'),'ใคร - Who ?'),
						'class' => '-fill',
						'placeholder' => 'ระบุชื่อคน,องค์กร,หน่วยงาน',
						'value' => htmlspecialchars($mapData->who),
						'autocomplete' => 'off',
					)
				);

	$form->addField(
					'dowhat',
					array(
						'type' => 'text',
						'label' => SG\getFirst($mapGroupRs->title_dowhat,cfg('map-dowhat'),'ทำอะไร - Do what ?'),
						'class' => '-fill',
						'placeholder' => 'เช่น แจกอาหาร,ที่จอดรถ',
						'value' => htmlspecialchars($mapData->dowhat),
					)
				);

	$whenValue = array();
	if ($mapData->prepare) $whenValue[]='prepare';
	if ($mapData->during) $whenValue[]='during';
	if ($mapData->after) $whenValue[]='after';

	$form->addField(
					'when',
					array(
						'type' => 'checkbox',
						'label' => 'เมื่อไหร่ - When ?',
						'class' => '-fill',
						'options' => array('prepare'=>'ก่อนเกิดเหตุ','during'=>'ระหว่างเกิดเหตุ','after'=>'หลังเกิดเหตุ'),
						'display' => 'inline',
						'multiple' => true,
						'value' => $whenValue,
					)
				);

	$form->addField(
					'address',
					array(
						'type' => 'text',
						'label' => SG\getFirst($mapGroupRs->title_where,cfg('map-address'),'อยู่ที่ไหน - Where ?'),
						'class' => 'sg-address -fill',
						'placeholder' => 'ระบุที่อยู่ ตำบล อำเภอ จังหวัด โทรศัพท์',
						'value' => htmlspecialchars($mapData->address),
						'attr' => array('data-altfld' => 'areacode'),
					)
				);

	$form->addField(
					'latlng',
					array(
						'type' => 'text',
						'label' => 'พิกัด - GIS ? '.($mapData->latlng ? '' : '<a href="javascript:void(0)" title="ปักหมุดตามที่อยู่" onclick="makePinFromAddress()"><i class="icon -pin"></i></a>'),
						'class' => '-fill',
						'placeholder' => 'ระบุพิกัดหรือคลิกบนแผนที่แล้วลากหมุดเพื่อย้ายตำแหน่ง',
						'value' => htmlspecialchars($mapData->latlng),
						'description' => $mapData->latlng ? '' : 'คำแนะนำ:ป้อน <strong>อยู่ที่ไหน - Where</strong> ก่อนแล้วจะสามารถคลิกปุ่มวางหมุดจะหาตำแหน่งบนแผนที่ให้',
					)
				);

	$form->addField(
					'submit',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>บันทึก'.($mapData->mapid?'การแก้ไข':'').'</span>',
						'pretext' => '<a class="btn -cancel -gray" data-action="add-cancel" data-id="'.$mapID.'" href="'.url('map/'.$mapData->mapgroup).'" title="ยกเลิก คำเตือน : ข้อมูลที่ยังไม่บันทึกจะสูญหาย"><i class="icon -cancel"></i><span>{tr:CANCEL}</span></a>',
						'container' => array('class'=>'-sg-text-right'),
					)
				);

	$form->addField(
					'detail',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียดเพิ่มเติม - More detail ?',
						'rows' => 2,
						'class' => '-fill',
						'placeholder' => 'รายละเอียดเพิ่มเติม',
						'value' => htmlspecialchars($mapData->detail),
					)
				);

	$form->addField(
					'privacy',
					array(
						'type' => 'radio',
						'label' => 'ความเป็นส่วนตัว - Privacy',
						'options' => array('private' => 'ให้ฉันเห็นเพียงคนเดียว', 'group' => 'ให้มองเห็นเฉพาะในกลุ่ม', 'public' => 'ให้ทุกคนมองเห็นได้'),
						'display' => 'inline',
						'value' => SG\getFirst($mapData->privacy,'public'),
					)
				);

	$form->addText('</div>');

	if (empty($mapData->mapid)) {
		$form->addText('<div class="box"><h3>รายละเอียดผู้ส่งข้อมูล</h3>');
		$form->addField(
						'poster',
						array(
							'type' => 'text',
							'label' => 'ชื่อผู้ส่งข้อมูล - Poster ?',
							'placeholder' => 'ระบุชื่อผู้ส่งข้อมูล',
							'value' => htmlspecialchars($mapData->poster),
							'description' => 'คำแนะนำ:ป้อน <strong>รายละเอียดของผู้ส่งข้อมูล</strong> จะถูกเก็บไว้เป็นความลับ ไม่มีการเผยแพร่ผ่านเว็บไซท์ มีไว้สำหรับการประสานงานกลับในกรณีที่ต้องการทราบข้อมูลเพิ่มเติมเท่านั้น',
						)
					);

		$form->addField(
						'email',
						array(
							'type' => 'text',
							'label' => 'อีเมล์ - E-Mail ?',
							'placeholder' => 'name@example.com',
							'value' => htmlspecialchars($mapData->email),
						)
					);

		$form->addField(
						'phone',
						array(
							'type' => 'text',
							'label' => 'โทรศัพท์ - Phone ?',
							'placeholder' => 'xxx xxx xxxx',
							'value' => htmlspecialchars($mapData->phone),
						)
					);
		$form->addText('</div>');
	}

	$ret .= $form->build();

	$ret.='<script type="text/javascript"><!--
	function makePinFromAddress() {
		console.log("Create marker from address")
			addEnable=false;
			var $mapGis = $("#edit-mapping-latlng");
			var locationStr = $("#edit-mapping-address").val()
			locationStr = locationStr.substr(locationStr.indexOf("ตำบล") - 1);


			console.log(locationStr)
			//searchLocation($("#edit-mapping-address").val())

			if (locationStr) {
				GMaps.geocode({
				  address: locationStr,
				  callback: function(results, status) {
				    if (status == "OK") {
				      var latlng = results[0].geometry.location;
				      map.setCenter(latlng.lat(), latlng.lng());
				      currentLoc.marker=map.addMarker({
								lat: latlng.lat(),
								lng: latlng.lng(),
								icon: "https://maps.google.com/mapfiles/arrow.png",
								infoWindow: {content: locationStr},
							});

							var mylat = latlng.lat()
							var mylng = latlng.lng()

							var marker = {lat: mylat, lng: mylng}
							$mapGis.val(mylat + "," + mylng)

							addMarker = map.addMarker({
								lat: marker.lat,
								lng: marker.lng,
								draggable: true,
								dragend: function(e) { $mapGis.val(e.latLng.lat()+","+e.latLng.lng()); },
							});

						} else {
							console.log("Location not found")
						}
				  }
				});
			} else {
				var mylat = map.getCenter().lat()
				var mylng = map.getCenter().lng()

				var marker = {lat: mylat, lng: mylng}
				$mapGis.val(mylat + "," + mylng)

				addMarker = map.addMarker({
					lat: marker.lat,
					lng: marker.lng,
					draggable: true,
					dragend: function(e) { $mapGis.val(e.latLng.lat()+","+e.latLng.lng()); },
				});
			}

			/*
			*/

	}

	$(document).ready(function() {
		if ($("#edit-mapping-mapid").val()!="") {
			// Case of edit marker
			addEnable=false;
			var mapID=$("#edit-mapping-mapid").val();
			var $mapGis = $("#edit-mapping-latlng");

			if ($mapGis.val()) {
				map.removeMarker(markers[mapID]);
				marker=gis.markers[mapID];

				addMarker=map.addMarker({
					lat: marker.lat,
					lng: marker.lng,
					draggable: true,
					dragend: function(e) { $("#edit-mapping-latlng").val(e.latLng.lat()+","+e.latLng.lng()); },
				});
			}
		} else {
			// Case of add new marker
			console.log("Add new marker on map")
		}
	});
	</script>';
	return $ret;
}
?>