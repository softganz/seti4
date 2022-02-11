<?php
/**
* Project Planning View Detail
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $tranId
* @return String
*/
function project_proposal_info_howto($self, $proposalInfo) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>แนวทาง/วิธีการสำคัญ</h3></header>';

	mydb::where('`tpid` = :tpid', ':tpid', $tpid);
	$stmt='SELECT
		tr.`trid`,tr.`refid`
	--	tg.`catid`,tg.`name`,
		FROM %project_tr% tr
		WHERE tr.`tpid` = :tpid AND tr.`formid` = "develop" AND tr.`part` = "supportplan"
	--	WHERE `taggroup`="project:planning"
		';

	$issueDbs = mydb::select($stmt);

	//$ret .= print_o($issueDbs,'$issueDbs');


	foreach ($issueDbs->items as $issueRs) {
		$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = :taggroup ORDER BY `catid` ASC';
		$dbs = mydb::select($stmt,':taggroup','project:guideline:'.$issueRs->refid);

		$tables = new Table();
		$tables->thead=array('no -catid'=>'ID','แนวทางดำเนินงาน','วิธีการสำคัญ	','icons -c1 -center'=>'');
		foreach ($dbs->items as $rs) {
			$detail = json_decode($rs->description);
			$tables->rows[]=array(
				$rs->catid,
				$rs->name,
				$detail->process,
			);
		}
		$ret .= $tables->build();

		//$ret .= print_o($dbs, '$dbs');
		
	}


	//$stmt='SELECT * FROM %tag% WHERE `taggroup`=:taggroup ORDER BY `catid` ASC';
	//$dbs=mydb::select($stmt,':taggroup','project:guideline:1');

	//$ret .= print_o($dbs);


	// ดึงจากค่าที่กำหนดไว้แล้ว
	//$ret.='<h4>แนวทาง/วิธีการสำคัญ</h4>';

	/*
	$ret.='<h3>แนวทางดำเนินงาน/วิธีการสำคัญ</h3>';
	$ret.='<form method="post" action="'.url('project/admin/planning/issue/'.$catid).'">';
	$tables = new Table();
	$tables->thead=array('center -catid'=>'ID','แนวทางดำเนินงาน','วิธีการสำคัญ	','icons -c1 -center'=>'');
	foreach ($dbs->items as $rs) {
		//$ret.=print_o($rs,'$rs');
		$detail=json_decode($rs->description);
		//$ret.=print_o($detail,'$detail');
		if ($action=='editguideline' && $trid==$rs->catid) {
			$detail->process=str_replace('<br />',"\n",$detail->process);

			$tables->rows[]=array(
				'<input class="form-text -fill -numeric" type="text" name="data[catid]" value="'.$rs->catid.'" readonly="readonly" />',
				'<textarea class="form-textarea -fill" name="data[guideline]" placeholder="แนวทางดำเนินงาน" rows="7">'.htmlspecialchars($rs->name).'</textarea>',
				'<textarea class="form-textarea -fill" name="data[process]" placeholder="วิธีการสำคัญ" rows="7">'.htmlspecialchars(($detail->process)).'</textarea>',
				'<button class="btn -primary" name="act" value="addguideline"><i class="icon -save -white"></i></button>',
			);
		} else {
			$tables->rows[]=array(
				$rs->catid,
				$rs->name,
				$detail->process,
				'<a class="" href="'.url('project/admin/planning/issue/'.$catid.'/editguideline/'.$rs->catid).'"><i class="icon -edit"></i></a>',
			);
		}
	}
	if (empty($action)) {
		$tables->rows[]=array(
			'<input class="form-text -fill -numeric" type="text" name="data[catid]" placeholder="auto" />',
			'<textarea class="form-textarea -fill" name="data[guideline]" placeholder="เพิ่มแนวทางดำเนินงาน" rows="7"></textarea>',
			'<textarea class="form-textarea -fill" name="data[process]" placeholder="วิธีการสำคัญ" rows="7"></textarea>',
			'<button class="btn -primary" name="act" value="addguideline"><i class="icon -save -white"></i></button>',
		);
	}
	$ret.=$tables->build();
	*/

	/*
	$tables = new Table();
	$tables->thead=array('no'=>'','แนวทาง','วิธีการสำคัญ');
	if ($isEdit) $tables->thead['icons -c1']='';
	$no=0;
	$cardItem='';
	foreach ($planInfo->guideline as $rs) {
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
			$row[]=empty($rs->catid)?'<span class=" hover-icon -tr"><a class="sg-action" href="'.url('project/planning/'.$tpid.'/info/removetr/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>':'';
		}
		$tables->rows[]=$row;
	}
	//$ret.=$cardItem;
	$ret.=$tables->build();

	$ret .= print_o($proposalInfo,'$proposalInfo');
	*/
	return $ret;
}
?>