<?php
/**
*  Print invite registration sign form
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @param Array $_POST
* @return String
*/
function project_join_printregister($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$getOrderBy = SG\getFirst(post('o'),'no');
	$getPrintProv = post('pageprov');
	$getGroup = SG\getFirst(post('group'));
	$searchText = SG\getFirst(post('search'));
	$getOrderBy = SG\getFirst(post('o'),'no');
	$getSortDir = SG\getFirst(post('s'),'d');
	$getOrgName = post('orgname');

	$ret = '';



	if ($getPrintProv) $getOrderBy = 'prov';

	$isMember = $projectInfo->info->membershipType;
	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($projectInfo->RIGHT & _IS_EDITABLE) || $isMember;



	if (!isset($getGroup)) {

		$joinGroupList = object_merge((object) array('*'=>'== แสดงรายชื่อทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));

		$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/printregister'), NULL, 'sg-form -inlineitem -no-print');
		$form->addConfig('method', 'GET');
		$form->addData('rel', '#report-output');

		$form->addField(
			'group',
			array(
				'type' => 'select',
				'options' => $joinGroupList,
				'value' => $getGroup,
				'attr' => array('onchange' => '$(this).parent(form).submit()'),
			)
		);

		$form->addField(
			'orgadd',
			array(
				'type' => 'checkbox',
				'options' => array('1'=>'ป้อนชื่อองค์กร'),
				'attr' => 'onclick="$(\'#form-item-edit-orgname\').show()"',
			)
		);

		$form->addField(
			'pageprov',
			array(
				'type' => 'checkbox',
				'value' => post('pageprov'),
				'options' => array('1'=>'พิมพ์แยกจังหวัด'),
			)
		);

		$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));

		$form->addField(
			'orgname',
			array(
				'type' => 'text',
				//'label' => 'ชื่อองค์กร',
				'class' => '-fill',
				'value' => $getOrgName,
				'placeholder' => 'ระบุชื่อองค์กรสำหรับแสดงบนส่วนหัวของใบลงทะเบียน',
				'container' => '{class: "-fill", style: "display: none; width: 100%;"}',
			)
		);

		$self->theme->navbar = $form->build();
		$ret .= '<div id="report-output"></div>';

		head ('<style type="text/css">
		.item.-register-print {width: 100%;}
		.item caption {background:#fff;}
		.item>thead>tr>th { padding:4px; vertical-align: middle; font-weight: normal; white-space: nowrap; }
		.item>thead>tr:nth-child(3)>th {white-space: nowrap;}
		.item>tbody>tr>td:first-child {text-align: center;}
		.item.-register-print>tbody>tr>td:nth-child(2) {white-space: nowrap;}

		@media print {
			body,.page {margin:0; padding:0;}
			.page.-content {margin: 0; padding: 0;}
			.toolbar.-main {display: none;}
			.register.-header {margin: 0; padding: 0;}
			table.item {border:1px #ccc solid;}
			.col-signature {widthL:1in; white-space:nowrap;}
			.col-name {white-space:nowrap;}
			.item>tbody>tr>td {padding:4px; border-right:1px #ccc solid;}
			.item.-register-print>thead>tr>th {border-right:1px #ccc solid;}

			#header-wrapper {display:none;}
			.col.-sign {width: 2cm;}
		}
		</style>'
		);
		return $ret;
	}



	// Show All of Register
	// Only show for auth

	$getConditions->doid = $projectInfo->doingInfo->doid;
	$getConditions->regtype = 'Invite';
	//$getConditions->jointype = 'register';
	if ($getGroup && $getGroup != '*') $getConditions->joingroup = $getGroup;
	if ($searchText) $getConditions->search = $searchText;

	$joinList = R::Model('project.join.get', $getConditions, '{debug: false, order: "'.$getOrderBy.'", limit: "*"}');

	//$ret .= mydb()->_query;

	//$ret .= print_o($joinList, '$joinList');


	$dateFrom =  new DateTime($projectInfo->calendarInfo->from_date);
	$dateTo = new DateTime($projectInfo->calendarInfo->to_date);
	$dateDiff = $dateTo->diff($dateFrom)->format("%a");

	//$ret .= 'Diff = '.$dateDiff;

	$headerText = '<div class="register -header -sg-text-center"><h3>'.SG\getFirst($getOrgName,$projectInfo->doingInfo->orgname).'</h3>รายนามผู้เข้าร่วมการประชุม<br />“'.$projectInfo->doingInfo->doings.'”<br />'.($dateDiff > 0 ? 'ระหว่าง' : '').'วันที่ '
		. sg_date($projectInfo->calendarInfo->from_date,'ว'.(sg_date($projectInfo->calendarInfo->from_date,'Y-m') == sg_date($projectInfo->calendarInfo->to_date,'Y-m') ? '' : ' ดด ปปปป'))
		. ($dateDiff > 0 ? ' - ' : '')
		. sg_date($projectInfo->calendarInfo->to_date,($projectInfo->calendarInfo->from_date == $projectInfo->calendarInfo->to_date ? '' : ' ว').' ดด ปปปป')
		. ' ณ '.$projectInfo->doingInfo->place.'</div>';

	//if (!$getPrintProv) $ret .= $headerText;

	$tables = new Table();
	$tables->addClass('-register-print');
	$tables->colgroup = array('no'=>'');

	$rowSpan = $dateDiff > 0 ? 2 : 1;

	$tables->thead = '<tr>'
		. '<th class="col -nowrap" rowspan="'.$rowSpan.'">ลำดับ <a class="sg-action -no-print" href="'.url(q(),array('o'=>'no','group'=>$getGroup)).'" data-rel="#report-output"><i class="icon -material'.($getOrderBy == 'no' ? '' : ' -sg-inactive').'">unfold_more</i></a></th>'
		. '<th rowspan="'.$rowSpan.'">ชื่อ - สกุล<a class="sg-action -no-print" href="'.url(q(),array('o'=>'name','group'=>$getGroup)).'" data-rel="#report-output"><i class="icon -material'.($getOrderBy == 'name' ? '' : ' -sg-inactive').'">unfold_more</i></a></th>'
		. '<th rowspan="'.$rowSpan.'">หน่วยงานและสถานที่ติดต่อ<a class="sg-action -no-print" href="'.url(q(),array('o'=>'prov','group'=>$getGroup)).'" data-rel="#report-output"><i class="icon -material'.($getOrderBy == 'prov' ? '' : ' -sg-inactive').'">unfold_more</i></th>'
		. '<th rowspan="'.$rowSpan.'">โทรศัพท์</th>'
		. '<th'.($dateDiff > 0 ? ' colspan="'.($dateDiff+1).'"' : '').'>ลายเซ็นต์</th>'
		. '</tr>';
	if ($dateDiff > 0) {
		$tables->thead .= '<tr>';
		for ($i = 0; $i <= $dateDiff; $i++) {
			$tables->thead .= '<th class="col -date -d1">'.sg_date(strtotime($projectInfo->calendarInfo->from_date.' +'.$i.' days'),'ว ดด ปป').'</th>';
		}
		$tables->thead .= '<tr>';
	}

	$tables->colgroup = array('no'=>'','name -nowrap'=>'','address'=>'','phone -nowrap'=>'');
	for ($i = 0; $i <= $dateDiff; $i++) {
		$tables->colgroup['sign -d'.($i+1).($i == $dateDiff ? ' -hover-parent' : '')] = '';
	}

	$isFirstItem = true;


	$currentProv = reset($joinList)->changwatName;

	foreach ($joinList as $rs) {
		unset($row, $rs->zip);

		if ($getPrintProv && $currentProv != $rs->changwatName) {
			$ret .= $headerText;
			$ret .= $tables->build();
			$ret .= '<hr class="pagebreak" />';
			unset($tables->rows);
			$currentProv = $rs->changwatName;
			$no = 0;
		}

		$menu = '';
		if ($getOrderBy == 'no') {
			$ui = new Ui();
			if ($isFirstItem && $rs->printweight) {
				; // Not show move up
			} else {
				$ui->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/moveup/'.$rs->psnid).'" data-rel="notify" data-done="load:#main:'.url(q(),array('o'=>$getOrderBy,'group'=>$getGroup)).'"><i class="icon -material">keyboard_arrow_up</i></a>');
			}
			if (!empty($rs->printweight)) {
				$ui->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/movedown/'.$rs->psnid).'" data-rel="notify" data-done="load:#main:'.url(q(),array('o'=>$getOrderBy,'group'=>$getGroup)).'"><i class="icon -material">keyboard_arrow_down</i></a>');
			}
			$menu = '<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';
		}
		$row = array(
			++$no,
			trim($rs->prename.' '.$rs->firstname.' '.$rs->lastname),
			($rs->orgname ? $rs->orgname.'<br />' : '')
			. SG\implode_address($rs,'short'),
			$rs->phone
		);
		for ($i = 0; $i <= $dateDiff; $i++) {
			$row[] = '&nbsp;' . ($i == $dateDiff ? $menu : '');
		}

		$tables->rows[]=$row;
		$isFirstItem = false;
	}

	$ret .= $headerText;
	$ret .= $tables->build();

	//$ret .= print_o($joinList, '$joinList');



	if (post('download')) {
		sendheader('application/octet-stream');
		mb_internal_encoding("UTF-8");
		header('Content-Disposition: attachment; filename="'.mb_substr($rs->doings,0,50).'-ลงทะเบียน.xls"');

		$ret='<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<HTML>
<HEAD>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta http-equiv="Content-Language" content="th" />
</HEAD>
<BODY>
'.$ret.'
</BODY>
</HTML>';
		die($ret);
	}





	$ret.='<p class="-no-print">หมายเหตุ : รายชื่อในแบบฟอร์มลงทะเบียน จะแสดงเฉพาะชื่อผู้ที่ถูกเชิญเข้าร่วมเท่านั้น</p>';
	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>