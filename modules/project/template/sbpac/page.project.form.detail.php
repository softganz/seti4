<?php
/**
* Project detail
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_detail($self,$topic,$para,$body) {
	$tpid=$topic->tpid;
	$strategy=$roadmap='';
	$self->theme->class.=' project-status-'.$rs->project_statuscode;
	$rs=$topic->project;

	$info=project_model::get_tr($tpid,'info');

	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));

	$isInputRelation=project_model::is_inputrelation($tpid);
	$isAdmin=user_access('administer projects');
	$isEdit=($rs->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));
	$liketitle=$isEdit?'คลิกเพื่อแก้ไข':'';
	$editclass=$isEdit?'editable':'';
	$emptytext=$isEdit?'<span style="color:#999;">แก้ไข</span>':'';

	$activityCount=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" LIMIT 1',':tpid',$tpid)->total;
	$commentCount=mydb::select('SELECT COUNT(*) total FROM %topic_comments% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->total;

	$ret.='<div class="sg-tabs">';
	$ret.='<ul class="tabs"><li class="-active"><a href="#project-info">รายละเอียดโครงการ</a></li><li><a href="#relation">ความสอดคล้อง</a></li><li><a href="#activity">บันทึกกิจกรรม'.($activityCount?' ('.$activityCount.')':'').'</a></li><li><a href="#localreport">บันทึกประจำเดือน</a></li><li><a href="#comment">ความคิดเห็น'.($commentCount?' ('.$commentCount.')':'').'</a></li>';
	if ($isAdmin) {
		$adminCommentCount=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="admin" AND `part`="comment" LIMIT 1',':tpid',$tpid)->total;
		$ret.='<li><a href="#adminreport">บันทึกเจ้าหน้าที่'.($adminCommentCount?' ('.$adminCommentCount.')':'').'</a></li>';
	}
	$ret.='</ul>'._NL;

	if ($isEdit && !$isInputRelation) $ret.='<p class="notify">กรุณาระบุ <strong>"ความสอดคล้อง"</strong> ของโครงการให้ครบถ้วนทั้ง 3 ข้อ แล้ว<a href="'.url('paper/'.$tpid).'">คลิกที่นี่เพื่อรีเฟรช</a>ด้วยค่ะ</p>';

	$ret.='<div id="detail">'._NL;

	$tables = new Table();
	$tables->addClass('item-2col project-info');
	$tables->rows[]=array('<strong>ชื่อ'.($rs->prtype=='โครงการ'?'กิจกรรม/โครงการ':$rs->prtype).'</strong>','<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title'),$topic->title,$isEdit).'</strong>');

	//$stmt='SELECT * FROM %topic_parent% p WHERE p.`tpid`'
	$tables->caption='รายละเอียดโครงการของสำนัก/กอง/หน่วยงาน'; //.($topic->orgid==1?' ภายใน ศอ.บต.':'ภายนอก ศอ.บต.');

	$stmt='SELECT DISTINCT o.`orgid`,o.`name`
					FROM %org_officer% of
						LEFT JOIN %db_org% o USING(`orgid`) WHERE of.`uid`=:uid
					UNION SELECT o.`orgid`, o.`name` FROM %db_org% o WHERE o.`orgid`=:orgid';
	$orgList=mydb::select($stmt,':uid',i()->uid,':orgid',$topic->orgid);
	//$ret.=print_o($orgList,'$orgList');
	foreach ($orgList->items as $item) $departments[$item->orgid]=$item->name;
	if ($isEdit) {
		$tables->rows[]=array('หน่วยงาน',view::inlineedit(array('group'=>'topic','fld'=>'orgid','value'=>$topic->orgid),$departments[$topic->orgid],$isEdit,'select',$departments));
	} else {
		$tables->rows[]=array('หน่วยงาน',$rs->orgName);
	}
	if ($rs->orgParent) $tables->rows[]=array('หน่วยงานต้นสังกัด',$rs->orgParent);
	if (user_access('administer projects')) {
		$tables->rows[]=array('ประเภทโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prtype'),$rs->prtype,$isEdit,'select',array('แผนงาน'=>'แผนงาน','ชุดโครงการ'=>'ชุดโครงการ','โครงการ'=>'โครงการย่อย')));
	}

	$tables->rows[]=array('รหัสโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prid'),$rs->prid,$isEdit));
	$tables->rows[]=array('วันที่อนุมัติ',view::inlineedit(array('group'=>'project','fld'=>'date_approve','ret'=>'date','value'=>$rs->date_approve),$rs->date_approve,$isEdit,'datepicker'));

	// งบประมาณ
	$mainProjectStr.='<h3>งบประมาณ</h3><div id="project-info-budget">';
	$mainProjectStr.=R::Page('project.form.budget',$self,$topic);
	$mainProjectStr.='</div>';
	if ($isEdit) $mainProjectStr.='<a class="sg-action button" data-rel="#project-budget-form" href="'.url('project/form/'.$tpid.'/budget',array('tpid'=>$tpid,'action'=>'form')).'">เพิ่มงบประมาณ</a><div id="project-budget-form" class="card"></div>';


	$subProject=mydb::select('SELECT h.*,t.`title`,p.`prtype` FROM %topic_parent% h LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %project% p USING (`tpid`) WHERE h.`parent`=:tpid',':tpid',$tpid);
	if ($subProject->_num_rows) {
		$mainProjectStr.='<h3>'.$subProject->_num_rows.' โครงการย่อย</h3>';

		$subTable = new Table();
		$subTable->thead=array('no'=>'','โครงการย่อย','money'=>'งบประมาณ');
		$subTotal=0;
		$no=0;
		foreach ($subProject->items as $item) {
			$subTable->rows[]=array(++$no,'<a href="'.url('paper/'.$item->tpid).'">'.$item->title.'</a>'.' ('.$item->prtype.')',number_format($item->budget,2));
			$subTotal+=$item->budget;
		}
		$subTable->rows[]=array('','รวม <strong>'.$subProject->_num_rows.'</strong> โครงการ รวมงบประมาณทั้งหมด','<strong>'.number_format($subTotal,2).'</strong>');
		$mainProjectStr .= $subTable->build();
	}
	$tables->rows[]=array('<td colspan="2">'.$mainProjectStr.'</td>');

	$tables->rows[]=array('หลักการและเหตุผล',view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text1,$isEdit,'textarea'));
	$tables->rows[]=array('วัตถุประสงค์',view::inlineedit(array('group'=>'project','fld'=>'objective','ret'=>'html'),$rs->objective,$isEdit,'textarea'));
	$tables->rows[]=array('วิธีการดำเนินกิจกรรม',view::inlineedit(array('group'=>'info:basic','fld'=>'text2', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text2,$isEdit,'textarea'));
	$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'totaltarget', 'ret'=>'numeric'),$rs->totaltarget,$isEdit,'text').' คน');
	// ให้ย้ายรายละเอียดกลุ่มเป้าหมาย จาก $basicInfo->text3 ไปไว้ใน project:target , วิธีดำเนินกิจกรรม จาก $basicInfo->text2 ไปไว้ project:activity
	//	$tables->rows[]=array('วิธีการดำเนินกิจกรรม',view::inlineedit(array('group'=>'project','fld'=>'activity', 'ret'=>'html'),$rs->activity,$isEdit,'textarea'));
	//$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'target', 'ret'=>'html'),$rs->target,$isEdit,'textarea'));
	$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'info:basic','fld'=>'text3', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text3,$isEdit,'textarea'));

	if ($rs->area) $tables->rows[]=array('พื้นที่ดำเนินการ',$rs->area);
	//$tables->rows[]=array('พื้นที่ดำเนินการ',view::inlineedit(array('group'=>'project','fld'=>'area', 'class'=>'w-10'),$rs->area,$isEdit).'<br />(ให้ระบุพื้นที่ หมู่บ้าน ตำบล อำเภอ จังหวัด)');

	$provStr=R::Page('project.form.addprov',$self,$topic);
	/*
	$provStr.='<ul id="project-provlist">';
	$provList=mydb::select('SELECT pv.*, cop.`provname` FROM %project_prov% pv LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat` WHERE `tpid`=:tpid',':tpid',$tpid);
	foreach ($provList->items as $item) {
		$provStr.='<li>'.$item->provname.($isEdit?' <a href="'.url('project/form/'.$tpid.'/addprov',array('delete'=>$item->autoid)).'" class="sg-action" data-rel="this" data-removeparent="li" data-confirm="ลบจังหวัดนี้?">X</a>':'').'</li>';
	}
	$provStr.='</ul>';
	*/

	if ($isEdit) {
		$provStr.='<a class="sg-action" data-rel="#project-addprovlink" href="'.url('project/form/'.$tpid.'/addprov',array('form'=>'show')).'" title="เพิ่มพื้นที่ดำเนินการ"><i class="icon -add"></i></a><div id="project-addprovlink"></div>';
	}
	$tables->rows[]=array('จังหวัด/ประเทศ'.($isEdit?'<span class="require">!</span>':''),$provStr);

	$tables->rows[]=array('ระยะเวลาดำเนินกิจกรรม',view::inlineedit(array('group'=>'project','fld'=>'date_from','ret'=>'date','require'=>true,'value'=>$rs->date_from?sg_date($rs->date_from,'d/m/Y'):''),
		$rs->date_from?sg_date($rs->date_from,'j ดดด ปปปป'):''
		,$isEdit,'datepicker').' - '.view::inlineedit(array('group'=>'project','fld'=>'date_end','ret'=>'date','require'=>true, 'value'=>$rs->date_end?sg_date($rs->date_end,'d/m/Y'):''),
		$rs->date_end?sg_date($rs->date_end,'j ดดด ปปปป'):''
		,$isEdit,'datepicker'));
	$tables->rows[]=array('ตัวชี้วัดกิจกรรม',view::inlineedit(array('group'=>'info:basic','fld'=>'text4', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text4,$isEdit,'textarea'));

	$tables->rows[]=array('ผลการดำเนินงานที่คาดว่าจะได้รับ',view::inlineedit(array('group'=>'info:basic','fld'=>'text5', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text5,$isEdit,'textarea'));


	$tables->rows[]=array(
				'ผู้รับผิดชอบกิจกรรม',
				//'หน่วยงาน/ส่วนราชการ : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail1', 'tr'=>$basicInfo->trid),$basicInfo->detail1,$isEdit).'<br />'
				//.'เบอร์โทร :'.view::inlineedit(array('group'=>'info:basic','fld'=>'detail2', 'tr'=>$basicInfo->trid),$basicInfo->detail2,$isEdit).'<br />'
				'เจ้าหน้าที่รับผิดชอบ : '.view::inlineedit(array('group'=>'project','fld'=>'prowner'),$rs->prowner,$isEdit).'<br />'
				.'ตำแหน่ง : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail1', 'tr'=>$basicInfo->trid),$basicInfo->detail1,$isEdit).'<br />'
				.'เบอร์โทร : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail2', 'tr'=>$basicInfo->trid),$basicInfo->detail2,$isEdit)
				);
	$tables->rows[]=array(
				'ผู้เสนอกิจกรรม<br />(หน่วยงานที่เสนอ)',
				'ชื่อ - สกุล : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail3', 'tr'=>$basicInfo->trid),$basicInfo->detail3,$isEdit).'<br />'
				.'ตำแหน่ง : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail4', 'tr'=>$basicInfo->trid),$basicInfo->detail4,$isEdit).'<br />'
				.'เบอร์โทร : '.view::inlineedit(array('group'=>'info:basic','fld'=>'detail5', 'tr'=>$basicInfo->trid),$basicInfo->detail5,$isEdit).'<br />'
				);
	//UPDATE `sgz_project_tr` SET `detail2`=`detail3`,`detail1`="", `detail3`="",`detail4`="" WHERE `formid` LIKE 'info' AND `part` LIKE 'basic'

	// Show member of this project
	$member=mydb::select('SELECT u.uid, u.username, u.name, tu.membership FROM %topic_user% tu LEFT JOIN %users% u ON u.uid=tu.uid WHERE `tpid`=:tpid',':tpid',$topic->tpid);
	$name='<ul class="member">'._NL;
	foreach ($member->items as $mrs) {
		$name.='<li>';
		$name.='<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />'.$mrs->name.' ('.($mrs->uid==$topic->uid?'Creater':'Join '.$mrs->membership).') ';
		$name.=(user_access('administer projects') || project_model::is_trainer_of($topic->tpid) || (i()->uid==$topic->uid && $mrs->membership=='Owner' && $mrs->uid!=i()->uid)?'<a class="sg-action" href="'.url('project/edit/removeowner/'.$tpid.'/'.$mrs->uid).'" data-rel="notify" data-confirm="ต้องการลบสมาชิกออกจากโครงการ กรุณายืนยัน?" data-removeparent="li"><i class="icon -cancel -gray"></i></a> ':'');
		//$name.='[i='.i()->uid.','.'topic='.$topic->uid.',owner='.$mrs->uid.']';
		$name.='</li>'._NL;
	}
	$name.='</ul>'._NL;
	$ui=new ui();
	if ((user_access('administer projects') || in_array('trainer',i()->roles)) && !project_model::is_trainer_of($tpid)) $ui->add('<a href="'.url('project/edit/addtrainer/'.$topic->tpid).'">เพิ่มเป็นพี่เลี้ยง</a>');
	if (user_access('administer projects') || project_model::is_trainer_of($topic->tpid) || project_model::is_owner_of($topic->tpid)) {
		$ui->add('<a class="sg-action" href="'.url('project/edit/addowner/'.$tpid).'" data-rel="#addowner">เพิ่มเจ้าของโครงการ</a><span id="addowner"></span>');
	}
	$name.=$ui->build();

	if ($isEdit) $tables->rows[]=array('ละติจูด-ลองจิจูด',view::inlineedit(array('group'=>'project','fld'=>'location'),($rs->location?$rs->lat.','.$rs->lnt:''),$isEdit));
	$tables->rows[]=array('ผู้ดำเนินการติดตามสนับสนุนโครงการ',$name);

	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL
					.'<div class="project-info-general">'._NL
					.$tables->build()._NL;
	$ret.=__sbpac_project_doc($tpid,$isEdit);
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$topic->uid)).'" title="'.htmlspecialchars($mrs->name).'"><img src="'.model::user_photo($topic->username).'" width="32" height="32" alt="'.htmlspecialchars($topic->owner).'" /> '.$topic->owner.'</a> เมื่อวันที่ '.sg_date($topic->created,'ว ดดด ปปปป H:i').' น.</p>';

	$ret.='</div>'._NL
					.'<div id="project-map" width="400" height="400">'._NL
					.'<div class="project-status project-status-'.$rs->project_statuscode.'">สถานภาพโครงการ <span>'.$rs->project_status.'</span></div>'._NL
					.'<div id="map_canvas"></div>'._NL
					.'</div><!--project-map--><br clear="all" />'._NL
					.'</div><!--project-info-->'._NL;

	$sector=mydb::select('SELECT `sector` FROM %db_org% WHERE `orgid`=:orgid LIMIT 1',':orgid',$topic->orgid)->sector;
	if ($sector>1) {
		$ret.='<div id="monthly">'._NL;
		$ret.='<h3>ผลการดำเนินงานประจำเดือน</h3>';
		$ret.=R::Page('project.form.monthly',$self,$topic,$para,$body,true)._NL;
		$ret.='</div><!-- monthly -->'._NL;
	}


	if (_ON_HOST && in_array($topic->type,explode(',',cfg('social.share.type'))) && !is_home() && $topic->property->option->social) {
		$ret.=view::social(url('paper/'.$topic->tpid));
	}
	$ret.='</div><!--detail -->';

	if ($isEdit) $inlineAttr['class']='inline-edit';
	$inlineAttr['class'].=' -hidden';
	$ret.='<div id="relation" '.sg_implode_attr($inlineAttr).'>'._NL;

	$infoRels=project_model::get_tr($tpid,'info:rel');

	$ret.='<h3>1. ความสอดคล้องตามแผนปฏิบัติการแก้ไขปัญหาและพัฒนาของรัฐบาล</h3>'._NL;
	$ret.=__sbpac_project_rel(model::get_category('project:rel-govplan'),$infoRels,$isEdit)._NL;

	$ret.='<h3>2. ความสอดคล้องกับยุทธศาสตร์และแผนปฏิบัติการพัฒนาจังหวัดชายแดนภาคใต้</h3>'._NL;
	$ret.=__sbpac_project_rel(model::get_category('project:rel-southplan'),$infoRels,$isEdit)._NL;

	$ret.='<h3>3. ความสอดคล้องกับตัวชี้วัดแผนงานการแก้ปัญหาจังหวัดชายแดนภาคใต้</h3>'._NL;
	$ret.=__sbpac_project_rel(model::get_category('project:rel-kpi'),$infoRels,$isEdit)._NL;

	$ret.='</div><!--tab:relation-->';

	$ret.='<div id="activity" class="-hidden">'._NL;
	$ret.=R::Page('project.form.show_activity',$self,$topic,$para,$body,false)._NL;
	$ret.='</div><!-- tabs activity -->'._NL;

	$ret.='<div id="localreport" class="-hidden">'._NL;
	$ret.=R::Page('project.form.monthly',$self,$topic,$para,$body,false)._NL;
	$ret.='</div><!-- tabs localreport -->'._NL;

	$ret.='<div id="comment" class="-hidden">'._NL.$body->comment._NL.'</div>'._NL;

	// บันทึกเจ้าหน้าที่
	if ($isAdmin) {
		$ret.='<div id="adminreport" class="-hidden">'._NL;
		$ret.=R::Page('project.form.adminreport',$self,$topic,$para,$body,false)._NL;
		$ret.='</div><!--adminreport-->'._NL;
	}




	$ret.='</div><!-- for sg-tabs -->'._NL;

	unset($body->comment);

	//$ret.=print_o($topic,'$topic');

	$gis['zoom']=7;

	$stmt='SELECT pv.*, cot.`subdistname`, coa.`distname`, cop.`provname`
					FROM %project_prov% pv
						LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
						LEFT JOIN %co_district% coa ON coa.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
						LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
					WHERE `tpid`=:tpid'.($autoid?' AND `autoid`=:autoid':'');
	$provList=mydb::select($stmt,':tpid',$tpid,':autoid',$autoid);
	foreach ($provList->items as $item) {
		$gis['address'][]='ต.'.$item->subdistname.' อ.'.$item->distname.' จ.'.$item->provname;
	}


	if ($rs->lat) {
		$gis['center']=$rs->lat.','.$rs->lnt;
		$gis['zoom']=8;
		$gis['current']=array('latitude'=>$rs->lat,
											'longitude'=>$rs->lnt,
											'title'=>$rs->title,
											'content'=>'<h4>'.$rs->title.'</h4><p>พื้นที่ : '.$rs->area.'</p>'
											);
	} else {
		$gis['center']=property('project:map.center:NULL');
	}
	if ($isEdit) $ret.='<script type="text/javascript"><!--
		var $map=$("#map_canvas");
		$(document).ready(function() {
			var imgSize = new google.maps.Size(16, 16);
			var gis='.json_encode($gis).';
			var is_point=false;
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: true
				})
				.bind("init", function(event, map) {
					if (gis.current) {
						marker=gis.current;
						is_point=true;
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							draggable: true,
						}).click(function() {
							//$map.gmap("openInfoWindow", { "content": "ลากหมุดเพื่อเปลี่ยนตำแหน่ง" }, this);
						}).mouseover(function() {
							//	$map.gmap("openInfoWindow", { "content": "ลากหมุดเพื่อเปลี่ยนตำแหน่ง" }, this);
						}).dragend(function(event) {
							var latLng=event.latLng.lat()+","+event.latLng.lng();
							projectUpdate($("[data-fld=\"location\"]"), latLng);
						});
					}

					if (gis.address) {
						var geocoder = new google.maps.Geocoder();
						var center
						$.each( gis.address, function(i, address) {
							geocoder.geocode( { "address": address}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									var latlng = results[0].geometry.location;
									$map.gmap("addMarker", {
										position: latlng,
										draggable: false,
										icon: "https://softganz.com/library/img/geo/circle-green.png",
									}).click(function() {
										$map.gmap("openInfoWindow", { "content": address }, this);
									});
									if (!gis.current) {
										center=latlng;
										map.setCenter(center);
									}
								}
							});
						});

/*
									$map.gmap("addMarker")
										lat: latlng.lat(),
										lng: latlng.lng(),
										icon: "https://softganz.com/library/img/geo/circle-green.png",
										infoWindow: {content: address},
									});

									map.setCenter(results[0].geometry.location);
									var marker = new google.maps.Marker({
										map: map,
										position: results[0].geometry.location
									});
*/

/*
							geocoder.geocode({
								address: address,
								function(results, status) {
									if (status == "OK") {
										var latlng = results[0].geometry.location;
										map.addMarker({
											lat: latlng.lat(),
											lng: latlng.lng(),
											icon: "https://softganz.com/library/img/geo/circle-green.png",
											infoWindow: {content: address},
										});
										if (!gis.current) map.setCenter(latlng.lat(), latlng.lng());
										notify(address+" , "+latlng.lat()+","+ latlng.lng()+" zoom:"+gis.zoom);
									}
								}
							});
*/

					}


					$(map).click(function(event, map) {
						if (!is_point) {
							$map.gmap("addMarker", {
								position: event.latLng,
								draggable: true,
								bounds: false
							}, function(map, marker) {
								// After add point
								var latLng=event.latLng.lat()+","+event.latLng.lng();
								projectUpdate($("[data-fld=\"location\"]"), latLng);
							}).dragend(function(event) {
								var latLng=event.latLng.lat()+","+event.latLng.lng();
								projectUpdate($("[data-fld=\"location\"]"), latLng);
							});
							is_point=true;
						}
					});
				});
		});
		--></script>';
				else {
					$ret.='<script type="text/javascript"><!--
		$(document).ready(function() {
			var imgSize = new google.maps.Size(16, 16);
			var gis='.json_encode($gis).';
			var is_point=false;
			$map=$("#map_canvas");
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: true
				})
				.bind("init", function(event, map) {
					if (gis.current) {
						marker=gis.current;
						is_point=true;
						$map.gmap("addMarker", {
							position: new google.maps.LatLng(marker.latitude, marker.longitude),
							draggable: false,
						}).click(function() {
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						}).mouseover(function() {
							$map.gmap("openInfoWindow", { "content": marker.mouseover }, this);
						});
					}
				});
		});
		--></script>';
	}

	return $ret;
}

function __sbpac_project_rel($category,$infoRels,$isEdit) {
foreach ($category as $key=>$item) {
	$relTr=NULL;
	foreach ($infoRels->items['rel'] as $relItem) {
		if ($key==$relItem->parent) {
			$relTr=$relItem;
			break;
		}
	}
	$ret.=view::inlineedit(array('group'=>'info:rel:'.$key,'fld'=>'rate1', 'tr'=>$relTr->trid, 'parent'=>$key, 'value'=>$relTr->rate1),'1:'.$item,$isEdit,'checkbox').'<br />'._NL;
}
return $ret;
}

function __sbpac_project_doc($tpid,$isEdit=false) {
	$docDb=mydb::select('SELECT * FROM %topic_files% WHERE `tpid`=:tpid AND `type`="doc" AND `cid`=0 ',':tpid',$tpid);
	if ($docDb->_num_rows) {
		$ret.='<h4>ไฟล์เอกสาร</h4>';
		$tableDoc = new Table();
		foreach ($docDb->items as $item) {
			$tableDoc->rows[]=array($item->title,'<a href="'.cfg('url').'upload/forum/'.$item->file.'">ดาวน์โหลด</a>',$isEdit?'<a href="'.url('paper/'.$tpid.'/edit/doc/delete/'.$item->fid.'/confirm/yes').'" class="sg-action" data-removeparent="tr" data-rel="this" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?">X</a>':'');
		}
		$ret .= $tableDoc->build();
	}
	// Upload document form
	if ($isEdit) {
		$form = new Form([
			'variable' => 'document',
			'enctype' => 'multipart/form-data',
			'action' => url('paper/'.$tpid.'/edit/doc'),
			'id' => 'project-edit-doc',
			'children' => [
				'ret' => ['type' => 'hidden', 'value' => 'paper/'.$tpid,],
				'title' => [
					'type' => 'select',
					'label' => 'อัพโหลดไฟล์รายละเอียดโครงการ',
					'options' => [
						'ไฟล์รายละเอียดโครงการ'=>'ไฟล์รายละเอียดโครงการ',
						'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์'=>'ไฟล์รายงานปิดโครงการ-ฉบับสมบูรณ์',
					],
				],
				'document' => [
					'name' => 'document',
					'type' => 'file',
					'size' => 50,
				],
				'save' => [
					'type' => 'button',
					'value' => 'อัพโหลด',
					'description' => '<strong>ข้อกำหนดในการส่งไฟล์ไฟล์รายละเอียดโครงการ</strong><ul><li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li><li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li><li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li></ul>',
				],
			], // children
		]);

		$ret .= $form->show();
	}
	return $ret;
}


/*
		//$tables->rows[]=array('ความถูกต้องที่สามารถดำเนินการได้ตามระเบียบการใช้จ่ายงบประมาณที่เกี่ยวข้อง',view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text6,$isEdit,'textarea'));
		//$tables->rows[]=array('ความเห็นของ สนผ.<br />(ผอ.สนผ. ศอ.บต. ให้ความเห็นชอบ)',view::inlineedit(array('group'=>'info:basic','fld'=>'text7', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text7,$isEdit,'textarea'));
		//$tables->rows[]=array('ผู้เห็นชอบกิจรรม<br />(รองเลขาธิการ ศอ.บต.)',view::inlineedit(array('group'=>'info:basic','fld'=>'text8', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text8,$isEdit,'textarea'));
		//$tables->rows[]=array('ผู้อนุมัติ<br />(เลขาธิการ ศอ.บต. เป็นผู้อนุมัติกิจกรรม)',view::inlineedit(array('group'=>'info:basic','fld'=>'text9', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text9,$isEdit,'textarea'));

		$rel1category=model::get_category('project:rel-govplan');
		foreach ($rel1category as $key=>$item) {
			$relTr=NULL;
			foreach ($infoRels->items['rel'] as $relItem) {
				if ($key==$relItem->parent) {
					$relTr=$relItem;
					break;
				}
			}
			$ret.=view::inlineedit(array('group'=>'info:rel:'.$key,'fld'=>'rate1', 'tr'=>$relTr->trid, 'parent'=>$key, 'value'=>$relTr->rate1),'1:'.$item,$isEdit,'checkbox').'<br />'._NL;
		}

		$rel2category=model::get_category('project:rel-southplan');
		$ret.='<h3>2. ความสอดคล้องกับยุทธศาสตร์และแผนปฏิบัติการพัฒนาจังหวัดชายแดนภาคใต้ พ.ศ. ๒๕๕๘</h3>';
		$no=0;
		foreach ($rel2category as $key=>$item) {
			$relTr=NULL;
			foreach ($infoRels->items['rel'] as $relItem) {
				if ($key==$relItem->parent) {
					$relTr=$relItem;
					break;
				}
			}
			//$ret.=print_o($relTr,'$relTr');
			$ret.=view::inlineedit(array('group'=>'info:rel:'.$key,'fld'=>'rate1', 'tr'=>$relTr->trid, 'parent'=>$key, 'value'=>$relTr->rate1),'1:'.(++$no).'. '.$item,$isEdit,'checkbox').'<br />'._NL;
		}

		$rel3category=model::get_category('project:rel-kpi');
		$ret.='<h3>3. ความสอดคล้องกับตัวชี้วัดแผนงานการแก้ปัญหาจังหวัดชายแดนภาคใต้</h3>';
		$no=0;
		foreach ($rel3category as $key=>$item) {
			$relTr=NULL;
			foreach ($infoRels->items['rel'] as $relItem) {
				if ($key==$relItem->parent) {
					$relTr=$relItem;
					break;
				}
			}
			//$ret.=print_o($relTr,'$relTr');
			$ret.=view::inlineedit(array('group'=>'info:rel:'.$key,'fld'=>'rate1', 'tr'=>$relTr->trid, 'parent'=>$key, 'value'=>$relTr->rate1),'1:'.(++$no).'. '.$item,$isEdit,'checkbox').'<br />'._NL;
		}

		$ret.='<h3>4. ความสอดคล้องงบประมาณที่ใช้จ่าย</h3>';
		$ret.='<div class="widget request" id="project-info-budget" widget-request="project/edit/budget" data-tpid="'.$tpid.'" /></div>';
			1. งบประมาณที่ขอ <strong>'.number_format($topic->project->budget,2).'</strong> บาท<br />
			2. งบประมาณที่เห็นชอบ <strong>'.number_format($topic->project->budget,2).'</strong> บาท';

		$rel5category=model::get_category('project:rel-cost');
		$ret.='<h3>5. ความสอดคล้องการคิดค่างาน</h3>';
		$no=0;
		foreach ($rel5category as $key=>$item) {
			$relTr=NULL;
			foreach ($infoRels->items['rel'] as $relItem) {
				if ($key==$relItem->parent) {
					$relTr=$relItem;
					break;
				}
			}
			//$ret.=print_o($relTr,'$relTr');
			$ret.=view::inlineedit(array('group'=>'info:rel:'.$key,'fld'=>'rate1', 'tr'=>$relTr->trid, 'parent'=>$key, 'value'=>$relTr->rate1),'1:'.(++$no).'. '.$item,$isEdit,'checkbox').'<br />'._NL;
		}
*/
?>