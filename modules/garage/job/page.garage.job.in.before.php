<?php
/**
* Garage :: Car Check Before Car In
* Created 2020-07-25
* Modify  2020-07-25
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_in_before($self, $jobInfo) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'NO JOB');

	$shopId = ($shopInfo = $jobInfo->shopInfo) ? $shopInfo->shopId : NULL;
	$isEditable = true;
	$beforeInfo = R::Model('bigdata.json', 'get', 'GARAGE', $jobId, 'DATA')->before;

	new Toolbar($self,'ตรวจรถก่อนซ่อม - '.$jobInfo->plate,'in', $jobInfo);

	$ret = '<section id="garage-job-in-before" class="garage-job-in-before">';
	$ret .= '<header class="header"><h3>ตรวจรถก่อนซ่อม</h3></header>';

	$checkList = array(
		//'{label: "น้ำมันเชื้อเพลิง"}',
		'{header: "ตรวจสอบทั่วไป"}',
		'{subheader: "รหัสการตรวจสอบ,มี,"}',
		'{label: "ขีดข่วน", input: [{type: "checkbox", field: "ขีดข่วน"},{type: "none"}]}',
		'{label: "สี ขีด,ด้าน,พอง,ยุบ", input: [{type: "checkbox", field: "สี"},{type: "none"}]}',
		'{label: "รอยบุบ,รอยแตก", input: [{type: "checkbox", field: "รอยบุบ"},{type: "none"}]}',
		'{label: "ฉีกขาด", input: [{type: "checkbox", field: "ฉีกขาด"},{type: "none"}]}',
		'{label: "อะไหล่ไม่มีมา", input: [{type: "checkbox", field: "อะไหล่ไม่มีมา"},{type: "none"}]}',
		'{label: "ไม่มีรอยขีดข่วน", input: [{type: "checkbox", field: "ไม่มีรอยขีดข่วน"},{type: "none"}]}',
		'{header: "รายการสิ่งของต่าง ๆ"}',
		'{subheader: "ภายในห้องโดยสาร,มี,จำนวน"}',
		'{label: "แผ่นรองปูพื้น", input: [{type: "checkbox", field: "แผ่นรองปูพื้น"},{type: "select", field: "แผ่นรองปูพื้น-จำนวน"}]}',
		'{label: "วิทยุ", input: [{type: "checkbox", field: "วิทยุ"},{type: "select", field: "วิทยุ-จำนวน"}]}',
		'{label: "ลำโพง", input: [{type: "checkbox", field: "ลำโพง"},{type: "select", field: "ลำโพง-จำนวน"}]}',
		'{label: "ที่จุดบุหรี่", input: [{type: "checkbox", field: "ที่จุดบุหรี่"},{type: "select", field: "ที่จุดบุหรี่-จำนวน"}]}',
		'{label: "กุญแจ รีโมท", input: [{type: "checkbox", field: "กุญแจรีโมท"},{type: "select", field: "กุญแจรีโมท-จำนวน"}]}',
		'{label: "ล็อคเกียร์", input: [{type: "checkbox", field: "ล็อคเกียร์"},{type: "select", field: "ล็อคเกียร์-จำนวน"}]}',
		'{label: "เพาเวอร์", input: [{type: "checkbox", field: "เพาเวอร์"},{type: "select", field: "เพาเวอร์-จำนวน"}]}',
		'{subheader: "ภายในฝากระโปรงท้าย,มี,จำนวน"}',
		'{label: "ยางอะไหล่", input: [{type: "checkbox", field: "ยางอะไหล่"},{type: "select", field: "ยางอะไหล่-จำนวน"}]}',
		'{label: "แม่แรง", input: [{type: "checkbox", field: "แม่แรง"},{type: "select", field: "แม่แรง-จำนวน"}]}',
		'{label: "เครื่องมือ", input: [{type: "checkbox", field: "เครื่องมือ"},{type: "select", field: "เครื่องมือ-จำนวน"}]}',
		'{label: "แผ่นกระดานปิดยาง", input: [{type: "checkbox", field: "แผ่นกระดานปิดยาง"},{type: "select", field: "แผ่นกระดานปิดยาง-จำนวน"}]}',
		'{label: "ผ้ายางท้าย", input: [{type: "checkbox", field: "ผ้ายางท้าย"},{type: "select", field: "ผ้ายางท้าย-จำนวน"}]}',
		'{label: "สายพ่วงแบตเตอรี", input: [{type: "checkbox", field: "สายพ่วงแบตเตอรี"},{type: "select", field: "สายพ่วงแบตเตอรี-จำนวน"}]}',
		'{label: "กล่องซีดี", input: [{type: "checkbox", field: "กล่องซีดี"},{type: "select", field: "กล่องซีดี-จำนวน"}]}',
		'{header: "ตรวจเช็คระบบต่าง ๆ"}',
		'{label: "ระบบแอร์", input: [{type: "checkbox", field: "ระบบแอร์"},{type: "none"}]}',
		'{label: "ระบบกระจกไฟฟ้า", input: [{type: "checkbox", field: "ระบบกระจกไฟฟ้า"},{type: "none"}]}',
		'{label: "ระบบเซ็นทรัลล็อค", input: [{type: "checkbox", field: "ระบบเซ็นทรัลล็อค"},{type: "none"}]}',
		'{label: "ระบบแบตเตอรี", input: [{type: "checkbox", field: "ระบบแบตเตอรี"},{type: "none"}]}',
		'{label: "ระบบวิทยุ", input: [{type: "checkbox", field: "ระบบวิทยุ"},{type: "none"}]}',
		'{label: "ระบบไฟฟ้า", input: [{type: "checkbox", field: "ระบบไฟฟ้า"},{type: "none"}]}',
	);

	$tables = new Table();
	$tables->addConfig('showHeader',false);
	$tables->thead = array('รหัสการตรวจสอบ', 'have -center' => 'มี', 'amt' => '');
	foreach ($checkList as $itemString) {
		$item = SG\json_decode($itemString);
		//debugMsg($itemString.'<br />');
		//$ret .= print_o($item,'$item');
		$row = array();
		if ($item->header) {
			$row[] = '<th colspan="3">'.$item->header.'</th>';
			$row['config'] = '{class: "header"}';
		} else if ($item->subheader) {
			foreach (explode(',', $item->subheader) as $text) {
			 	$row[] = '<th>'.$text.'</th>';
			 }
			$row['config'] = '{class: "subheader"}';
		} else if ($item->label) {
			$row[] = $item->label;
			foreach ($item->input as $input) {
				if ($input->type == 'checkbox') {
					$row[] = '<label class="btn -link -fill"><input type="checkbox" class="-hidden" name="'.$input->field.'" value="YES" '.($beforeInfo->{$input->field} ? 'checked="checked"' : '').' /><i class="icon -material -gray">check_circle</i></label>';
				} else if ($input->type == 'select') {
					$selectOptions = '<option>0</option>';
					for ($i = 1; $i <= 10; $i++) $selectOptions .= '<option value="'.$i.'"'.($i == $beforeInfo->{$input->field} ? ' selected="selected"' : '').'>'.$i.'</option>';
					$row[] = '<select class="form-select" name="'.$input->field.'">'.$selectOptions.'</select>';
				} else if ($input->type == 'text') {
					$row[] = '<input class="form-text -numeric" type="text" size="2" placeholder="0" />';
				} else {
					$row[] = '';
				}
			}
		}
		$tables->rows[] = $row;
	}


	$ret .= '<form id="garage-job-in-before-form" method="post" action="'.url('garage/job/'.$jobId.'/info/data.save').'" class="sg-form garage-job-in-before-form">';
	$ret .= $tables->build();
	$ret .= '</form>';

	$ret .= '<div>'
		. '<header class="header"><h3>ภาพรถก่อนซ่อม</h3></header>'
		. __photoAlbum($jobInfo,'before,out', $isEditable)->build(true)
		. '<header class="header"><h3>ภาพภายในรถก่อนซ่อม</h3></header>'
		. __photoAlbum($jobInfo,'before,in', $isEditable)->build(true)
		. '<header class="header"><h3>ภาพใบขับขี่</h3></header>'
		. __photoAlbum($jobInfo,'before,lcn', $isEditable)->build(true)
		. '<header class="header"><h3>ภาพอื่นๆ</h3></header>'
		. __photoAlbum($jobInfo,'before,etc', $isEditable)->build(true)
		. '</div>';

	$ret .= '<form id="garage-job-in-before-form" method="post" action="'.url('garage/job/'.$jobId.'/info/data.save').'" class="sg-form garage-job-in-before-form">'
		. '<div class="form-item"><label>หมายเหตุ</label><textarea class="form-textarea -fill" name="remark" rows="10">'.htmlspecialchars_decode(str_replace('<br />', _NL, $beforeInfo->remark)).'</textarea></div>'
		//. '<textarea class="form-textarea -fill" name="test" rows="10">'.str_replace('<br />', _NL, $beforeInfo->test).'</textarea>'
		. '<div class="form-item"><label>พนักงานที่ตรวจสอบ </label><input type="text" class="form-text -fill" name="officerName" value="'.htmlspecialchars($beforeInfo->officerName).'" /></div>'
		. '</form>';

	//$ret .= htmlspecialchars_decode($beforeInfo->remark);

	//$ret .= print_o($beforeInfo,'$beforeInfo');
	//$ret .= print_o($jobInfo,'$jobInfo');
	//$ret .= print_o($shopInfo,'$shopInfo');
	$ret .= '</section>';
	return $ret;
}

function __photoAlbum($jobInfo, $tagName, $isEditable) {
	$jobId = $jobInfo->jobId;
	$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname';
	$photoList = mydb::select($stmt, ':tpid', $jobId, ':tagname', 'garage,'.$tagName)->items;
	//debugMsg($photoList,'$photoList');

	$albumId = str_replace(',', '-', $tagName);
	$photoAlbumUi = new Ui(NULL,'ui-album -'.$albumId.' -justify-left');
	$photoAlbumUi->addId($albumId);

	foreach ($photoList as $photoItem) {
		$photoInfo = model::get_photo_property($photoItem->file);

		$photoNavUi = new Ui('span');
		if ($isEditable) {
			$photoNavUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/photo.delete/'.$photoItem->fid).'" data-rel="notify" data-done="remove:parent .ui-album>.ui-item" data-title="ลบภาพ" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$photo_alt = $photoItem->title;
		$photoAlbumUi->add(
			'<a class="sg-action" data-group="photo-'.$photoItem->jobtrid.'" href="'.url('garage/job/'.$photoItem->tpid.'/photo/'.$photoItem->fid).'" data-rel="box" title="'.htmlspecialchars($photo_alt).'">'
			. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="120" />'
			. '</a>'
			. '<nav class="nav -icons -hover">'.$photoNavUi->build().'</nav>',
			'{id: "photo-'.$photoItem->fid.'", class: "-hover-parent"}'
		);
	}

	$photoAlbumUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('garage/job/'.$jobId.'/info/photo.upload').'" data-rel="#'.$albumId.'" data-before="li"><input type="hidden" name="tagname" value="'.$tagName.'" /><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>', '{class: "-upload"}');

	//$photoAlbumUi->add('<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('garage/job/'.$jobId.'/info/photo.upload').'" data-rel="#job-photo-'.$class.' .ui-album.-'.$class.'" data-append="li"><input type="hidden" name="tagname" value="'.$tagName.'" /><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');

	return $photoAlbumUi;
}
?>