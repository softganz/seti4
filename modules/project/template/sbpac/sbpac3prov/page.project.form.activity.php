<?php
/**
 * Post/Update project activity
 *
 * @param Integer $tpid
 * @param Array $_POST["activity"]
 * @return String
 */
function project_form_activity($self,$topic=NULL,$para=NULL,$body=NULL,$part='owner') {
	if ($topic->project->project_status!='กำลังดำเนินโครงการ') return false;

	$action=post('act');
	$tpid=$topic->tpid;
	$trid=post('trid');
	$isAdmin=user_access('administer projects');
	$isEdit=user_access('administer projects') || (project_model::is_owner_of($tpid) && $part=="owner") || (project_model::is_trainer_of($tpid));
	if (!$isEdit) return false;

	$lockReportDate=project_model::get_lock_report_date($tpid);




	switch ($action) {

		// Add new report of calendar
		case 'addreport':
			$calid=post('calid');
			if ($calid) {
				$stmt='SELECT
						  c.`id` `calid`, c.`tpid`, tr.`trid`, tr.`calid` trCalId
						, a.`mainact` `mainactid`
						, "activity" `formid`, IF(a.`calowner`=1,"owner","trainer") `part`
						, c.`from_date`, c.`from_time`
						, m.`text1` `goal_do`
					FROM %calendar% c
						LEFT JOIN %project_tr% tr ON tr.`calid`=c.`id` AND tr.`formid`="activity"
						LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
						LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
					WHERE c.`id`=:calid AND c.`tpid`=:tpid LIMIT 1';
				$calRs=mydb::select($stmt,':calid',$calid,':tpid',$tpid);
				if ($calRs->_empty) return message('error','ข้อมูลปฏิทินกิจกรรมผิดพลาด');

				if (!$calRs->trid) {	// Report not exists
					$trid=null;
				} else {	// Report exists
					$trid=$calRs->trid;
				}
				//$ret.=print_o($calRs,'$calRs');
			} else if (post('trid')) {
				$trid=post('trid');
			} else {
				return message('error','ไม่ได้ระบุกิจกรรม');
			}

			// Get activity information and show form
			if ($trid) {
				$rs=__project_activity_get($tpid,$trid);
				//if (i()->username=='softganz') $ret.=print_o($rs,'$rs');
				if ($rs->_empty) return $ret.message('error','ข้อมูลบันทึกกิจกรรมผิดพลาด');
			} else {
				$rs=__project_activity_getcalendar($calid);
			}
			$ret.=__project_activity_form_activity($tpid,$rs);
			//$ret.=print_o($rs,'$rs');
			//$ret.='Add report '.$part.' trid='.$trid;
			return $ret;
			break;
		
		case 'updateaction' :
			$ret.=__project_activity_update();
			break;

		default:

			break;
	}


	// No activity was set, show list of activity for send activity
	$tables = new Table();
	$tables->addClass('project-activity-title');
	$order=SG\getFirst($_REQUEST['o'],'date');
	$calowner=$part=='owner'?1:2;
	$tables->thead=array(
		'no'=>'',
		'date'=>'<a href="'.url(q(),array('o'=>'date')).'">วันที่ทำกิจกรรม<br />(ตามแผน)'.($order=='date'?' v ':'').'</a>',
		'<a href="'.url(q(),array('o'=>'title')).'">รายชื่อกิจกรรมตามแผนที่วางไว้'.($order=='title'?' v ':'').'</a>',
		'money'=>'ค่าใช้จ่าย<br />(บาท)',
		'date submenu'=>'',
		'amt'=>'รายงานช้า<br />(วัน)',
		''
	);
	$orders=array('date'=>'from_date','title'=>'c.title');
	$stmt='SELECT
			  DATEDIFF(:curdate,c.`to_date`) late
			, c.*
			, c.`id` `calid`
			, tr.`trid`
			, tr.`flag`
			, COUNT(tr.`calid`) trtotal
			, tr.`num7` `exp_total`
		FROM %calendar% c
			LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
			LEFT JOIN %project_tr% tr ON tr.`tpid`=c.`tpid` AND tr.`formid`="activity" AND tr.`part` IN (:part) AND tr.`calid`=c.`id`
		WHERE c.`tpid`=:tpid AND a.`calowner`=:calowner
		GROUP BY c.`id`
		ORDER BY '.$orders[$order].' ASC, `from_date` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid,':part',$part, ':calowner',$calowner, ':curdate',date('Y-m-d'));

	if ($dbs->_empty) $ret.='<p class="notify">ยังไม่มีการสร้างกิจกรรมในปฏิทินกิจกรรมของโครงการ กรุณา <b><a href="'.url('paper/'.$tpid).'">คลิกที่รายละเอียดโครงการ</a></b> เพื่อ <b>"เพิ่มกิจกรรมย่อยของโครงการ"</b> ก่อน</p>';

	foreach ($dbs->items as $crs) {
		$isLate=$crs->to_date>$lockReportDate && $crs->trtotal<=0 && $crs->late>0;
		$ui=new ui();
		if ($crs->trtotal) {
			$ui->add('<a href="'.url('paper/'.$crs->tpid.'/'.$part,array('act'=>'addreport','trid'=>$crs->trid)).'" title="แก้ไข'.($crs->flag==_PROJECT_DRAFTREPORT?'(ร่าง)':'').'บันทึกกิจกรรม">แก้ไข'.($crs->flag==0?'(ร่าง)':'').'บันทึกกิจกรรม</a>');
			$ui->add('<a href="'.url('project/'.$tpid.'/info.expense/'.$crs->calid).'">ค่าใช้จ่าย/เอกสารการเงิน</a>');
		}
		$ui->add('<a href="'.url('project/'.$tpid.'/info.join/'.$crs->id).'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
		//$ui->add('<a href="'.url('paper/'.$tpid.'/owner/menu',array('calid'=>$crs->id)).'">ช่วยเหลือ</a>');
		$submenu=sg_dropbox($ui->build('ul'),'{type:"click"}');
		$tables->rows[]=array(
			++$no,
			sg_date($crs->from_date,'ว ดด ปปปป').($crs->to_date && $crs->to_date!=$crs->from_date?' - '.sg_date($crs->to_date,'ว ดด ปปปป'):''),
			$crs->title,
			$crs->exp_total?number_format($crs->exp_total,2):'',
			(cfg('project.activity.multiplereport') ?
				// Multiple report
				'<a href="'.(url('paper/'.$tpid.'/'.$part,'calid='.$crs->id)).'" title="บันทึกผลการทำกิจกรรมที่เสร็จเรียบร้อยแล้ว">บันทึกกิจกรรม</a>'.($crs->trtotal?'<span class="moredetail"> ('.$crs->trtotal.' บันทึก)<span>':'') :
				// Single report
				($crs->trtotal && $crs->flag==_PROJECT_DRAFTREPORT?'(ร่าง)':'')
				// Have report send
				. ($crs->trtotal ?
						'<!-- <span title="กิจกรรมนี้ได้มีการบันทึกผลกิจกรรมไปแล้ว สามารถแก้ไขรายละเอียดได้จากบันทึกรายงานกิจกรรมด้านล่าง">บันทึกกิจกรรม</span> -->' :
						($crs->from_date>$lockReportDate ?
							'<a href="'.(url('paper/'.$tpid.'/'.$part.'/activity',array('act'=>'addreport','calid'=>$crs->id))).'">บันทึกกิจกรรม</a>':
							'')
						).($crs->trtotal ?
							'(<a href="#tr-'.$crs->trid.'" title="คลิกเพื่อดูบันทึกกิจกรรม">'.$crs->trtotal.' บันทึกกิจกรรม</a>)' :
							'')
					),
			$isLate?$crs->late.' วัน':'',
			$submenu,
			'config'=>array('class'=>($isLate?'late':'').($crs->from_date<$lockReportDate?' lockreport':'') ),
		);
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');

	$ret.='<style type="text/css">.project-activity-title th {white-space:nowrap;} .project-activity-title td:nth-child(4) {padding-right:10px;}</style>';
	return $ret;
}

function __project_activity_getcalendar($calid) {
	$stmt='SELECT
			  c.*
			, a.`calowner`
			, a.`targetpreset`
			, a.`target`
			, a.`budget`
			, a.`mainact`
			, m.`detail1` `mainact_title`
			, m.`text3` `presetOutputOutcome`
		FROM %calendar% c
			LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
			LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
		WHERE c.id=:id LIMIT 1';
	$calendar=mydb::select($stmt,':id',$calid);
	$rs->calid=$calendar->id;
	$rs->part=$calendar->calowner==1?'owner':'trainer';
	$rs->date1=$calendar->from_date;
	$rs->date2=sg_date($calendar->from_date,'d/m/Y');
	$rs->activityname=$calendar->title;
	$rs->detail1=substr($calendar->from_time,0,5);
	$rs->text1=$calendar->detail;
	$rs->targetpreset=$calendar->targetpreset;
	$rs->text3=$calendar->target;
	$rs->budget=$calendar->budget;
	$rs->mainact=$calendar->mainact;
	$rs->presetOutputOutcome=$calendar->presetOutputOutcome;
	return $rs;
}

/**
* Get activity information
* @param Int $trid
* @return Record Set
*/
function __project_activity_get($tpid,$trid) {
	$stmt='SELECT tr.*
			, c.`title` activityname
			, c.`detail`
			, tr.`date1` `action_date`
			, tr.`detail1` `action_time`
			, tr.`text1` `goal_do`
			, tr.`text2` `real_do`
			, tr.`text4` `real_work`
			, tr.`text5` `problem`
			, a.`targetpreset`, a.`budget`, a.`mainact`
			, m.`detail1` mainact_title
			, m.`text3` `presetOutputOutcome`
			, m.`num2` `target`
			, m.`num3` `presetTargetChild`
			, m.`num4` `presetTargetTeen`
			, m.`num5` `presetTargetWorker`
			, m.`num6` `presetTargetElder`
			, m.`num7` `presetTargetDisabled`
			, m.`num8` `presetTargetWoman`
			, m.`num9` `presetTargetMuslim`
			, m.`num10` `presetTargetWorkman`
			, m.`num11` `presetTargetOtherman`
		FROM %project_tr% tr
			LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
			LEFT JOIN %project_activity% a ON a.`calid`=tr.`calid`
			LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
		WHERE tr.`tpid`=:tpid AND tr.`trid`=:trid AND tr.`formid`="activity" LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid, ':trid',$trid);
	return $rs;
}

/**
* Show activity form
* @param Object $post
* @return String
*/
function __project_activity_form_activity($tpid,$post) {
	$isAdmin=user_access('administer projects');
	$mainActivity=project_model::get_main_activity($tpid);

	$form=new Form('activity',url('paper/'.$tpid.'/owner',array('act'=>'updateaction')),'activity-add');
	$form->config->enctype='multipart/form-data';
	$form->config->attr='autosave="'.url('project/edit/activityautosave/'.$tpid).'"';
	$form->title='<h3>ชื่อกิจกรรม : '.$post->activityname.'</h3>';

	$form->tpid=array('type'=>'hidden','value'=>$tpid);
	$form->calid=array('type'=>'hidden','value'=>$post->calid);
	$form->trid=array('type'=>'hidden','value'=>$post->trid);
	$form->part=array('type'=>'hidden','value'=>$post->part);

	$form->date1->type='hidden';
	$form->date1->value=htmlspecialchars(SG\getFirst($post->date1,date('Y-m-d')));

	$form->s1->type='textfield';
	$form->s1->value='<h4>รายละเอียดกิจกรรมตามแผนงานที่วางไว้</h4>';

	$form->activityname->type='text';
	$form->activityname->label='ชื่อกิจกรรม';
	$form->activityname->class='-fill';
	$form->activityname->require=true;
	$form->activityname->size=60;
	$form->activityname->placeholder='ระบุชื่อกิจกรรม หรือป้อนชื่อแล้วเลือกจากรายการ';
	$form->activityname->value=$post->activityname;

	$form->mainact->type='hidden';
	$form->mainact->value=$post->mainact;
	if (cfg('project.usemainact') && ($isAdmin || $post->mainact<=0)) {
		$form->mainact->type='select';
		$form->mainact->label='กิจกรรมหลัก';
		foreach ($mainActivity->info as $_rs) {
			if ($_rs->trid) $form->mainact->options[$_rs->trid]=$_rs->title;
		}
	}


	$form->text1->type='textarea';
	$form->text1->label='รายละเอียดกิจกรรมตามแผน';
	$form->text1->class='-fill';
	$form->text1->cols=15;
	$form->text1->rows=6;
	$form->text1->require=false;
	$form->text1->description=$form->text1->placeholder='ระบุลักษณะของกิจกรรมตามแผนที่วางไว้';
	$form->text1->value=$post->text1;

	$form->targetpreset->type='text';
	$form->targetpreset->label='จำนวนกลุ่มเป้าหมายที่ตั้งไว้เชิงปริมาณ (จำนวนคน)';
	$form->targetpreset->maxlength=5;
	$form->targetpreset->require=true;
	$form->targetpreset->placeholder='0';
	$form->targetpreset->value=htmlspecialchars($post->targetpreset);

	$form->text3->type='textarea';
	$form->text3->label='รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้ตามแผนงาน';
	$form->text3->class='-fill';
	$form->text3->cols=15;
	$form->text3->rows=3;
	$form->text3->require=true;
	$form->text3->description=$form->text3->placeholder='ระบุรายละเอียดของกลุ่มเป้าหมายที่ตั้งไว้ตามแผนงาน เช่น กลุ่ม ภาคี จำนวนคน';
	$form->text3->value=$post->text3;

	$form->presetOutputOutcome->type='textarea';
	$form->presetOutputOutcome->label='ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่ตั้งไว้';
	$form->presetOutputOutcome->class='-fill';
	$form->presetOutputOutcome->cols=15;
	$form->presetOutputOutcome->rows=6;
	$form->presetOutputOutcome->readonly=true;
	$form->presetOutputOutcome->description=$form->presetOutputOutcome->placeholder='ระบุ ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่ตั้งไว้';
	$form->presetOutputOutcome->value=$post->presetOutputOutcome;




	$form->header3->type='textfield';
	$form->header3->value='<h4>แบบรายงานความก้าวหน้าโครงการ (ส. 1)</h4>';

	$form->addField(
		'date2',
		array(
			'type'=>'text',
			'label'=>'วันที่ปฎิบัติจริง',
			'class'=>'sg-datepicker -date',
			'require'=>true,
			'value'=>htmlspecialchars(sg_date(SG\getFirst($post->date1,date('Y-m-d')),'d/m/Y')),
			'attr'=>array('data-alt-field'=>'#edit-activity-date1'),

		)
	);

	$form->detail1->type='text';
	$form->detail1->label='เวลา/ช่วงเวลาการจัดกิจกรรม';
	$form->detail1->size=10;
	$form->detail1->maxlength=30;
	$form->detail1->require=true;
	$form->detail1->value=htmlspecialchars($post->detail1);
	$form->detail1->placeholder='00:00';

	$form->num8->type='text'; //participants
	$form->num8->label='จำนวนคน/ผู้เข้าร่วมกิจกรรมจริง';
	$form->num8->maxlength=5;
	$form->num8->require=true;
	$form->num8->placeholder='0';
	$form->num8->value=htmlspecialchars(number_format($post->num8,0));

	$form->text9->type='textarea';
	$form->text9->label='รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม';
	$form->text9->class='-fill';
	$form->text9->cols=15;
	$form->text9->rows=3;
	$form->text9->require=true;
	$form->text9->description=$form->text9->placeholder='ระบุรายละเอียดของกลุ่มเป้าหมายที่เข้าร่วมจริง เช่น กลุ่ม ภาคี จำนวนคน';
	$form->text9->value=$post->text9;

	$form->text2->type='textarea';
	$form->text2->label='รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง';
	$form->text2->class='-fill';
	$form->text2->cols=15;
	$form->text2->rows=6;
	$form->text2->require=true;
	$form->text2->description=$form->text2->placeholder='รายละเอียดขั้นตอน กระบวนการ กิจกรรมที่ได้ปฎิบัติจริง';
	$form->text2->value=$post->text2;

	$form->text4->type='textarea';
	$form->text4->label='ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่เกิดขึ้นจริง';
	$form->text4->class='-fill';
	$form->text4->cols=15;
	$form->text4->rows=15;
	$form->text4->require=true;
	$form->text4->description=$form->text4->placeholder='กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)';
	$form->text4->value=$post->text4;





	for ($i=0; $i<=100; $i=$i+10) $rateOtions[$i]=$i.'%';
	$form->addField(
		'rate1',
		array(
			'type'=>'radio',
			'label'=>'ประเมินความสำเร็จของการดำเนินกิจกรรม',
			'options'=>$rateOtions,
			'value'=>$post->rate1,
			'require'=>true,
			'display'=>'inline-block',
		)
	);

	$form->addField(
		'rate2',
		array(
			'type'=>'radio',
			'label'=>'ประเมินความสำเร็จของการดำเนินกิจกรรม โดยผู้ติดตามโครงการ',
			'options'=>$rateOtions,
			'value'=>$post->rate2,
			'require'=>true,
			'display'=>'inline-block',
		)
	);

	if ($part=='owner') {
		$form->s2->type='textfield';
		$form->s2->value='<h4>แบบรายงานการติดตามสนับสนุนโครงการ (ส.2)</h4>';

		$form->text8->type='textarea';
		$form->text8->label='คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่';
		$form->text8->class='-fill';
		$form->text8->cols=15;
		$form->text8->rows=5;
		$form->text8->require=true;
		$form->text8->value=$post->text8;

		$form->detail2->type='text';
		$form->detail2->label='ชื่อผู้ติดตามในพื้นที่ของ '.cfg('project.grantby').'';
		$form->detail2->require=true;
		$form->detail2->value=htmlspecialchars($post->detail2);
	} else {
		$form->text8->type='hidden';
		$form->text8->value='-';

		$form->detail2->type='hidden';
		$form->detail2->value='-';
	}

	if ($post->part=='owner') {
		$form->header_budget->type='textfield';
		$form->header_budget->value='<h4>รายงานการใช้เงิน</h4>';

		$form->addField(
			'budget',
			array(
				'type'=>'text',
				'label'=>'งบประมาณที่ตั้งไว้ (บาท)',
				'class'=>'-money',
				'maxlength'=>12,
				'value'=>number_format(htmlspecialchars($post->budget),2),
			)
		);

		$tables = new Table();
		$tables->addClass('project-money-send');
		$tables->caption='รายงานการใช้เงิน';
		$tables->thead='<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';

		if ($post->flag==_PROJECT_LOCKREPORT) {
			$tables->rows[]=array(
				$post->num1.'<input size="10"  name="activity[num1]" id="edit-activity-num1" class="form-text require" type="hidden" readonly="readonly" value="'.htmlspecialchars($post->num1).'" />',
				$post->num2.'<input size="10"  name="activity[num2]" id="edit-activity-num2" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num2).'" />',
				$post->num3.'<input size="10"  name="activity[num3]" id="edit-activity-num3" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num3).'" />',
				$post->num4.'<input size="10"  name="activity[num4]" id="edit-activity-num4" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num4).'" />',
				$post->num5.'<input size="10"  name="activity[num5]" id="edit-activity-num5" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num5).'" />',
				$post->num6.'<input size="10"  name="activity[num6]" id="edit-activity-num6" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num6).'" />',
				$post->num7.'<input size="10"  name="activity[num7]" id="edit-activity-num7" class="form-text require" type="hidden" value="'.htmlspecialchars($post->num7).'" />',
			);
		} else {
			$tables->rows[]=array(
				'<input size="10"  name="activity[num1]" id="edit-activity-num1" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num1).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num2]" id="edit-activity-num2" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num2).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num3]" id="edit-activity-num3" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num3).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num4]" id="edit-activity-num4" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num4).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num5]" id="edit-activity-num5" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num5).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num6]" id="edit-activity-num6" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num6).'" placeholder="0.00" />',
				'<input size="10"  name="activity[num7]" id="edit-activity-num7" class="form-text -money -fill -require" type="text" value="'.htmlspecialchars($post->num7).'" placeholder="0.00" />',
			);
		}
		$form->set2 = $tables->build();
	//			$form->set2.=print_o($post,'$post');
	} else {
		$form->set2='<input type="hidden"  name="activity[num1]" id="edit-activity-num1" value="0" /><input type="hidden"  name="activity[num2]" id="edit-activity-num2" value="0" /><input type="hidden"  name="activity[num3]" id="edit-activity-num3" value="0" /><input type="hidden"  name="activity[num4]" id="edit-activity-num4" value="0" /><input type="hidden"  name="activity[num5]" id="edit-activity-num5" value="0" /><input type="hidden"  name="activity[num6]" id="edit-activity-num6" value="0" /><input type="hidden"  name="activity[num7]" id="edit-activity-num7" value="0" />';
	}

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึกกิจกรรม</span>',
		)
	);


	$form->photoremark='<strong>หมายเหตุ : ภาพถ่ายประกอบกิจกรรมหรือไฟล์รายงานรายละเอียดประกอบกิจกรรม สามารถส่งเพิ่มเติมได้หลังจากบันทึกข้อมูลเสร็จเรียบร้อยแล้ว</strong>';

	$ret .= $form->build();

	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		var errorCount=0;
		var isChange=false;
		var $form=$("form#activity-add");
		var autoSave='.SG\getFirst($_REQUEST['r'],cfg('project.activity.autosave'),120).';
		var formSubmit = false;
		var debug = false

		$(".project-activity-money .form-text").change(function() {
			var amt=$(this).val().replace(/[^0-9.]/g, "");
			var total=0;
			if (amt.indexOf(".") == -1) amt=amt+".00";
			$(this).val(amt);
			$(".project-activity-money .form-text").each(function(i,data) {
				if ($(data).attr("name")!="activity[num7]") {
					var m=$(data).val();
					if (m!="") total+=parseFloat(m);
				}
			});
			$("#edit-activity-num7").val(total.toFixed(2));
			notify(total);
		});

		$form.find(".form-text, .form-textarea").keypress(function(e) {
			isChange=true;
		});

		$(".form-text, .form-checkbox").keypress(function(e) {
			if (e.which == 13) return false;
		});

		/*
		$("#edit-activity-date2")
		.datepicker({
			clickInput:true,
			dateFormat: "dd/mm/yy",
			altFormat: "yy-mm-dd",
			altField: "#edit-activity-date1",
			disabled: false,
			monthNames: thaiMonthName,
		});
		*/


		$("#edit-activity-num7").attr("readonly", true).css({"font-weight": "bold"});
		$(".project-money-send .form-text").change(function() {
			var amt=$(this).val().replace(/[^0-9.]/g, "");
			var total=0;
			if (amt.indexOf(".") == -1) amt=amt+".00";
			$(this).val(amt);
			$(".project-money-send .form-text").each(function(i,data) {
				if ($(data).attr("name")!="activity[num7]") {
					var m=$(data).val();
					if (m!="") total+=parseFloat(m);
				}
			});
			$("#edit-activity-num7").val(total.toFixed(2));
		});

		// ทุก ๆ x นาที ให้บันทึกข้อมูลอัตโนมัติ
		if (autoSave) {
			$(function () {
				(function autoSaveData() {
					if (isChange) {
						$.post($form.attr("autosave"),$form.serialize(true), function(data) {
							formSubmit=data.error?false:true;
							if (debug) notify(data.msg,30000); else notify(data.msg);
							isChange=false;
							if (data.trid) $("#edit-activity-trid").val(data.trid);
						},"json");
					}
					//calling the anonymous function after 1000 milli seconds
					setTimeout(autoSaveData, autoSave*1000);  //second
				})(); //self Executing anonymous function
			});
		}

		$form.submit(function() {
			var error=false;
			var $obj;
			var fld;
			var fldCheck=[
				["edit-activity-date1","วันที่"],
				["edit-activity-activityname","กิจกรรม"],
				/* ["edit-activity-detail3","วัตถุประสงค์ย่อย"], */
				/* ["edit-activity-text1","รายละเอียดกิจกรรมตามแผน"], */
				/* ["edit-activity-text3","รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้ตามแผนงาน"], */
				/* ["edit-activity-text9","รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม"], */
				["edit-activity-text2","รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง"],
				["edit-activity-text4","ผลสรุปที่สำคัญของกิจกรรม"],
				/* ["edit-activity-text5","ปัญหา/แนวทางแก้ไข"], */
				/* ["edit-activity-text6","ข้อเสนอแนะต่อ '.cfg('project.grantby').'"],
				["edit-activity-text7","ความต้องการสนับสนุนจากพี่เลี้ยงและ '.cfg('project.grantpass').'"],*/
				["activity[rate1]","ประเมินผล คุณภาพกิจกรรม"],
				/* ["edit-activity-text8","คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่"],
				["edit-activity-detail2","ชื่อผู้ติดตามในพื้นที่ของ '.cfg('project.grantby').'"], */
				["edit-activity-num1","ค่าตอบแทน"],
				["edit-activity-num2","ค่าจ้าง"],
				["edit-activity-num3","ค่าใช้สอย"],
				["edit-activity-num4","ค่าวัสดุ"],
				["edit-activity-num5","ค่าสาธารณูปโภค"],
				["edit-activity-num6","อื่น ๆ"],
				["edit-activity-num7","รวมรายจ่าย"],
			];
			for (fld in fldCheck) {
				if (fldCheck[fld][0]=="activity[rate1]") {
					if (!$("input[name=\'"+fldCheck[fld][0]+"\']:checked").val()) {
						error=fld;
						break;
					}
				} else if ($("#"+fldCheck[fld][0]).val().trim()=="") {
					error=fld;
					break;
				}
			}

			if (error) {
				// Auto save some data
				if (autoSave) {
					$.post($form.attr("autosave"),$form.serialize(true), function(data) {
						isChange=false;
						if (data.trid) $("#edit-activity-trid").val(data.trid);
					},"json");
				}
				// Notification and return to form
				var errorMsg="กรุณาป้อน \""+fldCheck[error][1]+"\"";
				if (errorCount>10) alert(errorMsg); else notify(errorMsg,10000);
				$("#"+fldCheck[error][0]).focus();
				++errorCount;
				formSubmit=false;
				return false;
			} else if (!formSubmit) {
				notify("กำลังตรวจสอบความถูกต้อง");
				console.log("Activity Saving");
				formSubmit=true;
				$.post($form.attr("autosave"),$form.serialize(true), function(data) {
					if (data.trid) $("#edit-activity-trid").val(data.trid);
					if (data.error) notify("Error : "+data.error);
					formSubmit=data.error?false:true;
					if (formSubmit) $form.submit();
				},"json");
			}
			if (formSubmit) {
				return true;
			} else {
				// Check form submit
				return false;
			}
		});
	 });
	</script>';

	$ret.='<style type="text/css">
	.form-item.-edit-activity-rate1 .option,
	.form-item.-edit-activity-rate2 .option {width:2.5em; padding:8px; border-radius:4px;border:1px #ccc solid; text-align: center; display: inline-block;}
	.form-item.-edit-activity-rate1 .option input,
	.form-item.-edit-activity-rate2 .option input {display: block; margin:0 auto;}
	</style>';
	return $ret;
}

function __project_activity_update($trid=NULL) {
	$post=(object)post('activity',_TRIM_);
	//$ret.=print_o($post,'$post');
	$error=false;

	if (empty($post->trid)) return $ret.'<p class="notify">ERROR : Empty Transaction</p>';

	// Save add/edit activity
	if ($part=='trainer' && empty($post->calid)) $error='กรุณาเลือกกิจกรรมจากรายชื่อกิจกรรมเท่านั้น';

	if ($error) return $error;

	$post->trid=empty($post->trid)?'func.NULL':$post->trid;
	$post->formid='activity';
	$post->uid=i()->ok?i()->uid:'func.NULL';

	if (empty($post->rate1)) $post->rate1=0;
	else if ($post->rate1==-1) $post->rate1=NULL;

	if (empty($post->rate2)) $post->rate2=0;
	else if ($post->rate2==-1) $post->rate2=NULL;

	$post->created=date('U');
	if ($post->trid) {
		$post->modified=date('U');
		$post->modifyby=SG\getFirst(i()->uid,'func.NULL');
	}
	foreach (array('num1','num2','num3','num4','num5','num6','num7','num8','budget','targetpreset') as $k) {
		if (isset($post->{$k})) $post->{$k}=preg_replace('/[^0-9\.\-]/','',$post->{$k});
	}
	$post->num8=abs(intval($post->num8));
	if (!isset($post->detail1)) $post->detail1='';
	if (!isset($post->detail2)) $post->detail2='';
	if (!isset($post->detail3)) $post->detail3='';
	if (!isset($post->detail4)) $post->detail4='';
	if (!isset($post->detail5)) $post->detail5='';

	if (!isset($post->text1)) $post->text1='';
	if (!isset($post->text2)) $post->text2='';
	if (!isset($post->text3)) $post->text3='';
	if (!isset($post->text4)) $post->text4='';
	if (!isset($post->text5)) $post->text5='';
	if (!isset($post->text6)) $post->text6='';
	if (!isset($post->text7)) $post->text7='';
	if (!isset($post->text8)) $post->text8='';
	if (!isset($post->text9)) $post->text9='';
	if (!isset($post->text10)) $post->text10='';
	if (!isset($post->parent) || $post->parent==-1) $post->parent=NULL;

	//detail3, "", "111", "", "222", :text5, :text6, :text7, "-", "", 0, "1.00", "0.0' at line 4

	//$ret.=print_o($post,'$post');

	// Start save data
	// Create new item on calendar when no calid, no $_REQUEST[calid] and not select calendar item from list
	if (empty($post->calid) && $post->activityname) {
		$calendar->tpid=$post->tpid;
		$calendar->owner=$post->uid;
		$calendar->privacy='public';
		$calendar->title=$post->activityname;
		$calendar->from_date=$post->date1;
		$calendar->from_time=$post->detail1;
		$calendar->detail=$post->text2;
		$calendar->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$calendar->created_date=date('Y-m-d H:i:s');
		$stmt='INSERT INTO %calendar% (`tpid`, `owner`, `privacy`, `title`, `from_date`, `from_time`, `detail`, `ip`, `created_date`) VALUES (:tpid, :owner, :privacy, :title, :from_date, :from_time, :detail, :ip, :created_date)';
		mydb::query($stmt,$calendar);

		$post->calid=mydb()->insert_id;
	}
	unset($post->detail);

	$flag=mydb::select('SELECT `flag` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$post->trid)->flag;
	$post->flag=$flag==_PROJECT_LOCKREPORT?_PROJECT_LOCKREPORT:_PROJECT_COMPLETEPORT;

	$stmt='INSERT INTO %project_tr_bak%
			(`trid`, `tpid`, `parent`, `calid`, `formid`, `part`, `flag`, `uid`, `date1`, `detail1`, `detail2`,`detail3`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text7`, `text8`, `text9`, `rate1`, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `num8`, `created`)
		VALUES
			(:trid, :tpid, :parent, :calid, :formid, :part, :flag, :uid, :date1, :detail1, :detail2, :detail3, :text1, :text2, :text3, :text4, :text5, :text6, :text7, :text8, :text9, :rate1, :num1, :num2, :num3, :num4, :num5, :num6, :num7, :num8, :created)
		ON DUPLICATE KEY
		UPDATE `calid`=:calid, `part`=:part, `flag`=:flag, `date1`=:date1, `detail1`=:detail1, `detail2`=:detail2, `detail3`=:detail3, `text1`=:text1, `text2`=:text2, `text3`=:text3, `text4`=:text4, `text5`=:text5, `text6`=:text6, `text7`=:text7, `text8`=:text8, `text9`=:text9, `rate1`=:rate1, `num1`=:num1, `num2`=:num2, `num3`=:num3, `num4`=:num4, `num5`=:num5, `num6`=:num6, `num7`=:num7, `num8`=:num8, `modified`=:modified, modifyby=:modifyby ;';
	mydb::query($stmt,$post);
	//$ret.=mydb()->_query.'<br />';

	$stmt='INSERT INTO %project_tr%
			(`trid`, `tpid`, `parent`, `calid`, `formid`, `part`, `flag`, `uid`, `date1`, `detail1`, `detail2`,`detail3`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text7`, `text8`, `text9`, `rate1`, `rate2`, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`, `num8`, `created`)
		VALUES
			(:trid, :tpid, :parent, :calid, :formid, :part, :flag, :uid, :date1, :detail1, :detail2, :detail3, :text1, :text2, :text3, :text4, :text5, :text6, :text7, :text8, :text9, :rate1, :rate2, :num1, :num2, :num3, :num4, :num5, :num6, :num7, :num8, :created)
		ON DUPLICATE KEY
		UPDATE `parent`=:parent, `calid`=:calid, `part`=:part, `flag`=:flag, `date1`=:date1, `detail1`=:detail1, `detail2`=:detail2, `detail3`=:detail3, `text1`=:text1, `text2`=:text2, `text3`=:text3, `text4`=:text4, `text5`=:text5, `text6`=:text6, `text7`=:text7, `text8`=:text8, `text9`=:text9, `rate1`=:rate1, `rate2`=:rate2, `num1`=:num1, `num2`=:num2, `num3`=:num3, `num4`=:num4, `num5`=:num5, `num6`=:num6, `num7`=:num7, `num8`=:num8, `modified`=:modified, modifyby=:modifyby ;';
	mydb::query($stmt,$post);
	//$ret.=mydb()->_query.'<br />';

	$trid=$post->trid=='func.NULL'?mydb()->insert_id:$post->trid;

	$post->calowner=$part=='owner'?_PROJECT_OWNER_ACTIVITY:_PROJECT_TRAINER_ACTIVITY;

	$stmt='INSERT INTO %project_activity% (`calid`, `calowner`, `mainact`, `targetpreset`, `budget`) VALUES (:calid, :calowner, :mainact, :targetpreset, :budget)
					ON  DUPLICATE KEY UPDATE `mainact`=:mainact, `targetpreset`=:targetpreset, `budget`=:budget';
	mydb::query($stmt, ':calid', $post->calid, ':calowner', $post->calowner, ':mainact', $post->mainact, ':targetpreset', $post->targetpreset, ':budget',$post->budget);
	//$ret.=mydb()->_query.'<br />';

	// convert multiple upload file to each upload file
	$photos= array();
	if (is_string($_FILES['photo']['name'])) {
		$photos[]=$_FILES['photo'];
	} elseif (is_array($_FILES['photo']['name'])) {
		foreach ($_FILES['photo']['name'] as $key=>$name) {
			$photos[$key]['name']=$_FILES['photo']['name'][$key];
			$photos[$key]['type']=$_FILES['photo']['type'][$key];
			$photos[$key]['tmp_name']=$_FILES['photo']['tmp_name'][$key];
			$photos[$key]['error']=$_FILES['photo']['error'][$key];
			$photos[$key]['size']=$_FILES['photo']['size'][$key];
		}
	}
	$gallery=SG\getFirst(	mydb::select('SELECT `gallery` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid)->gallery,
										mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1);
	$is_upload_photo=false;
	foreach ($photos as $photo) {
		if (!is_uploaded_file($photo['tmp_name'])) continue;
		$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
		if (!$upload->valid_format()) continue;
		if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
			sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
		}
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
		$pics_desc['type'] = 'photo';
		$pics_desc['tpid'] = $post->tpid;
		$pics_desc['cid'] = 'func.NULL';
		$pics_desc['gallery'] = $gallery;
		$pics_desc['title']=$post->activityname;
		$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
		$pics_desc['file']=$photo_upload;
		$pics_desc['timestamp']='func.NOW()';
		$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

		$sql_cmd=db_create_insert_cmd('%topic_files%',$pics_desc);
		if ($upload->copy()) {
			db_querytable($sql_cmd);
			$is_upload_photo=true;
		}
	}
	if ($is_upload_photo) mydb::query('UPDATE %project_tr% SET gallery=:gallery WHERE `trid`=:trid LIMIT 1',':trid',$trid,':gallery',$gallery);
	//$ret.=print_o($post,'$post');
	return $ret;
}

/**
* Save upload photo
* @param Integer $trid
* @return String
*/
function __project_activity_savephoto($trid) {
	if (empty($trid)) return;
	// convert multiple upload file to each upload file
	$photos= array();
	if (is_string($_FILES['photo']['name'])) {
		$photos[]=$_FILES['photo'];
	} elseif (is_array($_FILES['photo']['name'])) {
		foreach ($_FILES['photo']['name'] as $key=>$name) {
			$photos[$key]['name']=$_FILES['photo']['name'][$key];
			$photos[$key]['type']=$_FILES['photo']['type'][$key];
			$photos[$key]['tmp_name']=$_FILES['photo']['tmp_name'][$key];
			$photos[$key]['error']=$_FILES['photo']['error'][$key];
			$photos[$key]['size']=$_FILES['photo']['size'][$key];
		}
	}
	$gallery=SG\getFirst(	mydb::select('SELECT `gallery` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$trid)->gallery,
										mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1);
	$is_upload_photo=false;
	foreach ($photos as $photo) {
		if (!is_uploaded_file($photo['tmp_name'])) continue;
		$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
		if (!$upload->valid_format()) continue;
		if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
			sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
		}
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
		$pics_desc['type'] = 'photo';
		$pics_desc['tpid'] = $post->tpid;
		$pics_desc['cid'] = 'func.NULL';
		$pics_desc['gallery'] = $gallery;
		$pics_desc['title']=$post->activityname;
		$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
		$pics_desc['file']=$photo_upload;
		$pics_desc['timestamp']='func.NOW()';
		$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

		$sql_cmd=db_create_insert_cmd('%topic_files%',$pics_desc);
		if ($upload->copy()) {
			db_querytable($sql_cmd);
			$is_upload_photo=true;
		}
	}
	if ($is_upload_photo) mydb::query('UPDATE %project_tr% SET gallery=:gallery WHERE `trid`=:trid LIMIT 1',':trid',$trid,':gallery',$gallery);
}

?>