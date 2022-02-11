<?php
function project_data_info($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo))
		return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, $projectInfo->title.' : ข้อมูลองค์กร', NULL, $projectInfo,'{showPrint: true}');

	$isEditable = $projectInfo->info->isEdit;
	$isEdit = $isEditable && $action == 'edit';
	//$ret .= 'Action = '.$action;

	switch ($action) {
		case 'addlogo':
			if ($isEditable && $_FILES['photo']['tmp_name']) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_logo');

				if ($rs->fid) {
					$photoData->fid = $rs->fid;
					$result = R::Model('photo.delete', $rs->fid, '{deleteRecord: false}');
				}

				$photoData->tpid = $tpid;
				$photoData->prename = 'project_logo_';
				$photoData->tagname = 'project_logo';
				$photoData->title = 'ภาพสัญญลักษณ์';
				$photoData->deleteurl = url('project/data/'.$tpid.'/info/deletelogo');

				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $photoData);

				$ret .= $uploadResult->link;
				//$ret .= '<div class="-sg-text-left">'.print_o($uploadResult, '$uploadResult').'</div>';
			}

			//$ret .= print_o($_FILES, '$_FILES');
			//$ret .= print_o($uploadResult, '$uploadResult');
			return $ret;
			break;

		case 'dellogo':
			if ($isEditable && SG\confirm()) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_logo');
				//$ret .= print_o($rs,'$rs');
				if ($rs->fid)
					$result = R::Model('photo.delete', $rs->fid);
				//$ret .= print_o($result, '$result');
				$ret .= '<img src="/library/img/none.gif" width="100%" height="100%" />';
			}
			return $ret;

		case 'addboard' :
			do {
				$keyId = substr(uniqid(mt_rand()),-3);
				$stmt = 'SELECT `bigid` FROM %bigdata% WHERE `keyid` = :keyid AND `fldname` LIKE :boardid LIMIT 1';
				$isDupId = mydb::select($stmt, ':keyid', $tpid)->bigid;
				$ret .= $isDupId;
			} while ($isDupId);

			$boardTagName = 'board-'.$keyId;
			$photoItem = '<div class="project-info-board -hover-parent">';

			$photoItem .= '<nav class="nav iconset -hover">';
			$photoItem .= '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/data/'.$tpid.'/info/addboardphoto/'.$boardTagName).'" data-rel="#'.$boardTagName.'">'
				.'<span class="fileinput-button">'
				.'<i class="icon -camera"></i>'
				.'<span class="-hidden">ส่งภาพ</span>'
				.'<input type="file" name="photo" class="inline-upload -board" />'
				.'</span>'
				.'</form>'._NL;
			$photoItem .= '<a class="sg-action" href="'.url('project/data/'.$tpid.'/info/delboardphoto/'.$boardTagName).'" data-rel="'.$boardTagName.'" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>';
			$photoItem .= '</nav>';

			$imgSrc = '/library/img/photography.png';

			$photoItem .= '<span id="'.$boardTagName.'" class="photo"><img src="'.$imgSrc.'" width="100"></span>';
			$photoItem .= '</div>';

			$ret .= '<tr><td class="col-photo">'
				. $photoItem.'</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'board-'.$keyId, 'tr' => '', 'key' => 'name', 'class' => '-fill', 'placeholder' => 'ชื่อ นามสกุล'), $value, $isEditable)
				. '</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'board-'.$keyId, 'tr' => '', 'key' => 'position', 'class' => '-fill', 'placeholder' => 'ตำแหน่ง'), $value, $isEditable)
				. '</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'board-'.$keyId, 'tr' => '', 'key' => 'phone', 'class' => '-fill', 'placeholder' => 'โทร'), $value, $isEditable)
				. '</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'board-'.$keyId, 'tr' => '', 'key' => 'email', 'class' => '-fill', 'placeholder' => 'อีเมล์'), $value, $isEditable)
				. '</td></tr>';
			return $ret;
			break;

		case 'delboard':
			if ($isEditable && SG\confirm() && $tranId) {
				$stmt = 'SELECT * FROM %bigdata% WHERE `bigid` = :bigid AND `keyid` = :keyid LIMIT 1';
				$rs = mydb::select($stmt, ':bigid', $tranId, ':keyid', $tpid);
				$boardTagName = $rs->fldname;

				// Delete from bigdata
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid AND `keyid` = :keyid LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId, ':keyid', $tpid);

				// Delete board photo
				if ($boardTagName) {
					$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
					$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_'.$boardTagName);
					//$ret .= print_o($rs,'$rs');
					if ($rs->fid)
						R::Model('photo.delete', $rs->fid);
				}

				$ret .= 'ลบรายการเรียบร้อย';
			}
			return $ret;
			break;

		case 'addboardphoto':
			if ($isEditable && $_FILES['photo']['tmp_name'] && $tranId) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_'.$tranId);

				if ($rs->fid) {
					$photoData->fid = $rs->fid;
					$result = R::Model('photo.delete', $rs->fid, '{deleteRecord: false}');
				}

				$photoData->tpid = $tpid;
				$photoData->prename = 'project_'.$tranId;
				$photoData->tagname = 'project_'.$tranId;
				$photoData->title = 'ชื่อ';
				$photoData->deleteurl = url('project/data/'.$tpid.'/info/deletephoto');

				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $photoData, '{fileNameLength: '.strlen($photoData->prename).'}');

				$ret .= $uploadResult->link;
				//$ret .= '<div class="-sg-text-left">'.print_o($uploadResult, '$uploadResult').'</div>';
			}

			//$ret .= $tranId.print_o($_FILES, '$_FILES');
			return $ret;
			break;

		case 'delboardphoto':
			if ($isEditable && SG\confirm() && $tranId) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_'.$tranId);
				//$ret .= print_o($rs,'$rs');
				if ($rs->fid)
					$result = R::Model('photo.delete', $rs->fid);
				//$ret .= print_o($result, '$result');
				$ret .= '<img src="/library/img/photography.png" width="100" height="120" />';
			}
			return $ret;
			break;

		case 'addproject' :
			do {
				$keyId = substr(uniqid(mt_rand()),-3);
				$stmt = 'SELECT `bigid` FROM %bigdata% WHERE `keyid` = :keyid AND `fldname` LIKE :boardid LIMIT 1';
				$isDupId = mydb::select($stmt, ':keyid', $tpid)->bigid;
				$ret .= $isDupId;
			} while ($isDupId);
			$ret .= '<tr><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'project-'.$keyId, 'tr' => '', 'key' => 'title', 'class' => '-fill', 'placeholder' => 'ชื่อโครงการ/กิจกรรม'), $value, $isEditable)
				. '</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'project-'.$keyId, 'tr' => '', 'key' => 'target', 'class' => '-fill', 'placeholder' => 'กลุ่มเป้าหมาย'), $value, $isEditable)
				. '</td><td>'
				. view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$keyId, 'fld' => 'project-'.$keyId, 'tr' => '', 'key' => 'grantby', 'class' => '-fill', 'placeholder' => 'ผู้สนับสนุน'), $value, $isEditable)
				. '</td></tr>';
			return $ret;
			break;


		default:
			# code...
			break;
	}




	$bigdataGroup = 'bigdata:project.info.plan';
	$info = $projectInfo->bigdata;


	if ($isEditable && !$isEdit)
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/data/'.$tpid.'/info/edit').'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	if ($isEditable && $isEdit)
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('project/data/'.$tpid.'/info').'" data-rel="#main"><i class="icon -save -white"></i></a></div>';

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= 'project-info';


	//$ret .= '<h2 class="title -main">ข้อมูลองค์กร</h2>';

	$ret .= '<div id="project-info-'.$tpid.'" '.sg_implode_attr($inlineAttr).'>'._NL;


	$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` LIKE "project_logo" LIMIT 1';
	$logoPhotoRs = mydb::select($stmt, ':tpid', $tpid);

	//$ret .= print_o($logoPhotoRs);

	if ($logoPhotoRs->file) {
		$photoProp = model::get_photo_property($logoPhotoRs->file);
		//$ret .= print_o($photoProp);
		if ($photoProp->_exists)
			$logoUrl = $photoProp->_url;
	}
	if (empty($logoUrl))
		$logoUrl = '/library/img/none.gif';

	$ret .= '<div class="-sg-text-center -hover-parent" style="width: 200px; margin: 32px auto;"><div class="project-info-logo" id="project-info-logo"><img src="'.$logoUrl.'" width="100%" height="100%" /></div><br />สัญลักษณ์ (โลโก้)';
	if ($isEdit) {
		$ret .= '<nav class="nav iconset -hover">';
		$ret .= '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/data/'.$tpid.'/info/addlogo').'" data-rel="#project-info-logo">'
			.'<span class="fileinput-button">'
			.'<i class="icon -camera"></i>'
			.'<span class="-hidden">ส่งภาพ</span>'
			.'<input type="file" name="photo" class="inline-upload -board" />'
			.'</span>'
			.'</form>'._NL;
		$ret .= '<a class="sg-action" href="'.url('project/data/'.$tpid.'/info/dellogo').'" data-rel="#project-info-logo" data-confirm="ต้องการลบภาพ กรุณายืนยัน?"><i class="icon -cancel"></i></a>';
		$ret .= '</nav>';
	}
	$ret .= '</div>';

	$ret .= '<section class="section">';
	$ret .= '<h3>รายละเอียดองค์กร<a class="project-toogle-display" href="javascript:void(0)"><icon class="icon -up"></i>v</a></h3>';
	$ret .= '<div class="box"><b>ชื่อองค์กรที่รับผิดชอบ</b><br />'
		. __inlineEdit('ชื่อองค์กร',$info, $isEdit,'{class: "-fill"}')
		. '<br /><br />'
		. '<b>ที่ตั้งองค์กร</b> (ระบุ ตำบล อำเภอ จังหวัด)<br />'
		. __inlineEdit('ที่ตั้งองค์กร',$info, $isEdit,'{class: "sg-address -fill"}')
		. '<br /><br />'
		. '<b>พิกัด</b><br />'
		. __inlineEdit('พิกัด',$info, $isEdit,'{class: "-fill"}')
		. '<br /><br />'
		. '<b>ประวัติองค์กร</b>'
		. __inlineEdit('ประวัติองค์กร',$info, $isEdit,'{class: "-fill", ret: "html"}', 'textarea').'<br /><br />'
		. '<b>วัตถุประสงค์ขององค์กร</b>'
		. __inlineEdit('วัตถุประสงค์ขององค์กร',$info, $isEdit,'{class: "-fill", ret: "html"}', 'textarea').'<br /><br />'
		. '<b>ภาระกิจหลัก</b>'
		. __inlineEdit('ภาระกิจ',$info, $isEdit,'{class: "-fill", ret: "html"}', 'textarea').'<br /><br />'
		;

	// TODO: เพิ่ม จดทะเบียนถูกต้องตามกฏหมาย จด/ไม่จด , เลขที่ทะเบียน , วันที่จดทะเบียน


	$ret .='<b>คณะกรรมการบริหารองค์กร</b>';
	$tables = new Table();
	$tables->addId('project-info-board');
	$tables->thead = array('photo' => '', 'ชื่อ - นามสกุล', 'ตำแหน่ง', 'โทร', 'email -hover-parent' => 'อีเมล์');
	//ประธาน/รองประธาน/กรรมการ/กรรมการ/กรรมการ/เลขานุการ</div>';
	$stmt = 'SELECT b.*, f.`fid`, f.`tagname`, f.`file`
		FROM %bigdata% b
			LEFT JOIN %topic_files% f ON f.`tpid` = b.`keyid` AND f.`tagname` = CONCAT("project_", b.`fldname`)
		WHERE b.`keyid` = :keyid AND b.`keyname` = "project.info.plan" AND b.`fldname` LIKE "board-%"
		ORDER BY `bigid` ASC';
	$dbs = mydb::select($stmt, ':keyid', $tpid);
	foreach ($dbs->items as $rs) {
		$data = sg_json_decode($rs->flddata);
		if ($isEdit) {
			$menu = '<nav class="nav iconset -hover"><a class="sg-action" href="'.url('project/data/'.$tpid.'/info/delboard/'.$rs->bigid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>';
		}

		$photoItem = '<div class="project-info-board -hover-parent">';

		if ($isEdit) {
			$photoItem .= '<nav class="nav iconset -hover">';
			$photoItem .= '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/data/'.$tpid.'/info/addboardphoto/'.$rs->fldname).'" data-rel="#'.$rs->fldname.'">'
				.'<span class="fileinput-button">'
				.'<i class="icon -camera"></i>'
				.'<span class="-hidden">ส่งภาพ</span>'
				.'<input type="file" name="photo" class="inline-upload -board" />'
				.'</span>'
				.'</form>'._NL;
			$photoItem .= '<a class="sg-action" href="'.url('project/data/'.$tpid.'/info/delboardphoto/'.$rs->fldname).'" data-rel="#'.$rs->fldname.'" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>';
			$photoItem .= '</nav>';
		}

		$imgSrc = $rs->file ? cfg('paper.upload.photo.url').$rs->file : '/library/img/photography.png';

		$photoItem .= '<span id="'.$rs->fldname.'" class="photo"><img src="'.$imgSrc.'" width="100"></span>';
		$photoItem .= '</div>';

		//$ui->add($photoItem,array('class' => '-hover-parent'));

		$tables->rows[] = array(
			$photoItem,
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'name', 'class' => '-fill'), $data->name, $isEdit),
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'position', 'class' => '-fill'), $data->position, $isEdit),
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'phone', 'class' => '-fill'), $data->phone, $isEdit),
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'email', 'class' => '-fill'), $data->email, $isEdit)
			.$menu,
		);
	}
	$ret .= $tables->build();

	if ($isEdit)
		$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/data/'.$tpid.'/info/addboard').'" data-rel="append:#project-info-board"><i class="icon -addbig -white"></i><span>เพิ่มกรรมการ</span></a></nav><br /><br />';
	$ret .= '</div>';
	$ret .= '</section>';




	$ret .= '<section>';
	$ret .= '<h3>กิจกรรมโดดเด่นที่องค์กรได้ขับเคลื่อน<a class="project-toogle-display" href="javascript:void(0)"><icon class="icon -up"></i>v</a></h3>';
	$ret .='<div class="box">';

	$tables = new Table();
	$tables->addId('project-info-action');
	$tables->thead = array('โครงการ/กิจกรรม', 'กลุ่มเป้าหมาย', 'grantby -hover-parent' => 'ผู้สนับสนุน');

	$stmt = 'SELECT * FROM %bigdata% WHERE `keyid` = :keyid AND `keyname` = "project.info.plan" AND `fldname` LIKE "project-%" ORDER BY `bigid` ASC';
	$dbs = mydb::select($stmt, ':keyid', $tpid);
	foreach ($dbs->items as $rs) {
		$data = sg_json_decode($rs->flddata);
		if ($isEdit) {
			$menu = '<nav class="nav iconset -hover"><a class="sg-action" href="'.url('project/data/'.$tpid.'/info/delboard/'.$rs->bigid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>';
		}
		$tables->rows[] = array(
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'title', 'class' => '-fill'), $data->title, $isEdit),
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'target', 'class' => '-fill'), $data->target, $isEdit),
			view::inlineedit(array('group' => 'bigdata:project.info.plan:'.$rs->fldname, 'fld' => $rs->fldname, 'tr' => $rs->bigid, 'key' => 'grantby', 'class' => '-fill'), $data->grantby, $isEdit)
			.$menu,
		);
	}
	$ret .= $tables->build();

	if ($isEdit)
		$ret .= '<nav class="nav -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/data/'.$tpid.'/info/addproject').'" data-rel="append:#project-info-action"><i class="icon -addbig -white"></i><span>เพิ่มโครงการ/กิจกรรม</span></a></nav><br /><br />';

	$ret .= '</div>';
	$ret .= '</section>';




	$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = "project_planfile" ORDER BY `fid` ASC';
	$dbs = mydb::select($stmt, ':tpid', $tpid);

	$ret .= '<section>';
	$ret .= '<h3>ไฟล์แผนงานองค์กร<a class="project-toogle-display" href="javascript:void(0)"><icon class="icon -up"></i>v</a></h3>';
	$ret .='<div class="box">';
	$ui = new Ui(NULL, 'project-info-planfile');
	$ui->addId('project-info-planfile');
	foreach ($dbs->items as $rs) {
		$menu = '';
		if ($isEdit)
			$menu .= '<a class="sg-action" href="'.url('project/doc/'.$tpid.'/remove/'.$rs->fid).'" data-rel="notify" data-removeparent="li" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>';

		$uploadUrl = cfg('paper.upload.document.url').$rs->file;

		$cardItem = '<a href="'.$uploadUrl.'" target="_blank"><img class="doc-logo -pdf" src="//img.softganz.com/icon/pdf-icon.png" /><span class="-title">'.$rs->title.'</span></a>';
		$cardItem .= '<nav class="nav iconset -hover">'.$menu.'</nav>';
		$ui->add($cardItem, '{class: "-hover-parent"}');
	}
	$ret .= $ui->build(true);

	$ret .= ($isEdit ? '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/doc/'.$tpid.'/upload').'" '
			.'data-rel="#project-info-planfile" data-append="li"'
			.'>'
			.'<input type="hidden" name="tagname" value="project_planfile" />'
			.'<span class="btn fileinput-button" style="margin: 32px 0;">'
			.'<i class="icon -upload"></i><br />'
			.'<span class="">อัพโหลดไฟล์แผนงานองค์กร</span>'
			.'<input type="file" name="doc" class="inline-upload -map" />'
			.'</span>'
			.'</form>' : '');

	$ret .= '</div>';
	$ret .= '</section>';



	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret .= '<style type="text/css">
		.project-info h3 {margin:0 0 5px 0; padding: 8px 16px; background:#eee; color:#333; border:1px #ddd solid; position: relative;}
		.project-toogle-display .icon {margin:0; position: absolute; right:0; top: calc(50% - 12px);}
		.project-info section {margin-bottom: 64px;}
		.project-info-logo {border: 1px #ccc solid; width: 200px; height:200px; overflow: hidden;}
		.project-info-logo img {width: 100%; height: 100%;}
		.project-info-planfile {display: flex; flex-wrap: wrap; margin:0; padding: 0; list-style-type: none;}
		.project-info-planfile>.ui-item {width: 50%; margin: 32px 0; text-align: center;}
		.project-info-planfile .doc-logo {display: block; margin: 0 auto;}
		.fileinput-button {padding: 0 0 8px 0;}
		.col-photo {width: 100px;}
		.project-info-board .photo {width: 100px; height: 120px; margin:0 auto; display: block;}
		.project-info-board .photo img {width: 100%; height:100%;}

	</style>';

	return $ret;
}

function __inlineEdit($key, $info, $isEdit=false, $inlinePara='{}', $type='text') {
	$bigdataGroup = 'bigdata:project.info.plan';
	if (substr($inlinePara,0,1)!='{') $inlinePara='{class: "'.$inlinePara.'"}';
	$para = array_merge(
		array('group' => $bigdataGroup.':'.$key, 'fld' => $key),
		(array) sg_json_decode($inlinePara)
	);
	$ret = view::inlineedit($para, $info[$key], $isEdit, $type);
	//$ret .= '$inlinePara = '.$inlinePara.'<br />';
	//$ret .= print_o(sg_json_decode($inlinePara),'decode');
	//$ret .= print_o($para,'$para');
	return $ret;
}

function __project_data_info_plan($projectInfo, $planName, $planAmt = 3, $isEdit = false) {
	$planName = 'แผน' . $planName . 'โครงการ';
	$info = $projectInfo->bigdata;
	$tables = new Table();
	$tables->thead = array('no'=>'ที่', 'ชื่อโครงการ', 'วัตถุประสงค์', 'วิธีดำเนินการ', 'กลุ่มเป้าหมาย', 'ห้วงดำเนินการ', 'amt' => 'งบประมาณ', 'ผลที่คาดว่าจะได้รับ');
	for($i = 1; $i<=$planAmt; $i++) {
		$tables->rows[] = array(
			$i,
			__inlineEdit($planName.$i.'ชื่อ',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "โครงการ..."}', 'textarea'),
			__inlineEdit($planName.$i.'เพื่อ',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "1 เพื่อ...<br />2 เพื่อ...<br />3 เพื่อ..."}', 'textarea'),
			__inlineEdit($planName.$i.'วิธี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "1 ...<br />2 ...<br />3 ..."}', 'textarea'),
			__inlineEdit($planName.$i.'เป้าหมาย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "เป็นใคร จำนวนเท่าไหร่"}', 'textarea'),
			__inlineEdit($planName.$i.'เวลา',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "ควรแล้วเสร็จ ก.ค. '.($projectInfo->info->pryear+543).'"}', 'textarea'),
			__inlineEdit($planName.$i.'งบ',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "0.00"}', 'textarea'),
			__inlineEdit($planName.$i.'ผล',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "1 ...(ระดับผลผลิต)<br />2 ...(ระดับผลลัพธ์)<br />3 ...(ระดับผลกระทบ)"}', 'textarea'),
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>