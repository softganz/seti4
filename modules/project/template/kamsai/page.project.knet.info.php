<?php
/**
* Project View
*
* @param Object $self
* @param Int $orgId
* @return String
*/
function project_knet_info($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	R::View('project.toolbar', $self, $orgInfo->name, 'knet', $orgInfo);


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER'))) && post('mode') != 'view';

	$ret .= '<div class="project-knet -container">';


	// Show school Information
	$ret .= '<section class="project-knet -info -sg-box">';
	$ui = new Ui();
	if ($isEdit) {
		$ui->add('<a class="sg-action btn -link" href="'.url('project/knet/'.$orgId.'/school.edit').'" data-rel="box" data-width="600"><i class="icon -material">edit</i><span>แก้ไข</span></a>');
	}

	$ret .= '<header class="header"><h3>ข้อมูลพื้นฐาน</h3><nav class="nav">'.$ui->build().'</nav></header>';
	$ret .= '<b><big>'.$orgInfo->name.'</big></b><br />';
	$ret .= 'สถานที่ตั้ง '.$orgInfo->info->address.'<br />';
	$ret .= 'จำนวนนักเรียน '.$orgInfo->info->studentamt.' คน<br />';
	$ret .= 'ช่วงชั้น '.$orgInfo->info->classlevel.'<br />';
	$ret .= 'ผู้อำนวยการ '.$orgInfo->info->managername.'<br />';
	$ret .= 'ครูผู้รับผิดชอบ '.$orgInfo->info->contactname.'<br />';

	$ret .= '</section>';





	// Show child school
	if ($orgInfo->info->networktype == 1) {
		$ret .= '<section class="project-knet -child -sg-box">';
		$ret .= '<header class="header"><h3>โรงเรียนเครือข่าย</h3></header>';
		$stmt = 'SELECT * FROM %db_org% WHERE `parent` = :parent ORDER BY CONVERT(`name` USING tis620) ASC';
		$dbs = mydb::select($stmt, ':parent', $orgId);

		$tables = new Table();

		foreach ($dbs->items as $rs) {
			$tables->rows[] = array('<a href="'.url('project/knet/'.$rs->orgid).'">'.$rs->name.'</a>');
		}

		$ret .= $tables->build();
		if ($isEdit) {
			$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -link" href="'.url('project/knet/'.$orgId.'/school.add').'" data-rel="box" title="สร้างโรงเรียนเครือข่าย" data-width="600"><i class="icon -material">add_circle</i><span>เพิ่มโรงเรียนเครือข่าย</span></a></nav>';
		}
		$ret .= '</section>';
	}




	// Show weight
	$ret .= '<section class="project-knet -weight -sg-box">';
	$ret .= '<header class="header"><h3>ข้อมูลโภชนาการ</h3></header>';
	$stmt = 'SELECT w.`trid`, w.`tpid`, w.`detail1` `year`, w.`detail2` `term`, w.`period` `time` FROM %project_tr% w LEFT JOIN %topic% t USING(`tpid`) WHERE w.`formid` = "weight" AND w.`part` = "title" AND (t.`orgid` = :orgid OR w.`orgid` = :orgid) ORDER BY `year` ASC,`term` ASC,`time` ASC';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();
	$tables->thead = array('weight -nowrap -hover-parent'=>'');
	$tables->addConfig('showHeader',false);

	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/knet/'.$orgId.'/weight.edit/'.$rs->trid).'" data-rel="box"><i class="icon -material -gray">edit</i></a>');
			$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		}
		$tables->rows[] = array(
			'<a class="sg-action" href="'.url('project/knet/'.$orgId.'/weight.view/'.$rs->trid).'" data-rel="box">ปีการศึกษา '.($rs->year+543).' เทอม '.$rs->term.'/'.$rs->time.'</a>'
			. $menu
		);
	}

	$ret .= $tables->build();
	if ($isEdit) {
		$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -link" href="'.url('project/knet/'.$orgId.'/weight.add').'" data-rel="box"><i class="icon -material">add_circle</i><span>เพิ่มข้อมูลโภชนาการ</span></a></nav>';
	}
	$ret .= '</section>';




	// Show officer
	$ret .= '<section class="project-knet -officer -sg-box">';
	$ret .= '<header class="header"><h3>เจ้าหน้าที่</h3></header>';
	if ($isEdit) {
		$ret .= '<form class="sg-form" action="'.url('org/info/api/'.$orgId.'/officer.add').'" data-rel="refresh"><input id="admin-add-officer-uid" type="hidden" name="uid" value="" />';
	}

	$stmt = 'SELECT u.`uid`, u.`name`, o.`membership` FROM %org_officer% o LEFT JOIN %users% u USING(`uid`) WHERE o.`orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();
	$tables->thead = array('','membership -nowrap -hover-parent'=>'');
	$tables->addConfig('showHeader',false);
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('org/info/api/'.$orgId.'/officer.remove/'.$rs->uid).'" data-rel="none" data-title="ลบเจ้าหน้าที่" data-confirm="ต้องการลบเจ้าหน้าที่ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -material -gray">cancel</i></a>');
			$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		}
		$tables->rows[] = array(
			$isEdit ? '<a class="sg-action" href="'.url('project/knet/'.$orgId.'/officer/'.$rs->uid).'" data-rel="box">'.$rs->name.'</a>' : $rs->name,
			$rs->membership
			.$menu,
		);
	}
	if ($isEdit) {
		$tables->rows[]=array(
			'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('api/user',array('r'=>'id')).'" data-altfld="admin-add-officer-uid" size="40" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
			'<select name="membership" class="form-select"><option value="ADMIN">Admin</option><option value="OFFICER" selected="selected">Officer</option><option value="TRAINER">Trainer</option><option value="MEMBER">Regular Member</option></select> '
			.'<button class="btn -link"><i class="icon -material">add_circle</i></button>'
		);
	}

	$ret .= $tables->build();
	if ($isEdit) $ret .= '</form>';
	$ret .= '</section>';






	// Show action
	$ret .= '<section class="project-knet -action -sg-box">';
	$ret .= '<header class="header"><h3>โครงการ</h3></header>';
	$stmt = 'SELECT p.`tpid`, t.`title`, p.`date_from` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE t.`orgid` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->date_from ? sg_date($rs->date_from,'j/n/ปปปป') : '',
			'<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>'
		);
	}

	$ret .= $tables->build();


	$ui = new Ui();
	if ($isEdit) {
		$ui->add('<a class="sg-action btn -link" href="'.url('project/knet/'.$orgId.'/action.add').'" data-rel="box" data-width="640"><i class="icon -material">add_circle</i><span>บันทึกกิจกรรม</span></a>');
	}

	$ret .= '<header class="header -box"><h3>กิจกรรม</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$stmt = 'SELECT a.*, u.`username`, u.`name`
		FROM
			(SELECT
			  p.`tpid`, t.`orgid`, an.`trid` `actionId`, an.`uid`
			, an.`date1` `actionDate`
			, c.`title`
			, an.`text2` `actionReal`
			, an.`text4` `outputOutcomeReal`
			, an.`created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_tr% an ON an.`tpid` = p.`tpid` AND an.`formid` = "activity" AND an.`part` = "owner"
				LEFT JOIN %calendar% c ON c.`id` = an.`calid`
			WHERE t.`orgid` = :orgid
			UNION
			SELECT
				NULL, an.`orgid`, an.`trid`, an.`uid`
			, an.`date1`
			, ac.`detail1`
			, an.`text2` `actionReal`
			, an.`text4` `outputOutcomeReal`
			, an.`created`
			FROM %project_tr% an
				LEFT JOIN %project_tr% ac ON ac.`trid` = an.`refid`
			WHERE an.`orgid` = :orgid AND an.`formid` = "activity" AND an.`part` = "org"
			ORDER BY `actionDate` DESC
		) a
			LEFT JOIN %users% u USING(`uid`)
		';
	$dbs = mydb::select($stmt, ':orgid', $orgId);
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= mydb()->_query;

	$cardUi = new Ui('div','ui-card -hover-parent');

	foreach ($dbs->items as $rs) {
		$cardStr = '';
		$cardStr .= '<h3><a class="sg-action" href="'.url('project/knet/'.$rs->orgid.'/activity.view/'.$rs->actionId).'" data-rel="box">'.$rs->title.'</a></h3>';

		if ($isEdit) {
			$ui=new Ui();
			$ui->add('<a class="sg-action" href="'.url('project/knet/'.$orgId.'/action.edit/'.$rs->actionId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไข</span></a>');
			if (empty($rs->tpid)) {
				$ui->add('<a class="sg-action" href="'.url('project/knet/'.$orgId.'/action.delete/'.$rs->actionId).'" data-title="ลบรายการบันทึก" data-rel="notify" data-confirm="ลบรายการบันทึกนี้ทิ้ง รวมทั้งภาพถ่าย'._NL._NL.'กรุณายืนยัน?" data-removeparent="div.ui-item.-action"><i class="icon -delete"></i>ลบทิ้ง</a>');
			}
			$cardStr.=sg_dropbox($ui->build(),'{class:"leftside -atright"}');
		}

		$cardStr.='<div class="card-item -header">'._NL;
		$cardStr.='<a class="sg-action" href="'.url('project/knet/'.$orgId.'/user.info/'.$rs->username).'" data-webview="'.$rs->name.'"><img class="card-item -owner-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="" /><span class="card-item -owner-name">'.$rs->name.'</a></span>';

		$cardStr .= '<span class="card-item -timestamp"> เมื่อ '.sg_date($rs->actionDate,'ว ดด ปป').' น. @'.sg_date($rs->created,'ว ดด ปป H:i');
		$cardStr.='</span>'._NL;
		$cardStr.='</div><!-- timestamp -->'._NL;

	$stmt = 'SELECT
		f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
		FROM %topic_files% f
		WHERE f.`orgid` = :orgid AND f.`refid` = :refid AND f.`type` = "photo" AND f.`tagname` = "project,knet,action"
		';

	$photoDbs = mydb::select($stmt, ':orgid', $orgId, ':refid', $rs->actionId);
	//$cardStr .= print_o($photoDbs, '$photoDbs');

	// Show photo
	$photoUi = new Ui(NULL, 'ui-album -justify-left');
	$photoUi->addId('project-info-photo-card');

	foreach ($photoDbs->items as $item) {
		$photoStrItem = '';
		$ui = new Ui('span');

		if ($item->type == 'photo') {
			//$ret.=print_o($item,'$item');
			$photo = model::get_photo_property($item->file);

			if ($isEdit) {
				$ui->add('<a class="sg-action" href="'.url('project/knet/'.$orgId.'/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
			}

			$photo_alt = $item->title;

			$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

			$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
			$photoStrItem .= '<img class="photoitem photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
			$photoStrItem .= ' />';
			$photoStrItem .= '</a>';


			if ($isEdit) {
				$photoStrItem .= view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $item->fid, 'options' => '{class: "-fill", placeholder: "คำอธิบายภาพ"}', 'container' => '{class: "-fill -photodetail"}'), $item->title, $isEdit, 'text');
			} else {
				$photoStrItem .= '<span>'.$item->title.'</span>';
			}

			$photoUi->add($photoStrItem, '{class: "-hover-parent"}');

		} else if ($item->type == 'doc') {
			$docStr = '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($item->title).'" target="_blank">';
			$docStr .= '<img class="photoitem" src="http://img.softganz.com/icon/pdf-icon.png" width="80%" alt="'.$item->title.'" />';
			$docStr .= '</a>';

			if ($isEdit) {
				$ui->add(' <a class="sg-action" href="'.url('project/knet/'.$orgId.'/photo.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-title="ลบไฟล์" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
			}
			$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
			$photoUi->add($docStr, '{class: "-doc -hover-parent"}');
		} else if ($item->type == 'movie') {
			list($a,$youtubeId) = explode('?v=', $item->file);
			$docStr = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtubeId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div class="detail"><span>'.$item->title.'</span><span><a href="'.$item->file.'" target="_blank">View on YouTube</a></span></div>';

			if ($isEdit) {
				$ui->add(' <a class="sg-action" href="'.url('project/knet/'.$orgId.'/vdo.delete/'.$item->fid).'" title="ลบ Video" data-title="ลบ Video" data-confirm="ยืนยันว่าจะลบ Video นี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
			}
			$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
			$photoUi->add($docStr, '{class: "-vdo -hover-parent"}');
		}
	}

	$cardStr .= $photoUi->build(true);


	$ui = new Ui();
	if ($isEdit) {
		$ui->add('<form class="sg-upload -no-print" '
			. 'method="post" enctype="multipart/form-data" '
			. 'action="'.url('project/knet/'.$orgId.'/photo.upload/'.$rs->actionId).'" '
			. 'data-rel="#project-info-photo-card'.'" data-append="li">'
			. '<input type="hidden" name="tagname" value="action" />'
			. '<span class="btn -primary btn-success fileinput-button"><i class="icon -material">add_a_photo</i>'
			. '<span>ส่งภาพถ่าย</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" />'
			. '</span>'
			. '</form>'
		);
	}

	$cardStr .= '<nav class="nav -sg-text-center -no-print" style="padding: 32px 0;">'.$ui->build().'</nav>';




		$cardStr .= '<div class="card-item -summary">';
		$cardStr .= '<b>รายละเอียด:</b><div>'.sg_text2html($rs->actionReal).'</div>';
		$cardStr .= '<b>ผลผลิต/ผลลัพธ์:</b><div>'.sg_text2html($rs->outputOutcomeReal).'</div>';
		$cardStr .= '</div><!-- summary -->'._NL;


		$albumUi = new Ui('div', 'ui-album');

		if ($rs->photos) {
			foreach (explode(',',$rs->photos) as $photoItem) {
				$albumStr = '';
				list($fid,$photofile)=explode('|', $photoItem);
				if (!$fid) continue;

				$photo = model::get_photo_property($rs->file);
				if ($photo->_exists) {
					$albumStr .= '<a class="sg-action" href="'.$photo->_src.'" data-rel="img"><img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" height="206" width="206" /></a>';
				} else {
					$albumStr .= '<span title="'.$rs->file.'">Photo not exists</span>';
				}

				if ($isEdit) {
					$ui = new Ui('span','iconset -hover');
					$ui->add('<a class="sg-action -no-print" href="'.url('project/knet'.$orgId.'/photo.delete/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i></a>');
					$albumStr.=$ui->build();
				}
				$albumUi->add($albumStr);
			}
		}
		if (0 && $isEdit) {
			$albumUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/knet/'.$orgId.'/action.uploadphoto/'.$rs->actionId).'" data-rel="#ui-album-'.$rs->seq.' .photo" data-before="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
		}
		$cardStr .= '<div class="card-item -photo">'.$albumUi->build().'</div>'._NL;

		//$cardStr .= print_o($rs,'$rs');
		$cardUi->add($cardStr,array('id'=>'action-'.$rs->actionId,'class'=>'-action'));
	}

	$ret .= $cardUi->build();

	$ret .= '</section>';


	$ret .= '</div><!-- project-knet -container -->';


	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '<style type="text/css">
	.project-knet .header {background: #fff5ee;}
	.project-knet .header h3 {font-size: 1.4em;}
	.project-knet.-action {box-shadow: none; padding: 0;}
	.card-item.-header {padding: 8px; color: #999; font-size: 0.9em;}
	.card-item.-header a {color: #999;}
	.card-item.-owner-name {font-weight: bold;}
	.card-item.-owner-photo {width: 24px; height: 24px; vertical-align: middle; border-radius: 50%; margin:0 8px 0 0;}
	.card-item.-summary {padding: 8px;}
	</style>';

	return $ret;
}
?>
