<?php
/**
* Project Join Mate
* Created 2019-05-16
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_mate($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$getJoinGroup = post('group',_TRIM);
	$getHotel = post('hotel');
	$getChangwat = post('pv');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;


	//if (!isset($getJoinGroup)) {
		$joinGroup = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));
		//if (i()->username == 'softganz') $ret .= print_o($joinGroup,'$joinGroup');

		$hotelOptions = array('' => '== ทุกโรงแรม ==');
		mydb::where('`tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `refid` = :doid AND `text2` != ""', ':tpid', $tpid, ':doid', $projectInfo->doingInfo->doid);

		$stmt = 'SELECT
			`text2` `hotelname`
			FROM %project_tr%
			%WHERE%
			GROUP BY `hotelname`
			ORDER BY CONVERT(`hotelname` USING tis620) ASC';

		$dbs = mydb::select($stmt);

		foreach ($dbs->items as $rs) $hotelOptions[$rs->hotelname] = $rs->hotelname;
		//$ret .= mydb()->_query;
		//$ret .= print_o($dbs,'$dbs');

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

		$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/mate'), NULL, 'sg-form -no-print -inlineitem');
		$form->addConfig('method', 'GET');
		$form->addData('rel', 'replace:#report-output');
		$form->addField(
			'group',
			array(
				'type' => 'select',
				'options' => $joinGroup,
				'value' => $getJoinGroup,
				'attr' => array('onchange' => 'this.form.submit()'),
				//'attr' => array('onchange' => '$(this).parent(form).submit()'),
			)
		);

		$form->addField(
			'pv',
			array(
				'type' => 'select',
				'options' => $changwatList,
				'value' => $getChangwat,
				'attr' => array('onchange' => 'this.form.submit()'),
				//'attr' => array('onchange' => '$(this).parent(form).submit()'),
			)
		);

		$form->addField(
			'hotel',
			array(
				'type' => 'select',
				'options' => $hotelOptions,
				'value' => $getHotel,
				'attr' => array('onchange' => 'this.form.submit()'),
				//'attr' => array('onchange' => '$(this).parent(form).submit()'),
			)
		);

		$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));
		$self->theme->navbar = '<nav class="nav -page">'.$form->build().'</nav>';
	//}


	$ret .= '<div id="report-output">';
	// Show All of Register
	// Only show for auth
	mydb::where('do.`tpid` = :tpid AND do.`calid` = :calid AND ds.`isjoin` >= 0', ':tpid', $tpid, ':calid', $calId);

	if ($getJoinGroup && $getJoinGroup != '*')
		mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $getJoinGroup);

	if ($getChangwat) mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);

	if ($getHotel) {
		mydb::where('po.`text2` = :hotelname', ':hotelname', $getHotel);
	}

	if ($isEdit) {

	} else if (i()->ok) {
		//mydb::where('ds.`uid` = :uid', ':uid', i()->uid);
	} else {
		return $ret;
	}


	// Show Register
	$stmt = 'SELECT
		  ds.`psnid`
		, p.`psnid` `p_psnid`
		, po.`refcode` `j_psnid`
		, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `fullname`
		, po.`text2` `hotelname`
		, po.`trid` `orgtrid`
	--	, hm.`trid` `hoteltrid`
		, po.`text3` `matename`
		, po.`text4` `matepsnid`
		, ds.`joingroup`
		, ds.`rest`
		, ds.`withdrawrest`
		, hm.`text2` `matehotelname`
		, CONCAT(mp.`prename`,"",mp.`name`," ",mp.`lname`) `mateFullName`
		FROM %org_dos% ds
			LEFT JOIN %org_doings% do USING(`doid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %project_tr% po ON po.`tpid` = do.`tpid` AND po.`formid` = "join" AND po.`part` = "register" AND po.`refid` = ds.`doid` AND po.`refcode` = ds.`psnid`
			LEFT JOIN %project_tr% hm ON hm.`tpid` = do.`tpid` AND hm.`formid` = "join" AND hm.`part` = "register" AND hm.`text4` =  ds.`psnid`
			LEFT JOIN %db_person% mp ON mp.`psnid` = po.`text4`
		%WHERE%
	--	HAVING `matename` != ""
		ORDER BY CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC;
		-- {key: "psnid"}';
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$restRate = 600; // rate per night

	$tables = new Table();
	$totalPrice = 0;
	$tables->thead = array('no' => '', 'name -nowrap' => 'ชื่อ-นามสกุล', 'โรงแรม', 'center' => 'ประเภท', 'โรงแรม', 'travel -nowrap' => 'พักคู่', 'เครือข่าย');
	$skipId = array();
	foreach ($dbs->items as $rs) {
		if ($rs->matepsnid) $skipId[] = $rs->matepsnid;
		if (in_array($rs->psnid, $skipId)) continue;
		$class = '-parent-of-hover ';
		if ($rs->isjoin) $class .= '-joined';

		$tables->rows[] = array(
			++$no,
			'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box">'.$rs->fullname.'</a>'
			. ($isAdmin ? ' ('.$rs->psnid.')' : ''),
			$rs->hotelname,
			($rs->rest ? $rs->rest : '')
			. ($rs->withdrawrest<0 ? ' (ไม่เบิก)' : ''),
			$rs->matehotelname,
			($rs->matepsnid ? '<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->matepsnid).'" data-rel="box">'.SG\getFirst($rs->mateFullName,$rs->matename).'</a>' : $rs->matename)
			.($isAdmin && $rs->matepsnid ? ' ('.$rs->matepsnid.')' : ''),
			$rs->joingroup,
			'config' => array('class' => $class),
		);
	}

	if ($dbs->count()) {
		$ret .= $tables->build();
		$ret .= '<p>จำนวนผู้ลงทะเบียนล่วงหน้า <b>'.$dbs->count().'</b> คน</p>';
	} else {
		$ret .= message('notify', 'ไม่มีผู้ลงทะเบียน : ยังไม่มีผู้ลงทะเบียนเข้าร่วมกิจกรรมจาก "'.$getJoinGroup.'"');
	}
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '</div>';

	head('<style type="text/css">
		.navbar.-main .form .form-select, .navbar.-main .form .form-text {width: 160px;}
		</style>');
	return $ret;
}
?>