<?php
/**
* Project Action Join Distance
* Created 2019-05-16
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_distance($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$showJoinGroup = SG\getFirst(post('group'),'*');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;



	$joinGroup = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));

	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/distance'), NULL, 'sg-form form -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addData('rel', 'replace:#report-output');
	$form->addField('group',
		array(
			'type' => 'select',
			'options' => $joinGroup,
			'value' => $showJoinGroup,
			'attr' => array('onchange' => '$(this).parent(form).submit()'),
		)
	);
	//$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));
	$self->theme->navbar = $form->build();


	$ret .= '<div id="report-output">';

	// Show All of Register
	// Only show for auth
	mydb::where('d.`tpid` = :tpid AND d.`calid` = :calid', ':tpid', $tpid, ':calid', $calId);
	if ($showJoinGroup && $showJoinGroup != '*')
		mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $showJoinGroup);
	if ($isEdit) {

	} else if (i()->ok) {
		//mydb::where('ds.`uid` = :uid', ':uid', i()->uid);
	} else {
		return $ret;
	}


	// Show Register
	$stmt = 'SELECT
		d.*
		, ds.*
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `fullname`
		, dt.`fromareacode`, dt.`toareacode`, dt.`distance`, dt.`fixprice`
		, po.`trid` `orgtrid`
		, po.`detail1` `orgname`
		, po.`detail2` `orgtype`
		, po.`detail3` `position`
		, po.`detail4` `tripotherby`
		, po.`text1` `carregist`
		, po.`text2` `hotelname`
		, po.`text3` `hotelmate`
		, po.`num1` `busprice`
		, po.`num2` `airprice`
		, po.`num3` `tripotherprice`
		, po.`num4` `taxiprice`
		, po.`num5` `trainprice`
		, po.`num8` `rentprice`
		, po.`num6` `hotelprice`
		, ROUND(po.`num7`) `hotelnight`
		, cosub.`subdistname` `tambonName`
		, codist.`distname` `ampurName`
		, cop.`provname` `changwatName`
		FROM %org_dos% ds
			LEFT JOIN %org_doings% d USING(`doid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %project_tr% po ON po.`tpid` = d.`tpid` AND po.`formid` = "join" AND po.`part` = "register" AND po.`refid` = ds.`doid` AND po.`refcode` = ds.`psnid`
			LEFT JOIN %distance% dt ON dt.`fromareacode` = CONCAT(p.`changwat`, p.`ampur`) AND dt.`toareacode` = d.`areacode`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
		%WHERE%
		ORDER BY CONVERT(`fullname` USING tis620) ASC
		-- LIMIT 10';


	$stmt = 'SELECT
		a.*
		, po.`trid` `orgtrid`
		, po.`detail1` `orgname`
		, po.`detail2` `orgtype`
		, po.`detail3` `position`
		, po.`detail4` `tripotherby`
		, po.`text1` `carregist`
		, po.`text2` `hotelname`
		, po.`text3` `hotelmate`
		, po.`num1` `busprice`
		, po.`num2` `airprice`
		, po.`num3` `tripotherprice`
		, po.`num4` `taxiprice`
		, po.`num5` `trainprice`
		, po.`num8` `rentprice`
		, po.`num6` `hotelprice`
		, ROUND(po.`num7`) `hotelnight`

		FROM
			(SELECT
				d.`tpid`, d.`calid`
				, ds.*
				, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `fullname`
				, dt.`fromareacode`, dt.`toareacode`, dt.`distance`, dt.`fixprice`
				, cosub.`subdistname` `tambonName`
				, codist.`distname` `ampurName`
				, cop.`provname` `changwatName`
				FROM %org_dos% ds
					LEFT JOIN %org_doings% d USING(`doid`)
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %distance% dt ON dt.`fromareacode` = CONCAT(p.`changwat`, p.`ampur`) AND dt.`toareacode` = d.`areacode`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
				%WHERE%
			--	ORDER BY CONVERT(`fullname` USING tis620) ASC
				) a
					LEFT JOIN %project_tr% po ON po.`tpid` = a.`tpid` AND po.`formid` = "join" AND po.`part` = "register" AND po.`refid` = a.`doid` AND po.`refcode` = a.`psnid`

				-- LIMIT 10';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($dbs,'$dbs');

	$restRate = 600; // rate per night

	$sumtable = new Table();
	$sumtable->addClass('-networksum');
	$sumtable->thead = array('เครือข่าย','trip -money'=>'ประมาณค่าเดินทาง','rest -money'=>'ประมาณค่าที่พัก');

	//debugMsg($joinGroup,'$joinGroup');
	foreach ($joinGroup as $key => $value) {
		if ($key == '*') continue;
		if ($key && is_object($value)) {
			foreach ($value as $k2 => $v2) {
				$sumtable->rows[$k2] = array('name'=>$v2,'tripPrice'=>0, 'restPrice'=>0);
			}
		} else if ($key) {
			$sumtable->rows[$key] = array('name'=>$value,'tripPrice'=>0, 'restPrice'=>0);
		}
	}



	// Show each person
	$tables = new Table();
	$totalPrice = 0;
	$tables->thead = array('no' => '', 'name -nowrap' => 'ชื่อ-นามสกุล', 'travel' => 'เดินทาง', 'จาก', 'distamt -amt' => 'ระยะทางไป-กลับ (กม.)', 'travel -money' => 'ค่าเดินทาง (บาท)', 'rest -money' => 'ค่าที่พัก');
	foreach ($dbs->items as $rs) {
		// Generate item menu
		/*
		$menuUi = new Ui('span');
		if ($isEdit || user_access('edit own project content',$rs->uid)) {
			$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>');
			$menuUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/edit/'.$rs->psnid).'" title="แก้ไขรายละเอียด"><i class="icon -edit"></i></a>');
			$dropUi = new Ui();
			if (!$rs->isjoin) $menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/delete/'.$rs->psnid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="notify" data-removeparent="tr" title="ยกเลิกการลงทะเบียน"><i class="icon -delete"></i></a>');
			if ($isEdit) {
				if ($rs->isjoin)
					$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/proved/'.$rs->psnid).'" title="Mark as not join - บันทึกว่าไม่เข้าร่วม" data-rel="none" data-callback="projectJoinMakJoinCallback"><i class="icon -save -green -circle"></i></a>');
				else
					$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/proved/'.$rs->psnid).'" title="Mark as join - บันทึกว่าเข้าร่วม" data-rel="none" data-callback="projectJoinMakJoinCallback"><i class="icon -save -gray"></i></a>');
				$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/addrcv').'" title="สร้างใบสำคัญรับเงิน"><i class="icon -adddoc"></i><span>สร้างใบสำคัญรับเงิน</span></a>');
				$dropUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/viewrcv').'" title="รายละเอียดใบสำคัญรับเงิน"><i class="icon -viewdoc"></i><span>ใบสำคัญรับเงิน</span></a>');
			}
			if ($dropUi->count())
				$menuUi->add(sg_dropbox($dropUi->build()));
		}
		$menu = '<nav class="iconset -parent-hover">'.$menuUi->build().'</nav>'._NL;
		*/

		$class = '-parent-of-hover ';
		if ($rs->isjoin) $class .= '-joined';

		$showFull = $isEdit || (i()->ok && $rs->uid == i()->uid);
		$tripPrice = 0;

		$tripbyList = explode(',', $rs->tripby);
		foreach ($tripbyList as $tripBy) {
			switch ($tripBy) {
				case 'รถยนต์ส่วนตัว':
					$tripPrice = $rs->fixprice ? $rs->fixprice : $rs->distance * 2 *4;
					break;
				case 'เดินทางร่วม':
					break;
				case 'รถโดยสารประจำทาง':
					$tripPrice += $rs->busprice;
					break;
				case 'รถรับจ้าง':
					$tripPrice += $rs->taxiprice;
					break;
				case 'เครื่องบิน':
					$tripPrice += $rs->airprice;
					break;
				case 'รถไฟ':
					$tripPrice += $rs->trainprice;
					break;
				case 'รถตู้เช่า':
					$tripPrice += $rs->rentprice;
					break;
				case 'อื่นๆ':
					$tripPrice += $rs->tripotherprice;
					break;
				case 'ไม่เบิกค่าเดินทาง':
					break;
			}
		}

		$totalPrice += $tripPrice;

		$restPrice = 0;
		if ($rs->rest == 'พักเดี่ยว')
			$restPrice = $rs->hotelprice * $rs->hotelnight;
		if ($rs->rest == 'พักคู่')
			$restPrice = ($rs->hotelprice * $rs->hotelnight) / 2;
		$totalRestPrice += $restPrice;

		if ($rs->joingroup) {
			$sumtable->rows[$rs->joingroup]['tripPrice'] += $tripPrice;
			$sumtable->rows[$rs->joingroup]['restPrice'] += $restPrice;
		}

		$tables->rows[] = array(
			++$no,
			'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box">'.trim($rs->prename.' '.$rs->fullname).'</a>',
			str_replace(',', ', ', $rs->tripby),
			SG\implode_address($rs, 'short'),
			$rs->distance * 2,
			$tripPrice ? number_format($tripPrice,2)
			. ($rs->tripby == 'รถยนต์ส่วนตัว' && $rs->fixprice ? '<sup>*</sup>' : '') : '-',
			$restPrice ? number_format($restPrice,2) : '-',
			'config' => array('class' => $class),
		);
	}

	//$ret .= print_o($sumtable,'$sumtable');

	$tables->tfoot[] = array('<td></td>', 'รวม', '', '', '', number_format($totalPrice,2), number_format($totalRestPrice,2));

	$sumNetTripPrice = $sumNetRestPrice = 0;
	foreach ($sumtable->rows as $key => $value) {
		$sumtable->rows[$key]['tripPrice'] = number_format($value['tripPrice'],2);
		$sumtable->rows[$key]['restPrice'] = number_format($value['restPrice'],2);
		$sumNetTripPrice += $value['tripPrice'];
		$sumNetRestPrice += $value['restPrice'];
	}
	$sumtable->tfoot[] = array('รวม',number_format($sumNetTripPrice,2),number_format($sumNetRestPrice,2));

	if ($showJoinGroup == '*') {
		$ret .= $sumtable->build();
		//$ret .= print_o($sumtable,'$sumtable');
	}

	if ($dbs->count()) {
		$ret .= $tables->build();
		$ret .= '<p>จำนวนผู้ลงทะเบียนล่วงหน้า <b>'.$dbs->count().'</b> คน</p>';
		$ret .= '<p>หมายเหตุ :<ul><li>* คือ ค่าเดินทางเหมาจ่าย</li></p>';
	} else {
		$ret .= message('notify', 'ไม่มีผู้ลงทะเบียน : ยังไม่มีผู้ลงทะเบียนเข้าร่วมกิจกรรมจาก "'.$showJoinGroup.'"');
	}
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	tr.-joined {color:green;}
	tr.-joined a {color: green;}
	tr.-joined>td:first-child {border-left: 2px green solid;}
	tr.-joined>td {background-color: #f3ffeb;}
	.item.-networksum>tbody>tr>td:nth-child(n+2) {text-align:center;}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinMakJoinCallback($this, ui) {
		console.log("Mark Join")
		var $parent = $this.closest("tr")
		$parent.toggleClass("-joined")
		$this.find("i").toggleClass("-circle -gray -green")
	}
	</script>';
	$ret .= '</div>';

	return $ret;
}
?>