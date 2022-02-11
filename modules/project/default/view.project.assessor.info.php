<?php
/**
 * View assessor information
 *
 * @param Object $assessorInfo
 * @param String $action
 * @param Integer $trid
 * @return String
 */
function view_project_assessor_info($assessorInfo, $action = NULL, $trid = NULL) {
	$psnid = $assessorInfo->psnid;
	$userId = $assessorInfo->uid;
	$psnInfo = $assessorInfo->psnInfo->info;

	$self->theme->title=$psnInfo->fullname;

	//if (!$assessorInfo->psnid) return $ret.message('error','ไม่มีข้อมูล');

	//$userId = mydb::select('SELECT `psnid` FROM %person_group% WHERE `groupname` = "assessor" AND `uid` = :uid LIMIT 1',':uid',$userId)->psnid;

	$isEdit = $action!='print' && user_access('administer projects','edit own project content',$assessorInfo->uid);
	$showAll = user_access('administer projects','edit own project content',$assessorInfo->uid);

	//$ret .= 'uid = '.$assessorInfo->uid . ' '. ($isEdit ? 'Is EDIT':'Not EDIT');
	//$ret .= print_o($psnInfo,'$psnInfo');

	$stmt='SELECT
					`psntrid`, `psnid`, `uid`
					, YEAR(`date1`) `year`
					, `detail1` `grade`
					, `detail2` `faculty`
					, `detail3` `branch`
					, `detail4` `college`
					FROM %person_tr%
					WHERE `psnid`=:psnid AND `tagname`="education"
					ORDER BY `date1` ASC;
					-- {key:"psntrid"}';
	$eduInfo=mydb::select($stmt,':psnid',$psnid)->items;


	$stmt='SELECT
					`psntrid`, `psnid`, `uid`
					, YEAR(`date1`) `year`
					, `detail1` `position`
					, `detail2` `company`
					, `detail3` `orgtype`
					FROM %person_tr%
					WHERE `psnid`=:psnid AND `tagname`="job"
					ORDER BY `date1` ASC;
					-- {key:"psntrid"}';
	$jobInfo=mydb::select($stmt,':psnid',$psnid)->items;


	$stmt='SELECT
					`psntrid`, `psnid`, `uid`
					, `detail1` `skill`
					FROM %person_tr%
					WHERE `psnid`=:psnid AND `tagname`="skill"
					ORDER BY `psntrid` ASC;
					-- {key:"psntrid"}';
	$skillInfo=mydb::select($stmt,':psnid',$psnid)->items;


	$stmt='SELECT
					`psntrid`, `psnid`, `uid`
					, YEAR(`date1`) `year`
					, `detail1` `project`
					, `detail2` `granter`
					FROM %person_tr%
					WHERE `psnid`=:psnid AND `tagname`="project"
					ORDER BY `date1` ASC;
					-- {key:"psntrid"}';
	$projectInfo=mydb::select($stmt,':psnid',$psnid)->items;


	$stmt='SELECT
					`psntrid`, `psnid`, `uid`
					, `detail1` `name`
					, `detail2` `company`
					FROM %person_tr%
					WHERE `psnid`=:psnid AND `tagname`="reference"
					ORDER BY `psntrid` ASC;
					-- {key:"psntrid"}';
	$refInfo=mydb::select($stmt,':psnid',$psnid)->items;


	//$ret .= '<h3>รายละเอียดสมาชิก - <a href="'.url('org/member/info/'.$psnid).'">'.$psnInfo->fullname.'</a></h3>'._NL;

	if ($isEdit) {
		$inlineAttr['class']='sg-inlineupdate';
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/assessor/edit/'.$psnid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="org-meeting-info" '.sg_implode_attr($inlineAttr).'>';

	$tables = new Table();
	$tables->colgroup=array('width="30%"','width="70%"');
	$tables->rows[]=array('ชื่อ-สกุล',
														'<strong class="big">'
														.view::inlineedit(array('group'=>'person','fld'=>'prename','tr'=>$psnid,'class'=>'w-1'),$psnInfo->prename,$isEdit)
														.' '
														.view::inlineedit(array('group'=>'person','fld'=>'name','tr'=>$psnid,'class'=>'w-5'),$psnInfo->name.' '.$psnInfo->lname,$isEdit)
														.'</strong>'
														);
	if ($showAll) {
		$tables->rows[]=array(
											'ที่อยู่',
											view::inlineedit(
												array(
													'group'=>'person',
													'fld'=>'address',
													'query'=>url('api/address'),
													'minlength'=>'5',
													'class'=>'-fill',
													'ret'=>'address',
													),
												SG\implode_address($psnInfo),$isEdit,'autocomplete')
											);

		$tables->rows[]=array('โทรศัพท์',
														'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'phone','tr'=>$psnid,'class'=>'-fill'),$psnInfo->phone,$isEdit).'</strong>');
		$tables->rows[]=array('อีเมล์',
														'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'email','tr'=>$psnid,'class'=>'-fill'),$psnInfo->email,$isEdit).'</strong>');
	} else {
		unset($psnInfo->house);
		$tables->rows[]=array('ที่อยู่',
														view::inlineedit(array('group'=>'person','fld'=>'address','tr'=>$psnid,'class'=>'w-10'),SG\implode_address($psnInfo),$isEdit,'autocomplete')
														);

	}
	$tables->rows[]=array('เฟซบุ๊ค',
														view::inlineedit(array('group'=>'person','fld'=>'website','tr'=>$psnid,'class'=>'-fill'),$psnInfo->website,$isEdit));
	$ret.=$tables->build();





	// ประวัติการศึกษา
	if ($isEdit) {
		$ret.='<form class="x-sg-form" method="post" action="'.url('project/assessor/'.$assessorInfo->uid.'/addtr').'" data-rel="#main">';
	}

	$addBtn='<a class="tran-remove -hidden" href="" data-rel="none" data-removeparent="tr"><i class="icon -cancel -gray"></i></a><a class="add-tran" href="javascript:void(0)" title="เพิ่ม"><i class="icon -addbig -gray -circle"></i></a>';
	$ret.='<h4>ประวัติการศึกษา</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('date'=>'พ.ศ.ที่จบ','ระดับการศึกษา','คณะ','สาขา','สถาบันการศึกษา','icons -c1 -center'=>'');
	foreach ($eduInfo as $psnInfo) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="edu['.$psnInfo->psntrid.'][trid]" value="'.$psnInfo->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="edu['.$psnInfo->psntrid.'][year]" value="'.($psnInfo->year+543).'" />',
												'<select class="form-select -fill -line" name="edu['.$psnInfo->psntrid.'][grade]"><option value=""></option><option value="ปริญาตรี" '.($psnInfo->grade=='ปริญาตรี'?'selected="selected"':'').'>ปริญาตรี</option><option value="ปริญญาโท" '.($psnInfo->grade=='ปริญญาโท'?'selected="selected"':'').'>ปริญญาโท</option><option value="ปริญาเอก" '.($psnInfo->grade=='ปริญาเอก'?'selected="selected"':'').'>ปริญาเอก</option></select>',
												'<input class="form-text -fill -line" type="text" name="edu['.$psnInfo->psntrid.'][faculty]" value="'.$psnInfo->faculty.'" />',
												'<input class="form-text -fill -line" type="text" name="edu['.$psnInfo->psntrid.'][branch]" value="'.$psnInfo->branch.'" />',
												'<input class="form-text -fill -line" type="text" name="edu['.$psnInfo->psntrid.'][college]" value="'.$psnInfo->college.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$userId.'/deltr/'.$psnInfo->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>',
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($psnInfo->year+543),
												$psnInfo->grade,
												$psnInfo->faculty,
												$psnInfo->branch,
												$psnInfo->college,
												);
		}
	}
	if ($isEdit) {
		$tables->rows[]=array(
											'<input class="form-text -fill -line" type="text" name="edu[-1][year]" />',
											'<select class="form-select -fill -line" name="edu[-1][grade]"><option value=""></option><option value="ปริญาตรี">ปริญาตรี</option><option value="ปริญญาโท">ปริญญาโท</option><option value="ปริญาเอก">ปริญาเอก</option></select>',
											'<input class="form-text -fill -line" type="text" name="edu[-1][faculty]" />',
											'<input class="form-text -fill -line" type="text" name="edu[-1][branch]" />',
											'<input class="form-text -fill -line" type="text" name="edu[-1][college]" />',
											$addBtn,
											'config'=>array('data-idx'=>-1),
											);
	}
	$ret.=$tables->build();




	// ประวัติการทำงาน
	$ret.='<h4>ประวัติการทำงาน</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('date'=>'ปีที่เริ่ม','ตำแหน่ง','หน่วยงาน/บริษัท','ภาคส่วน','icons -c1 -center'=>'');
	foreach ($jobInfo as $psnInfo) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="job['.$psnInfo->psntrid.'][trid]" value="'.$psnInfo->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="job['.$psnInfo->psntrid.'][year]" value="'.($psnInfo->year+543).'" />',
												'<input class="form-text -fill -line" type="text" name="job['.$psnInfo->psntrid.'][position]" value="'.$psnInfo->position.'" />',
												'<input class="form-text -fill -line" type="text" name="job['.$psnInfo->psntrid.'][company]" value="'.$psnInfo->company.'" />',
												'<select class="form-select -fill -line" name="job['.$psnInfo->psntrid.'][orgtype]"><option value=""></option><option value="รัฐ" '.($psnInfo->orgtype=='รัฐ'?'selected="selected"':'').'>รัฐ</option><option value="เอกชน" '.($psnInfo->orgtype=='เอกชน'?'selected="selected"':'').'>เอกชน</option><option value="ประชาสังคม" '.($psnInfo->orgtype=='ประชาสังคม'?'selected="selected"':'').'>ประชาสังคม</option></select>',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$userId.'/deltr/'.$psnInfo->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($psnInfo->year+543),
												$psnInfo->position,
												$psnInfo->company,
												$psnInfo->orgtype,
												);
		}
	}
	if ($isEdit) {
		$tables->rows[]=array(
											'<input class="form-text -fill -line" type="text" name="job[-1][year]" placeholder="" />',
											'<input class="form-text -fill -line" type="text" name="job[-1][position]" placeholder="" />',
											'<input class="form-text -fill -line" type="text" name="job[-1][company]" placeholder="" />',
											'<select class="form-select -fill -line" name="job[-1][orgtype]"><option value=""></option><option value="รัฐ">รัฐ</option><option value="เอกชน">เอกชน</option><option value="ประชาสังคม">ประชาสังคม</option></select>',
											$addBtn,
											'config'=>array('data-idx'=>-1),
											);
	}
	$ret.=$tables->build();




	// ความชำนาญ
	$ret.='<h4>ความชำนาญ</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('สาขา','icons -c1 -center'=>'');
	foreach ($skillInfo as $psnInfo) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="skill['.$psnInfo->psntrid.'][trid]" value="'.$psnInfo->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="skill['.$psnInfo->psntrid.'][skill]" value="'.$psnInfo->skill.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$userId.'/deltr/'.$psnInfo->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												$psnInfo->skill,
												$menu,
												);
		}
	}
	if ($isEdit) {
		$tables->rows[]=array(
											'<input class="form-text -fill -line" type="text" name="skill[-1][skill]" />',
											$addBtn,
											'config'=>array('data-idx'=>-1),
											);
	}
	$ret.=$tables->build();



	// ประสบการณ์งานติดตามประเมินผล
	$ret.='<h4>ประสบการณ์งานติดตามประเมินผล</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('date'=>'ปี พ.ศ.','ชื่อโครงการ','แหล่งทุน','icons -c1 -center'=>'');
	foreach ($projectInfo as $psnInfo) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="project['.$psnInfo->psntrid.'][trid]" value="'.$psnInfo->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="project['.$psnInfo->psntrid.'][year]" value="'.($psnInfo->year+543).'" />',
												'<input class="form-text -fill -line" type="text" name="project['.$psnInfo->psntrid.'][project]" value="'.$psnInfo->project.'" />',
												'<input class="form-text -fill -line" type="text" name="project['.$psnInfo->psntrid.'][granter]" value="'.$psnInfo->granter.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$userId.'/deltr/'.$psnInfo->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($psnInfo->year+543),
												$psnInfo->project,
												$psnInfo->granter,
												$menu,
												);
		}
	}
	if ($isEdit) {
		$tables->rows[]=array(
											'<input class="form-text -fill -line" type="text" name="project[-1][year]" />',
											'<input class="form-text -fill -line" type="text" name="project[-1][title]" />',
											'<input class="form-text -fill -line" type="text" name="project[-1][granter]" />',
											$addBtn,
											'config'=>array('data-idx'=>-1),
											);
	}
	$ret.=$tables->build();




	// บุคคลหรือหน่วยงานอ้างอิง
	$ret.='<h4>บุคคลหรือหน่วยงานอ้างอิง</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('ชื่อบุคคล','หน่วยงาน','icons -c1 -center'=>'');
	foreach ($refInfo as $psnInfo) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="ref['.$psnInfo->psntrid.'][trid]" value="'.$psnInfo->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="ref['.$psnInfo->psntrid.'][name]" value="'.$psnInfo->name.'" />',
												'<input class="form-text -fill -line" type="text" name="ref['.$psnInfo->psntrid.'][company]" value="'.$psnInfo->company.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$userId.'/deltr/'.$psnInfo->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												$psnInfo->name,
												$psnInfo->company,
												$menu,
												);
		}
	}
	if ($isEdit) {
		$tables->rows[]=array(
											'<input class="form-text -fill -line" type="text" name="ref[-1][name]" />',
											'<input class="form-text -fill -line" type="text" name="ref[-1][company]" />',
											$addBtn,
											'config'=>array('data-idx'=>-1),
											);
	}
	$ret.=$tables->build();

	if ($isEdit) {
		$ret.='<div class="form-item -submit btn-floating -right-bottom">';
		//$ret.='<a class="btn -link" href="'.url('project/assessor').'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>&nbsp;&nbsp;';
		$ret .= '<button class="btn -floating -circle48" name="save" value="Save"><i class="icon -save -white"></i><span>{tr:SAVE}</span></button>'
		.'<a class="btn -cancel -circle48" href="'.url('project/assessor').'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>'
		;
		//<a class="sg-action btn -floating -circle48" href="'.url('project/develop/'.$tpid,array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a>-->';
		//$ret.='<button class="btn -primary" name="save" value="Save"><i class="icon -save -white"></i><span>{tr:SAVE}</span></button>';
		$ret.='</div>';
		$ret.='</form>';
	}

	$ret.='<h4>ภาพถ่าย ผลงาน รางวัล</h4>';

	$stmt='SELECT * FROM %topic_files% WHERE `refid`=:psnid AND `tagname`="assessor" ORDER BY `fid` DESC';
	$dbs=mydb::select($stmt,':psnid',$psnid);
	//$ret.=print_o($dbs,'$dbs');

	$ret.='<ul id="project-photo-assessor" class="card -photo -assessor">';
	if ($isEdit) {
		$ret.='<li class="card-item -upload"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/assessor/'.$userId.'/uploadphoto').'" data-rel="#project-photo-assessor" data-after="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>อัพโหลดภาพถ่าย</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></li>';
	}

	// Show photos
	foreach ($dbs->items as $psnInfo) {
		list($photoid,$photo)=explode('|',$psnInfo);
		if ($psnInfo->type=='photo') {
			$photo=model::get_photo_property($psnInfo->file);
			$photo_alt=$psnInfo->title;
			$ret .= '<li class="card-item -hover-parent">';
			$ret.='<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="box" title="'.htmlspecialchars($photo_alt).'">';
			$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
			$ret.=' />';
			$ret.='</a>';
			$ui=new Ui('span','iconset -hover');
			if ($isEdit) {
				$ui->add('<a class="sg-action" href="'.url('project/assessor/'.$userId.'/delphoto/'.$psnInfo->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -delete"></i></a>');
			}
			$ret.=$ui->build();
			/*
			if ($isEdit) {
				$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$psnInfo->fid),$psnInfo->title,$isEdit,'text');
			} else {
				$ret.='<span>'.$psnInfo->title.'</span>';
			}
			*/
			$ret .= '</li>'._NL;
		} else {
			$uploadUrl=cfg('paper.upload.document.url').$psnInfo->file;
			$ret.='<li><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a></li>';
		}
	}
	$ret.='</ul><!-- loapp-photo -->';

	if ($isEdit) {
		$ret.='<div class="qrcode">';
		$ret.='<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('project/assessor/'.$userId).'&chs=180x180&choe=UTF-8&chld=L|2" alt="">';
		$ret.='<p>อัพโหลดภาพถ่ายโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกดปุ่ม "อัพโหลดภาพถ่าย" เลือกกล้องถ่ายรูป</p>';
		$ret.='</div>';
	}

	$ret.='</div>';

	if ($isEdit) $ret.='<style type="text/css">
	.inline-edit .item.-line-input>tbody>tr>td {padding:4px 8px; border-bottom:none;}
	.col-icons {vertical-align:middle;}
	td.col-date {width:60px;}
	td.col-icons.-c3 {width:86px;}
	.col-icons.-c3 a {padding:0px;}
	.page.-main h4 {padding:16px;margin:64px 0 4px 0; background:#ddd;}
	.form-item.-submit {text-align: right;}
	.form-item.-submit .btn {width: 64px; height: 64px; padding: 0;}
	.form-item.-submit .btn .icon {display: block; margin: 0 auto;}
	.form-item.-submit .btn.-cancel {margin:0 0 16px 0;}
	.qrcode {clear:both;text-align:center;display:none;}
	@media (min-width:50em) {    /* 800/16 = 50 */
		.qrcode {display:block;}
	}
	</style>
	<script type="text/javascript">
	$(document).on("click",\'[role="button"]\',function(){
		$(this).closest("form").trigger("submit");
		return false;
	});
	$(document).on("click",".add-tran",function() {
		var $tr=$(this).closest("tr");
		var row=$tr.html();
		var $tbody=$(this).closest("tbody");
		var currentIdx=$tr.data("idx");
		var nextIdx=currentIdx-1;
		$(this).closest("a").hide();
		$(this).closest("td").find(".tran-remove").removeClass("-hidden");
		row=row.split("["+currentIdx+"]").join("["+nextIdx+"]")
		$tbody.append("<tr data-idx="+nextIdx+">"+row+"</tr>");
		return false;
	});
	$(document).on("click",".tran-remove",function(){
		$(this).closest("tr").remove();
		return false;
	})
	$(".-set-focus").focus();
	</script>';



	if ($psnInfo->location) {
		$ret.='<iframe id="project-assessor-info-map" width="400" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="//softganz.com/gis/point/'.$psnInfo->location.'/type/map?title='.$psnInfo->name.' '.$psnInfo->lname.'"></iframe>';
	}

	return $ret;
}
?>