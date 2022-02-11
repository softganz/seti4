<?php
/**
* Fund Financial Controller
*
* @param Object $self
* @param String $fundId
* @param String $action
* @param Int $tranId
* @param Object $data
* @return String
*
* @usage project/fund/financial
* @usage project/fund/$orgId/financial
* @usage project/fund/$orgId/financial/edit/$tranId
*/

$debug = true;

function project_fund_financial($self, $fundInfo = NULL, $action = NULL, $tranId = NULL) {
	if (!$fundInfo) return R::Page('project.fund.financial.home',$self);

	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	R::view('project.toolbar',$self,'ข้อมูลการเงิน - '.$fundInfo->name,'fund',$fundInfo);

	$isEdit = $fundInfo->right->editFinancial;
	$isAccess = $fundInfo->right->accessFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';

	$ret = R::Model('project.financial.summary',$fundInfo);

	__project_fund_financial_head();

	if (!$fundInfo->hasInitAccount) {
		return $ret.'<p class="notify" style="margin: 64px 0;">กองทุนยังไม่มีการกำหนดข้อมูลบัญชี-การเงิน</p><nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/fund/'.$orgId.'/info.finance').'" data-rel="box" data-width="640">กำหนดข้อมูลบัญชี-การเงิน</a></nav>';
	}

	$dropboxmenu=new ui();
	$dropboxmenu->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial').'" data-rel="#main"><i class="icon -list"></i><span>รายการบันทึก</span></a>');
	$dropboxmenu->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.book').'" data-rel="#project-financial-info"><i class="icon -list"></i><span>สมุดคุมรับ-จ่าย</span></a>');
	$dropboxmenu->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.list').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>สรุปการรับ-จ่ายรายเดือน-ปี</span></a>');
	//$dropboxmenu->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.list').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>สรุปรายไตรมาส</span></a>');
	//$dropboxmenu->add('<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial.list').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>สรุปรายปี</span></a>');
	$dropboxmenu->add('<a class="" href="'.url('project/fund/'.$orgId.'/paiddoc').'" rel="nofollow"><i class="icon -list"></i><span>ใบเบิกเงิน</span></a>');
	$dropboxmenu->add('<a class="" href="'.url('project/fund/'.$orgId.'/financial.rcv').'"><i class="icon -list"></i><span>ใบเสร็จรับเงิน</span></a>');

	$ret .= '<div class="noprint" style="position:relative;margin:16px 0; padding-right:50px;">';
	$ui=new ui(NULL,'iconset -sg-text-right');
	$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial').'" data-rel="#main"><i class="icon -list"></i><span>รายการ</span></a>');
	$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial.book').'" data-rel="#project-financial-info"><i class="icon -list"></i><span>สมุดคุมรับ-จ่าย</span></a>');
	//$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial.month').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>รายเดือน</span></a>');
	//$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial.quarter').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>รายไตรมาส</span></a>');
	//$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial.year').'" data-rel="#project-financial-info"><i class="icon -module"></i><span>รายปี</span></a>');
	$ui->add('<a class="sg-action btn" href="'.url('project/fund/'.$orgId.'/financial.list').'" data-rel="#project-financial-info" title="ปิดงวดประจำเดือน"><i class="icon -lock"></i><span>ปิดงวดเดือน</span></a>');
	$ui->add('<a class="btn -primary" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print -white"></i><span>พิมพ์</span></a>');
	$ret .= $ui->build();
	$ret .= '<span style="position:absolute;right:10px; top:0;">'.($dropboxmenu?sg_dropbox($dropboxmenu->build()):'').'</span></div>'._NL;

	$ret .= '<div id="project-financial-info">';
	$ret .=__project_fund_financial_item($fundInfo,$tranId);
	$ret .= '</div><!-- project-financial-info -->';

	return $ret;
}

function __project_fund_financial_head() {
	head('<style type="text/css">
	.project-summary {padding:10px;background:#1565C0; color:#fff;}
	.project-summary p {margin:0; padding:0 0 0 16px;}
	.project-summary>div {width:33%; display:inline-block;vertical-align: top;}
	.project-summary>div>span {display:block;}
	.project-summary .itemvalue {font-size: 1.2em; line-height:1.2em;}
	.project-report-section {margin: 16px; padding:8px; float: left; box-shadow: 2px 2px 10px #ccc;}
	.item tr.subheader td {padding-left:10px;}
	tr.editrow td {background:#E5BBA0;}
	</style>

	<script type="text/javascript">
	$("#glcode").change(function() {
		$("#showglcode").text($(this).val());
	});
	</script>');
}

function __project_fund_financial_item($fundInfo,$tranId = NULL) {
	$orgId = $fundInfo->orgid;

	$isEdit = $fundInfo->right->edit;

	$glcodeForInput=cfg('project.fund.rcvglcode');
	$data=NULL;

	$minInputDate = SG\getFirst($fundInfo->finclosemonth,$fundInfo->info->openbaldate);

	mydb::where('g.`orgid` = :orgid', ':orgid', $fundInfo->orgid);

	$stmt = 'SELECT g.*
			, gc.`gltype`, gc.`glname`
			, DATE_FORMAT(g.`refdate`,"%Y-%m") `refmonth`
			, t.`title` `projectTitle`
			FROM %project_gl% g
				LEFT JOIN %glcode% gc USING(`glcode`)
				LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY
		  `refmonth` DESC
		, g.`refdate` ASC, `refcode`
		, CASE
				WHEN g.`amount`>=0 THEN 1
				WHEN g.`amount`<0 THEN 2
			END ASC 
		, g.`glcode` ASC';
	$dbs=mydb::select($stmt);
	//$ret .=mydb()->_query;
	//$ret .=print_o($fundInfo,'$fundInfo');

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','รหัสอ้างอิง', 'glcode -nowrap' => 'รหัสบัญชี','รายการ','rev -money -nowrap'=>'เดบิท<br />(บาท)','expense -money -nowrap'=>'เครดิต<br />(บาท)','');

	if ($isEdit) {

		// Prepare input form
		$ret .= '<form class="sg-form" method="post" action="'.url('project/fund/'.$fundInfo->orgid.'/info/financial.add').'" data-rel="notify" data-done="load">';

		// OptGroup
		$stmt = 'SELECT g.`glcode`, g.`glname`, g.`gltype`
						FROM %glcode% g
						WHERE g.`glcode` IN (:glcodeForInput)';
		$glDbs=mydb::select($stmt,':glcodeForInput','SET-STRING:'.$glcodeForInput);
		//$ret .=print_o($glDbs);

		$selectGlCode = '<select id="glcode" class="form-select -fill" name="glcode">';
		$selectGlCode .= '<option value="">==เลือกประเภทรายการรับ==</option>';
		foreach ($glDbs->items as $optItem) {
			$selectGlCode .= '<option value="'.$optItem->glcode.'" '.($optItem->glcode== $data->position?' selected="selected"':'').'>'.$optItem->glname.'</option>';
		}
		$selectGlCode .= '</select>';

		$saveButton = '<button class="btn -primary -nowrap" type="submit"><i class="icon -save -white"></i><span>บันทึกรายรับ</button>';

		$tables->rows[]=array(
			'<input class="sg-datepicker form-text -fill" type="text" name="refdate" size="8" value="'.$data->refdate.'"  placeholder="31/12/2559" data-max-date="'.date('d/m/Y').'" data-min-date="'.sg_date($minInputDate,'d/m/Y').'" readonly="readonly" />',
			'',
			'<span id="showglcode"></span>',
			$selectGlCode,
			'<input class="form-text -money -fill" type="text" name="debit" size="10" placeholder="0.00" />',
			'<td colspan="2">'.$saveButton.'</td>'
		);
		$tables->rows[]=array('<td colspan="7">* รายการจ่ายและรายการรับเงินคืนจากโครงการจะบันทึกข้อมูลจาก <a href="'.url('project/fund/'.$fundInfo->orgid.'/follow').'"><b>โครงการ</b></a> => <b>ใบเบิกเงิน</b> , <b>ปิดโครงการ/บันทึกเงินคืน</b></td>');
	}

	$prevrs=NULL;

	foreach ($dbs->items as $rs) {
		if (empty($prevrs) || sg_date($rs->refdate,'Y-m')!=sg_date($prevrs->refdate,'Y-m')) {
			$tables->rows[]=array('<td colspan="7">'.sg_date($rs->refdate,'ดดด ปปปป').'</td>','config'=>array('class'=>'subheader'));
		}

		//  Menu for each GL Transaction
		$menu = '';
		if ($rs->refcode!= $prevrs->refcode) {
			if (substr($rs->refcode,0,3)== 'RCV') {
				$menu .= ' <span class="iconset"><a href="'.url('project/fund/'.$orgId.'/financial.view/'.$rs->pglid).'"><i class="icon -material">find_in_page</i></a> ';
				if ($rs->refdate>$fundInfo->finclosemonth) {
					if ($isEdit) $menu .= '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/financial/edit/'.$rs->pglid).'" title="แก้ไข" data-rel="#main"><i class="icon -edit"></i><span class="-hidden"> แก้ไข</span></a></span>';
				} else {
					$menu .= '<i class="icon -lock -gray"></i>';
				}
				//$menu .= $rs->refdate.' '.$fundInfo->finclosemonth;
			} else if (substr($rs->refcode,0,3)== 'PAY') {
				$menu .= '<span class="iconset"><a href="'.url('project/'.$rs->tpid.'/info.paiddoc/'.$rs->actid).'" rel="nofollow"><i class="icon -material">find_in_page</i></a></span>';
				if ($rs->refdate<= $fundInfo->finclosemonth) $menu .= '<i class="icon -lock -gray"></i>';
			} else if (substr($rs->refcode,0,3)== 'RET') {
				$menu .= '<span class="iconset"><a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.moneyback/'.$rs->actid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a></span>';
				if ($rs->refdate<= $fundInfo->finclosemonth) $menu .= '<i class="icon -lock -gray"></i>';
			}
		}

		if ($tranId && $rs->pglid== $tranId) {
			// Edit GL Transaction
			$glTrans=R::Model('project.gl.tran.get',$rs->refcode);
			$glcodeTran=explode(',', $glTrans->glCodes);
			$selectGlCode = '<select id="glcode" class="form-select" name="glcode">';
			$selectGlCode .= '<option value="">==เลือกประเภทรายการรับ==</option>';
			foreach ($glDbs->items as $optItem) {
				$selectGlCode .= '<option value="'.$optItem->glcode.'" '.(in_array($optItem->glcode,$glcodeTran)?' selected="selected"':'').'>'.$optItem->glname.'</option>';
			}
			$selectGlCode .= '</select>';
			$tables->rows[]=array(
				'<input type="hidden" name="refcode" value="'.$rs->refcode.'" />'
				.'<input class="form-text -date sg-datepicker" type="text" name="refdate" value="'.sg_date($rs->refdate,'d/m/Y').'" data-max-date="'.date('d/m/Y').'" data-min-date="'.(date('d/m/').(date('Y')-1)).'" readonly="readonly" />',
				'',
				'',
				$selectGlCode,//.print_o($glTrans,'$glTrans'). print_o($rs,'$rs'),
				'<input class="form-text -money" type="text" name="debit" value="'.number_format($rs->amount,2).'" />',
				'<button class="btn -primary" type="submit"><span>บันทึก</button>'
				.'<br /><a href="'.url('project/fund/'.$orgId.'/financial').'">ยกเลิก</a>',
				$menu,
				'config'=>array('class'=>'editrow')
			);
		} else {
			// Show GL Transation
			$glname = str_replace('{บัญชีธนาคาร}','{'.$fundInfo->info->accbank.'}',$rs->glname);
			$config = array();
			if ($rs->refdate < $fundInfo->info->openbaldate) {
				$config['class'] = '-date-under';
			}

			$tables->rows[] = array(
				empty($rs->refdate) ? '???' : ($rs->refdate != $prevrs->refdate ? sg_date($rs->refdate,'ว ดด ปปปป') : ''),
				$rs->refcode != $prevrs->refcode?$rs->refcode : '',
				$rs->glcode,
				$glname
				.(in_array(substr($rs->glcode,0,1),array('5','4')) && $rs->projectTitle?'<br />(<a href="'.url('project/'.$rs->tpid).'">'.$rs->projectTitle.'</a>)':''),
				$rs->amount >= 0 ? number_format($rs->amount,2) : '',
				$rs->amount <= 0 ? number_format(abs($rs->amount),2) : '',
				$menu,
				'config' => $config,
			);
		}

		$prevrs = $rs;
	}
	$ret .= $tables->build();

	if ($isEdit) $ret .= '</form>';

	//$ret .=print_o($dbs,'$dbs');
	//$ret .= print_o($fundInfo,'$fundInfo');

	head('<style type="text/css">
	.-date-under {color: #ff6666; text-decoration: line-through;}
	</style>');
	return $ret;
}


/*
รายการรับ 				
	เงินค่าบริการสาธารณสุขที่ได้รับจากสำนักงานหลักประกันสุขภาพแห่งชาติ
	เงินอุดหนุนหรืองบประมาณที่ได้รับจากองค์กรปกครองส่วนท้องถิ่น
	เงินได้จากดอกเบี้ยเงินฝากธนาคาร
	เงินสมทบจากชุมชน เงินบริจาค เงินได้อื่นๆ
	เงินรับคืนจากการดำเนินแผนงาน/โครงการ/กิจกรรม
	  	  	 
รายการจ่าย 				
	ค่าใช้จ่ายสนับสนุนหน่วยบริการ/สถานบริการ/หน่วยงานสาธารณสุข (ประเภทที่ 1)
	ค่าใช้จ่ายสนับสนุนกลุ่มหรือองค์กรประชาชน/หน่วยงานอื่น (ประเภทที่ 2)
	ค่าใช้จ่ายสนับสนุนศูนย์ เด็กเล็ก/ผู้สูงอายุ/คนพิการ (ประเภทที่ 3)
	ค่าใช้จ่ายสนับสนุนการบริหาร/พัฒนากองทุนฯ (ประเภทที่ 4)
	ค่าใช้จ่ายสนับสนุนกรณีเกิดโรคระบาด/ภัยพิบัติ (ประเภทที่ 5)
*/
?>