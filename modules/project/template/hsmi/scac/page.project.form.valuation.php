<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_valuation($self,$topic,$para,$body) {
	$tpid=$topic->tpid;
	$formid='valuation';
	$info=project_model::get_tr($tpid,'info');
	$mainact=project_model::get_main_activity($tpid, 'owner');
	$valuationTr=project_model::get_tr($tpid,$formid);
	$bigDataTr=mydb::select('SELECT * FROM %bigdata% WHERE `keyid`=:tpid; -- {key:"fldname"}',':tpid',$tpid)->items;
	$url=q();

	if (post('action')=='getsummary') {
		$ret.='<h3>ผลการดำเนินงานที่สำคัญ</h3>';
		$no=0;
		foreach ($valuationTr->items as $k=>$rs) {
			foreach ($rs as $item) {
				if ($item->rate1 && $item->text1) $text.=++$no.'. '.$item->text1._NL._NL;
			}
		}
		$ret.='<textarea class="form-textarea -fill" rows="30">'.$text.'</textarea>';
		//$ret.=print_o($valuationTr,'$valuationTr');
		return $ret;
	}
	$isAdmin=$topic->project->isAdmin;

	$ret.='<h3 class="title">แบบฟอร์มการสังเคราะห์คุณค่าของโครงการ</h3>';

	$titleRs=end($valuationTr->items['title']);

	$locked=$titleRs->flag;

	if (post('lock') && $isAdmin && $titleRs->trid) {
		$locked=$titleRs->flag==_PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
		$stmt='UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
		mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
		location($url);
	}

	$isEditable=$topic->project->isEdit && !$locked;
	$isEdit=post('act')=='edit' && $isEditable;


	$ret.='<a href="'.url($url,$isAdmin?array('lock'=>$locked?'no':'yes') : NULL).'" title=" สถานะรายงาน : '.($locked?'Locked':'UnLocked').' '.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'" style="position:absolute; right:8px;top:24px;border-radius:50%;background-color:#ddd;padding:4px;"><i class="icon '.($locked?'-lock':'-unlock').'"></i></a>';


	if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom">';
		if ($isEdit) {
			$ret.='<a class="sg-action btn -floating" href="'.url('paper/'.$tpid.'/info/valuation').'" data-rel="#main"><i class="icon -save -white"></i></a>';
		} else {
			$ret.='<a class="sg-action btn -floating" href="'.url('paper/'.$tpid.'/info/valuation',array('act'=>'edit')).'" data-rel="#main"><i class="icon -edit -white"></i></a>';
		}
		$ret.='</div>';
	}



	if ($isEdit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-estimation" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;



	$section='title';
	$irs=end($valuationTr->items[$section]);

	$ret.='<p>ชื่อโครงการ <strong>'.$topic->title.'</strong></p>'._NL;
	$ret.='<p>รหัสโครงการ <strong>'.$topic->project->prid.'</strong> รหัสสัญญา <strong>'.$topic->project->agrno.'</strong> ระยะเวลาโครงการ <strong>'.sg_date($topic->project->date_from,'ว ดดด ปปปป').' - '.sg_date($topic->project->date_end,'ว ดดด ปปปป').'</strong></p>'._NL;



	$ret.='<p>วันที่เริ่มประเมิน <b>'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1),$irs->detail1?$irs->detail1:'',$isEdit,'datepicker').'</b></p>';






	$ret.='<h3 class="title -sub">ประเด็นหลักของโครงการ</h3>';
	$ret.='<p><em>เลือกประเด็นหลักของโครงการ (เลือกได้มากกว่า 1 ข้อ)</em></p>';
	//view::inlineedit(array('group'=>'bigdata','fld'=>'org-name','class'=>'-fill'),$project->info->{'org-name'},$isEditDetail);
	$projectCategory=model::get_category('project:category','catid',ture);
	foreach ($projectCategory as $key => $item) {
		$ret.='<abbr class="checkbox -block -level'.($item->catparent?'2':'1').'"><label>';
		if ($item->process) {
			$ret.=view::inlineedit(
										array(
											'group'=>'bigdata:project.category:'.$item->catid,
											'fld'=>$item->catid,
											'tr'=>$bigDataTr[$item->catid]->bigid,
											'value'=>$bigDataTr[$item->catid]->flddata,
											'removeempty'=>'yes'),
										$item->catid.': '.$item->name,
										$isEdit,
										'checkbox'
										);
		} else {
			$ret.='<input type="checkbox" disabled="disabled" /> '.$item->name;
		}
		$ret.='</label></abbr>';
	}







	$ret.='<h3 class="title -sub1">การเกิดขึ้นของเครือข่าย</h3>';
	$ret.=R::Page('project.network',NULL,$tpid,$isEdit?'showbutton':'');








	$ret.='<h3 class="title -sub1">คุณค่าที่เกิดขึ้น</h3>';
	$ret.='<p>เป็นการคุณค่าที่เกิดจากโครงการในมิติต่อไปนี้</p><ol>
<li>ความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ</li>
<li>การปรับเปลี่ยนพฤติกรรมที่มีผลต่อสุขภาวะ</li>
<li>การปรับเปลี่ยนสิ่งแวดล้อมที่เอื้อต่อสุขภาวะ</li>
<li>ผลกระทบเชิงบวกและนโยบายสาธารณะที่เอื้อต่อการสร้างสุขภาวะ</li>
<li>กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่</li>
<li>มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ</li>
</ol>';



	$outputList['inno']=array(
								'title'=>'1. เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
								'items'=>array(
													array('section'=>'1','title'=>'ความรู้ใหม่ / องค์ความรู้ใหม่'),
													array('section'=>'2','title'=>'สิ่งประดิษฐ์ / ผลผลิตใหม่'),
													array('section'=>'3','title'=>'กระบวนการใหม่'),
													array('section'=>'4','title'=>'วิธีการทำงาน / การจัดการใหม่'),
													array('section'=>'5','title'=>'การเกิดกลุ่ม / โครงสร้างในชุมชนใหม่'),
													array('section'=>'6','title'=>'แหล่งเรียนรู้ใหม่'),
													array('section'=>'99','title'=>'อื่นๆ'),
												)
								);
	$outputList['behavior']=array(
								'title'=>'2. เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
								'items'=>array(
													array('section'=>'1','title'=>'การดูแลสุขอนามัยส่วนบุคคล'),
													array('section'=>'2','title'=>'การบริโภค'),
													array('section'=>'3','title'=>'การกิจกรรมทางกายและการออกกำลังกาย'),
													array('section'=>'4','title'=>'การลด ละ เลิก อบายมุข','desc'=>'การลด ละ เลิก อบายมุข เช่น การพนัน เหล้า บุหรี่ สารเสพติด'),
													array('section'=>'5','title'=>'การลดพฤติกรรมเสี่ยง','desc'=>'การลดพฤติกรรมเสี่ยง เช่น พฤติกรรมเสี่ยงทางเพศ การขับรถโดยประมาท'),
													array('section'=>'6','title'=>'การจัดการอารมณ์ / ความเครียด'),
													array('section'=>'7','title'=>'การดำรงชีวิต / วิถีชีวิต','desc'=>'การดำรงชีวิต / วิถีชีวิต เช่น การใช้ภูมิปัญญาท้องถิ่น / สมุนไพรในการดูแลสุขภาพตนเอง'),
													array('section'=>'8','title'=>'พฤติกรรมการจัดการตนเอง ครอบครัว ชุมชน'),
													array('section'=>'9','title'=>'อื่นๆ'),
												)
								);
	$outputList['environment']=array(
								'title'=>'3. การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
								'items'=>array(
													array('section'=>'1','title'=>'กายภาพ','desc'=>'กายภาพ  เช่น  มีการจัดการขยะ  ป่า  น้ำ  การใช้สารเคมีเกษตร  และการสร้างสิ่งแวดล้อมในครัวเรือนที่ถูกสุขลักษณะ'),
													array('section'=>'2','title'=>'สังคม','desc'=>'สังคม เช่น มีความปลอดภัยในชีวิตและทรัพย์สิน ลดการเกิดอุบัติเหตุ ครอบครัวอบอุ่น การจัดสภาพแวดล้อมที่เอื้อต่อเด็ก เยาวชน และกลุ่มวัยต่าง ๆ มีพื้นที่สาธารณะ/พื้นที่ทางสังคม เพื่อเอื้อต่อการส่งเสริมสุขภาพของคนในชุมชน มีการใช้ศาสนา/วัฒนธรรมเป็นฐานการพัฒนา'),
													array('section'=>'3','title'=>'เศรษฐกิจสร้างสรรค์สังคม /สร้างอาชีพ / เพิ่มรายได้'),
													array('section'=>'4','title'=>'มีการบริการสุขภาพทางเลือก และมีช่องทางการเข้าถึงระบบบริการสุขภาพ'),
													array('section'=>'9','title'=>'อื่นๆ'),
												)
								);
	$outputList['publicpolicy']=array(
								'title'=>'4. การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
								'items'=>array(
													array('section'=>'1','title'=>'มีนโยบายสาธารณะ'),
													array('section'=>'2','title'=>'มียุทธศาสตร์'),
													array('section'=>'3','title'=>'มีแผน'),
													array('section'=>'4','title'=>'มีธรรมนูญของพื้นที่'),
													array('section'=>'5','title'=>'มีกติกา/ข้อตกลง/มาตรการ'),
													array('section'=>'6','title'=>'อื่นๆ'),
												)
								);
	$outputList['social']=array(
								'title'=>'5. กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
								'items'=>array(
													array('section'=>'1','title'=>'เกิดการเชื่อมโยงประสานงานระหว่างกลุ่ม / เครือข่าย (ใน และหรือนอกพื้นที่)'),
													array('section'=>'2','title'=>'การเรียนรู้การแก้ปัญหาในพื้นที่ (การประเมินปัญหา การวางแผน การปฏิบัติการ และการประเมิน)'),
													array('section'=>'3','title'=>'การใช้ประโยชน์จากทุนในพื้นที่ เช่น การระดมทุน การใช้ทรัพยากรบุคคลในพื้นที่'),
													array('section'=>'4','title'=>'มีการขับเคลื่อนการดำเนินงานของกลุ่มและพื้นที่ที่เกิดจากโครงการอย่างต่อเนื่อง'),
													array('section'=>'5','title'=>'เกิดกระบวนการจัดการความรู้ในพื้นที่'),
													array('section'=>'6','title'=>'เกิดทักษะในการจัดการโครงการ เช่น การใช้ข้อมูลในการตัดสินใจ การทำแผนปฏิบัติการ'),
													array('section'=>'7','title'=>'อื่นๆ'),
												)
								);
	$outputList['spirite']=array(
								'title'=>'6. มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
								'items'=>array(
													array('section'=>'1','title'=>'ความรู้สึกภาคภูมิใจในตัวเอง / กลุ่ม / ชุมชน'),
													array('section'=>'2','title'=>'การเห็นประโยชน์ส่วนรวมและส่วนตนอย่างสมดุล'),
													array('section'=>'3','title'=>'การใช้ชีวิตอย่างเรียบง่าย และพอเพียง'),
													array('section'=>'4','title'=>'สังคมมีความเอื้ออาทร'),
													array('section'=>'5','title'=>'มีการตัดสินใจโดยใช้ฐานปัญญา'),
													array('section'=>'6','title'=>'อื่นๆ'),
												)
								);

	$tables = new Table();
	$tables->addClass('project-form-estimation -other');
	$tables->colgroup=array('width="20%"','width="5%"','width="5%"','width="25%"','width="25%"','width="20%"');
	$tables->thead='<thead><tr><th rowspan="2">คุณค่าที่เกิดขึ้น<br />ประเด็น</th><th colspan="2">ผลที่เกิดขึ้น</th><th rowspan="2">รายละเอียด/การจัดการ</th><th rowspan="2">หลักฐาน/แหล่งอ้างอิง</th><th rowspan="2">แนวทางการพัฒนาต่อ</th></tr><tr><th style="width:30px;">มี</th><th style="width:30px;">ไม่มี</th></tr></thead>';
	foreach ($outputList as $mainKey=>$mainValue) {
		$tables->rows[]=array('<td colspan="6"><h3 class="title -item">'.$mainValue['title'].'</h3></td>');
		foreach ($mainValue['items'] as $k=>$v) {
			if (!empty($v['section'])) $tables->rows[]='<header>';
			if (empty($v['section'])) {
				$tables->rows[]=array('<td colspan="6"><b>'.$v['title'].'</b></td>');
				continue;
			}
			$section=$mainKey.'.'.$v['section'];
			$irs=end($valuationTr->items[$section]);
			unset($row);
			$row[]='<span>'.($v['section']).'. '.$v['title'].'</span>'.'<p>'.$v['desc'].'</p>';
			$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'1:',$isEdit,'radio');
			$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'rate1', 'name'=>'rate'.$section, 'tr'=>$irs->trid, 'value'=>$irs->rate1),'0:',$isEdit,'radio');
			$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html', 'value'=>trim($irs->text1)),$irs->text1,$isEdit,'textarea');
			$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text2),$irs->text2,$isEdit,'textarea');
			$row[]=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$irs->trid, 'ret'=>'html', 'value'=>$irs->text3),$irs->text3,$isEdit,'textarea');
			$tables->rows[]=$row;

			$tables->rows[]=array('','config'=>array('class'=>'empty'));
		}
	}

	$ret .= $tables->build();






	// ดึงค่า default จากรายละเอียดโครงการ
	$preAbstract='โครงการนี้มีวัตถุประสงค์เพื่อ';
	if ($info->items['objective']) {
		$oi=0;
		foreach ($info->items['objective'] as $irs) {
			$preAbstract.=' ('.(++$oi).') '.$irs->text1;
		}
	} else $ret.=$topic->project->objective;
	$preAbstract.=_NL._NL;

	$oi=0;
	$preAbstract.='กิจกรรมหลักคือ';
	foreach ($mainact->info as $mrs) {
		if (empty($mrs->trid)) continue;
		$preAbstract.=' ('.(++$oi).') '.$mrs->title;
	}
	$preAbstract.=_NL._NL;
	$preAbstract.='ผลการดำเนินงานที่สำคัญ ได้แก่'._NL._NL;

	$preAbstract.='*** คัดสำเนา "ผลการดำเนินงานที่สำคัญ" มาวางตรงนี้ ***'._NL._NL;
	/*
	$oi=0;
	foreach ($mainact->info as $mrs) {
		foreach ($mainact->activity[$mrs->trid] as $key => $activity) {
			$preAbstract.=' ('.(++$oi).') '.$activity->title;
		}
	}
	*/

	//$preAbstract.=_NL._NL;
	$preAbstract.='ข้อเสนอแนะ ได้แก่ (1) ...';


	$section='title';
	$irs=end($valuationTr->items[$section]);

	$ret.='<h3 class="title -sub">สรุปผล (บทคัดย่อ)</h3>';
	$ret.='<p align="right"><a class="sg-action btn" href="'.url('paper/'.$tpid.'/info/valuation',array('action'=>'getsummary')).'" data-rel="box">คัดสำเนา "ผลการดำเนินงานที่สำคัญ"</a></p>';
	$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html','button'=>'yes', 'value'=>trim(SG\getFirst($irs->text1,$preAbstract))),SG\getFirst($irs->text1,$preAbstract),$isEdit,'textarea');










	$ret.='<p class="noprint">หมายเหตุ *<ul><li><strong>สรุปผล (บทคัดย่อ)</strong> จะนำไปใส่ในบทคัดย่อของรายงานสรุปปิดโครงการ (ส.3)</li><li>หากต้องการใช้ค่าเริ่มต้นของสรุปผล ให้ลบข้อความในช่องสรุปผลทั้งหมด แล้วกดปุ่ม Refresh</li></ul></p>';
	$ret.='</div>';
	$ret.='<style>
	h3.title {margin:8px 0;background:#999;color:#fff;font-size:2em; padding:8px 16px;}
	h3.title.-sub1 {}
	h3.title.-item {color:#333;border: 1px #ccc solid; padding: 8px; border-radius: 4px; background-color: #eee;}

	.__main {position: relative;}
	.project-form-estimation td:nth-child(2), .project-form-estimation td:nth-child(3) {text-align:center;}
	.project-form-estimation thead {display:none;}
	.project-form-estimation .header th {font-weight:normal;}
	.project-form-estimation td:first-child span {background:#888; color:#fff; display: block; padding: 8px; border-radius:4px;}
	.project-form-estimation td {border-bottom:none;}
	.project-form-estimation tr.empty td:first-child {background:transparent;}

	</style>';

	$ret.='<script type="text/javascript">
	// Innovation radio group
	$(".project-form-estimation.-inno span.inline-edit-field").each(function() {
		var radio=$(this).closest("tr").find("input[type=\'radio\']:checked").val();
		//console.log($(this).data("group")+" - "+$(this).data("fld")+" - radio="+radio);
		if (radio!=1) {
			$(this).hide();
		}
	});

	$(".project-form-estimation.-inno input[type=\'radio\']").change(function() {
		var rate=$(this).val();
		var $inlineInput=$(this).closest("tr").find("td>span.inline-edit-field");
		//console.log("radio change "+$(this).val());
		if (rate==1) {
			$inlineInput.show();
		} else {
			$inlineInput.hide();
		}
	});

	// Other radio group
	$(".project-form-estimation.-other span.inline-edit-field").each(function() {
		var radio=$(this).closest("tr").find("input[type=\'radio\']:checked").val();
		//console.log($(this).data("group")+" - "+$(this).data("fld")+" - radio="+radio);
		if (!(radio==0 || radio==1)) {
			$(this).hide();
		}
	});

	$(".project-form-estimation.-other input[type=\'radio\']").change(function() {
		var rate=$(this).val();
		var $inlineInput=$(this).closest("tr").find("td>span");
		//console.log("radio change "+$(this).val());
		$inlineInput.show();
	});

	</script>';

	//$ret.=print_o($valuationTr,'$valuationTr');
	return $ret;
}


?>