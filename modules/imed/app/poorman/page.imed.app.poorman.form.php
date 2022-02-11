<?php
function imed_app_poorman_form($self, $qtref = NULL, $action = NULL) {
	$getRef = post('ref');

	R::View('imed.toolbar',$self,'คนยากลำบาก','app.poorman');
	if (!i()->ok) return R::View('signform');

	$formId = 4;		// แบบฟอร์ม ศปจ.
	$formGroup = 4; // คนยากลำบาก

	//$result->msg.=print_o($_FILES,'$_FILES');
	//return json_encode((array)$result);

	$data = (object)post('data');

	//$ret.='post(save)='.post('save');
	//$ret .= print_o($data,'$data');

	// Create new quatation
	if ( empty($qtref) && ($data->psnid || ($data->{'qt:PSNL.FULLNAME'} && $data->{'qt:PSNL.CID'})) )  {
		// Create new qt
		$data->appsrc='Android';
		$result=R::Model('imed.poorman.save',$data);
		//$ret.='Create new';
		//$ret.=print_o($data,'$data');
		//$ret.=print_o($result,'$result');
		if ($result->isDupPerson) {
			// Show form again
			$error.='<p class="notify">ชื่อ <b>"'.$data->{'qt:PSNL.FULLNAME'} .'" เลขที่บัตรประจำตัวประชาชน "'.$data->{'qt:PSNL.CID'}.'"</b> มีอยู่ในฐานข้อมูลแล้ว ไม่สามารถสร้างซ้ำได้ กรุณาเลือกรายชื่อจากรายการที่แสดง</p>';
		//} else if (i()->username=='softganz') {
		//	$ret.=print_o($result,'$result');
		} else if ($result->qtref) {
			location('imed/app/poorman/form/'.$result->qtref.'/edit', array('ref' => $getRef));
		}
	}



	// Create new quatation form
	if (empty($qtref)) {
		$getPsnid = post('pid');
		$ret.='<h3 class="header -sub">แบบสำรวจข้อมูลประชาชนในภาวะยากลำบากและกลุ่มเปราะบางทางสังคม'.(post('fmid')?' (แบบฟอร์ม '.post('fmid').')':'').'</h3>';
		$form=new Form('data',url('imed/app/poorman/form'),'imed-poorman-form','sg-form imed-poorman-form');
		$form->addData('checkValid',true);
		$form->addField('psnid',array('type'=>'hidden','id'=>'psnid', 'value'=>$getPsnid));
		$form->addField('qtgroup',array('type'=>'hidden','value'=>$formGroup));
		$form->addField('qtform',array('type'=>'hidden','value'=>SG\getFirst(post('fmid'),$formId)));

		$form->addField('h1','<div style="margin:0 0 16px auto;padding:8px;border:1px #ccc solid;background:#f0f0f0;text-align:right;width:17em;white-space:nowrap;">ลำดับที่การเก็บข้อมูล <input id="qtrefno" class="form-text" type="text" style="width:6em;text-align:center;" value="????/'.(date('Y')+543).'" readonly="readonly" /></div>');

		$form->addField('h2','<h3>ข้อมูลบุคคลในแบบสำรวจ</h3>');

		$form->addField('qt:PSNL.PRENAME',
			array(
				'type'=>'select',
				'label'=>'คำนำหน้าชื่อ :',
				'class'=>'-fill',
				'options'=>array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว'),
				'value'=>$data->{'qt:PSNL.PRENAME'},
				'posttext'=>'<input class="form-text -fill -hidden" type="text" name="prename-other" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
				)
			);

		$form->addField('qt:PSNL.FULLNAME',
			array(
				'type'=>'text',
				'label'=>'ชื่อ - นามสกุล',
				'class'=>(empty($getPsnid) ? 'sg-autocomplete' : '').' -fill',
				'require'=>true,
				'value'=>$data->{'qt:PSNL.FULLNAME'},
				'placeholder'=>'ป้อนชื่อ นามสกุล หรือ เลข 13 หลัก',
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
				'value'=>$data->{'qt:PSNL.CID'},
				'require'=>true,
				'maxlength'=>13,
				)
			);

		$form->addField('qt:PSNL.REGIST.ADDRESS',
			array(
				'type'=>'text',
				'label'=>'ที่อยู่',
				'class'=>'sg-address -fill',
				'placeholder'=>'ตัวอย่าง 0/0 ซอยชื่อ ถนนชื่อ ม.0 ต.ชื่อ แล้วเลือกรายการจากด้านล่าง',
				'require'=>true,
				'value'=>$data->{'qt:PSNL.REGIST.ADDRESS'},
				'attr'=>array('data-altfld'=>'PSNL_REGIST_AREACODE')
				)
			);

		$form->addField('qt:PSNL.REGIST.AREACODE',
			array(
				'type'=>'hidden',
				'label'=>'กรุณาเลือกที่อยู่จากรายการที่แสดง',
				'id'=>'PSNL_REGIST_AREACODE',
				'require'=>true,
				'value'=>$data->{'qt:PSNL.REGIST.AREACODE'},
				)
			);

		if ($error) $form->addField('error',$error);

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

		$form->description='<div><b>**คำแนะนำ**</b><ul><li><b>กรุณาป้อนข้อมูลให้ครบทุกช่อง</b></li><li><b>หากเป็นชื่อที่มีอยู่ในฐานข้อมูลอยู่แล้ว</b> ให้เลือกจากรายชื่อที่แสดงใต้ช่องป้อนชื่อ-นามสกุล</li><li><b>การบันทึกที่อยู่</b> ให้ป้อนที่อยู่เช่นบ้านเลขที่ ซอย ถนน ก่อน แล้วจึงป้อน หมู่ที่ โดยพิมพ์ ม.?? หลังจากนั้นจึงป้อนตำบล โดยป้อน ต.??? เมื่อพิมพ์ชื่อตำบลประมาณ 3-4 อักษร จะมีรายชื่อตำบลแสดงด้านล่างช่องป้อนที่อยู่ ให้เลือกตำบลที่แสดงในรายการโดยไม่ต้องป้อนส่วนที่เหลือ</li></ul></div>';

		$ret.=$form->build();

		return $ret;
	}



	// Save quatation data
	if (post('save')) {
		$result=R::Model('imed.poorman.save',$data);
		//location('imed/app/poorman/list');
		return $ret;
	} else if ($_FILES) {
		$photoKey = key($_FILES);
		//$ret.=print_o($_FILES,'$_FILES');
		// Para : $data->seq from post
		$data->prename = $photoKey.'_'.$data->psnid.'_'.date('ymdhis').'_';
		$data->tagname = $photoKey;
		$data->deleteurl = url('imed/api/visit/'.$data->psnid.'/photo.delete/'.$data->seq.'?f=');
		$uploadResult = R::Model('imed.visit.photo.upload',$_FILES[$photoKey],$data);
		$ret .= $uploadResult['link'];

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






	// Get quatation data
	$qtInfo=R::Model('imed.qt.get',$qtref);

	// Show full quatation form
	R::View('imed.toolbar',$self,$qtInfo->tr['PSNL.FULLNAME']->value,'app.poorman');

	$ret .= R::View('imed.app.poorman.form.'.$qtInfo->qtform,$qtInfo,$action);

	//$ret.=print_o($qtInfo,'$qtInfo');
	head('js.imed.js','<script type="text/javascript" src="imed/js.imed.js"></script>');



	$head.='<script type="text/javascript">
	function imedAppPoormanGetPerson($this,ui) {
		console.log("Callback "+ui.item.value);
		$("#psnid").val(ui.item.value);
	}

	</script>';
	//head($head);
	$ret .= $head;

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
	$isEditable=true;
	foreach ($photoList as $rs) {
		if ($rs->tagname!=$tagname) continue;
		$photoInfo = imed_model::upload_photo($rs->file);
		$photoUrl = $photoInfo->_url;
		$ret.='<li class="-hover-parent">';
		//$ret.=$rs->file;
		$ret.='<a class="sg-action" href="'.$photoUrl.'" data-rel="img" data-group="'.$tagname.'"><img src="'.$photoUrl.'" height="140" /></a>';
		if ($isEditable) {
			$ui=new Ui('span','');
			$ui->add('<a class="sg-action" href="'.url('imed/api/visit/'.$rs->psnid.'/photo.delete/'.$rs->seq,array('f'=>$rs->fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i></a>');
			$ret.='<nav class="nav iconset -hover -no-print">'.$ui->build().'</nav>';
		}
		$ret.='</li>'._NL;
	}
	//$ret.='Tagname='.$tagname.'<br />';
	//$ret.=print_o($photoList,'$photoList');
	return $ret;
}
?>