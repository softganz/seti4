<?php
/**
 * View assessor information
 *
 * @param Object $psnInfo
 * @param String $action
 * @param Integer $trid
 * @return String
 */
function view_qt_group_course_profile($psnInfo,$action=NULL,$trid=NULL) {
	$rs=$psnInfo->info;
	$psnid=$psnInfo->psnid;

	$self->theme->title=$rs->fullname;

	if (!$psnInfo) return $ret.message('error','ไม่มีข้อมูล');

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
					ORDER BY `date1` ASC
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


	$isEdit=$action!='print' && user_access('administrator orgs','edit own org content',$rs->uid);
	$showAll=user_access('administrator orgs','edit own org content',$rs->uid);

	//$ret .= '<h3>รายละเอียดสมาชิก - <a href="'.url('org/member/info/'.$psnid).'">'.$rs->fullname.'</a></h3>'._NL;

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
														.view::inlineedit(array('group'=>'person','fld'=>'prename','tr'=>$psnid,'class'=>'w-1'),$rs->prename,$isEdit)
														.' '
														.view::inlineedit(array('group'=>'person','fld'=>'name','tr'=>$psnid,'class'=>'w-5'),$rs->name.' '.$rs->lname,$isEdit)
														.'</strong>'
														);

	$tables->rows[]=array(
										'เพศ',
										view::inlineedit(array('group'=>'person','fld'=>'sex','tr'=>$psnid,'class'=>'-fill'),$rs->sex,$isEdit,'select','ชาย,หญิง')
									);
	$tables->rows[]=array(
										'อายุ',
										view::inlineedit(array('group'=>'person','fld'=>'age','tr'=>$psnid),$rs->age,$isEdit).' ปี'
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
												SG\implode_address($rs),$isEdit,'autocomplete'
											)
										);

		$tables->rows[]=array(
											'โทรศัพท์',
											'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'phone','tr'=>$psnid,'class'=>'-fill'),$rs->phone,$isEdit).'</strong>'
										);
		$tables->rows[]=array(
											'อีเมล์',
											'<strong class="big">'.view::inlineedit(array('group'=>'person','fld'=>'email','tr'=>$psnid,'class'=>'-fill'),$rs->email,$isEdit).'</strong>'
										);
	} else {
		unset($rs->house);
		$tables->rows[]=array(
											'ที่อยู่',
											view::inlineedit(array('group'=>'person','fld'=>'address','tr'=>$psnid,'class'=>'w-10'),SG\implode_address($rs),$isEdit,'autocomplete')
										);

	}
	$tables->rows[]=array(
										'เฟซบุ๊ค',
										view::inlineedit(array('group'=>'person','fld'=>'website','tr'=>$psnid,'class'=>'-fill'),$rs->website,$isEdit)
									);

	$tables->rows[]=array(
										'การศึกษา',
										view::inlineedit(array('group'=>'person','fld'=>'educate','tr'=>$psnid,'class'=>'-fill'),$rs->edu_desc,$isEdit,'select',array('x'=>'ตำกว่าปริญาตรี','6'=>'ปริญาตรี','7'=>'ปริญญาโท','8'=>'ปริญาเอก'))
									);
	$tables->rows[]=array(
										'เครือข่าย/หน่วยงาน',
										view::inlineedit(array('group'=>'person','fld'=>'network','tr'=>$psnid,'class'=>'-fill'),$rs->network,$isEdit)
									);
	$tables->rows[]=array(
										'ประสบการณ์ทำงานในเครือข่าย',
										view::inlineedit(array('group'=>'person','fld'=>'yearexp','tr'=>$psnid),$rs->yearexp,$isEdit).' ปี'
									);

	$ret.=$tables->build();





	// ประวัติการศึกษา
	if ($isEdit) {
		$ret.='<form class="x-sg-form" method="post" action="'.url('project/assessor/'.$psnid.'/addtr').'" data-rel="#main">';
	}

	$addBtn='<a class="tran-remove -hidden" href="" data-rel="none" data-removeparent="tr"><i class="icon -cancel -gray"></i></a><a class="add-tran" href="javascript:void(0)" title="เพิ่ม"><i class="icon -addbig -gray -circle"></i></a>';
	$ret.='<h4>ประวัติการศึกษา</h4>';
	$tables = new Table();
	$tables->addClass('-line-input');
	$tables->thead=array('date'=>'พ.ศ.ที่จบ','ระดับการศึกษา','คณะ','สาขา','สถาบันการศึกษา','icons -c1 -center'=>'');
	foreach ($eduInfo as $rs) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="edu['.$rs->psntrid.'][trid]" value="'.$rs->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="edu['.$rs->psntrid.'][year]" value="'.($rs->year+543).'" />',
												'<select class="form-select -fill -line" name="edu['.$rs->psntrid.'][grade]"><option value=""></option><option value="ปริญาตรี" '.($rs->grade=='ปริญาตรี'?'selected="selected"':'').'>ปริญาตรี</option><option value="ปริญญาโท" '.($rs->grade=='ปริญญาโท'?'selected="selected"':'').'>ปริญญาโท</option><option value="ปริญาเอก" '.($rs->grade=='ปริญาเอก'?'selected="selected"':'').'>ปริญาเอก</option></select>',
												'<input class="form-text -fill -line" type="text" name="edu['.$rs->psntrid.'][faculty]" value="'.$rs->faculty.'" />',
												'<input class="form-text -fill -line" type="text" name="edu['.$rs->psntrid.'][branch]" value="'.$rs->branch.'" />',
												'<input class="form-text -fill -line" type="text" name="edu['.$rs->psntrid.'][college]" value="'.$rs->college.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$psnid.'/deltr/'.$rs->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>',
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($rs->year+543),
												$rs->grade,
												$rs->faculty,
												$rs->branch,
												$rs->college,
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
	foreach ($jobInfo as $rs) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="job['.$rs->psntrid.'][trid]" value="'.$rs->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="job['.$rs->psntrid.'][year]" value="'.($rs->year+543).'" />',
												'<input class="form-text -fill -line" type="text" name="job['.$rs->psntrid.'][position]" value="'.$rs->position.'" />',
												'<input class="form-text -fill -line" type="text" name="job['.$rs->psntrid.'][company]" value="'.$rs->company.'" />',
												'<select class="form-select -fill -line" name="job['.$rs->psntrid.'][orgtype]"><option value=""></option><option value="รัฐ" '.($rs->orgtype=='รัฐ'?'selected="selected"':'').'>รัฐ</option><option value="เอกชน" '.($rs->orgtype=='เอกชน'?'selected="selected"':'').'>เอกชน</option><option value="ประชาสังคม" '.($rs->orgtype=='ประชาสังคม'?'selected="selected"':'').'>ประชาสังคม</option></select>',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$psnid.'/deltr/'.$rs->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($rs->year+543),
												$rs->position,
												$rs->company,
												$rs->orgtype,
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
	foreach ($skillInfo as $rs) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="skill['.$rs->psntrid.'][trid]" value="'.$rs->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="skill['.$rs->psntrid.'][skill]" value="'.$rs->skill.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$psnid.'/deltr/'.$rs->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												$rs->skill,
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
	foreach ($projectInfo as $rs) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="project['.$rs->psntrid.'][trid]" value="'.$rs->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="project['.$rs->psntrid.'][year]" value="'.($rs->year+543).'" />',
												'<input class="form-text -fill -line" type="text" name="project['.$rs->psntrid.'][project]" value="'.$rs->project.'" />',
												'<input class="form-text -fill -line" type="text" name="project['.$rs->psntrid.'][granter]" value="'.$rs->granter.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$psnid.'/deltr/'.$rs->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												'พ.ศ. '.($rs->year+543),
												$rs->project,
												$rs->granter,
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
	foreach ($refInfo as $rs) {
		if ($isEdit) {
			$tables->rows[]=array(
												'<input type="hidden" name="ref['.$rs->psntrid.'][trid]" value="'.$rs->psntrid.'">'
												.'<input class="form-text -fill -line" type="text" name="ref['.$rs->psntrid.'][name]" value="'.$rs->name.'" />',
												'<input class="form-text -fill -line" type="text" name="ref['.$rs->psntrid.'][company]" value="'.$rs->company.'" />',
												'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/assessor/'.$psnid.'/deltr/'.$rs->psntrid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>'
												);
		} else {
			$tables->rows[]=array(
												$rs->name,
												$rs->company,
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
		$ret.='<div class="form-item -submit">';
		$ret.='<a class="btn -link" href="'.url('project/assessor').'"><span>{tr:Cancel}</span></a> ';
		$ret.='<button class="btn -primary" name="save" value="Save"><i class="icon -save -white"></i><span>{tr:Save}</span></button>';
		$ret.='</div>';
		$ret.='</form>';
	}

	$ret.='<h4>ภาพถ่าย ผลงาน รางวัล</h4>';

	$stmt='SELECT * FROM %topic_files% WHERE `refid`=:psnid AND `tagname`="assessor" ORDER BY `fid` DESC';
	$dbs=mydb::select($stmt,':psnid',$psnid);
	//$ret.=print_o($dbs,'$dbs');

	$ret.='<ul id="project-photo-assessor" class="card -photo -assessor">';
	if ($isEdit) {
		$ret.='<li class="card-item -upload"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/assessor/'.$psnid.'/uploadphoto').'" data-rel="#project-photo-tor" data-after="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>อัพโหลดภาพถ่าย</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></li>';
	}

	// Show photos
	foreach ($dbs->items as $rs) {
		list($photoid,$photo)=explode('|',$rs);
		if ($rs->type=='photo') {
			$photo=model::get_photo_property($rs->file);
			$photo_alt=$rs->title;
			$ret .= '<li class="card-item">';
			$ret.='<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
			$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
			$ret.=' />';
			$ret.='</a>';
			$photomenu=array();
			$ui=new ui();
			if ($isEdit) {
				$ui->add('<a class="sg-action" href="'.url('project/assessor/'.$psnid.'/delphoto/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -delete"></i></a>');
			}
			$ret.=$ui->build('span','iconset -hover');
			/*
			if ($isEdit) {
				$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$rs->fid),$rs->title,$isEdit,'text');
			} else {
				$ret.='<span>'.$rs->title.'</span>';
			}
			*/
			$ret .= '</li>'._NL;
		} else {
			$uploadUrl=cfg('paper.upload.document.url').$rs->file;
			$ret.='<li><a href="'.$uploadUrl.'"><img src="http://img.softganz.com/icon/pdf-icon.png" /></a></li>';
		}
	}
	$ret.='</ul><!-- loapp-photo -->';

	if ($isEdit) {
		$ret.='<div class="qrcode">';
		$ret.='<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('project/assessor/'.$psnid).'&chs=180x180&choe=UTF-8&chld=L|2" alt="">';
		$ret.='<p>อัพโหลดภาพถ่ายโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกดปุ่ม "อัพโหลดภาพถ่าย" เลือกกล้องถ่ายรูป</p>';
		$ret.='</div>';
	}

	$ret.='</div>';

	if ($isEdit) $ret.='<style type="text/css">
	.inline-edit .item.-line-input>tbody>tr>td {padding:4px 8px; border-bottom:none;}
	.col-icons {vertical-align:middle;}
	td.col-icons.-c3 {width:86px;}
	.col-icons.-c3 a {padding:0px;}
	.page.-main h4 {padding:16px;margin:64px 0 4px 0; background:#ddd;}
	.form-item.-submit {text-align: right;}
	.form-item.-submit .btn {margin:8px 16px;}
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



	if ($rs->location) {
		$ret.='<iframe id="project-assessor-info-map" width="400" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://softganz.com/gis/point/'.$rs->location.'/type/map?title='.$rs->name.' '.$rs->lname.'"></iframe>';
	}

	return $ret;
}
?>