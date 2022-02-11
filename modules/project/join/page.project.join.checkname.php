<?php
/**
* Project Action Join Check Register List
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_checkname($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$showJoinGroup = post('group');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isAuthRefCode = $_SESSION['auth.join.refcode'];



	$joinGroup = object_merge((object) array('*'=>'== แสดงรายชื่อทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));


	$ret .= '<nav class="nav -page -no-print">';
	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/checkname'), NULL, 'sg-form form -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addData('rel', '#main');
	$form->addField('group',
		array(
			'type' => 'select',
			'options' => $joinGroup,
			'value' => $showJoinGroup,
			'attr' => array('onchange' => '$(this).parent(form).submit()'),
		)
	);
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));
	$ret .= $form->build();
	$ret .= '</nav>';

	// Show All of Register
	// Only show for auth
	mydb::where('d.`tpid` = :tpid AND d.`calid` = :calid', ':tpid', $tpid, ':calid', $calId);
	/*
	if ($_SESSION['auth.join.refcode'])
		mydb::where('ds.`refcode` = :refcode', ':refcode', $_SESSION['auth.join.refcode']);
		*/
	if ($showJoinGroup && $showJoinGroup != '*')
		mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $showJoinGroup);


	// Show Register
	$stmt = 'SELECT
		  d.*
		, ds.*
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `fullname`
		, GROUP_CONCAT(do.`dopid`) `dopid`
		, COUNT(do.`dopid`) `hasrcv`
		FROM %org_dos% ds
			LEFT JOIN %org_doings% d USING(`doid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %org_dopaid% do ON do.`doid`=ds.`doid` AND do.`psnid`=ds.`psnid`
		%WHERE%
		GROUP BY `psnid`
		ORDER BY CONVERT(`fullname` USING tis620) ASC';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead = array('no' => '', 'name -nowrap' => 'ชื่อ-นามสกุล', 'group -center' => 'เครือข่าย', 'food -center' => 'อาหาร');
	//$cardUi = new Ui('div', 'ui-card -hover-parent');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			trim($rs->prename.' '.$rs->fullname),
			$rs->joingroup,
			$rs->foodtype,
		);
	}

	//$ret .= $cardUi->build();

	if ($dbs->count()) {
		$ret .= $tables->build();

		$ret .= '<p>จำนวนผู้ลงทะเบียนล่วงหน้า <b>'.$dbs->count().'</b> คน</p>';
	} else {
		$ret .= message('notify', 'ไม่มีผู้ลงทะเบียน : ยังไม่มีผู้ลงทะเบียนเข้าร่วมกิจกรรมจาก "'.$showJoinGroup.'"');
	}
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($calendarInfo, '$calendarInfo');

	$ret .= '<style type="text/css">
	tr.-joined {color:green;}
	tr.-joined a {color: green;}
	tr.-joined>td:first-child {border-left: 2px green solid;}
	tr.-joined>td {background-color: #f3ffeb;}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinMakeJoinCallback($this, ui) {
		console.log("Mark Join")
		var $parent = $this.closest("tr")
		$parent.toggleClass("-joined")
		$this.find("i").toggleClass("-circle -gray -green")
	}
	</script>';
	return $ret;
}
?>