<?php
/**
* Project :: View Planning Detail
* Created 2021-08-01
* Modify  2021-08-01
*
* @param Int $planningInfo
* @param String $action
* @param Int $tranId
* @request mode,ref,debug
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

// import('model:project.php');
import('widget:project.like.status.php');

class ProjectPlanningInfoView extends Page {
	var $planningInfo;
	var $action;
	var $tranId;

	function __construct($planningInfo, $action = NULL, $tranId = NULL) {
		$this->planningInfo = $planningInfo;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$actionMode = post('mode');

		$planningInfo = $this->planningInfo;
		$action = $this->action;
		$tranId = $this->tranId;

		$planningId = $planningInfo->projectId;

		if (!$planningId) return message(['text' => 'PROCESS ERROR']);
		$ret = '';

		// R::View('project.toolbar',$self,$planningInfo->title, 'planning', $planningInfo,'{showPrint:true}');

		//$ret .= '$action ='.$action.' $tranId = '.$tranId;

		if (empty($planningInfo->tpid)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

		// $isPrint=$action=='print';
		// $isAdmin=$project->isAdmin;
		// $isEdit=($planningInfo->RIGHT & _IS_EDITABLE) || ($planningInfo->RIGHT & _IS_TRAINER);
		// if ($isPrint) $isEdit=false;

		$isPrint = $action == 'print';
		$isAdmin = $planningInfo->RIGHT & _IS_ADMIN;
		$isEditable = ($planningInfo->RIGHT & _IS_EDITABLE) || ($planningInfo->RIGHT & _IS_TRAINER);
		if ($isPrint) $isEditable = false;

		$isEdit = $isEditable && $actionMode == 'edit';
		$isDeleteable = ($planningInfo->RIGHT & _IS_EDITABLE) && empty($planningInfo->project);

		$optionEditIndicator=cfg('project.option.planning.editindicator');

		$title = reset(SG\getFirst(project_model::get_tr($planningId,$formid='info:title')->items['title'], []));


		if ($action) {
			switch ($action) {
				case 'detail':
					$problem=NULL;
					$refid=post('ref');
					foreach ($planningInfo->problem as $rs) {
						if (($tranId && $rs->trid==$tranId) || ($refid && $rs->refid==$refid)) {
							$problem=$rs;
							break;
						}
					}
					$ret.='<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;
					$ret.='<h2>รายละเอียดสถานการณ์ปัญหา</h2>';
					$ret.=view::inlineedit(array('group'=>'info:problem:'.$refid,'fld'=>'text1','tr'=>$problem->trid,'refid'=>$refid,'class'=>'-fill','ret'=>'html','placeholder'=>'...'),$problem->detailproblem,$isEdit,'textarea');
					$ret.='</div><!-- project-info -->';
					//$ret.=print_o($problem,'$problem');
					//$ret.=print_o($planningInfo,'$planningInfo');
					return $ret;
					break;

				default:
					# code...
					break;
			}
		}

		$ret .= (new ScrollView([
			'child' => new ProjectLikeStatusWidget([
				'projectInfo' => $planningInfo,
			]),
		]))->build();

		$addBtn='<a class="tran-remove -hidden" href="" data-rel="none" data-removeparent="tr"><i class="icon -cancel -gray"></i></a><a class="add-tran" href="javascript:void(0)" title="เพิ่ม"><i class="icon -addbig -primary -circle"></i></a>';



		$inlineAttr = array();
		$inlineAttr['class'] = 'project-planning ';

		if ($isEdit) {
			$inlineAttr['data-tpid'] = $planningId;
			$inlineAttr['class'] .= 'sg-inline-edit';
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug'] = 'yes';
		}

		$ret .= '<article id="project-planning" '.sg_implode_attr($inlineAttr).'>'._NL;

		//$ret.='<h2>'.$planningInfo->info->planName.' ประจำปีงบประมาณ '.($planningInfo->info->pryear+543).'<br />'.$fundInfo->name.'</h2>';

		//'ชื่อโครงการ/กิจกรรม'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$topic->title,$isEdit).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')

		$ret.='<section id="project-planning-view-detail" class="box project-planning-view-detail">';
		$ret.='<h4>ข้อมูลแผนงาน</h4>';

		//$ret.=view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill','label'=>'ชื่อแผนงาน'),$planningInfo->title, $isEdit);
		//$ret.=view::inlineedit(array('group'=>'project','fld'=>'pryear','label'=>'ประจำปีงบประมาณ'),$planningInfo->info->pryear, $isEdit);

		$tables = new Table();
		$tables->addClass('item__card project-info');
		$tables->colgroup = array('width="30%"','width="70%"');


		$tables->rows[] = array(
			'ชื่อแผนงาน',
			'<strong>'.view::inlineedit(array('group'=>'topic','fld'=>'title','class'=>'-fill'),$planningInfo->title,$isEdit).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
		);

		$tables->rows[] = array(
			'รหัสแผนงาน',
			view::inlineedit(array('group'=>'project','fld'=>'prid'),$planningInfo->info->prid,$isEdit)
		);

		$tables->rows[] = array(
			'ภายใต้ชื่อแผนงาน/ชุดโครงการ',
			view::inlineedit(array('group'=>'info:title','fld'=>'detail1','class'=>'-fill'),$title->detail1,$isEdit)
			. ($planningInfo->info->projectset_name ? '<a href="'.url('paper/'.$planningInfo->info->projectset).'">'.$planningInfo->info->projectset_name.'</a>' : '')
		);


		$tables->rows[] = array(
			'ชื่อองค์กรที่รับผิดชอบ',
			view::inlineedit(array('group'=>'project','fld'=>'orgnamedo','class'=>'-fill'),$planningInfo->info->orgnamedo,$isEdit)
		);

		$openYear = SG\getFirst(date('Y'));
		$pryearList = array();
		for ($i = $openYear; $i <= date('Y'); $i++) {
			$pryearList[$i] = $i + 543;
		}

		$tables->rows[] = array(
			'วันที่อนุมัติ',
			view::inlineedit(array('group'=>'project','fld'=>'date_approve','ret'=>'date:ว ดดด ปปปป','value'=>$planningInfo->info->date_approve?sg_date($planningInfo->info->date_approve,'d/m/Y'):''),
			$planningInfo->info->date_approve,
			$isEdit,
			'datepicker')
			.($isEdit?' <span class="form-required">*</span>':'')
		);

			//view::inlineedit(array('group'=>'project','fld'=>'date_approve','value'),$planningInfo->info->date_approve,$isEdit,'datepicker',$pryearList).' <span class="form-required">*</span>'.'นำไปคำนวนปีงบประมาณ');
		if ($isAdmin)
			$tables->rows[]=array(
				'ปี',
				view::inlineedit(array('group'=>'project','fld'=>'pryear'),$planningInfo->info->pryear+543,$isEdit,'select',$pryearList).' (เฉพาะแอดมิน)'
			);

		$tables->rows[] = array(
			'ระยะเวลาดำเนินงาน',
			view::inlineedit(
				array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$planningInfo->info->date_from?sg_date($planningInfo->info->date_from,'d/m/Y'):''),
				$planningInfo->info->date_from,
				$isEdit,
				'datepicker'
			)
			. ' - '
			. view::inlineedit(
				array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$planningInfo->info->date_end?sg_date($planningInfo->info->date_end,'d/m/Y'):''),
				$planningInfo->info->date_end,
				$isEdit,
				'datepicker'
			)
			.($isEdit ? ' <span class="form-required">*</span>' : '')
		);

		$tables->rows[] = array(
			'งบประมาณ',
			view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money'),$planningInfo->info->budget,$isEdit,'money').' บาท'.($isEdit?' <span class="form-required">*</span>':'')
		);



		// ข้อมูลผู้รับผิดชอบโครงการ
		$tables->rows[] = array(
			'ผู้รับผิดชอบ',
			view::inlineedit(array('group'=>'project','fld'=>'prowner','class'=>'-fill'),$planningInfo->info->prowner,$isEdit)
		);








		if (empty($planningInfo->info->area))
			$planningInfo->info->area=$planningInfo->info->areaName;

		$tables->rows[] = array(
			'พื้นที่ดำเนินการ',
			view::inlineedit(
				array(
					'group' => 'project',
					'fld' => 'area',
					'areacode' => $planningInfo->info->areacode,
					'class' => '-fill',
					'options' => '{
						class: "-fill",
						autocomplete: {
							target: "areacode",
							query: "'.url('api/address').'",
							minlength: 5
						}
					}',
				),
				$planningInfo->info->area,
				$isEdit,
				'autocomplete'
			)
		);

		$gis['address'] = array();

		//$ret.=print_o($provList,'$provList');




		$ret .= $tables->build()._NL;

		$ret .= R::PageWidget('project.info.period', [$planningInfo])->build();

		$ret .= '</section>';


		$openYear=SG\getFirst($planningInfo->info->pryear,date('Y'))-1;
		$pryearList=array();
		for ($i=$openYear; $i <= date('Y')+1; $i++) {
			$pryearList[$i]=$i+543;
		}
		//$ret.=view::inlineedit(array('group'=>'project','fld'=>'pryear','class'=>'-fill','label'=>'ประจำปีงบประมาณ'),$planningInfo->info->pryear+543,$false,'select',$pryearList);

	//<span class="inline-edit-field -textarea" onclick="" data-group="info:basic" data-fld="text1" data-tr="" data-ret="html" data-placeholder="..." data-button="yes" data-type="textarea" data-value="" title="คลิกเพื่อแก้ไข">...</span>

		$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;

		$ret .= '<h2 class="title -main">ข้อมูลในการดำเนินงาน</h2>'._NL;

		$stmt = 'SELECT
			tg.`catid`,tg.`name`,tr.`trid`,tr.`refid`, tg.`process`
			FROM %tag% tg
				LEFT JOIN %project_tr% tr ON tr.`tpid` = :tpid AND tr.`formid` = "info" AND tr.`part` = "supportplan" AND tr.`refid` = tg.`catid`
			WHERE tg.`taggroup` = "project:planning" AND tg.`process` IS NOT NULL';
		$issueDbs=mydb::select($stmt,':tpid',$planningId);

		if ($issueDbs->_num_rows) {
			$optionsIssue = array();
			foreach ($issueDbs->items as $rs) {
				if ($isEdit) {
					$optionsIssue[] = '<abbr class="checkbox -block"><label>'
						.view::inlineedit(
							array(
								'group'=>'info:supportplan:'.$rs->catid,
								'fld'=>'refid',
								'tr'=>$rs->trid,
								'value'=>$rs->refid,
								'removeempty'=>'yes',
								'callback' => 'projectDevelopIssueChange',
								'callback-url' => url('paper/'.$planningId)
							),
							$rs->catid.':'.$rs->name,
							$isEdit,
							'checkbox')
						.' </label></abbr>';
				} else {
					if ($rs->trid) $optionsIssue[] = $rs->name;
				}
			}

			$ret .= '<h3>ประเด็นที่เกี่ยวข้อง</h3>';
			$ret .= '<div class="project-info-issue box" id="project-info-issue">';
			//if ($issueDbs->_empty) $ret .= '<div class="-no-print">ยังไม่มีการสร้างรายการความสอดคล้องในระบบ</div>';
			$ret .= ($isEdit ? implode('', $optionsIssue) : implode(' , ', $optionsIssue));
			$ret .= '</div><!-- project-info-issue -->';
		}

		$ret .= '<div class="project-info-problem box" id="project-info-problem">';
		$ret .= '<h3>สถานการณ์ปัญหา</h3>';
		$ret .= R::PageWidget('project.info.problem', [$projectInfo])->build();
		$ret .= '<p><b>สถานการณ์/หลักการและเหตุผล (บรรยายเพิ่มเติม)</b></p>'
				. view::inlineedit(array('group'=>'info:basic','fld'=>'text1', 'tr'=>$basicInfo->trid, 'ret'=>'html', 'placeholder' => 'บรรยายสถานการณ์/หลักการและเหตุเพิ่มเติมได้ในช่องนี้'),$basicInfo->text1,$isEdit,'textarea')
				. _NL;
		$ret.='</div><!-- project-info-problem -->';







		$ret.='<section class="box">';
		// Show project objective
		$ret .= '<h3>วัตถุประสงค์/เป้าหมาย</h3>'._NL;
		$ret .= '<div id="project-info-objective" class="project-info-objective box">'._NL;
		$ret .= R::PageWidget('project.info.objective', [$planningInfo])->build();
		$ret.='</section><!-- box -->';
		//$ret .= print_o($planningInfo,'$planningInfo');




		// ดึงจากค่าที่กำหนดไว้แล้ว
		$ret.='<section class="box">';
		$ret.='<h4>แนวทาง/วิธีการสำคัญ</h4>';
		$tables = new Table();
		$tables->thead=array('no'=>'','แนวทาง','วิธีการสำคัญ');
		if ($isEdit) $tables->thead['icons -c1']='';
		$no=0;
		$cardItem='';
		foreach ($planningInfo->guideline as $rs) {
			$row=array(
				++$no,
				$rs->refid?$rs->title:view::inlineedit(array('group'=>'info:guideline','fld'=>'text1','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุแนวทาง','ret'=>'html'),$rs->title,$isEdit,'textarea'),
				view::inlineedit(array('group'=>'info:guideline','fld'=>'text2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'ระบุวิธีการ','ret'=>'html'),$rs->action,$isEdit && ($rs->refid?$optionEditIndicator:true),'textarea')
			);

			$cardItem.='<div>';
			$cardItem.='<div>';
			$cardItem.=$rs->refid?'<h5>'.$no.'. '.$rs->title.'</h5>':view::inlineedit(array('group'=>'info:guideline','fld'=>'text1','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุแนวทาง','ret'=>'html'),$rs->title,$isEdit,'textarea');
			$cardItem.='</div>';
			$cardItem.='<div>';
			$cardItem.=view::inlineedit(array('group'=>'info:guideline','fld'=>'text2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'ระบุวิธีการ','ret'=>'html'),$rs->action,$isEdit && ($rs->refid?$optionEditIndicator:true),'textarea');
			$cardItem.='</div>';
			$cardItem.='</div>';


			if ($isEdit) {
				$row[]=empty($rs->catid)?'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$planningId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>':'';
			}
			$tables->rows[]=$row;
		}
		//$ret.=$cardItem;
		$ret.=$tables->build();

		if ($isEdit) {
			$ret.='<div class="-sg-text-right"><a class="sg-action add-tran" href="'.url('project/planning/'.$planningId.'/info/addtr/guideline').'" title="เพิ่มแนวทาง/วิธีการสำคัญ" data-rel="#main"><i class="icon -addbig -white -circle -primary"></i></a></div>';
		}
		$ret.='</section><!-- box -->';





		$ret.='<section id="project-planning-view-budget" class="box">';
		$ret.='<h4>งบประมาณที่ตั้งไว้ตามแผนงาน (บาท)</h4>';
		$ret.=view::inlineedit(array('group'=>'project','fld'=>'budget','class'=>'-fill'),$planningInfo->info->budget,$isEdit);
		$ret.='</section><!-- box -->';



		$ret.='<section id="project-planning-view-todo" class="box">';
		$ret.='<h4>โครงการที่ควรดำเนินการ</h4>';
		$tables = new Table();
		$tables->thead=array('no'=>'','title'=>'ชื่อโครงการย่อย','ผู้รับผิดชอบ','money'=>'งบประมาณที่ตั้งไว้ (บาท)','btncmd -no-print'=>'');
		if ($isEdit) $tables->thead['icons -c1 -no-print']='';
		$no=0;
		foreach ($planningInfo->project as $rs) {
			$row=array(
				++$no,
				view::inlineedit(array('group'=>'info:project','fld'=>'detail1','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุชื่อโครงการ','callback'=>'projectPlanningProjectTitleChange'),$rs->title,$isEdit),
				view::inlineedit(array('group'=>'info:project','fld'=>'detail2','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุชื่อผู้รับผิดชอบ'),$rs->owner,$isEdit),
				view::inlineedit(array('group'=>'info:project','fld'=>'num1','tr'=>$rs->trid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'0.00'),number_format($rs->budget,2),$isEdit),
				$rs->refid?
				($isPrint?'':'<a class="btn -no-print" href="'.url('project/develop/'.$rs->refid).'"><i class="icon -viewdoc"></i>พัฒนาโครงการ</a>')
				:
				($isEdit?'<a id="project-todo-'.$rs->trid.'" class="btn sg-action'.($rs->title?'':' -hidden').' -no-print" href="'.url('project/planning/'.$rs->tpid.'/makedev',array('year'=>$planningInfo->info->pryear,'refid'=>$rs->trid,'budget'=>0,'group'=>$planningInfo->info->planGroup,'title'=>$rs->title)).'" data-confirm="ต้องการพัฒนาโครงการนี้ กรุณายืนยัน?"><i class="icon -add"></i>พัฒนาโครงการ</a>':'')
			);
			if ($isEdit) $row[]='<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$planningId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';
			$tables->rows[]=$row;
		}
		$ret.=$tables->build();
		if ($isEdit) {
			$ret.='<div class="-sg-text-right"><a class="sg-action add-tran" href="'.url('project/planning/'.$planningId.'/info/addtr/project').'" title="เพิ่ม" data-rel="#main"><i class="icon -addbig -white -circle -primary"></i></a></div>';
		}
		$ret.='</section><!-- box -->';


		$ret.='<section id="project-planning-view-develop" class="box -no-print">';
		$ret.='<h4>รายชื่อพัฒนาโครงการ</h4>';
		$stmt='SELECT
			*
			FROM %project_tr% tr
				RIGHT JOIN %project_dev% d USING(`tpid`)
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid`=:refid AND t.`orgid`=:orgid';
		$dbs=mydb::select($stmt,':refid',$planningInfo->info->planGroup, ':orgid',$planningInfo->info->orgid);
		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead=array('no'=>'','year -date' => 'ปีงบประมาณ','ชื่อพัฒนาโครงการ','money'=>'งบประมาณ (บาท)');
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					$rs->pryear+543,
					'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2)
				);
			}
			$ret.=$tables->build();
		} else {
			$ret.='ไม่มี';
		}
		$ret.='</section><!-- box -->';
		//$ret.=print_o($dbs,'$dbs');

		$stmt='SELECT
			p.`tpid`, p.`pryear`, t.`title`, p.`budget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE p.`prtype`="แผนงาน" AND t.`parent` = :tpid';
		$dbs = mydb::select($stmt,':tpid',$planningId);
		if ($dbs->count()) {
			$ret.='<section class="box">';
			$ret.='<h4>แผนงานย่อย</h4>';
			$tables = new Table();
			$tables->thead=array('year -date' => 'ปีงบประมาณ', 'ชื่อชุดโครงการ','budget -money'=>'งบประมาณ');
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					$rs->pryear+543,
					'<a href="'.url('project/planning/'.$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2)
				);
			}
			$ret.=$tables->build();
			$ret.='</section><!-- box -->';
		}

		$ret.='<section class="box">';
		$ret.='<h4>ชุดโครงการตามแผนงาน</h4>';
		$stmt='SELECT
			p.`tpid`, p.`pryear`, t.`title`, p.`budget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE p.`prtype`="ชุดโครงการ" AND t.`parent` = :tpid';
		$dbs=mydb::select($stmt,':tpid',$planningId);
		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead=array('year -date' => 'ปีงบประมาณ', 'ชื่อชุดโครงการ','budget -money'=>'งบประมาณ');
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					$rs->pryear+543,
					'<a href="'.url('project/set/'.$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2)
				);
			}
			$ret.=$tables->build();
		} else {
			$ret.='ไม่มี';
		}
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary -circle24" href="'.url('project/my/set/new',array('parent'=>$planningId)).'"><i class="icon -addbig -white"></i></a></nav>';
		}
		$ret.='</section><!-- box -->';


		$ret.='<section class="box">';
		$ret.='<h4>โครงการตามแผนงาน</h4>';
		$stmt='SELECT
						p.`tpid`, p.`pryear`, t.`title`, p.`budget`
						FROM %project% p
							LEFT JOIN %topic% t USING(`tpid`)
						WHERE p.`prtype`="โครงการ" AND t.`parent` = :tpid';
		$dbs=mydb::select($stmt,':tpid',$planningId);
		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead=array('no'=>'','year -date' => 'ปีงบประมาณ', 'ชื่อติดตามโครงการ','องค์กรรับผิดชอบ','money'=>'งบประมาณ (บาท)');
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					$rs->pryear+543,
					'<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>',
					$rs->orgnamedo,
					number_format($rs->budget,2)
				);
			}
			$ret.=$tables->build();
		} else {
			$ret.='ไม่มี';
		}
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary -circle24" href="'.url('project/my/project/new',array('parent'=>$planningId)).'"><i class="icon -addbig -white"></i></a></nav>';
		}
		$ret.='</section><!-- box -->';





		$ret.='<section id="project-plan-section" class="project-plan-section box">';
		$ret .= '<h4>กิจกรรมตามแผนงาน</h4>'._NL;
		$ret .= '<div id="project-plan" class="project-plan">'._NL;
			$ret .= '<div id="project-calendar-wrapper">'._NL;
			$ret .= R::Page('project.calendar', $self, $planningId)._NL;
			$ret .= '</div><!-- project-calendar-wrapper -->'._NL;

			$ret .= '<div>'._NL
				. view::inlineedit(
						array('group' => 'project', 'fld' => 'activity', 'ret' => 'html', 'class' => '-fill', 'placeholder' => 'กรณีที่ต้องการบรรยายรายละเอียดวิธีดำเนินการเพิ่มเติม ให้บันทึกไว้ในช่องบรรยายนี้'),
						$project->activity,
						$isEdit,
						'textarea')
				. '</div>'._NL;
		$ret .= '</div><!-- project-plan -->'._NL._NL;
		$ret .= '</section><!-- project-plan-section -->';

		$ret .= '</section><!-- project-detail-information -->';
		$ret .= '</article><!-- planning -->';

		//$ret.=print_o($planningInfo,'$planningInfo');

		$ret.='<style type="text/css">
		.project-detail-information .inline-edit-item {display: block;}
		.page.-main h2 {padding:8px;background-color:#666;color:#fff;text-align:center;}
		.box h4 {margin-bottom:4px; text-align:left;padding:8px;background:#bbb; color:#333;}
		.col-amt.-size,.col-amt.-target {width:1em;}
		.item.-objective th {white-space:nowrap;}
		.item.-objective td:first-child {width:1%;}
		.item.-objective td:nth-child(2) {width:30%;}
		.item.-objective td:nth-child(3) {width:40%;}
		.issue-problem {display: inline-block; padding: 7px 0;}
		.col.-amt .inline-edit-item {display: inline-block; padding: 7px 0;}
		.inline-edit .col.-amt .inline-edit-item {display: inline-block; padding: 0;}

		@media print {
			.page.-main h2 {color:#333; background-color: #fff; font-weight: bold;}
			.module-project .box {box-shadow: none; border: none;}
			.module-project h4 {color:#000; font-weight: bold;}
			.module-project .box h3 {color:#000; font-weight: bold; padding:0;}
		}
		</style>';

		$ret.='<script type="text/javascript">
		function planningIssueSizeUpdate($this,data,$parent) {
			var $ele = $("#problemsize-"+$this.data("updateid"));
			var $edtBtn = $this.closest("tr").find(".show-problem-detail")
			if ($ele.length==1) {
				var valueText = data.value == null ? "" : data.value
				$ele.text(valueText)
			}
			$edtBtn.show()
		}

		function projectPlanningProjectTitleChange($this,data,$parent) {
			var $ele=$("#project-todo-"+data.tr);
			if (data.value=="") {
				$ele.addClass("-hidden")
			} else {
				$ele.removeClass("-hidden")
			}
		}

		$(".show-problem-detail").click(function() {
			var $detailEle = $(this).closest("tr").find(".inline-edit-field.-textarea")
			$detailEle.toggleClass("-hidden")
			return false
		})

		</script>';

		//<a class="sg-action btn" href="'.url('project/create/'.$tpid, array('rel' => 'box')).'" data-rel="box" data-width="640" title="Create New Project"><i class="icon -material">add</i><span>เพิ่มโครงการ</span></a>
 		$floatingActionButton = new FloatingActionButton();
		if ($isViewOnly) {
			// Do nothing
		} else if ($isEdit) {
			$floatingActionButton->children('<a class="sg-action btn -floating" href="'.url('project/planning/'.$planningId,array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -material">done_all</i></a>');
		} else if ($isEditable) {
			if ($isDeleteable) $floatingActionButton->children('<a class="sg-action btn" href="'.url('project/planning/'.$planningId.'/info/delete').'" data-rel="notify" data-done="reload:'.url('org/'.$planningInfo->info->orgid.'/info.planning').'" data-title="ลบแผนงาน" data-confirm="ต้องการลบแผนงานนี้ กรุณายืนยัน?"><i class="icon -material">delete</i></a>');
			$floatingActionButton->children('<a class="sg-action btn -floating" href="'.url('project/planning/'.$planningId,array('mode'=>'edit')).'" data-rel="#main"><i class="icon -material">edit</i></a>');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->planningInfo->title,
				// 'trailing' => $appBarDropBox,
			]),
			'body' => new Widget([
				'children' => [
					$ret,
					new Container([
						'tagName' => 'section',
						'id' => 'project-docs',
						'class' => 'project-docs box',
						'children' => [
							'<h3>เอกสารประกอบแผนงาน</h3>',
							R::PageWidget('project.info.docs', [$this->planningInfo]),
						], // children
					]), // Container
					$floatingActionButton,
				],
			]),
		]);
		return $ret;
	}
}
?>