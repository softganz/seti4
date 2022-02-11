<?php
/**
* Project Action Join Money
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_topaid($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$getStatus = post('status');
	$getJoinGroup = SG\getFirst(post('group'));
	$getChangwat = post('pv');
	$getSearchText = SG\getFirst(post('search'));
	$getOrderBy = SG\getFirst(post('o'),'created');
	$getSortDir = SG\getFirst(post('s'),'d');

	$right = R::Model('project.join.right', $projectInfo);

	$ret = '';

	R::View('project.toolbar', $self, 'การเงิน - '.$projectInfo->calendarInfo->title, 'join', $projectInfo);

	$orderList = array(
		'name' => 'CONVERT(`fullname` USING tis620)',
		'network' => 'CONVERT(`joingroup` USING tis620)',
		'created' => '`created`',
	);

	$joinGroupList = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));
	$joinGroupList->my = 'ลงทะเบียนโดยฉัน';
	$joinGroupList->all = 'ใบสำคัญรับเงินทั้งหมด';

	// Get province
	mydb::where('ds.`doid` = :doid AND ds.`isjoin` >= 0', ':doid', $projectInfo->doingInfo->doid);
	if ($getJoinGroup && $getJoinGroup != '*')
			mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $getJoinGroup);

	$stmt = 'SELECT
		p.`changwat`
		, cop.`provname`
		, COUNT(*) `amt`
		FROM %org_dos% ds
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
		%WHERE%
		GROUP BY `changwat`
		';
	$dbs = mydb::select($stmt);

	$changwatList = array('' => '== ทุกจังหวัด ==');
	foreach ($dbs->items as $rs) {
		if ($rs->changwat) {
			$changwatList[$rs->changwat] = $rs->provname.'  ('.$rs->amt.' คน)';
		} else {
			$changwatList['na'] = 'ไม่ระบุ  ('.$rs->amt.' คน)';
		}
	}


	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/topaid'), NULL, 'sg-form -no-print -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addField('status', array('type' => 'hidden', 'value' => $getStatus));
	$form->addField('o', array('type' => 'hidden', 'value' => $getOrderBy));
	$form->addField('s', array('type' => 'hidden', 'value' => $getSortDir));
	$form->addField(
		'group',
		array(
			'type' => 'select',
			'options' => $joinGroupList,
			'value' => $getJoinGroup,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);
	$form->addField(
		'pv',
		array(
			'type' => 'select',
			'options' => $changwatList,
			'value' => $getChangwat,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);

	$form->addField('psnid', array('type' => 'hidden'));
	$form->addField(
		'search',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete',
			'placeholder' => 'ค้นชื่อ , CID , โทร',
			'attr' => array(
				'data-query'=>url('project/api/join/person/'.$tpid.'/'.$calId),
				'data-altfld'=>'edit-psnid',
				'data-callback'=>'submit',
			),
		)
	);
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -material -white">search</i>'));

	$self->theme->navbar = $form->build();

	$ret .= '<div id="report-output">';

	$ret .= '<h3>รายการจ่ายเงิน "'.($getJoinGroup == '*' ? 'ทุกเครือข่าย' : $getJoinGroup).'"</h3>';

	mydb::where('d.`tpid` = :tpid AND d.`calid` = :calid AND dp.`psnid` IS NOT NULL', ':tpid', $tpid, ':calid', $calId);
	/*
	if ($_SESSION['auth.join.refcode'])
		mydb::where('ds.`refcode` = :refcode', ':refcode', $_SESSION['auth.join.refcode']);
		*/

	if (post('psnid')) {
		mydb::where('ds.`psnid` = :psnid', ':psnid', post('psnid'));
	} else if ($getSearchText != '') {
		list($name, $lname) = sg::explode_name(' ', $getSearchText);
		mydb::where('(p.`cid` LIKE :name OR p.`phone` LIKE :name OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').'))',':name','%'.$name.'%', ':lname','%'.$lname.'%');
	} else if ($getJoinGroup == 'my' && i()->ok) {
		mydb::where('ds.`uid` = :uid', ':uid', i()->uid);
	} else {
		if ($getChangwat) mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
		if ($getJoinGroup && $getJoinGroup != '*')
			mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $getJoinGroup);
	}
	if ($getStatus == 'rcv') {
		mydb::where('do.`dopid` IS NOT NULL');
	} else if ($getStatus == 'norcv') {
		mydb::where('do.`dopid` IS NULL');
	} else if ($getStatus == 'cancel') {
		mydb::where('ds.`isjoin` < 0');
	}

	mydb::value('$order', $orderList[$getOrderBy]);
	mydb::value('$sort', $getSortDir == 'd' ? 'DESC' : 'ASC');

	// Show Register
	$stmt = 'SELECT
		  dp.*
		, dtr.*
		, tg.`name` `catName`
		--	, ds.*
		--	, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `fullname`
		--	, p.`cid`
		--	, GROUP_CONCAT(do.`dopid`) `dopid`
		--	, COUNT(do.`dopid`) `hasrcv`
		--	, do.`islock`
		--	, GROUP_CONCAT(do.`dopid`) `dopids`
		--	, do.`formid`
		FROM %org_dopaid% dp
			LEFT JOIN %org_doings% d USING(`doid`)
			LEFT JOIN %org_dos% ds ON ds.`doid` = dp.`doid` AND ds.`psnid` = dp.`psnid`
			LEFT JOIN %db_person% p ON p.`psnid` = dp.`psnid`
			LEFT JOIN %org_dopaidtr% dtr USING (`dopid`)
			LEFT JOIN %tag% tg ON tg.`taggroup` = "project:expcode" AND tg.`catid` = dtr.`catid`
		--	LEFT JOIN %org_dopaid% do ON do.`doid` = ds.`doid` AND do.`psnid` = ds.`psnid`
		%WHERE%
		--	GROUP BY `psnid`
		--	ORDER BY $order $sort
		';

	$dbs = mydb::select($stmt);


	$myUid = i()->uid;
	$totals = 0;
	$currentUrl = q();

	$cardUi = new Ui('div', 'ui-card -hover-parent');

	$tables = new Table();
	$tables->addClass('project-join-list');
	$tables->thead = array(
		'no' => '',
		'name' => 'ชื่อ-นามสกุล<a href="'.url($currentUrl, array('status'=>$getStatus, 'group'=> $getJoinGroup,'search' => $getSearchText, 'o' => 'name', 's' => $getSortDir == 'a' ? 'd' : 'a')).'"><i class="icon -sort'.($getOrderBy == 'name' ? '' : ' -gray').'"></i></a>',
		'money -money' => 'จำนวนเงิน',
	);


	$currentId = NULL; //reset($dbs->items)->dopid;

	foreach ($dbs->items as $rs) {
		// Generate item menu
		$menuUi = new Ui('span');
		$dropUi = new Ui();

		// All member can view register information



		if ($right->adminWeb) {
			$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/admininfo/'.$rs->psnid).'" data-rel="box" data-width="640"><i class="icon -material -gray">info</i><span>Information</span></a>');
		}

		if ($dropUi->count()) $menuUi->add(sg_dropbox($dropUi->build()));

		$menu = '<nav class="nav -header -icons -hover -no-print">'.$menuUi->build().'</nav>'._NL;
		$showFull = $right->accessJoin || $rs->uid == $myUid;




		$class = '';
		if ($rs->isjoin == 3) $class .= '-notrcv';
		else if ($rs->isjoin < 0) $class .= '-cancel';
		else if ($rs->isjoin) $class .= '-joined';
		if ($rs->dopid) $class .= ' -paided ';
		if ($rs->islock) $class .= ' -locked';


		//if ($rs->isjoin >= 0) $totals++;



			if ($currentId != $rs->dopid) {
				$tables->rows[] = array(++$no,'<td colspan="2"><a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box" data-width="640"><b>'.$rs->paidname.'</b></td>');
				$currentId = $rs->dopid;
			}


		$tables->rows[] = array(
				'<td></td>',
				$rs->catName.($rs->detail ? ' ('.$rs->detail.')': ''),
				number_format($rs->amt,2),
			);

	}

	//$ret .= $cardUi->build();

	if ($no) {
		$ret .= $tables->build();

		$ret .= '<p>จำนวนใบสำคัญรับเงิน <b>'.$no.'</b> คน</p>';
	} else {
		$ret .= message('notify', 'ไม่มีใบสำคัญรับเงิน : ยังไม่มีใบสำคัญรับเงินจาก "'.$getJoinGroup.'"');
	}

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '</div>';


	head('<style type="text/css">
		.navbar.-main .form .form-select, .navbar.-main .form .form-text {width: 160px;}
		.item {width: 100%;}
		</style>
		'
	);

	return $ret;
}
?>