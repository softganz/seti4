<?php
/**
 * Post/Update project activity
 *
 * @param Integer $tpid
 * @param Array $_POST["activity"]
 * @return String
 */
function project_form_activity($self,$topic,$para,$body,$part='owner') {
	if ($topic->project->project_status!='กำลังดำเนินโครงการ') return false;

	$action=post('act');
	$tpid=$topic->tpid;
	$trid=post('trid');
	$isAdmin=user_access('administer projects');
	$is_edit=user_access('administer projects') || (project_model::is_owner_of($tpid) && $part=="owner") || (project_model::is_trainer_of($tpid));
	if (!$is_edit) return false;

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
						LEFT JOIN %project_tr% tr ON tr.`calid`=c.`id`
						LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
						LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
					WHERE c.`id`=:calid AND c.`tpid`=:tpid LIMIT 1';
				$calRs=mydb::select($stmt,':calid',$calid,':tpid',$tpid);
				$part=$calRs->calowner==1?'owner':'trainer';

				if (!$calRs->trid) {	// Report not exists
					$ret.='Add transaction ';

					$post->calid=$calendar->id;
					$post->date1=$calendar->from_date;
					$post->date2=sg_date($calendar->from_date,'d/m/Y');
					$post->activityname=$calendar->title;
					$post->detail1=substr($calendar->from_time,0,5);
					$post->text1=$calendar->detail;
					$post->targetpreset=$calendar->targetpreset;
					$post->text3=$calendar->target;
					$post->budget=$calendar->budget;
					$post->mainact=$calendar->mainact;
					$post->presetOutputOutcome=$calendar->presetOutputOutcome;

					$calRs->from_time=substr($calRs->from_time,0,5);
					$calRs->uid=i()->uid;
					$calRs->created=date('U');
					$stmt='INSERT INTO %project_tr% (
						  `tpid`, `calid`, `parent`, `formid`, `part`, `flag`, `uid`
						, `date1`, `detail1`, `text1`, `created`
						) VALUES (
							:tpid, :calid, :mainactid, "activity", :part, 1, :uid
						, :from_date, :from_time, :goal_do, :created
						)';
					mydb::query($stmt,$calRs);
					$trid=mydb()->insert_id;
					$ret.='trid='.$trid;
					$ret.=mydb()->_query;
					$ret.=print_o($calRs,'$calRs');
				} else {	// Report exists
					$trid=$calRs->trid;
				}

			} else if (post('trid')) $trid=post('trid');

			// Get activity information and show form
			if ($trid) {
				$rs=__project_activity_get($trid);
				$ret.=__project_activity_form_activity($rs,$rs,'expense');
				$ret.=print_o($rs,'$rs');
			}
			$ret.='Add report '.$part.' trid='.$trid;
			return $ret;
			break;
		
		case 'updateaction' :
			$ret.='Update';
			$ret.=__project_activity_update();
			if (post('nextform')=='expense') {
				$post=post('activity');
				$trid=$post['trid'];
				location('paper/'.$tpid.'/owner/expense',array('trid'=>$trid));
			} else {
				$rs=__project_activity_get($_POST['activity']['trid']);
				$ret.=__project_activity_form_activity($rs,$rs);
			}
			$ret.=print_o(post(),'post()');
			return $ret;

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
			, tr.`trid`
			, tr.`flag`
			, COUNT(tr.`calid`) trtotal
			, tr.`num7` `exp_total`
		FROM %calendar% c
			LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
			LEFT JOIN %project_tr% tr ON tr.`calid`=c.`id` AND tr.`part` IN (:part)
		WHERE c.`tpid`=:tpid AND a.`calowner`=:calowner
		GROUP BY c.`id`
		ORDER BY '.$orders[$order].' ASC, `from_date` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid,':part',$part, ':calowner',$calowner, ':curdate',date('Y-m-d'));
	if ($dbs->_empty) $ret.='<p class="notify">ยังไม่มีการสร้างกิจกรรมในปฏิทินกิจกรรมของโครงการ กรุณา<a href="'.url('project/'.$tpid.'/info.calendar').'">คลิกที่ปฏิทินโครงการ</a> เพื่อเพิ่มกิจกรรมในปฏิทินของโครงการก่อน</p>';
	foreach ($dbs->items as $crs) {
		$isLate=$crs->to_date>$lockReportDate && $crs->trtotal<=0 && $crs->late>0;
		$ui=new ui();
		if ($crs->trtotal) $ui->add('<a href="'.url('paper/'.$crs->tpid.'/'.$part,array('act'=>'addreport','trid'=>$crs->trid)).'" title="แก้ไข'.($crs->flag==_PROJECT_DRAFTREPORT?'(ร่าง)':'').'บันทึกกิจกรรม">แก้ไข'.($crs->flag==0?'(ร่าง)':'').'บันทึกกิจกรรม</a>');
		$ui->add('<a href="'.url('paper/'.$tpid.'/owner/expense',array('trid'=>$trid)).'">ค่าใช้จ่าย</a>');
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
							'<a class="sg-action" href="'.(url('paper/'.$tpid.'/'.$part.'/activity',array('act'=>'addreport','calid'=>$crs->id))).'" data-confirm="กรุณายืนยัน?">บันทึกกิจกรรม</a>':
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

	$ret.='<style type="text/css">.project-activity-title th {white-space:nowrap;} .project-activity-title td:nth-child(4) {padding-right:10px;}</style>';
	return $ret;







if ($_REQUEST['calid']>0) {			// Get calendar and create add form
		$stmt='SELECT
				  c.*
				, a.`targetpreset`
				, a.`target`
				, a.`budget`
				, a.`mainact`
				, m.`detail1` `mainact_title`
				, m.`text3` `presetOutputOutcome`
				, m.`num2` `target`
				, m.`num3` `targetChild`
				, m.`num4` `targetTeen`
				, m.`num5` `targetWorker`
				, m.`num6` `targetElder`
				, m.`num7` `targetDisabled`
				, m.`num8` `targetWoman`
				, m.`num9` `targetMuslim`
				, m.`num10` `targetWorkman`
				, m.`num11` `targetOtherman`
			FROM %calendar% c
				LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
				LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
			WHERE c.id=:id LIMIT 1';
		$calendar=mydb::select($stmt,':id',$_REQUEST['calid']);
		$post->calid=$calendar->id;
		$post->date1=$calendar->from_date;
		$post->date2=sg_date($calendar->from_date,'d/m/Y');
		$post->activityname=$calendar->title;
		$post->detail1=substr($calendar->from_time,0,5);
		$post->text1=$calendar->detail;
		$post->targetpreset=$calendar->targetpreset;
		$post->text3=$calendar->target;
		$post->budget=$calendar->budget;
		$post->mainact=$calendar->mainact;
		$post->presetOutputOutcome=$calendar->presetOutputOutcome;
		//$ret.=print_o($calendar,'$calendar');
	}



	if ($post->date1<=$lockReportDate) {
		$ret.=message('error','กิจกรรมนี้อยู่ในช่วงที่ปิดงวดการเงินเรียบร้อยแล้ว ไม่สามารถแก้ไข/ลบบันทึกกิจกรรมนี้ได้');
		return $ret;
	}




	return $ret;
}

/**
* Get activity information
* @param Int $trid
* @return Record Set
*/
function __project_activity_get($trid) {
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
		WHERE tr.`trid`=:trid LIMIT 1';
	$rs=mydb::select($stmt,':trid',$trid);
	return $rs;
}

/**
* Show activity form
* @param Object $post
* @return String
*/
function __project_activity_form_activity($post,$rs,$nextform=NULL) {
	$form->config->variable='activity';
	$form->config->method='post';
	$form->config->enctype='multipart/form-data';
	$form->config->action=url(q());
	//$form->config->attr='autosave="'.url('project/edit/activityautosave/'.$tpid).'"';
	$form->title='<h3>ชื่อกิจกรรม : '.$post->activityname.'</h3>';
	if (cfg('project.usemainact')) $form->title.='<p><strong>กิจกรรมหลัก : '.$post->mainact_title.'</strong></p>';

	$form->action=array('name'=>'act', 'value'=>'updateaction', 'type'=>'hidden');
	$form->trid=array('type'=>'hidden', 'value'=>$post->trid);
	$form->tpid=array('type'=>'hidden', 'value'=>$post->tpid);
	$form->calid=array('type'=>'hidden', 'value'=>$post->calid);
	$form->part=array('type'=>'hidden', 'value'=>$post->part);
	if ($nextform) $form->nextform=array('type'=>'hidden','name'=>'nextform','value'=>$nextform);

	//$form->action_date=array('type'=>'hidden','value'=>htmlspecialchars(SG\getFirst($post->action_date,date('Y-m-d'))));

	$form->headerplan=array('type'=>'textfield','value'=>'<h4>รายละเอียดกิจกรรมตามแผนงานที่วางไว้</h4>');
	$form->headerdo=array('type'=>'textfield','value'=>'<h4>รายละเอียดกิจกรรมที่ทำจริง</h4>');

	$form->activityname->type='text';
	$form->activityname->label='ชื่อกิจกรรม';
	$form->activityname->require=true;
	$form->activityname->size=60;
	$form->activityname->placeholder='ระบุชื่อกิจกรรม';
	$form->activityname->value=htmlspecialchars($post->activityname);

	$form->headers1=array('type'=>'textfield','value'=>'<h4>แบบรายงานความก้าวหน้าโครงการ (ส. 1)</h4>');

	$form->action_date->type='text';
	$form->action_date->label='วันที่ปฎิบัติจริง';
	$form->action_date->class='sg-datepicker';
	$form->action_date->require=true;
	$form->action_date->value=htmlspecialchars(sg_date(SG\getFirst($post->action_date,date('Y-m-d')),'d/m/Y'));

	$form->action_time->type='text';
	$form->action_time->label='เวลา/ช่วงเวลาการจัดกิจกรรม';
	$form->action_time->size=10;
	$form->action_time->maxlength=30;
	$form->action_time->require=true;
	$form->action_time->value=htmlspecialchars($post->action_time);
	$form->action_time->placeholder='00:00';


	$form->goal_do->type='textarea';
	$form->goal_do->label='รายละเอียดกิจกรรมตามแผน';
	$form->goal_do->cols=15;
	$form->goal_do->rows=6;
	$form->goal_do->require=false;
	$form->goal_do->description=$form->goal_do->placeholder='ระบุลักษณะของกิจกรรมตามแผนที่วางไว้';
	$form->goal_do->value=$post->goal_do;

	$form->real_do->type='textarea';
	$form->real_do->label='รายละเอียดขั้นตอน กระบวนการ กิจกรรมที่ปฎิบัติจริง';
	$form->real_do->cols=15;
	$form->real_do->rows=6;
	$form->real_do->require=true;
	$form->real_do->description=$form->real_do->placeholder='รายละเอียดขั้นตอน กระบวนการ กิจกรรมที่ได้ปฎิบัติจริง';
	$form->real_do->value=$post->real_do;

	$presetTarget='';
	$tables = new Table();
	$tables->colgroup=array('','amt'=>'','center'=>'');
	$tables->rows[]=array('<th colspan="3">จำแนกตามช่วงวัย</th>');
	$tables->rows[]=array('เด็กเล็ก',number_format($rs->presetTargetChild),'คน');
	$tables->rows[]=array('เด็กวัยเรียน',number_format($rs->presetTargetTeen),'คน');
	$tables->rows[]=array('วัยทำงาน',number_format($rs->presetTargetWorker),'คน');
	$tables->rows[]=array('ผู้สูงอายุ',number_format($rs->presetTargetElder),'คน');
	$tables->rows[]=array('<th colspan="3">จำแนกกลุ่มเฉพาะ</th>');
	$tables->rows[]=array('คนพิการ',number_format($rs->presetTargetDisabled),'คน');
	$tables->rows[]=array('ผู้หญิง',number_format($rs->presetTargetWoman),'คน');
	$tables->rows[]=array('มุสลิม',number_format($rs->presetTargetMuslim),'คน');
	$tables->rows[]=array('แรงงาน',number_format($rs->presetTargetWorkman),'คน');
	$tables->rows[]=array($calendar->targetOtherDesc?$rs->targetOtherDesc:'อื่น ๆ',number_format($rs->presetTargetOtherman),'คน');
	$tables->rows[]=array('<strong>รวมทั้งสิ้น</strong>','<strong>'.$post->targetpreset.'</strong>','<strong>คน</strong>');
	$presetTarget.=$tables->build();

	//$presetTarget.=print_o($post,'$post');

	$form->presetTarget->type='textfield';
	$form->presetTarget->label='กลุ่มเป้าหมายที่วางแผนไว้';
	$form->presetTarget->value=$presetTarget;

	$realTarget='';
	$tables = new Table();
	$tables->colgroup=array('','amt'=>'','center'=>'');
	$tables->rows[]=array('<th colspan="3">จำแนกตามช่วงวัย</th>');
	$tables->rows[]=array('เด็กเล็ก','<input type="text" class="form-number" name="activity[targetChild]" id="edit-activity-targetChild" value="'.number_format($post->targetChild,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('เด็กวัยเรียน','<input type="text" class="form-number" name="activity[targetTeen]" id="edit-activity-targetTeen" value="'.number_format($post->targetTeen,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('วัยทำงาน','<input type="text" class="form-number" name="activity[targetWorker]" id="edit-activity-targetWorker" value="'.number_format($post->targetWorker,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('ผู้สูงอายุ','<input type="text" class="form-number" name="activity[targetElder]" id="edit-activity-targetElder" value="'.number_format($post->targetElder,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('<th colspan="3">จำแนกกลุ่มเฉพาะ</th>');
	$tables->rows[]=array('คนพิการ','<input type="text" class="form-number" name="activity[targetDisabled]" id="edit-activity-targetDisabled" value="'.number_format($post->targetDisabled,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('ผู้หญิง','<input type="text" class="form-number" name="activity[targetWoman]" id="edit-activity-targetWoman" value="'.number_format($post->targetWoman,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('มุสลิม','<input type="text" class="form-number" name="activity[targetMuslim]" id="edit-activity-targetMuslim" value="'.number_format($post->targetMuslim,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array('แรงงาน','<input type="text" class="form-number" name="activity[targetWorkman]" id="edit-activity-targetWorkman" value="'.number_format($post->targetWorkman,0,'.','').'" placeholder="0" />','คน');
	$tables->rows[]=array($calendar->targetOtherDesc?$calendar->targetOtherDesc:'อื่น ๆ','<input type="text" class="form-number" name="activity[targetOtherman]" id="edit-activity-targetOtherman" value="'.number_format($post->targetOtherman,0,'.','').'" placeholder="0" />','คน');

	$tables->rows[]=array($calendar->targetOtherDesc?$calendar->targetOtherDesc:'<strong>รวมทั้งสิ้น</strong>','<input type="text" class="form-number" name="activity[num8]" id="edit-activity-num8" value="'.number_format($post->num8,0,'.','').'" placeholder="0" />','<strong>คน</strong>');

	$realTarget.=$tables->build();

	$form->realTarget->type='textfield';
	$form->realTarget->label='กลุ่มเป้าหมายที่เข้าร่วมจริง';
	$form->realTarget->value=$realTarget;





	$form->presetOutputOutcome->type='textfield';
	$form->presetOutputOutcome->label='ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่ตั้งไว้';
	$form->presetOutputOutcome->cols=15;
	$form->presetOutputOutcome->rows=6;
	$form->presetOutputOutcome->readonly=true;
	//$form->presetOutputOutcome->description=$form->presetOutputOutcome->placeholder='ระบุ ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่ตั้งไว้';
	$form->presetOutputOutcome->value=$post->presetOutputOutcome;


	$form->real_work->type='textarea';
	$form->real_work->label='ผลผลิต (Output) / ผลลัพธ์ (Outcome) ที่เกิดขึ้นจริง';
	$form->real_work->cols=15;
	$form->real_work->rows=15;
	$form->real_work->require=true;
	$form->real_work->description=$form->real_work->placeholder='กรุณาระบุเนื้อหา/ข้อสรุปสำคัญต่าง ๆ จากกิจกรรม ที่สามารถนำมาขยายผลต่อได้ เช่น ความรู้ กลุ่มแกนนำ แผนงานต่าง ๆ และผลที่ได้จากกิจกรรม อาทิ พฤติกรรม หรือสิ่งที่เกิดขึ้นภายหลังกิจกรรม เช่น การรวมกลุ่มทำกิจกรรมต่อเนื่อง (ซึ่งจะทราบได้จากการติดตามประเมินผลของโครงการ)';
	$form->real_work->value=$post->real_work;





	$form->problem->type='textarea';
	$form->problem->label='ปัญหา/แนวทางแก้ไข';
	$form->problem->cols=15;
	$form->problem->rows=5;
	$form->problem->placeholder='ระบุปัญหา และ แนวทางการพัฒนาครั้งต่อไป';
	$form->problem->value=$post->problem;


	/*
	$form->rate1->type='radio';
	$form->rate1->label='ประเมินผล คุณภาพกิจกรรม';
	$form->rate1->options=array('4'=>'4=บรรลุผลมากกว่าเป้าหมาย', '3'=>'3=บรรลุผลตามเป้าหมาย', '2'=>'2=เกือบได้ตามเป้าหมาย', '1'=>'1=ได้น้อยกว่าเป้าหมายมาก','-1'=>'0=ไม่สามารถประเมินได้');
	$form->rate1->value=$post->rate1;
	$form->rate1->require=true;
	*/

	$form->hp4='<h4>ประเมินผลคุณภาพกิจกรรม ตามตัวชี้วัดการประเมิน</h4>';

	$indicatorOptions=array(5=>5,4=>4,3=>3,2=>2,1=>1,-1=>'ไม่สามารถประเมินได้');
	$form->indicator01->type='radio';
	$form->indicator01->label='1. การมีส่วนร่วม :';
	$form->indicator01->display='inline';
	$form->indicator01->pretext='คะแนน';
	$form->indicator01->options=$indicatorOptions;
	$form->indicator01->value=SG\getFirst($post->indicator01,0);
	$form->indicator01->description='<ul><li>เกิดกลไกขับเคลื่อนในพื้นที่ เช่น สภาผู้นำ/กลุ่ม/เครือข่าย</li></ul>';

	$form->indicator02->type='radio';
	$form->indicator02->label='2. ผู้นำ/แกนชุมชน :';
	$form->indicator02->display='inline';
	$form->indicator02->pretext='คะแนน';
	$form->indicator02->options=$indicatorOptions;
	$form->indicator02->value=$post->indicator02;
	$form->indicator02->description='<ul><li>เกิดกลไกขับเคลื่อนในพื้นที่ เช่น สภาผู้นำ/กลุ่ม/เครือข่าย</li></ul>';

	$form->indicator03->type='radio';
	$form->indicator03->label='3. โครงสร้างองค์กร :';
	$form->indicator03->display='inline';
	$form->indicator03->pretext='คะแนน';
	$form->indicator03->options=$indicatorOptions;
	$form->indicator03->value=$post->indicator03;
	$form->indicator03->description='<ul><li>เกิดกลไกขับเคลื่อนในพื้นที่ เป็นส่วนสำคัญในโครงสร้างชุมชน เช่น กรรมการหมู่บ้าน กรรมการชุมชน เป็นต้น</li></ul>';

	$form->indicator04->type='radio';
	$form->indicator04->label='4. การประเมินปัญหา :';
	$form->indicator04->display='inline';
	$form->indicator04->pretext='คะแนน';
	$form->indicator04->options=$indicatorOptions;
	$form->indicator04->value=$post->indicator04;
	$form->indicator04->description='<ul><li>มีฐานข้อมูลชุมชน (ปัญหาของชุมชน , ปัญหาเฉพาะประเด็น)</li></ul>';

	$form->indicator05->type='radio';
	$form->indicator05->label='5. การถามว่าทำไม :';
	$form->indicator05->display='inline';
	$form->indicator05->pretext='คะแนน';
	$form->indicator05->options=$indicatorOptions;
	$form->indicator05->value=$post->indicator05;
	$form->indicator05->description='<ul><li>มีฐานข้อมูลชุมชน (ปัญหาของชุมชน , ปัญหาเฉพาะประเด็น)</li></ul>';

	$form->indicator06->type='radio';
	$form->indicator06->label='6. การระดมทรัพยากร :';
	$form->indicator06->display='inline';
	$form->indicator06->pretext='คะแนน';
	$form->indicator06->options=$indicatorOptions;
	$form->indicator06->value=SG\getFirst($post->indicator06,0);
	$form->indicator06->description='<ul><li>การระดมทรัพยากรและการเชื่อมโยงภายนอก มีการบรรจุอยู่ใน แผนชุมชน แผน อบต./เทศบาล แผนของหน่วยงาน</li></ul>';

	$form->indicator07->type='radio';
	$form->indicator07->label='7. การเชื่อมโยงภายนอก :';
	$form->indicator07->display='inline';
	$form->indicator07->pretext='คะแนน';
	$form->indicator07->options=$indicatorOptions;
	$form->indicator07->value=$post->indicator07;
	$form->indicator07->description='<ul><li>การระดมทรัพยากรและการเชื่อมโยงภายนอก มีการบรรจุอยู่ใน แผนชุมชน แผน อบต./เทศบาล แผนของหน่วยงาน</li></ul>';

	$form->indicator08->type='radio';
	$form->indicator08->label='8. บทบาทตัวแทน :';
	$form->indicator08->display='inline';
	$form->indicator08->pretext='คะแนน';
	$form->indicator08->options=$indicatorOptions;
	$form->indicator08->value=$post->indicator08;
	$form->indicator08->description='<ul><li>ผู้รับผิดชอบโครงการเข้าไปมีส่วนร่วมในกลุ่ม / เครือข่าย หรือ หน่วยงานทั้งภายในและภายนอกชุมชน</li><li>ผู้รับผิดชอบโครงการยกระดับเป็นพี่เลี้ยง</li></ul>';

	$form->indicator09->type='radio';
	$form->indicator09->label='9. การบริหารจัดการ :';
	$form->indicator09->display='inline';
	$form->indicator09->pretext='คะแนน';
	$form->indicator09->options=$indicatorOptions;
	$form->indicator09->value=$post->indicator09;
	$form->indicator09->description='<ul><li>การใช้ระบบติดตามประเมินผลบนเว็บไซต์ (รายงาน, การเงิน)</li></ul>';

	$form->indicatorDetail->type='textarea';
	$form->indicatorDetail->label='รายละเอียดการประเมิน';
	$form->indicatorDetail->rows=3;
	$form->indicatorDetail->value=$post->indicatorDetail;
	$form->indicatorDetail->description=$form->indicatorDetail->placeholder='อธิบายรายละเอียดของการประเมินผลการทำกิจกรรม';


	if ($part=='owner') {
		$form->header_budget->type='textfield';
		$form->header_budget->value='<h4>รายงานการใช้เงิน</h4>';

		$form->budget->type='text';
		$form->budget->label='งบประมาณที่ตั้งไว้ (บาท)';
		$form->budget->maxlength=12;
		$form->budget->value=number_format(htmlspecialchars($post->budget),2);

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
				'<input size="10"  name="activity[num1]" id="edit-activity-num1" class="form-text require" type="text" value="'.htmlspecialchars($post->num1).'" />',
				'<input size="10"  name="activity[num2]" id="edit-activity-num2" class="form-text require" type="text" value="'.htmlspecialchars($post->num2).'" />',
				'<input size="10"  name="activity[num3]" id="edit-activity-num3" class="form-text require" type="text" value="'.htmlspecialchars($post->num3).'" />',
				'<input size="10"  name="activity[num4]" id="edit-activity-num4" class="form-text require" type="text" value="'.htmlspecialchars($post->num4).'" />',
				'<input size="10"  name="activity[num5]" id="edit-activity-num5" class="form-text require" type="text" value="'.htmlspecialchars($post->num5).'" />',
				'<input size="10"  name="activity[num6]" id="edit-activity-num6" class="form-text require" type="text" value="'.htmlspecialchars($post->num6).'" />',
				'<input size="10"  name="activity[num7]" id="edit-activity-num7" class="form-text require" type="text" value="'.htmlspecialchars($post->num7).'" />',
			);
		}
		$form->set2 = $tables->build();
		//			$form->set2.=print_o($post,'$post');
	} else {
		$form->set2='<input type="hidden"  name="activity[num1]" id="edit-activity-num1" value="0" /><input type="hidden"  name="activity[num2]" id="edit-activity-num2" value="0" /><input type="hidden"  name="activity[num3]" id="edit-activity-num3" value="0" /><input type="hidden"  name="activity[num4]" id="edit-activity-num4" value="0" /><input type="hidden"  name="activity[num5]" id="edit-activity-num5" value="0" /><input type="hidden"  name="activity[num6]" id="edit-activity-num6" value="0" /><input type="hidden"  name="activity[num7]" id="edit-activity-num7" value="0" />';
	}


	$form->submit->type='submit';
	$form->submit->items->save=tr('Save');

	$form->photoremark='<strong>หมายเหตุ : ภาพถ่ายประกอบกิจกรรมหรือไฟล์รายงานรายละเอียดประกอบกิจกรรม สามารถส่งเพิ่มเติมได้หลังจากบันทึกข้อมูลเสร็จเรียบร้อยแล้ว</strong>';

	$ret .= theme('form','activity-add',$form);

	$ret.='<script type="text/javascript">
$(document).ready(function() {
	var errorCount=0;
	var isChange=false;
	var $form=$("form#activity-add");
	var autoSave='.SG\getFirst($_REQUEST['r'],cfg('project.activity.autosave')).';
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

	$("#edit-activity-date2")
	.datepicker({
		clickInput:true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "#edit-activity-date1",
		disabled: false,
		monthNames: thaiMonthName,
	});

	/* ปิด autocomplete ไว้ก่อนชั่วคราว จำไม่ได้ว่าทำไมถึงเปิดให้เลือกตรงนี้ */
	/*
	$("#edit-activity-activityname")
	.autocomplete({
		source: function(request, response){
			$.get(url+"project/get/activity/"+tpid+"?n=50&q="+encodeURIComponent(request.term), function(data){
				response($.map(data, function(item){
				return {
					label: item.label,
					value: item.value,
					date: item.date,
					detail: item.detail
				}
				}))
			}, "json");
		},
		minLength: 2,
		dataType: "json",
		cache: false,
		select: function(event, ui) {
			this.value = ui.item.label;
			// Do something with id
			$("#edit-activity-calid").val(ui.item.value);
			$("#edit-activity-text1").val(ui.item.detail);
			return false;
		}
	})
	.data( "autocomplete" )._renderItem = function( ul, item ) {
		return $( "<li></li>" )
			.data( "item.autocomplete", item )
			.append( "<a>" + item.label + "<p>" + item.date +" : "+item.detail+ "</p></a>" )
			.appendTo( ul );
	};
	*/

	$("#edit-activity-num7").attr("readonly", true).css({"font-weight": "bold"});
	/*	$("#edit-activity-num7").attr("disabled", "disabled").css({"background-color": "#eeeeee", "font-weight": "bold"}); */
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

	/*
	$("input[name=\'cancel\']").click(function() {
		if ($("#edit-activity-trid").val()!="") {
			alert("กำลังยกเลิกรายการ"+$form.attr("autosave")+"/remove");
			$.post($form.attr("autosave")+"/remove",$form.serialize(true), function(data) {
				notify(data.msg,2000);
				isChange=false;
	//				window.location=$("#activity-add").attr("action");
			},"json");
		} else {
	//			window.location=$("#activity-add").attr("action");
		}
	});
	*/

	$form.submit(function() {
		var error=false;
		var $obj;
		var fld;
		var fldCheck=[
			["edit-activity-date1","วันที่"],
			["edit-activity-activityname","กิจกรรม"],
			["edit-activity-detail3","วัตถุประสงค์ย่อย"],
			/* ["edit-activity-text1","รายละเอียดกิจกรรมตามแผน"], */
			["edit-activity-text3","รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้ตามแผนงาน"],
			["edit-activity-text9","รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม"],
			["edit-activity-text2","รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง"],
			["edit-activity-text4","ผลสรุปที่สำคัญของกิจกรรม"],
			/* ["edit-activity-text5","ปัญหา/แนวทางแก้ไข"], */
			/* ["edit-activity-text6","ข้อเสนอแนะต่อ '.cfg('project.grantby').'"],
			["edit-activity-text7","ความต้องการสนับสนุนจากพี่เลี้ยงและ '.cfg('project.grantpass').'"],*/
			/* ["activity[rate1]","ประเมินผล คุณภาพกิจกรรม"], */
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
	//					notify(data.debug);
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
			$.post($form.attr("autosave"),$form.serialize(true), function(data) {
				if (data.trid) $("#edit-activity-trid").val(data.trid);
				if (data.error) notify("Error : "+data.error);
				formSubmit=data.error?false:true;
				if (formSubmit) $form.submit();
	//					notify(data.debug);
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
	return $ret;
}

function __project_activity_update($trid) {
	$post=(object)post('activity',_TRIM_);
	$ret.=print_o($post,'$post');
	$error=false;

	if (empty($post->trid)) return $ret;

	// Save add/edit activity
	if ($part=='trainer' && empty($post->calid)) $error='กรุณาเลือกกิจกรรมจากรายชื่อกิจกรรมเท่านั้น';

	if (!$error) {
		$post->trid=empty($post->trid)?'func.NULL':$post->trid;
		$post->formid='activity';
		$post->uid=i()->ok?i()->uid:'func.NULL';
		if (empty($post->rate1)) $post->rate1=0;
		else if ($post->rate1==-1) $post->rate1=NULL;
		$post->created=date('U');
		$post->action_date=sg_date($post->action_date,'Y-m-d');
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

		if (!isset($post->indicator01) || $post->indicator01<1) $post->indicator01=NULL;
		if (!isset($post->indicator02) || $post->indicator02<1) $post->indicator02=NULL;
		if (!isset($post->indicator03) || $post->indicator03<1) $post->indicator03=NULL;
		if (!isset($post->indicator04) || $post->indicator04<1) $post->indicator04=NULL;
		if (!isset($post->indicator05) || $post->indicator05<1) $post->indicator05=NULL;
		if (!isset($post->indicator06) || $post->indicator06<1) $post->indicator06=NULL;
		if (!isset($post->indicator07) || $post->indicator07<1) $post->indicator07=NULL;
		if (!isset($post->indicator08) || $post->indicator08<1) $post->indicator08=NULL;
		if (!isset($post->indicator09) || $post->indicator09<1) $post->indicator09=NULL;

		if (!isset($post->indicatorDetail)) $post->indicatorDetail='';

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

		if ($post->activityname) {
			$stmt='UPDATE %calendar% SET `title`=:activityname WHERE `id`=:calid LIMIT 1';
			mydb::query($stmt,':calid',$post->calid,':activityname',$post->activityname);
		}
		$stmt='UPDATE %project_tr% SET
			  `flag`=:flag
			, `date1`=:action_date
			, `detail1`=:action_time
			, `text1`=:goal_do
			, `text2`=:real_do
			, `text4`=:real_work
			, `text5`=:problem
			, `indicator01`=:indicator01
			, `indicator02`=:indicator02
			, `indicator03`=:indicator03
			, `indicator04`=:indicator04
			, `indicator05`=:indicator05
			, `indicator06`=:indicator06
			, `indicator07`=:indicator07
			, `indicator08`=:indicator08
			, `indicator09`=:indicator09
			, `indicatorDetail`=:indicatorDetail
			, `targetChild`=:targetChild
			, `targetTeen`=:targetTeen
			, `targetWorker`=:targetWorker
			, `targetElder`=:targetElder
			, `targetDisabled`=:targetDisabled
			, `targetWoman`=:targetWoman
			, `targetMuslim`=:targetMuslim
			, `targetWorkman`=:targetWorkman
			, `targetOtherman`=:targetOtherman
			, `num8`=:num8
			, `modified`=:modified
			, modifyby=:modifyby
			WHERE `trid`=:trid LIMIT 1;';
		mydb::query($stmt,$post);
		$ret.=mydb()->_query;

		$trid=$post->trid=='func.NULL'?mydb()->insert_id:$post->trid;

		$post->calowner=$part=='owner'?_PROJECT_OWNER_ACTIVITY:_PROJECT_TRAINER_ACTIVITY;

		$stmt='INSERT INTO %project_activity% (`calid`, `calowner`, `mainact`, `targetpreset`, `budget`) VALUES (:calid, :calowner, :mainact, :targetpreset, :budget)
						ON  DUPLICATE KEY UPDATE `mainact`=:mainact, `targetpreset`=:targetpreset, `budget`=:budget';
		mydb::query($stmt, ':calid', $post->calid, ':calowner', $post->calowner, ':mainact', $post->mainact, ':targetpreset', $post->targetpreset, ':budget',$post->budget);


		// $ret.=print_o($_POST,'$_POST');
		// location(q());
	}
	return $error.$ret;
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