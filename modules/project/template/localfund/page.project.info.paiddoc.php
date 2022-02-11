<?php
/**
* Project : Localfund Paid Document
* Created 2019-10-01
* Modify  2021-12-20
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_info_paiddoc($self, $projectInfo, $tranId = NULL) {
	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PROCESS ERROR');

	$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	$options = options('project');
	$ret = '';

	if (!$projectInfo)
		return message('error', 'This is not a project');

	$action = SG\getFirst($action,post('act'));

	$isAdmin = user_access('administer projects');
	$isRight = user_access('administer projects')
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
		|| in_array($fundInfo->officers[i()->uid], array('ADMIN','OFFICER'));
	$isEdit = $isRight;

	if (!$isRight) {
		return message('notify', 'ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น');
	}

	if ($error = R::Model('project.paiddoc.cancreate', $projectInfo, $fundInfo))
		return message('error',$error);

	$ret .= R::View('project.statusbar', $projectInfo)->build();


	if ($tranId) {
		$paiddocInfo = R::Model('project.paiddoc.get', $tpid, $tranId);
		$ret .= __project_info_paiddoc_menu($tpid,$projectInfo,$fundInfo,$tranId,$paiddocInfo);
		$ret .= R::PageWidget('project.info.paiddoc.view', [$projectInfo, $tranId])->build();
		return $ret;
	}

	//R::Model('project.nhso.obt.update',$fundInfo);


	$ret .= __project_info_paiddoc_menu($tpid, $projectInfo, $fundInfo);
	$paidDocs = R::Model('project.paiddoc.get', $tpid, NULL, NULL, '{getAllRecord: true, debug: false}');

	$tables = new Table();
	$tables->thead = array('เลขที่ใบเบิกเงิน', 'date' => 'ลงวันที่', 'amt -paid' => 'จำนวนเงินเบิก', 'amt -remain' => 'จำนวนเงินคงเหลือ', 'เลขอ้างอิง', 'สร้างเมื่อ', 'icons -nowrap' => '');

	if ($isEdit) {
		// Prepare input form
		$ret .= '<form id="project-add" class="sg-form" method="post" action="'.url('project/'.$tpid.'/info/paiddoc.add').' " data-checkvalid="true" xdata-rel="none">'._NL;

		$minDate = $projectInfo->info->date_approve;
		if ($minDate < $fundInfo->finclosemonth) $minDate = date('Y-m-d',strtotime($fundInfo->finclosemonth.' +1 days'));

		$tables->rows['input'] = array(
			'<label class="-hidden" for="project-edit-docno">เลขที่ใบเบิกเงิน</label><input id="project-edit-docno" class="form-text require -fill" type="text" name="data[docno]" placeholder="เลขที่ใบเบิก" />',
			'<label class="-hidden" for="project-edit-paiddate">ลงวันที่</label><input id="project-edit-paiddate" class="form-text sg-datepicker require -fill" type="text" name="data[paiddate]" placeholder="31/12/'.date('Y').'" size="10" data-max-date="'.date('d/m/Y').'" data-min-date="'.sg_date($minDate,'d/m/Y').'" readonly="readonly" />',
			'<label class="-hidden" for="project-edit-amount">จำนวนเงินเบิก</label><input id="project-edit-amount" class="form-text require -money -fill" type="text" name="data[amount]" placeholder="0.00" />',
			'',
			'<td colspan="3" style="text-align:center;"><button class="btn -primary" type="submit" value="สร้างใบเบิกเงิน"><i class="icon -material -white">done</i><span>สร้างใบเบิกเงิน</span></button></td>',
			'config'=>array('class'=>'-datainput')
		);
	} else {
		$tables->addConfig('showHeader', false);
	}


	$budgetRemain = $projectInfo->info->budget;
	if ($paidDocs) {
		$tables->rows[]='<header>';
		$tables->rows[]=array('','<td colspan="2" align="center"><b>งบประมาณ</b></td>','<b>'.number_format($projectInfo->info->budget,2).'</b>','','','');


		$totalPaid = $totalReturn = $totalExpense = 0;

		foreach ($paidDocs as $rs) {
			$isLock = $rs->paiddate <= $fundInfo->finclosemonth;
			$ui = new Ui('span');
			if ($isRight) {
				if ($rs->paidtype=='PAID') {
					$ui->add('<a href="'.url('project/'.$tpid.'/info.paiddoc/'.$rs->paidid).'" rel="nofollow"><i class="icon -material">find_in_page</i></a> <a href="'.url('project/'.$tpid.'/info.paiddoc/'.$rs->paidid,array('print'=>'yes')).'"><i class="icon -material">print</i></a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.moneyback/'.$rs->paidid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
					$ui->add('<a><i class="icon -material"></i></a>');
				}
				if ($isLock) {
					$ui->add('<i class="icon -material -gray">lock</i>');
				} else {
					$ui->add('<i class="icon -material -gray">lock_open</i>');
				}
			}
			$menu = '<nav class="nav -icons">'.$ui->build().'</nav>';

			$totalPaid += $rs->amount;

			if ($rs->paidtype == 'RET') {
				$totalReturn += $rs->amount;
			} else {
				$totalExpense += $rs->amount;
			}

			$tables->rows[] = array(
				$rs->docno.($rs->paidtype == 'RET' ? '(รับคืน)' : ''),
				$rs->paiddate ? sg_date($rs->paiddate, 'ว ดด ปปปป') : '???',
				number_format($rs->amount, 2),
				number_format($projectInfo->info->budget - $totalPaid, 2),
				$rs->refcode,
				sg_date($rs->created, 'ว ดด ปป'),
				$menu,
			);
		}

		$budgetRemain = $projectInfo->info->budget - $totalPaid;

		$tables->tfoot[] = array(
			'<td colspan="2">รวมเบิก</td>',
			$totalExpense ? number_format($totalExpense,2) : '-',
			'',
			'',
			'',
			'',
		);
		$tables->tfoot[] = array(
			'<td colspan="2">รวมรับคืน</td>',
			$totalReturn ? number_format($totalReturn,2) : '-',
			'',
			'',
			'',
			'',
		);
		$tables->tfoot[] = array(
			'<td colspan="2">รวมจ่าย/คงเหลือ</td>',
			$totalPaid ? number_format($totalPaid,2) : '',
			number_format($budgetRemain,2),
			'',
			'',
			'',
		);
	}
	if ($isEdit) $tables->rows['input'][3]='<b>'.number_format($budgetRemain,2).'</b>';
	$ret.=$tables->build();

	if ($isEdit) $ret.='</form>'._NL;

	if (!$paidDocs)	$ret.='<p align="center">*** ยังไม่มีรายการเบิก ***</p>';




	$ret.='<div class="photocard -projectrcv">'._NL;
	$stmt='SELECT * FROM %project_paiddoc% tr LEFT JOIN %topic_files% f ON (f.`tagname`="project,paiddoc" && f.`refid`=tr.`paidid`) OR f.`gallery`=tr.`gallery` WHERE '.($trid?'tr.`paidid`=:trid':'tr.`tpid`=:tpid').' AND `tagname`="project,paiddoc" ORDER BY f.`fid` DESC';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':trid',$trid);
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<ul id="projectrcv-photo" class="">'._NL;
	if ($dbs->items) {
		// Get photo from database
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`gallery`=:gallery AND `tagname`="project,paiddoc" ORDER BY `fid` DESC', ':gallery',$gallery);
		//$ret.=print_o($photos,'$photos');

		// Show photos
		foreach ($dbs->items as $item) {
			list($photoid,$photo)=explode('|',$item);
			if ($item->type=='photo') {
				$photo=model::get_photo_property($item->file);
				$photo_alt=$item->title;
				$ret .= '<li class="-hover-parent">';
				$ret.='<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$ret.=' />';
				$ret.='</a>';

				$ui=new Ui('span','iconset -hover');
				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -material -gray">cancel</i></a>');
				}
				$ret.=$ui->build();
				$ret .= '</li>'._NL;
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$item->file;
				$ret.='<li>';
				$ret.='<a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a>';
				/*
				$ui=new Ui('span','iconset -hover');
				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -delete"></i></a>');
				}
				$ret.=$ui->build();
				*/
				$ret.='</li>';
			}
		}
	}
	$ret.='</ul>'._NL;
	$ret.='</div><!--photo-->'._NL;

	//$ret.=__project_form_paiddoc_updoc($tpid,$topic,$fundInfo);
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='<style type="text/css">
	.button.-adddoc {margin:10px 0 10px 0; position: relative; right:0; display: inline-block;}

	.photocard>ul {margin:0; padding:0; list-style-type:none;}
	.photocard>ul>li {height: 200px; margin:0 4px 4px 0; float: left; overflow:hidden; position: relative; display: inline-block;}
	.photocard .photoitem {height:100%;}

	.photocard.-projectrcv>ul {clear: both;}
	.photocard .ui-action {position: absolute; top:4px; right:4px;}
	.photocard .ui-action>a {background: #fff; border-radius: 50%; display: inline-block;}
	.photocard .ui-action>a:hover .icon {border-radius: 50%; background-color: red;}
	.fileinput-button {padding:20px;}
	.col-icons {width:48px; text-align:center;}
	</style>';

	return $ret;
}

function __project_info_paiddoc_menu($tpid, $projectInfo, $fundInfo, $trid = NULL, $paiddocInfo = NULL) {
	$isLock = $paiddocInfo->paiddate <= $fundInfo->finclosemonth;
	$isRight = $projectInfo->info->isRight;
	$isEdit = $projectInfo->info->isEdit && !$isLock;


	$ui = new Ui(NULL, '-sg-text-right');
	$dropUi = new Ui();

	if ($trid) {
		$ui->add('<a class="btn -link"><i class="icon -material -gray">'.($isLock ? 'lock' : 'lock_open').'</i></a>');
		if ($isEdit) {
			$ui->add('<a class="sg-action btn'.($isLock ? ' -disabled' : '').'" href="'.url('project/'.$tpid.'/info.paiddoc.form/'.$trid).'" data-rel="box" data-width="640" rel="nofollow"><i class="icon -material">edit</i><span class="-hidden">แก้ไข</span></a>');
			$ui->add('<a class="sg-action btn'.($isLock ? ' -disabled' : '').'" href="'.url('project/'.$tpid.'/info/paiddoc.remove/'.$trid).'" data-title="ลบใบเบิกเงิน" data-confirm="ต้องการลบใบเบิกเงินนี้ กรุณายืนยัน?" data-rel="none" data-done="reload:'.url('project/'.$tpid.'/info.paiddoc').'" rel="nofollow"><i class="icon -material">delete</i><span class="-hidden">ลบใบเบิกเงิน</span></a>');
		}
		$ui->add('<a class="btn -primary" href="javascript:window.print()"><i class="icon -material -white">print</i></a>');

	} else {
		$ui->add('<a class="sg-action btn" href="'.url('project/'.$tpid.'/info.moneyback.form').'" data-rel="box" data-width="640"><i class="icon -material">undo</i><span>บันทึกการคืนเงิน</span></a>');
	}


	$ret .= '<nav class="nav -page -no-print" style="position:relative;margin:16px 0;">'._NL;
	if ($dropUi->count())
		$ui->add(sg_dropbox($dropUi->build(),'{class: "leftside"}'));
	$ret .= $ui->build();

	$ret .= '</nav>';

	//$ret.=print_o($fundInfo,'$fundInfo');
	//$ret.=print_o($paiddocInfo,'$paiddocInfo');
	return $ret;
}

?>