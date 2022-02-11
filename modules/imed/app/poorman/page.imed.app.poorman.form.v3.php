<?php
function imed_app_poorman_form_v3($self,$qtref) {
	R::View('imed.toolbar',$self,'คนยากลำบาก','app.poorman');
	if (!i()->ok) return R::View('signform');

		//$result->msg.=print_o($_FILES,'$_FILES');
		//return json_encode((array)$result);

	$data=(object)post('data');

	//$ret.='post(save)='.post('save');
	//$ret.=print_o($data,'$data');

	if ( empty($qtref) && ($data->psnid || ($data->{'qt:PSNL.FULLNAME'} && $data->{'qt:PSNL.CID'})) )  {
		// Create new qt
		$data->appsrc='Android';
		$result=R::Model('imed.poorman.save',$data);
		//$ret.='Create new';
		//$ret.=print_o($data,'$data');
		//$ret.=print_o($result,'$result');
		location('imed/app/poorman/form/v3/'.$result->qtref);
		return $ret;
	} else if ($qtref) {
		// Save data
		if (post('save')) {
			$result=R::Model('imed.poorman.save',$data);
			location('imed/app/poorman/list');
			return $ret;
		} else if ($_FILES) {
			$photoKey=key($_FILES);
			//$ret.=print_o($_FILES,'$_FILES');
			// Para : $data->seq from post
			$data->prename=$photoKey.'_'.$data->psnid.'_'.date('ymdhis').'_';
			$data->tagname=$photoKey;
			$data->deleteurl = url('imed/api/visit/'.$data->psnid.'/photo.delete/'.$data->seq.'?f=');
			$uploadResult=R::Model('imed.visit.photo.upload',$_FILES[$photoKey],$data);
			$ret.=$uploadResult['link'];

			//$ret.='<div class="notify">Upload photo underconstruction @'.date('H:i:s').'</div><img src="/library/img/dialog-warning.png" width="100%" />';
			//$ret.='<br />'.$_FILES['photocommuneneed']['name'].'<br />'.$_FILES['photocommuneneed']['tmp_name'];
			//$ret.=print_o($uploadResult,'$uploadResult');
			return $ret;
		} else if (post('data')) {
			if ($data->{'qt:PSNL.PRENAME'}=='อื่นๆ') $data->{'qt:PSNL.PRENAME'}=$data->{'prename-other'};
			$result=R::Model('imed.poorman.save',$data);
			if (post('publish')) {
				mydb::query('UPDATE %qtmast% SET `qtstatus`=:qtstatus WHERE `qtref`=:qtref LIMIT 1',':qtref',$qtref,':qtstatus',_WAITING);
			}
			$ret=json_encode((array)$result);
			return $ret;
		}

		$stmt='SELECT
						q.*
					FROM %qtmast% q
					WHERE q.`qtref`=:qtref LIMIT 1';
		$qtmast=mydb::select($stmt,':qtref',$qtref);
		$qtmast->qtrefno=$qtmast->qtref.'/'.(sg_date($qtmast->qtdate,'Y')+543);

		$stmt='SELECT * FROM %qttran% WHERE `qtref`=:qtref ORDER BY `qtid` ASC; -- {key:"part"}';
		$qttran=mydb::select($stmt,':qtref',$qtref)->items;
		list($qttran['PSNL.BIRTH.YEAR'],$qttran['PSNL.BIRTH.MONTH'],$qttran['PSNL.BIRTH.DATE'])=explode('-',$qttran['PSNL.BIRTH']->value);

		$photoList=mydb::select('SELECT f.*,s.`pid` `psnid` FROM %imed_files% f LEFT JOIN %imed_service% s USING(`seq`) WHERE f.`seq`=:seq',':seq',$qtmast->seq)->items;
		//$ret.=print_o($photoList,'$photoList');

		/*
		$data->person=R::Model('imed.patient.get',$data->psnid);
		$data->date['date']=sg_date($data->person->info->birth,'d');
		$data->date['month']=sg_date($data->person->info->birth,'m');
		$data->date['year']=sg_date($data->person->info->birth,'Y');
		*/
	}


	$ret.='<h3 class="header -sub">แบบสำรวจข้อมูลประชาชนในภาวะยากลำบากและกลุ่มเปราะบางทางสังคม</h3>';

	if (!$qtref) {
		$form=new Form('data',url('imed/app/poorman/form/v3'),'imed-poorman-form','sg-form imed-poorman-form');
		$form->addData('checkValid',true);
		$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$qtmast->psnid));

		$form->addField('h1','<div style="margin:0 0 16px auto;padding:8px;border:1px #ccc solid;background:#f0f0f0;text-align:right;width:17em;white-space:nowrap;">ลำดับที่การเก็บข้อมูล <input id="qtrefno" class="form-text" type="text" style="width:6em;text-align:center;" value="'.SG\getFirst($qtmast->qtrefno,'????/'.(date('Y')+543)).'" readonly="readonly" /></div>');

		$form->addField('h2','<h3>ข้อมูลบุคคลในแบบสำรวจ</h3>');

		$form->addField('qt:PSNL.PRENAME',
							array(
								'type'=>'select',
								'label'=>'คำนำหน้าชื่อ :',
								'class'=>'-fill',
								'options'=>array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว'),
								'value'=>$qttran['PSNL.PRENAME']->value,
								'posttext'=>'<input class="form-text -fill -hidden" type="text" name="prename-other" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
								)
							);

		$form->addField('qt:PSNL.FULLNAME',
							array(
								'type'=>'text',
								'label'=>'ชื่อ - นามสกุล',
								'class'=>$qtmast->psnid?'-fill':'sg-autocomplete -fill',
								'require'=>true,
								'placeholder'=>'ป้อนชื่อ นามสกุล หรือ เลข 13 หลัก',
								'value'=>$qttran['PSNL.FULLNAME']->value,
								'attr'=>array(
													'data-query'=>url('imed/api/person'),
													'data-altfld'=>'psnid',
													),
								)
							);

		$form->addField('qt:PSNL.CID',
							array(
								'type'=>'text',
								'label'=>'เลขที่บัตรประจำตัวประชาชน',
								'class'=>'-fill',
								'require'=>true,
								'maxlength'=>13,
								'value'=>$qttran['PSNL.CID']->value,
								)
							);

		$form->addField('save',
							array(
								'type'=>'button',
								'name'=>'save',
								'items'=>array(
													'type'=>'submit',
													'class'=>'-primary',
													'value'=>'<i class="icon -addbig -white"></i><span>เพิ่มแบบสำรวจ</span>',
													),
								)
							);

		$ret.=$form->build();
		return $ret;
	}


	$isEdit=(i()->ok && $qtmast->uid==i()->uid) || user_access('administer imeds');

	if (!$isEdit) return '<p class="notify">ขออภัย : ข้อมูลนี้ไม่ได้อยู่ในความรับผิดชอบของท่าน</p>';

	R::View('imed.toolbar',$self,'คนยากลำบาก : '.$qttran['PSNL.FULLNAME']->value,'app.poorman');

	$form=new Form('data',url('imed/app/poorman/form/v3/'.$qtref),'imed-poorman-form','imed-poorman-form');
	$form->addConfig('enctype','multipart/form-data');
	$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$qtmast->psnid));
	$form->addField('qtref',array('type'=>'hidden','id'=>'qtref','value'=>$qtmast->qtref));
	$form->addField('seq',array('type'=>'hidden','id'=>'seq','value'=>$qtmast->seq));

	$form->addField('h1','<div style="margin:0 0 16px auto;padding:8px;border:1px #ccc solid;background:#f0f0f0;text-align:right;width:17em;white-space:nowrap;">ลำดับที่การเก็บข้อมูล <input id="qtrefno" class="form-text" type="text" style="width:6em;text-align:center;" value="'.SG\getFirst($qtmast->qtrefno,'????/'.(date('Y')+543)).'" readonly="readonly" /></div>');

	$form->addField('h2','<h3>ข้อมูลทั่วไป</h3>');

	$optionsName=array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว');
	if ($qttran['PSNL.PRENAME']->value && !in_array($qttran['PSNL.PRENAME']->value, $optionsName)) {
		$optionsName[$qttran['PSNL.PRENAME']->value]=$qttran['PSNL.PRENAME']->value;
	}
	$optionsName['อื่นๆ']='อื่นๆ';
	$form->addField('qt:PSNL.PRENAME',
						array(
							'type'=>'select',
							'label'=>'คำนำหน้าชื่อ :',
							'class'=>'-fill',
							'options'=>$optionsName,
							'value'=>$qttran['PSNL.PRENAME']->value,
							'posttext'=>'<input class="form-text -fill -hidden" type="text" name="data[prename-other]" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
							)
						);

	$form->addField('qt:PSNL.FULLNAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อ - นามสกุล',
							'class'=>$qtmast->psnid?'-fill':'sg-autocomplete -fill',
							'placeholder'=>'ป้อนชื่อ นามสกุล หรือ เลข 13 หลัก',
							'value'=>$qttran['PSNL.FULLNAME']->value,
							'attr'=>array(
												'data-query'=>url('imed/api/person'),
												'data-altfld'=>'psnid',
												'data-callback'=>'imedAppPoormanGetPerson',
												),
							)
						);

	$form->addField('qt:PSNL.CID',
						array(
							'type'=>'text',
							'label'=>'เลขที่บัตรประจำตัวประชาชน',
							'class'=>'-fill',
							'maxlength'=>13,
							'value'=>$qttran['PSNL.CID']->value,
							)
						);

	$form->addField('qt:PSNL.NOIDCARD',
						array(
							'type'=>'text',
							'label'=>'กรณีไม่มีบัตรประจำตัวประชาชนเนื่องจาก',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.NOIDCARD']->value,
							)
						);

	$form->addField('birth',
						array(
							'type'=>'date',
							'label'=>'วันเกิด (วันที่/เดือน/ปี พ.ศ.) :',
							'year'=>(object)array('range'=>'-110,110','type'=>'BC'),
							'value'=>(object)array(
													'date'=>$qttran['PSNL.BIRTH.DATE'],
													'month'=>$qttran['PSNL.BIRTH.MONTH'],
													'year'=>$qttran['PSNL.BIRTH.YEAR']
													),
							'posttext'=>' อายุ <span id="age">'.($qttran['PSNL.BIRTH.YEAR']?date('Y')-$qttran['PSNL.BIRTH.YEAR']:'??').'</span> ปี',
							)
						);

	$form->addField('qt:PSNL.SEX',
						array(
							'type'=>'radio',
							'label'=>'เพศ :',
							'options'=>array('ชาย'=>'ชาย','หญิง'=>'หญิง'),
							'value'=>$qttran['PSNL.SEX']->value,
							)
						);

	$form->addField('qt:PSNL.RACE',
						array(
							'type'=>'text',
							'label'=>'เชื้อชาติ',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.RACE']->value,
							)
						);

	$form->addField('qt:PSNL.NATION',
						array(
							'type'=>'text',
							'label'=>'สัญชาติ',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.NATION']->value,
							)
						);

	$form->addField('qt:PSNL.RELIGION',
						array(
							'type'=>'text',
							'label'=>'ศาสนา',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.RELIGION']->value,
							)
						);

	$form->addField('qt:PSNL.MARRIED',
						array(
							'type'=>'radio',
							'label'=>'สถานภาพ :',
							'options'=>array('โสด'=>'โสด','สมรสอยู่ด้วยกัน'=>'สมรสอยู่ด้วยกัน','สมรสแยกกันอยู่'=>'สมรสแยกกันอยู่','หย่าร้าง'=>'หย่าร้าง','ไม่ได้สมรสแต่อยู่ด้วยกัน'=>'ไม่ได้สมรสแต่อยู่ด้วยกัน','หม้าย (คู่สมรสเสียชีวิต)'=>'หม้าย (คู่สมรสเสียชีวิต)'),
							'value'=>$qttran['PSNL.MARRIED']->value,
							)
						);

	$form->addField('t2_s','<p><b>ที่อยู่ตามทะเบียนบ้าน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField('qt:PSNL.REGIST.PLACENAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.REGIST.PLACENAME']->value,
							)
						);

	$form->addField('qt:PSNL.REGIST.HOUSEID',
						array(
							'type'=>'text',
							'label'=>'รหัสประจำบ้าน',
							'class'=>'-fill',
							'maxlength'=>10,
							'value'=>$qttran['PSNL.REGIST.HOUSEID']->value,
							)
						);

	$form->addField('qt:PSNL.REGIST.ADDRESS',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ตามทะเบียนบ้าน',
							'class'=>'sg-address -fill',
							'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
							'value'=>$qttran['PSNL.REGIST.ADDRESS']->value,
							'attr'=>array('data-altfld'=>'PSNL_REGIST_AREACODE')
							)
						);

	$form->addField('qt:PSNL.REGIST.AREACODE',
						array(
							'type'=>'hidden',
							'id'=>'PSNL_REGIST_AREACODE',
							'value'=>$qttran['PSNL.REGIST.AREACODE']->value,
							)
						);

	$form->addField('qt:PSNL.REGIST.ZIP',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							'maxlength'=>5,
							'value'=>$qttran['PSNL.REGIST.ZIP']->value,
							)
						);

	$form->addField('qt:PSNL.REGIST.PHONE',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์บ้าน',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['PSNL.REGIST.PHONE']->value,
							)
						);

	$form->addField('qt:PSNL.REGIST.MOBILE',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['PSNL.REGIST.MOBILE']->value,
							)
						);

	$form->addField('t2_e','</div>');

	$form->addField('t3_s','<p><b>ที่อยู่ปัจจุบัน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField('qt:PSNL.HOME.STATUS',
						array(
							'type'=>'radio',
							'label'=>'สถานะของที่พักอาศัย :',
							'options'=>array(1=>'บ้านตนเอง','อาศัยผู้อื่นอยู่','บ้านเช่า','อยู่กับผู้จ้าง','ไม่มีที่อยู่เป็นหลักแหล่ง'),
							'value'=>$qttran['PSNL.HOME.STATUS']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.NOTSAMEADDRESS',
						array(
							'type'=>'checkbox',
							'label'=>'ที่อยู่ปัจจุบันกับที่อยู่ตามทะเบียนบ้าน',
							'options'=>array(1=>'ที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน'),
							'value'=>$qttran['PSNL.HOME.NOTSAMEADDRESS']->value,

							)
						);

	$form->addField('t1_s','<div id="imed-poorman-form-regishome" class="'.($qttran['PSNL.HOME.NOTSAMEADDRESS']->value?'':'hidden').'">กรณีที่อยู่ปัจจุบันไม่เป็นที่เดียวกับที่อยู่ตามทะเบียนบ้าน (ระบุรายละเอียดเพิ่มเติมด้านล่าง)');

	$form->addField('qt:PSNL.HOME.PLACENAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อสถานที่',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.HOME.PLACENAME']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.HOUSEID',
						array(
							'type'=>'text',
							'label'=>'รหัสประจำบ้าน',
							'class'=>'-fill',
							'maxlength'=>10,
							'value'=>$qttran['PSNL.HOME.HOUSEID']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.ADDRESS',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ปัจจุบัน',
							'class'=>'sg-address -fill',
							'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
							'value'=>$qttran['PSNL.HOME.ADDRESS']->value,
							'attr'=>array('data-altfld'=>'PSNL_HOME_AREACODE')
							)
						);

	$form->addField('qt:PSNL.HOME.AREACODE',
						array(
							'type'=>'hidden',
							'id'=>'PSNL_HOME_AREACODE',
							'value'=>$qttran['PSNL.HOME.AREACODE']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.ZIP',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							'maxlength'=>5,
							'value'=>$qttran['PSNL.HOME.ZIP']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.PHONE',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์บ้าน',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['PSNL.HOME.PHONE']->value,
							)
						);

	$form->addField('qt:PSNL.HOME.MOBILE',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['PSNL.HOME.MOBILE']->value,
							)
						);

	$form->addField('t1_e','</div>');
	$form->addField('t3_e','</div>');

	$form->addField('qt:PSNL.EDUCA',
						array(
							'type'=>'radio',
							'label'=>'ระดับการศึกษา :',
							'options'=>array(1=>'ไม่ได้รับการศึกษา/ไม่จบชั้นประถมศึกษาตอนต้น','ประถมศึกษาตอนต้น','ประถมศึกษาตอนปลาย','มัธยมศึกษาตอนต้น','มัธยมศึกษาตอนปลาย','ปวช./อนุปริญญา','ปริญญาตรี','สูงกว่าปริญญาตรี'),
							'value'=>$qttran['PSNL.EDUCA']->value,
							)
						);

	$form->addField('qt:PSNL.OCCUPA',
						array(
							'type'=>'radio',
							'label'=>'อาชีพ :',
							'options'=>array(1=>'ไม่มีอาชีพ/ว่างงาน','นักเรียน/นักศึกษา','ค้าขาย/ธุรกิจส่วนตัว','ภิกษุ/สามเณร/แม่ชี','เกษตรกร (ทำไร่/นา/สวน/สัตว์เลี้ยง/ประมง)','ข้าราชการ/พนักงานของรัฐ','พนักงานรัฐวิสาหกิจ','พนักงานบริษัท','รับจ้าง',99=>'อื่น ๆ : ระบุรายละเอียดอาชีพด้านล่าง'),
							'value'=>$qttran['PSNL.OCCUPA']->value,
							)
						);

	$form->addField('qt:ECON.OCCUPY.DETAIL',
						array(
							'type'=>'text',
							'label'=>'รายละเอียดอาชีพ',
							'class'=>'-fill',
							'value'=>$qttran['ECON.OCCUPY.DETAIL']->value,
							)
						);

	$form->addField('qt:ECON.INCOME.MONTHLY.AMT',
						array(
							'type'=>'text',
							'label'=>'รายได้เฉลี่ยต่อเดือน',
							'class'=>'',
							'posttext'=>' บาท',
							'value'=>$qttran['ECON.INCOME.MONTHLY.AMT']->value,
							)
						);

	$form->addField('qt:ECON.INCOME.FROM',
						array(
							'type'=>'text',
							'label'=>'ที่มาของรายได้',
							'class'=>'-fill',
							'value'=>$qttran['ECON.INCOME.FROM']->value,
							)
						);

	$form->addField('qt:PSNL.INFO.BYTYPE',
						array(
							'type'=>'radio',
							'label'=>'ผู้ให้ข้อมูล :',
							'options'=>array(1=>'เป็นผู้ให้เอง','ผู้อื่นให้'),
							'value'=>$qttran['PSNL.INFO.BYTYPE']->value,
							)
						);

	$form->addField('qt:PSNL.INFO.BYNAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อผู้ให้ข้อมูล',
							'containerclass'=>'-last',
							'class'=>'-fill',
							'value'=>$qttran['PSNL.INFO.BYNAME']->value,
							)
						);

	$form->addField('h3','<h3>สภาวะความยากลำบาก และ เปราะบางทางสังคม</h3>');

	$form->addField('qt:POOR.TYPE.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'1. ประเภทของสภาวะความยากลำบากและกลุ่มเปราะบางทางสังคม :',
							'containerclass'=>'-bigheader -first',
							'options'=>array(1=>'1. คนไร้บ้าน','2. คนไร้สัญชาติ','3. ผู้สูงอายุที่ถูกทอดทิ้ง','4. ผู้ติดเชื้อ','5. ผู้ป่วยติดบ้าน/ติดเตียง','6. อดีตผู้ต้องขัง','7. คนพิการ','8. ผู้ได้รับผลกระทบจากสถานการณ์','9. เด็กกำพร้า (ทั่วไป/สถานการณ์)'),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.TYPE.LIST.',$qttran),
							)
						);

	$form->addField('poor1photo','ภาพถ่ายบุคคลพร้อมคำบรรยาย (ถ้ามี) <ul class="card -photo">'.__imed_app_poorman_form_photo($photoList,'poorperson').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorperson" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');


	$form->addField('qt:POOR.CAUSE.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'2. สาเหตุของความยากลำบาก :',
							'containerclass'=>'-bigheader',
							'options'=>array(1=>'1. ยากจน / รายได้น้อย','2. มีหนี้สิน','3. ตกงาน / ไม่มีงานทำ / ไม่มีอาชีพ','4. ขาดผู้อุปการะ','5. ขาดความรู้ที่จะประกอบอาชีพ','6. ปัญหาครอบครัว','7. ไม่มีที่อยู่อาศัย / ไม่มีที่ดินทำกิน','8. ถูกชักจูงโดยคนรู้จัก / เพื่อน','9. ถูกบังคับ / ล่อลวง / แสวงหาผลประโยชน์','10. ไม่มีสถานะทางทะเบียนราษฎร์','11. ขาดโอกาสทางการศึกษาตามเกณฑ์','12. เจ็บป่วยเรื้อรัง','13. ช่วยเหลือตนเองไม่ได้ในชีวิตประจำวัน',99=>'14. อื่น ๆ : ระบุรายละเอียดด้านล่าง'),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.CAUSE.LIST.',$qttran),
							)
						);

	$form->addField('qt:POOR.CAUSE.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดสาเหตุความยากลำบาก',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$qttran['POOR.CAUSE.DETAIL']->value,
							)
						);

	$disabledList=array(1=>'ทางการเห็น','ทางการได้ยินหรือสื่อความหมาย','ทางการเคลื่อนไหวหรือทางร่างกาย','ทางจิตใจหรือพฤติกรรม','ทางสติปัญญา','ทางการเรียนรู้','ทางออทิสติก');
	foreach ($disabledList as $key => $value) {
		$disabledType.='<label class="option" style="display:block;">'
									.'<input name="data[qt:POOR.HEALTH.DISABLED.'.$key.']" value="'.$key.'" '
									.($qttran['POOR.HEALTH.DISABLED.'.$key]->value==$key?'checked="checked"':'')
									.' class="form-checkbox" type="checkbox" /> '.$value
									.'</label>';
	}


	$form->addField('qt:POOR.HEALTH.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'3. สถานะทางสุขภาพในปัจจุบัน :',
							'containerclass'=>'-bigheader',
							'options'=>array(
													1=>'1. ปกติ',
													'2. ผู้สูงอายุ',
													'3. เจ็บป่วย',
													'4. พิการ<br />'.$disabledType,
													99=>'5. อื่น ๆ'
													),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.HEALTH.LIST.',$qttran),
							)
						);

	$form->addField('qt:POOR.HEALTH.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดสถานะทางสุขภาพในปัจจุบัน',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$qttran['POOR.HEALTH.DETAIL']->value,
							)
						);

	$form->addField('qt:POOR.NEED.GOV.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'4. สิ่งที่ต้องการให้รัฐช่วยเหลือ',
							'containerclass'=>'-bigheader',
							'options'=>array(1=>'1. เข้าสถานสงเคราะห์','2. กลับภูมิลำเนา','3. ฝึกอาชีพ','4. หางานทำ','5. ที่พักชั่วคราว','6. เงินทุนประกอบอาชีพ','7. เงินสงเคราะห์ช่วยเหลือ','8. รักษาพยาบาล','9. ทำบัตรประชาชน',99=>'10. อื่น ๆ'),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.NEED.GOV.LIST.',$qttran),
							)
						);

	$form->addField('qt:POOR.NEED.GOV.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดสิ่งที่ต้องการให้รัฐช่วยเหลือ',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$qttran['POOR.NEED.GOV.DETAIL']->value,
							)
						);

	$form->addField('poor4photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี)  <ul class="card -photo">'.__imed_app_poorman_form_photo($photoList,'poorgvneed').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorgvneed" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');

	$form->addField('qt:POOR.HELP.ORG.YES',
						array(
							'type'=>'radio',
							'label'=>'5. เคยได้รับความช่วยเหลือจากหน่วยงานใดบ้าง',
							'containerclass'=>'-bigheader',
							'options'=>array(1=>'ไม่เคยได้รับ','เคยได้รับความช่วยเหลือเป็น'),
							'value'=>$qttran['POOR.HELP.ORG.YES']->value,
							)
						);

	$form->addField('qt:POOR.HELP.ORG.LIST.',
						array(
							'type'=>'checkbox',
							'options'=>array(1=>'บริการ','เงิน','สิ่งของ'),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.HELP.ORG.LIST.',$qttran),
							)
						);

	$form->addField('qt:POOR.HELP.ORG.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียดความช่วยเหลือที่ได้รับ',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$qttran['POOR.HELP.GOV.DETAIL']->value,
							)
						);

	$form->addField('qt:POOR.HELP.ORG.YEAR',
						array(
							'type'=>'text',
							'label'=>'เมื่อปี',
							'class'=>'-fill',
							'value'=>$qttran['POOR.HELP.GOV.YEAR']->value,
							)
						);

	$form->addField('qt:POOR.HELP.ORG.NAME',
						array(
							'type'=>'text',
							'label'=>'จากหน่วยงาน',
							'class'=>'-fill',
							'value'=>$qttran['POOR.HELP.GOV.NAME']->value,
							)
						);

	$form->addField('qt:POOR.NEED.COMMUNITY.LIST.',
						array(
							'type'=>'checkbox',
							'label'=>'6. สิ่งที่ต้องการให้ชุมชนหรือองค์กรท้องถิ่นช่วยเหลือ',
							'containerclass'=>'-bigheader',
							'options'=>array(1=>'1. ซ่อมแซมที่อยู่อาศัย','2. อาหาร','3. ฝึกอาชีพ','4. ให้งานทำ','5. ของใช้ในชีวิต','6. เครื่องนุ่งห่ม','7. เงินสงเคราะห์ช่วยเหลือ','8. วัสดุเพื่อการรักษาพยาบาล',99=>'9. อื่นๆ'),
							'separate'=>true,
							'value'=>__imed_app_poorman_form_tranvalue('POOR.NEED.COMMUNITY.LIST.',$qttran),
							)
						);

	$form->addField('qt:POOR.NEED.COMMUNITY.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'ระบุรายละเอียดสิ่งที่ต้องการด้านล่าง',
							'class'=>'-fill',
							'rows'=>5,
							'value'=>$qttran['POOR.NEED.COMMUNITY.DETAIL']->value,
							)
						);

	$form->addField('poor6photo','ภาพถ่ายพร้อมคำบรรยาย (ถ้ามี)  <ul class="card -photo">'.__imed_app_poorman_form_photo($photoList,'poorcmneed').'<li class="card-item -upload"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="poorcmneed" class="inline-upload" accept="image/*;capture=camcorder" /></span></li></ul>');


	$form->addField('poor7.h',
						array(
							'type'=>'textfield',
							'label'=>'7. สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
							'containerclass'=>'-bigheader',
							)
						);

	$form->addField('qt:POOR.ECON.INCOME.YES',
						array(
							'type'=>'radio',
							'label'=>'รายรับ :',
							'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
							'value'=>$qttran['POOR.ECON.INCOME.YES']->value,
							)
						);

	$form->addField('qt:POOR.ECON.EXPENSE.YES',
						array(
							'type'=>'radio',
							'label'=>'รายจ่าย :',
							'options'=>array(1=>'ไม่เพียงพอ',2=>'เพียงพอ'),
							'value'=>$qttran['POOR.ECON.EXPENSE.YES']->value,
							)
						);

	$form->addField('qt:POOR.ECON.LOAN.YES',
						array(
							'type'=>'radio',
							'label'=>'หนี้สิน :',
							'options'=>array(1=>'ไม่มี',2=>'มีมากเกินรายได้',3=>'มีพอกับรายได้'),
							'value'=>$qttran['POOR.ECON.LOAN.YES']->value,
							)
						);

	$form->addField('qt:POOR.ECON.DETAIL',
						array(
							'type'=>'textarea',
							'label'=>'รายละเอียด สภาพเศรษฐกิจ รายรับ รายจ่าย หนี้สิน',
							'class'=>'-fill',
							'rows'=>5,
							'value'=>$qttran['POOR.ECON.DETAIL']->value,
							)
						);

	$form->addField('qt:POOR.PSNL.CARETAKER.HEADER',
						array(
							'type'=>'textfield',
							'label'=>'8. ผู้ดูแลหรือผู้ปกครอง (ในกรณีไม่สามารถดูแลตนเองได้)',
							'containerclass'=>'-bigheader',
							)
						);
	$form->addField('qt:POOR.PSNL.CARETAKER.NAME',
						array(
							'type'=>'text',
							'label'=>'ชื่อผู้ดูแลหรือผู้ปกครอง',
							'class'=>'-fill',
							'value'=>$qttran['POOR.PSNL.CARETAKER.NAME']->value,
							)
						);
	$form->addField('qt:POOR.PSNL.CARETAKER.RELATION',
						array(
							'type'=>'text',
							'label'=>'ความสัมพันธ์',
							'class'=>'-fill',
							'value'=>$qttran['POOR.PSNL.CARETAKER.RELATION']->value,
							)
						);
	$form->addField('qt:POOR.PSNL.CARETAKER.ADDRESS',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ผู้ดูแล',
							'class'=>'sg-address -fill',
							'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
							'value'=>$qttran['POOR.PSNL.CARETAKER.ADDRESS']->value,
							)
						);
	$form->addField('qt:POOR.PSNL.CARETAKER.ZIP',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							'maxlength'=>5,
							'value'=>$qttran['POOR.PSNL.CARETAKER.ZIP']->value,
							)
						);

	$form->addField('qt:POOR.PSNL.CARETAKER.PHONE',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์บ้าน',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['POOR.PSNL.CARETAKER.PHONE']->value,
							)
						);

	$form->addField('qt:POOR.PSNL.CARETAKER.MOBILE',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['POOR.PSNL.CARETAKER.MOBILE']->value,
							)
						);





	$form->addField('qt:POOR.PSNL.HISTORY.MORE',
						array(
							'type'=>'textarea',
							'label'=>'9. ประวัติ(เพิ่มเติม)',
							'containerclass'=>'-bigheader',
							'class'=>'-fill',
							'rows'=>5,
							'value'=>$qttran['POOR.PSNL.HISTORY.MORE']->value,
							)
						);

	$form->addField('qt:POOR.PSNL.HISTORY.FAMILY',
						array(
							'type'=>'textarea',
							'label'=>'10. ประวัติครอบครัว',
							'containerclass'=>'-bigheader',
							'class'=>'-fill',
							'rows'=>5,
							'value'=>$qttran['POOR.PSNL.HISTORY.FAMILY']->value,
							)
						);

	$form->addField('collectname',
						array(
							'type'=>'text',
							'label'=>'ผู้จัดเก็บข้อมูล',
							'containerclass'=>'-bigheader',
							'class'=>'-fill',
							'value'=>$qtmast->collectname,
							)
						);

	$form->addField('qtdate',
						array(
							'type'=>'text',
							'label'=>'วันที่เก็บข้อมูล',
							'class'=>'sg-datepicker -fill',
							'readonly'=>true,
							'value'=>sg_date(SG\getFirst($qtmast->qtdate,date('Y-m-d')),'d/m/Y'),
							'attr'=>array('data-max-date'=>date('d/m/Y')),
							)
						);
	$form->addField('qt:COLLECTOR.PHONE',
						array(
							'type'=>'text',
							'label'=>'เบอร์โทรศัพท์',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['COLLECTOR.PHONE']->value,
							)
						);
	$form->addField('qt:COLLECTOR.IDLINE',
						array(
							'type'=>'text',
							'label'=>'ID LINE',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$qttran['COLLECTOR.IDLINE']->value,
							)
						);

	$form->addField(
						'publish',
						array(
							'type'=>'checkbox',
							'label'=>'แจ้งสถานะการบันทึกแบบสอบถาม:',
							'name'=>'publish',
							'options'=>array(_WAITING=>'บันทึกแบบสอบถามเสร็จสมบูรณ์แล้ว'),
							'value'=>$qtmast->qtstatus,
							)
						);

	$form->addField('save',
						array(
							'type'=>'button',
							'name'=>'save',
							'items'=>array(
												'type'=>'submit',
												'class'=>'-primary',
												'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
												),
							)
						);

	$ret.=$form->build();

	//$ret.=print_o($qtmast,'$qtmast');
	//$ret.=print_o($qttran,'$qttran');




	$head='<style type="text/css">
	.imed-poorman-form {margin:0;padding:8px;}
	.imed-poorman-form h3 {padding:16px 16px;background:green; color:#fff;}
	.imed-poorman-form .form-item {clear:both}
	.imed-poorman-form .option {padding: 4px 0 4px 16px;}
	.imed-poorman-form .option:hover {background-color:#eee;}
	.card-item.-upload {box-shadow:none;}
	.form-item.-bigheader {margin-top:48px;}
	.form-item.-bigheader.-first {margin-top:0;}
	.form-item.-last {margin-bottom:48px;}
	.card.-photo>li {position:relative;}
	.card.-photo .photoitem {height:140px;}
	.card.-photo .iconset {position:absolute;top:0;right:0;}
	.card.-photo .iconset .icon {background-color:red;border-radius:50%;}
	.form-item.-edit-data-qt-POOR-HELP-ORG-LIST {padding-left:32px;}
	</style>';




	$head.='<script type="text/javascript">
	var lastid=0;
	$(document).on("change","#edit-data-qt-PSNL-PRENAME",function(){
		console.log($(this).val())
		if ($(this).val()=="อื่นๆ") {
			$(this).next().show().focus()
		} else {
			$(this).next().hide()
		}
	});


	$(document).on("click","input[name=\'data[qt:PSNL.HOME.NOTSAMEADDRESS]\']",function() {
		if($(this).is(":checked")) {
			$("#imed-poorman-form-regishome").show();
		} else {
			$("#imed-poorman-form-regishome").hide();
		}
	});

	$(document).on("submit", "#imed-poorman-form", function() {
		console.log("Form "+$(this).attr("id")+" submit");
		return true;
	})
	.on("keydown", "#imed-poorman-form input:text", function(event) {
		var n = $("input:text").length
		if(event.keyCode == 13) {
			event.preventDefault()
			var nextIndex = $("input:text,textarea").index(this) + 1
			if(nextIndex < n)
				$("input:text,textarea")[nextIndex].focus()
			return false
		}
	});

	var isFormChangeWaitng=true;
	$(document).on("change","#imed-poorman-form input, #imed-poorman-form textarea",function(){
		//if (!isFormChangeWaitng) return;
		var $this=$(this);
		var addPara="";
		isFormChangeWaitng=false;

		if ($this.attr("type")=="file") return false;
		console.log("Update change of "+$this.attr("type")+" "+$this.attr("name"));
		if ($this.attr("type")=="checkbox") {
			console.log("Check value = "+$this.val())
			$this.data("old",$this.val());
			if ($this.is(":checked")) {
				;
			} else {
				addPara="&"+$this.attr("name")+"=";
				//$this.val("");
			}
		}
		//console.log("ID "+$this.attr("id")+" change.");
		var $form=$this.closest("form");
		var para=$form.serialize()+addPara;
		//console.log(para)
		$.post($form.attr("action"),para, function(data) {
			console.log("qtref="+data.qtref+" psnid="+data.psnid);
			console.log("Save result "+data.msg);

			$("#qtref").val(data.qtref);
			$("#psnid").val(data.psnid);
			$("#qtrefno").val(data.qtrefno);
			//if ($this.attr("type")=="checkbox") $this.val($this.data("old"));
			isFormChangeWaitng=true;
		},
		"json");
		return false;
	});


	$(document).on("change","#edit-data-birth-year",function(){
		console.log("Age change")
		var age=new Date().getFullYear()-$(this).val();
		$("#age").text(age);
	});


	function imedAppPoormanGetPerson($this,ui) {
		console.log("Callback "+ui.item.value);
		$("#psnid").val(ui.item.value);
	}


	$(document).on("change","#imed-poorman-form .inline-upload",function(){
		var $this=$(this);
		var $form=$this.closest("form");
		var id="photo-"+(++lastid);
		console.log("Poorman inline upload file start and show result "+id)
		console.log("Inline action "+$form.attr("action"));
		var insertElement="<li id=\""+id+"\"><img class=\"photoitem\" src=\"/library/img/loading.gif\" /></li>";
		$this.closest("li").before(insertElement);
		$form.ajaxSubmit({
			success: function(data) {
				console.log("Inline upload Save result :: "+data);
				$("#"+id).html(data);
				$this.val("");
				$this.replaceWith($this.clone(true));
			}
		})
		//.submit();
		return false;
	});
	</script>';
	head($head);
	return $ret;
}

function __imed_app_poorman_form_tranvalue($key,$qttran) {
	$values=array();
	foreach ($qttran as $k => $item) {
		if (preg_match('/^'.$key.'[0-9]/',$k)) $values[]=$item->value;
	}
	return $values;
}

function __imed_app_poorman_form_photo($photoList,$tagname) {
	$isEdit=true;
	foreach ($photoList as $rs) {
		if ($rs->tagname!=$tagname) continue;
		$photoUrl=imed_model::upload_photo($rs->file);
		$ret.='<li>';
		//$ret.=$rs->file;
		$ret.='<a class="sg-action" href="'.$photoUrl.'" data-rel="img"><img src="'.$photoUrl.'" height="140" /></a>';
		if ($isEdit) {
			$ui=new Ui('span','iconset -hover');
			$ui->add('<a class="sg-action -no-print" href="'.url('imed/api/visit/'.$rs->psnid.'/photo.delete/'.$rs->seq,array('f'=>$rs->fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -delete"></i></a>');
			$ret.=$ui->build();
		}
		$ret.='</li>'._NL;
	}
	return $ret;
}
?>