<?php
function project_data_info($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	R::View('project.toolbar',$self, 'แผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน', NULL, $projectInfo,'{showPrint: true}');

	$isEdit = $projectInfo->info->isEdit;

	switch ($action) {
		case 'addboardphoto':
			if ($isEdit && $_FILES['photo']['tmp_name'] && $tranId) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_cmboard_'.$tranId);

				if ($rs->fid) {
					$photoData->fid = $rs->fid;
					$result = R::Model('photo.delete', $rs->fid, '{deleteRecord: false}');
				}

				$photoData->tpid = $tpid;
				$photoData->prename = 'project_cmboard_';
				$photoData->tagname = 'project_cmboard_'.$tranId;
				$photoData->title = $projectInfo->bigdata['กม-'.$tranId.'-ชื่อ'];
				$photoData->deleteurl = url('project/data/'.$tpid.'/info/deletephoto');

				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $photoData);

				$ret .= $uploadResult->link;
				//$ret .= '<div class="-sg-text-left">'.print_o($uploadResult, '$uploadResult').'</div>';
			}

			//$ret .= print_o($_FILES, '$_FILES');
			return $ret;
			break;

		case 'delboardphoto':
			if ($isEdit && SG\confirm() && $tranId) {
				$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_cmboard_'.$tranId);
				//$ret .= print_o($rs,'$rs');
				if ($rs->fid)
					$result = R::Model('photo.delete', $rs->fid);
				//$ret .= print_o($result, '$result');
				$ret .= '<img src="/library/img/photography.png" width="140" height="180" />';
			}
			return $ret;
			break;

		default:
			# code...
			break;
	}




	$bigdataGroup = 'bigdata:project.info.plan';
	$info = $projectInfo->bigdata;


	$inlineAttr['class'] = 'project-info';

	if ($isEdit) {
		$inlineAttr['class'] .= ' inline-edit';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}


	$ret .= '<div id="project-info-'.$tpid.'" '.sg_implode_attr($inlineAttr).'>'._NL;





	//$ret .= '<p class="notify -no-print">กำลังดำเนินการ</p>';

	$ret .= '<section class="box -cover"><h3>แผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน</h3><br />'
		.'<b>โครงการขยายผลพัฒนาหมู่บ้าน/ชุมชนเข้มแข็ง มั่นคง มั่งคง ยั่งยืน พ.ศ. 2561</b><br /><br /><br />'
		.'บ้าน '
		.__inlineEdit('ชื่อบ้าน',$info, $isEdit)
		.' หมู่ที่ '
		.__inlineEdit('หมู่ที่', $info, $isEdit)
		.' ตำบล '
		.__inlineEdit('ตำบล', $info, $isEdit)
		.' อำเภอ '
		.__inlineEdit('อำเภอ', $info, $isEdit)
		.' จังหวัด '
		.__inlineEdit('จังหวัด', $info, $isEdit)
		.'</section>';



	$ret .= '<section class="box"><h3>คำนำ</h3>'
		.__inlineEdit('คำนำ', $info, $isEdit,'{class: "-fill", ret: "html"}', 'textarea')
		.'<p class="-sg-text-center">คณะกรรมการหมู่บ้าน ....................................<br />'
		.'วัน.............เดือน...............................พ.ศ.............</p>'
		.'</section>';



	$ret .= '<section class="box"><h3>สารบัญ</h3>
		<p class="-sg-text-right">หน้า</p>
		<p>
		<b>ส่วนที่ 1 ข้อมูลทั่วไปของหมู่บ้าน</b><br />
		1.1 วิสัยทัศน์<br />
		1.2 ข้อมูลทั่วไปของหมู่บ้าน<br />
		1.3 ข้อมูลอาณาเขตพื้นที่หมู่บ้าน<br />
		1.4 ข้อมูลประชากรในพื้นหมู่บ้าน<br />
		1.5 ข้อมูลการศึกษาในพื้นที่หมู่บ้าน<br />
		1.6 ข้อมูลด้านศาสนาในพื้นที่หมู่บ้าน<br />
		1.7 ข้อมูลเศรษฐกิจในพื้นที่หมู่บ้าน<br />
		1.8 ข้อมูลการท่องเที่ยวในพื้นที่หมู่บ้าน<br />
		1.9 ข้อมูลการบริการขั้นพื้นฐานในพื้นที่หมู่บ้าน<br /><br />

		<b>ส่วนที่ 2 ข้อมูลการวิเคราะห์สภาพปัญหาในพื้นที่หมู่บ้าน</b><br />
		2.1 สถานการณ์ในอดีตและปัจจุบันในหมู่บ้าน <br />
		2.2 จุดแข็งหรือทุนทางสังคมหรือของดีของหมู่บ้านที่สนับสนุนต่อการพัฒนาหมู่บ้าน<br />
		2.3 จุดอ่อนหรือปัญหาอุปสรรค์ต่อการพัฒนาหมู่บ้าน<br />
		2.4 แนวทางแก้ไขและพัฒนาหมู่บ้าน<br />
		2.5 โครงการสำคัญที่ผ่านมาที่ส่งผลต่อการพัฒนาหมู่บ้านให้เกิดความเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน<br /><br />

		<b>ส่วนที่ 3 แผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน</b><br />
		3.1 แผนงาน/โครงการที่ขอรับการสนับสนุนงบประมาณจากโครงการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน ประจำปี 2561<br />
			- ด้านความมั่นคง<br />
			- ด้านการสร้างความเข้าใจ<br />
			- ด้านการพัฒนา<br />
			</p>
		</section>'._NL;


	$ret .= '<section class="box section-1">'._NL;
	$ret .= '<h3>ส่วนที่ 1 ข้อมูลทั่วไปของหมู่บ้าน</h3>';

		$ret .= '<p><b>1.1 วิสัยทัศน์</b><br />'
			.__inlineEdit('วิสัยทัศน์', $info, $isEdit, '-fill')
			.'</p>';

		$ret .= '<b>1.2 ข้อมูลทั่วไปของหมู่บ้าน</b><br /><br />'
			.'1) พิกัด<br />'
			. $projectInfo->info->lat.','.$projectInfo->info->lnt.'<br />'
			//.__inlineEdit('พิกัด', $info, $isEdit, '-fill')
			.'แผนที่เดินดิน<br />'
			.($isEdit ? '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/photo/'.$tpid.'/upload').'" '
			.'data-rel="#project-info-walkmap" data-prepend="li"'
			.'>'
			.'<input type="hidden" name="tagname" value="project_walkmap" />'
			.'<span class="btn fileinput-button" style="margin: 32px 0;">'
			.'<i class="icon -camera"></i><br />'
			.'<span class="">อัพโหลดแผนที่เดินดิน</span>'
			.'<input type="file" name="photo" class="inline-upload -map" />'
			.'</span>'
			.'</form>' : '');

		$ui = new Ui(NULL, 'ui-album');
		$ui->addId('project-info-walkmap');
		$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` LIKE "project_walkmap%";';
		$walkMapPhotoDbs = mydb::select($stmt, ':tpid', $tpid);

		foreach ($walkMapPhotoDbs->items as $item) {
			$photoStrItem = '';
			if ($item->type == 'photo') {
				//$ret.=print_o($item,'$item');
				$photo=model::get_photo_property($item->file);
				$photo_alt = $item->title;
				$photoStrItem .= '<a class="sg-action" data-group="walkmap" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$photoStrItem .= ' />';
				$photoStrItem .= '</a>';

				$itemNav = new Ui('span');
				if ($isEdit) {
					$itemNav->add('<a class="sg-action" href="'.url('project/photo/'.$tpid.'/remove/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-removeparent="li"><i class="icon -delete"></i></a>');
				}
				$photoStrItem .= '<nav class="nav iconset -hover -no-print">'.$itemNav->build().'</nav>';
			}
			$ui->add($photoStrItem, '{class: "-hover-parent"}');
		}
		$ret .= $ui->build(true);

		//$ret .= print_o($walkMapPhotoDbs, '$walkMapPhotoDbs');

		$ret .= '</ul>'
			.'<br /><br />'
			.'2) ประวัติหมู่บ้าน'
			.__inlineEdit('ประวัติหมู่บ้าน',$info, $isEdit, '{class: "-fill", ret: "html"}', 'textarea')
			.'<br /><br />'
			.'3) โครงสร้างคณะกรรมการหมู่บ้าน (กม.)/อพป.<br /><br />';


		$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` LIKE "project_cmboard_%"; -- {key: "tagname"}';
		$boardPhotoDbs = mydb::select($stmt, ':tpid', $tpid);
		//$ret .= print_o($boardPhotoDbs);

		$ui = new Ui('div', 'ui-card -board');

		$cardList = array(
			'ประธานคณะกรรมการหมู่บ้าน',
			'หัวหน้าคณะทำงาน<br />ด้านอำนวยการ',
			'หัวหน้าคณะทำงาน<br />ด้านการปกครองและรักษาความปลอดภัย',
			'หัวหน้าคณะทำงาน<br />ด้านแผนพัฒนาหมู่บ้าน',
			'หัวหน้าคณะทำงาน<br />ด้านส่งเสริมเศรษฐกิจ',
			'หัวหน้าคณะทำงาน<br />ด้านสังคม สิ่งแวดล้อม และสาธารณสุข',
			'หัวหน้าคณะทำงาน<br />ด้านศึกษา ศาสนา และวัฒนธรรม',
			'หัวหน้าคณะทำงาน<br />ด้านอื่นๆ',
		);

		foreach ($cardList as $key => $item) {
			$boardPhotoKey = $key+1;
			$boardPhotoId = 'project-info-board-'.$boardPhotoKey;
			$boardPhotoFileId = 'project_cmboard_'.$boardPhotoKey;
			$cardItem = '';

			if ($isEdit) {
				$cardItem .= '<nav class="nav iconset -hover">';
				$cardItem .= '<form class="sg-upload -no-print -inline" method="post" enctype="multipart/form-data" action="'.url('project/data/'.$tpid.'/info/addboardphoto/'.$boardPhotoKey).'" data-rel="#'.$boardPhotoId.'">'
					.'<span class="fileinput-button">'
					.'<i class="icon -camera"></i>'
					.'<span class="-hidden">ส่งภาพ</span>'
					.'<input type="file" name="photo" class="inline-upload -board" />'
					.'</span>'
					.'</form>'._NL;
				$cardItem .= '<a class="sg-action" href="'.url('project/data/'.$tpid.'/info/delboardphoto/'.$boardPhotoKey).'" data-rel="'.$boardPhotoId.'" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>';
				$cardItem .= '</nav>';
			}

			$imgSrc = $boardPhotoDbs->items[$boardPhotoFileId] ? cfg('paper.upload.photo.url').$boardPhotoDbs->items[$boardPhotoFileId]->file : '/library/img/photography.png';

			$cardItem .= '<span id="'.$boardPhotoId.'" class="photo"><img src="'.$imgSrc.'" width="120"></span>';
			$cardItem .= '<p>';
			$cardItem .= __inlineEdit('กม-'.$boardPhotoKey.'-ชื่อ', $info, $isEdit, '{class: "-fill", placeholder: "ชื่อ-สกุล"}');
			$cardItem .= __inlineEdit('กม-'.$boardPhotoKey.'-โทร', $info, $isEdit, '{class: "-fill", placeholder: "เบอร์โทรศัทพ์"}');
			$cardItem .= $item.'</p>';

			$ui->add($cardItem,array('class' => '-hover-parent'));
		}
		$ret.=$ui->build();

		$ret.='<br clear="all" />';


		$ret .= '4) ศูนย์ราชการในหมู่บ้าน<br />'
			.'<em>(ทั่วไปมีอะไรบ้างได้แก่ วัด มัสยิด โรงเรียน โรงพยาบาล สถานีอนามัย ป้อมตำรวจ ศูนย์เด็กฯ หอกระจายข่าว ศาลาหมู่บ้าน รวมถึง หน่วยราชการต่างๆ)</em><br />'
			.__inlineEdit('ศูนย์ราชการ', $info, $isEdit, '{class: "-fill", ret: "html"}', 'textarea')
			.'<br /><br />';

		$ret .= '<b>1.3 ข้อมูลอาณาเขตในพื้นที่หมู่บ้าน</b><br /><br />'
			.'1) อาณาเขต<br />'
			.'ทิศเหนือ ติดต่อกับ<br />'
			.__inlineEdit('ทิศเหนือ', $info, $isEdit, '-fill')
			.'ทิศใต้ ติดต่อกับ<br />'
			.__inlineEdit('ทิศใต้', $info, $isEdit, '-fill')
			.'ทิศตะวันออก ติดต่อกับ<br />'
			.__inlineEdit('ทิศตะวันออก', $info, $isEdit, '-fill')
			.'ทิศตะวันตก	ติดต่อกับ<br />'
			.__inlineEdit('ทิศตะวันตก', $info, $isEdit, '-fill')
			.'<br /><br />'
			.'2) ลักษณะภูมิประเทศ<br /><em>(ได้แก่ ภูมิอากาศและฤดูกาล เช่น มีสภาพื้นที่ราบลุ่ม ฤดูร้อนอากาศร้อนมาก หรือฤดูฝน ฝนตกหนัก ตกไม่แน่นอน สภาพอากาศเหมาะสำหรับการประกอบอาชีพทำนา เพาะปลูก หรือเลี้ยงสัตว์อะไร)</em>'
			.__inlineEdit('ภูมิประเทศ', $info, $isEdit, '{class: "-fill", ret: "html"}', 'textarea')
			.'<br /><br />';

		$ret .= '<b>1.4 ข้อมูลประชากรในพื้นที่หมู่บ้าน</b><br /><br />'
			.'1) ประชากรของหมู่บ้านทั้งหมด จำนวน '
			.__inlineEdit('จำนวนประชากร', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนประชากรชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / เพศหญิง '
			.__inlineEdit('จำนวนประชากรหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'
			//
			.'2) เยาวชนในหมู่บ้านทั้งหมด จำนวน '
			.__inlineEdit('จำนวนเยาวชน', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนเยาวชนชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / เพศหญิง '
			.__inlineEdit('จำนวนเยาวชนหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><em>("เด็ก" หมายถึง บุคคลที่มีอายุเกิน 7 ปีบริบูรณ์ แต่ยังไม่เกิน 14 ปีบริบูรณ์ "เยาวชน" หมายถึง บุคคลที่มีอายุเกิน 14 ปีบริบูรณ์ แต่ยังไม่ถึง 18 ปีบริบูรณ์)</em><br /><br />'
			//
			.'3) ผู้สูงอายุในหมู่บ้านทั้งหมด จำนวน '
			.__inlineEdit('จำนวนผู้สูงอายุ', $info, $isEdit, '{ret: "numeric"}').' คน '
			.'แยกเป็น เพศชาย'
			.__inlineEdit('จำนวนผู้สูงอายุชาย', $info, $isEdit, '{ret: "numeric"}').' คน / '
			.'เพศหญิง'
			.__inlineEdit('จำนวนผู้สูงอายุหญิง', $info, $isEdit, '{ret: "numeric"}').' คน<br /><em>(อายุ 60 ปีขึ้นไป)</em><br /><br />'
			//
			.'4) ประชากร/ครัวเรือนยากจน<br /><em>(รายได้ไม่ถึง 30,000 บาท/คน/ปี ยึดข้อมูล จปฐ. ปี 2560)</em><br />
				- ประชากรในหมู่บ้านมีรายได้ จำนวน '
			.__inlineEdit('รายได้ประชากร', $info, $isEdit, '{ret: "money"}').
			' บาท/คน/ปี<br />'
			.'- ครัวเรือนยากจน จำนวน '
			.__inlineEdit('ครัวเรือนยากจน', $info, $isEdit, '{ret: "numeric"}').
			' ครัวเรือน<br /><br />'
			.'5) เด็กกำพร้า จำนวน '
			.__inlineEdit('จำนวนเด็กกำพร้า', $info, $isEdit, '{ret: "numeric"}').
			' คน	 แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนเด็กกำพร้าชาย', $info, $isEdit, '{ret: "numeric"}').
			' คน/ เพศหญิง '
			.__inlineEdit('จำนวนเด็กกำพร้าหญิง', $info, $isEdit, '{ret: "numeric"}').
			' คน<br /><br />'
			//
			.'6) คนพิการ จำนวน '
			.__inlineEdit('จำนวนคนพิการ', $info, $isEdit, '{ret: "numeric"}').
			' คน 	แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนคนพิการชาย', $info, $isEdit, '{ret: "numeric"}').
			' คน / เพศหญิง '
			.__inlineEdit('จำนวนคนพิการหญิง', $info, $isEdit, '{ret: "numeric"}').
			' คน<br /><br />'
			.'7) ผู้ป่วยติดเตียง จำนวน '
			.__inlineEdit('จำนวนผู้ป่วยติดเตียง', $info, $isEdit, '{ret: "numeric"}').
			' คน 	แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนผู้ป่วยติดเตียงชาย', $info, $isEdit, '{ret: "numeric"}').
			' คน / เพศหญิง '
			.__inlineEdit('จำนวนผู้ป่วยติดเตียงหญิง', $info, $isEdit, '{ret: "numeric"}').
			' คน<br /><br />';

		$ret .= '<b>1.5 ข้อมูลการศึกษาในพื้นที่หมู่บ้าน</b><br /><br />'
			//// การศึกษาสายสามัญ
			.'1) การศึกษาสายสามัญ 	จำนวน '
			.__inlineEdit('จำนวนศึกษาสายสามัญ', $info, $isEdit, '{ret: "numeric"}')
			.' คน '
			.'แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนศึกษาสายสามัญชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง'
			.__inlineEdit('จำนวนศึกษาสายสามัญหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'

			//// การศึกษาสายอาชีพ
			.'2) การศึกษาสายอาชีพ จำนวน '
			.__inlineEdit('จำนวนศึกษาสายอาชีพ', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น เพศชาย '
			.__inlineEdit('จำนวนศึกษาสายอาชีพชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาสายอาชีพหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'

			//// การศึกษาทางศาสนา
			.'3) การศึกษาทางศาสนา '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาทางศาสนา', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาทางศาสนาชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาทางศาสนาหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'
			.'4) การศึกษาระดับอุดมศึกษา '
			.' จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดม', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'

			//// การศึกษาระดับอุดมศึกษาในประเทศ
			.'- การศึกษาระดับอุดมศึกษาในประเทศ<br />'
			.'ระดับปริญญาตรี'
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมในตรี', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมในตรีชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมในตรีหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'ระดับปริญญาโท '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมในโท', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมในโทชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมในโทหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'ระดับปริญญาเอก '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมในเอก', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมในเอกชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมในเอกหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'

			//// การศึกษาระดับอุดมศึกษาต่างประเทศ
			.'- การศึกษาระดับอุดมศึกษาต่างประเทศ<br />'
			.'ระดับปริญญาตรี '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมต่างตรี', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมต่างตรีชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมต่างตรีหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'ระดับปริญญาโท '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมต่างโท', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมต่างโทชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมต่างโทหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'ระดับปริญญาเอก '
			.'จำนวน '
			.__inlineEdit('จำนวนศึกษาอุดมต่างเอก', $info, $isEdit, '{ret: "numeric"}')
			.' คน แยกเป็น '
			.'เพศชาย '
			.__inlineEdit('จำนวนศึกษาอุดมต่างเอกชาย', $info, $isEdit, '{ret: "numeric"}')
			.' คน / '
			.'เพศหญิง '
			.__inlineEdit('จำนวนศึกษาอุดมต่างเอกหญิง', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'

			//// สถานศึกษาในพื้นที่หมู่บ้าน
			.'5) สถานศึกษาในพื้นที่หมู่บ้าน<br />'
			.'- โรงเรียนประถมศึกษา จำนวน '
			.__inlineEdit('จำนวนโรงเรียนประถม', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- โรงเรียนมัธยมศึกษา จำนวน '
			.__inlineEdit('จำนวนโรงเรียนมัธยม', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- โรงเรียนเอกชนสอนศาสนา จำนวน '
			.__inlineEdit('จำนวนโรงเรียนเอกชน', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- สถาบันปอเนาะ จำนวน '
			.__inlineEdit('จำนวน', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- ตาดีกา จำนวน '
			.__inlineEdit('จำนวนปอเนาะ', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- อื่น<br />'
			.__inlineEdit('สถานศึกษาอื่น', $info, $isEdit, '{class: "-fill", placeholder: "อื่นๆ ระบุ"}')
			.'<br /><br />';

		$ret .= '<b>1.6 ข้อมูลด้านศาสนาในพื้นที่หมู่บ้าน</b><br /><br />'
			.'1) ผู้นับถือศาสนาในพื้นที่หมู่บ้าน<br />'
			.'- ศาสนาอิสลาม 	จำนวน '
			.__inlineEdit('จำนวนอิสลาม', $info, $isEdit, '{ret: "numeric"}')
			.' คน '
			.'คิดเป็นร้อยละของประชากรในหมู่บ้าน '
			.__inlineEdit('ร้อยละอิสลาม', $info, $isEdit, '{ret: "numeric"}')
			.'<br />'
			.'- ศาสนาพุทธ จำนวน '
			.__inlineEdit('จำนวนพุทธ', $info, $isEdit, '{ret: "numeric"}')
			.'คน '
			.'คิดเป็นร้อยละของประชากรในหมู่บ้าน '
			.__inlineEdit('ร้อยละพุทธ', $info, $isEdit, '{ret: "numeric"}')
			.'<br />'
			.'- ศาสนาอื่นโปรด จำนวน '
			.__inlineEdit('จำนวนศาสนาอื่น', $info, $isEdit, '{ret: "numeric"}')
			.' คน '
			.'คิดเป็นร้อยละของประชากรในหมู่บ้าน '
			.__inlineEdit('ร้อยละศาสนาอื่น', $info, $isEdit, '{ret: "numeric"}')
			.'<br /><br />'
			.'2) ศาสนสถานในพื้นที่หมู่บ้าน<br />'
			.'- มัสยิด จำนวน '
			.__inlineEdit('จำนวนมัสยิด', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- บาราเซาะ /สุเหร่า จำนวน '
			.__inlineEdit('จำนวนบาราเซาะ', $info, $isEdit, '{ret: "numeric"}')
			.'แห่ง<br />'
			.'- วัด จำนวน '
			.__inlineEdit('จำนวนวัด', $info, $isEdit, '{ret: "numeric"}')
			.'แห่ง<br />'
			.'- สำนักสงฆ์ จำนวน '
			.__inlineEdit('จำนวนสำนักสงฆ์', $info, $isEdit, '{ret: "numeric"}')
			.'แห่ง	<br />'
			.'- ที่พักสงฆ์ จำนวน '
			.__inlineEdit('จำนวนที่พักสงฆ์', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br />'
			.'- อื่นๆ ระบุ '
			.__inlineEdit('ศาสนสถานอื่น', $info, $isEdit, '{class: "-fill", placeholder: "อื่นๆ ระบุ"}')
			.'<br /><br />';

		$ret .= '<b>1.7 ข้อมูลเศรษฐกิจในพื้นที่หมู่บ้าน</b><br /><br />'
			.'1) อาชีพหลักประชากรในพื้นที่หมู่บ้าน  <em>(เช่น ค้าขาย ทอผ้า หรือรับจ้างทั่วไป เป็นต้น)</em><br />'

			// อาชีพหลัก
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก1', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก1', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก2', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก2', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก3', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก3', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก4', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก4', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก5', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก5', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพหลัก6', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพหลัก6', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br /><br />'

			// อาชีพเสริม
			.'2) อาชีพเสริมประชากรในพื้นที่หมู่บ้าน <em>(เช่น เกษตรกรรม เลี้ยงสัตว์ เป็นต้น)</em><br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม1', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม1', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม2', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม2', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม3', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม3', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม4', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม4', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม5', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม5', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br />'
			.'ระบุอาชีพ '
			.__inlineEdit('อาชีพเสริม6', $info, $isEdit)
			.' จำนวน '
			.__inlineEdit('จำนวนอาชีพเสริม6', $info, $isEdit, '{ret: "numeric"}')
			.' ครัวเรือน<br /><br />'

			// ภูมิปัญญา
			.'3) ภูมิปัญญา/ผลผลิตหรือผลิตภัณฑ์ที่น่าสนใจของหมู่บ้าน<br />'
			.__inlineEdit('ภูมิปัญญาผลผลิตเด่น', $info, $isEdit, '{class: "-fill", ret: "html"}', 'textarea').'<br >'

			// ข้อมูลศูนย์การเรียนรู้ในพื้นที่หมู่บ้าน
			.'4) ข้อมูลศูนย์การเรียนรู้ในพื้นที่หมู่บ้าน<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้1ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้1ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้1โทร', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้2ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้2ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้2โทร', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้3ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้3ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้3โทร', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้4ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้4ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้4โทร', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้5ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้5ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้5โทร', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('ศูนย์การเรียนรู้6ชื่อ', $info, $isEdit)
			.' ชื่อผู้รับผิดชอบ '
			.__inlineEdit('ศูนย์การเรียนรู้6ชื่อคน', $info, $isEdit)
			.' เบอร์โทร '
			.__inlineEdit('ศูนย์การเรียนรู้6โทร', $info, $isEdit)
			.'<br /><br />'

			.'5) หน่วยธุรกิจในหมู่บ้าน (เช่น ปั๊มน้ำมัน โรงสี ร้านค้า)'
			.'จำนวน '
			.__inlineEdit('จำนวนหน่วยธุรกิจ', $info, $isEdit, '{ret: "numeric"}')
			.' แห่ง<br /><br />'

			.'6) ข้อมูลกลุ่มหรือองค์กรในพื้นที่หมู่บ้าน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร1ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร1สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร2ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร2สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร3ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร3สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร4ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร4สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร5ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร5สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกลุ่ม/องค์กร '
			.__inlineEdit('กลุ่มองค์กร6ชื่อ', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กลุ่มองค์กร6สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br /><br />'

			.'7) กองทุนในหมู่บ้าน<br />'
			.'จำนวน '
			.__inlineEdit('จำนวนกองทุน', $info, $isEdit, '{ret: "numeric"}').' แห่ง<br />'
			.'เช่น กองทุน '
			.__inlineEdit('ชื่อกองทุน', $info, $isEdit, '{class: "-fill", placeholder: "ระบุตัวอย่างชื่อกองทุน"}')
			.'<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน1ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน1ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุน1สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน2ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน2ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุน2สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน3ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน3ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุน3สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน4ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน4ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุน4สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน5ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน5ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุน5สมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'- ชื่อกองทุน '
			.__inlineEdit('กองทุน6ชื่อ', $info, $isEdit)
			.' ชื่อ/เบอร์โทรประธาน '
			.__inlineEdit('กองทุน6ประธาน', $info, $isEdit)
			.' จำนวนสมาชิก '
			.__inlineEdit('กองทุนสมาชิก', $info, $isEdit, '{ret: "numeric"}')
			.' คน<br />'
			.'<br />';

		$ret .= '<b>1.8 ข้อมูลการท่องเที่ยวในพื้นที่หมู่บ้าน</b><br />'
			.'1) แหล่งท่องเที่ยวที่สำคัญ<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว1ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว1ที่ตั้ง', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว2ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว2ที่ตั้ง', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว3ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว3ที่ตั้ง', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว4ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว4ที่ตั้ง', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว5ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว5ที่ตั้ง', $info, $isEdit)
			.'<br />'
			.'- ชื่อสถานที่ '
			.__inlineEdit('แหล่งท่องเที่ยว6ชื่อ', $info, $isEdit)
			.' ที่ตั้ง '
			.__inlineEdit('แหล่งท่องเที่ยว6ที่ตั้ง', $info, $isEdit)
			.'<br /><br />'

			.'2) ประเพณีวัฒนธรรมที่สำคัญของหมู่บ้าน <em>(เช่นประเพณีสงกรานต์ ประเพณีลอยกระทง ประเพณีสารทเดือนสิบ ประเพณีกวนอาซูรอ เป็นต้น)</em>'
			.__inlineEdit('ประเพณีสำคัญ', $info, $isEdit, '{class: "-fill", ret: "html"}', 'textarea')
			.'<br /><br />';

		$ret .= '<b>1.9 ข้อมูลการบริการขั้นพื้นฐานในพื้นที่หมู่บ้าน</b><br /><br />'
			// ถนน
			.'1) ถนน<br />'
			.'ข้อมูลการเดินทางเข้าหมู่บ้าน '
			.'จากอำเภอ '
			.__inlineEdit('เดินทางจากอำเภอ', $info, $isEdit)
			.'ใช้ทางหลวงสาย '
			.__inlineEdit('เดินทางทางหลวง', $info, $isEdit)
			.' ระยะ '
			.__inlineEdit('เดินทางระยะ', $info, $isEdit, '{ret: "numeric"}')
			.' กิโลเมตร<br /><br />'

			.'- ถนนลาดยาง จำนวน '
			.__inlineEdit('ถนนลาดยางจำนวน', $info, $isEdit, '{ret: "numeric"}')
			.'สาย ได้แก่<br />'
			.'ชื่อถนน '
			.__inlineEdit('ถนนลาดยาง1ชื่อ', $info, $isEdit)
			.' ระยะทางประมาณ '
			.__inlineEdit('ถนนลาดยาง1ระยะ', $info, $isEdit, '{ret: "numeric"}')
			.' กิโลเมตร<br />'
			.'ชื่อถนน '
			.__inlineEdit('ถนนลาดยาง2ชื่อ', $info, $isEdit)
			.' ระยะทางประมาณ '
			.__inlineEdit('ถนนลาดยาง2ระยะ', $info, $isEdit, '{ret: "numeric"}')
			.' กิโลเมตร<br />'
			.'<br />'

			.'- ถนนลูกรัง จำนวน '
			.__inlineEdit('ถนนลูกรังจำนวน', $info, $isEdit, '{ret: "numeric"}')
			.'สาย ได้แก่<br />'
			.'ชื่อถนน '
			.__inlineEdit('ถนนลูกรัง1ชื่อ', $info, $isEdit)
			.' ระยะทางประมาณ '
			.__inlineEdit('ถนนลูกรัง1ระยะ', $info, $isEdit, '{ret: "numeric"}')
			.' กิโลเมตร<br />'
			.'ชื่อถนน '
			.__inlineEdit('ถนนลูกรัง2ชื่อ', $info, $isEdit)
			.' ระยะทางประมาณ '
			.__inlineEdit('ถนนลูกรัง2ระยะ', $info, $isEdit, '{ret: "numeric"}')
			.' กิโลเมตร<br />'
			.'<br />'

			// การไฟฟ้า
			.'2) การไฟฟ้า <em>(มีไฟฟ้าใช้ครอบคลุมทุกครัวเรือนในหมู่บ้าน )</em> คิดเป็นร้อยละ '
			.__inlineEdit('ถนนลูกรัง2ระยะ', $info, $isEdit, '{ret: "numeric"}')
			.'<br /><br />'

			// แหล่งน้ำธรรมชาติและแหล่งน้ำสาธารณะ
			.'3) แหล่งน้ำธรรมชาติและแหล่งน้ำสาธารณะ<br />';

		$tables = new Table();
		$tables->caption = 'แหล่งน้ำธรรมชาติ';
		$tables->thead = array('no'=>'', 'ชื่อลำน้ำ/แหล่งน้ำ', 'ไหลผ่านหมู่บ้าน', 'สภาพแหล่งน้ำ');
		for ($i=1; $i<=5; $i++) {
			$tables->rows[] = array(
				$i,
				__inlineEdit('ลำน้ำ'.$i.'ชื่อ', $info, $isEdit, '-fill'),
				__inlineEdit('ลำน้ำ'.$i.'ผ่าน', $info, $isEdit, '-fill'),
				__inlineEdit('ลำน้ำ'.$i.'สภาพ', $info, $isEdit, '-fill'),
			);
		}

		$ret .= $tables->build();
		$ret .= '<em>สภาพแหล่งน้ำ เช่น ตื้นเขิน กักเก็บน้ำได้น้อย ในฤดูแล้งไม่มีน้ำ</em>';

		$tables = new Table();
		$tables->caption = 'แหล่งน้ำสาธารณะ';
		$tables->thead = array('no'=>'', 'ชื่ออ่างเก็บน้ำ/สระน้ำ/หนองน้ำ', 'ขนาด/ความจุ ต่อไร่', 'สภาพแหล่งน้ำ');
		for ($i=1; $i<=5; $i++) {
			$tables->rows[] = array(
				$i,
				__inlineEdit('แหล่งน้ำ'.$i.'ชื่อ', $info, $isEdit, '-fill'),
				__inlineEdit('แหล่งน้ำ'.$i.'ผ่าน', $info, $isEdit, '-fill'),
				__inlineEdit('แหล่งน้ำ'.$i.'สภาพ', $info, $isEdit, '-fill'),
			);
		}

		$ret .= $tables->build();
		$ret .= '<em>สภาพแหล่งน้ำ เช่น น้ำตื้นเขิน น้ำใช้ได้ตลอดปี น้ำใช้ไม่ตลอดปี</em><br /><br />';

		$ret .= '4) พื้นที่ป่าชุมชน<br />'
			.'- ชื่อป่าชุมชน '
			.__inlineEdit('ป่าชุมชน1ชื่อ', $info, $isEdit)
			.' จำนวนพื้นที่ '
			.__inlineEdit('ป่าชุมชน1พื้นที่', $info, $isEdit, '{ret: "numeric"}')
			.' ไร่<br />'
			.'- ชื่อป่าชุมชน '
			.__inlineEdit('ป่าชุมชน2ชื่อ', $info, $isEdit)
			.' จำนวนพื้นที่ '
			.__inlineEdit('ป่าชุมชน2พื้นที่', $info, $isEdit, '{ret: "numeric"}')
			.' ไร่<br />'
			.'<br />'
			;

	$ret.='</section><!-- section-1 -->'._NL;





	$ret .= '<section class="box section-2">'._NL;
	$ret .= '<h3>ส่วนที่ 2 ข้อมูลการวิเคราะห์สภาพปัญหาในพื้นที่หมู่บ้าน</h3>';

		$ret.= '<b>2.1 สถานการณ์ในอดีตและปัจจุบันในหมู่บ้าน</b><br /><br />'
			//// ด้านความมั่นคง
			.'2.1.1 ด้านความมั่นคง<br /><br />'
			.'1) ยุทธศาสตร์ คนดี มีคุณธรรม เช่น<br />'
			.__inlineEdit('สถานการณ์คนดีมีคุณธรรม',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่รอดปลอดภัย เช่น<br />'
			.__inlineEdit('สถานการณ์อยู่รอดปลอดภัย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'

			//// ด้านการพัฒนา
			.'2.1.2 ด้านการพัฒนา<br /><br />'
			.'1) ยุทธศาสตร์ อยู่เย็น เป็นสุข เช่น<br />'
			.__inlineEdit('สถานการณ์อยู่เย็นเป็นสุข',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่ดี กินดี เช่น<br />'
			.__inlineEdit('สถานการณ์อยู่ดีกินดี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'

			////
			.'2.1.3 ด้านการสร้างความเข้าใจ<br /><br />'
			.'1) ยุทธศาสตร์ อยู่ร่วมกันอย่างสันติสุข<br />'
			.__inlineEdit('สถานการณ์อยู่ร่วมกัน',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />';

			//// จุดแข็งหรือทุนทางสังคมหรือของดีของหมู่บ้านที่สนับสนุนต่อการพัฒนาหมู่บ้าน
		$ret .= '<b>2.2 จุดแข็ง หรือทุนทางสังคมหรือของดีของหมู่บ้านที่สนับสนุนต่อการพัฒนาหมู่บ้าน</b><br /><br />'
			.'2.2.1 ด้านความมั่นคง<br /><br />'
			.'1) ยุทธศาสตร์ คนดี มีคุณธรรม เช่น<br />'
			.__inlineEdit('จุดแข็งคนดีมีคุณธรรม',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่รอดปลอดภัย เช่น<br />'
			.__inlineEdit('จุดแข็งอยู่รอดปลอดภัย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.2.2 ด้านการพัฒนา<br /><br />'
			.'1) ยุทธศาสตร์ อยู่เย็น เป็นสุข เช่น<br />'
			.__inlineEdit('จุดแข็งอยู่เย็นเป็นสุข',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่ดี กินดี เช่น<br />'
			.__inlineEdit('จุดแข็งอยู่ดีกินดี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.2.3 ด้านการสร้างความเข้าใจ<br /><br />'
			.'1) ยุทธศาสตร์ อยู่ร่วมกันอย่างสันติสุข<br />'
			.__inlineEdit('จุดแข็งอยู่ร่วมกัน',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />';

		//// จุดอ่อนหรือปัญหาอุปสรรค์ต่อการพัฒนาของหมู่บ้าน
		$ret .= '<b>2.3 จุดอ่อนหรือปัญหาอุปสรรค์ต่อการพัฒนาของหมู่บ้าน</b><br /><br />'
			.'2.3.1 ด้านความมั่นคง<br /><br />'
			.'1) ยุทธศาสตร์ คนดี มีคุณธรรม เช่น<br />'
			.__inlineEdit('จุดอ่อนคนดีมีคุณธรรม',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่รอดปลอดภัย เช่น<br />'
			.__inlineEdit('จุดอ่อนอยู่รอดปลอดภัย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.3.2 ด้านการพัฒนา<br /><br />'
			.'1) ยุทธศาสตร์ อยู่เย็น เป็นสุข เช่น<br />'
			.__inlineEdit('จุดอ่อนอยู่เย็นเป็นสุข',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่ดี กินดี เช่น<br />'
			.__inlineEdit('จุดอ่อนอยู่ดีกินดี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.3.3 ด้านการสร้างความเข้าใจ<br /><br />'
			.'1) ยุทธศาสตร์ อยู่ร่วมกันอย่างสันติสุข<br />'
			.__inlineEdit('จุดอ่อนอยู่ร่วมกัน',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />';

		//// แนวทางแก้ไขปัญหาและพัฒนาหมู่บ้าน
		$ret .= '<b>2.4 แนวทางแก้ไขปัญหาและพัฒนาหมู่บ้าน</b><br /><br />'
			.'2.4.1 ด้านความมั่นคง<br /><br />'
			.'1) ยุทธศาสตร์ คนดี มีคุณธรรม เช่น<br />'
			.__inlineEdit('แนวทางคนดีมีคุณธรรม',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่รอดปลอดภัย เช่น<br />'
			.__inlineEdit('แนวทางอยู่รอดปลอดภัย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.4.2 ด้านการพัฒนา<br /><br />'
			.'1) ยุทธศาสตร์ อยู่เย็น เป็นสุข เช่น<br />'
			.__inlineEdit('แนวทางอยู่เย็นเป็นสุข',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่ดี กินดี เช่น<br />'
			.__inlineEdit('แนวทางอยู่ดีกินดี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.4.3 ด้านการสร้างความเข้าใจ<br /><br />'
			.'1) ยุทธศาสตร์ อยู่ร่วมกันอย่างสันติสุข<br />'
			.__inlineEdit('แนวทางอยู่ร่วมกัน',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			;


		//// โครงการสำคัญที่ผ่านมาที่ส่งผลต่อการพัฒนาหมู่บ้านให้เกิดความเข้มแข็ง
		$ret .= '<b>2.5 โครงการสำคัญที่ผ่านมาที่ส่งผลต่อการพัฒนาหมู่บ้านให้เกิดความเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน</b> (ระบุไม่เกิน 5 โครงการ)<br /><br />'
			.'2.5.1 ด้านความมั่นคง<br /><br />'
			.'1) ยุทธศาสตร์ คนดี มีคุณธรรม เช่น<br />'
			.__inlineEdit('โครงการคนดีมีคุณธรรม',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่รอดปลอดภัย เช่น<br />'
			.__inlineEdit('โครงการอยู่รอดปลอดภัย',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.5.2 ด้านการพัฒนา<br /><br />'
			.'1) ยุทธศาสตร์ อยู่เย็น เป็นสุข เช่น<br />'
			.__inlineEdit('โครงการอยู่เย็นเป็นสุข',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2) ยุทธศาสตร์ อยู่ดี กินดี เช่น<br />'
			.__inlineEdit('โครงการอยู่ดีกินดี',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />'
			.'2.5.3 ด้านการสร้างความเข้าใจ<br /><br />'
			.'1) ยุทธศาสตร์ อยู่ร่วมกันอย่างสันติสุข<br />'
			.__inlineEdit('โครงการอยู่ร่วมกัน',$info, $isEdit, '{class: "-fill", ret: "html", placeholder: "(1) ...<br />(2) ...<br />(3) ..."}', 'textarea')
			.'<br /><br />';

	$ret.='</section><!-- section-2 -->'._NL;




$maxProject = 6;

$ret .= '<section class="box section-2">'._NL;
$ret .= '<h3>ส่วนที่3 แผนปฏิบัติการหมู่บ้านเข้มแข็ง มั่นคง มั่งคั่ง ยั่งยืน</h3>';
	$ret .= '<b>3.1 ด้านความมั่นคง</b><br /><br />'
		.'1) ยุทธศาสตร์คนดี มีคุณธรรม<br />'
		.__project_data_info_plan($projectInfo, 'คนดีมีคุณธรรม', $maxProject, $isEdit)
		.'<br />'
		.'2) ยุทธศาสตร์อยู่รอด ปลอดภัย<br />'
		.__project_data_info_plan($projectInfo, 'อยู่รอดปลอดภัย', $maxProject, $isEdit)
		.'<br />'
		.'<b>3.2 ด้านการพัฒนา</b><br /><br />'
		.'1) ยุทธศาสตร์อยู่เย็น เป็นสุข<br />'
		.__project_data_info_plan($projectInfo, 'อยู่เย็นเป็นสุข', $maxProject, $isEdit)
		.'<br />'
		.'2) ยุทธศาสตร์อยู่ดี กินดี<br />'
		.__project_data_info_plan($projectInfo, 'อยู่ดีกินดี', $maxProject, $isEdit)
		.'<br />'
		.'<b>3.2 ด้านการสร้างความเข้าใจ</b><br />'
		.'1) ยุทธศาสตร์อยู่ร่วมกันอย่างสันติสุข<br />'
		.__project_data_info_plan($projectInfo, 'อยู่ร่วมกัน', $maxProject, $isEdit)
		;




	$ret.='</section><!-- section-2 -->'._NL;


	$ret.='</div><!-- project-info -->';



	head('<style type="text/css">
		.ui-card.-board {text-align: center; display: flex; flex-wrap: wrap; justify-content: center;}
		.ui-card.-board .ui-item {width: 40%; margin-bottom: 32px;}
		.ui-card.-board .ui-item:first-child {width: 80%;}
		.ui-card.-board .ui-item .photo {width: 140px; height: 180px; margin:16px auto; display: block;}
		.ui-card.-board .ui-item .photo img {width: 100%; height:100%;}
		.project-info em {color: #999; font-size: 0.9em;}
		.inline-edit-field {padding: 1px 4px;}
		.inline-edit-field.-text {}
		.inline-edit-field.-fill {padding: 4px; margin-bottom:8px;}
		.inline-edit-field.-empty>span {color: #999;}
		.box.-cover {padding: 0 0 64px 0; text-align: center;}
		.box>h3 {text-align: center;}

		@media print {
			.module-project .box {box-shadow: none; border: none;}
		}
		</style>'
	);
	//$ret.=print_o($projectInfo,'$projectInfo');
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