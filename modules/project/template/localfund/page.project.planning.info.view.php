<?php
/**
* Project :: Planning View information
* Created 2021-07-28
* Modify  2021-12-19
*
* @param Object $planningInfo
* @return Widget
*
* @usage project/planning/{id}[/{action}]
*/

import('package:project/fund/widgets/widget.fund.nav');
import('widget:project.like.status.php');

class ProjectPlanningInfoView extends Page {
	var $projectId;
	var $action;
	var $planningInfo;

	var $actionMode;
	var $isPrint = false;
	var $isEdit = false;
	var $isEditable = false;
	var $isDeleteable = false;

	function __construct($planningInfo, $action = NULL) {
		// parent::__construct();
		$this->projectId = $planningInfo->projectId;
		$this->planningInfo = $planningInfo;
		$this->action = $action;

		$this->actionMode = post('mode');
		$this->isPrint = $this->action == 'print';
		$this->isEdit = ($planningInfo->RIGHT & _IS_EDITABLE) || ($planningInfo->RIGHT & _IS_TRAINER);
		if ($this->isPrint) $this->isEdit = false;

		$this->isEditable = $this->isEdit && $this->actionMode == 'edit';
		$this->isDeleteable = ($planningInfo->RIGHT & _IS_EDITABLE) && empty($planningInfo->project);
	}

	function build() {

		$planningInfo = $this->planningInfo;
		$projectId = $this->projectId;

		if (empty($projectId)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

		// If Ampur Plan, Load Ampur Plan
		if (empty($this->planningInfo->orgId) && strlen($planningInfo->info->areacode) == 4) {
			return R::Page('project.planning.ampur.view', $planningInfo);
		}

		if ($this->action) {
			switch ($this->action) {
				case 'project.form':
					return $this->_projectForm();
					break;
			}
			return;
		}


		$optionEditIndicator = cfg('project.option.planning.editindicator');

		R::Model('reaction.add', $projectId, 'TOPIC.VIEW');


		// สถานการณ์ปัญหา
		$ret .= '<section class="box">';
		$ret .= '<header class="header"><h3 style="flex: 1">สถานการณ์ปัญหา</h3><nav class="nav" style="position: absolute; right: 0px;"><ul><li><a class="sg-action btn -no-print" href="'.url('project/planning/'.$projectId.'/info.compare').'" data-rel="box"><i class="icon -material">compare</i>สถานการณ์ปัญหาทุกปี</a></li></ul></nav></header>';

		$tables = new Table();
		$tables->thead = array('no'=>'','สถานการณ์ปัญหา');
		if ($this->isEdit) $tables->thead['icons -c1 -center'] = '';
		$tables->thead['amt -size'] = 'ขนาด';
		if ($this->isEdit) $tables->thead['icons -c2 -hover-parent'] = '';

		$no = 0;
		foreach ($planningInfo->problem as $rs) {
			if ($rs->process <= 0 || (empty($rs->trid) && !$this->isEdit)) continue;
			$detail = json_decode($rs->description);

			$row = [
				++$no,
				($rs->refid ?
					$detail->problem
				:
					view::inlineedit(
						[
							'group'=>'info:problem:'.$rs->catid,
							'fld'=>'detail1',
							'tr'=>$rs->trid,
							'class'=>'-fill',
							'options' => ['placeholder'=>'ระบุสถานการณ์ปัญหา',],
						],
						$rs->problem,
						$this->isEdit
					)
				)
				// แสดงรายละเอียดปัญหา
				. view::inlineedit(
					[
						'group' => 'info:problem:'.$rs->catid,
						'fld' => 'text1',
						'tr' => $rs->trid,
						'class' => '-problem-detail -fill'.($rs->detailproblem ? '' : ' -hidden'),
						'ret' => 'html',
						'options' => ['placeholder' => 'ระบุรายละเอียดสถานการณ์ปัญหา',],
					],
					$rs->detailproblem,
					$this->isEdit,
					'textarea'
				)
			];

			if ($this->isEdit) $row[] = '<nav class="nav -icons -no-print'.($rs->trid ? '' : ' -hidden').'"><ul><li><a class="btn -link show-problem-detail" href="javascript:void(0)"><i class="icon -material -gray">post_add</i></a></li></ul></nav>';

			$row[] = view::inlineedit(
				[
					'group'=>'info:problem:'.$rs->catid,
					'fld'=>'num1',
					'tr'=>$rs->trid,
					'refid'=>$rs->catid,
					'class'=>'-fill -numeric',
					'ret'=>'numeric',
					'blank'=>'NULL',
					'options' => ['placeholder'=>'?',],
					'updateid'=>$no,
					'min-value' => $detail->minValue != '' ? $detail->minValue : NULL,
					'max-value' => $detail->maxValue != '' ? $detail->maxValue : NULL,
					'callback'=>'planningIssueSizeUpdate',
				],
				$rs->trid && $rs->problemsize!='' ? number_format($rs->problemsize,2) : '',
				$this->isEdit
			);
			if ($this->isEdit) {
				$row[] = $rs->trid ? '<nav class="nav -icons -hover"><a class="sg-action btn -link" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-done="load:#main:'.url('project/planning/'.$projectId).'" data-title="ลบสถานการณ์" data-confirm="ต้องการลบสถานการณ์และวัตถุประสงค์รายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>':'';
			}

			$row['config'] = array('class' => $rs->trid ? '' : '-no-print');

			$tables->rows[] = $row;
		}


		if ($this->isEdit) {
			$tables->rows[] = [
				'<td></td>',
				'',
				'',
				'',
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info.problem.form').'" title="เพิ่มสถานการณ์ปัญหา" data-rel="box" data-width="480"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			];
		}

		$ret .= $tables->build();

		$ret .= view::inlineedit(
			[
				'group'=>'info:basic',
				'fld'=>'text1',
				'tr'=>$planningInfo->basic->trid,
				'class'=>'-fill',
				'ret'=>'html',
				'label'=>'รายละเอียดเพิ่มเติม',
				'options' => ['placeholder' => 'ระบุรายละเอียดภาพรวมของสถานการณ์ปัญหา'],
			],
			$planningInfo->basic->situation,
			$this->isEdit,
			'textarea'
		);

		$ret .= '</section><!-- box -->';




		// วัตถุประสงค์ - ตัวชี้วัด - เป้าหมาย
		$ret .= '<section class="box">';
		$ret .= '<h4>วัตถุประสงค์</h4>';
		$no = 0;
		$tables = new Table();
		$tables->addClass('-objective');
		$tables->thead = [
			'no'=>'',
			'วัตถุประสงค์',
			'ตัวชี้วัด',
			'amt -size'=>'ขนาด',
			'amt -target'=>'เป้าหมาย 1 ปี',
		];
		if ($this->isEdit) $tables->thead['icons -c1'] = '';

		foreach ($planningInfo->problem as $rs) {
			//$ret .= print_o($rs,'$rs');
			if ($rs->process <= 0 || (empty($rs->trid) && !$this->isEdit)) continue;
			//if (empty($rs->refid)) continue;

			$detail = json_decode($rs->description);

			$row = [
				++$no,
				$rs->refid
				?
				$detail->objective
				:
				view::inlineedit(
					[
						'group'=>'info:problem:'.$rs->refid,
						'fld'=>'detail2',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุวัตถุประสงค์',],
					],
					SG\getFirst($rs->objective,'เพื่อ(เพิ่ม/ลด)'.$rs->problem),
					$this->isEdit,
					'textarea'
				)
				,
				$rs->refid
				?
				view::inlineedit(
					[
						'group'=>'info:problem:'.$rs->refid,
						'fld'=>'text3',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุตัวชี้วัด',],
						'ret'=>'html',
					],
					$rs->indicator,
					$optionEditIndicator && $this->isEdit,
					'textarea'
				)
				:
				view::inlineedit(
					[
						'group'=>'info:problem:'.$rs->refid,
						'fld'=>'text3',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุตัวชี้วัด',],
						'ret'=>'html',
					],
					$rs->indicator,
					$this->isEdit,
					'textarea'
				),
				'<span id="problemsize-'.$no.'" class="issue-problem" data-group="info:problem:'.$rs->refid.'" data-tr="'.$rs->trid.'" data-refid="'.$rs->refid.'">'
				. ($rs->trid && $rs->problemsize != '' ? number_format($rs->problemsize,2) : '')
				. '</span>',
				//'<span>'.($rs->trid?number_format($rs->problemsize,2):'').'</span>',
				view::inlineedit(
					[
						'group'=>'info:problem:'.$rs->refid,
						'fld'=>'num2',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill -numeric',
						'ret'=>'numeric',
						'blank'=>'NULL',
						'options' => ['placeholder'=>'?',],
					],
					$rs->trid && $rs->targetsize!='' ? number_format($rs->targetsize,2):'',
					$this->isEdit
				)
			];

			if ($this->isEdit) $row[]='';
			$row['config'] = ['class'=>$rs->trid?'':'-no-print'];
			$tables->rows[]=$row;
		}

		foreach ($planningInfo->objective as $rs) {
			$row = [
				++$no,
				view::inlineedit(
					[
						'group'=>'info:objective',
						'fld'=>'detail1',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุวัตถุประสงค์',],
						'ret'=>'html',
					],
					$rs->title,
					$this->isEdit,
					'textarea'
				),
				view::inlineedit(
					[
						'group'=>'info:objective',
						'fld'=>'text1',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุตัวชี้วัด',],
						'ret'=>'html',
					],
					$rs->indicator,
					$this->isEdit,
					'textarea'
				),
				'',
				view::inlineedit(
					[
						'group'=>'info:problem',
						'fld'=>'num2',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill -numeric',
						'ret'=>'numeric',
						'options' => ['placeholder'=>'?',],
					],
					$rs->trid?number_format($rs->targetsize,2):'',
					$this->isEdit
				)
			];
			if ($this->isEdit) $row[] = '<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';

			$tables->rows[] = $row;
		}

		$ret .= $tables->build();

		/*
		// เปลี่ยนเป็นเพิ่มจากสถานการณ์
		if ($this->isEdit) {
			$ret.='<div class="-sg-text-right"><a class="sg-action add-tran" href="'.url('project/planning/'.$projectId.'/info/addtr/objective').'" title="เพิ่มวัตถุประสงค์" data-rel="#main"><i class="icon -addbig -white -circle -primary"></i></a></div>';
		}
		*/
		$ret.='</section><!-- box -->';



		// แนวทาง/วิธีการสำคัญ
		// ดึงจากค่าที่กำหนดไว้แล้ว
		$ret .= '<section class="box">';
		$ret .= '<h4>แนวทาง/วิธีการสำคัญ</h4>';
		$tables = new Table();
		$tables->addClass('-howto');
		$tables->thead = array('no'=>'','แนวทาง','วิธีการสำคัญ');
		if ($this->isEdit) $tables->thead['icons -hover-parent'] = '';
		$no = 0;
		$cardItem = '';
		foreach ($planningInfo->guideline as $rs) {
			$row=array(
				++$no,
				$rs->refid ? $rs->title : view::inlineedit(
					[
						'group'=>'info:guideline',
						'fld'=>'text1',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุแนวทาง',],
						'ret'=>'html',
					],
					$rs->title,
					$this->isEdit,
					'textarea'
				),
				view::inlineedit(
					[
						'group'=>'info:guideline',
						'fld'=>'text2',
						'tr'=>$rs->trid,
						'refid'=>$rs->refid,
						'class'=>'-fill',
						'ret'=>'numeric',
						'options' => ['placeholder'=>'ระบุวิธีการ',],
						'ret'=>'html',
					],
					$rs->action,
					$this->isEdit && ($rs->refid?$optionEditIndicator:true),
					'textarea'
				)
			);

			$cardItem.='<div>';
			$cardItem.='<div>';
			$cardItem.=$rs->refid ? '<h5>'.$no.'. '.$rs->title.'</h5>' : view::inlineedit(
				[
					'group'=>'info:guideline',
					'fld'=>'text1',
					'tr'=>$rs->trid,
					'refid'=>$rs->refid,
					'class'=>'-fill',
					'options' => ['placeholder'=>'ระบุแนวทาง',],
					'ret'=>'html',
				],
				$rs->title,
				$this->isEdit,
				'textarea'
			);
			$cardItem.='</div>';
			$cardItem.='<div>';
			$cardItem.=view::inlineedit(
				[
					'group'=>'info:guideline',
					'fld'=>'text2',
					'tr'=>$rs->trid,
					'refid'=>$rs->refid,
					'class'=>'-fill',
					'ret'=>'numeric',
					'options' => ['placeholder'=>'ระบุวิธีการ',],
					'ret'=>'html',
				],
				$rs->action,
				$this->isEdit && ($rs->refid?$optionEditIndicator:true),
				'textarea'
			);
			$cardItem.='</div>';
			$cardItem.='</div>';


			if ($this->isEdit) {
				$row[] = empty($rs->catid) ? '<nav class="nav -icons -hover"><ul><li><a class="btn -link sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a></li></ul></nav>':'';
			}
			$tables->rows[]=$row;
		}
		//$ret.=$cardItem;

		if ($this->isEdit) {
			$tables->rows[] = [
				'<td></td>',
				'',
				'',
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info/addtr/guideline').'" title="เพิ่มแนวทาง/วิธีการสำคัญ" data-rel="notify" data-done="load"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			];
		}
		$ret .= $tables->build();

		$ret.='</section><!-- box -->';




		// งบประมาณที่ตั้งไว้ตามแผนงาน
		$ret.='<section class="box">';
		$ret.='<h4>งบประมาณที่ตั้งไว้ตามแผนงาน (บาท)</h4>';
		$ret.=view::inlineedit(
			[
				'group'=>'project',
				'fld'=>'budget',
				'class'=>'-fill',
			],
			$planningInfo->info->budget,
			$this->isEdit
		);
		$ret.='</section><!-- box -->';



		// โครงการที่ควรดำเนินการ
		$ret.='<section class="box">';
		$ret.='<h4>โครงการที่ควรดำเนินการ</h4>';

		$tables = new Table();
		$tables->thead = [
			'no'=>'',
			'title'=>'ชื่อโครงการย่อย',
			'ผู้รับผิดชอบ',
			'money'=>'งบประมาณที่ตั้งไว้ (บาท)',
			'btncmd -hover-parent -no-print'=>''
		];
		if ($this->isEdit) $tables->thead['icons -hover-parent -no-print']='';
		$no=0;
		foreach ($planningInfo->project as $rs) {
			$row = [
				++$no,
				view::inlineedit(
					[
						'group'=>'info:project',
						'fld'=>'detail1',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุชื่อโครงการ',],
						'callback'=>'projectPlanningProjectTitleChange'],
						$rs->title,
						$this->isEdit
				),
				view::inlineedit(
					[
						'group'=>'info:project',
						'fld'=>'detail2',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'options' => ['placeholder'=>'ระบุชื่อผู้รับผิดชอบ',]
					],
					$rs->owner,
					$this->isEdit
				),

				view::inlineedit(
					[
						'group'=>'info:project',
						'fld'=>'num1',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'ret'=>'numeric',
						'options' => ['placeholder'=>'0.00',],
					],
					number_format($rs->budget,2),
					$this->isEdit
				),
				$rs->refid ?
					($this->isPrint ? '' : '<a class="btn -no-print" href="'.url('project/develop/'.$rs->refid).'"><i class="icon -viewdoc"></i>พัฒนาโครงการ</a>')
					:
					($this->isEdit ? '<a id="project-todo-'.$rs->trid.'" class="btn sg-action'.($rs->title?'':' -hidden').' -no-print" href="'.url('project/fund/'.$this->planningInfo->orgId.'/info/proposal.add', ['year'=>$planningInfo->info->pryear,'refid'=>$rs->trid,'budget'=>0,'group'=>$planningInfo->info->planGroup,'title'=>$rs->title]).'" data-title="สร้างพัฒนาโครงการ" data-confirm="ต้องการพัฒนาโครงการนี้ กรุณายืนยัน?"><i class="icon -add"></i>พัฒนาโครงการ</a>' : '')
			];

			if ($this->isEdit) $row[] = '<nav class="nav -icons -hover"><ul><li><a class="btn -link sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a></li></ul></nav>';
			$tables->rows[]=$row;
		}

		if ($this->isEdit) {
			$tables->rows[] = [
				'<td></td>',
				'',
				'',
				'',
				'',
				// '<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info/addtr/project').'" title="เพิ่มโครงการที่ควรดำเนินการ" data-rel="notify" data-done="load"><i class="icon -material">add_circle</i></a></li></ul></nav>'
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info.view/project.form').'" title="เพิ่มโครงการที่ควรดำเนินการ" data-rel="box" data-width="480"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			];
		}

		$ret .= $tables->build();

		$ret .= '</section><!-- box -->';



		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->planningInfo->title,
				'navigator' => new FundNavWidget((Object)['orgId' => $this->planningInfo->orgId ]),
			]),
			'body' => new Widget([
				'children' => [
					new Container([
						'tagName' => 'article',
						'id' => 'project-planning',
						'class' => 'project-planning'.($this->isEdit ? ' sg-inline-edit' : ''),
						'attribute' => [
							'data-tpid' => $this->isEdit ? $projectId : NULL,
							'data-update-url' =>  $this->isEdit ? url('project/edit/tr') : NULL,
							'data-debug' => $this->isEdit && debug('inline') ? 'inline' : NULL,
						], // attribute
						'children' => [

							'<h2>'.$planningInfo->title.'<br />ประจำปีงบประมาณ '.($planningInfo->info->pryear+543).'</h2>',

							new ScrollView([
								'child' => new ProjectLikeStatusWidget([
									'projectInfo' => $this->planningInfo,
								]),
							]),

							new ScrollView([
								'child' => $ret,
							]),

						], // children
					]), // Container
					new FloatingActionButton([
						'children' => (function() {
							$widgets = [];
							if ($isViewOnly) {
								// Do nothing
							} else if ($this->isEditable) {
								$widgets[] = '<a class="sg-action btn -floating -circle48" href="'.url('project/planning/'.$this->projectId, ['debug' => post('debug')]).'" data-rel="#main"><i class="icon -material -white">done_all</i></a>';
							} else if ($this->isEdit) {
								if ($this->isDeleteable) {
									$widgets[] = '<a class="sg-action btn -btn-delete -floating -circle48" href="'.url('project/planning/'.$this->projectId.'/info/delete').'" data-rel="notify" data-done="reload:'.url('project/fund/'.$this->planningInfo->info->orgid.'/planning').'" data-title="ลบแผนงาน" data-confirm="ต้องการลบแผนงานนี้ กรุณายืนยัน?"><i class="icon -material -white">delete</i></a>';
								}
								$widgets[] = '<a class="sg-action btn -floating -circle48" href="'.url('project/planning/'.$this->projectId, ['mode' => 'edit']).'" data-rel="#main"><i class="icon -material -white">edit</i></a>';
							}
							return $widgets;
						})(),
					]), // FloatingActionButton

					$this->proposalWidget(),
					$this->followWidget(),

					$this->_script(),
				], // children
			]),
		]);
	}


	function proposalWidget() {
		// รายชื่อพัฒนาโครงการ
		$ret .= '<section class="box -no-print">';
		$ret .= '<h4>รายชื่อพัฒนาโครงการ</h4>';
		$stmt = 'SELECT
			d.`tpid`, d.`pryear`, t.`title`, d.`budget`
			FROM %project_dev% d
				RIGHT JOIN %project_tr% tr ON tr.`tpid` = d.`tpid` AND tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid` = :refid
				LEFT JOIN %topic% t ON t.`tpid` = d.`tpid`
			WHERE t.`orgid` = :orgid AND d.`pryear` = :pryear;
			-- {sum: "budget"}';

		$dbs = mydb::select($stmt,
			':refid', $this->planningInfo->info->planGroup,
			':orgid', $this->planningInfo->info->orgid,
			':pryear', $this->planningInfo->info->pryear
		);

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
			$tables->tfoot[] = ['<td></td>','','รวม',number_format($dbs->sum->budget,2)];
			$ret .= $tables->build();
		} else {
			$ret.='ไม่มี';
		}
		$ret.='</section><!-- box -->';
		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}

	function followWidget() {
		// รายชื่อติดตามโครงการ
		$ret .= '<section class="box -no-print">';
		$ret .= '<h4>รายชื่อติดตามโครงการ</h4>';
		$stmt = 'SELECT
			p.`pryear`, p.`tpid`, t.`title`, p.`orgnamedo`, p.`budget`
			FROM %project% p
				RIGHT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid`="info" AND tr.`part`="supportplan" AND tr.`refid` = :refid
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE p.`prtype` = "โครงการ" AND t.`orgid` = :orgid AND p.`pryear` = :pryear;
			-- {sum: "budget"}';

		$dbs = mydb::select($stmt,
			':refid', $this->planningInfo->info->planGroup,
			':orgid',$this->planningInfo->info->orgid,
			':pryear', $this->planningInfo->info->pryear
		);

		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead=array('no'=>'','year -date' => 'ปีงบประมาณ', 'ชื่อติดตามโครงการ','องค์กรรับผิดชอบ','money'=>'งบประมาณ (บาท)');
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					$rs->pryear+543,
					'<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.short').'" data-rel="box" data-width="640">'.$rs->title.'</a>',
					$rs->orgnamedo,
					number_format($rs->budget,2)
				);
			}
			$tables->tfoot[] = ['<td></td>','','รวม','',number_format($dbs->sum->budget,2)];
			$ret .= $tables->build();
		} else {
			$ret.='ไม่มี';
		}
		$ret.='</section><!-- box -->';
		return $ret;
	}

	function _projectForm() {
		return new Form([
			'class' => 'sg-form',
			'action' => url('project/planning/'.$this->projectId.'/info/project.save'),
			'rel' => 'none',
			'done' => 'load | close',
			'checkValid' => true,
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>โครงการที่ควรดำเนินการ</h3></header>',
				'title' => [
					'type' => 'text',
					'class' => '-fill',
					'label' => 'ชื่อโครงการที่ควรดำเนินการ',
					'require' => true,
					'placeholder' => 'ระบุชื่อโครงการที่ควรดำเนินการ',
				],
				'supportType' => [
					'type' => 'radio',
					'label' => 'ประเภทการสนับสนุน',
					'require' => true,
					'options' => 		$supportTypeNameList = model::get_category('project:supporttype','catid'),
				],
				'orgnamedo' => [
					'type' => 'text',
					'label' => 'หน่วยงาน/บุคคลผู้รับผิดชอบ',
					'class' => '-fill',
					'placeholder' => 'ระบุชื่อหน่วยงาน/บุคคลผู้รับผิดชอบ'
				],
				'budget' => [
					'type' => 'text',
					'label' => 'งบประมาณที่ตั้งไว้(บาท)',
					'class' => '-fill',
					'require' => true,
					'placeholder' => '0.00',
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function _script() {
		return
		'<style type="text/css">
		.inline-edit-item {display: block;}
		.page.-main h2 {padding:8px;background-color:#666;color:#fff;text-align:center;}
		.box h4 {margin-bottom:4px; text-align:left;padding:8px;background:#bbb; color:#333;}
		.col-amt.-size,.col-amt.-target {width:1em;}
		.item.-objective th {white-space:nowrap;}
		.item.-objective td:first-child {width:1%;}
		.item.-objective td:nth-child(2) {width:30%;}
		.item.-objective td:nth-child(3) {width:40%;}
		.issue-problem {display: inline-block; padding: 7px 0;}
		.item.-howto td:nth-child(2) {width:30%;}
		.item.-howto td:nth-child(3) {width:70%;}
		.col.-amt .inline-edit-item {display: inline-block; padding: 7px 0;}
		.inline-edit .col.-amt .inline-edit-item {display: inline-block; padding: 0;}

		.btn-floating.-btn-planning:hover .btn.-btn-delete {display: inline-block;}

		@media print {
			.page.-main h2 {color:#333; background-color: #fff; font-weight: bold;}
			.module-project .box {box-shadow: none; border: none;}
			.module-project h4 {color:#000; font-weight: bold;}
			.module-project .box h3 {color:#000; font-weight: bold; padding:0;}
		}
		</style>

		<script type="text/javascript">
		function planningIssueSizeUpdate($this,data,$parent) {
			var $ele = $("#problemsize-"+$this.data("updateid"));
			var $edtBtn = $this.closest("tr").find(".show-problem-detail").closest("nav")
			$this.closest("tr").removeClass("-no-print")
			$ele.closest("tr").removeClass("-no-print")
			if ($ele.length == 1) {
				var valueText = data.value == null ? "" : data.value
				$ele.text(valueText)
			}
			if (data.value == null) {
				$edtBtn.hide()
			} else {
				$edtBtn.show()
			}
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
			$detailEle.toggle("-hidden")
			return false
		})

		</script>';
	}
}
?>