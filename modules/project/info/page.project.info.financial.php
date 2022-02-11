<?php
/**
* Project :: Money Report
* Created 2022-01-05
* Modify  2022-01-19
*
* @param Object $projectInfo
* @param Int $period
* @return Widget
*
* @usage project/{id}/info.financial/{period}
*/

import('model:project.follow.php');
import('widget:project.follow.nav.php');

class ProjectInfoFinancial extends Page {
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
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectId = $this->projectId;
		$projectInfo = $this->projectInfo;
		$period = $this->period;

		$showBudget = $projectInfo->is->showBudget;


		$formid='ง.1';

		if (!$showBudget) return message('error', 'สำหรับเจ้าหน้าที่โครงการเท่านั้น');

		if (empty($period)) return message('error', 'ไม่ระบุงวด');

		$periodInfo = ProjectFollowModel::getPeriod($projectId, $period);
		if (is_null($periodInfo->financialStatus)) return message('error', 'ยังไม่มีการสร้างรายงาน');

		// debugMsg($periodInfo, '$periodInfo');

		$statusText = [
			_PROJECT_DRAFTREPORT=>'เริ่มทำรายงาน',
			_PROJECT_COMPLETEPORT=>'ส่งรายงานจากพื้นที่',
			_PROJECT_LOCKREPORT=>'ผ่านการตรวจสอบของพี่เลี้ยงโครงการ',
			_PROJECT_PASS_HSMI=>'ผ่านการตรวจสอบของ '.cfg('project.grantpass'),
			_PROJECT_PASS_SSS=>'ผ่านการตรวจสอบของ '.cfg('project.grantby')
		];

		$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
		$isOwner = $projectInfo->RIGHT & _IS_OWNER;
		$isTrainer = $projectInfo->RIGHT & _IS_TRAINER;
		$isTeam = $isAdmin || $isOwner || $isTrainer;
		$isEdit = $projectInfo->info->project_statuscode == 1 && $isTeam;

		$isAccessActivityExpense=user_access('access activity expense') || $isOwner;


		$reportInfo=project_model::get_tr($projectId,$formid,$period);
		$locked=$periodInfo->financialStatus>=_PROJECT_LOCKREPORT;

		if ($locked) $isEdit=false;

		$url='project/'.$projectId.'/info.financial';
		// Show form toolbar
		$ui=new Ui();
		$ui->add('<a class="sg-action btn -link" href="'.url($url.'.status/'.$period).'" data-rel="box" data-width="640">สถานะรายงาน : '.$statusText[$periodInfo->financialStatus].'</a>');

		if ($isEdit && $periodInfo->financialStatus==_PROJECT_DRAFTREPORT) {
			$ui->add('<a class="sg-action btn -link" href="'.url('project/info/api/'.$projectId.'/financial.period.status/'.$period, ['step'=>_PROJECT_COMPLETEPORT]).'" title="คลิกเพื่อแจ้งรายงานเสร็จสมบูรณ์" data-rel="notify" data-done="load"><i class="icon -save"></i><span>แจ้งรายงานเสร็จสมบูรณ์</span></a>');
		} else if ($isEdit && $periodInfo->financialStatus==_PROJECT_COMPLETEPORT) {
			$ui->add('<a class="sg-action btn -link" href="'.url('project/info/api/'.$projectId.'/financial.period.status/'.$period, ['step'=>_PROJECT_DRAFTREPORT]).'" title="คลิกเพื่อยกเลิกการแจ้งรายงานเสร็จสมบูรณ์" data-rel="notify" data-done="load"><i class="icon -cancel -gray"></i><span>ยกเลิกการแจ้งรายงานเสร็จสมบูรณ์</span></a>');
		}

		if ($isAdmin && $periodInfo->financialStatus==_PROJECT_DRAFTREPORT) {
			$subui=new ui();
			$subui->add('<a class="sg-action" href="'.url('project/info/api/'.$projectId.'/financial.period.delete/'.$period).'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId.'/info.financials').'" data-confirm="ต้องการลบรายงานนี้ใช่ไหรือไม่ กรุณายืนยัน?"><i class="icon -delete"></i><span>ลบรายงานการเงินประจำงวดนี้</span></a>');
			$ui->add(sg_dropbox($subui->build('ul')));
		}





		$ret .= '<nav class="nav -page -no-print">'.$ui->build().'</nav>';



		$ret .= '<p class="form-info">รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong><br />สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong><br />งวดที่ <strong>'.$period.'</strong></p>'._NL;

		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit';
			$inlineAttr['data-tpid']=$projectId;
			$inlineAttr['data-update-url']=url('project/edit/tr');
			$inlineAttr['data-period']=$period;
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}
		$inlineAttr['class'] .= ' project-operate-report -m1';
		$ret .= '<div id="project-operate-m1-detail" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret .= '<h3>แบบรายงานการเงินโครงการ ประจำงวดที่ '.$period.'</h3>'._NL;
		$ret .= '<p>งวดโครงการ ตั้งแต่ <strong>'.sg_date($periodInfo->fromDate,'ว ดดด ปปปป').'</strong> ถึง <strong>'.sg_date($periodInfo->toDate,'ว ดดด ปปปป').'</strong></p>'._NL;

		$ret .= '<p>งวดรายงาน ตั้งแต่ '
			.view::inlineedit(array('group'=>'info:period','fld'=>'detail1','tr'=>$periodInfo->periodTranId,'ret'=>'date:ว ดดด ปปปป', 'value'=>sg_date($periodInfo->reportFromDate,'d/m/Y')),$periodInfo->reportFromDate,$isEdit,'datepicker')
			.' ถึง '
			.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$periodInfo->periodTranId,'ret'=>'date:ว ดดด ปปปป', 'value'=>sg_date($periodInfo->reportToDate,'d/m/Y')),$periodInfo->reportToDate,$isEdit,'datepicker')
			.'</p>'._NL;


		$ret .= '<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong></p>'._NL;


		// Section 1
		$ret .= '<h4>ส่วนที่ 1 แบบแจกแจงรายจ่ายแยกตามกิจกรรมของโครงการ</h4>'._NL;

		$tables = new Table();
		$tables->id='project-form-m1-tr';

		if ($isAccessActivityExpense) {
			$activity = mydb::select(
				'SELECT tr.`trid`, c.`title` activity, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`
				FROM %project_tr% tr
					LEFT JOIN %calendar% c ON c.id=tr.calid
				WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="owner" AND tr.`date1` BETWEEN :start AND :end
				ORDER BY tr.`date1` ASC',
				[
					':tpid' => $projectInfo->tpid,
					':formid' => 'activity',
					':start' => $periodInfo->reportFromDate,
					':end' => $periodInfo->reportToDate,
				]
			);

			$tables->colgroup=array('','','money m1'=>'','money m2'=>'','money m3'=>'','money m4'=>'','money m5'=>'','money m6'=>'','money m7'=>'');
			$tables->thead='<thead><tr><th rowspan="2"></th><th rowspan="2">กิจกรรม</th><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
			foreach ($activity->items as $irs) {
				if ($irs->num7<=0) continue;
				$tables->rows[] = [
					++$no.')',
					$irs->activity,
					number_format($irs->num1,2),
					number_format($irs->num2,2),
					number_format($irs->num3,2),
					number_format($irs->num4,2),
					number_format($irs->num5,2),
					number_format($irs->num6,2),
					number_format($irs->num7,2)
				];
			}
		} else {
			$tables->colgroup=array('','','money m1'=>'','money m2'=>'','money m3'=>'','money m4'=>'','money m5'=>'','money m6'=>'','money m7'=>'');
			$tables->thead=array('','กิจกรรม');
			$activity=mydb::select('SELECT tr.`trid`, c.`title` activity, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7` FROM %project_tr% tr LEFT JOIN %calendar% c ON c.id=tr.calid WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="owner" AND tr.`date1` BETWEEN :start AND :end ORDER BY tr.`date1` ASC',':tpid',$projectInfo->tpid,':formid','activity',':start',$periodInfo->reportFromDate,':end',$periodInfo->reportToDate);
			foreach ($activity->items as $irs) {
				if ($irs->num7<=0) continue;
				$tables->rows[] = [
					++$no.')',
					$irs->activity,
				];
			}
		}
		$ret.=$tables->build();


		// Section 2
		$section='summary';
		$ret .= '<h4>ส่วนที่ 2 แบบรายงานสรุปการใช้จ่ายเงินประจำงวดนี้</h4><p> (โปรดแนบสำเนาสมุดบัญชีเงินฝากธนาคารที่ปรับปรุงยอดล่าสุด)</p>'._NL;

		$tables = new Table();
		$tables->id='project-operate-m1-detail-summary';
		$tables->thead=array('(1) รายรับ','(2) รายจ่าย','(3) คงเหลือ');
		unset($row);
		$row[]=	'1) เงินคงเหลือยกมา (ถ้ามี) = '
			. view::inlineedit(
				[
					'group' => $formid.':'.$section,
					'fld' => 'num1',
					'tr' => $periodInfo->summaryTranId,
					'ret' => 'money',
					'callback' => 'm1_checksum'
				],
				$periodInfo->openBalance,
				$isEdit,
				'money'
			)
			. ' บาท<br />'
			. '2) เงินรับจาก '.cfg('project.grantby').' งวดนี้ = '
			. view::inlineedit(
				[
					'group'=>$formid.':'.$section,
					'fld'=>'num2',
					'tr'=>$periodInfo->summaryTranId,
					'ret'=>'money',
					'callback'=>'m1_checksum'
				],
				$periodInfo->incomeGrant,
				$isEdit,
				'money'
			)
			. ' บาท<br />'
			. '3) ดอกเบี้ย = '
			. view::inlineedit(
				[
					'group'=>$formid.':'.$section,
					'fld'=>'num3',
					'tr'=>$periodInfo->summaryTranId,
					'ret'=>'money',
					'callback'=>'m1_checksum'
				],
				$periodInfo->incomeInterest,
				$isEdit,
				'money'
			)
			. ' บาท<br />'
			. '4) เงินรับอื่น ๆ = '
			. view::inlineedit(
				[
					'group'=>$formid.':'.$section,
					'fld'=>'num4',
					'tr'=>$periodInfo->summaryTranId,
					'ret'=>'money',
					'callback'=>'m1_checksum'
				]
				,$periodInfo->incomeOther,
				$isEdit,
				'money'
			)
			. ' บาท';

		$no=0;
		foreach ($activity->items as $mrs) {
			if ($mrs->num7<=0) continue;
			if ($isAccessActivityExpense) {
				$act_money.=++$no.') กิจกรรม '.$no.' = '.number_format($mrs->num7,2).' บาท<br />';
			}
			$act_money_total+=$mrs->num7;
		}
		$row[]=$act_money;
		$balance=$periodInfo->incomeTotal-$act_money_total;
		$real_balance = $periodInfo->pettyCash+$periodInfo->bankBalance;
		//$ret .= 'Balance='.$balance.' Real balance='.$real_balance;

		$row[] = '1) เงินสดในมือ = '
			. view::inlineedit(
				[
					'group'=>$formid.':'.$section.'',
					'fld'=>'num5',
					'tr'=>$periodInfo->summaryTranId,
					'ret'=>'money',
					'callback'=>'m1_checksum'
				],
				$periodInfo->pettyCash,
				$isEdit,
				'money'
			)
			. ' บาท<br />'
			. '2) เงินสดในบัญชี = '
			. view::inlineedit(
				[
					'group'=>$formid.':'.$section.'',
					'fld'=>'num6',
					'tr'=>$periodInfo->summaryTranId,
					'ret'=>'money',
					'callback'=>'m1_checksum'
				],
				$periodInfo->bankBalance,
				$isEdit,
				'money'
			)
			. ' บาท';

		$tables->rows[] = $row;

		$is_balance = (String) $balance == (String) $real_balance;
		$tables->rows[] = [
			'รวมรายรับ (1) = <strong id="project-m1-sum-income">'.number_format($periodInfo->incomeTotal,2).'</strong> บาท',
			'รวมรายจ่าย (2) = <strong id="project-m1-sum-expense">'.number_format($act_money_total,2).'</strong> บาท',
			'(1) รายรับ - (2) รายจ่าย = (3) <strong id="project-m1-sum-balance"'.($is_balance?'':' class="notbalance"').'>'.number_format($balance,2).'</strong> บาท<br /><span id="project-m1-sum-balance-msg" class="noprint '.($is_balance?'hidden':'notbalance').'">ยอด (1) - (2) ไม่เท่ากับ (3)</span>',
		];

		$ret .= $tables->build();


		// Section 4
		$nextPeriod = ProjectFollowModel::getPeriod($projectId, $period+1);
		$ret .= '<h4>ส่วนที่ 3 ขอเบิกเงินสนับสนุนโครงการงวดต่อไป</h4>'._NL;
		$useNextPeriod = $periodInfo->withdrawNextPeriod == 2 ? 2 : 1;
		if ($periodInfo->openBalance == 0) $periodInfo->openBalance = '';

		if ($isEdit) {
			$ret .= '<p class="noprint">'
				. '<label>ขอเบิกเงินสนับสนุนโครงการงวดต่อไป :</label><br />'
				. view::inlineedit(
					[
						'group'=>$formid.':'.$section.'',
						'fld'=>'flag',
						'tr'=>$periodInfo->summaryTranId,
						'value'=>$useNextPeriod
					],
					'1:ขอเบิก',
					$isEdit,
					'radio'
				).'<br />'
				. view::inlineedit(
					[
						'group'=>$formid.':'.$section.'',
						'fld'=>'flag',
						'tr'=>$periodInfo->summaryTranId,
						'value'=>$useNextPeriod
					],
					'2:ไม่ขอเบิก',
					$isEdit,
					'radio'
				)
				. '</p>';
		}

		if ($useNextPeriod == 1) {
			$ret .= 'งวดที่ <strong>'.($period+1).'</strong> เป็นจำนวนเงิน <strong>'
				. view::inlineedit(
					[
						'group'=>$formid.':'.$section.'',
						'fld'=>'num10',
						'tr'=>$periodInfo->summaryTranId,
						'ret'=>'money',
						'callback'=>'m1_checksum'
					],
					SG\getFirst($periodInfo->withdrawNextMoney,number_format($nextPeriod->budget,2)),
					$isEdit,
					'money'
				).'</strong> บาท<br />'._NL;
		} else {
			$ret .= 'งวดที่ <strong>'.($period+1).'</strong> เป็นจำนวนเงิน <strong>0.00</strong> บาท<br />'._NL;
		}

		$ret .= '<p class="sign -item"><span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span class="sign -position"> หัวหน้าโครงการ/ผู้รับผิดชอบโครงการ</span><br />'._NL;
		$ret .= '<span class="sign -prespace"></span><span class="sign -nametext">( '.$projectInfo->info->prowner.' )</span>';
		$ret .= '</p>';

		if ($useNextPeriod==1) {
			$allPeriod = count(ProjectFollowModel::getPeriod($projectId));
			// Do not show activity on last report
			if ($period < $allPeriod - 1) {
				$ret .= '<p>เพื่อดำเนินกิจกรรมหลัก ดังต่อไปนี้</p>'._NL;
				$nextActDbs = project_model::get_calendar($projectId,$period+1,'owner');
				$ret .= '<ol>'._NL;
				foreach ($nextActDbs->items as $irs) {
					$ret .= '<li><strong>'.$irs->title.'</strong> ('.sg_date($irs->from_date).($irs->to_date!=$irs->from_date?' - '.sg_date($irs->to_date):'').')</li>'._NL;

				}
				$ret .= '</ol>'._NL;
			}
		}

		$ret .= '<p>ข้าพเจ้าขอรับรองว่าเงินสนับสนุนโครงการ ได้นำมาใช้อย่างถูกต้อง ตรงตามแผนงาน โครงการ ที่ระบุไว้ในสัญญาทุกประการ และขอรับรองรายงานข้างต้น</p>'._NL;

		// ผู้รับผิดชอบโครงการ
		$ret .= '<p class="sign -item">';
		$ret .= '<span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span sign -position"> หัวหน้าโครงการ/ผู้รับผิดชอบโครงการ</span><br />'._NL;
		$ret .= '<span class="sign -prespace"></span><span class="sign -nametext">( '.$projectInfo->info->prowner.' )</span><br />'._NL;
		$ret .= '<span class="sign -prespace"></span><span class="sign -date">'.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail1','tr'=>$periodInfo->summaryTranId,'ret'=>'date:ว ดดด ปปปป', 'value'=>$periodInfo->signDate?sg_date($periodInfo->signDate,'d/m/Y'):''),$periodInfo->signDate,$isEdit,'datepicker').'</span>';
		$ret .= '</p>';

		// เจ้าหน้าที่การเงินโครงการ
		$ret .= '<p class="sign -item">';
		$ret .= '<span class="sign -pretext">ลงชื่อ </span><span class="sign -signdraw">....................................................</span><span class="sign -position"> เจ้าหน้าที่การเงินโครงการ</span><br />'._NL;
		$ret .= '<span class="sign -prespace"></span><span class="sign -nametext">( '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail4','tr'=>$periodInfo->summaryTranId, 'class'=>"W1"),$periodInfo->signOfficerName,$isEdit,'text-block').' )</span><br />'._NL;
		$ret .= '<span class="sign -prespace"></span><span class="sign -date"> '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'detail2','tr'=>$periodInfo->summaryTranId,'ret'=>'date:ว ดดด ปปปป', 'value'=>$periodInfo->signOfficerDate?sg_date($periodInfo->signOfficerDate,'d/m/Y'):''),$periodInfo->signOfficerDate,$isEdit,'datepicker').'</span><br />'._NL;
		$ret .= '</p>'._NL;

		$ret .= '</div>'._NL;
		$ret .= '<style type="text/css">
			.sign.-item {margin: 3em 0;}
			.sign.-pretext, .sign.-prespace {width:5em; display: inline-block;}
			.sign.-signdraw, .sign.-nametext, .sign.-date {width: 20em; display: inline-block; text-align: center; vertical-align: bottom;}
			.sign.-signdraw {overflow: hidden;}
			.fixed-width {display:inline-block; width: 20em;overflow:hidden;text-align:center;}

			@media print {
				.sign.-pretext, .sign.-prespace {width:2em;}
				.sign.-signdraw, .sign.-nametext, .sign.-date {width: 10em;}

			}
			</style>'._NL;

		if ($isEdit) {
			$ret .= '<script type="text/javascript">
			$(document).ready(function() {
				var postUrl=$(".inline-edit").attr("data-url");
				var period=$(".inline-edit").attr("data-period");
				$(".inline-edit a[action]")
				.click(function() {
					$this=$(this);
					var action=$this.attr("action");
					var group=$this.attr("group");
					var tr=$this.attr("data-tr");
					var para={id: tpid, action: action, group: group, period: period, tr: tr};
					if (group=="ง.1:nextact" && action=="add") para.calid=$("#nextactivity").val();
					$.post(postUrl,para, function(data) {
						if (action=="del") {
							var row = $this.closest("li");
							row.remove();
						} else if (action=="add") {
							var $target=$this.closest("li");
							var conceptName = $target.find(":selected").text();
							$("<li>"+conceptName+"</li>").insertBefore($target);
							$target.find("select").val("");
						}
					}, "json");
					return false;
				});
			});
			</script>'._NL;
		};

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">attach_money</i>',
				'navigator' => new ProjectFollowNavWidget($this->projectInfo, ['showPrint' => true]),
			]),
			'body' => new Container([
				'class' => 'project-operate',
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}
}
?>