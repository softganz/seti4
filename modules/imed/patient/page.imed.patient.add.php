<?php
/**
 * Add new patient
 *
 * @return String and die
 */
function imed_patient_add($self) {
	$self->theme->title='เพิ่มรายชื่อผู้ป่วย';
	$zone=imed_model::get_user_zone(i()->uid,'imed');
	if ($zone) {
		// do nothing
	} else if (!user_access('administer imed,create imed at home')) return message('error','Access denied');
	$post=(object)post();
	$fullname=trim(post('fullname'));
	$post->prename=trim(post('prename'));
	$address=trim(post('address'));
	$areacode=post('areacode');

	if ($post->fullname) {
		$data->prename=trim($post->prename);
		$data->fullname=trim($post->fullname);
		$data->cid=trim($post->cid);
		$data->address=trim($post->address);
		$data->areacode=trim($post->areacode);

		$result=R::Model('imed.person.create',$data);
		if ($post->{'data-type'}=='json') {
			return $result;
		} else {
			// $ret.=print_o($result,'$result');
			// $ret.=print_o($data,'$data');
			return $ret;
		}
	}

	$ret.='<h3>ข้อมูลส่วนบุคคลของผู้ป่วยรายใหม่</h3>';
	$form=new Form('patient',url(q()),'imed-add-patient','sg-form');
	$form->addData('checkValid',true);
	if (!post('data-type')) {
		$form->addData('data-type','json');
		$form->addData('rel','none');
		$form->addData('callback','imedAddPersonCallback');

		$form->addField('data-type',array('type'=>'hidden','name'=>'data-type','value'=>'json'));
	}

	$form->addField(
		'cid',
		array(
			'type'=>'text',
			'name'=>'cid',
			'label'=>'หมายเลขบัตรประชาชน	(13 หลัก)',
			'class'=>'-fill',
			'maxlength'=>13,
			'require'=>true,
			'placeholder'=>'เลข 13 หลัก',
			'value'=>htmlspecialchars($post->cid)
		)
	);

	$form->addField(
		'prename',
		array(
			'type'=>'text',
			'name'=>'prename',
			'label'=>'คำนำหน้าชื่อ',
			'size'=>20,
			'maxlength'=>20,
			'require'=>true,
			'placeholder'=>'คำนำหน้าชื่อ',
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
			'value'=>htmlspecialchars($post->name),
			'description'=>'กรุณาป้อนขื่อ นามสกุล โดยเคาะเว้นวรรคจำนวน 1 ครั้งระหว่างชื่อกับนามสกุล <a class="info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank">i</a>'
		)
	);

	//$post->address='256/21 ม.6 ตำบลคลองแห อำเภอหาดใหญ่ จังหวัดสงขลา';
	$form->addField(
		'address',
		array(
			'type'=>'text',
			'name'=>'address',
			'label'=>'ที่อยู่ปัจจุบัน',
			'maxlength'=>100,
			'require'=>true,
			'class'=>'sg-address -fill',
			'placeholder'=>'เลขที่ ซอย ถนน ม.0 ต.ชื่อตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง',
			'value'=>htmlspecialchars($post->address),
			'attr'=>array('data-altfld'=>'edit-areacode'),
			'description'=>'วิธีการป้อนที่อยู่ ให้ป้อนบ้านเลขที่ ซอย ถนน หมู่ที่ ต.ชื่อตำบล เมื่อป้อนชื่อตำบล จะมีรายชื่อตำบลแสดงมาด้านล่าง ให้คลิกเลือกตำบลที่ต้องการจากรายการแสดงเท่านั้น โปรแกรมจะเติมอำเภอและจังหวัดให้โดยอัตโนมัติ เช่น <b>0/0 ซอยประชายินดี ถนนมิตรภาพ ม.1 ต.คอหงส์ แล้วคลิกเลือกชื่อตำบลจากรายการด้านล่าง</b>',
		)
	);

	$form->addField(
		'tambon',
		array(
			'type'=>'hidden',
			'label'=>'ที่อยู่ปัจจุบัน แล้วเลือกตำบลจากรายการที่แสดง',
			'value'=>$post->areacode,
			'name'=>'areacode',
			'require'=>true
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -addbig -white"></i><span>เพิ่มผู้ป่วยรายใหม่</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret.='<script type="text/javascript">
		var name=$("#patientSearch").val();
		if (name != "undefined") {
			if (/^\d+$/.test(name)) {
				// Is only number
				$("#edit-cid").val(name);
			} else {
				$("#edit-fullname").val(name);
			}
			console.log(/^\d+$/.test(name));
		}
		</script>';
	return $ret;
}
?>