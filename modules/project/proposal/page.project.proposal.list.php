<?php
/**
* List All Project Proposal
* Created 2018-11-10
* Modify  2019-09-23
*
* @param Object $self
* @return String
*/

$debug = true;

import('model:project.proposal.php');

function project_proposal_list($self) {
	$getSet = post('set');
	$getOrg = post('org');
	$getChangwat = post('prov');
	$getAmpur = post('ampur');
	$getYear = post('year');
	$getStatus = post('status');
	$getSearch = post('searchdev');
	$getDownloadXls = post('download') == 'xls';

	$isAdmin = user_access('administer projects');

	// Init Project Template
	if ($getSet) {
		ProjectProposalModel::get($getSet, '{initTemplate: true}');
		$proposalInfo = (Object) array('info'=>(Object)array('parent' => $getSet));
	}

	//$ret .= print_o(post(),'post()');
	//$ret .= print_o($proposalInfo, '$proposalInfo');

	R::View('project.toolbar',$self,'ข้อเสนอโครงการ','proposal', $proposalInfo);

	$statusList = project_base::$statusList;

	$orders = array(
		'changwat' => 'provname',
		'title' => 'CONVERT(t.title USING tis620)',
		'create' => 't.created',
		'modify' => 't.changed',
		'hsmi' => 't.commenthsmidate',
		'sss' => 't.commentsssdate',
		'status' => 't.status',
	);

	$sorts = array(
		'changwat'=>'ASC',
		'title'=>'ASC',
		'status'=>'ASC,
		t.changed DESC',
	);

	$yearList = mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;

	$form = new Form(NULL, url('project/proposal/list'), NULL, '-inlineitem');
	$form->addConfig('method', 'GET');

	if ($getSet) $form->addField('set',array('type' => 'hidden', 'value' => $getSet));
	if ($getOrg) $form->addField('org',array('type' => 'hidden', 'value' => $getOrg));

	$yearOptions = array('' => '==ทุกปี==');

	$getYearList = mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;
	foreach (explode(',',$getYearList) as $item) $yearOptions[$item] = 'พ.ศ. '.($item+543);

	$form->addField(
		'year',
		array(
			'type' => 'select',
			'options' => $yearOptions,
			'value' => $getYear,
		)
	);

	$provOptions = array('' => '==ทุกจังหวัด==');
	$getChangwatDb = mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project-develop" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($getChangwatDb->items as $item) $provOptions[$item->changwat] = $item->provname;

	$form->addField(
		'prov',
		array(
			'type' => 'select',
			'options' => $provOptions,
			'value' => $getChangwat,
		)
	);

	$form->addField(
		'searchdev',
		array(
			'type' => 'text',
			'value' => $getSearch,
			'placeholder' => 'ค้นหาโครงการพัฒนา',
		)
	);

	$form->addField(
		'status',
		array(
			'type' => 'select',
			'options' => array('' => '==ทุกสถานะ==')+$statusList,
			'value' => $getStatus,
		)
	);

	if ($isAdmin) {
		$form->addField(
			'submit',
			array(
				'type'=>'button',
				'items'=>array(
					'go'=>array(
						'type'=>'submit',
						'class'=>'-primary',
						'btnvalue' => 'go',
						'value'=>'<i class="icon -material">search</i><span>ดูรายชื่อ</span>'
						),
					'download'=>array(
						'type'=>'submit',
						'btnvalue' => 'xls',
						'value'=>'<i class="icon -material">cloud_download</i><span>ดาวน์โหลด</span>'
						),
					),
				)
			);
	} else {
		$form->addField(
			'submit',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -material">search</i><span>ดูรายชื่อ</span>'
			)
		);
	}

	$ret.='<nav class="nav -page">'.$form->build().'</nav>';


	if ($getSet) mydb::where('t.`parent` = :parent', ':parent', $getSet);
	if ($getOrg) mydb::where('(t.`orgid` = :orgid OR o.`parent` = :orgid )', ':orgid', $getOrg);
	if ($getChangwat) mydb::where('t.changwat = :changwat', ':changwat',$getChangwat);
	if ($getAmpur) mydb::where('d.ampur = :ampur', ':ampur',$getAmpur);
	if ($getYear) mydb::where('d.`pryear` = :year',':year',$getYear);
	if ($getStatus) mydb::where('d.status = :status', ':status',$getStatus);
	if ($getSearch) mydb::where('t.`title` LIKE :search', ':search','%'.$getSearch.'%');

	$stmt = 'SELECT t.*
			, p.`tpid` isProject
			, u.`name`, cop.`provname`, r.`email` prid
			, d.`prid`, d.`status`, ps.`title` projectSetName
			, d.`budget`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_revisions% r USING(`revid`)
			LEFT JOIN %project% p ON p.`tpid`=d.`tpid`
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			LEFT JOIN %topic% ps ON t.`parent`=ps.`tpid`
			LEFT JOIN %users% u ON u.`uid`=t.`uid`
			LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
		%WHERE%
		ORDER BY '.SG\getFirst($orders[post('o')],'t.`changed`').'  '.SG\getFirst($sorts[post('o')],'DESC');

	$dbs = mydb::select($stmt,$where['value']);

	//$ret.=print_o($dbs,'$dbs');

	if ($isAdmin) {
		$inlineAttr['data-update-url'] = url('project/proposal/update/');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}

	$tables = new Table();
	$tables->addClass(($isAdmin ? ' inline-edit' : '').' -developlist');
	$tables->addConfig('id','project-develop-list');
	if ($inlineAttr) $tables->attr=sg_implode_attr($inlineAttr);
	$tables->caption = 'รายชื่อข้อเสนอโครงการ';

	$totalBudget = 0;

	if ($getDownloadXls) {
		$tables->thead = array(
			'no' => 'ลำดับ',
			'status' => 'สถานะ',
			'changwat' => 'จังหวัด',
			'title' => 'ชื่อโครงการ',
			'set' => 'ชุดโครงการ',
			'prid' => 'รหัสโครงการ',
			'owner' => 'เจ้าของโครงการ',
			'created' => 'วันที่เริ่มพัฒนา',
			'changed' => 'แก้ไขล่าสุด',
			'orgName' => 'สถาบันอุดมศึกษาหลัก',
			'orgnamedo' => 'ชื่อหน่วยงานหลัก',
			'coorg' => 'ชื่อหน่วยงานร่วม',
			'commune' => 'ชื่อชุมชน',
			'prowner' => 'ชื่อผู้รับผิดชอบ',
			'owner-address' => 'ที่อยู่ผู้รับผิดชอบ',
			'prphone' => 'การติดต่อ',
			'ownerJoin' => 'ชื่อผู้ร่วมโครงการ',
			'area' => 'พื้นที่ดำเนินงาน',
			'info-commune' => 'ข้อมูลพื้นฐาน',
			'info-potential' => 'ข้อมูลศักยภาพ/ทรัพยากร',
			'info-issue' => 'ข้อมูลประเด็นปัญหา',
			'info-need' => 'ข้อมูลความต้องการเชิงพื้นที่',
			'mainissue-1' => 'ปัญหาความยากจน',
			'mainissue-2' => 'ปัญหาความเหลื่อมล้ำ',
			'mainissue-3' => 'ปัญหาคุณภาพชีวิต',
			'category-31' => 'การเกษตรและเทคโนโลยีชีวภาพ',
			'category-32' => 'อาหารและการแปรรูป - ฮาลาล',
			'category-33' => 'การท่องเที่ยวกลุ่มรายได้ดี และการท่องเที่ยวเชิงคุณภาพ',
			'category-34' => 'ทรัพยากรธรรมชาติสิ่งแวดล้อมและ การจัดการภัยพิบัติ ทรัพยากรทางทะเลและชายฝั่ง ทะเลสาบสงขลา ประมงและการเพาะเลี้ยง',
			'category-35' => 'สังคมพหุวัฒนธรรม การศึกษา ภาษา',
			'category-36' => 'สุขภาพและการแพทย์',
			'category-37' => 'Digital Smart city and Creative Economy',
			'category-38' => 'การจัดการพลังงาน',
			'category-39' => 'สังคมสูงวัย (Aging Society)',
			'category-40' => 'ชุมชนท้องถิ่นเข้มแข็ง ภายใต้แผนปฏิรูปด้านสังคม',
			'knowledge' => 'องค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน',
			'objective' => 'วัตถุประสงค์',
			'target' => 'กลุ่มเป้าหมาย',
			'fromdate' => 'ระยะเวลา ตั้งแต่',
			'todate' => 'ระยะเวลา ถึง',
			'activity' => 'วิธีดำเนินงาน',
			'budget' => 'งบประมาณ',
			'output-commune' => 'ผลผลิต (Output) ต่อชุมชน	',
			'output-student' => 'ผลผลิต (Output) ต่อนักศึกษา',
			'outcome-commune' => 'ผลลัพธ์ (Outcome) ต่อชุมชน	',
			'outcome-student' => 'ผลลัพธ์ (Outcome) ต่อนักศึกษา',
			'impact-commune' => 'ผลกระทบ (Impact) ต่อชุมชน	',
			'impact-student' => 'ผลกระทบ (Impact) ต่อนักศึกษา',
		);

	} else {
		$tables->thead = array(
			'no' => '',
			'สถานะ <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'searchdev' => $getSearch,'o'=>'status','searchdev' => $getSearch)).'"><i class="icon -sort"></i></a>',
			'จังหวัด <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'searchdev' => $getSearch,'o'=>'changwat')).'"><i class="icon -sort"></i></a>',
			'ชื่อโครงการ <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'searchdev' => $getSearch,'o'=>'title')).'"><i class="icon -sort"></i></a>',
			'budget -money' => 'งบประมาณ',
			'วันที่เริ่มพัฒนา <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'searchdev' => $getSearch,'o'=>'create')).'"><i class="icon -sort"></i></a>',
			'date changed' => 'แก้ไขล่าสุด <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'searchdev' => $getSearch,'o'=>'modify')).'"><i class="icon -sort"></i></a>',
			/*
			'date changed-hsmi' => 'ความเห็นพี่เลี้ยง <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'o'=>'hsmi')).'"><i class="icon -sort"></i></a>',
			'date changed-sss' => 'ความเห็นผู้ทรงคุณวุฒิ <a href="'.url(q(),array('set'=>$getSet, 'year'=>$getYear,'prov'=>$getChangwat,'status'=>$getStatus,'o'=>'sss')).'"><i class="icon -sort"></i></a>',
			*/
			'icons -hover-parent' => ''
		);
	}

	$no = 0;
	define('_NEWLINE', '<br />');

	//$prSet=array('101'=>'โครงการร่วมสร้างชุมชนน่าอยู่','3074'=>'โครงการร่วมสร้างชุมชนน่าอยู่ - ชุดเล็ก');

	$prSets=mydb::select('SELECT * FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype`="ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620) ASC');
	foreach ($prSets->items as $item) $prSet[$item->tpid] = $item->title;

	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) {
			if ($getDownloadXls) {
				$row = array();
				foreach ($tables->thead as $key => $value) $row[$key] = '';
				$row['no'] = ++$no;
				$row['status'] = $statusList[$rs->status];
				$row['changwat'] = $rs->provname;
				$row['title'] = SG\getFirst($rs->title,'ไม่ระบุชื่อ');
				$row['set'] = $rs->projectSetName;
				$row['prid'] = $rs->prid;
				$row['owner'] = $rs->name;
				$row['created'] = $rs->created;
				$row['changed'] = $rs->changed;

				$proposalInfo = ProjectProposalModel::get($rs->tpid);

				$row['orgName'] = $proposalInfo->info->orgName;
				$row['orgnamedo'] = $proposalInfo->data['orgnamedo'];
				$row['coorg'] = $proposalInfo->data['project-coorg'];
				$row['commune'] = $proposalInfo->data['commune'];
				$row['prowner'] = $proposalInfo->data['prowner'];
				$row['owner-address'] = $proposalInfo->data['owner-address'];
				$row['prphone'] = $proposalInfo->data['prphone'];
				$row['ownerJoin'] = nl2br($proposalInfo->data['owner-join']);

				$row['area'] = '???';

				$row['info-commune'] = $proposalInfo->data['info-commune'];
				$row['info-potential'] = nl2br($proposalInfo->data['info-potential']);
				$row['info-issue'] = nl2br($proposalInfo->data['info-issue']);
				$row['info-need'] = nl2br($proposalInfo->data['info-need']);
				$row['mainissue-1'] = $proposalInfo->data['mainissue-1'] ? 'YES' : '';
				$row['mainissue-2'] = $proposalInfo->data['mainissue-2'] ? 'YES' : '';
				$row['mainissue-3'] = $proposalInfo->data['mainissue-3'] ? 'YES' : '';
				$row['category-31'] = $proposalInfo->data['category-31'] ? 'YES' : '';
				$row['category-32'] = $proposalInfo->data['category-32'] ? 'YES' : '';
				$row['category-33'] = $proposalInfo->data['category-33'] ? 'YES' : '';
				$row['category-34'] = $proposalInfo->data['category-34'] ? 'YES' : '';
				$row['category-35'] = $proposalInfo->data['category-35'] ? 'YES' : '';
				$row['category-36'] = $proposalInfo->data['category-36'] ? 'YES' : '';
				$row['category-37'] = $proposalInfo->data['category-37'] ? 'YES' : '';
				$row['category-38'] = $proposalInfo->data['category-38'] ? 'YES' : '';
				$row['category-39'] = $proposalInfo->data['category-39'] ? 'YES' : '';
				$row['category-40'] = $proposalInfo->data['category-40'] ? 'YES' : '';
				$row['knowledge'] = nl2br($proposalInfo->data['knowledge']);
				$row['objective'] = '???วัตถุประสงค์';
				$row['target'] = '???กลุ่มเป้าหมาย';
				$row['fromdate'] = $proposalInfo->info->date_from;
				$row['todate'] = $proposalInfo->info->date_end;
				$row['activity'] = '???วิธีดำเนินงาน';
				$row['budget'] = $proposalInfo->info->budget;
				$row['output-commune'] = nl2br($proposalInfo->data['output-commune']);
				$row['output-student'] = nl2br($proposalInfo->data['output-student']);
				$row['outcome-commune'] = nl2br($proposalInfo->data['outcome-commune']);
				$row['outcome-student'] = nl2br($proposalInfo->data['outcome-student']);
				$row['impact-commune'] = nl2br($proposalInfo->data['impact-commune']);
				$row['impact-student'] = nl2br($proposalInfo->data['impact-student']);

				$areaStr = '';
				$areaNo = 0;
				foreach ($proposalInfo->area as $key => $value) {
					$areaStr .= (++$areaNo).'. '.SG\implode_address($value)._NEWLINE;
				}
				$row['area'] = $areaStr;

				$objectiveStr = '';
				$objectiveNo = 0;
				foreach ($proposalInfo->objective as $key => $value) {
					$objectiveStr .= (++$objectiveNo).'. '.$value->title._NEWLINE._NEWLINE.'ตัวชี้วัด '.nl2br($value->indicatorDetail)._NEWLINE._NEWLINE.'ขนาดปัญหา '.$value->problemsize._NEWLINE._NEWLINE.'เป้าหมาย '.$value->targetsize._NEWLINE._NEWLINE;
				}
				$row['objective'] = $objectiveStr;

				$targetStr = '';
				$targetNo = 0;
				foreach ($proposalInfo->target as $key => $value) {
					$targetStr .= (++$targetNo).'. '.$value->tgtid.' จำนวน '.$value->amount._NEWLINE;
				}
				$row['target'] = $targetStr;

				$activityStr = '';
				$activityNo = 0;
				foreach ($proposalInfo->activity as $key => $value) {
					$activityStr .= 'กิจกรรมที่ '.(++$activityNo).'. '.$value->title._NEWLINE._NEWLINE
						. 'รายละเอียดกิจกรรม : '._NEWLINE.nl2br($value->desc)._NEWLINE
						. 'ระยะเวลาดำเนินงาน : '.$value->fromdate.' ถึง '.$value->todate._NEWLINE
						. 'ผลผลิต (Output) / ผลลัพธ์ (Outcome) : '._NEWLINE.nl2br($value->output)._NEWLINE
						. 'ทรัพยากรอื่น ๆ : '._NEWLINE.nl2br($value->otherresource)._NEWLINE
						. 'ภาคีร่วมสนับสนุน : '._NEWLINE.nl2br($value->copartner)._NEWLINE;

					$expNo = 0;
					$expenseTotal = 0;
					$activityStr .= 'รายละเอียดงบประมาณ'._NEWLINE;
					foreach ($value->expense as $expId) {
						$expenseInfo = $proposalInfo->expense[$expId];
						$activityStr .= (++$expNo).'. '
							. $expenseInfo->expName.($expenseInfo->detail ? ' ('.$expenseInfo->detail.')' : '').' '
							. 'จำนวน '.$expenseInfo->amt.' '.$expenseInfo->unitname.' '
							. $expenseInfo->unitprice.' บาท '
							. $expenseInfo->times.' ครั้ง '
							. 'รวมเงิน '.$expenseInfo->total.' บาท'
							. _NEWLINE;

						$expenseTotal += $expenseInfo->total;
					}
					$activityStr .= 'รวมค่าใช้จ่าย '.number_format($expenseTotal,2).' บาท'._NEWLINE._NEWLINE;
				}
				$row['activity'] = $activityStr;

				//$ret .= print_o($proposalInfo, '$proposalInfo');

				$tables->rows[] = $row;

			} else {
				$today=date('Y-m-d');
				if (empty($rs->changed)) {
					$changed='';
				} else if ($today==sg_date($rs->changed,'Y-m-d')) {
					$changed=sg_date($rs->changed,'H:i:s').' น.';
				} else {
					$changed=sg_date($rs->changed,'ว ดด ปป H:i').' น.';
				}
				if (sg_date($rs->created,'Y-m-d')==$today) {
					$created='วันนี้ '.sg_date($rs->created,'H:i').' น.';
				} else {
					$created=sg_date($rs->created,'ว ดด ปป');
				}
				unset($row);
				$row[]=++$no;
				$row[] = '<a class="sg-action btn -link" href="'.url('project/proposal/'.$rs->tpid.'/info.status').'" data-rel="box" data-width="640"><i class="icon -material">assignment_turned_in</i><span>'.$statusList[$rs->status].'</span></a>';
				/*
				view::inlineedit(
					array('group'=>'dev','fld'=>'status','tpid'=>$rs->tpid),$statusList[$rs->status],$isAdmin,'select',$statusList)
					.($rs->isProject ? '<p><a href="'.url('paper/'.$rs->tpid).'" target="_blank">โครงการติดตาม</a></p>' : ($isAdmin && $rs->status==10 ? '<p id="move-'.$rs->tpid.'"><a class="sg-action button" data-rel="#move-'.$rs->tpid.'" href="'.url('project/proposal/createproject/'.$rs->tpid).'" data-confirm="ยืนยันการสร้างโครงการติดตาม">สร้างโครงการติดตาม</a></p>':''));
					*/
				$row[]=$rs->provname;
				$row[]='<a href="'.url('project/proposal/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'
					.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
					//.($prSet?'<br />ชุดโครงการ '.view::inlineedit(array('group'=>'topic','fld'=>'parent','tpid'=>$rs->tpid),$rs->projectSetName,$isAdmin,'select',$prSet) : '')
					//.'<br />รหัสโครงการ : '.view::inlineedit(array('group'=>'dev','fld'=>'prid','tpid'=>$rs->tpid, 'class'=>'inline'),$rs->prid,$isAdmin)
					.'<br />โดย '.$rs->name;
				$row[] = number_format($rs->budget,2);
				$row[]=$created;
				$row[]=$changed;
				/*
				$row[]=$rs->commenthsmidate?sg_date($rs->commenthsmidate,'ว ดด ปป H:i'):'';
				$row[]=$rs->commentsssdate?sg_date($rs->commentsssdate,'ว ดด ปป H:i'):'';
				*/
				$row[]='<nav class="nav iconset -hover"><a href="'.url('project/proposal/'.$rs->tpid).'"><i class="icon -viewdoc"></i></a><a href="'.url('project/proposal/'.$rs->tpid).'" onclick="printExternal(this.href);return false;"><i class="icon -print"></i></a></nav>';
				$row['config']=array('class'=>'project-develop-status-'.$rs->status);
				$tables->rows[]=$row;

				$totalBudget += $rs->budget;
			}
		}

		if ($getDownloadXls) {
			$ret = $tables->build();
			//die($tables->build());
			/*
			header('Content-Type: application/vnd.ms-excel');
			header("Content-Type: text/xls; charset=UTF-8");
			header('Content-disposition: attachment; filename='.rand().'.xls');
			die($tables->build());
			die(R::Model('excel.export',$tables,'Proposal-'.$getYear.($getProv ? '-'.$getProv : '').' '.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
			*/
		} else {
			$tables->tfoot[] = array('<td></td>','','','รวม',number_format($totalBudget,2),'','');
			$ret .= $tables->build();
		}

	} else {
		$ret .= message('notify','ไม่มีโครงการที่กำลังพัฒนาตามเงื่อนไขที่ระบุ');
	}



	head('<script type="text/javascript" >
function printExternal(str) {
	printWindow = window.open(  str,"mywindow");
	setTimeout("printWindow.print()", 2000);
	//printWindow.close()
	setTimeout("printWindow.close()", 2000);
}
$(document).on("change","form#project-develop select",function() {
	var $this=$(this)
	var para=$this.closest("form").serialize()
	notify("กำลังโหลด")
	location.replace(window.location.pathname+"?"+para)
});

</script>');
	return $ret;
}
?>