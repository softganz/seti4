<?php
/**
* Green :: Organic Register
*
* @param Object $self
* @return String
*/

$debug = true;

function green_organic_register($self) {
	$getRefUrl = post('ref');

	$isAdmin = is_admin('green');

	if (!i()->ok) return R::View('signform', '{showTime: false, time: -1}');

	if (post('name')) {
		$data = new stdClass();
		$data = (Object) array_slice(post(),1);
		$result = R::Model('green.shop.create', $data);
		//$ret .= print_o($result,'$result');
		//$ret .= print_o($data,'$data');
		if ($result->shopId) {
			$_SESSION['shopid'] = $result->shopId;
			location('green/organic/my/land');
		}
	}

	// Shop Registers
	$ret .= '<section id="ibut-shop-verify" class="card -margin">'
		. '<p class="-sg-text-center" style="padding: 32px 0;"><big>ยินดีต้อนรับ <b>'.i()->name.'</b> เข้าสู่ระบบการจัดการข้อมูล <b>"เกษตรอินทรีย์"</b></big><br /><br /><br />'
		. '<span>ท่านยังไม่เคยใช้งานมาก่อน ต้องการเริ่มใช้งานหรือไม่?<br /><br /><br /><br />'
		. '<a class="sg-action btn -primary" href="#green-organic-register-template" data-rel="parent:span" data-width="640">สมัครสมาชิกเกษตรอินทรีย์</a></span>'
		. '</p>';
	$ret .= '<p style="padding: 32px;">'
		. '* การสมัครสมาชิกเกษตรอินทรีย์ แสดงว่าท่านยอมรับข้อตกลงในการใช้งานระบบการจัดการข้อมูล "เกษตรอินทรีย์" เรียบร้อยแล้ว<br />'
		. '** กรณีที่ท่านเป็นสมาชิกของกลุ่ม/เครือข่ายที่ได้สร้างกลุ่ม/องค์กร/หน่วยงาน/ร้านค้าไว้ในระบบแล้ว ท่านสามารถแจ้งผู้ดูแลกลุ่ม/เครือข่ายให้เพิ่มชื่อของท่านเข้าเป็นสมาชิกของกลุ่ม/เครือข่ายเพื่อจัดการข้อมูลร่วมกัน โดยไม่จำเป็นต้องสร้างกลุ่ม/ร้านค้าใหม่'
		. '</p>';
	$ret .= '</section>';


	$provinceOptions = array();

	$stmt = 'SELECT
		*
		, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
		FROM %co_province%
		ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC, CONVERT(`provname` USING tis620) ASC';
	//$ret .= print_o(mydb::select($stmt),'dbs');
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;

	$form = new Form(NULL, url('green/organic/register'), 'green-organic-register', 'sg-form');
	$form->addData('checkValid', true);
	//$form->addData('rel', 'notify');
	//$form->addData('done', 'reload');

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อกลุ่ม/เครือข่าย/องค์กร/บุคคล',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->name),
			'placeholder' => 'ระบุชื่อกลุ่ม เครือข่าย องค์กร หรือ บุคคล'
		)
	);

	$form->addField(
		'landname',
		array(
			'label' => 'ชื่อแปลงที่ดิน',
			'type' => 'text',
			'class' => '-fill',
			'require' => $isAdmin ? false : true,
			'value' => htmlspecialchars($data->landname),
			'placeholder' => 'ระบุชื่อแปลงที่ดิน',
		)
	);

	$form->addField(
		'deedno',
		array(
			'label' => 'เลขที่โฉนดที่ดิน',
			'type' => 'text',
			'class' => '-fill',
			'value' => htmlspecialchars($data->deedno),
			'placeholder' => 'ระบุเลขที่ของโฉนดที่ดิน',
		)
	);

	$form->addText('<div class="area -sg-flex -justify-left">');
	$form->addField(
		'arearai',
		array(
			'label' => 'พื้นที่ :',
			'type' => 'text',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->arearai),
			'posttext' => '<div class="input-append"><span>ไร่</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
//			'posttext' => '<div class="input-append"><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#edit-searchqt").val("");$(this).closest("form").submit()\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
		)
	);
	$form->addField(
		'areahan',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areahan),
			'posttext' => '<div class="input-append"><span>งาน</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addField(
		'areawa',
		array(
			'type' => 'text',
			'label' => '&nbsp;',
			'class' => '-numeric -fill',
			'value' => htmlspecialchars($data->areawa),
			'posttext' => '<div class="input-append -nowrap"><span>ตร.วา</span></div>',
			'placeholder' => '0',
			'container' => '{class: "-group -inlineblock"}',
		)
	);
	$form->addText('</div>');

	$form->addField(
		'areacode',
		array(
			'type' => 'hidden',
			'label' => 'เลือกตำบลในที่อยู่',
			'value'=>$data->areacode,
			'name' => 'areacode',
			'require' => $isAdmin ? false : true,
		)
	);

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'name' => 'address',
			'label' => 'ที่อยู่แปลงที่ดิน',
			'class' => 'sg-address -fill',
			'maxlength' => 100,
			'attr' => array('data-altfld' => 'edit-areacode'),
			'placeholder' => 'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
			'value' => htmlspecialchars($data->address)
		)
	);

	$form->addField('changwat',
		array(
		//	'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
		)
	);

	$form->addField('ampur',
		array(
		//	'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'options' => array('' => '== เลือกอำเภอ =='),
			'value' => $data->ampur,
		)
	);

	$form->addField('tambon',
		array(
		//	'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'options' => array('' => '== เลือกตำบล =='),
			'value' => $data->tambon,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>สมัครใช้งาน</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= '<div id="green-organic-register-template" class="-hidden"><div class="-sg-text-left">'.$form->build().'</div></div>';

	$ret .= '<script type="text/javascript">
		$(document).on("change", "#edit-tambon", function() {
			if ($(this).val() == "") {
				$("#edit-areacode").val("")
				return
			}
			var areaCode = $("#edit-changwat").val()+$("#edit-ampur").val()+$("#edit-tambon").val()
			$("#edit-areacode").val(areaCode)
		});
	</script>';
	return $ret;
}
?>