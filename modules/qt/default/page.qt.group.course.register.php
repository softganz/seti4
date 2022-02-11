<?php
function qt_group_course_register($self,$uid=NULL) {
	R::View('toolbar',$self,'ข้อมูลทั่วไป','qt.course');

	if ($uid) return R::Page('qt.group.course.profile',$self,$uid);

	if (!i()->ok) {
		R::View('toolbar',$self,'เข้าสู่ระบบสมาชิก','qt.course');
		return '<p class="notify">สำหรับผู้ที่เป็นสมาชิกเว็บอยู่แล้ว กรุณาเข้าสู่ระบบสมาชิกก่อนลงทะเบียน<br />หากยังไม่ได้เป็นสมาชิก กรุณา<a href="{url:user/register}">สมัครสมาชิกเว็บ</a>ให้เรียบร้อยก่อนลงทะเบียน</p>'.R::View('signform');
	}

	$post=(object)post();

	$networkOptions = array(
		1=>'ชุมชนท้องถิ่นน่าอยู่',
		'ความมั่นทางอาหาร',
		'ท่องเที่ยวชุมชน',
		'ปัจจัยเสี่ยง เหล้า บุหรี่ ยาเสพติด',
		'การส่งเสริมกิจกรรมทางกาย',
		'การจัดการฐานทรัพยากรธรรมชาติและสิ่งแวดล้อม',
		'ความมั่นคงทางมนุษย์ เด็กและเยาวชน ผู้สูงอายุ ผู้ด้อยโอกาส ผู้พิการ',
		'กองทุนตำบล',
		99=>'อื่น ๆ'
	);

	if ($post->fullname || ($post->name && $post->lname)) {
		if ($post->fullname) list($post->name,$post->lname)=sg::explode_name(' ',$post->fullname);

		$addrList=SG\explode_address($post->address,$post->areacode);
		$post->house=$addrList['house'];
		$post->village=$addrList['village'];
		$post->tambon=$addrList['tambonCode'];
		$post->ampur=$addrList['ampurCode'];
		$post->changwat=$addrList['changwatCode'];

		$post->rhouse=$addrList['house'];
		$post->rvillage=$addrList['village'];
		$post->rtambon=$addrList['tambonCode'];
		$post->rampur=$addrList['ampurCode'];
		$post->rchangwat=$addrList['changwatCode'];

		//$ret.=print_o($post,'$post');
		//$ret.=print_o($addrList,'$addrList');


		if (empty($post->name) || empty($post->lname)) {
			$error='กรุณาป้อน ชื่อ และ นามสกุล หรือ เว้นวรรค 1 เคาะระหว่างชื่อและนามสกุล';
		} else if ($post->name && $post->lname &&
			$dupid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE name=:name AND lname=:lname LIMIT 1',$post)->psnid) {
			$error='ชื่อ <b>"'.$post->name.' '.$post->lname.'"</b> มีอยู่ในฐานข้อมูลแล้ว หากต้องการยืนยันชื่อ-นามสกุลนี้จริง กรุณาคลิกปุ่มยืนยันด้านล่าง';
		}
		if ($error) {
			$ret.=message('error',$error);
		} else {
			//$ret.='<p>Prepare to save person</p>';
			$post->uid=i()->uid;
			$post->created=date('U');

			$stmt='INSERT INTO %db_person% (
				  `uid`, `cid`, `prename`, `name`, `lname`
				, `sex`, `educate`
				, `house`, `village`, `tambon`, `ampur`, `changwat`
				, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
				, `phone`, `email`, `website`
				, `created`
				) VALUES (
				  :uid, :cid, :prename, :name, :lname
				, :sex, :educate
				, :house, :village, :tambon, :ampur, :changwat
				, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
				, :phone, :email, :website
				, :created
				)';
			mydb::query($stmt,$post);
			//$ret.='<p>'.mydb()->_query.'</p>';

			if (!mydb()->_error) {
				$psnid=$post->psnid=mydb()->insert_id;
				$post->joindate=date('Y-m-d H:i:s');

				$stmt='INSERT INTO %person_group%
					(`groupname`,`psnid`,`uid`,`joindate`)
					VALUES
					("assessor",:psnid,:uid,:joindate)
					';
				mydb::query($stmt,$post);
				//$ret.='<p>'.mydb()->_query.'</p>';

				$stmt='INSERT INTO %person_tr%
					(`psnid`,`uid`,`tagname`,`num1`,`num2`,`created`)
					VALUES
					(:psnid,:uid,"info",:age,:yearexp,:created)';
				mydb::query($stmt,$post);

				foreach ($post->network as $item) {
					$post->netid=$item;
					$post->netname=$networkOptions[$item];
					$post->othnet=NULL;
					if ($item==99) $post->othnet=$post->othernetwork;
					$stmt='INSERT INTO %person_tr%
						(`psnid`,`uid`,`tagname`,`num1`,`detail1`,`detail2`,`created`)
						VALUES
						(:psnid,:uid,"network",:netid,:netname,:othnet,:created)';
					mydb::query($stmt,$post);
					//$ret.='<p>'.mydb()->_query.'</p>';
				}

				location('qt/group/course/take');
			}

			//$ret.=print_o($post,'$post');
			return $ret;
		}
	}

	if (i()->ok) {
		$isRegister=mydb::select('SELECT `psnid` FROM %person_group% WHERE `groupname`="assessor" AND `uid`=:uid LIMIT 1',':uid',i()->uid)->psnid;
		if ($isRegister) location('qt/group/course/take');
	}

	$form=new Form('assessor',url(q()),'project-assessor-add','sg-form');
	$form->addData('checkValid',true);
	//$form->addData('rel','#main');

	$form->addField(
		'cid',
		array(
			'type'=>'hidden',
			'name'=>'cid',
			'label'=>'หมายเลขประจำตัวประชาชน 13 หลัก',
			'class'=>'-fill',
			'maxlength'=>13,
			'require'=>false,
			'placeholder'=>'หมายเลข 13 หลัก'
			,'value'=>htmlspecialchars($post->cid)
		)
	);

	$form->addField(
		'prename',
		array(
			'type'=>'text',
			'name'=>'prename',
			'label'=>'คำนำหน้าชื่อ',
			'class'=>'-fill',
			'maxlength'=>20,
			'require'=>true,
			'placeholder'=>'คำนำหน้าชื่อ'
			,'value'=>htmlspecialchars($post->prename)
		)
	);
	/*
	$form->addField(
		'fullname',
		array(
			'type'=>'text',
			'name'=>'fullname',
			'label'=>'ชื่อ - นามสกุล',
			'class'=>'-fill',
			'maxlength'=>100,
			'require'=>true,
			'placeholder'=>'ชื่อ นามสกุล',
			'value'=>htmlspecialchars($post->fullname),
			'description'=>'กรุณาป้อนขื่อ - นามสกุล โดยเคาะเว้นวรรคจำนวน 1 ครั้งระหว่างชื่อกับนามสกุล <!-- <a class="info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank">i</a>-->'
		)
	);
	*/

	$form->addField(
		'name',
		array(
			'type'=>'text',
			'name'=>'name',
			'label'=>'ชื่อ',
			'class'=>'-fill',
			'maxlength'=>100,
			'require'=>true,
			'placeholder'=>'ชื่อจริง',
			'value'=>htmlspecialchars($post->name),
		)
	);

	$form->addField(
		'lname',
		array(
			'type'=>'text',
			'name'=>'lname',
			'label'=>'นามสกุล',
			'class'=>'-fill',
			'maxlength'=>100,
			'require'=>true,
			'placeholder'=>'นามสกุล',
			'value'=>htmlspecialchars($post->lname),
		)
	);

	$form->addField(
		'sex',
		array(
			'type'=>'radio',
			'name'=>'sex',
			'label'=>'เพศ',
			'require'=>true,
			'options'=>array('ชาย'=>'ชาย','หญิง'=>'หญิง'),
			'value'=>htmlspecialchars($post->sex),
		)
	);

	$form->addField(
		'age',
		array(
			'type'=>'text',
			'name'=>'age',
			'label'=>'อายุ',
			'require'=>true,
			'size'=>5,
			'value'=>htmlspecialchars($post->age),
			'posttext'=>' ปี',
		)
	);

	$form->addField(
		'address',
		array(
			'type'=>'text',
			'name'=>'address',
			'label'=>'ที่อยู่',
			'class'=>'sg-address -fill',
			'maxlength'=>100,
			'require'=>true,
			'attr'=>array('data-altfld'=>'edit-areacode'),
			'placeholder'=>'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกตำบลจากรายการที่แสดง',
			'value'=>htmlspecialchars($post->address)
		)
	);

	$provOptions=array(''=>'==เลือกจังหวัด==');
	foreach (mydb::select('SELECT `provid`,`provname` FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC; -- {key:"provid"}')->items AS $item) $provOptions[$item->provid]=$item->provname;

	$form->addField(
		'changwat',
		array(
			'label' => 'หรือ เลือกจังหวัด/อำเภอ/ตำบล จากช่องเลือก :',
			'name' => 'changwat',
			'type' => 'select',
			'class' => 'sg-changwat',
			'options' => $provOptions,
			'posttext' => '<select name="ampur" id="ampur" class="form-select sg-ampur -hidden">'
				. '<option value="">==เลือกอำเภอ==</option>'
				. '</select>'
				. '<select name="tambon" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode">'
				. '<option value="">==เลือกตำบล==</option>'
				. '</select>'

		)
	);

	/*
	$provStr='<div class="form-item"><label>เลือกจังหวัด/อำเภอ/ตำบล/หมู่บ้าน จากช่องเลือก</label><select name="changwat" id="changwat" class="form-select sg-changwat">'._NL;
	$provStr.='<option value="">==เลือกจังหวัด==</option>'._NL;
	foreach ($provOptions as $k=>$v) {
		$provStr.='<option value="'.$k.'">'.$v.'</option>'._NL;
	}
	$provStr.='</select>'._NL;
	$provStr.='<select name="ampur" id="ampur" class="form-select sg-ampur -hidden"><option value="">==เลือกอำเภอ==</option></select>'._NL;
	$provStr.='<select name="tambon" id="tambon" class="form-select sg-tambon -hidden" data-altfld="#edit-areacode"><option value="">==เลือกตำบล==</option></select>'._NL;
	$provStr.='</div>'._NL;

	$form->addField('province',$provStr);
	*/

	$form->addField(
		'areacode',
		array(
			'type'=>'hidden',
			'label'=>'เลือกตำบลในที่อยู่',
			'value'=>$post->areacode,
			'name'=>'areacode',
			'require'=>true
		)
	);

	$form->addField(
		'phone',
		array(
			'type'=>'text',
			'name'=>'phone',
			'label'=>'โทรศัพท์',
			'class'=>'-fill',
			'maxlength'=>50,
			'require'=>true,
			'placeholder'=>'โทรศัพท์'
			,'value'=>htmlspecialchars($post->phone)
		)
	);

	$form->addField(
		'email',
		array(
			'type'=>'text',
			'name'=>'email',
			'label'=>'อีเมล์',
			'class'=>'-fill',
			'maxlength'=>50,
			'require'=>true,
			'placeholder'=>'อีเมล์'
			,'value'=>htmlspecialchars($post->email)
		)
	);

	$form->addField(
		'website',
		array(
			'type'=>'text',
			'name'=>'website',
			'label'=>'เฟซบุ๊ค',
			'class'=>'-fill',
			'maxlength'=>100,
			'require'=>true,
			'placeholder'=>'เฟซบุ๊ค'
			,'value'=>htmlspecialchars($post->website)
		)
	);

	$form->addField(
		'educate',
		array(
			'type'=>'radio',
			'name'=>'educate',
			'label'=>'การศึกษา:',
			'require'=>true,
			'options'=>array('x'=>'ต่ำกว่า ป.ตรี',6=>'ป.ตรี',7=>'ป.โท',8=>'ป.เอก'),
			'value'=>$post->educate,
		)
	);

	$form->addField(
		'network',
		array(
			'type'=>'checkbox',
			'name'=>'network',
			'label'=>'เครือข่าย/หน่วยงาน:',
			//'require'=>true,
			'multiple'=>true,
			'options'=>array(
				1=>'ชุมชนท้องถิ่นน่าอยู่',
				'ความมั่นทางอาหาร',
				'ท่องเที่ยวชุมชน',
				'ปัจจัยเสี่ยง เหล้า บุหรี่ ยาเสพติด',
				'การส่งเสริมกิจกรรมทางกาย',
				'การจัดการฐานทรัพยากรธรรมชาติและสิ่งแวดล้อม',
				'ความมั่นคงทางมนุษย์ เด็กและเยาวชน ผู้สูงอายุ ผู้ด้อยโอกาส ผู้พิการ',
				'กองทุนตำบล',
				99=>'อื่น ๆ : <input class="form-text -fill" type="text" name="othernetwork" value="'.htmlspecialchars($post->othernetwork).'" placeholder="ระบุเครือข่าย/หน่วยงานอื่น ๆ" />',
			),
			'value'=>$post->network,
		)
	);

	$form->addField(
		'yearexp',
		array(
			'type'=>'text',
			'name'=>'yearexp',
			'label'=>'ประสบการณ์ทำงานในเครือข่าย:',
			'require'=>true,
			'size'=>5,
			'value'=>htmlspecialchars($post->yearexp),
			'posttext'=>' ปี',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'name'=>'save',
			'containerclass'=>'-sg-text-right',
			'value'=>'<i class="icon -save -white"></i><span>ลงทะเบียนข้อมูลทั่วไป</span>'
		)
	);

	$form->description='<div><b>**คำแนะนำ**</b><ul><li><b>กรุณาป้อนข้อมูลให้ครบทุกช่อง</b></li><li><b>การบันทึกที่อยู่</b> ให้ป้อนที่อยู่เช่นบ้านเลขที่ ซอย ถนน ให้ครบถ้วนก่อน แล้วจึงป้อน หมู่ที่ โดยพิมพ์ ม.?? หลังจากนั้นจึงป้อนตำบล โดยป้อน ต.??? เมื่อพิมพ์ชื่อตำบลประมาณ 3-4 อักษร จะมีรายชื่อตำบลแสดงด้านล่างช่องป้อนที่อยู่ ให้เลือกตำบลที่แสดงในรายการโดยไม่ต้องป้อนส่วนที่เหลือ</li><li><b>การแสดงข้อมูลส่วนบุคคลต่อสาธารณะ</b> จะแสดงที่อยู่เฉพาะหมู่ที่ ตำบล อำเภอ จังหวัด เฟซบุ๊ค เท่านั้น การแสดงที่อยู่บ้านเลขที่,ถนน,ซอย,โทรศัพท์,อีเมล์ จะต้องได้รับอนุญาตจากเจ้าของข้อมูลก่อนเท่านั้น</li></ul></div>';

	$ret .= $form->build();

	return $ret;
}
?>