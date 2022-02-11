<?php
function project_develop_proposal($self,$tpid,$action=NULL) {
	$devInfo = R::Model('project.develop.get',$tpid);
	$fundInfo = R::Model('project.fund.get',$devInfo->orgid);

	$isEditable = $devInfo->info->RIGHT & _IS_EDITABLE;

	$ret = '';

	if ($isEditable && $action != 'edit') {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/develop/proposal/'.$tpid.'/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	$isEdit = $action == 'edit' && $isEditable;

	R::View('project.toolbar',$self,$devInfo->title,'develop',$devInfo);


	if ($isEdit) {
		head('<script>var tpid='.$tpid.'</script>');

		$inlinePara['class']=' inline-edit';
		$inlinePara['data-tpid']=$tpid;
		$inlinePara['data-update-url']=url('project/develop/update/'.$tpid);
		if (post('debug')) $inlinePara['data-debug']='yes';
	}

	foreach ($inlinePara as $k => $v) {
		$inlineStr.=$k.'="'.$v.'" ';
	}

	$ret.='<div id="project-develop" '.$inlineStr.'>'._NL;

	$ret.='<section id="project-cover" class="box project-cover">';
	$ret.='<h2 class="title">แบบเสนอโครงการกองทุนหลักประกันสุขภาพระดับท้องถิ่น<br />เขต '.$fundInfo->info->areaid.' '.$fundInfo->info->namearea.'</h2>';
	$ret.='<p>รหัสโครงการ …………………………………………</p>';
	$ret.='<p>ชื่อโครงการ/กิจกรรม <b>'.$devInfo->info->title.'</b></p>';
	$ret.='<p>ชื่อกองทุน <b>'.$devInfo->info->orgName.'</b></p>';
	$ret.='<p class="-hidden">ประเภทการสนับสนุน<br />
		<input id="cover-category-1" class="cover-category" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->category==1?' checked="checked"':'').' /> สนับสนุนการจัดบริการสาธารณสุขของ หน่วยบริการ/สถานบริการ/หน่วยงานสาธารณสุข [ข้อ 10(1)]<br />
		<input id="cover-category-2" class="cover-category" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->category==2?' checked="checked"':'').' /> สนับสนุนกิจกรรมสร้างเสริมสุขภาพ การป้องกันโรคของกลุ่มหรือองค์กรประชาชน/หน่วยงานอื่น [ข้อ 10(2)]<br />
		<input id="cover-category-3" class="cover-category" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->category==3?' checked="checked"':'').' /> สนับสนุนการจัดกิจกรรมของ ศูนย์เด็กเล็ก/ผู้สูงอายุ/คนพิการ [ข้อ 10(3)]<br />
		<input id="cover-category-4" class="cover-category" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->category==4?' checked="checked"':'').' /> สนับสนุนการบริหารหรือพัฒนากองทุนฯ [ข้อ 10(4)]<br />
		<input id="cover-category-5" class="cover-category" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->category==5?' checked="checked"':'').' /> สนับสนุนกรณีเกิดโรคระบาดหรือภัยพิบัติ [ข้อ 10(5)]</p>';

	$ret.='<p class="-hidden">หน่วยงาน/องค์กร/กลุ่มคน ที่รับผิดชอบโครงการ<br />
		<input id="cover-ownergroup-1" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==1?' checked="checked"':'').' /> หน่วยบริการหรือสถานบริการสาธารณสุข เช่น รพ.สต.<br />
		<input id="cover-ownergroup-2" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==2?' checked="checked"':'').' /> หน่วยงานสาธารณสุขอื่นของ อปท. เช่น กองสาธารณสุขของเทศบาล<br />
		<input id="cover-ownergroup-3" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==3?' checked="checked"':'').' /> หน่วยงานสาธารณสุขอื่นของรัฐ เช่น สสอ.<br />
		<input id="cover-ownergroup-4" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==4?' checked="checked"':'').' /> หน่วยงานอื่นๆ ที่ไม่ใช่หน่วยงานสาธารณสุข เช่น โรงเรียน กองการศึกษาฯ<br />
		<input id="cover-ownergroup-5" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==5?' checked="checked"':'').' /> กลุ่มหรือองค์กรประชาชนตั้งแต่  5 คน<br />
		<input id="cover-ownergroup-6" class="cover-ownergroup" type="checkbox" readonly="readonly" disabled="disabled" '.($devInfo->info->ownergroup==6?' checked="checked"':'').' /> สำนักงานเลขาฯกองทุน<br />
		</p>';
					

	$ret.='<p>ชื่อองค์กร <b>'.$devInfo->info->orgnamedo.'</b></p>';
	$ret.='<p>กลุ่มคน<br />'.sg_text2html($devInfo->data['owner-name-all']).'';

	$ret.='<p>วันอนุมัติ …………………………………………</p>';
	$ret.='ระยะเวลาดำเนินโครงการ	 ตั้งแต่ วันที่ '.sg_date($devInfo->info->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($devInfo->info->date_end,'ว ดดด ปปปป').'</p>';

	$ret.='งบประมาณ จำนวน '.number_format($devInfo->info->budget,2).' บาท</p>';

	$ret.='</section><!-- project-cover -->';

	$ret.='<hr class="pagebreak" />';

	$ret.='<section id="project-detail" class="box">';
	$ret.='<h3>1. หลักการและเหตุผล</h3><p>'.sg_text2html($devInfo->data['project-problem']).'</p>';

	$ret.='<h3>2. สถานการณ์ปัญหา	</h3>';
	$tables = new Table();
	$tables->thead=array('no'=>'','สถานการณ์ปัญหา','amt -size'=>'ขนาด');
	foreach ($devInfo->problem as $rs) {
		$tables->rows[] = array(
			++$no,
			($rs->refid ? $rs->problem : $rs->problem)
			. ($rs->detailproblem ? '<p>'.$rs->detailproblem.'</p>' : '')
			,
			number_format($rs->problemsize,2),
		);
	}
	$ret.=$tables->build();


	/*
	$ret.='<h3>วิธีดำเนินการ</h3>';

	$ret.='<ol>';
	foreach ($devInfo->activity as $mainact) {
		if (empty($mainact->trid)) continue;
		$ret.='<li>'.$mainact->title.'</li>';
	}
	$ret.='</ol>';
	*/

	$ret.='<h3>3. วัตถุประสงค์/ตัวชี้วัด</h3>';
	$objectiveNo=0;
	$tables = new Table();
	$tables->colgroup=array('no'=>'width="5%"','objective'=>'width="40%"','indicator'=>'width="40%"','targetsize'=>'width="5%"');

	$tables->thead = array(
		'no'=>'',
		'วัตถุประสงค์',
		'ตัวชี้วัดความสำเร็จ',
		'เป้าหมาย 1 ปี',
	);

	foreach ($devInfo->objective as $objective) {
		$tables->rows[] = array(
			++$objectiveNo,
			$objective->title,
			$objective->indicatorDetail,
			$objective->targetsize,
		);
	}

	$ret.=$tables->build();


	$ret.='<h3>4. วิธีดำเนินการ/กิจกรรม</h3>';

	$activityIdx=0;
	foreach ($devInfo->activity as $mainact) {
		if (empty($mainact->trid)) continue;
		$ret .= '<div class="activity-item">';
		$ret .= '<h4>'.(++$activityIdx).'. '.$mainact->title.'</h4>';
		$ret .= '<b>รายละเอียด</b>'.sg_text2html($mainact->desc);
		//$ret.='<b>ผลที่คาดว่าจะได้รับ</b>'.sg_text2html($mainact->output);
		$ret .= '<b>งบประมาณ '.number_format($mainact->budget,2).' บาท</b>';
		$ret .= '</div>';
	}

	$ret.='<h3>5. งบประมาณ</h3>';
	$ret.='<b>งบประมาณโครงการ '.number_format($devInfo->info->budget,2).' บาท</b>';

	$ret.='<h3>6. ระยะเวลาดำเนินการ</h3><p>ระยะเวลาดำเนินโครงการ	 ตั้งแต่ วันที่ '.sg_date($devInfo->info->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($devInfo->info->date_end,'ว ดดด ปปปป').'</p>';

	$ret.='<h3>7. สถานที่ดำเนินการ</h3><p>'.$devInfo->info->area.'</p>';

	$ret.='<h3>8. งบประมาณ</h3><p>จากงบประมาณกองทุนหลักประกันสุขภาพ'.$devInfo->info->orgName.'  จำนวน '.number_format($devInfo->info->budget,2).' บาท</b> รายละเอียดดังในวิธีดำเนินการ/กิจกรรม ด้านบน';
	if ($devInfo->data['budget-remark']) $ret .= '<p><b>หมายเหตุ : </b>'.$devInfo->data['budget-remark'].'</p>';

	//$ret.=R::Page('project.develop.plan.single',NULL,$tpid);



	$ret.='<h3>9. ผลที่คาดว่าจะได้รับ</h3><p>'.sg_text2html($devInfo->data['conversion-human']).'</p>';




	$ret.='</section><!-- project-detail -->';

	$ret.='<section id="project-10" class="box">';
	$ret.='<h3>10. สรุปแผนงาน/โครงการ/กิจกรรม</h3>';

	$ret.='<h4>10.1 หน่วยงาน/องค์กร/กลุ่มคน ที่รับผิดชอบโครงการ (ตามประกาศคณะกรรมการหลักประกันฯ พ.ศ. 2561 ข้อ 10)</h4>';
	
	if ($devInfo->info->orgnamedo) $ret.='<p><b>ชื่อหน่วยงาน/องค์กร '.$devInfo->info->orgnamedo.'</b></p>';
	if ($devInfo->data['owner-name-all']) $ret.='<p><b>ชื่อกลุ่มคน</b><br />'.nl2br($devInfo->data['owner-name-all']).'</p>';

	$supportOrgNameList=model::get_category('project:supportorg','catid');

	$ret.='<b>ประเภทหน่วยงาน</b>';
	foreach ($supportOrgNameList as $key => $value) {
		/*
		$retx.='<abbr class="checkbox"><label>'
					.'<input type="radio" data-type="radio" class="inline-edit-field" name="ownergroup" data-group="dev" data-group="dev" data-fld="ownergroup" value="'.$key.'" '.($key==$devInfo->info->ownergroup?'checked="checked"':'').' /> '
					.$key.':'.'10.1.'.$key.' '.$value
					.'</label>'
					.'</abbr>';
		*/
		$ret .= '<abbr class="checkbox"><label>'
			.view::inlineedit(
					array('group'=>'dev','fld'=>'ownergroup','class'=>'-fill -ownergroup','value'=>$devInfo->info->ownergroup),
					$key.':'.'10.1.'.$key.' '.$value,
					$isEdit,
					'radio'
				)
			.'</label>'
			.'</abbr>';
	}

	$supportTypeNameList=model::get_category('project:supporttype','catid');

	$ret.='<h4>10.2 ประเภทการสนับสนุน (ตามประกาศคณะกรรมการหลักประกันฯ พ.ศ. 2561 ข้อ 10)</h4>';
	foreach ($supportTypeNameList as $key => $value) {
		$ret .= '<abbr class="checkbox"><label>'
			.view::inlineedit(
					array('group'=>'dev','fld'=>'category','class'=>'-fill -category','value'=>$devInfo->info->category),
					$key.':'.'10.2.'.$key.' '.$value,
					$isEdit,
					'radio'
				)
			.'</label>'
			.'</abbr>';
	}

	$ret.='<h4>10.3 กลุ่มเป้าหมายหลัก</h4>';

	$targetgroupList=array(
		1=>array('2001','กลุ่มหญิงตั้งครรภ์และหญิงหลังคลอด'),
		array('1001','กลุ่มเด็กเล็กและเด็กก่อนวัยเรียน'),
		array('1002','กลุ่มเด็กวัยเรียนและเยาวชน'),
		array('1003','กลุ่มวัยทำงาน'),
		array('1004','กลุ่มผู้สูงอายุ'),
		array('2002','กลุ่มผู้ป่วยโรคเรื้อรัง'),
		array('2003','กลุ่มคนพิการและทุพพลภาพ'),
		array('2004','กลุ่มประชาชนทั่วไปที่มีภาวะเสี่ยง'),
		array('2005','สำหรับการบริหารหรือพัฒนากองทุนฯ [ข้อ 10(4)]'),
	);

	foreach ($targetgroupList as $key => $value) {
		$ret .= '<abbr class="checkbox"><label>'
			. view::inlineedit(
					array('group'=>'dev','fld'=>'targetgroup','class'=>'-fill -targetgroup','value'=>$devInfo->info->targetgroup),
					$value[0].':'.'10.3.'.$key.' '.$value[1],
					$isEdit,
					'radio'
				)
			.'</label>'
			.'</abbr>';
	}
	$ret .= '<p><label>จำนวนกลุ่มเป้าหมายที่คาดว่าจะได้รับผลประโยชน์</label>'
		. view::inlineedit(
				array('group'=>'bigdata', 'fld'=>'target-main-total','class'=>''),
				$devInfo->data['target-main-total'],
				$isEdit
			)
		.' คน</p>';


	$ret.='<section id="act-target" class="act-target"><h4>10.4 กิจกรรมหลักตามกลุ่มเป้าหมายหลัก</h4>';



	$targetList=model::get_category('project:target','catid');
	$stmt = 'SELECT
		  p.`catid` `parentId`, p.`name` `parentName`
		, c.`catid`, c.`name` `targetName`
		, t.`amount`
		FROM %tag% p
			LEFT JOIN %tag% c ON c.`taggroup`="project:target" AND c.`catparent`=p.`catid`
			LEFT JOIN %project_target% t ON t.`tpid`=:tpid AND t.`tgtid`=c.`catid`
		WHERE p.`taggroup`="project:target" AND p.`catparent` IS NULL;
		-- {group:"parentId", key:"catid"}';
	$targetList=mydb::select($stmt,':tpid',$tpid)->items;
	//$ret.=print_o($targetList,'$targetList');

	$stmt = 'SELECT
		  p.`tgtid`, p.`tpid` `planId`, p.`weight`
		, g.`name` `targetGroup`
		, t.`title` `planName`
		, tp.`parent` `planSelect`
		FROM %project_targetplan% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %tag% g ON g.`taggroup`="project:target" AND g.`catid`=p.`tgtid`
			LEFT JOIN %topic_parent% tp ON tp.`tpid`=:tpid AND tp.`parent`=p.`tpid` AND tp.`tgtid`=p.`tgtid`
		ORDER BY `tgtid`,`weight`;
		-- {group:"tgtid",key:"planId"}';
	$mainactList=mydb::select($stmt,':tpid',$tpid)->items;
	//$ret.=mydb()->_query;

	foreach ($targetgroupList as $targetGroupKey=>$targetGroupValue) {
		$h=reset($targetGroup);

		$ret.='<section id="act-target-group-'.$targetGroupValue[0].'" class="act-target-group -'.$targetGroupValue[0].'"><h5>10.4.'.$targetGroupKey.' '.$targetGroupValue[1].'</h5>';
		//$ret.=print_o($mainactList[$targetGroupValue[0]]);
		$key=0;
		foreach ($mainactList[$targetGroupValue[0]] as $mainactItem) {
			$key++;
			$dataKey='act-target-'.$targetGroupValue[0].'-'.$mainactItem->planId;
			$ret .= '<abbr id="act-target-item-'.$targetGroupValue[0].'-'.$mainactItem->planId.'" class="checkbox act-target-item -'.$targetGroupValue[0].'-'.$mainactItem->planId.'"><label>'
				. view::inlineedit(
						array(
							'group'=>'bigdata:project.develop:'.$dataKey,
							'fld'=>$dataKey,
							'class'=>'-fill',
							'value'=>$devInfo->data[$dataKey],
							'removeempty'=>"yes"
						),
						$mainactItem->planId.':'.'10.4.'.$targetGroupKey.'.'.$key.' '.$mainactItem->planName,
						$isEdit,
						'checkbox'
					)
				.'</label>'
				.'</abbr>';
		}

		$dataKey='act-target-other-'.$targetGroupValue[0];
		$ret.='<p><label>ระบุ</label>'
					. view::inlineedit(
							array('group'=>'bigdata:project.develop:'.$dataKey, 'fld'=>$dataKey,'class'=>'-fill'),
							$devInfo->data[$dataKey],
							$isEdit
						)
					.'</p>';
		$ret.='</section>';
	}

	$ret .= '<p style="width:500px;margin:32px 0 0 auto; text-align:center;">ลงชื่อ  . . . . . . . . . . . . . . . . . . . . . . . . . . . <span style="display:inline-block;width:14em; text-align: left;">ผู้เสนอแผนงาน/โครงการ/กิจกรรม</span><br /><br />
       ( . . . . . . . . . . . . . . . . . . . . . . . . . . . .)<span style="display:inline-block;width:12em;"></span><br /><br />
ตำแหน่ง  . . . . . . . . . . . . . . . . . . . . . . . . . . . <span style="display:inline-block;width:13em;"></span><br /><br />
วันที่-เดือน-พ.ศ.  . . . . . . . . . . . . . . . . . .<span style="display:inline-block;width:13em;"></span></p>';
	$ret.='</section>';


	//$ret.=print_o($mainactList,'$mainactList');
	//$ret.=print_o($devInfo->data,'$devInfo->data');

	$ret.='</section><!-- project-7 -->';




	$ret.='<hr class="pagebreak" />';
	$ret.='<section id="project-result" class="box project-result">';
	$ret.='<h3>ส่วนที่ 2 : ผลการพิจารณาแผนงาน/โครงการ/กิจกรรม (สำหรับเจ้าหน้าที่ อปท. ที่ได้รับมอบหมายลงรายละเอียด)</h3>';

	$ret.='<p class="text-indent">ตามมติการประชุมคณะกรรมการกองทุนหลักประกันสุขภาพ  . . . . . . . . . . . . . .  . . . . . . . . . . . . . . . . . . . . . . . . . . . . <br />ครั้งที่  . . . . . . . . . . . / . . . . . . . . . เมื่อวันที่  . . . . . . . . . . . . . . . . . . .  ผลการพิจารณาแผนงาน/โครงการ/กิจกรรม ดังนี้</p>
		<p class="text-indent"><input type="checkbox" readonly="readonly" disabled="disabled" /> อนุมัติงบประมาณ เพื่อสนับสนุนแผนงาน/โครงการ/กิจกรรม จำนวน  . . . . . . . . . . . . . . บาท<br />
		เพราะ . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		<p class="text-indent"><input type="checkbox" readonly="readonly" disabled="disabled" /> ไม่อนุมัติงบประมาณ เพื่อสนับสนุนแผนงาน/โครงการ/กิจกรรม<br />
		เพราะ . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		  <p class="text-indent">หมายเหตุเพิ่มเติม (ถ้ามี) . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . . </p>
		 <p class="text-indent">ให้รายงานผลความสำเร็จของแผนงาน/โครงการ/กิจกรรม ตามแบบฟอร์ม (ส่วนที่ 3) ภายในวันที่  . . . . . . . . . . . . . . . . . . . . . . . . . . . .</p>
		 <p style="width:400px;margin:32px 0 0 auto; text-align:center;">ลงชื่อ  . . . . . . . . . . . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
       ( . . . . . . . . . . . . . . . . . . . . . . . . . . . . )<br /><br />
ตำแหน่ง  . . . . . . . . . . . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br /><br />
วันที่-เดือน-พ.ศ.  . . . . . . . . . . . . . . . . . .&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>';
	$ret.='</section><!-- project-2 -->';

	$ret.='</div><!-- project-develop -->'._NL;

	//$ret.=print_o($fundInfo);
	//$ret.=print_o($devInfo,'$devInfo');

	$ret.='<style type="text/css">
	.project-develop-plan-add {display:none;}
	abbr.checkbox {display:block; padding:4px 0;}
	h4, h5 {background:#eee; margin:8px 0;}
	.project-cover .title {text-align: center; padding-bottom:32px;}
	.project-cover p {padding:8px 0;}
	.project-result p {padding:16px 0;}
	.project-result p.text-indent {text-indent:1cm;}
	.activity-item {padding: 16px 16px;}

	@media print {
		.project-cover p.-hidden {display: block;}
		.module-project .box {padding:0; margin:0; box-shadow:none; border:none;}
		.module-project .box h3,
		.module-project .box h4,
		.module-project .box h5 {color:#000; background:transparent; padding:8px 0; font-weight: bold;}
	}
	</style>';

	$ret .= '<script type="text/javascript">
	$(".inline-edit-field.-category").change(function() {
		var $this = $(this)
		var coverId = "#cover-category-" + $this.val()
		$(".cover-category").prop("checked", false)
		$(coverId).prop("checked", true)
	});
	$(".inline-edit-field.-ownergroup").change(function() {
		var $this = $(this)
		var coverId = "#cover-ownergroup-" + $this.val()
		$(".cover-ownergroup").prop("checked", false)
		$(coverId).prop("checked", true)
	});

	</script>';
	return $ret;
}
?>