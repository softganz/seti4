<?php
/**
* Project Action Join Register
* Created 2019-02-28
* Modify  2019-07-30
*
* @param Object $self
* @param Int $
* @return String
*/

$debug = true;

// FIXME : หลังจากเลือกชื่อเรียบร้อย หากแก้ไขชื่อเป็นคนอื่น จะท้ำให้ข้อมูลชื่อเดิมเปลี่ยนไป
function view_project_join_register_form($data, $options = '{}') {
	$defaults = '{debug: false, mode: "edit", accessPerson: false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$isFormEnable = in_array($options->mode, array('register','edit'));

	$isAdmin = user_access('access administrator pages,administer projects');
	$isAccessPerson = $options->accessPerson;

	//$ret .= '<header class="header -box"><h3>'.$formTitle.'</h3></header>';

	if ($data->regtype == 'Invite') $formTitle = 'แบบฟอร์มเชิญเข้าร่วมกิจกรรม';
	else if ($data->regtype == 'Walk In') $formTitle = 'แบบฟอร์มสมัครเข้าร่วมกิจกรรม ประเภท Walk In';
	else $formTitle = 'ลงทะเบียน';
	$formTitle .= $data->calendarTitle;

	$ui = new Ui();
	$ui->add('<a class="sg-action" href="'.url('project/join/'.$data->tpid.'/'.$data->calid.'/view/'.$data->psnid).'" data-rel="box"><i class="icon -material">search</i></a>');

	if ($options->isEdit) {
		$ui->add('<a class="sg-action" href="'.url('project/join/'.$data->tpid.'/'.$data->calid.'/edit/'.$data->psnid).'" data-rel="box"><i class="icon -material">edit</i></a>');
	}

	if ($data->dopid) {
		$ui->add('<a class="sg-action" href="'.url('project/join/'.$data->tpid.'/'.$data->calid.'/rcv/'.$data->dopid).'" data-rel="box"><i class="icon -material">attach_money</i></a>');
	}

	$ui->add('<a href="'.url('project/join/'.$data->tpid.'/'.$data->calid.'/view/'.$data->psnid).'" onclick="sgPrintPage(this.href);return false;"><i class="icon -material">print</i></a>');

	if (in_array($options->mode, array('view','edit'))) {
		$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>รายละเอียดผู้เข้าร่วม</h3><nav class="nav">'.$ui->build().'</nav></header>';
	}

	//$ret .= print_o($data, '$data');
	//$ret .= print_o($options,'$options');

	$provinceOptions = array();
	$ampurOptions = array();
	$tambonOptions = array();

	$stmt = 'SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->provid] = $rs->provname;

	if ($data->changwat) {
		$stmt = 'SELECT * FROM %co_district% WHERE `distid` LIKE :changwat ORDER BY CONVERT(`distname` USING tis620) ASC';
		foreach (mydb::select($stmt, ':changwat', $data->changwat.'%')->items as $rs) $ampurOptions[substr($rs->distid,2,2)] = $rs->distname;
	}

	if ($data->ampur) {
		$stmt = 'SELECT * FROM %co_subdistrict% WHERE `subdistid` LIKE :ampur ORDER BY CONVERT(`subdistname` USING tis620) ASC';
		foreach (mydb::select($stmt, ':ampur', $data->changwat.$data->ampur.'%')->items as $rs) $tambonOptions[substr($rs->subdistid,4,2)] = $rs->subdistname;
	}

	if (!is_array($data->birth)) {
		list($yy, $mm, $dd) = explode('-', $data->birth);
		unset($data->birth);
		$data->birth['date'] = $dd;
		$data->birth['month'] = $mm;
		$data->birth['year'] = $yy;
	}


	$form = new Form('reg', url('project/join/'.$data->tpid.'/'.$data->calid.'/registersave'), NULL, 'sg-form -register -'.$options->mode);

	$form->addConfig('title', $formTitle);
	$form->addData('checkValid', true);
	if ($data->psnid) {
		$form->addData('complete', 'closebox');
		$form->addData('rel', 'none');
	}

	if (!$isFormEnable) $form->addConfig('readonly', true);

	$form->addField('tpid', array('type' => 'hidden', 'value' => $data->tpid));
	$form->addField('psnid', array('type' => 'hidden', 'value' => $data->psnid));
	$form->addField('doid', array('type' => 'hidden', 'value' => $data->doid));
	$form->addField('calid', array('type' => 'hidden', 'value' => $data->calid));
	$form->addField('regtype', array('type' => 'hidden', 'value' => $data->regtype));

	if ($data->refcode) {
		if (!$isFormEnable) {
			$form->addText('<p class="-no-print"><em>*** กรุณาพิมพ์หน้านี้เก็บไว้เพื่อใช้ในการอ้างอิงในการลงทะเบียนเข้าร่วมงาน ***</em></p>');
			$linkUrl = url('project/join/'.$data->tpid.'/'.$data->calid.'/ref/'.$data->refcode);
			$qrcodeUrl=_DOMAIN.urlencode($linkUrl);

			$refText = '<div class="refcode">Ref. Code<br /><b style="font-size:1.4em;">'.$data->refcode.'</b>';

			$refText .= '<img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chl='.$qrcodeUrl.'&chs=160x160&choe=UTF-8&chld=L|2" alt="" style="display: block; margin:0 auto;"><br />';

			$refText .= '<span class="-hidden">'._DOMAIN.$linkUrl.'</span>';
			$refText .= '</div>';

			$form->addText($refText);
		}


		$form->addField('refcode', array('type' => 'hidden', 'value' => $data->refcode));
	}

	$form->addText('<section class="box -personal"><h3>ข้อมูลส่วนบุคคล</h3>');


	if ($isFormEnable) {
		$form->addField(
			'prename',
			array(
				'label' => 'คำนำหน้านาม',
				'type' => 'text',
				'require' => true,
				'value' => $data->prename,
			)
		);

		$form->addField(
			'firstname',
			array(
				'label' => 'ชื่อ',
				'type' => 'text',
				'class'=>$data->psnid?'-fill': ($data->registerBy == 'member' ? 'sg-autocomplete' : '').' -fill',
				'require' => true,
				'placeholder' => $isAdmin || $isAccessPerson ? 'ป้อนชื่อ นามสกุล หรือ เลข 13 หลัก เพื่อค้นหา' : NULL,
				'value'=>$data->firstname,
				'attr'=>array(
					'data-query' => $data->registerBy == 'member' ? url('project/api/person') : '',
					'data-altfld'=>'edit-reg-psnid',
					'data-callback'=>'projectJoinGetPerson',
				),
			)
		);

		$form->addField(
			'lastname',
			array(
				'label' => 'นามสกุล',
				'type' => 'text',
				'class' => '-fill',
				'require' => true,
				'value' => $data->lastname,
				'description' => $data->psnid ? 'ห้ามเปลี่ยนชื่อ - นามสกุล สามารถแก้ไข ชื่อ - นามสกุล ได้' : NULL,
			)
		);
	} else {
		$form->addText('<b><big>ชื่อ : '.$data->prename.' '.$data->firstname.' '.$data->lastname.'</b></big>');
	}

	$form->addField(
		'cid',
		array(
			'label' => 'เลขประจำตัวบัตรประชาชน',
			'type' => 'text',
			'require' => i()->ok ? false : true,
			'maxlength' => 13,
			'value' => $data->cid,
		)
	);

	$form->addField(
		'sex',
		array(
			'label' => 'เพศ:',
			'type' => 'radio',
			'require' => true,
			'display' => $isFormEnable ? NULL : 'inline',
			'options' => array('ชาย' => 'ชาย', 'หญิง' => 'หญิง'),
			'value' => $data->sex,
		)
	);

	$form->addField(
		'religion',
		array(
			'label' => 'ศาสนา:',
			'type' => 'radio',
			'display' => $isFormEnable ? NULL : 'inline',
			'options' => array('1' => 'พุทธ', '3' =>'คริสต์', '2' => 'อิสลาม', '5' => 'อื่น ๆ'),
			'value' => $data->religion,
		)
	);

	$form->addField(
		'birth',
		array(
			'type' => 'date',
			'label' => 'วันเกิด:',
			'year' => (object) array('range' => '-10,80,DESC','type' => 'BC'),
			'value' => (object) array(
				'date' => $data->birth['date'],
				'month' => $data->birth['month'],
				'year' => $data->birth['year']
			),
			'posttext' => ' อายุ <span id="age">'.($data->birth['year'] ? date('Y') - $data->birth['year'] : '??').'</span> ปี',
		)
	);

	if ($isFormEnable) {
		$form->addField(
			'address',
			array(
				'label' => 'ที่อยู่ปัจจุบัน (บ้านเลขที่ อาคาร ซอย ถนน หมู่ที่)',
				'type' => 'text',
				'class' => 'sg-address -fill',
				'require' => true,
				'value' => $data->house ? $data->house.($data->village ? ' ม.'.$data->village : '') : $data->address,
				'attr'=>array('data-callback'=>'projectJoinAddress'),
			)
		);

		$form->addField(
			'changwat',
			array(
				'label' => 'จังหวัด:',
				'type' => 'select',
				'class' => 'sg-changwat -fill',
				'require' => true,
				'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
				'value' => $data->changwat,
				'containerclass' => '-inlineblock',
			)
		);

		$form->addField(
			'ampur',
			array(
				'label' => 'อำเภอ:',
				'type' => 'select',
				'class' => 'sg-ampur -fill',
				'require' => true,
				'options' => array('' => '== เลือกอำเภอ ==') + $ampurOptions,
				'containerclass' => '-inlineblock',
				'value' => $data->ampur,
			)
		);

		$form->addField(
			'tambon',
			array(
				'label' => 'ตำบล:',
				'type' => 'select',
				'class' => 'sg-tambon -fill',
				'require' => true,
				'options' => array('' => '== เลือกตำบล ==') + $tambonOptions,
				'value' => $data->tambon,
				'containerclass' => '-inlineblock',
			)
		);

		$form->addField(
			'zip',
			array(
				'label' => 'รหัสไปรษณีย์',
				'type' => 'text',
				'maxlength' => 5,
				'value' => $data->zip,
			)
		);
	} else {
		$form->addText('<b>ที่อยู่</b> '.$data->address);
	}

	$form->addField(
		'phone',
		array(
			'label' => 'โทรศัพท์มือถือ',
			'type' => 'text',
			'require' => i()->ok ? false : true,
			'value' => $data->phone,
		)
	);

	$form->addText('</section>');

	$form->addText('<section class="box"><h3>ข้อมูลการเข้าร่วมงาน</h3>');

	$form->addField(
		'jointype',
		array(
			'label' => 'สถานะการเข้าร่วมงาน: <span>(สามารถเลือกได้มากกว่า 1 ข้อ)</span>',
			'type' => 'checkbox',
			'multiple' => true,
			'display' => $isFormEnable ? NULL : 'inline',
			'options' => array(
				'Attendee' => 'ผู้เข้าร่วมงาน',
				'Exhibition' => 'ผู้จัดนิทรรศการ',
				'Conference' => 'ผู้เข้าประชุมวิชาการ',
				'Speaker' => 'ผู้นำเสนอบนเวที/วิทยากร',
				'Staff' => 'คณะทำงาน',
				//'Expert' => 'วิทยากร',
			),
			'value' => $data->jointype,
			'container' => '{class: "-fieldset"}',
		)
	);

	$form->addText('<div class="form-item -fieldset"><label>ข้อมูลหน่วยงาน</label>');

	$form->addField(
		'orgtype',
		array(
			'label' => 'หน่วยงาน:',
			'type' => 'radio',
			'display' => $isFormEnable ? NULL : 'inline',
			'options' => array(
				'1' => 'รัฐ/ส่วนราชการ',
				'6' => 'ภาคเอกชน',
				'4' => 'ภาคประชาสังคม/ภาคประชาชน',
			),
			'value' => $data->orgtype,
		)
	);

	$form->addField(
		'orgname',
		array(
			'label' => 'ชื่อหน่วยงาน',
			'type' => 'text',
			'class' => '-fill',
			'value' => $data->orgname,
		)
	);


	$form->addField(
		'position',
		array(
			'label' => 'ตำแหน่ง',
			'type' => 'text',
			'class' => '-fill',
			'value' => $data->position,
		)
	);

	$form->addText('</div>');

	$joinGroup = object_merge((object) array('-1'=>'== เลือกการเบิกจ่าย ==') ,json_decode($data->paidgroup));

	//$ret .= print_o($joinGroup,'$joinGroup');

	if (count((array) $joinGroup) > 1) {
		$form->addField(
			'joingroup',
			array(
				'label' => 'การเบิกจ่าย ค่าใช้จ่ายในการเดินทางและที่พัก:',
				'type' => 'select',
				'class' => '-fill',
				'require' => true,
				'options' => $joinGroup,
				'value' => $data->joingroup,
				'container' => '{class: "-fieldset"}',
			)
		);
	}

	$form->addField(
		'tripby',
		array(
			'label' => 'ท่านเดินทางมาร่วมงานอย่างไร:',
			'type' => 'checkbox',
			'multiple' => true,
			//'require' => true,
			'options' => array(
				'รถยนต์ส่วนตัว' => 'รถยนต์ส่วนตัว: ทะเบียนรถ '
					.'<input class="form-text" type="text" name="reg[carregist]" placeholder="ทะเบียนรถ" value="'.htmlspecialchars($data->carregist).'" /> '
					.'<input class="form-text" type="text" name="reg[carregprov]" placeholder="จังหวัด" value="'.htmlspecialchars($data->carregprov).'" />',
				'รถโดยสารประจำทาง' => 'รถโดยสารประจำทาง: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[busprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->busprice).'" /> บาท',
				'รถรับจ้าง' => 'รถรับจ้าง/แท็กซี่: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[taxiprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->taxiprice).'" /> บาท',
				'เครื่องบิน' => 'เครื่องบิน: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[airprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->airprice).'" /> บาท<br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;'
					.'เที่ยวไปสายการบิน '
					.'<input class="form-text" type="text" name="reg[airgoline]" placeholder="สายการบิน" value="'.htmlspecialchars($data->airgoline).'" /> '
					.'จาก <input class="form-text" type="text" name="reg[airgofrom]" placeholder="จาก" value="'.htmlspecialchars($data->airgofrom).'" /> '
					.'ถึง <input class="form-text" type="text" name="reg[airgoto]" placeholder="ถึง" value="'.htmlspecialchars($data->airgoto).'" /><br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;เที่ยวกลับสายการบิน '
					.'<input class="form-text" type="text" name="reg[airretline]" placeholder="สายการบิน" value="'.htmlspecialchars($data->airretline).'" /> '
					.'จาก <input class="form-text" type="text" name="reg[airretfrom]" placeholder="จาก" value="'.htmlspecialchars($data->airretfrom).'" /> '
					.'ถึง <input class="form-text" type="text" name="reg[airretto]" placeholder="ถึง" value="'.htmlspecialchars($data->airretto).'" />'
					,
				'รถไฟ' => 'รถไฟ: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[trainprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->trainprice).'" /> บาท',
				'ค่าเดินทางเหมาจ่ายในพื้นที่' => 'ค่าเดินทางเหมาจ่ายในพื้นที่: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[localprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->localprice).'" /> บาท',
				'อื่นๆ' => 'อื่น ๆ: '
					.'<input class="form-text" type="text" name="reg[tripotherby]" placeholder="ระบุ เช่น ค่าเรือ" value="'.htmlspecialchars($data->tripotherby).'" /> '
					.' <input class="form-text -money -tripprice" type="text" name="reg[tripotherprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->tripotherprice).'" /> บาท',
				'ไม่เบิกค่าเดินทาง' => 'ไม่เบิกค่าเดินทาง',
			),
			'value' => $data->tripByList ? $data->tripByList : $data->tripby,
			'posttext' => '<span class="-hidden">รวมค่าเดินทาง <b><span id="trip-total-price">'.number_format($data->tripTotalPrice,2).'</span></b> บาท</span>',
			'container' => '{class: "-fieldset"}',
		)
	);

	$form->addField(
		'tripgroup',
		array(
			'label' => 'ท่านเดินทางร่วมกับบุคคลอื่น:',
			'type' => 'checkbox',
			'multiple' => true,
			//'require' => true,
			'options' => array(
				'เดินทางร่วม' => 'รถยนต์บุคคลอื่น: '
					.'<input class="form-text" type="text" name="reg[carwithname]" placeholder="ระบุชื่อเจ้าของรถ" value="'.htmlspecialchars($data->carwithname).'" />',
				'รถตู้เช่า' => 'รถตู้เช่า: '
					.'<input class="form-text -money -tripprice" type="text" name="reg[rentprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->rentprice).'" /> บาท<br />'
					.'&nbsp;&nbsp;&nbsp;&nbsp;'
					.'ทะเบียนรถ '
					.'<input class="form-text" type="text" name="reg[rentregist]" placeholder="ทะเบียนรถ จังหวัด" value="'.htmlspecialchars($data->rentregist).'" /> '
					.'รายชื่อผู้โดยสาร <input class="form-text" type="text" name="reg[rentpassenger]" size="40" placeholder="รายชื่อผู้โดยสารที่เดินทางร่วมกัน" value="'.htmlspecialchars($data->rentpassenger).'" />',
			),
			'value' => $data->tripByList ? $data->tripByList : $data->tripby,
			'container' => '{class: "-fieldset"}',
		)
	);

	$hotelList = array(
		/*
		'โรงแรมไดมอนด์พลาซ่า',
		'โรงแรมนิภาการ์เด้น',
		'โรงแรมบรรจงบุรี ',
		'โรงแรมเคพาร์ค ',
		'โรงแรมแก้วสมุยรีสอร์ท',
		'โรงแรมร้อยเกาะ',
		'โรงแรมมาร์ลิน',
		'โรงแรม S.22',
		'โรงแรมราชธานี',
		'โรงแรม เอเวอร์กรีนสวีท',
		'โรงแรมปริ้นเซส ปาร์ค (มุสลิม)',
		'โรงแรม บี เจ',
		'โรงแรมมายเพลส สุราษฎร์ธานี',
		'โรงแรมแกรนด์ ธารา',
		'โรงแรมตาปี',
		'โรงแรมไทยรุ่งเรือง',
		'โรงแรม เดอะ วัน',
		'โรงแรมสุราษฏร์ เกสเฮ้าส์',
		'โรงแรมบรรโฮเทล',
		'โรงแรมวังใต้',
		'เดอะ เซนทริโน เซอร์วิส เรสซิเดนซ์',
		'เหรียญชัย เพลส',
		'โรงแรมสยามธานี',
		'เดอะริชเรสซิเดนซ์',
		'โรงแรมซีบีดี โฮเท็ล',
		'โรงแรมเอส ทารา แกรนด์',
		'โรงแรม ออร์คิด ริเวอร์วิว',
		'โรงแรมโคเมท',
		'โรงแรมริเวอร์เนเจอร์',
		*/
		'เซ็นทารา หาดใหญ่',
		'เดอะ เบด หาดใหญ่',
		'เดอะ เบลส เรสซิเด้นท์',
		'เดอะ คัลเลอร์',
		'เดอะ รีเจนซี่ โฮเต็ล หาดใหญ่',
		'เดอะสมาร์ท หาดใหญ่ The Smart Hotel Hat Yai',
		'เรดแพลนเนต หาดใหญ่ Red Planet Hat Yai',
		'เอเชีย แกรนด์ หาดใหญ่',
		'เอเชี่ยน',
		'แกรนด์พลาซา',
		'โกลเด้นคราวน์ แกรนด์',
		'โฆษิต หาดใหญ่',
		'ไดมอนด์ พลาซ่า',
		'ไอดู บูทีค สวีท',
		'ไฮซีซั่น',
		'คริสตัล หาดใหญ่ (หน้าเซ็นทรัลเฟสติวัล)',
		'คิส การ์เด้นโฮม ชิค โฮเต็ล',
		'จีทูโฮเทล หาดใหญ่',
		'ชัชฏา เพลส',
		'ทองมณี อพาร์ตเมนท์',
		'ทีอาร์ร็อคฮิลล์ (TR Rock hill)',
		'นิว ซีซั่น',
		'พี เรสซิเดนซ์',
		'พีบี แกรนด์',
		'ยู หาดใหญ่ โฮเต็ล',
		'รถไฟ หาดใหญ่',
		'ลลิตา บูติก โฮเต็ล (Lalita Boutique Hotel)',
		'ลา พอส หาดใหญ่',
		'ลีวาน่า (Leevana Hotel)',
		'วินสตาร์หาดใหญ่ Winstar Hotel',
		'วิสดอม เรสซิเดนซ์ (Wisdom Residence)',
		'วี โอเชียน พาเลซ',
		'วี.แอล. หาดใหญ่',
		'สยาม เซ็นเตอร์',
		'สิงห์โกลเด้น เพลส',
		'หรรษาเจบี',
		'หาดใหญ่ เมอริเดียน',
		'หาดใหญ่ โกลเด้น คราวน์',
		'หาดใหญ่ กรีนวิว',
		'หาดใหญ่ พาเลส โฮเต็ล',
		'หาดใหญ่ พาราไดซ์ โฮเทล แอนด์ รีสอร์ท',
		'หาดใหญ่ ยูธ โฮสเทล',
		'หาดใหญ่ ฮอลิเดย์',
		'อยู่ หาดใหญ่',
		'อยู่ดี ลีฟวิ่ง เพลส',
		'อาโลฮา หาดใหญ่',
		'ฮ๊อปอินน์ หาดใหญ่ (Hop Inn Hat Yai)',
	);


	$selectHotel .= '<select id="edit-reg-hotelname" class="form-select" name="reg[hotelname]"><option value="">== เลือกโรงแรม ==</option>';
	foreach ($hotelList as $value) {
		$selectHotel .= '<option value="'.$value.'" '.($data->hotelname == $value ? 'selected="selected"' : '').'>'.$value.'</option>';
	}
	$selectHotel .= '</select>';

	$form->addField(
		'rest',
		array(
			'label' => 'รายละเอียดที่พัก:',
			'type' => 'radio',
			'options' => array(
				'โรงแรม' => '&nbsp;<b class="-hidden">ที่พัก (โรงแรม):</b> '
					.'<span style="display:inline-block; vertical-align:top;">'.$selectHotel.'<br />'
					.'<input class="form-text -fill" type="text" name="reg[hotelothername]" placeholder="ระบุชื่อโรงแรม" value="'.(in_array($data->hotelname, $hotelList) ? '' : htmlspecialchars($data->hotelname)).'" style="margin-top:4px;" />'
					.'</span>'
					.' ราคาคืนละ <input id="edit-reg-hotelprice" class="form-text -money" type="text" name="reg[hotelprice]" size="8" maxlength="7" placeholder="0.00" value="'.htmlspecialchars($data->hotelprice).'" /> บาท '
					.' จำนวน <input id="edit-reg-hotelnight" class="form-text -numeric" type="text" name="reg[hotelnight]" size="2" maxlength="1" placeholder="0" value="'.htmlspecialchars($data->hotelnight).'" /> คืน<br />'
					,
				'พักเดี่ยว' => 'พักเดี่ยว (เบิกได้ไม่เกิน 600 บาท/คน/คืน)',
				'พักคู่' => 'พักคู่: '
					. 'ชื่อคู่พัก '
					. '<input id="edit-reg-hotelwithpsnid" type="hidden" name="reg[hotelwithpsnid]" value="'.$data->hotelwithpsnid.'" />'
					. '<input id="edit-reg-hotelmate" class="form-text'.($isAdmin || $isAccessPerson ? ' sg-autocomplete' : '').'" type="text" name="reg[hotelmate]" placeholder="ระบุ ชื่อ" value="'.htmlspecialchars($data->hotelmate).'" data-query="'.url('project/api/join/person/'.$data->tpid.'/'.$data->calid).'" data-altfld="edit-reg-hotelwithpsnid" />',
				//'ไม่เบิกค่าที่พัก' => 'ไม่ประสงค์เบิกค่าที่พัก',
			),
			'require' => i()->ok ? false : true,
			'value' => $data->rest,
			'posttext' => '<label class="option"><input class="form-checkbox" type="checkbox" name="reg[withdrawrest]" value="-1" '.($data->withdrawrest < 0 ? 'checked="checked"' : '').' />ไม่ประสงค์เบิกค่าที่พัก</label>',
			'container' => '{class: "-fieldset"}',
		)
	);

	$form->addField(
		'foodtype',
		array(
			'label' => 'ท่านประสงค์รับประทานอาหาร:',
			'type' => 'radio',
			'require' => $data->joinInfo->options->selectFood ? true : false,
			'display' => $isFormEnable ? NULL : 'inline',
			'options' => array(
				'มุสลิม' => 'มุสลิม',
				'มังสวิรัติ' => 'มังสวิรัติ/เจ',
				'ทั่วไป' => 'ทั่วไป (ไทยพุทธ)',
			),
			'value' => $data->foodtype,
			'container' => '{class: "-fieldset"}',
		)
	);


	$form->addField(
		'remark',
		array(
			'type' => 'textarea',
			'label' => 'หมายเหตุ:',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->remark,
			'placeholder' => 'ระบุข้อมูลรายละเอียดเพิ่มเติม',
		)
	);

	$form->addText('</section>');



	//if ($isFormEnable)
		//$form->addText('<p class="notify -no-print">หลังจากลงทะเบียนเรียบร้อย กรุณาพิมพ์ข้อมูลการลงทะเบียนเก็บไว้เพื่อเป็นข้อมูลในการอ้างอิงในภายหลัง</p>');
	//else
		//$form->addText('<p class="notify">ท่านสามารถใช้โทรศัพท์สแกน QR CODE ด้านบน หรือบันทึกเก็บลิงก์สำหรับการแก้ไขข้อมูลการลงทะเบียนในภายหลัง <a href="'._DOMAIN.$linkUrl.'">'._DOMAIN.$linkUrl.'</a></p>');

	if ($data->registerrem) $form->addText('<div class="footnote">'.sg_text2html($data->registerrem).'</div>');



	if ($isFormEnable) {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'class' => $data->psnid ? '' : '-disabled', // Disable button on create new
				'value' => '<i class="icon -save -white"></i><span>'.($data->doid ? 'บันทึกแก้ไขการลงทะเบียน' : 'ยืนยันการลงทะเบียน').'</span>',
				'container' => '{class: "-sg-text-right -no-print"}',
				//'pretext' => '<a class="btn -link -cancel" href="'.url('project/join/'.$data->tpid.'/'.$data->calid).'"><i class="icon -back -gray"></i><span>ยกเลิก</span></a>',
				//'pretext' => '<a class="btn -link" href="javascript:window.print()"><i class="icon -print"></i><span>พิมพ์ใบลงทะเบียน</span></a>',
			)
		);
	} else {
		//$form->addText('<a class="-no-print" href="'.url('project/join/'.$data->tpid.'/'.$data->calid).'"><i class="icon -back"></i><span>กลับหน้าลงทะเบียน</span></a>');
	}
	$ret .= $form->build();

	if ($data->registerByName) $ret .= '<p class="-no-print">ลงทะเบียนโดย '.$data->registerByName.' เมื่อ '.sg_date($data->created, 'ว ดด ปปปป H:i').' น.</p>';

	//$ret .= print_o($data, '$data');

	$ret .= '<script type="text/javascript">
		//$(".btn.-primary").prop("disabled", true)

		$("form.-readonly input, form.-readonly select").attr("disabled", true)

		$(document).on("change","#edit-reg-birth-year",function(){
			console.log("Age change")
			var age=new Date().getFullYear()-$(this).val();
			$("#age").text(age);
		});

		$(document).on("keyup","#edit-reg-firstname",function(){
			//console.log($(this).val())
			if ($("#edit-reg-psnid").val() == "") {
				if ($(this).val() == "")
					$(".btn.-primary").addClass("-disabled")
				else
					$(".btn.-primary").removeClass("-disabled")
			}
		});

		$(document).on("keyup", ".form-text.-tripprice", function() {
			console.log("Trip Change")
			var sum = 0;
			$(".form-text.-tripprice").each(function(){
				sum += +$(this).val();
			});
			$("#trip-total-price").text(isNaN(sum) ? "???" : sum.toFixed(2));
		});

		function projectJoinGetPerson($this, ui) {
			//console.log(ui.item)

			//console.log("DOID="+$("#edit-reg-doid").val())

			if ($("#edit-reg-doid").val()) {
				projectJoinSetPerson($this, ui)
				//$(".btn.-primary").prop("disabled", false)
			} else {
				var url = "'.url('project/join/'.$data->tpid.'/'.$data->calid.'/check').'/" + ui.item.value
				//console.log("Check ",ui.item.value,url)
				$.get(url, function(data) {
					//console.log(data)
					if (data.ok) {
						notify(data.fullname + " ได้สมัครเข้าร่วมกิจกรรมเรียบร้อยแล้ว (" + data.joingroup + ")")
						$("#edit-reg-psnid").val("")
						$("#edit-reg-prename").val(ui.item.preName)
						$("#edit-reg-firstname").val(ui.item.firstName)
						$("#edit-reg-lastname").val(ui.item.lastName)
						$(".btn.-primary").addClass("-disabled")
					} else {
						projectJoinSetPerson($this, ui)
						$(".btn.-primary").removeClass("-disabled")
					}
				}, "json")
			}
		}

		function projectJoinSetPerson($this, ui) {
			$("#edit-reg-prename").val(ui.item.preName)
			$("#edit-reg-firstname").val(ui.item.firstName)
			$("#edit-reg-lastname").val(ui.item.lastName)
			$("#edit-reg-address").val(ui.item.address)
			$("#edit-reg-cid").val(ui.item.cid)
			$("#edit-reg-zip").val(ui.item.zip)
			$("#edit-reg-phone").val(ui.item.phone)
			$("#edit-reg-religion").val(ui.item.religion)
			if (ui.item.sex == "ชาย") $("#edit-reg-sex-1").prop("checked", true)
			else if (ui.item.sex == "หญิง") $("#edit-reg-sex-2").prop("checked", true)
			projectJoinAddress($this, ui)
		}

		function projectJoinAddress($this, ui) {
			//console.log(ui.item)

			var $ampur = $("#edit-reg-ampur")
			var $tambon = $("#edit-reg-tambon")


			$ampur[0].options.length = 1;
			$tambon[0].options.length = 1;

			$("#edit-reg-changwat").val(ui.item.changwatId)

			$ampur.append(
				$("<option></option>")
				.text(ui.item.ampurName)
				.val(ui.item.ampurId)
			).val(ui.item.ampurId);

			$tambon.append(
				$("<option></option>")
				.text(ui.item.tambonName)
				.val(ui.item.tambonId)
			).val(ui.item.tambonId);

			//22 ตำบลคลองแห อำเภอหาดใหญ่ จังหวัดสงขลา
		}

		$("input[name=\'reg[rest]\']").change(function() {
			var checkValue = $(this).val()
			console.log("Change "+ checkValue)
			if (checkValue != "พักคู่") {
				$("#edit-reg-hotelwithpsnid").val("")
				$("#edit-reg-hotelmate").val("")
			}
			if (checkValue == "ไม่พัก") {
				console.log("Clear Hotel")
				$("#edit-reg-hotelname").val("")
				$("#edit-reg-hotelprice").val("")
				$("#edit-reg-hotelnight").val("")
			}
		})
		</script>';

	$ret .= '<style type="text/css">
		.btn.-cancel {margin-right:32px;}
		.form.-register {position: relative;}
		.form.-register .refcode {margin: 0 16px; text-align: center; border: 1px #ccc solid; padding:8px;}
		.form.-register .title {text-align: center;}
		.form.-register.-view .form-required {display: none;}
		.form.-register.-view label>span {display: none;}
		.form.-register.-view label {display: inline-block;}
		.form.-register.-view .form-text { display: inline-block; width: auto; box-shadow: none; border: none; color: #000;}
		.form.-register.-view .form-select {border: none; font-size: 1.0em; width: auto; box-shadow: none; color: #000;}
		.form.-register.-view .form-textarea.-fill {box-shadow: none; border: none;}
		.form.-register.-view .description {display: none;}
		.footnote {color:red;}

		@media (min-width:43.75em) {    /* 800/16 = 43.75 */
		.form.-register .refcode {width: 200px; margin: 0 0 0 auto; text-align: center; border: 1px #ccc solid; padding:8px; position: absolute; background: #fff; top: 0; right: 0px;}
		}

		@media print {
			.page.-content {padding:0; margin:0;}
			.form.-register .refcode {width: 200px; margin: 0 0 0 auto; text-align: center; border: none; padding:0; position: absolute; background: transparent; top: 28px; right: 8px; line-height: 1.2em; padding:0;}
			.form-item.-edit-reg-birth {display: none;}
			.form.-register.-view {font-size: 0.85em; line-height: 1.2em;}
			.form.-register.-view .form-text {padding: 0;}

			.form.-register.-view .form-select {font-size: 0.6em;}
			.form.-register.-view .form-textarea {box-shadow: none;}
			.form.-register.-view .title {font-size: 1.4em; text-align: center; color: #333;}
			.module-project .box {box-shadow: none; border: 1px #ccc solid; margin-bottom: 8px;}
			.module-project .box.-personal {margin-right: 250px;}
			.module-project .box h3 {color: #000; padding: 8px 0; font-size: 1.4em; margin:0;}
			.refcode {top: 54px;}
			.footnote {font-size: 0.7em; line-height: 1.1em; color: #333;}
		}
		</style>';
	return $ret;
}
?>