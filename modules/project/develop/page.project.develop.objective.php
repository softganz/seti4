<?php
/**
* Project Development Objective Interface
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @return String
*/

function project_develop_objective($self, $tpid = NULL, $action = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;


	$isEditable = $devInfo->RIGHT & _IS_EDITABLE;
	$isEdit = $isEditable && $action == 'edit';

	// วัตถุประสงค์ทั่วไป และ วัตถุประสงค์เฉพาะ
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) {
		$objTypeList[$item->catid]=$item->name;
	}


	$ret .= '<div id="project-develop-objective" class="project-develop-objective">'._NL;


	$objectiveNo = 0;

	$tables = new Table();
	$tables->addClass('-list');
	$tables->colgroup = array('no'=>'','objective'=>'width="90%"','problem'=>'width="5%"','targetsize'=>'width="5%"');
	$tables->thead = array(
		'',
		'วัตถุประสงค์ / ตัวชี้วัดความสำเร็จ',
		'ขนาด',
		'เป้าหมาย 1 ปี',
	);
	if ($isEdit) $tables->thead[] = '';

	foreach ($objTypeList as $objTypeId => $objTypeName) {
		//if ($objTypeId!=1) continue;
		//if ($objTypeId==1) $tables->rows[]='<tr><th colspan="4"><h4>วัตถุประสงค์โดยตรง</h4></th></tr>';
		//else if ($objTypeId==2) $tables->rows[]='<tr><th colspan="4"><h4>วัตถุประสงค์โดยอ้อม</h4></th></tr>';

		foreach ($devInfo->objective as $objective) {
			if ($objective->objectiveType!=$objTypeId) continue;

			$objectiveIsInUse=false;
			foreach ($devInfo->info->mainact as $mainActItem) {
				if (empty($mainActItem->parentObjectiveId)) continue;
				if (in_array($objective->trid, explode(',', $mainActItem->parentObjectiveId))) {
					$objectiveIsInUse=true;
					break;
				}
			}

			// Create submenu
			/*
				$ui=new ui();
				$ui->add('<a href="'.url('project/develop/objective/'.$tpid.'/info/'.$objective->trid).'" class="sg-action" data-rel="box"><i class="icon -view"></i> รายละเอียด</a>');
				if ($isEdit) {
					$ui->add('<sep>');
					//$ui->add('<a href="'.url('project/develop/objective/'.$tpid,array('action'=>'move','id'=>$objective->trid)).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์">ย้ายวัตถุประสงค์</a>');
					if ($objectiveIsInUse) {
						$ui->add('<a href="javascript:void(0)" title="วัตถุประสงค์ข้อนี้มีการใช้งานในกิจกรรมหลักแล้ว">ลบวัตถุประสงค์ไม่ได้</a>');
					} else {
						$ui->add('<a class="sg-action" href="'.url('project/develop/objective/'.$tpid.'/remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -delete"></i> ลบวัตถุประสงค์</a>');
					}
				}
				$submenu=sg_dropbox($ui->build('ul'));
			*/
			$submenu='';
			if ($isEdit) {
				if ($objectiveIsInUse) {
					//$ui->add('<a href="javascript:void(0)" title="วัตถุประสงค์ข้อนี้มีการใช้งานในกิจกรรมหลักแล้ว">ลบวัตถุประสงค์ไม่ได้</a>');
				} else {
					$submenu = '<nav class="nav -icons"><span class="hover-icon -tr"><a class="sg-action" href="'.url('project/develop/info/'.$tpid.'/objective.remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="replace:#project-develop-objective" data-ret="'.url('project/develop/'.$tpid.'/objective/edit').'"><i class="icon -cancel -gray"></i></a></span></nav>';
				}
			}

			$row = array();
			$row[] = ++$objectiveNo;

			$row[] = '<b>'
							.(
								$objective->refid
								?
								$objective->title
								:
								view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid,'class'=>'-fill'), $objective->title, $isEdit, 'textarea')
							)
							.'</b><br />'
							.'<label><i>ตัวชี้วัดความสำเร็จ :</i></label>'.view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html','class'=>'-fill'),$objective->indicatorDetail,$isEdit,'textarea');

			$row[] = view::inlineedit(
								array(
									'group'=>'tr:info:objective',
									'fld'=>'num1',
									'tr'=>$objective->trid,
									'class'=>'-numeric -fill',
									'ret'=>'numeric',
									'placeholder'=>'?',
									'options' => '{blank: null}',
								),
								$objective->problemsize,
								$isEdit
							);

			$row[] = view::inlineedit(
								array('group'=>'tr:info:objective','fld'=>'num2','tr'=>$objective->trid,'class'=>'-numeric -fill','ret'=>'numeric','placeholder'=>'?'),
								$objective->targetsize,
								$isEdit
							);
			if ($isEdit) $row[] = $submenu;

			$tables->rows[] = $row;
		}
	}

	if ($isEdit) {
		// Get problem of select plan
		$stmt='SELECT p.*,pn.`name` `planName`
			FROM %tag% p
				LEFT JOIN %tag% pn ON pn.`taggroup` = "project:planning" AND CONCAT("project:problem:",pn.`catid`) = p.`taggroup`
			WHERE p.`taggroup` IN
				(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid` = :tpid AND `formid`="develop" AND `part` = "supportplan")';
		$problemDbs=mydb::select($stmt,':tpid',$tpid);
		//$ret .= print_o($problemDbs,'$problemDbs');
		//$ret .= print_o($devInfo->problem,'$devInfo->problem');


		$ret.='<form class="sg-form project-objective-form" method="post" action="'.url('project/develop/info/'.$tpid.'/objective.edit').'" data-checkvalid="yes" data-rel="replace:#project-develop-objective" data-ret="'.url('project/develop/'.$tpid.'/objective/edit').'">';

		$form=new Form(NULL,url('project/develop/info/'.$tpid.'/objective.edit'),NULL,'sg-form project-objective-form');

		$optionsObjective['']='==เลือกตัวอย่างวัตถุประสงค์==';
		foreach ($problemDbs->items as $rs) {
			$foundProblem = __is_dev_objective_problem_exists($rs->taggroup,$rs->catid,$devInfo->problem);
			if (!$foundProblem) continue;
			if (__is_dev_objective_exists($rs->taggroup,$rs->catid,$devInfo->objective)) continue;
			//$ret .= print_o($foundProblem,'$foundProblem');
			$detail = json_decode($rs->description);
			$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid] = $detail->objective.' (ขนาด '.$foundProblem->problemsize.')';
		}

		$form->addField(
			'problemref',
			array(
				'type'=>'select',
				'label'=>'เลือกตัวอย่างวัตถุประสงค์:',
				'class'=>'-fill',
				'options'=>$optionsObjective,
			)
		);

		$tables->rows[]=array(
			'<td></td>',
			$form->get('edit-problemref'),
			'',
			'<input class="form-text -numeric -require" type="text" name="targetsize" size="5" placeholder="0.00" autocomplete="off" style="display:block; margin:0 auto;" />',
			'<button class="btn -link" type="submit"><i class="icon -add"></i></button>',
		);
	}

	$ret.=$tables->build();


	// Add new objective
	if ($isEdit) {
		$ret.='</form>';
		$ret.='<div class="actionbar -project -objective -sg-text-right">หรือ <a class="sg-action btn -primary" data-rel="parent" href="'.url('project/develop/'.$tpid.'/objective.form').'"><i class="icon -addbig -white"></i><span>เพิ่มวัตถุประสงค์อื่น ๆ</span></a></div>'._NL;
		$ret.='<p><em>คลิกเพิ่มวัตถุประสงค์ เลือกตัวอย่างวัตถุประสงค์จากความสอดคล้องกับแผนงานที่ระบุไว้แล้ว หรือ ระบุวัตถุประสงค์เพิ่มเติม แล้วบันทึก</em></p>';
	}

	//$ret.=print_o($devInfo,'$devInfo');
	//$ret.=print_o($options,'$options');

	$ret.='</div><!-- project-develop-objective -->'._NL;
	return $ret;
}

function __is_dev_objective_exists($taggroup, $catid, $problem = NULL) {
	$found = false;
	//debugMsg('Check '.$taggroup.' Catid '.$catid);
	foreach ($problem as $rs) {
		if ($taggroup == $rs->tagname && $catid == $rs->refid) {
			$found = true;
			break;
		}
	}
	return $found;
}

function __is_dev_objective_problem_exists($taggroup, $catid, $problem = NULL) {
	$found = false;
	//debugMsg('Check '.$taggroup.' Catid '.$catid);
	foreach ($problem as $rs) {
		if ($taggroup == $rs->tagname && $catid == $rs->refid) {
			$found = $rs;
			break;
		}
	}
	return $found;
}
?>