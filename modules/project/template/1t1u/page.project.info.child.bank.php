<?php
/**
* Project : Follow Dashboard for App
* Created 2021-02-26
* Modify  2021-02-26
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.dashboard_app
*/

$debug = true;

function project_info_child_bank($self, $projectInfo) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$getStatus = post('status');
	$getTambon = post('child');
	$getHideTitle = post('hideTitle');

	$isAdmin = is_admin();
	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	$cfgFollow = cfg('project')->follow;

	$tambonList = R::Model('project.follows', '{childOf: '.$projectId.', ownerType: "'._PROJECT_OWNERTYPE_TAMBON.'"}', '{items: "*", order: "CONVERT(t.`title` USING tis620)", sort: "ASC", debug: false}');
	$tambonOptions = array('' => 'ทุกตำบล');
	foreach ($tambonList->items as $rs) $tambonOptions[$rs->tpid] = $rs->title;



	// View Model
	$toolbar = new Toolbar($self, $projectInfo->title);
	$form = new Form(NULL, url('project/'.$projectId.'/info.child.bank'), NULL, 'sg-form -sg-flex -justify-left');
	$form->addData('rel', '#main');
	$form->addField(
		'status',
		array(
			'type' => 'select',
			'options' => array('' => 'ทั้งหมด', '1' => 'แก้ไข', '9' => 'ยืนยัน', 'no' => 'ยังไม่ปรับปรุง','nobank' => 'ไม่มีเลขบัญชี','nocid' => 'ไม่มีเลข 13 หลัก'),
			'value' => $getStatus,
		)
	);

	$form->addField(
		'child',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $tambonOptions,
			'value' => $getTambon,
			'container' => '{style: "width:100px;"}',
		)
	);

	$form->addField(
		'hideTitle',
		array(
			'type' => 'checkbox',
			'options' => array('yes' => 'ปิดชื่อโครงการ'),
			'value' => $getHideTitle,
		)
	);

	$form->addField(
		'go',
		array('type' => 'button', 'value' => '<i class="icon -material">search</i>'),
	);

	$toolbar->addNav('form', $form);

	$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>ยืนยันบัญชีธนาคาร</h3></header>';;

	mydb::where('p.`project_status` = "กำลังดำเนินโครงการ" AND t.`parent` IN (SELECT `tpid` FROM %topic% WHERE `parent` = :projectId)', ':projectId', $projectId);
	if ($getStatus == 'no') {
		mydb::where('c.`fldref` IS NULL');
	} else if ($getStatus == 'nobank') {
		mydb::where('(p.`bankno` IS NULL OR p.`bankno` = "")');
	} else if ($getStatus == 'nocid') {
		mydb::where('(pn.`cid` IS NULL OR pn.`cid` = "")');
	} else if ($getStatus) {
		mydb::where('c.`fldref` = :status', ':status', $getStatus);
	}
	if ($getTambon) {
		mydb::where('t.`parent` = :tambon', ':tambon', $getTambon);
	}

	$childDbs = mydb::select(
		'SELECT
			p.`tpid` `projectId`, t.`title`
		, p.`bankaccount`, p.`bankno`, p.`bankname`
		, p.`ownertype`
		, pn.`cid`, pn.`phone`
		, u.`email`
		, pn.`graduated`, pn.`faculty`
		, c.`fldref` `bankChackStatus`
		, tp.`title` `parentTitle`
		, cop.`provname` `changwatName`
		, cod.`distname` `ampurName`
		, cos.`subdistname` `tambonName`
		-- , COUNT(*) `amt`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
			LEFT JOIN %db_person% pn ON pn.`userid` = t.`uid`
			LEFT JOIN %users% u ON u.`uid` = t.`uid`
			LEFT JOIN %bigdata% c ON c.`keyname` = "project.info" AND c.`keyid` = p.`tpid` AND c.`fldname` = "bankcheck"
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`, 4)
			LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(t.`areacode`, 6)
		%WHERE%
		-- GROUP BY `projectId` HAVING `amt`>1
		ORDER BY CONVERT(`parentTitle` USING tis620) ASC, `ownertype` ASC, CONVERT(t.`title` USING tis620) ASC
		'
	);

	$tables = new Table();
	$tables->thead = array(
		'no' => '',
		'title' => 'ชื่อโครงการ',
		'bankaccount -nowrap' => 'ชื่อบัญชี',
		'type -center -nowrap' => 'ประเภท',
		'cid -center -nowrap' => 'เลขบัตรประชาชน',
		'phone -center -nowrap' => 'โทรศัพท์',
		'email' => 'อีเมล์',
		'nabkno -center -nowrap' => 'เลขที่บัญชี',
		'bank -center' => 'ธนาคาร',
		'สถานศึกษา',
		'คณะ',
		'action -noprint' => ''
	);
	if ($getHideTitle) unset($tables->thead['title']);

	$tables->addConfig('showHeader', false);
	$no = 0;
	foreach ($childDbs->items as $rs) {
		if ($rs->parentTitle != $currentParentTitle) {
			$columnCount = 12;
			if ($hideTitle) $columnCount--;
			$tables->rows[] = array('<th colspan="'.$columnCount.'">'.$rs->parentTitle.'</th>');
			$currentParentTitle = $rs->parentTitle;
			$tables->rows[] = '<header>';
			$no = 0;
		}

		switch ($rs->bankChackStatus) {
			case 9: $checkIcon = '<i class="icon -material -sg-active">check_circle</i>'; break;
			case 1: $checkIcon = '<i class="icon -material -sg-active">check_circle_outline</i>'; break;
			default: $checkIcon = '<i class="icon -material -sg-inactive">check_circle_outline</i>'; break;
		}

		$phone = substr_replace($rs->phone, '-', 3, 0);
		$phone = substr_replace($phone, '-', 7, 0);
		$row = array(
			++$no,
			'title' => $rs->title.'@'.$rs->parentTitle,
			$rs->bankaccount,
			$cfgFollow->ownerType->{$rs->ownertype}->title,
			$rs->cid,
			$phone,
			$rs->email,
			$rs->bankno,
			$rs->bankname,
			$rs->graduated,
			$rs->faculty,
			'<nav class="nav -icons"><ul><li><a class="btn -link">'.$checkIcon.'</a></li>'
			. '<li><a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.child.bank.edit/'.$rs->projectId).'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a></li></ul></nav>',
		);
		if ($getHideTitle) unset($row['title']);
		//debugMsg('title '.$getHideTitle);
		//debugMsg($row, '$row');
		$tables->rows[] = $row;
	}

	$ret .= '<div style="overflow: auto;">'.$tables->build().'</div>';

	$ret .= '<p>รวมทั้งหมด <b>'.$childDbs->count().'</b> คน</p>';

	$ret .= '<nav class="-noprint">'.$form->build().'</nav>';

	$ret .= '<div class="-noprint"><i class="icon -material -sg-active">check_circle</i> ยืนยันข้อมูลธนาคารแล้ว<br /><i class="icon -material -sg-active">check_circle_outline</i> แก้ไขบัญชีธนาคารแล้ว<br /><i class="icon -material -gray">check_circle_outline</i> ยังไม่ได้เข้ามายืนยัน/แก้ไข</div>';

	//$ret .= print_o($childDbs, '$childDbs');
	return $ret;
}
?>