<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $actionId
* @return String
*/
function project_eval_kamsaisum($self, $tpid, $action = NULL, $transId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$formid = 'eval-kamsaisum';
	$year = '2018';

	$valuationTr = project_model::get_tr($tpid,$formid.':'.$year);

	$url = q();

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	$titleRs = end($valuationTr->items[$year]);
	//$ret .= print_o($titleRs,'$titleRs');

	$locked=$titleRs->flag;

	$isViewOnly = $action == 'view';
	$isEditable = $projectInfo->info->isRight;
	$isEdit = $projectInfo->info->isRight && $action == 'edit' && !$locked;

	if (post('lock') && $isAdmin && $titleRs->trid) {
		$locked=$titleRs->flag==_PROJECT_LOCKREPORT?NULL:_PROJECT_LOCKREPORT;
		$stmt='UPDATE %project_tr% SET `flag`=:flag WHERE `trid`=:trid LIMIT 1';
		mydb::query($stmt,':trid',$titleRs->trid,':flag',$locked);
		location($url);
	}


	$ret.='<h2 class="title -main">แบบฟอร์มสรุปผลการประเมิน</h2>';
	$ret .= '<p align="center"><b>ศูนย์เรียนรู้ต้นแบบเด็กไทยแก้มใสเพื่อคัดเลือกเป็น The Smart Learning Center ปี '.($year+543).'<br />'._NL;
	$ret .= $projectInfo->title.'<br />'.$projectInfo->info->area.'</b></p>';


	/*
	$ui = new Ui();
	$ui->add('<a href="'.url($url).'">รายงานแบบประเมิน</a>');
	$ui->add('<a href="'.url($url,$isAdmin?array('lock'=>$locked?'no':'yes') : NULL).'" title="'.($isAdmin?'คลิกเพื่อเปลี่ยนสถานะรายงาน':'').'">สถานะรายงาน : '.($locked?'Lock':'UnLock').'</a>');
	$ret.='<nav class="nav reportbar">'.$ui->build().'</nav>';
	*/



	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= 'project-eval -success';

	$ret.='<div id="project-success" '.sg_implode_attr($inlineAttr).'>'._NL;


	$outputList=array(
								'title'=>'1. ตัวชี้วัด',
								'field'=>',,num1',
								'items'=>array(
													array('section'=>'1','title'=>'การพัฒนาภาวะโภชนาการและการแก้ปัญหา'),
													array('section'=>'2','title'=>'การบูรณาการจัดการเรียนรู้ที่เชื่อมโยงภาวะโภชนาการกับการจัดประการอาหารในโรงเรียนและการพัฒนาสุขนิสัยของนักเรียน'),
													array('section'=>'3','title'=>'การปฎิบัติที่เป็นเลิศ (Best practice) จากการทำกิจกรรมโครงการเด็กไทยแก้มใส'),
													array('section'=>'4','title'=>'การขยายเครือข่ายเด็กไทยแก้มใส'),
													array('section'=>'5','title'=>'การเชื่อมโยงเข้าสู่นโยบายท้องถิ่นและการสนับสนุนทางท้องถิ่น'),
												)
								);



	$ret .= '<section class="section-5 box">';

	$tables = new Table();
	$tables->addClass('project-valuation-form -other');
	$tables->thead = array('ตัวชี้วัด','all -amt'=>'คะแนนเต็ม', 'score -amt'=>'คะแนนที่ได้','point -amt -hover-parent'=>'ผลการประเมิน(ระดับ)');

	$total = 0;
	foreach ($outputList['items'] as $k=>$v) {
		//$section = $mainKey.'.'.$v['section'];
		$section = $v['section'];
		$field = 'num'.$section;
		$value = $irs[$field];
		unset($row);
		$row[] = '<span>'.($v['section']).'. '.$v['title'].'</span>';

		$row[] = 20;
		$row[] = view::inlineedit(array('group'=>$formid.':'.$year,'fld'=>$field,'tr'=>$titleRs->trid,'ret'=>'numeric', 'value'=>trim($titleRs->{$field})),$titleRs->{$field},$isEdit);
		$row[] = '';
		$tables->rows[] = $row;
		$total += $titleRs->{$field};
	}
	$tables->tfoot[] = array('<td align="center">รวม</td>',100, number_format($total,2),'');
	$ret .= $tables->build();

	$ret .= '<p><b>ระดับคุณภาพศูนย์เรียนรู้ต้นแบบเด็กไทยแก้มใสฯ แบ่งเป็น 4 ระดับ</b><br /><br />'
			. '<input type="checkbox" readonly="readonly" disabled="disabled" '.(floor($total/10) == 5 ? 'checked="checked"' : '').' /> ระดับพื้นฐาน (Beginner Level) คะแนนผ่านเกณฑ์การประเมินจากคณะกรรมการฯ ร้อยละ ๕๐-๕๙<br />'
			. '<input type="checkbox" readonly="readonly" disabled="disabled" '.(floor($total/10) == 6 ? 'checked="checked"' : '').' /> ระดับดี (Intermediat Level) คะแนนผ่านเกณฑ์การประเมินจากคณะกรรมการฯ ร้อยละ ๖๐-๖๙<br />'
			. '<input type="checkbox" readonly="readonly" disabled="disabled" '.(floor($total/10) == 7 ? 'checked="checked"' : '').' /> ระดับดีมาก (Advance Level) คะแนนผ่านเกณฑ์การประเมินจากคณะกรรมการฯ ร้อยละ ๗๐-๗๙<br />'
			. '<input type="checkbox" readonly="readonly" disabled="disabled" '.(floor($total/10) >=8 ? 'checked="checked"' : '').' /> ระดับดีเยี่ยม (Excellent Level) คะแนนผ่านเกณฑ์การประเมินจากคณะกรรมการฯ ร้อยละ ๘๐-๑๐๐'
			. '</p>';

/*
	foreach ($outputList as $mainKey=>$mainValue) {
		$tables->rows[] = array('<td colspan="5"><h3>'.$mainValue['title'].'</h3></td>');
		$tables->rows[] = '<header>';
		$section = $mainKey.'.'.$v['section'];

		foreach ($valuationTr->items[$mainKey] as $rs) {
			$menu = '';
			if ($isEdit) $menu = '<nav class="nav -icons -hover -no-print"><a class="sg-action" href="'.url('project/'.$tpid.'/info/tran.remove/'.$rs->trid).'" data-rel="none" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel"></i></a></nav>';
			unset($row);
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text1','tr'=>$rs->trid,'ret'=>'html', 'value'=>trim($rs->text1)),$rs->text1,$isEdit,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text2','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text2,$isEdit,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text3','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text3,$isEdit,'textarea');
			$row[] = view::inlineedit(array('group'=>$formid.':'.$section,'fld'=>'text4','tr'=>$rs->trid, 'ret'=>'html', 'value'=>$rs->text2),$rs->text4,$isEdit,'textarea')
				. $menu;
			$tables->rows[] = $row;
		}
		if ($isEdit) {
			$tables->rows[] = array('<td colspan="4" class="-sg-text-right"><a class="sg-action" href="'.url('project/'.$tpid.'/info/tran.add/eval-success,'.$mainKey).'" data-rel="#main" data-ret="'.url('project/'.$tpid.'/eval.success/edit').'"><i class="icon -add"></i></a></td>');
		}


	}
	$ret .= $tables->build();
	*/

	$ret .= '</section><!-- section-5 -->';



	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/'.$tpid.'/eval.kamsaisum',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/'.$tpid.'/eval.kamsaisum/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	$ret.='</div>';


	$ret.='<style>
	.project-eval.-success td {width: 25%;}
	</style>';


	$ret .= '<script type="text/javascript">
	// Other radio group
	$(".project-valuation-form.-other input.inline-edit-field.-radio").each(function() {
		var $radioBtn = $(this).closest("tr").find(".inline-edit-field.-radio:checked")
		var radioValue = $radioBtn.val();
		//console.log("Tr = "+$radioBtn.data("tr")+" - radioValue="+radioValue);
		if (!(radioValue==0 || radioValue==1)) {
			$(this).closest("tr").find("span.inline-edit-field").hide();
		}
	});

	$(".project-valuation-form.-other input[type=\'radio\']").change(function() {
		var rate = $(this).val()
		var $inlineInput = $(this).closest("tr").find("td>span>span")
		//console.log("radio change "+$(this).val())
		$inlineInput.show()
	});

	</script>';

	//$ret.=print_o($valuationTr,'$valuationTr');
	return $ret;
}


?>