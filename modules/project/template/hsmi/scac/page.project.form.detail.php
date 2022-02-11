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
	$project=$topic->project;
	$self->theme->class.=' project-status-'.$project->project_statuscode;

	$projectInfo=R::Model('project.get',$tpid);

	$info=project_model::get_info($tpid,'info');
	$basicInfo = reset(SG\getFirst(project_model::get_tr($tpid, 'info:basic')->items['basic'], []));

	foreach ($topic->tags as $t) {
		if ($t->vid==4) $strategy.='<li>'.$t->name.'</li>';
		if ($t->vid==5) $roadmap.='<li>'.$t->name.'</li>';
	}
	$strategy='<ol>'.$strategy.'</ol>';
	$roadmap='<ol>'.$roadmap.'</ol>';

	//$ret.=print_o($project,'$project');
	$isAdmin=$project->isAdmin;
	$isTrainer=project_model::is_trainer_of($topic->tpid);
	$isEdit=$project->isEdit;
	$isEditDetail=$project->isEditDetail;
	$lockReportDate=$project->lockReportDate;

	$liketitle=$isEdit?'คลิกเพื่อแก้ไข':'';
	$editclass=$isEdit?'editable':'';
	$emptytext=$isEdit?'<span style="color:#999;">แก้ไข</span>':'';

	$activityCount=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" LIMIT 1',':tpid',$tpid)->total;
	$commentCount=mydb::select('SELECT COUNT(*) total FROM %topic_comments% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->total;
	$ret.='<div class="sg-tabs project--info">'._NL;
	$ret.='<ul class="tabs -no-print">';
	$ret.='<li class="-active"><a href="#project-info">รายละเอียดโครงการ</a></li>';
	if ($topic->project->proposalId) $ret.='<li><a href="'.url('project/develop/'.$tpid).'" target="_top">พัฒนาโครงการ</a></li>';
	$ret.='<li><a href="#activity">บันทึกกิจกรรม'.($activityCount?' ('.$activityCount.')':'').'</a></li>';
	if ($topic->project->proposalId) $ret.='<li><a href="'.url('project/'.$tpid.'/eval.valuation').'" target="_top">ประเมินคุณค่า</a></li>';
	if ($isAdmin) {
		$adminCommentCount=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="admin" AND `part`="comment" LIMIT 1',':tpid',$tpid)->total;
		$ret.='<li><a href="#adminreport">บันทึกเจ้าหน้าที่'.($adminCommentCount?' ('.$adminCommentCount.')':'').'</a></li>';
	}
	$ret.='<li><a href="#comment">ความคิดเห็น'.($commentCount?' ('.$commentCount.')':'').'</a></li>';
	$ret.='</ul>'._NL;






	// รายละเอียดโครงการ
	$ret.='<div id="detail">'._NL;
	$tables = new Table();
	$tables->addClass('item__card project-info');
	$tables->colgroup=array('width="30%"','width="70%"');
	$tables->caption='รายละเอียดโครงการ';
	if ($isAdmin) {
		$tables->rows[]=array('ประเภท',view::inlineedit(array('group'=>'project','fld'=>'prtype','class'=>'-fill'),$project->prtype,$isEdit,'select',array('แผนงาน'=>'แผนงาน','ชุดโครงการ'=>'ชุดโครงการ','โครงการ'=>'โครงการย่อย')));
	}
	$tables->rows[]=array('เลขที่ข้อตกลง',view::inlineedit(array('group'=>'project','fld'=>'agrno','class'=>'-fill'),$project->agrno,$isEdit));
	//$tables->rows[]=array('รหัสโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prid','class'=>'-fill'),$project->prid,$isEditDetail));
	$tables->rows[]=array('ชื่อโครงการ','<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$topic->title,$isEditDetail).'</strong>');





	// Show project set
	//$tree = model::get_taxonomy_tree(cfg('project.set.vid'));
	//foreach ($tree as $term) $projectSets[$term->tid]=$term->name;

	$dbs=mydb::select('SELECT * FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `prtype` IN ("แผนงาน","ชุดโครงการ") AND `project_status`="กำลังดำเนินโครงการ"');
	$projectSets=array();
	foreach ($dbs->items as $item) $projectSets[$item->tpid]=$item->title;

	$tables->rows[]=array('ชุดโครงการ',view::inlineedit(array('group'=>'project','fld'=>'projectset','class'=>'-fill','value'=>$project->projectset),$project->projectset_name,$isAdmin,'select',$projectSets));

	$tables->rows[]=array('ผู้รับผิดชอบโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$project->prowner,$isEditDetail));

	$tables->rows[]=array('คณะทำงาน <sup><a href="#" title="แยกแต่ละรายชื่อด้วยเครื่องหมาย ,">?</a></sup>',view::inlineedit(array('group'=>'project','fld'=>'prteam','class'=>'-fill'),$project->prteam,$isEditDetail));

	//$tables->rows[]=array('พี่เลี้ยงโครงการ',view::inlineedit(array('group'=>'project','fld'=>'prtrainer','class'=>'-fill'),$project->prtrainer,$isEdit));

	$tables->rows[]=array('ระยะเวลาดำเนินโครงการ',view::inlineedit(array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$project->date_from?sg_date($project->date_from,'d/m/Y'):''),
		$project->date_from
		,$isEditDetail,'datepicker').' - '.view::inlineedit(array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$project->date_end?sg_date($project->date_end,'d/m/Y'):''),
		$project->date_end
		,$isEditDetail,'datepicker'));
	$tables->rows[]=array('งบประมาณโครงการ',view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money'),$project->budget,$isEditDetail,'money').' บาท');







	$tables->rows[]=array('จำนวนกลุ่มเป้าหมาย (คน)',view::inlineedit(array('group'=>'project','fld'=>'totaltarget','value'=>$project->totaltarget),$project->totaltarget,$isEditDetail));
	$tables->rows[]=array('รายละเอียดกลุ่มเป้าหมาย',view::inlineedit(array('group'=>'project','fld'=>'target','button'=>'yes','ret'=>'html'),$project->target,$isEditDetail,'textarea'));
	$tables->rows[]=array('พื้นที่ดำเนินการ',view::inlineedit(array('group'=>'project','fld'=>'area','class'=>'-fill'),$project->area,$isEditDetail));
	$tables->rows[]=array('จังหวัด',$topic->project->provname);

	$tables->rows[]=array(
		'ละติจูด-ลองจิจูด',
		view::inlineedit(
			array('group'=>'project','fld'=>'location','class'=>''),
			($project->location?$project->lat.','.$project->lnt:''),
			$isEdit
		)
		. '<a href="'.url('project/'.$tpid.'/info.map').'"><i class="icon -pin"></i></a>'
	);




	//		$tables->rows[]=array('<td colspan="2" class="project-object-info">'.$text.'</td>');

	if ($project->objective) $tables->rows[]=array('วัตถุประสงค์ของกิจกรรม/โครงการ',view::inlineedit(array('group'=>'project','fld'=>'objective','button'=>'yes','ret'=>'html'),$project->objective,false,'textarea'));
	if ($project->activity) $tables->rows[]=array('กิจกรรมหลัก',view::inlineedit(array('group'=>'project','fld'=>'activity','button'=>'yes','ret'=>'html'),$project->activity,false,'textarea'));

	if (cfg('project.show.detail')) $tables->rows[]=array('รายละเอียดโครงการ',$topic->body);







	if ($isEdit) {
		$inlineAttr['class']='sg-inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL
					.'<div class="project-info-general">'._NL
					. $tables->build()._NL
					. R::PageWidget('project.info.period', [$planningInfo])->build()
					.'</div>'._NL;




	$ret.='<p><b>หลักการและเหตุผล</b></p>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text1,$isEdit,'textarea');
	$ret.='<p><b>กรอบแนวคิดและยุทธศาสตร์หลัก</b></p>'.view::inlineedit(array('group'=>'info:basic','fld'=>'text6', 'tr'=>$basicInfo->trid, 'ret'=>'html'),$basicInfo->text6,$isEdit,'textarea');


	// Section :: วัตถุประสงค์ของโครงการ
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





	// Section :: Project Creator
	$ret.='<p>โครงการเข้าสู่ระบบโดย <a href="'.url('project/list',array('u'=>$topic->uid)).'" title="'.htmlspecialchars($mrs->name).'"><img src="'.model::user_photo($topic->username).'" width="32" height="32" alt="'.htmlspecialchars($topic->owner).'" /> '.$topic->owner.'</a> เมื่อวันที่ '.sg_date($topic->created,'ว ดดด ปปปป H:i').' น.</p>';
	$ret.='</div>'._NL;





	// Section :: Social share
	if (_ON_HOST && in_array($topic->type,explode(',',cfg('social.share.type'))) && !is_home() && $topic->property->option->social) {
		$ret.=view::social(url('paper/'.$topic->tpid));
	}


	$ret.='</div><!--detail-->'._NL;





	// Section :: Owner report : รายงานผู้รับผิดชอบ
	$ret.='<div id="activity" class="-hidden">'._NL;
	$ret.=R::Page('project.form.show_activity',$self,$topic,$para,$body,false)._NL;
	$ret.='</div><!--activity-->'._NL;






	// Section :: User comments : ความคิดเห็น
	$ret.='<div id="comment" class="-hidden">'._NL;
	$ret.=$body->comment._NL;
	$ret.='</div><!--comment-->'._NL;





	// Section :: Officer comment : บันทึกเจ้าหน้าที่
	if ($isAdmin) {
		$ret.='<div id="adminreport" class="-hidden">'._NL;
		$ret.=R::Page('project.form.adminreport',$self,$topic,$para,$body,false)._NL;
		$ret.='</div><!--adminreport-->'._NL;
	}



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


	return $ret;
}
?>