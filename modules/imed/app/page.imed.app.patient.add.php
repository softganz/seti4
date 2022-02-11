<?php
/**
* i<ed :: Add New Patient
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function imed_app_patient_add($self) {
	$getInitName = post('initname');

	if (!user_access('administer imed,create imed at home')) return message('error','Access denied');


	$post = (Object) post();

	if ($post->fullname) {
		list($post->name,$post->lname) = sg::explode_name(' ',$post->fullname);

		// Update address
		/*
		if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
			$out[3]=trim($out[3]);
			$post->house=trim($out[1]);
			$post->village=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
			$post->tambon=substr($tambon,4,2);
			$post->ampur=substr($tambon,2,2);
			$post->changwat=substr($tambon,0,2);
		}
		*/

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
			$error='กรุณาป้อน ชื่อ และ นามสกุล โดยเว้นวรรค 1 เคาะ';
		} else if ($post->name && $post->lname && $post->cid != '?'
			&& $dupid = mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE `name` = :name && `lname` = :lname AND `cid` = :cid LIMIT 1', $post)->psnid) {
			$error = 'ชื่อ <b>"'.$post->fullname.'"</b> มีอยู่ในฐานข้อมูลแล้ว';
		}
		if ($error) {
			$ret.=message('error',$error);
		} else {
			//$ret.='<p>Prepare to save person</p>';
			$post->sex = SG\getFirst($post->sex);
			$post->uid=SG\getFirst(i()->uid,'func.NULL');
			$post->created=date('U');

			$stmt = 'INSERT INTO %db_person% (
					  `uid`, `cid`, `prename`, `name`, `lname`, `sex`
					, `house`, `village`, `tambon`, `ampur`, `changwat`
					, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
					, `created`
				) VALUES (
					  :uid, :cid, :prename, :name, :lname, :sex
					, :house, :village, :tambon, :ampur, :changwat
					, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
					, :created
				)';

			mydb::query($stmt,$post);
			//$ret.='<p>'.mydb()->_query.'</p>';

			if (!mydb()->_error) {
				$psnid=$post->pid=$psnid=mydb()->insert_id;

				$stmt = 'INSERT INTO %imed_patient%
					(`pid`, `uid`, `created`)
					VALUES
					(:pid, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`uid` = :uid';

				mydb::query($stmt,$post);
				//$ret.='<p>'.mydb()->_query.'</p>';

				$ret.=R::Page('imed.app.patient.add.group',$self,$psnid);
			}
			location('imed/app/'.$psnid);
			return $ret;
		}
	}


	$provinceOptions = array();
	$ampurOptions = array();
	$tambonOptions = array();

	$stmt = 'SELECT
		*
		, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
		FROM %co_province%
		ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC, CONVERT(`provname` USING tis620) ASC';
	//$ret .= print_o(mydb::select($stmt),'dbs');
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;


	//$ret .= print_o($post,'$post');

	$ret .= '<div class="card-item" style="margin: 0;">';

	$ret .= '<header class="header -box"><h3>เพิ่มชื่อผู้ป่วยรายใหม่</h3></header>';
	$form = new Form('patient',url('imed/app/patient/add'),'imed-patient-add','sg-form');
	$form->addData('checkValid',true);

	$form->addField('pid',array('type'=>'hidden','name'=>'pid','value'=>$post->pid));

	$form->addField(
		'cid',
		array(
			'type'=>'text',
			'name'=>'cid',
			'label'=>'หมายเลขประจำตัวประชาชน 13 หลัก',
			'class'=>'-fill',
			'maxlength'=>13,
			'require'=>true,
			'placeholder'=>'หมายเลข 13 หลัก',
			'value'=>htmlspecialchars($post->cid),
			'description' => 'ป้อน ? ในกรณีที่ไม่มีบัตรประชาชนหรือยังไม่ทราบ',
		)
	);

	$form->addField(
		'prename',
		array(
			'type'=>'text',
			'name'=>'prename',
			'label'=>'คำนำหน้านาม',
			'class'=>'-fill',
			'maxlength'=>20,
			'require'=>true,
			'placeholder'=>'eg. นาย นาง',
			'value'=>htmlspecialchars($post->prename)
		)
	);

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
			'value'=>htmlspecialchars(SG\getFirst($post->fullname,$getInitName)),
			'description'=>'กรุณาป้อนขื่อ - นามสกุล โดยเคาะเว้นวรรคจำนวน 1 ครั้งระหว่างชื่อกับนามสกุล <!-- <a class="info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank">i</a>-->'
		)
	);

	$form->addField(
		'sex',
		array(
			'type'=>'radio',
			'name'=>'sex',
			'label'=>'เพศ:',
			'require'=>true,
			'options'=>array('1'=>'ชาย','2'=>'หญิง'),
			'value'=>$post->sex,
		)
	);

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
		'address',
		array(
			'type'=>'text',
			'name'=>'address',
			'label'=>'ที่อยู่',
			'class'=>'sg-address -fill',
			'maxlength'=>100,
			'require'=>true,
			'attr'=>array('data-altfld'=>'edit-areacode'),
			'placeholder'=>'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
			'value'=>htmlspecialchars($post->address)
		)
	);

	$form->addField('changwat',
		array(
		//	'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
		//	'containerclass' => '-inlineblock',
		)
	);

	$form->addField('ampur',
		array(
		//	'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'options' => array('' => '== เลือกอำเภอ ==') + $ampurOptions,
		//	'containerclass' => '-inlineblock',
			'value' => $data->ampur,
		)
	);

	$form->addField('tambon',
		array(
		//	'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'options' => array('' => '== เลือกตำบล ==') + $tambonOptions,
			'value' => $data->tambon,
		//	'containerclass' => '-inlineblock',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'name'=>'save',
			'value'=>'<i class="icon -addbig -white"></i><span>เพิ่มชื่อรายใหม่</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.card-item .header.-box {margin: 0;}
	.module-imed.-app .card-item .form {padding: 0 16px;}
	.card-item .form .form-item {padding: 0 0 4px 0;}
	</style>';

	$ret .= '<script type="text/javascript">
		var inputStr = $("#edit-pn").val()
		if (inputStr) {
			if (inputStr.match(/^\d+$/)) {
				$("#edit-cid").val(inputStr.substr(0,13))
			} else {
				$("#edit-fullname").val(inputStr)
			}
		}

		$(document).on("change", "#edit-patient-changwat,#edit-patient-ampur", function() {
			$("#edit-areacode").val("")
		})

		$(document).on("change", "#edit-patient-tambon", function() {
			if ($(this).val() == "") {
				$("#edit-areacode").val("")
				return
			}
			var areaCode = $("#edit-patient-changwat").val()+$("#edit-patient-ampur").val()+$("#edit-patient-tambon").val()
			console.log(areaCode);
			$("#edit-areacode").val(areaCode)
		})
	</script>';
	$ret .= '</div>';

	return $ret;
}
?>