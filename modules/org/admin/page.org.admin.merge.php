<?php
/**
 * Merge duplicate name and lname
 *
 * @param String $_GET['name']
 * @param String $_GET['lname']
 * @return String
 */
function org_admin_merge($self) {
	$self->theme->title='รวมชื่อ-นามสกุลซ้ำ';
	//$self->theme->pretext=$self->__toolbar('member','member');

	if (post('cancel')) location('org/member');

	if ($useid = post('useid')) {
		//if (empty($_POST['useid']) || empty($_POST['id'])) return 'กรุณาเลือกชุดข้อมูลที่ต้องการใช้งาน';
		$unUsed = post('id');
		unset($unUsed[$useid]);
		$used_person = false;


		// Start process to move person information
		if (!empty($unUsed)) {
			$ret .= message('', 'Process Person Merge');

			$ret .= '<p>USE ID = '.$useid.'<br />UNUSED ID = '.implode(',',$unUsed).'</p>';


			$unUsedId = 'SET:'.implode(',', $unUsed);

			// Module org : Table org_dos, org_morg, org_dopaid, org_mjoin 
			if (mydb::table_exists('org_dos')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_dos% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate member from org_dos
				$stmt = 'DELETE FROM %org_dos% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Delete unuse member name from org_mjoin
			if (mydb::table_exists('org_mjoin')) {
				$stmt = 'DELETE FROM %org_mjoin% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Delete unuse member name from org_morg
			if (mydb::table_exists('org_morg')) {
				$stmt = 'DELETE FROM %org_morg% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}
			
			// Move member in doing paid :: org_dopaid
			if (mydb::table_exists('org_dopaid')) {
				$stmt = 'UPDATE IGNORE %org_dopaid% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}



			// Base table : person_group, person_tr
			if (mydb::table_exists('person_group')) {
				// Move member in person group :: person_group
				$stmt = 'UPDATE IGNORE %person_group% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate person group from person_group
				$stmt = 'DELETE FROM %person_group% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in person tran :: person_tr
			if (mydb::table_exists('person_tr')) {
				$stmt = 'UPDATE IGNORE %person_tr% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in person tran :: person_tr
			if (mydb::table_exists('qtmast')) {
				$stmt = 'UPDATE IGNORE %qtmast% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}




			// TODO: Change psnid of module imed_barthel, imed_care, imed_disabled, imed_disabled_defect, imed_group, imed_patient, imed_patient_gis, imed_qt, imed_service, imed_tr

			if (mydb::table_exists('imed_barthel')) {
				// Move member in person tran :: person_tr
				$stmt = 'UPDATE IGNORE %imed_barthel% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_care
			if (mydb::table_exists('imed_care')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_care% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_care
				$stmt = 'DELETE FROM %imed_care% WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_disabled
			if (mydb::table_exists('imed_disabled')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_disabled% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_disabled
				$stmt = 'DELETE FROM %imed_disabled% WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_disabled_defect
			if (mydb::table_exists('imed_disabled_defect')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_disabled_defect% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_disabled_defect
				$stmt = 'DELETE FROM %imed_disabled_defect% WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_group
			if (mydb::table_exists('imed_group')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_group% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_group
				$stmt = 'DELETE FROM %imed_group% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_patient
			if (mydb::table_exists('imed_patient')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_patient% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_patient
				$stmt = 'DELETE FROM %imed_patient% WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Change psnid of table imed_patient_gis
			if (mydb::table_exists('imed_patient_gis')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %imed_patient_gis% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_patient
				$stmt = 'DELETE FROM %imed_patient_gis% WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in imed_qt
			if (mydb::table_exists('imed_qt')) {
				$stmt = 'UPDATE IGNORE %imed_qt% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in imed service :: imed_service
			if (mydb::table_exists('imed_service')) {
				$stmt = 'UPDATE IGNORE %imed_service% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in person tran :: person_tr
			if (mydb::table_exists('imed_tr')) {
				$stmt = 'UPDATE IGNORE %imed_tr% SET `pid` = :useid WHERE `pid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// Move member in imed social patient :: imed_socialpatient
			if (mydb::table_exists('imed_socialpatient')) {
				$stmt = 'UPDATE IGNORE %imed_socialpatient% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in imed_socialpatient
				$stmt = 'DELETE FROM %imed_socialpatient% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}

			// TODO: Change psnid of module ibuy_fran



			// Change psnid of table poormember
			if (mydb::table_exists('poormember')) {
				// Move member in person_group
				$stmt = 'UPDATE IGNORE %poormember% SET `psnid` = :useid WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate in poormember
				$stmt = 'DELETE FROM %poormember% WHERE `psnid` IN (:unused)';
				mydb::query($stmt, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}



			// Change psnid of table bigdata using by mapping keyname = map and fldname = psnid value in fldref
			if (mydb::table_exists('bigdata') && mydb::columns('bigdata','fldref')) {
				$stmt = 'UPDATE IGNORE %bigdata% SET `fldref` = :useid WHERE `keyname` = "map" AND `fldname` = "psnid" AND `fldref` IN (:unused)';
				mydb::query($stmt, ':useid', $useid, ':unused', $unUsedId);
				$ret .= mydb()->_query.'<br />';
			}



			// Delete unuse member name from person
			$stmt = 'DELETE FROM %db_person% WHERE `psnid` IN (:unused)';
			mydb::query($stmt, ':unused', $unUsedId);
			$ret .= mydb()->_query.'<br />';
		}

		$ret .= '<nav class="nav -page"><a class="btn -primary" href="'.url('org/admin/merge',array('dup'=>'name')).'"><i class="icon -back -white"></i><span>Back to member name list</a> <a class="btn -primary" href="'.url('org/admin/merge',array('dup'=>'cid')).'"><i class="icon -back -white"></i><span>Back to member CID list</a></nav>';
		// location('orgdb/member/'.$useid);
	} else if (post('id')) {
		$id = post('id');

		if ($id && is_array($id)) {
			mydb::where('p.`psnid` IN ('.implode(',',$id).')');
		} else if (post('name') && post('lname')) {
			mydb::where('p.`name` = :name AND p.`lname` = :lname', ':name', post('name'), ':lname', post('lname'));
		}

		if (mydb::table_exists('imed_service')) {
			mydb::value('$IMEDSERVICE$',', (SELECT COUNT(*) FROM %imed_service% WHERE `pid` = p.`psnid`) `imedService` ');
		} else {
			mydb::value('$IMEDSERVICE$','');
		}

		if (mydb::table_exists('imed_qt')) {
			mydb::value('$IMEDQT$',', (SELECT COUNT(*) FROM %imed_qt% WHERE `pid` = p.`psnid`) `imedQt` ');
		} else {
			mydb::value('$IMEDQT$','');
		}

		if (mydb::table_exists('qtmast')) {
			mydb::value('$QTMAST$',', (SELECT COUNT(*) FROM %qtmast% WHERE `psnid` = p.`psnid`) `qtMast` ');
		} else {
			mydb::value('$QTMAST$','');
		}

		$stmt = 'SELECT
			u.`name` `ownerName`, p.`psnid` , p.`prename` , p.`name` , p.`lname`, `cid`
			, p.`birth`
			, p.`house`, p.`village` , p.`tambon` , p.`ampur` , p.`changwat`
			, p.`zip` , p.`phone` , p.`email` , p.`website`
			, p.`aptitude` , p.`location` , p.`interest`
			, p.`remark`
			$IMEDSERVICE$
			$IMEDQT$
			$QTMAST$
			FROM %db_person% p
				LEFT JOIN %users% u USING(`uid`)
			%WHERE%
			ORDER BY `psnid` ASC
			';

		$dbs = mydb::select($stmt);

		$tables = new Table();

		$tables->rows['select'][]='เลือก';
		$tables->rows['psnid'][]='หมายเลข';
		$tables->rows['ownerName'][]='Owner';
		$tables->rows['prename'][]='คำนำหน้านาม';
		$tables->rows['name'][]='ชื่อ';
		$tables->rows['lname'][]='นามสกุล';
		$tables->rows['cid'][]='หมายเลข 13 หลัก';
		$tables->rows['birth'][]='วันเกิด';
		$tables->rows['house'][]='ที่อยู่';
		$tables->rows['village'][]='หมู่ที่';
		$tables->rows['tambon'][]='ตำบล';
		$tables->rows['ampur'][]='อำเภอ';
		$tables->rows['changwat'][]='จังหวัด';
		$tables->rows['zip'][]='รหัสไปรษณีย์';
		$tables->rows['phone'][]='โทรศัพท์';
		$tables->rows['email'][]='อีเมล์';
		$tables->rows['website'][]='เว็บไซท์';
		$tables->rows['aptitude'][]='ความถนัด';
		$tables->rows['location'][]='พิกัด';
		$tables->rows['interest'][]='ความสนใจ';
		$tables->rows['remark'][]='หมายเหตุ';
		$tables->rows['imedQt'][]='iMed@QT';
		$tables->rows['imedService'][]='imedService';
		$tables->rows['qtMast'][]='qtMast';

		foreach ($dbs->items as $rs) {
			$tables->rows['select'][]='<input type="hidden" name="id['.$rs->psnid.']" value="'.$rs->psnid.'" /><input type="radio" name="useid" value="'.$rs->psnid.'" '.($rs->psnid == post('useid') ? 'checked="checked"' : '').' /> เลือกใช้ข้อมูลนี้ หรือ <a class="sg-action btn" href="'.url('org/member/'.$rs->psnid).'" data-rel="box" data-width="640"><i class="icon -edit"></i><span>แก้ไข</span></a>';
			foreach ($rs as $key=>$value) {
				$tables->rows[$key][]=$value;
			}
		}

		$ret.='<form method="POST" action="'.url('org/admin/merge').'">กรุณาเลือกชุดข้อมูลที่ต้องการใช้งาน และ คลิก <button class="btn -primary" type="submit" name="save"><i class="icon -material -white">merge_type</i><span>เริ่มกระบวนการรวมชุดข้อมูล</span></button>';
		$ret .= $tables->build();
		$ret .= '</form>';

		$ret .= '<nav class="nav -page"><a class="btn -primary" href="'.url('org/admin/merge',array('dup'=>'name')).'"><i class="icon -back -white"></i><span>Back to member name list</a> <a class="btn -primary" href="'.url('org/admin/merge',array('dup'=>'cid')).'"><i class="icon -back -white"></i><span>Back to member CID list</a></nav>';
	} else {
		// Show first charactor of name
		$stmt = 'SELECT DISTINCT LEFT(`name`,1) `firstchar` FROM `sgz_db_person`';
		$ret .= '<nav class="nav -page">';
		$ret .= '<a class="btn" href="'.url('org/admin/merge',array('dup'=>'name')).'">Duplicate Name</a> ';
		$ret .= '<a class="btn" href="'.url('org/admin/merge',array('dup'=>'cid')).'">Duplicate CID</a> ';

		foreach(mydb::select($stmt)->items as $rs) {
			$ret .= '<a class="btn" href="'.url('org/admin/merge',array('fc'=>$rs->firstchar)).'">&nbsp;'.$rs->firstchar.'&nbsp;</a> ';
		}
		$ret .= '</nav>';

		mydb::value('$GROUPBY', 'GROUP BY `fullname`');
		mydb::value('$HAVING', 'HAVING `dupamt` > 1');

		if (post('dup') == 'cid') {
			mydb::where('(`cid` IS NOT NULL AND `cid` != "")');
			mydb::value('$GROUPBY', 'GROUP BY `cid`');
		}
		if (post('fc')) {
			mydb::where('LEFT(`name`,1) = :firstchar', ':firstchar', post('fc'));
			mydb::value('$HAVING','');
		}
		$stmt = 'SELECT
						GROUP_CONCAT(`psnid` SEPARATOR ", ") `psnid`, `name`, `lname`, `cid`, `phone`
						, COUNT(*) `dupamt`
						, CONCAT(`name`,IFNULL(`lname`,"")) `fullname`
						FROM %db_person% p
						%WHERE%
						$GROUPBY
						$HAVING
						ORDER BY
							CONVERT(`name` USING tis620) ASC,
							CONVERT(`lname` USING tis620) ASC
						LIMIT 10000';
		$dbs = mydb::select($stmt);

		//$ret .= mydb()->_query;

		$ret .= '<form method="post" action="'.url('org/admin/merge').'">';
		$ret .= '<button class="btn -primary" type="submit" name="save"><i class="icon -addbig -white"></i><span>รวมชุดข้อมูล</span></button> รวม <b>'.number_format($dbs->count()).'</b> รายการ';
		$tables = new Table();
		$tables->thead = array('select -center' => '','merge -center'=>'','psnid -center' => 'psnid', 'ชื่อ', 'นามสกุล', 'เลข 13 หลัก', 'dup -amt' => 'ซ้ำ', 'phone -hover-parent' => 'โทรศัพท์');
		foreach ($dbs->items as $rs) {
			$menuUi = new Ui('span');
			if ($rs->dupamt == 1)
				$menuUi->add('<a href="'.url('org/member/'.$rs->psnid).'" target="_blank"><i class="icon -viewdoc"></i></a>');
			$menu = '<nav class="iconset -hover">'.$menuUi->build().'</nav>'._NL;

			if ($rs->isjoin) $class .= '-joined';

			$psnIdList = str_replace(' ', '', $rs->psnid);

			$tables->rows[] = array(
													'<input type="checkbox" name="id['.$psnIdList.']" value="'.$rs->psnid.'" />',
													'<a href="'.url('org/admin/merge',array('id[]'=>$psnIdList)).'" target="_blank"><i class="icon -material">merge_type</i></a>',
													$rs->psnid,
													$rs->name,
													$rs->lname,
													$rs->cid,
													$rs->dupamt,
													$rs->phone
													.$menu,
												'config' => array('class' => $class),
												);
		}
		$ret .= $tables->build();
		$ret .= '<button class="btn -primary" type="submit" name="save"><i class="icon -addbig -white"></i><span>รวมชุดข้อมูล</span></button> รวม <b>'.number_format($dbs->count()).'</b> รายการ';
		$ret .= '</form>';

		//$ret .= print_o($dbs, '$dbs');
	}

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}
?>