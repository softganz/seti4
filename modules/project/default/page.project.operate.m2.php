<?php
/**
* Project :: รายงานสรุปการเงินปิดโครงการ (ง.2)
* Created 2021-11-14
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.m2[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectOperateM2 extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		$showBudget = $projectInfo->is->showBudget;

		$period=SG\getFirst($para->period,1);
		$formid='ง.2';
		$period=1;

		if (!$showBudget) return message('error', 'สำหรับเจ้าหน้าที่โครงการเท่านั้น');

		$isEdit=($projectInfo->info->project_statuscode==1) && (user_access('administer projects') || project_model::is_owner_of($this->projectId) || project_model::is_trainer_of($this->projectId));
		$isAccessActivityExpense=user_access('access activity expense') || $isOwner;

		$rs = project_model::get_tr($this->projectId,$formid,$period);
		$mainActivity = project_model::get_main_activity($this->projectId, _PROJECT_OWNER_ACTIVITY);

		// Show form toolbar
		$ui=new ui();
		$ui->add('<a href="'.url('project/operate/m2/'.$this->projectId).'">รายงานสรุปการเงินปิดโครงการ (ง.2)</a>');
		$ui->add('<a href="'.url('project/operate/m2/'.$this->projectId,array('download'=>'word')).'" >ดาวน์โหลด</a>');
		$ui->add('<a href="javascript:window.print()">พิมพ์</a>');
		if (!post('download')) $ret.='<div class="reportbar -no-print">'.$ui->build('ul').'</div>';

		$ret.='<p class="form-info">รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong><br />สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong></p>'._NL;

		if ($isEdit) {
			$inlineAttr['data-update-url']=url('project/edit/tr');
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-period']=$period;
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}
		$ret.='<div id="project-report-m2" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret.='<h3>รายงานสรุปการเงินปิดโครงการ</h3>'._NL;

		$section='title';
		$irs = isset($rs->items[$section]) ? end($rs->items[$section]) : (Object) [];

		$ret.='<p>ชื่อโครงการ <strong>'.$projectInfo->title.'</strong><br />รหัสโครงการ <strong>'.$projectInfo->info->prid.'</strong> สัญญาเลขที่ <strong>'.$projectInfo->info->agrno.'</strong></p>'._NL;
		$ret.='<p>ระยะเวลาตามสัญญา <strong>'.($projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'ว ดดด ปปปป'):'ไม่ระบุ').' - '. ($projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'ว ดดด ปปปป'):'ไม่ระบุ').'</strong><br />';
		$ret.='ระยะเวลาดำเนินการจริง <strong>'.view::inlineedit(array('group'=>'project','fld'=>'rdate_from','ret'=>'date:ว ดดด ปปปป', 'value'=>$projectInfo->info->rdate_from),$projectInfo->info->rdate_from,$isEdit,'datepicker').' - '.view::inlineedit(array('group'=>'project','fld'=>'rdate_end','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->rdate_end),$projectInfo->info->rdate_end,$isEdit,'datepicker').'</strong></p>'._NL;


		$ret.='<p>จำนวนเงินตามสัญญา <strong>'.number_format($projectInfo->info->budget,2).'</strong> บาท</p>';

		// คำนวณรายรับ
		$dbs = mydb::select(
			'SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ง.1" AND `part`="summary" ORDER BY `period`',
			[':tpid' => $this->projectId]
		);
		foreach ($dbs->items as $mrs) {
			$rcvMoneyStr.='งวดที่ '.$mrs->period.' = <strong>'.number_format($mrs->num2,2).' บาท</strong><br />';
			$rcvMoney+=$mrs->num2;
			$rcvInterestStr.='งวดที่ '.$mrs->period.' = <strong>'.number_format($mrs->num3,2).' บาท</strong><br />';
			$rcvInterest+=$mrs->num3;
			$rcvEtcStr.='งวดที่ '.$mrs->period.' = <strong>'.number_format($mrs->num4,2).' บาท</strong><br />';
			$rcvEtc+=$mrs->num4;
		}

		// คำนวณรายจ่ายทั้งโครงการ
	//	$projectPaid=mydb::select('SELECT SUM(num7) as project_paid FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="activity" AND `part`="owner" LIMIT 1',':tpid',$this->projectId)->project_paid;

		$projectPaid=$mainActivity->summary->expense;
		$total=$rcvMoney+$rcvInterest+$rcvEtc-$projectPaid;

		$activity=mydb::select('SELECT tr.`trid`, c.`title` activity, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7` FROM %project_tr% tr LEFT JOIN %calendar% c ON c.id=tr.calid WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid AND `part`="owner" AND tr.`date1` BETWEEN :start AND :end ORDER BY tr.`date1` ASC',':tpid',$projectInfo->tpid,':formid','activity',':start',$irs->date1,':end',$irs->date2);

		$tables = new Table();
		$tables->thead=array('','','จำนวนเงิน(บาท)');
		$tables->rows[]=array('รายรับรวม','(1) รับโอนจริงจาก '.cfg('project.grantby'),$rcvMoneyStr.'<br />รวมโอนทั้งสิ้น <strong>'.number_format($rcvMoney,2).'</strong> บาท');
		$tables->rows[]=array('','(2) ดอกเบี้ยรับ',$rcvInterestStr.'<br />รวมดอกเบี้ยรับทั้งสิ้น <strong>'.number_format($rcvInterest,2).'</strong> บาท');
		$tables->rows[]=array('','(3) เงินรับอื่น ๆ',$rcvEtcStr.'<br />รวมเงินรับอื่น ๆ ทั้งสิ้น <strong>'.number_format($rcvEtc,2).'</strong> บาท');
		$tables->rows[]=array('รายจ่ายรวม','(4) รายจ่ายทั้งโครงการ','<strong>'.number_format($projectPaid,2).'</strong> บาท');
		$tables->rows[]=array('<strong>สุทธิ</strong>','รายรับหักรายจ่าย (1)+(2)+(3)-(4)','<strong>'.number_format($total,2).'</strong> บาท');

		$ret .= $tables->build();

		if ($isEdit) {
			if ($_REQUEST['act']=='addmain') {
				mydb::query('INSERT INTO %project_tr% (`tpid`, `formid`, `part`, `uid`, `detail1`, `created`) VALUES (:tpid, "info", "mainact", :uid, "ระบุชื่อกิจกรรมหลัก", :created)',':tpid',$this->projectId, ':uid',i()->uid, ':created',date('U'));
				location(q());
			} else if ($_REQUEST['act']=='removeact') {
				mydb::query('UPDATE %project_tr% SET `parent`=NULL WHERE trid=:trid LIMIT 1',':trid',$_REQUEST['trid']);
				location(q());
			} else if ($_REQUEST['act']=='removemainact') {
				$subActivity=mydb::select('SELECT COUNT(*) amt FROM %project_tr% WHERE parent=:trid AND `formid`="activity" LIMIT 1',':trid',$_REQUEST['trid'])->amt;
				if ($subActivity==0) mydb::query('DELETE FROM %project_tr% WHERE trid=:trid LIMIT 1',':trid',$_REQUEST['trid']);
				location(q());
			}
		}

		$budgetRcv=$budgetExp=0;

		$ret.='<p>ตารางเปรียบเทียบงบประมาณที่ได้รับ กับงบใช้จริง</p>'._NL;

		// $tables = new Table([
		// 	'class' => 'project-report-item-mainactivity',
		// 	'thead' => ['กิจกรรม','amt amt1'=>'งบที่ได้รับ (บาท)','amt amt2'=>'งบใช้จริง (บาท)'],
		// ]);

		// foreach ($mainActivity->info as $mainact) {
		// 	$budgetRcv += $mainact->budget;
		// 	$tables->children[] = [
		// 		'<big>กิจกรรมหลัก : '.SG\getFirst($mainact->title,'ไม่ระบุ').'</big>'.print_o($mainact,'$mainact'),
		// 		'<big>'.number_format($mainact->budget,2).'</big>',
		// 		'<big>'.number_format($mainact->totalExpense,2).'</big>'
		// 	];
		// 	$i=0;
		// 	foreach ($mainActivity->activity[$mainact->trid] as $activity) {
		// 		if ($isAccessActivityExpense) {
		// 			$tables->children[] = [
		// 				++$i.'. '.$activity->title,
		// 				number_format($activity->budget,2),
		// 				number_format($activity->exp_total,2)
		// 			];
		// 		} else {
		// 			$tables->children[] = [
		// 				++$i.'. '.$activity->title,
		// 				'',
		// 				''
		// 			];
		// 		}
		// 		$budgetExp+=$activity->exp_total;
		// 	}
		// }
		// $tables->children[] = ['<h4>รวมงบทั้งหมด</h4>','<strong>'.number_format($budgetRcv,2).'</strong>', '<strong>'.number_format($budgetExp,2).'</strong>'];

		// $ret .= $tables->build();

		$tables = new Table([
			'class' => 'project-report-item-mainactivity',
			'thead' => ['กิจกรรม','amt amt1'=>'งบที่ได้รับ (บาท)','amt amt2'=>'งบใช้จริง (บาท)'],
		]);

		foreach ($projectInfo->activity as $activityMain) {
			if (!$activityMain->childs) continue;
			$mainExpense = 0;
			$budgetRcv += $activityMain->planBudget;
			$tables->children['main-'.$activityMain->trid] = [
				'<big>กิจกรรมหลัก : '.SG\getFirst($activityMain->title,'ไม่ระบุ').'</big>',
				'<big>'.number_format($activityMain->planBudget,2).'</big>',
				'<big>'.number_format($activityMain->totalExpense,2).'</big>'
			];
			$i=0;
			foreach (explode(',', $activityMain->childs) as $activityId) {
				$activity = $projectInfo->activity[$activityId];
				if ($isAccessActivityExpense) {
					$tables->children[] = [
						++$i.'. '.$activity->title,
						number_format($activity->budget,2),
						number_format($activity->totalExpense,2)
					];
				} else {
					$tables->children[] = [
						++$i.'. '.$activity->title,
						'',
						''
					];
				}
				$mainExpense += $activity->totalExpense;
				$budgetExp += $activity->totalExpense;
			}
			$tables->children['main-'.$activityMain->trid][2] = '<big>'.number_format($mainExpense,2).'</big>';
		}
		$tables->children[] = ['<h4>รวมงบทั้งหมด</h4>','<strong>'.number_format($budgetRcv,2).'</strong>', '<strong>'.number_format($budgetExp,2).'</strong>'];

		$ret .= $tables->build();

		// $ret.=print_o($mainActivity,'$mainActivity');

		$ret.='<p>ยอดรวมของงบใช้จริง จะต้องเท่ากับ(4) รายจ่ายทั้งโครงการ</p><p>ขอรับรองรายงานการเงินข้างต้นถูกต้องตรงตามความเป็นจริงทุกประการ</p>'._NL;

		$section='title';
		$irs = isset($rs->items[$section]) ? end($rs->items[$section]) : (Object) [];
		$ret.='<p>ลงชื่อ .............................................. หัวหน้าโครงการ/ผู้รับผิดชอบโครงการ<br />'._NL;
		$ret.=str_repeat('&nbsp;',10).'( '.$projectInfo->info->prowner.' )<br />';
		$ret.='วันที่รายงาน '.view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'date1','tr'=>$irs->trid,'ret'=>'date:ว ดดด ปปปป', 'value'=>$irs->date1),$irs->date1,$isEdit,'datepicker');
		$ret.='</p>';

		$ret.='</div>';

		if (post('download')) {
			sendheader('application/octet-stream');
			mb_internal_encoding("UTF-8");
			header('Content-Disposition: attachment; filename="'.mb_substr($projectInfo->title,0,50).'-ง.2-'.date('Y-m-d').'.doc"');
			if (post('o')=='word') {
				// move style tag to head section
				$body=$ret;
				if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
					foreach ($out[0] as $style) $styles.=$style._NL;
					$body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
				}
				$ret='<HTML>
				<HEAD>
				<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
				<TITLE>'.$projectInfo->title.'</TITLE>
				'.$styles.'
				</HEAD>
				<BODY>
				'.$body.'
				</BODY>
				</HTML>';
				die($ret);
			}
		}

		if ($isEdit) {
			$ret.='<script type="text/javascript">
	$(document).ready(function() {
	var postUrl=$(".inline-edit").attr("url");
	var period=$(".inline-edit").attr("period");
	$(".inline-edit a[action]")
	.click(function() {
		$this=$(this);
		var action=$this.attr("action");
		var group=$this.attr("group");
		var tr=$this.attr("tr");
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
	</script>';
		};
		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
					// $this->_script(),
				], // children
			]), // Widget
		]);
	}
}
?>