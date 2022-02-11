<?php
/**
* Project :: Progresses Report List
* Created 2022-01-05
* Modify  2022-01-05
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.progresses
*/

import('model:project.follow.php');
import('widget:project.follow.nav.php');

class ProjectInfoProgress extends Page {
	var $projectId;
	var $period;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $period = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->period = $period;
		$this->right = (Object) [
			'editable' => $projectInfo->info->isRight,
			'edit' => $projectInfo->info->isRight,
		];
	}

	function build() {
		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$projectInfo = $this->projectInfo;
		$periodInfo = ProjectFollowModel::getProgressReport($this->projectId, $this->period);

		if (!$periodInfo) return '<p>โครงการนี้ยังไม่มีการการกำหนดงวด <a href="'.url('project/'.$this->projectId).'">กรุณากำหนดงวด</a> ของโครงการใน<a href="'.url('project/'.$this->projectId).'">รายละเอียดโครงการ</a>ก่อน !!!</p>';

		// debugMsg($periodInfo, '$periodInfo');

		if (!$projectInfo) return message('error', 'No Project');

		$formid = 'ส.1';
		$showBudget = $projectInfo->is->showBudget;

		$isOwner = project_model::is_owner_of($this->projectId);
		$isTrainer = project_model::is_trainer_of($this->projectId);
		$isAdmin = user_access('administer projects');
		$isTeam = $isAdmin || $isOwner || $isTrainer;
		$isEdit = $projectInfo->info->project_statuscode==1 && $isTeam;
		$isAccessActivityExpense = user_access('access activity expense') || $isOwner;

		$ret = '';
		//$ret .= 'Period = '.$this->period.' , Action = '.$action.' , TranId = '.$tranId;

		$reportInfo=project_model::get_tr($this->projectId,$formid,$this->period);

		if ($reportInfo->_empty) return message('error', 'ยังไม่มีการสร้างรายงาน');

		$periodInfo=project_model::get_period($this->projectId,$this->period);
		$activities=R::Model('project.activity.get.bytpid',$this->projectId,
									'{owner:'._PROJECT_OWNER_ACTIVITY.', period:'.$this->period.'}'
									);

		$locked=$periodInfo->flag>=_PROJECT_LOCKREPORT;
		if ($locked) $isEdit=false;

		//$ret.=print_o($reportInfo,'$reportInfo');
		//$ret.=print_o($periodInfo,'$periodInfo');
		//$ret.=print_o($activities,'$activities');

		$url='project/'.$this->projectId.'/operate.result/'.$this->period;

		// Show form toolbar
		$ui = new Ui();
		/*
		$ui->add('<a href="'.url($url).'">รายงาน งวดที่ '.$this->period.'</a>');
		if ($order=='mainact') {
			$ui->add('<a href="'.url($url,array('order'=>'date')).'">เรียงกิจกรรมตามวันที่</a>');
		} else {
			$ui->add('<a href="'.url($url).'">เรียงกิจกรรมตามกิจกรรมหลัก</a>');
		}
		*/
		$ui->add('<a class="btn -link" href="javascript:void(0)">สถานะรายงาน : <i class="icon -'.($locked?'lock':'unlock').' -gray"></i><span>'.($locked?'Locked':'Unlock').'</span></a>');
		//$ui->add('<a href="'.url('paper/'.$this->projectId.'/owner/m1/period/'.$this->period,array('o'=>'word','a'=>'download')).'">ดาวน์โหลด</a>');
		if ($isEdit) {
			$subui=new ui();
			$subui->add('<a class="sg-action" href="'.url('project/info/api/'.$this->projectId.'/progress.delete/'.$this->period).'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId.'/info.progresses').'" data-title="ลบรายงาน" data-confirm="ยืนยันการลบรายงาน?"><i class="icon -delete"></i><span>ลบรายงาน</span></a>');
			$ui->add(sg_dropbox($subui->build('ul'),'{class: "leftside -atright"}'));
		}

		$ret.='<nav class="nav -page -no-print">'.$ui->build().'</nav>';


		if ($isEdit) {
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-period'] = $this->period;
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$ret.='<div id="project-report-s1" class="inline-edit project__report project__report--s1" '.sg_implode_attr($inlineAttr).'>'._NL;

		$section='title';
		$irs=end($reportInfo->items[$section]);

		/*
		$ret.='<p class="form-info">รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong><br />สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong><br />งวดที่ <strong>'.$irs->period.'</strong></p>'._NL;
		*/
		$ret.='<h2>แบบรายงานผลการดำเนินโครงการประจำงวด '.$this->period.'</h2>'._NL;

		$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL;
		if ($projectInfo->info->area)
			$ret.='<p>ชุมชน <strong>'.$projectInfo->info->area.'</strong></p>'._NL;
		$ret.='<p>รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> เลขที่ข้อตกลง <strong>'.$projectInfo->info->agrno.'</strong></p>'._NL;
		$ret.='<p>ระยะเวลาดำเนินงาน ตั้งแต่ <strong>'.sg_date($projectInfo->info->date_from,'ว ดดด ปปปป').' ถึง '.sg_date($projectInfo->info->date_end,'ว ดดด ปปปป').'</strong></p>'._NL;


		$ret.='<p>รายงานงวดที่ : <strong>'.$this->period.'</strong> จากเดือน <strong>'.sg_date(SG\getFirst($periodInfo->report_from_date,$periodInfo->from_date),'ดดด ปปปป').'</strong> ถึงเดือน <strong>'.sg_date(SG\getFirst($periodInfo->report_to_date,$periodInfo->to_date),'ดดด ปปปป').'</strong></p>'._NL;

		$ret.='<h3>ส่วนที่ 1 ผลการดำเนินโครงการ <span>(แสดงผลการดำเนินงานรายกิจกรรมที่แสดงผลผลิตและผลลัพธ์ที่เกิดขึ้นจริง</span></h3>'._NL;

		// Get activity from project_tr that between report date

		if ($order=='mainact') {
			$mainact=project_model::get_main_activity($this->projectId, 'owner', $this->period);

			$tables = new Table();
			$tables->addClass('project-report-s1-item');
			$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
			$activityHead='<tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr>';

			$mno=0;
			foreach ($mainact->info as $mrs) {
				//$ret.='<h3>'.$mrs->title.'</h3>';
				$tables->rows[]=array('<td colspan="3"><h3 style="text-align:left;">'.(++$mno).'. '.$mrs->title.'</h3></td>');
				$no=0;
				if (empty($mainact->activity[$mrs->trid])) {
					$tables->rows[]=array('<td colspan="3">ไม่มีกิจกรรม</td>');
					continue;
				}
				$no=0;

				foreach ($mainact->activity[$mrs->trid] as $key => $activity) {
					$tables->rows[]=$activityHead;
					unset($row);
					$summary='';

					$tables->rows[]=array('<td colspan="3"><h4 style="text-align:left;">'.$mno.'.'.(++$no).' '.$activity->title.'</h4></td>');

					$summary.='<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.' น.</strong>';
					$summary.='<h5>วัตถุประสงค์ที่ตั้งไว้</h5>';
					$summary.=sg_text2html($activity->objective);

					$summary.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($activity->presetOutputOutcome)._NL;
					$summary.='<h5>ผลลัพธ์ที่เกิดขึ้นจริง</h5>'.sg_text2html($activity->real_work)._NL;

					$summary.='<h5>กิจกรรมที่กำหนดไว้ในแผน</h5>'._NL.sg_text2html($activity->goal_do)._NL;
					$summary.='<h5>กิจกรรมที่ทำจริง</h5>'._NL.sg_text2html($activity->real_do)._NL;

					if ($activity->gallery) {
						if (debug('method')) $summary.=$activity->photos.'<br />'.print_o($activity,'$activity');
						$summary.='<div class="photo">'._NL;
						$summary.='<ul>'._NL;
						foreach (explode(',',$activity->photos) as $item) {
							list($photoid,$photo)=explode('|',$item);
							if (substr($photo,0,12)=='project_rcv_' && !$isAccessExpense) continue;
							$photo=model::get_photo_property($photo);
							$photo_alt=$item->title;
							$summary .= '<li>';
							$summary.='<img height="80" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
							$summary.=' />';
							$summary.=$item->title;
							$summary .= '</li>'._NL;
						}
						$summary.='</ul>'._NL;
						$summary.='</div><!--photo-->'._NL;
					}
					$summary.='<p>&nbsp;</p>';

					$row[]=$summary;
					$row[]=number_format($activity->targetpreset);
					$row[]=number_format($activity->targetjoin);
					$tables->rows[]=$row;
				}
			}

			$ret .= $tables->build();

		} else {
			$tables = new Table('project-report-s1-item');
			$tables->colgroup=array('summary'=>'width="80%"','amt output1'=>'width="10%"','amt output2'=>'width="10%"');
			$tables->thead='<thead><tr><th rowspan="2">วัตถุประสงค์ที่ตั้งไว้<br />ผลลัพธ์และตัวชี้วัดผลลัพธ์<sup>**</sup><br />กิจกรรมของโครงการ</th><th colspan="2">ผลผลิต<sup>*</sup></th></tr><tr><th>ผลผลิตที่ตั้งไว้</th><th>ผลผลิตที่เกิดขึ้นจริง</th></tr></thead>';

			$no=0;
			foreach ($activities->items as $activity) {
				unset($row);
				$summary='';

				$tables->rows[]=array('<td colspan="3"><h3 style="text-align:left;">'.(++$no).'. '.$activity->title.'</h3></td>');

				$summary.='<strong>วันที่ '.sg_date($activity->action_date,'ว ดดด ปปปป').' เวลา '.$activity->action_time.' น.</strong>';
				$summary.='<h4>วัตถุประสงค์ที่ตั้งไว้</h4>';
				$summary.=sg_text2html($activity->objective);

				$summary.='<h4>ผลลัพธ์ที่ตั้งไว้</h4>'.sg_text2html($activity->presetOutputOutcome)._NL;
				$summary.='<h4>ผลลัพธ์ที่เกิดขึ้นจริง</h4>'.sg_text2html($activity->real_work)._NL;

				$summary.='<h4>กิจกรรมที่กำหนดไว้ในแผน</h4>'._NL.sg_text2html($activity->goal_do)._NL;
				$summary.='<h4>กิจกรรมที่ทำจริง</h4>'._NL.sg_text2html($activity->real_do)._NL;

				if ($activity->gallery) {
					if (debug('method')) $summary.=$activity->photos.'<br />'.print_o($activity,'$activity');
					$summary.='<div class="photo">'._NL;
					$summary.='<ul>'._NL;
					foreach (explode(',',$activity->photos) as $item) {
						list($photoid,$photo)=explode('|',$item);
						if (substr($photo,0,12)=='project_rcv_' && !$isAccessExpense) continue;
						$photo=model::get_photo_property($photo);
						$photo_alt=$item->title;
						$summary .= '<li>';
						$summary.='<img height="80" class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
						$summary.=' />';
						$summary.=$item->title;
						$summary .= '</li>'._NL;
					}
					$summary.='</ul>'._NL;
					$summary.='</div><!--photo-->'._NL;
				}
				$summary.='<p>&nbsp;</p>';

				$row[]=$summary;
				$row[]=number_format($activity->targetpreset);
				$row[]=number_format($activity->targetjoin);
				$tables->rows[]=$row;
			}
			$ret .= $tables->build();
		}

		$ret.='<p>* ผลผลิต หมายถึง ผลที่เกิดขึ้นเชิงปริมาณจากการทำกิจกรรม เช่น จำนวนผู้เข้าร่วมประชุม จำนวนผู้ผ่านการอบรม จำนวนครัวเรือนที่ปลูกผักสวนครัว เป็นต้น<br />** ผลลัพธ์ หมายถึง การเปลี่ยนแปลงที่นำไปสู่การแก้ปัญหา เช่น หลังอบรมมีผู้ปรับเปลี่ยนพฤติกรรมจำนวนกี่คน มีข้อบังคับหรือมาตรการของชุมชนที่นำไปสู่การปรับเปลี่ยนพฤติกรรมหรือสภาพแวดล้อม เป็นต้น ทั้งนี้ต้องมีข้อมูลอ้างอิงประกอบการรายงาน เช่น ข้อมูลรายชื่อแกนนำ , แบบสรุปการประเมินความรู้ , รูปภาพกิจกรรมพร้อมคำอธิบายใต้ภาพ เป็นต้น</p>'._NL;







		$ret.='<h3>ส่วนที่ 2 ประเมินความก้าวหน้าของการดำเนินงานโครงการและปัญหา/อุปสรรคในการดำเนินโครงการ</h3>';
		$ret.='<h4>ประเมินความก้าวหน้าของการดำเนินงานโครงการ</h4>';

		$totalCalendar=mydb::select('SELECT COUNT(*) total FROM %calendar% c LEFT JOIN %project_activity% a ON a.`calid`=c.`id` WHERE `tpid`=:tpid AND `calowner`=1 LIMIT 1',':tpid',$this->projectId,':reportdate',$periodInfo->report_to_date)->total;
		$totalActivity=mydb::select('SELECT COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$this->projectId,':reportdate',$periodInfo->report_to_date)->total;

		$activityPercent=round($totalActivity*100/$totalCalendar);


		$totalBudget=$projectInfo->info->budget;
		$totalSpend=mydb::select('SELECT SUM(`num7`) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$this->projectId,':reportdate',$periodInfo->report_to_date)->total;
		$spendPercent=round($totalSpend*100/$totalBudget);

		$rateRs = mydb::select('SELECT SUM(`rate1`) rate,4*COUNT(*) total FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" AND `date1`<=:reportdate LIMIT 1',':tpid',$this->projectId,':reportdate',$periodInfo->report_to_date);
		$totalPoint = $rateRs->rate;
		$totalRate = $rateRs->total;
		$ratePercent = $totalRate ? round($totalPoint*100/$totalRate) : 0;

		$tables = new Table();
		$tables->addClass('project__report--percent');
		$tables->thead=array('การดำเนินงานเมื่อเทียบกับการดำเนินงานทั้งโครงการ','ทั้งหมด','ทำแล้ว','10%','20%','30%','40%','50%','60%','70%','80%','90%','100%');
		$row=array();
		$row[]='การทำกิจกรรม';
		$row[]=$totalCalendar;
		$row[]=$totalActivity;
		for ($i=0;$i<100;$i=$i+10) $row[]=$activityPercent>$i && $activityPercent<=$i+10?'<span title="'.$activityPercent.'%">✔</span>':'&nbsp;';
		$tables->rows[]=$row;

		if ($showBudget) {
			$row=array();
			$row[]='การใช้จ่ายงบประมาณ';
			$row[]=number_format($totalBudget,2);
			$row[]=number_format($totalSpend,2);
			for ($i=0;$i<100;$i=$i+10) $row[]=$spendPercent>$i && $spendPercent<=$i+10?'<span title="'.$spendPercent.'%">✔</span>':'&nbsp;';
			$tables->rows[]=$row;
		}

		$row=array();
		$row[]='คุณภาพกิจกรรม';
		$row[]=$totalRate;
		$row[]=$totalPoint;
		for ($i=0;$i<100;$i=$i+10) $row[]=$ratePercent>$i && $ratePercent<=$i+10?'<span title="'.$ratePercent.'%">✔</span>':'&nbsp;';
		$tables->rows[]=$row;

		$ret .= $tables->build();
		$ret.='<style type="text/css">.project__report--percent td:nth-child(n+2) {text-align:center;} .project__report--percent td:nth-child(n+4) {font-size:20pt;}</style>';





		$ret.='<h4>ปัญหา/อุปสรรคในการดำเนินงานโครงการ (สรุปเป็นข้อ ๆ)</h4>'._NL;

		$tables = new Table();
		$tables->thead=array('ประเด็นปัญหา/อุปสรรค','สาเหตุเพราะ','แนวทางการแก้ไขของผู้รับทุน');
		$tables->rows[]=array(
					view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$irs->trid,'ret'=>'html'),$irs->text1,$isEdit,'textarea'),
					view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text5','tr'=>$irs->trid,'ret'=>'html'),$irs->text5,$isEdit,'textarea'),
					view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text6','tr'=>$irs->trid,'ret'=>'html'),$irs->text6,$isEdit,'textarea'),
					);

		$ret .= $tables->build();



		$ret.='<h4>แผนงาน/กิจกรรม ที่จะดำเนินการในงวดต่อไป</h4>'._NL;

		$useNextPeriod=1;

		if ($useNextPeriod==1) {
			$allPeriod=count(project_model::get_period($this->projectId));
			// Do not show activity on last report
			if ($this->period<$allPeriod-1) {
				$nextActDbs=project_model::get_calendar($this->projectId,$this->period+1,'owner');
				$ret.='<ol>'._NL;
				foreach ($nextActDbs->items as $irs) {
					$ret.='<li>'.$irs->title.' ( '.sg_date($irs->from_date).($irs->to_date!=$irs->from_date?' - '.sg_date($irs->to_date):'').' )</li>'._NL;
				}
				$ret.='</ol>'._NL;
			}
		}

		$section='title';
		$irs=end($reportInfo->items[$section]);

		//$ret.=view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$irs->trid,'ret'=>'html'),SG\getFirst($irs->text2,'1. '._NL.'2. '._NL.'3. '),$isEdit,'textarea');

		$ret.='<div style="width:40%;float:left;margin:60px 5% 30px; text-align:center;"><p>(................................)<br />'.$projectInfo->info->prowner.'<br />ผู้รับผิดชอบโครงการ<br />'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->detail1),$irs->detail1?sg_date($irs->detail1,'ว ดดด ปปปป'):'',$isEdit,'datepicker').'</p></div>'._NL;


		$ret.='</div>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">task_alt</i>',
				'navigator' => new ProjectFollowNavWidget($this->projectInfo, ['showPrint' => true]),
			]),
			'body' => new Widget([
				'children' => [
					new Container([
						'class' => 'project-operate',
						'children' => [
							$ret,
						], // children
					]), // Widget
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
	}
}
?>