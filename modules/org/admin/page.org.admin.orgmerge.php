<?php
/**
 * Merge Duplicate Organization
 *
 * @param String $_GET['name']
 * @param String $_GET['lname']
 * @return String
 */
function org_admin_orgmerge($self) {
	$self->theme->title='รวมองค์กรซ้ำ';
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

			$vars = new stdClass();
			$vars->useid = $useid;
			$vars->unused = $unUsedId;
			// Module org : Table org_dos, org_morg, org_dopaid, org_mjoin 
			if (mydb::table_exists('org_doc')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_doc% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('org_doings')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_doings% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('org_mjoin')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_mjoin% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('org_morg')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_morg% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('org_officer')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_officer% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate
				$stmt = 'DELETE FROM %org_officer% WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

			}

			if (mydb::table_exists('org_ojoin')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_ojoin% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate
				$stmt = 'DELETE FROM %org_ojoin% WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_ojoin% SET `jorgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

				// Delete duplicate
				$stmt = 'DELETE FROM %org_ojoin% WHERE `jorgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';

			}

			if (mydb::table_exists('org_subject')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %org_subject% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('project_tr')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %project_tr% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('qtmast')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %qtmast% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('topic')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %topic% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			if (mydb::table_exists('topic_files')) {
				// Move member in doings :: org_dos
				$stmt = 'UPDATE IGNORE %topic_files% SET `orgid` = :useid WHERE `orgid` IN (:unused)';
				mydb::query($stmt, $vars);
				$ret .= mydb()->_query.'<br />';
			}

			/*

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
			*/



			// Delete unuse member name from person
			$stmt = 'DELETE FROM %db_org% WHERE `orgid` IN (:unused)';
			mydb::query($stmt, $vars);
			$ret .= mydb()->_query.'<br />';
		}

		$ret .= '<nav class="nav -page"><a class="btn -primary" href="'.url('org/admin/orgmerge',array('dup'=>'name')).'"><i class="icon -back -white"></i><span>Back to org name list</a></nav>';
		// location('orgdb/member/'.$useid);




	} else if (post('id')) {
		$id = post('id');

		if ($id && is_array($id)) {
			mydb::where('o.`orgid` IN ('.implode(',',$id).')');
		} else if (post('name')) {
			mydb::where('o.`name` = :name', ':name', post('name'));
		}

		mydb::value('$OFFICER$','');
		mydb::value('$DOINGS$','');
		mydb::value('$PROJECTTR$','');
		mydb::value('$TOPIC$','');

		if (mydb::table_exists('org_officer')) {
			mydb::value('$OFFICER$',', (SELECT COUNT(*) FROM %org_officer% WHERE `orgid` = o.`orgid`) `officerCount` ');
		}

		if (mydb::table_exists('org_doings')) {
			mydb::value('$DOINGS$',', (SELECT COUNT(*) FROM %org_doings% WHERE `orgid` = o.`orgid`) `doingsCount` ');
		}

		if (mydb::table_exists('project_tr')) {
			mydb::value('$PROJECTTR$',', (SELECT COUNT(*) FROM %project_tr% WHERE `orgid` = o.`orgid`) `projectTrCount` ');
		}

		if (mydb::table_exists('qtmast')) {
			mydb::value('$TOPIC$',', (SELECT COUNT(*) FROM %topic% WHERE `orgid` = o.`orgid`) `topicCount` ');
		}

		$stmt = 'SELECT
			  u.`name` `ownerName`
			, o.`orgid` , o.`name`, o.`address`, o.`shortname`, o.`enshortname`
			, o.`house`, o.`areacode`
			$OFFICER$
			$DOINGS$
			$PROJECTTR$
			$TOPIC$
			FROM %db_org% o
				LEFT JOIN %users% u USING(`uid`)
			%WHERE%
			ORDER BY `orgid` ASC
			';

		$dbs = mydb::select($stmt);
		//$ret .= print_o($dbs,'$dbs');

		$tables = new Table();

		$tables->rows['select'][]='เลือก';
		$tables->rows['orgid'][]='หมายเลข';
		$tables->rows['ownerName'][]='Owner';
		$tables->rows['name'][]='ชื่อ';
		$tables->rows['shortname'][]='ชื่อย่อ(TH)';
		$tables->rows['enshortname'][]='ชื่อย่อ(EN)';
		$tables->rows['house'][]='House';
		$tables->rows['areacide'][]='Area Code';
		$tables->rows['address'][]='ที่อยู่';
		$tables->rows['officerCount'][]='officerCount';
		$tables->rows['doingsCount'][]='Doings';
		$tables->rows['projectTrCount'][]='projectTrCount';
		$tables->rows['topicCount'][]='topicCount';

		foreach ($dbs->items as $rs) {
			$tables->rows['select'][]='<input type="hidden" name="id['.$rs->orgid.']" value="'.$rs->orgid.'" /><input type="radio" name="useid" value="'.$rs->orgid.'" '.($rs->orgid == post('useid') ? 'checked="checked"' : '').' /> เลือกใช้ข้อมูลนี้ หรือ <a class="sg-action btn" href="'.url('org/'.$rs->orgid).'" data-rel="box" data-width="640"><i class="icon -edit"></i><span>แก้ไข</span></a>';
			foreach ($rs as $key=>$value) {
				$tables->rows[$key][]=$value;
			}
		}

		$ret.='<form method="POST" action="'.url('org/admin/orgmerge').'">กรุณาเลือกชุดข้อมูลที่ต้องการใช้งาน และ คลิก <button class="btn -primary" type="submit" name="save"><i class="icon -material -white">merge_type</i><span>เริ่มกระบวนการรวมชุดข้อมูล</span></button>';
		$ret .= $tables->build();
		$ret .= '</form>';

		$ret .= '<nav class="nav -page"><a class="btn -primary" href="'.url('org/admin/orgmerge',array('dup'=>'name')).'"><i class="icon -back -white"></i><span>Back to member name list</a>';




	} else {
		// Show first charactor of name
		$stmt = 'SELECT DISTINCT LEFT(`name`,1) `firstchar` FROM %db_org%';
		$ret .= '<nav class="nav -page">';
		$ret .= '<a class="btn" href="'.url('org/admin/orgmerge',array('dup'=>'name')).'">Duplicate Name</a> ';

		foreach(mydb::select($stmt)->items as $rs) {
			$ret .= '<a class="btn" href="'.url('org/admin/orgmerge',array('fc'=>$rs->firstchar)).'">&nbsp;'.$rs->firstchar.'&nbsp;</a> ';
		}
		$ret .= '</nav>';

		mydb::value('$IDLIST$', ', GROUP_CONCAT(`orgid` SEPARATOR ", ") `orgid`', false);
		mydb::value('$COUNT$', ', COUNT(*) `dupamt`');

		mydb::value('$GROUPBY$', 'GROUP BY `name`');
		mydb::value('$HAVING$', 'HAVING `dupamt` > 1');

		if (post('dup') == 'cid') {
			mydb::where('(`cid` IS NOT NULL AND `cid` != "")');
			mydb::value('$GROUPBY$', 'GROUP BY `cid`');
		}
		if (post('fc')) {
			mydb::where('LEFT(`name`,1) = :firstchar', ':firstchar', post('fc'));
			mydb::value('$IDLIST$','');
			mydb::value('$COUNT$','');
			mydb::value('$GROUPBY$','');
			mydb::value('$HAVING$','');
		}
		$stmt = 'SELECT
			 `name`
			, `orgid`
			$IDLIST$
			$COUNT$
			FROM %db_org% o
			%WHERE%
			$GROUPBY$
			$HAVING$
			ORDER BY CONVERT(`name` USING tis620) ASC
			';
		$dbs = mydb::select($stmt);

		//$ret .= mydb()->_query;

		$ret .= '<form method="post" action="'.url('org/admin/orgmerge').'">';
		$ret .= '<button class="btn -primary" type="submit" name="save"><i class="icon -material -white">merge_type</i><span>รวมชุดข้อมูล</span></button> รวม <b>'.number_format($dbs->count()).'</b> รายการ';
		$tables = new Table();
		$tables->thead = array('select -center' => '','merge -center'=>'','psnid -center' => 'psnid', 'ชื่อ', 'dup -amt -hover-parent' => 'ซ้ำ');
		foreach ($dbs->items as $rs) {
			$menuUi = new Ui('span');
			if ($rs->dupamt == 1)
				$menuUi->add('<a href="'.url('org/member/'.$rs->psnid).'" target="_blank"><i class="icon -viewdoc"></i></a>');
			$menu = '<nav class="iconset -hover">'.$menuUi->build().'</nav>'._NL;

			if ($rs->isjoin) $class .= '-joined';

			$psnIdList = str_replace(' ', '', $rs->orgid);

			$tables->rows[] = array(
				'<input type="checkbox" name="id['.$rs->orgid.']" value="'.$rs->orgid.'" />',
				'<a href="'.url('org/admin/orgmerge',array('id[]'=>$psnIdList)).'" target="_blank"><i class="icon -material">merge_type</i></a>',
				$rs->orgid,
				$rs->name,
				$rs->dupamt
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