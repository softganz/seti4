<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_care_view($self, $psnId = NULL) {
	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{data: "info"}');
	$psnId = $psnInfo->psnId;

	$orgId = post('org');

	if (!$psnId) return message('error','ไม่มีข้อมูลของผู้ป่วยที่ระบุ');


	// $ret .= R::View('imed.toolbox',$self,'iMed@Care Plan', 'social');


	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) {
		$ret .= message('error',$psnInfo->error);
		return $ret;
	}


	//$ret .= '<div class="imed-sidebar"><h3>'.$orgInfo->name.'</h3>'.R::View('imed.menu.group',$orgInfo).'</div>';


	//$ret .= '<div id="imed-app" class="imed-app">'._NL;


	$ui = new Ui();
	if ($orgId) {
		$ui->add('<a class="sg-action btn -primary" href="'.url('imed/care/'.$psnId.'/create',array('org'=>$orgId)).'" data-rel="#imed-app" data-title="Create Care Plan" data-confirm="ต้องการสร้าง Care Plan ใหม่ กรุณายืนยัน?"><i class="icon -material -white">add</i><span>Cretae Care Plan</span></a>');
	}
	$ui->add('<a class="sg-action" href="'.url('imed/care/'.$psnId, array('org'=>$orgId)).'" data-rel="#imed-app"><i class="icon -material">assignment</i></a>');
	$ui->add('<a class="" href="'.url('imed',['pid'=>$psnId]).'" role="patient" data-pid="'.$psnId.'"><i class="icon -material">accessible</i><span class="-hidden">เยี่ยมบ้าน</span></a>', '{class: "-visit"}');

	$dropUi = new Ui();
	$dropUi->add('<a class="sg-action" href="'.url('imed/care/'.$psnId).'" data-rel="#imed-app"><i class="icon -material">account_box</i><span>Information</span></a>');
	$ui->add(sg_dropbox($dropUi->build()));

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient').'" data-rel="#imed-app"><i class="icon -material">arrow_back</i></a></nav><h3>Care Plan @'.$psnInfo->realname.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	//$ret .= '<nav class="nav -page -sg-text-right">'.$ui->build().'</nav>';


	$stmt = 'SELECT p.*
					, u.`name` `ownerName`
					, COUNT(tr.`cpid`) `amtPlan`
					, COUNT(tr.`seq`) `amtDone`
					FROM %imed_careplan% p
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %imed_careplantr% tr USING (`cpid`)
					WHERE `psnid` = :psnid AND p.`orgid` = :orgid
					GROUP BY `cpid`';

	$dbs = mydb::select($stmt, ':psnid', $psnId, ':orgid', $orgId);


	//$ret .= '<div id="imed-care-plan-list">';

	$tables = new Table();
	$tables->addClass('-sg-is-desktop');
	$tables->thead = array('make -date' => 'วันที่จัดทำ', 'ผู้จัดทำแผน', 'การวินิจฉัยโรค', 'tai -center' => 'TAI' , 'adl -amt' => 'ADL', 'plan -amt' => 'รายการแผน', 'done -amt -hover-parent' => 'ดำเนินการ');

	$cardUi = new Ui('div a','ui-card -sg-is-mobile -card -flex');
	foreach ($dbs->items as $rs) {

		$cardStr = '<div class="header"><b>@'.sg_date($rs->datemake, 'ว ดด ปปปป').' โดย '.$rs->ownerName.'</b>';
		$cardStr .= '<nav class="nav -icons -header -sg-text-right"></nav>';
		$cardStr .= '</div>';

		$cardStr .= '<div class="detail -plan">'._NL;
		$cardStr .= 'การวินิจฉัยโรค '.$rs->diagnose.'<br />TAI '.$rs->tai.' ADL '.$rs->adl.'<br />';
		$cardStr .= 'แผนงาน '.number_format($rs->amtPlan).' รายการ ';
		$cardStr .= 'ดำเนินการ '.number_format($rs->amtDone).' รายการ ';
		$cardStr .= '</div>';
		//$cardStr .= '</a>';

		$cardUi->add(
			$cardStr,
			array('href'=>url('imed/care/'.$rs->psnid.'/plan/'.$rs->cpid), 'class'=>'sg-action', 'data-rel' => "#imed-app", "data-webview" => true, "data-webview-title" => "แผนการดูแล")
		);

		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('imed/care/'.$rs->psnid.'/plan/'.$rs->cpid).'" data-rel="#imed-app"><i class="icon -material">find_in_page</i></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
			sg_date($rs->datemake, 'ว ดด ปปปป'),
			$rs->ownerName,
			$rs->diagnose,
			$rs->tai,
			$rs->adl,
			$rs->amtPlan ? $rs->amtPlan : '',
			($rs->amtDone ? $rs->amtDone : '')
			. $menu,
		);
	}
	$ret .= $cardUi->build();

	$ret .= $tables->build();

	/*
	$tables = new Table();
	$tables->thead = array('make -date' => 'วันที่จัดทำ', 'การวินิจฉัยโรค', 'plan -amt' => 'แผน', 'done -amt -hover-parent' => 'เรียบร้อย');
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('imed/care/'.$rs->psnid.'/plan/'.$rs->cpid).'" data-rel="#imed-app"><i class="icon -material">find_in_page</icon></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
												sg_date($rs->datemake, 'ว ดด ปปปป')
												. '<br />'.$rs->ownerName,
												$rs->diagnose
												.'<br />TAI '.$rs->tai.' ADL '.$rs->adl,
											//	,
											//	,
												$rs->amtPlan ? $rs->amtPlan : '',
												($rs->amtDone ? $rs->amtDone : '')
												. $menu,
											);
	}
	$ret .= $tables->build();

	$tables = new Table();
	$tables->thead = array('make -date' => 'วันที่จัดทำ', 'ผู้จัดทำ', 'การวินิจฉัยโรค', 'plan -amt' => 'แผน', 'done -amt -hover-parent' => 'เรียบร้อย');
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('imed/care/'.$rs->psnid.'/plan/'.$rs->cpid).'" data-rel="#imed-app"><i class="icon -material">find_in_page</icon></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$tables->rows[] = array(
												sg_date($rs->datemake, 'ว ดด ปปปป'),
												$rs->ownerName,
												$rs->diagnose,
												$rs->tai,
												$rs->adl,
												$rs->amtPlan ? $rs->amtPlan : '',
												($rs->amtDone ? $rs->amtDone : '')
												. $menu,
											);
	}
	$ret .= $tables->build();
	*/

	//$ret .= print_o($dbs);

	//$ret .= print_o($psnInfo, '$psnInfo');

	//$ret .= '</div><!-- imed-care-plan-list -->';

	return $ret;
}
?>