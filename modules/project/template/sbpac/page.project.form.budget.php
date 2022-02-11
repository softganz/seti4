<?php
/**
* Project edit budget
*
* @return String
*/
function project_form_budget($self, $topic, $para = NULL) {
	$tpid=SG\getFirst(post('tpid'),$topic->tpid);
	$totalBudgetChange=false;

	$isAdmin=user_access('administer projects');
	$isEdit=$topic->project->project_status=='กำลังดำเนินโครงการ' && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));

	if (!$tpid) return 'Error : No project';

	$post=post();

	if ($post['action']=='form') return __project_edit_budget_form($topic,$isAdmin);

	if ($post['budget'] && isset($post['budget']['budget'])) {
		$budget=(object)$post['budget'];
		$budget->tpid=$tpid;
		$budget->budget=preg_replace('/[^0-9\.\-]/','',$budget->budget);
		if ($budget->parent) {
			$budget->bdgroup=mydb::select('SELECT `bdgroup` FROM %topic_parent% WHERE `tpid`=:parent LIMIT 1',':parent',$budget->parent)->bdgroup;
			$stmt='INSERT INTO %topic_parent% (`tpid`,`parent`, `budget`, `bdgroup`) VALUES (:tpid, :parent, :budget, :bdgroup)  ON DUPLICATE KEY UPDATE `tpid`=:tpid';
			mydb::query($stmt,$budget);

			$isNoRel=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="rel" LIMIT 1',':tpid',$tpid)->_empty;
			if ($isNoRel) {
				$stmt='INSERT INTO %project_tr% (`tpid`,`parent`,`formid`,`part`,`uid`,`rate1`,`created`)
								SELECT :tpid,`parent`,`formid`,`part`,:uid,1,:created
									FROM %project_tr%
									WHERE `tpid`=:parent_tpid AND `formid`="info" AND `part`="rel" AND `rate1`=1';
				mydb::query($stmt,':tpid',$tpid,':parent_tpid',$budget->parent,':uid',i()->uid,':created',date('U'));
				$isNewRel=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="rel" LIMIT 1',':tpid',$tpid)->_num_rows;
				// $ret.='New rel='.$isNewRel;
			}
		} else if ($budget->bdgroup) {
			$stmt='INSERT INTO %topic_parent% (`tpid`,`parent`, `budget`, `bdgroup`) VALUES (:tpid, :parent, :budget, :bdgroup)  ON DUPLICATE KEY UPDATE `tpid`=:tpid';
			mydb::query($stmt,$budget);
		}
		$totalBudgetChange=true;
	}

	// Delete budget from topic_paretn include parent is zero
	if (isset($post['delete'])) {
		mydb::query('DELETE FROM %topic_parent% WHERE `tpid`=:tpid AND `parent`=:parent LIMIT 1',':tpid',$tpid, ':parent',$post['delete']);
		$totalBudgetChange=true;
	}

	$stmt='SELECT h.*, t.`title`, tg.`name`
		FROM %topic_parent% h
			LEFT JOIN %topic% t ON t.`tpid`=h.`parent`
			LEFT JOIN %tag% tg ON tg.`tid`=h.`bdgroup`
		WHERE h.`tpid`=:tpid';
	$mainProject=mydb::select($stmt,':tpid',$tpid);

	if ($mainProject->_num_rows) {
		$totalBudget=0;

		$subTable = new Table();
		$subTable->thead=array('แผนงาน/ชุดโครงการ','money'=>'งบประมาณ(บาท)','');
		foreach ($mainProject->items as $item) {
			$subTable->rows[]=array(
				$item->parent?'<a href="'.url('paper/'.$item->parent).'">'.SG\getFirst($item->title,$item->name).'</a>' : '<a href="'.url('project/report/budget',array('pr'=>0,'bg'=>$item->bdgroup)).'">'.SG\getFirst($item->title,$item->name).'</a>',
				'<strong>'.($item->budget>=1000000?'<span title="'.number_format($item->budget,2).'">'.number_format($item->budget/1000000,3).'ล.</span>':number_format($item->budget,2)).'</strong>',
				$isEdit?'<a href="'.url("project/form/$tpid/budget",array('delete'=>$item->parent)).'" title="ลบรายการนี้" class="sg-action" data-confirm="ต้องการลบรายการนี้" data-rel="#project-info-budget">X</a>':''
			);
			$totalBudget+=$item->budget;
		}
		$subTable->rows[]=array('<strong>รวมงบประมาณทั้งหมด</strong>','<strong>'.($totalBudget>=1000000?'<span title="'.number_format($totalBudget,2).'">'.number_format($totalBudget/1000000,3).'ล.</span>':number_format($totalBudget,2)).'</strong>');
		$ret .= $subTable->build();
	} else {
		$ret.=$isEdit?'<p class="notify">กรุณาระบุงบประมาณโดยการคลิกปุ่ม <strong>"เพิ่มงบประมาณ"</strong> ด้านล่าง</p>':'<p>ยังไม่ได้กำหนดงบประมาณ</p>';
	}

	//$ret.=print_o($budgets);
	if ($totalBudgetChange) {
		mydb::query('UPDATE %project% SET `budget`=:budget WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':budget',$totalBudget);
	}
	//$ret.=print_o($topic,'$topic');
	if ($isNoRel && $isNewRel) {
		$ret.='<script>window.location.assign("'.url('paper/'.$tpid).'");</script>';
	}
	return $ret;
}

function __project_edit_budget_form($topic,$isAdmin) {
	$tpid=$topic->tpid;
	$org=mydb::select('SELECT t.`orgid`, o.`parent` FROM %topic% t LEFT JOIN %db_org% o ON o.`orgid`=t.`orgid` WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);

	/*
	เงื่อนไขในการดึงโครงการคือ
	1. เป็นแผนงาน หรือ ชุดโครงการ
	2. อยู่ภายใต้หน่วยงานตนเอง หรือ หน่วยงานต้นสังกัด
	3. กรณีเป็น Admin ไม่ต้องตรวจสอบเงื่อนไขข้อ 2
	4. แผนงาน หรือ ชุดโครงการ อยู่ในสถานะ กำลังดำเนินการ
	*/
	$where=array();
	$where=sg::add_condition($where,'t.`type`="project" AND t.`tpid`!=:tpid','tpid',$tpid);
	if (!$isAdmin) $where=sg::add_condition($where,'t.`orgid` IN (:orgid,:parentorg)','orgid',$org->orgid,'parentorg',$org->parent);
	$where=sg::add_condition($where,'p.`project_status`="กำลังดำเนินโครงการ" AND p.`prtype` IN ("แผนงาน","ชุดโครงการ")');

	$stmt='SELECT
						t.`tpid`, t.`orgid`, p.`prtype`, t.`title`,
						pr.`budget`, pr.`parent` projectParent,
						pr.`bdgroup`, tg.`name` budgetName
					FROM %topic% t
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic_parent% pr USING(`tpid`)
						LEFT JOIN %tag% tg ON tg.`tid`=pr.`bdgroup` '
					.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					ORDER BY `prtype` ASC,CONVERT(`title` USING tis620) ASC
					';
	$parentDbs=mydb::select($stmt,$where['value']);
	//$ret.='<form class="sg-form" action="'.url('project/edit/budget/tpid/'.$tpid).'" data-rel="#project-info-budget"><input type="hidden" name="tpid" value="'.$tpid.'" />';

	$form->config->variable='budget';
	$form->config->method='post';
	$form->config->action=url('project/form/'.$tpid.'/budget');
	$form->config->class='sg-form';
	$form->config->attr=array('data-rel'=>'project-info-budget');

	$form->tpid->type='hidden';
	$form->tpid->name='tpid';
	$form->tpid->value=$tpid;
	if ($isAdmin && in_array($topic->project->prtype, array('แผนงาน','ชุดโครงการ'))) {
		$form->bdgroup->type='select';
		$form->bdgroup->label='หมวดงบประมาณ';
		$budgetGroup=mydb::select('SELECT `tid`,`name` FROM %tag% WHERE `taggroup`="project:bdgroup"')->items;
		$form->bdgroup->options['']='==เลือกหมวดงบประมาณ==';
		foreach ($budgetGroup as $item) $form->bdgroup->options[$item->tid]=$item->name;
	}

	$form->parent->type='select';
	$form->parent->label='แผนงาน/ชุดโครงการหลัก';
	$form->parent->options[0]='==เลือกแผนงาน/ชุดโครงการ==';
	foreach ($parentDbs->items as $key=>$item) {
		$form->parent->options[$item->budgetName][$item->tpid]=$item->prtype.' - '.$item->title.' ('.number_format($item->budget,2).' บาท)';
	}

	$form->budget->type='text';
	$form->budget->label='จำนวนเงิน (บาท)';
	$form->budget->placeholder='0.00';
	$form->budget->autocomplete='off';

	$form->submit->type='submit';
	$form->submit->items->save='บันทึก';
	$form->submit->posttext=' หรือ <a class="cancel cancel--budget" href="'.url('paper/'.$tpid).'">ยกเลิก</a>';
	$ret .= theme('form','project-budget-add',$form);


	$ret.='<script type="text/javascript">
					$("#edit-budget-budget").focus()
					$("#edit-budget-parent, input[name=\'budget[parent]\']").change(function() {
						if ($(this).val()==0) {
							$("#form-item-edit-budget-bdgroup").show()
						} else {
							$("#form-item-edit-budget-bdgroup").hide()
						}
						$("#edit-budget-budget").focus()
					});
					$("#project-budget-add").submit(function() {$(this).parent().empty()});
					$(".cancel--budget").click(function() {$(this).closest("form").parent().empty();return false;});
					</script>';
	//$ret.=print_o($parentDbs,'$parentDbs');
	return $ret;
}
?>