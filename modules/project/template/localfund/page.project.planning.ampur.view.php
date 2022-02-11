<?php
/**
* Project :: View Ampur Planning
* Created 2021-05-18
* Modify  2021-05-18
*
* @param Object $planningInfo
* @return String
*
* @usage project/planning/{id}
*/

$debug = true;

import('widget:project.like.status.php');

class ProjectPlanningAmpurView extends Page {
	var $planningInfo;

	function __construct($planningInfo) {
		$this->planningInfo = $planningInfo;
	}

	function build() {
		$planningInfo = $this->planningInfo;
		$projectId = $planningInfo->projectId;
		$actionMode = SG\getFirst($_SESSION['mode'],post('mode'));
		unset($_SESSION['mode']);

		if (empty($projectId)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

		$isPrint = $action == 'print';
		$isAdmin = $planningInfo->RIGHT & _IS_ADMIN;
		$isEdit = ($planningInfo->RIGHT & _IS_EDITABLE) || ($planningInfo->RIGHT & _IS_TRAINER);
		if ($isPrint) $isEdit = false;

		$isEditable = $isEdit && $actionMode == 'edit';
		$isDeleteable = ($planningInfo->RIGHT & _IS_EDITABLE) && empty($planningInfo->project);

		$optionEditIndicator = cfg('project.option.planning.editindicator');

		$inlineAttr = array();
		$inlineAttr['class'] = 'project-planning ';

		if ($isEditable) {
			$inlineAttr['data-tpid'] = $projectId;
			$inlineAttr['class'] .= 'sg-inline-edit';
			$inlineAttr['data-update-url'] = url('project/edit/tr');
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}


		R::Model('reaction.add', $projectId, 'TOPIC.VIEW');

		$addBtn = '<a class="tran-remove -hidden" href="" data-rel="none" data-removeparent="tr"><i class="icon -cancel -gray"></i></a><a class="add-tran" href="javascript:void(0)" title="เพิ่ม"><i class="icon -addbig -primary -circle"></i></a>';


		$calculateNav = new Container([
			'tagName' => 'nav',
			'class' => 'nav',
			'style' => 'position: absolute; right: 0px;',
			'child' => new Ui([
				'children' => [
					'<a class="btn" onClick="showAmpurSituation()"><i class="icon -material">trending_up</i><span>แนวโน้ม</span></a>',
					$isEditable ? '<a class="btn" onClick="calculateSituation()"><i class="icon -material">calculate</i>คำนวณสถานการณ์</a>' : NULL,
					//['text' => 'hello', 'options' => (Object)['class' => 'ui-class']],
				],
			]),
		]);

		//debugMsg($calculateNav->children[0], '$calculateUi');

			// '<nav class="nav -no-print" style="position: absolute; right: 0px;">'
			// . '<ul><li><a class="btn" onClick="calculateSituation()"><i class="icon -material">calculate</i>คำนวณสถานการณ์ปัญหา</a></li></ul>'
			// . '</nav>';

		$ret .= '<article id="project-planning" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret .= '<h2>'.$planningInfo->title.'<br />ประจำปีงบประมาณ '.($planningInfo->info->pryear+543).'</h2>';

		$ret .= (new ScrollView([
			'child' => new ProjectLikeStatusWidget([
				'projectInfo' => $planningInfo,
			]),
		]))->build();

		// สถานการณ์ปัญหา
		$ret .= '<section class="box">';
		$ret .= '<header class="header"><h3 style="flex: 1">สถานการณ์ปัญหา</h3>'
			. $calculateNav->build()
			. '</header>';

		$tables = new Table();
		$tables->addId('situation');
		$tables->thead = array('no'=>'','สถานการณ์ปัญหา');
		if ($isEditable) $tables->thead['icons -c1 -center -detail'] = '';
		$tables->thead['size -amt'] = 'ขนาด';
		$tables->thead['cal -amt'] = 'คำนวณ';
		if ($isEditable) $tables->thead['icons -center -c2 -hover-parent'] = '<a class="btn -primary -hidden"><i class="icon -material">done_all</i></a>';

		$no = 0;
		foreach ($planningInfo->problem as $rs) {
			if ($rs->process<=0 || (empty($rs->trid) && !$isEditable)) continue;
			$detail = json_decode($rs->description);

			$row = array(
				++$no,
				($rs->refid ?
					$detail->problem
				:
					view::inlineedit(
						array(
							'group'=>'info:problem:'.$rs->catid,
							'fld'=>'detail1',
							'tr'=>$rs->trid,
							'class'=>'-fill',
							'placeholder'=>'ระบุสถานการณ์ปัญหา',
						),
						$rs->problem,
						$isEditable
					)
				)
				// แสดงรายละเอียดปัญหา
				. view::inlineedit(
					array(
						'group' => 'info:problem:'.$rs->catid,
						'fld' => 'text1',
						'tr' => $rs->trid,
						'class' => '-problem-detail -fill'.($rs->detailproblem ? '' : ' -hidden'),
						'ret' => 'html',
						'placeholder' => 'ระบุรายละเอียดสถานการณ์ปัญหา'
					),
					$rs->detailproblem,
					$isEditable,
					'textarea'
				)
			);

			if ($isEditable) $row[] = '<nav class="nav -icons -no-print'.($rs->trid ? '' : ' -hidden').'"><ul><li><a class="btn -link show-problem-detail" href="javascript:void(0)"><i class="icon -material -gray">post_add</i></a></li></ul></nav>';
			$row[] = view::inlineedit(
				array('group'=>'info:problem:'.$rs->catid,'fld'=>'num1','tr'=>$rs->trid,'refid'=>$rs->catid,'class'=>'-fill -numeric','ret'=>'numeric','blank'=>'NULL','placeholder'=>'?','updateid'=>$no,'callback'=>'planningIssueSizeUpdate'),
				$rs->trid && $rs->problemsize!='' ? number_format($rs->problemsize,2) : '',
				$isEditable
			);
			//view::inlineedit(array('group'=>'info:problem','fld'=>'num2','tr'=>$rs->trid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'0.00'),$rs->targetsize,$isEditable),
			$row[] = '';
			if ($isEditable) {
				$row[] = $rs->trid ? '<nav class="nav -icons -hover"><a class="btn -link sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="notify" data-done="callback:deleteProblem" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" title="ลบรายการ"><i class="icon -cancel -gray"></i></a></nav>':'';
			}

			$row['config'] = array('class' => $rs->trid ? '' : '-no-print');

			$tables->rows[] = $row;
		}


		if ($isEditable) {
			$tables->rows[] = array(
				'<td></td>',
				'',
				'',
				'',
				'',
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info.problem.form').'" title="เพิ่มสถานการณ์ปัญหา" data-rel="box" data-width="480"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			);
		}
		$ret .= $tables->build();

		$ret .= view::inlineedit(
			array('group'=>'info:basic','fld'=>'text1','tr'=>$planningInfo->basic->trid,'class'=>'-fill','ret'=>'html','label'=>'รายละเอียดเพิ่มเติม'),
			$planningInfo->basic->situation,
			$isEditable,
			'textarea'
		);

		$ret .= '</section><!-- box -->';




		// วัตถุประสงค์ - ตัวชี้วัด - เป้าหมาย
		$ret .= '<section class="box">';
		$ret .= '<h4>วัตถุประสงค์</h4>';
		$no = 0;
		$tables = new Table();
		$tables->addId('objective');
		$tables->addClass('-objective');
		$tables->thead = array('no'=>'','วัตถุประสงค์','ตัวชี้วัด','amt -size'=>'ขนาด','amt -target'=>'เป้าหมาย 1 ปี');
		if ($isEditable) $tables->thead['icons -c1'] = '';

		foreach ($planningInfo->problem as $rs) {
			//$ret .= print_o($rs,'$rs');
			if ($rs->process <= 0 || (empty($rs->trid) && !$isEditable)) continue;
			//if (empty($rs->refid)) continue;

			$detail = json_decode($rs->description);

			$row = array(
				++$no,
				$rs->refid
				?
				$detail->objective
				:
				view::inlineedit(
					array('group'=>'info:problem:'.$rs->refid,'fld'=>'detail2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุวัตถุประสงค์'),
					SG\getFirst($rs->objective,'เพื่อ(เพิ่ม/ลด)'.$rs->problem),
					$isEditable,
					'textarea'
				)
				,
				$rs->refid
				?
				view::inlineedit(
					array('group'=>'info:problem:'.$rs->refid,'fld'=>'text3','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุตัวชี้วัด','ret'=>'html'),
					$rs->indicator,
					$optionEditIndicator && $isEditable,
					'textarea'
				)
				:
				view::inlineedit(
					array('group'=>'info:problem:'.$rs->refid,'fld'=>'text3','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุตัวชี้วัด','ret'=>'html'),
					$rs->indicator,
					$isEditable,
					'textarea'
				),
				//view::inlineedit(array('group'=>'info:problem:'.$rs->catid,'tr'=>$rs->trid,'refid'=>$rs->catid,'class'=>'-fill','placeholder'=>'?'),$rs->trid?number_format($rs->problemsize,2):'',false),
				'<span id="problemsize-'.$no.'" class="issue-problem" data-group="info:problem:'.$rs->refid.'" data-tr="'.$rs->trid.'" data-refid="'.$rs->refid.'">'
				. ($rs->trid && $rs->problemsize != '' ? number_format($rs->problemsize,2) : '')
				. '</span>',
				//'<span>'.($rs->trid?number_format($rs->problemsize,2):'').'</span>',
				view::inlineedit(
					array('group'=>'info:problem:'.$rs->refid,'fld'=>'num2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill -numeric','ret'=>'numeric','blank'=>'NULL', 'placeholder'=>'?'),
					$rs->trid && $rs->targetsize!='' ? number_format($rs->targetsize,2):'',
					$isEditable
				)
			);

			if ($isEditable) $row[]='';
			$row['config']=array('class'=>$rs->trid?'':'-no-print');
			$tables->rows[]=$row;
		}
		foreach ($planningInfo->objective as $rs) {
			$row=array(
				++$no,
				view::inlineedit(
					array('group'=>'info:objective','fld'=>'detail1','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุวัตถุประสงค์','ret'=>'html'),
					$rs->title,
					$isEditable,
					'textarea'
				),
				view::inlineedit(
					array('group'=>'info:objective','fld'=>'text1','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุตัวชี้วัด','ret'=>'html'),
					$rs->indicator,
					$isEditable,
					'textarea'
				),
				'',
				view::inlineedit(
					array('group'=>'info:problem','fld'=>'num2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill -numeric','ret'=>'numeric','placeholder'=>'?'),
					$rs->trid?number_format($rs->targetsize,2):'',
					$isEditable
				)
			);
			if ($isEditable) $row[] = '<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';

			$tables->rows[] = $row;
		}

		$ret .= $tables->build();

		/*
		// เปลี่ยนเป็นเพิ่มจากสถานการณ์
		if ($isEditable) {
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
		if ($isEditable) $tables->thead['icons -hover-parent'] = '';
		$no = 0;
		$cardItem = '';
		foreach ($planningInfo->guideline as $rs) {
			$row=array(
				++$no,
				$rs->refid?$rs->title:view::inlineedit(array('group'=>'info:guideline','fld'=>'text1','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุแนวทาง','ret'=>'html'),$rs->title,$isEditable,'textarea'),
				view::inlineedit(array('group'=>'info:guideline','fld'=>'text2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'ระบุวิธีการ','ret'=>'html'),$rs->action,$isEditable && ($rs->refid?$optionEditIndicator:true),'textarea')
			);

			$cardItem.='<div>';
			$cardItem.='<div>';
			$cardItem.=$rs->refid?'<h5>'.$no.'. '.$rs->title.'</h5>':view::inlineedit(array('group'=>'info:guideline','fld'=>'text1','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','placeholder'=>'ระบุแนวทาง','ret'=>'html'),$rs->title,$isEditable,'textarea');
			$cardItem.='</div>';
			$cardItem.='<div>';
			$cardItem.=view::inlineedit(array('group'=>'info:guideline','fld'=>'text2','tr'=>$rs->trid,'refid'=>$rs->refid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'ระบุวิธีการ','ret'=>'html'),$rs->action,$isEditable && ($rs->refid?$optionEditIndicator:true),'textarea');
			$cardItem.='</div>';
			$cardItem.='</div>';


			if ($isEditable) {
				$row[] = empty($rs->catid) ? '<nav class="nav -icons -hover"><ul><li><a class="btn -link sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a></li></ul></nav>':'';
			}
			$tables->rows[]=$row;
		}
		//$ret.=$cardItem;

		if ($isEditable) {
			$tables->rows[] = array(
				'<td></td>',
				'',
				'',
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info/addtr/guideline').'" title="เพิ่มแนวทาง/วิธีการสำคัญ" data-rel="notify" data-done="load:#main:'.url('project/planning/'.$projectId,['mode'=>'edit']).'"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			);
		}
		$ret .= $tables->build();

		$ret.='</section><!-- box -->';




		// // งบประมาณที่ตั้งไว้ตามแผนงาน
		// $ret.='<section class="box">';
		// $ret.='<h4>งบประมาณที่ตั้งไว้ตามแผนงาน (บาท)</h4>';
		// $ret.=view::inlineedit(array('group'=>'project','fld'=>'budget','class'=>'-fill'),$planningInfo->info->budget,$isEditable);
		// $ret.='</section><!-- box -->';



		// โครงการที่ควรดำเนินการ
		$ret.='<section class="box">';
		$ret.='<h4>โครงการที่ควรดำเนินการ</h4>';

		$tables = new Table();
		$tables->thead = array(
			'no'=>'',
			'title'=>'ชื่อโครงการย่อย',
			'ผู้รับผิดชอบ',
			'money'=>'งบประมาณที่ตั้งไว้ (บาท)',
			'btncmd -hover-parent -no-print'=>''
		);
		if ($isEditable) $tables->thead['icons -hover-parent -no-print']='';
		$no=0;
		foreach ($planningInfo->project as $rs) {
			$row=array(
				++$no,
				view::inlineedit(array('group'=>'info:project','fld'=>'detail1','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุชื่อโครงการ','callback'=>'projectPlanningProjectTitleChange'),$rs->title,$isEditable),
				view::inlineedit(array('group'=>'info:project','fld'=>'detail2','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุชื่อผู้รับผิดชอบ'),$rs->owner,$isEditable),
				view::inlineedit(array('group'=>'info:project','fld'=>'num1','tr'=>$rs->trid,'class'=>'-fill','ret'=>'numeric','placeholder'=>'0.00'),number_format($rs->budget,2),$isEditable),
				$rs->refid ?
					($isPrint ? '' : '<a class="btn -no-print" href="'.url('project/develop/'.$rs->refid).'"><i class="icon -viewdoc"></i>พัฒนาโครงการ</a>')
					:
					($isEditable ? '<a id="project-todo-'.$rs->trid.'" class="btn sg-action'.($rs->title?'':' -hidden').' -no-print" href="'.url('project/fund/'.$planningInfo->info->orgid.'/info/proposal.add',array('year'=>$planningInfo->info->pryear,'refid'=>$rs->trid,'budget'=>0,'group'=>$planningInfo->info->planGroup,'title'=>$rs->title)).'" data-title="สร้างพัฒนาโครงการ" data-confirm="ต้องการพัฒนาโครงการนี้ กรุณายืนยัน?"><i class="icon -add"></i>พัฒนาโครงการ</a>' : '')
			);
			if ($isEditable) $row[] = '<nav class="nav -icons -hover"><ul><li><a class="btn -link sg-action" href="'.url('project/planning/'.$projectId.'/info/removetr/'.$rs->trid).'" data-rel="none" data-done="remove:parent tr" data-title="ลบโครงการที่ควรดำเนินการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></li></ul></nav>';
			$tables->rows[]=$row;
		}

		if ($isEditable) {
			$tables->rows[] = array(
				'<td></td>',
				'',
				'',
				'',
				'',
				'<nav class="nav -no-print"><ul><li><a class="sg-action btn -primary -circle24" href="'.url('project/planning/'.$projectId.'/info/addtr/project').'" title="เพิ่มโครงการที่ควรดำเนินการ" data-rel="notify" data-done="load:#main:'.url('project/planning/'.$projectId,['mode'=>'edit']).'"><i class="icon -material">add_circle</i></a></li></ul></nav>'
			);
		}

		$ret .= $tables->build();

		$ret .= '</section><!-- box -->';



		// รายชื่อพัฒนาโครงการ
		$ret .= '<section class="box -no-print">';
		$ret .= '<h4>รายชื่อพัฒนาโครงการ 	ปีงบประมาณ '.($planningInfo->info->pryear+543).'</h4>';
		$stmt = 'SELECT
			d.`tpid`, d.`pryear`, t.`title`, d.`budget`
			FROM %project_dev% d
				RIGHT JOIN %project_tr% tr ON tr.`tpid` = d.`tpid` AND tr.`formid`="develop" AND tr.`part`="supportplan" AND tr.`refid` = :refid
				LEFT JOIN %topic% t ON t.`tpid` = d.`tpid`
			WHERE d.`changwat` = :changwat AND d.`ampur` = :ampur AND d.`pryear` = :pryear;
		-- {sum: "budget"}';

		$dbs = mydb::select($stmt,
			':refid', $planningInfo->info->planGroup,
			':changwat', substr($planningInfo->info->areacode,0,2),
			':ampur', substr($planningInfo->info->areacode,2,2),
			':pryear', $planningInfo->info->pryear
		);

		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead = ['no'=>'','ชื่อพัฒนาโครงการ','budget -money'=>'งบประมาณ (บาท)'];
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('project/develop/'.$rs->tpid).'">'.$rs->title.'</a>',
					number_format($rs->budget,2)
				);
			}
			$tables->tfoot[] = ['<td></td>','รวม',number_format($dbs->sum->budget,2)];
			$ret.=$tables->build();
		} else {
			$ret.='ไม่มี';
		}
		$ret.='</section><!-- box -->';
		//$ret.=print_o($dbs,'$dbs');



		// รายชื่อติดตามโครงการ
		$ret .= '<section class="box -no-print">';
		$ret .= '<h4>รายชื่อติดตามโครงการ 	ปีงบประมาณ '.($planningInfo->info->pryear+543).'</h4>';
		$stmt = 'SELECT
			p.`pryear`, p.`tpid`, t.`title`, p.`orgnamedo`, p.`budget`
			, o.`name` `orgName`
			FROM %project% p
				RIGHT JOIN %project_tr% tr ON tr.`tpid` = p.`tpid` AND tr.`formid`="info" AND tr.`part`="supportplan" AND tr.`refid` = :refid
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			WHERE p.`prtype` = "โครงการ" AND LEFT(t.`areacode`,4) = :ampurcode AND p.`pryear` = :pryear;
		-- {sum: "budget"}';

		$dbs = mydb::select($stmt,
			':refid', $planningInfo->info->planGroup,
			':ampurcode',$planningInfo->info->areacode,
			':pryear', $planningInfo->info->pryear
		);

		if ($dbs->count()) {
			$tables = new Table();
			$tables->thead = ['no'=>'', 'ชื่อติดตามโครงการ','budget -money'=>'งบประมาณ (บาท)'];
			$no=0;
			foreach ($dbs->items as $rs) {
				$tables->rows[] = [
					++$no,
					'<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.short').'" data-rel="box" data-width="640">'.$rs->title.'</a><br /><em>'.$rs->orgnamedo.'/'.$rs->orgName.'</em>',
					number_format($rs->budget,2)
				];
			}
			$tables->tfoot[] = ['<td></td>','รวม',number_format($dbs->sum->budget,2)];
			$ret .= $tables->build();
		} else {
			$ret.='ไม่มี';
		}
		$ret.='</section><!-- box -->';

		$ret.='</article><!-- planning -->';

		if ($isViewOnly) {
			// Do nothing
		} else if ($isEditable) {
			$ret .= (new FloatingActionButton([
				'children' => [
					new Button([
						'url' => url('project/planning/'.$projectId,array('debug'=>post('debug'))),
						'icon' => 'done_all',
						'class' => 'sg-action -floating',
						'rel' => '#main'
					])
				],
			]))->build();
		} else if ($isEdit) {
			$ret .= (new FloatingActionButton([
				'children' => [
					$isDeleteable ? new Button([
						'url' => url('project/planning/'.$projectId.'/info/delete'),
						'icon' => 'delete',
						'class' => 'sg-action -floating',
						'rel' => 'none',
						'done' => 'reload:'.url('project/planning/ampur'),
						'data-title' => 'ลบแผนงาน',
						'data-confirm' => 'ต้องการลบแผนงานนี้ กรุณายืนยัน?',
					]) : NULL,
					new Button([
						'url' => url('project/planning/'.$projectId,array('mode'=>'edit')),
						'icon' => 'edit',
						'class' => 'sg-action -floating',
						'rel' => '#main',
					]),
				]
			]))->build();
		}

		//$ret.=print_o($planningInfo,'$planningInfo');

		$ret.='<style type="text/css">
		.inline-edit-item {display: block;}
		.page.-main h2 {padding: 8px; margin: 4px 0; background-color: #666; color: #fff; text-align: center;}
		.box h4 {margin-bottom:4px; text-align:left;padding:8px;background:#bbb; color:#333;}
		.col-amt.-size,.col-amt.-target {width:1em;}
		.item .col.-cal, .item .col.-icons {padding-top: 22px;}
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
		</style>';

		$ret.='<script type="text/javascript">
		function planningIssueSizeUpdate($this,data,$parent) {
			var $ele = $("#problemsize-"+$this.data("updateid"));
			var $edtBtn = $this.closest("tr").find(".show-problem-detail")
			$this.closest("tr").removeClass("-no-print")
			$ele.closest("tr").removeClass("-no-print")
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

		function deleteProblem($this, data) {
			let $row = $this.closest("tr")
			let problemValue = $row.find("td.-cal").text()
			let $target = $row.find("td.-size .inline-edit-field")

			$target.data("value", "").children("span").html("<span class=\"placeholder -no-print\">...</span>")
			$(".issue-problem[data-refid="+$target.data("refid")+"]").text("")
			$row.find("td.-icons.-detail").children().hide()
			$this.hide()


			console.log("DELETE")
		}

		function showAmpurSituation() {
			let url = "'.url('project/api/planning/situations.summary',['plan' => $planningInfo->info->planGroup , 'ampur' => $planningInfo->info->areacode, 'group' => 'year/problem']).'"
			let para = {}
			notify("กำลังโหลดข้อมูล")
			$.get(url, function(data) {
				notify()
				let allYear = []
				let allPlan = []

				let table = $("<table />").addClass("item")
				let thead = $("<thead />")
				let headRow = $("<tr />")
					.append($("<th />").attr("rowspan",2))
					.append($("<th />").attr("rowspan",2).text("สถานการณ์ปัญหา"))
				let headRow2 = $("<tr />")
				for (let item of data.summary) {
					if (allYear.indexOf(item.year) == -1) {
						allYear.push(item.year)
						headRow2
							.append($("<th />").addClass("-nowrap").text("ขนาด"))
							.append($("<th />").addClass("-nowrap").text("เป้าหมาย"))
					}
					if (allPlan.indexOf(item.situationName) == -1) {
						allPlan.push(item.situationName)
					}
				}
				for (let year of allYear) {
					headRow.append($("<th />").attr("colspan",2).text(year + 543))
				}
				thead.append(headRow)
				thead.append(headRow2)
				table.append(thead)

				let body = $("<tbody />")
				let no = 0
				for (let plan of allPlan) {
					let row = $("<tr />")
					row.append($("<td />").addClass("col -no").text(++no))
						.append($("<td />").text(plan))
					for (let year of allYear) {
						let rowItem = null
						for (let item of data.summary) {
							if (item.year == year && item.situationName == plan) {
								rowItem = item
							}
						}
						if (rowItem != null) {
							row.append($("<td />").addClass("col -center").text(rowItem.ampurProblem != null ? rowItem.ampurProblem : "-"))
								.append($("<td />").addClass("col -center").text(rowItem.ampurTarget != null ? rowItem.ampurTarget : "-"))
						} else {
							row.append($("<td />").addClass("col -center").text("-"))
								.append($("<td />").addClass("col -center").text("-"))
						}
					}
					body.append(row)
				}
				table.append(body)
				//console.log(table.html())
				sgShowBox($("<div />")
					.append($("<header />").addClass("header").append($("<h3 />").text("สถานการณ์ปัญหาของอำเภอ")))
					.append(table).html()
				)
				console.log(data)
			}, "json")
			return false
		}

		function calculateSituation() {
			let url = "'.url('project/api/planning/situations.summary',['plan' => $planningInfo->info->planGroup , 'ampur' => $planningInfo->info->areacode, 'year' => $planningInfo->info->pryear, 'group' => 'problem']).'"
			notify("กรุณารอสักครู่...")
			console.log(url)
			$.get(url, function(data) {
				notify()
				for (let value of data.summary) {
					if (value.ampurProblem === null) continue
					$target = $("#situation .inline-edit-field[data-refid="+value.problemId+"]")
					$target.closest("tr").find("td.-cal").html(value.ampurProblem)
					$target.closest("tr").find("td:last-child").html("<a class=\"btn -link -circle24\" onClick=\"updateCalSituation(this)\"><i class=\"icon -material\">task_alt</i></a>")
				}
			}, "json")
			return false
		}

		function updateCalSituation(e) {
			let $row = $(e).closest("tr")
			let problemValue = $row.find("td.-cal").text()
			let $target = $row.find("td.-size .inline-edit-field")
			let $inlineedit = $row.closest(".sg-inline-edit")
			let projectId = $inlineedit.data("tpid")
			let updateUrl = $inlineedit.data("updateUrl")

			$target.data("value", problemValue).children("span").text(problemValue)
			$target.sgInlineEdit()
			$(e).closest("td").empty()

			// console.log(projectId, updateUrl)
			// console.log("Value = ",problemValue,$target)
			return false
		}

		$(".show-problem-detail").click(function() {
			var $detailEle = $(this).closest("tr").find(".inline-edit-field.-textarea")
			$detailEle.toggleClass("-hidden")
			return false
		})

		</script>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $planningInfo->title,
				'navigator' => [
					'info' => new Ui([
						'children' => [
							['text' => '<a href="'.url('project/planning/ampur').'"><i class="icon -material">fact_check</i><span>แผนอำเภอ</span></a>'],
						], // children
					]),
				], // navigator
			]), // AppBar
			'children' => [
				$ret
			] // children
		]);
	}
}
?>