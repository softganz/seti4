<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

function project_develop_problem($self, $tpid, $action = NULL, $trid = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;

	// TODO: Check right to edit
	$isEditable = $devInfo->RIGHT & _IS_EDITABLE;
	$isEdit = $isEditable && $action == 'edit';

	$ret = '';

	$tables = new Table();
	$tables->addClass('project-develop-problem-list');
	$tables->thead['no'] = '';
	$tables->thead['detail'] = 'สถานการณ์ปัญหา';
	if ($isEdit) $tables->thead['icons -c1 -center'] = '';
	$tables->thead['amt -size'] = 'ขนาด';
	if ($isEdit) $tables->thead[] = '';

	foreach ($devInfo->problem as $rs) {
		$row = array();
		$row[] = ++$no;
		$row[] = ($rs->refid ? $rs->problem : view::inlineedit(array('group'=>'tr:develop:problem','fld'=>'detail1','tr'=>$rs->trid,'class'=>'-fill','placeholder'=>'ระบุสถานการณ์ปัญหา'),$rs->problem,$isEdit))
				. view::inlineedit(
					array(
						'group' => 'tr:develop:problem',
						'fld' => 'text1',
						'tr' => $rs->trid,
						'class' => '-fill'.($rs->detailproblem ? '' : ' -hidden'),
						'ret' => 'html',
						'placeholder' => 'ระบุรายละเอียด'
					),
					$rs->detailproblem,
					$isEdit,
					'textarea'
				);
		if ($isEdit) $row[] = '<a class="show-problem-detail" href="javascript:void(0)"><i class="icon -edit -gray"></i></a>';
		$row[] = view::inlineedit(
					array(
						'group'=>'tr:'.$rs->tagname.':'.$rs->refid,
						'fld'=>'num1',
						'tr'=>$rs->trid,
						'class'=>'-numeric -fill',
						'ret'=>'numeric',
						'options'=>'{placeholder: "?", done: "load->replace:#project-develop-objective:'.url('project/develop/'.$tpid.'/objective/edit').'"}',
					),
					$rs->trid?number_format($rs->problemsize,2):'',
					$isEdit
				);
		if ($isEdit) $row[] = $rs->trid ? '<span class="hover-icon -tr"><a class="sg-action" href="'.url('project/develop/info/'.$tpid.'/problem.remove/'.$rs->trid).'" data-rel="#main" data-ret="'.url('project/develop/'.$tpid.'/view/edit').'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" title="ลบรายการ"><i class="icon -cancel -gray"></i></a>' : '';

		$tables->rows[] = $row;
	}

	if ($isEdit) {
		$stmt = 'SELECT p.*,pn.`name` `planName`
			FROM %tag% p
				LEFT JOIN %tag% pn ON pn.`taggroup` = "project:planning" AND CONCAT("project:problem:",pn.`catid`) = p.`taggroup`
			WHERE p.`taggroup` IN
				(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "supportplan")
			ORDER BY `weight` ASC';
		$problemDbs = mydb::select($stmt, ':tpid', $tpid);


		$ret .= '<form class="sg-form project-develop-problem-form -sg-clearfix" method="post" action="'.url('project/develop/info/'.$tpid.'/problem.edit').'" data-checkvalid="yes" data-rel="#main" data-ret="'.url('project/develop/'.$tpid.'/view/edit').'">';

		$form = new Form([
			'action' => url('project/develop/info/'.$tpid.'/problem.edit'),
			'class' => 'sg-form project-develop-problem-form -sg-clearfix',
		]);

		if ($problemDbs->_num_rows) {
			$optionsProblem[''] = '==เลือกตัวอย่างสถานการณ์==';
			foreach ($problemDbs->items as $rs) {
				if (__is_dev_problem_exists($rs->taggroup,$rs->catid,$devInfo->problem)) continue;
				$detail = json_decode($rs->description);
				$optionsProblem[$rs->planName][$rs->taggroup.':'.$rs->catid] = $rs->name;
			}
			$form->addField(
				'problemref',
				array(
					'type' => 'select',
					'name' => 'problemref',
					'class' => '-fill',
					'require' => true,
					'options' => $optionsProblem,
				)
			);

			$tables->rows[] = array(
				'<td></td>',
				$form->get('edit-problemref')
				. ($options->showAutoAddObjective ? '<input type="checkbox" name="autoaddobjective" /> เพิ่มวัตถุประสงค์/ตัวชี้วัดตามตัวอย่างสถานการณ์' : ''),
				'',
				'<input class="form-text -numeric -require" type="text" name="problemsize" size="5" placeholder="0.00" autocomplete="off" />',
				'<button class="btn -link" type="submit"><i class="icon -add"></i></button>',
				'config' => array('class' => '-no-print'),
			);
		}
	}
	$ret .= $tables->build();
	// $ret .= print_o($devInfo->problem,'$devInfo->problem');

	//$ret.=print_o($dbs);

	if ($isEdit) {
		$ret .= '</form>';
		$ret .= '<div class="-sg-text-right -no-print">หรือ <a class="sg-action btn -primary" href="'.url('project/develop/'.$tpid.'/problem.form').'" data-rel="replace:.-sg-text-right"><i class="icon -addbig -white"></i><span>เพิ่มสถานการณ์อื่น ๆ</span></a></div>';
		$ret .= '<p class="-no-print"><em>คลิกเพิ่มสถานการณ์ เลือกตัวอย่างสถานการณ์จากความสอดคล้องกับแผนงานที่ระบุไว้แล้ว หรือ ระบุสถานการณ์เพิ่มเติม ป้อนขนาดปัญหา แล้วบันทึก</em></p>';
	}

	$ret .= '<script type="text/javascript">
	$(".show-problem-detail").click(function() {
		var $detailEle = $(this).closest("tr").find(".inline-edit-field.-textarea")
		$detailEle.toggleClass("-hidden")
		return false
	})
	</script>';
	return $ret;
}


function __is_dev_problem_exists($taggroup, $catid, $problem = NULL) {
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
?>