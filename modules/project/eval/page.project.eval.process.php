<?php
/**
* Project :: แบบการติดตามประเมินผลการดำเนินกิจกรรมของโครงการ (Process Evaluation)
* Created 2022-02-05
* Modify  2022-02-05
*
* @param Object $projectInfo
* @param String $action
* @return Widget
*
* @usage project/{id}/eval.process[/{action}]
*/

import('widget:project.info.appbar.php');

class ProjectEvalProcess extends Page {
	var $projectId;
	var $action;
	var $projectInfo;

	function __construct($projectInfo, $action = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->action = $action;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		$actionList = R::Model('project.action.get',$this->projectId, '{key: "activityId"}');

		$isViewOnly = $this->action == 'view';
		$isEditable = $projectInfo->info->isRight;
		$isEdit = $projectInfo->info->isRight && $this->action == 'edit';

		$ret .= '<h2 class="title -main">แบบการติดตามประเมินผลการดำเนินกิจกรรมของโครงการ (Process Evaluation)</h2>';

		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$this->projectId.'/eval.process',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
		} else if ($isEditable) {
			$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$this->projectId.'/eval.process/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
		}

		if ($isEdit) {
			$inlineAttr['class'] = 'sg-inline-edit ';
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}
		$inlineAttr['class'] .= 'project-result';

		$ret.='<div id="project-result" '.sg_implode_attr($inlineAttr).'>'._NL;


		$tables = new Table();
		$tables->thead='<thead>'._NL.'<tr><th rowspan="2">กิจกรรม</th><th colspan="2">ระยะเวลา</th><th colspan="2">เป้าหมาย/วิธีการ</th><th colspan="2">ผลการดำเนินงาน</th><th rowspan="2">ปัญหา/อุปสรรค/แนวทางแก้ไข</th></tr>'._NL.'<tr><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th></tr>'._NL.'</thead>'._NL;


		foreach ($projectInfo->activity as $rs) {
			$actionRs = $actionList[$rs->trid];
			$tables->rows[] = array(
				$rs->title,
				$rs->fromdate ? sg_date($rs->fromdate,'ว ดด ปปปป') : '',
				$actionRs->actionDate ? sg_date($actionRs->actionDate,'ว ดด ปปปป') : '',
				sg_text2html($actionRs->actionPreset),
				sg_text2html($actionRs->actionReal),
				sg_text2html($actionRs->outputOutcomePreset),
				sg_text2html($actionRs->outputOutcomeReal),
				sg_text2html($actionRs->problem),
			);
		}

		$ret .= $tables->build();
		$ret.='</div><!-- project-result -->';

		//$ret .= print_o($projectInfo->activity[115],'$activity');
		//$ret .= print_o($actionList,'$actionList');

		/*
		$stmt='SELECT tr.trid, c.id, c.title, c.location, c.from_date, c.to_date, tr.part,
							tr.date1 date_do,
							IFNULL(tr.text1,c.detail) goal_plan,
							tr.text2 goal_do,
							tr.text3 result_plan,
							tr.text4 result_do,
							tr.text5 problem,
							a.mainact,
							ma.detail1 mainact_title
						FROM %calendar% c
							LEFT JOIN %project_tr% tr ON tr.calid=c.id
							LEFT JOIN %project_activity% a ON a.calid=c.id
							LEFT JOIN %project_tr% ma ON ma.trid=a.mainact
						WHERE c.tpid=:tpid
						ORDER BY `from_date` ASC';
		$dbs=mydb::select($stmt,':tpid',$this->projectId);

		$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));
		$editclass=$is_edit?'editable':'';

		$ret.='<div id="project-evaluation" class="inline-edit" url="'.url('project/edit/tr').'">'._NL;

		$tables = new Table();
		$tables->thead='<thead>'._NL.'<tr><th colspan="2">ระยะเวลา</th><th colspan="2">เป้าหมาย/วิธีการ</th><th colspan="2">ผลการดำเนินงาน</th><th rowspan="2">ปัญหา/อุปสรรค/แนวทางแก้ไข</th></tr>'._NL.'<tr><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th></tr>'._NL.'</thead>'._NL;
		foreach ($dbs->items as $rs) {
			list($yy,$mm,$dd)=explode('-',$rs->date_do);
			$tables->rows[]=array('<td colspan="7">'
													.'<h4>'.($rs->trid?'<a href="'.url('paper/'.$topic->tpid.'/member/'.$rs->part,NULL,'tr-'.$rs->trid).'">':'').++$no.'. '.$rs->title.($rs->trid?'</a>':'').'</h4> '
													.'<p>กิจกรรมหลัก : '.(is_null($rs->mainact_title)?'ไม่ระบุ':$rs->mainact_title).'</p>'
													.'</td>');

			$tables->rows[]=array(	sg_date($rs->from_date,'ววว ว ดด ปป').($rs->to_date && $rs->to_date!=$rs->from_date ? ' - '.sg_date($rs->to_date,'ววว ว ดด ปป') : ''),
														!$rs->trid && $is_edit?'<p><a class="button" href="'.url('paper/'.$topic->tpid.'/member/owner','calid='.$rs->id).'" title="เขียนบันทึกกิจกรรม">บันทึกกิจกรรม</a></p>':
														($rs->date_do?sg_date($rs->date_do,'ววว ว ดด ปป'):''),
														sg_text2html($rs->goal_plan),
														sg_text2html($rs->goal_do),
														sg_text2html($rs->result_plan),
														sg_text2html($rs->result_do),
														sg_text2html($rs->problem),
														'config'=>array('calid'=>$rs->id,'tr'=>$rs->trid)
													);
		}
		$ret.=$tables->build();
		$ret.='</div><!--project-evaluation-->';
		*/

		///$ret .= print_o($projectInfo,'$projectInfo');
		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}
}
?>