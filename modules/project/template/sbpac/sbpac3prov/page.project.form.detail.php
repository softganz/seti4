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
function project_form_detail($self, $topic = NULL, $para = NULL, $body = NULL) {
	$tpid=$topic->tpid;
	$project=$topic->project;
	$self->theme->class.=' project-status-'.$project->project_statuscode;

	$projectInfo=R::Model('project.get',$tpid);


	$info=project_model::get_info($tpid,'info');
	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));


	//$ret.=print_o($topic,'$topic');
	$isAdmin=$project->isAdmin;
	$isTrainer=project_model::is_trainer_of($topic->tpid);
	$isEdit=$project->isEdit;
	$isEditDetail=$project->isEditDetail;
	$lockReportDate=$project->lockReportDate;

	$showMap = false;

	$liketitle=$isEdit?'คลิกเพื่อแก้ไข':'';
	$editclass=$isEdit?'editable':'';
	$emptytext=$isEdit?'<span style="color:#999;">แก้ไข</span>':'';

	$ret.='<div class="sg-tabs project--info">'._NL;



	// รายละเอียดโครงการ
	$ret.='<div id="detail">'._NL;

	$tables = new Table();
	$tables->addClass('item__card project-info');
	$tables->colgroup=array('width="30%"','width="70%"');
	$tables->caption='รายละเอียดโครงการ' . ($isAdmin && $project->prtype != 'โครงการ' ? ' (' . $project->prtype .')' : '');

	/*
	if ($isAdmin) {
		$tables->rows[]=array('ประเภท',view::inlineedit(array('group'=>'project','fld'=>'prtype','class'=>'-fill'),$project->prtype,$isEdit,'select',array('แผนงาน'=>'แผนงาน','ชุดโครงการ'=>'ชุดโครงการ','โครงการ'=>'โครงการย่อย')));
	}
	*/

	$tables->rows[]=array('เลขที่ข้อตกลง',view::inlineedit(array('group'=>'project','fld'=>'agrno','class'=>'-fill'),$project->agrno,$isEdit));
	$tables->rows[]=array('รหัสโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prid','class'=>'-fill'),$project->prid,$isEditDetail));
	$tables->rows[]=array('ชื่อโครงการ','<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$topic->title,$isEditDetail).'</strong>');





	// Show project set
	$dbs=mydb::select('SELECT * FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `prtype` IN ("แผนงาน","ชุดโครงการ") AND `project_status`="กำลังดำเนินโครงการ"');
	$projectSets=array();
	foreach ($dbs->items as $item) $projectSets[$item->tpid]=$item->title;

	$tables->rows[]=array('ชุดโครงการ',view::inlineedit(array('group'=>'project','fld'=>'projectset','class'=>'-fill','value'=>$project->projectset),$project->projectset_name,$isAdmin,'select',$projectSets));


	/*
	if ($isAdmin) {
		$projectIssues=array(
			1=>'ประเด็นที่ 1 งานรักษาความปลอดภัยชีวิตและทรัพย์สิน',
			'ประเด็นที่ 2 งานอำนวยความยุติธรรมและเยียวยา',
			'ประเด็นที่ 3 งานสร้างความเข้าใจทั้งในและต่างประเทศและเรื่องสิทธิมนุษยชน',
			'ประเด็นที่ 4 งานการศึกษา ศาสนา และศิลปวัฒนธรรม',
			'ประเด็นที่ 5 งานพัฒนาตามศักยภาพของพื้นที่และคุณภาพชีวิตประชาชน',
			'ประเด็นที่ 6 งานแสวงหาทางออกจากความขัดแย้งโดยสันติวิธี',
			'ประเด็นที่ 7 งานขับเคลื่อนการพัฒนาโครงการเมืองต้นแบบ สามเหลี่ยม มั่นคง มั่งคั่ง ยั่งยืน',
			'ประเด็นที่ 8 งานขับเคลื่อนนโยบายการแก้ไขปัญหา จชต. ปี 60-62',
			'ประเด็นที่ 9 งานป้องกันและแก้ไขปัญหายาเสพติด',
			'ประเด็นที่ 10 งานพัฒนาสร้างศักยภาพองค์กรภาคประชาสังคม'
		);
		$tables->rows[]=array('ประเภทโครงการ/ประเด็นงาน',view::inlineedit(array('group'=>'project','fld'=>'supporttype','class'=>'-fill','value'=>$project->supporttype),$projectIssues[$project->supporttype],$isEditDetail,'select',$projectIssues));
	}
	*/

	$strategicList = array(
		1 => 'ยุทธศาสตร์คนดีมีคุณธรรม',
		'ยุทธศาสตร์อยู่รอดปลอดภัย',
		'ยุทธศาสตร์อยู่เย็น เป็นสุข',
		'ยุทธศาสตร์อยู่ดี กินดี',
		'ยุทธศาสตร์อยู่ร่วมกันอย่างสันติสุข',
	);
	$tables->rows[]=array('ยุทธศาสตร์',view::inlineedit(array('group'=>'project','fld'=>'strategic','class'=>'-fill','value'=>$project->strategic),$strategicList[$project->strategic],$isEditDetail,'select',$strategicList));




	$tables->rows[]=array('ผู้รับผิดชอบโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$project->prowner,$isEditDetail));

	$tables->rows[]=array('คณะกรรมการ <sup><a href="#" title="แยกแต่ละรายชื่อด้วยเครื่องหมาย ,">?</a></sup>',view::inlineedit(array('group'=>'project','fld'=>'prteam','class'=>'-fill'),$project->prteam,$isEditDetail));

	$tables->rows[]=array('พี่เลี้ยงโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prtrainer','class'=>'-fill'),$project->prtrainer,$isEdit));

	$tables->rows[]=array('ระยะเวลาดำเนินโครงการ',view::inlineedit(array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$project->date_from?sg_date($project->date_from,'d/m/Y'):''),
		$project->date_from
		,$isEditDetail,'datepicker').' - '.view::inlineedit(array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$project->date_end?sg_date($project->date_end,'d/m/Y'):''),
		$project->date_end
		,$isEditDetail,'datepicker'));

	$tables->rows[]=array(
		'กำหนดวันส่งรายงาน',
		view::inlineedit(array('group'=>'project','fld'=>'date_toreport','ret'=>'date:ว ดดด ปปปป','value'=>$project->date_toreport?sg_date($project->date_toreport,'d/m/Y'):''),
			$project->date_toreport,
			$isEditDetail,
			'datepicker')
	);

	$tables->rows[]=array('งบประมาณโครงการ',view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money'),$project->budget,$isEditDetail,'money').' บาท');






	$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย (คน)',view::inlineedit(array('group'=>'project','fld'=>'totaltarget','value'=>$project->totaltarget),$project->totaltarget,$isEditDetail));
	$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'target','button'=>'yes','ret'=>'html'),$project->target,$isEditDetail,'textarea'));
	$tables->rows[]=array('พื้นที่ดำเนินงาน',view::inlineedit(array('group'=>'project','fld'=>'area','class'=>'-fill'),$project->area,$isEditDetail));
	$tables->rows[]=array('จังหวัด',$topic->project->provname);

	if ($isEdit)
		$tables->rows[] = array(
			'ละติจูด-ลองจิจูด',
			view::inlineedit(array('group'=>'project','fld'=>'location','class'=>'-fill'),($project->location?$project->lat.','.$project->lnt:''),$isEdit),
			'config' => array('class'=>'latlng -hidden'),
		);




	// Show member of this project
	$member = mydb::select('SELECT u.`uid`, u.`username`, u.`name`, tu.`membership` FROM %topic_user% tu LEFT JOIN %users% u USING(`uid`) WHERE `tpid` = :tpid ORDER BY FIELD(`membership`,"Owner","Trainer","Manager") ASC', ':tpid', $topic->tpid);
	$isViewMemberProfile = user_access('access user profiles');
	$name = '<ul class="project__member">'._NL;
	foreach ($member->items as $mrs) {
		$name .= '<li>'
			. '<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />'
			. ($isViewMemberProfile ? '<span><a class="sg-action" href="'.url('profile/'.$mrs->uid).'" data-rel="box">' : '')
			. $mrs->name
			. ' ('.$mrs->membership.')'
			. ($isViewMemberProfile ? '</a></span>':'')
			. '&nbsp;&nbsp;'
			. ($isAdmin || $projectInfo->info->isTrainer ? '<a class="sg-action" href="'.url('project/edit/removeowner/'.$tpid.'/'.$mrs->uid).'" data-rel="notify" data-confirm="ต้องการลบสมาชิกออกจากโครงการ กรุณายืนยัน?" data-removeparent="li"><i class="icon -cancel -gray"></i></a> ' : '')
			.'</li>'
			. _NL;
	}
	$name .= '</ul>'._NL;

	//$ui=new Ui();
	//if ((user_access('administer projects') || in_array('trainer',i()->roles)) && !project_model::is_trainer_of($tpid)) $ui->add('<a href="'.url('project/edit/addtrainer/'.$topic->tpid).'">เพิ่มเป็นพี่เลี้ยง</a>');
	if ($isAdmin || $projectInfo->info->isTrainer) {
		$name .= '<nav class="-sg-text-right">'
			. '<a class="sg-action btn" href="'.url('project/edit/addowner/'.$tpid).'" data-rel="#addowner">'
			. '<i class="icon -person-add -gray"></i>'
			. '<span>เพิ่มเจ้าของโครงการ</span></a>'
			. '</nav>'
			. '<div id="addowner"></div>';
	}
	//$name.=$ui->build();
	$tables->rows[] = array('ผู้ดำเนินการติดตามสนับสนุนโครงการ',$name);

	//		$tables->rows[]=array('<td colspan="2" class="project-object-info">'.$text.'</td>');

	if ($project->objective) $tables->rows[]=array('วัตถุประสงค์ของกิจกรรม/โครงการ',view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$project->objective,false,'textarea'));
	if ($project->activity) $tables->rows[]=array('กิจกรรมหลัก',view::inlineedit(array('group'=>'project','fld'=>'activity','button'=>'yes','ret'=>'html'),$project->activity,false,'textarea'));

	if (cfg('project.show.detail')) $tables->rows[]=array('รายละเอียดโครงการ',$topic->body);





	// งวดสำหรับทำรายงาน
	$ptables = new Table();
	$ptables->addClass('project-period-items');
	$ptables->caption='งวดสำหรับการทำรายงาน';
	$ptables->colgroup=array(
		'no'=>'align="center" width="5%"',
		'date fromdate'=>'align="center" width="20%"',
		'date todate'=>'align="center" width="20%"',
		'date rfromdate'=>'align="center" width="20%"',
		'date rtodate'=>'align="center" width="20%"',
		'money budget'=>'align="center" width="20%"',
		'align="center" width="5%"',
	);
	$ptables->thead='<thead><tr><th rowspan="2">งวด</th><th colspan="2">วันที่งวดโครงการ</th><th colspan="2">วันที่งวดรายงาน</th><th rowspan="2">งบประมาณ<br />(บาท)</th><th rowspan="2"></th></tr><tr><th>จากวันที่</th><th>ถึงวันที่</th><th>จากวันที่</th><th>ถึงวันที่</th></tr></thead>';

	$projectPeriod=project_model::get_period($tpid);
	$budgetPeriodSum=0;
	foreach ($projectPeriod as $period) {
		unset($row);
		$isEditPeriod=$isEdit && (empty($period->report_from_date) || $period->report_to_date>$lockReportDate);

		$row[]=$period->period;
		$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'date1','tr'=>$period->trid,'ret'=>'date:j ดด ปปปป','value'=>$period->from_date?sg_date($period->from_date,'d/m/Y'):''),$period->from_date,$isEditPeriod,'datepicker');
		$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'date2','tr'=>$period->trid,'ret'=>'date:j ดด ปปปป','value'=>$period->to_date?sg_date($period->to_date,'d/m/Y'):''),$period->to_date,$isEditPeriod,'datepicker');

		$row[]=$period->report_from_date?sg_date($period->report_from_date,'j ดด ปปปป'):'';
		$row[]=$period->report_to_date?sg_date($period->report_to_date,'j ดด ปปปป'):'';

		$row[]=view::inlineedit(array('group'=>'info:period','fld'=>'num1','tr'=>$period->trid, 'ret'=>'money','callback'=>'refreshContent'),$period->budget,$isEditPeriod,'money');
		if ($isEdit && $period->period==count($projectPeriod)) {
			$row[]='<a class="sg-action" href="'.url('project/edit/period/remove/'.$period->trid).'" data-rel="#main" data-confirm="ต้องการลบงวดรายงานนี้ กรุณายืนยัน?" title="ลบงวดสำหรับการทำรายงาน"><i class="icon -cancel"></i></a>';
		} else {
			$row[]='';
		}
		$ptables->rows[]=$row;
		$budgetPeriodSum+=$period->budget;
	}
	$ptables->tfoot[]=array('<td colspan="5" align="right"><strong>รวมงบประมาณ</strong></td>','<td align="right"><strong>'.number_format($budgetPeriodSum,2).'</strong></td>','');

	$periodStr = $ptables->build();

	if ($isEdit && count($projectPeriod)<cfg('project.period.max')) {
		$periodStr.='<a class="sg-action btn -primary" href="'.url('project/edit/period/add/'.$tpid).'" data-rel="#main"><i class="icon -addbig -white"></i><span>เพิ่มงวด</span></a>';
	}
	if ($project->budget!=$budgetPeriodSum) {
		$periodStr.='<p class="notify">คำเตือน : รวมงบประมาณของทุกงวด ('.number_format($budgetPeriodSum,2).' บาท) ไม่เท่ากับ งบประมาณโครงการ ('.number_format($project->budget,2).' บาท)</p>';
	}





	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-info" '.sg_implode_attr($inlineAttr).' style="position: relative;">'._NL
		.'<div class="project-info-general">'._NL
		.$tables->build()._NL
		.$periodStr
		.'</div>'._NL;

	if ($showMap) {
		$ret.='<div id="project-map" width="400" height="400" style="position:absolute; top:0;right:0;">'._NL
			.'<div class="project-status project-status-'.$project->project_statuscode.'">สถานภาพโครงการ <span>'.$project->project_status.'</span></div>'._NL
			.($project->risk>0 && ($isAdmin || $isTrainer) ? '<div class="project-risk"><span class="text">ระดับความเสี่ยง '.$project->risk.'</span><span class="project-risk-bar -level-'.$project->risk.'" title="ระดับความเสี่ยง '.$project->risk.'"></span></div>'._NL:'')
			.'<div id="map_canvas"></div>'._NL
			.'</div><br clear="all" />'._NL;
	}


	$ret .= '<div class="box">';
	$ret .= '<h3>รายละเอียดโครงการ</h3>';
	$ret .= '<p><b>หลักการและเหตุผล</b></p>'
		. view::inlineedit(
			array('group' => 'info:basic', 'fld' => 'text1', 'tr' => $basicInfo->trid, 'ret' => 'html'),
			$basicInfo->text1,
			$isEdit,
			'textarea'
		);

	$ret .= '<p><b>วัตถุประสงค์</b></p>'
		. view::inlineedit(
			array('group' => 'project', 'fld' => 'objective', 'ret' => 'html'),
			$project->objective,
			$isEdit,
			'textarea'
		);

	if ($project->activity) $tables->rows[]=array('กิจกรรมหลัก',view::inlineedit(array('group'=>'project','fld'=>'activity','button'=>'yes','ret'=>'html'),$project->activity,false,'textarea'));


	/*
	$ret .= '<p><b>กรอบแนวคิดและยุทธศาสตร์หลัก</b></p>'
		. view::inlineedit(
			array('group' => 'info:basic', 'fld' => 'text6', 'tr' => $basicInfo->trid, 'ret' => 'html'),
			$basicInfo->text6,
			$isEdit,
			'textarea'
		);
	*/


	// Section :: วัตถุประสงค์ของโครงการ
	/*
	$ret.='<h3>วัตถุประสงค์ของโครงการ / ตัวชี้วัด / การดำเนินกิจกรรม</h3>';
	$mainActGroupBy=$_COOKIE['maingrby'];
	$ret.='<div class="sg-tabs">'._NL;
	$ret.='<ul class="tabs -no-print">'._NL;
	$ret.='<li class="'.(empty($mainActGroupBy) || $mainActGroupBy=='act'?'active':'').'"><a href="'.url('project/mainact/'.$tpid,array('gr'=>'act')).'">กิจกรรมหลักลำดับดำเนินการ</a><li>';
	$ret.='<li class="'.($mainActGroupBy=='obj'?'active':'').'"><a href="'.url('project/mainact/'.$tpid,array('gr'=>'obj')).'">กิจกรรมหลักตามวัตถุประสงค์</a></li>';
	$ret.='</ul>'._NL;
	$ret.='<div id="project-objective-wrapper">';
	$ret.=R::Page('project.mainact',$self,$tpid,NULL,'{isEdit:'.$isEdit.', isEditDetail:'.$isEditDetail.'}');
	$ret.='</div></div>';
	*/

	/*
	$ret.='<div id="project-objective" class="project-objective box">';
	$ret.='<h3>วัตถุประสงค์/เป้าหมาย</h3>';
	$ret.=R::Page('project.objective',NULL,$tpid);
	$ret.='</div><!-- project-objective -->';
	*/

		//$ret.=$_COOKIE['maingrby'];
	$activityGroupBy=SG\getFirst(post('gr'),'act');
	//setcookie('maingrby',$activityGroupBy,time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	if ($activityGroupBy!='act') $activityGroupBy='act';
	$_COOKIE['maingrby']=$activityGroupBy;
	$ret.='<h3>โครงการย่อย/กิจกรรม</h3>';
	$ret.='<div id="project-calendar-wrapper">';
	$ret.=R::Page('project.calendar', NULL, $projectInfo);
	$ret.='</div><!-- project-calendar-wrapper -->'._NL;
	//$ret.='</div><!-- sg-tabs -->'._NL;
	$ret.='</div><!-- project-plan -->'._NL;

	$ret.='</div><!-- box -->';



	// Section :: Project Creator
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$topic->uid)).'" title="'.htmlspecialchars($mrs->name).'"><img src="'.model::user_photo($topic->username).'" width="32" height="32" alt="'.htmlspecialchars($topic->owner).'" /> '.$topic->owner.'</a> เมื่อวันที่ '.sg_date($topic->created,'ว ดดด ปปปป H:i').' น.</p>';
	$ret.='</div>'._NL;





	// Section :: Social share
	if (_ON_HOST && in_array($topic->type,explode(',',cfg('social.share.type'))) && !is_home() && $topic->property->option->social) {
		$ret.=view::social(url('paper/'.$topic->tpid));
	}



	$ret.='</div><!--detail-->'._NL;





	$ret.='</div><!--sg-tabs-->'._NL;


	unset($body->comment);








	// Section :: Project GIS Location
	//head('jquery.ui.map.js','<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	//head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$gis['center']='8.604486109074745,100.1000';
	$gis['zoom']=7;

	$stmt='SELECT pv.*, cot.`subdistname`, coa.`distname`, cop.`provname`
		FROM %project_prov% pv
			LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
			LEFT JOIN %co_district% coa ON coa.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
			LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
		WHERE `tpid`=:tpid'.($autoid?' AND `autoid`=:autoid':'');
	$provList=mydb::select($stmt,':tpid',$tpid,':autoid',$autoid);
	if ($project->area) $gis['address'][]=$project->area;
	foreach ($provList->items as $item) {
		$gis['address'][]='ต.'.$item->subdistname.' อ.'.$item->distname.' จ.'.$item->provname;
	}

	if ($project->lat) {
		$gis['center']=$project->lat.','.$project->lnt;
		$gis['zoom']=8;
		$gis['current']=array(
			'latitude'=>$project->lat,
			'longitude'=>$project->lnt,
			'title'=>$project->title,
			'content'=>'<h4>'.$project->title.'</h4><p>พื้นที่ : '.$project->area.'</p>'
		);
	}





	$ret .= '<style type="text/css">
	.project-info-general {width: auto; float: none;}
	</style>';

	// Section :: Script
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
							$map.gmap("openInfoWindow", { "content": marker.content }, this);
						});
					}
				});

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
				}
		});
		--></script>';
	}

	return $ret;
}
?>
