<?php
/**
* Garage : Customer Form
* Created 2020-10-14
* Modify  2020-10-14
*
* @param Object $self
* @param Int $custId
* @return String
*
* @usage garage/code/customer/{$action}[/{$custId}]
*/

$debug = true;

function garage_code_customer_form($self, $custId = NULL) {
	if ($custId) {
		$stmt = 'SELECT
			c.*
			, CAST(SUBSTR(c.`areacode`,7,2) AS UNSIGNED) `village`
			, cosub.`subdistname` `tambonName`
			, codist.`distname` `ampurName`
			, copv.`provname` `changwatName`
			FROM %garage_customer% c
				LEFT JOIN %co_province% copv ON copv.`provid` = LEFT(c.`areacode`,2)
				LEFT JOIN %co_district% codist ON codist.`distid` = LEFT(c.`areacode`,4)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = LEFT(c.`areacode`,6)
				LEFT JOIN %co_village% covi ON covi.`villid` = c.`areacode`
			WHERE `customerid` = :id
			LIMIT 1';
		$data = mydb::select($stmt, ':id', $custId);
		$data->address = SG\implode_address($data);
	}
	$ret = '<header class="header"><h3>ข้อมูลลูกค้า</h3></header>';

	$form = new Form('data', url('garage/code/info/customer.save/'.$custId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', (post('callback') ? 'callback:'.post('callback').' | ' : ''). 'close');
	$form->addData('checkValid', true);

	$form->addField(
		'prename',
		array(
			'type' => 'text',
			'label' => 'คำนำหน้า',
			'value' => $data->prename,
		)
	);

	$form->addField(
		'customername',
		array(
			'type' => 'text',
			'label' => 'ชื่อลูกค้า',
			'class' => '-fill',
			'require' => true,
			'value' => $data->customername,
		)
	);

	$form->addField('areacode', array('type' => 'hidden', 'value' => $data->areacode));
	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class' => 'sg-address -fill',
			'maxlength' => 255,
			'placeholder' => 'ระบุสถานที่ บ้านเลขที่ หมู่ที่ ตำบล แล้วเลือกจากรายการที่แสดงด้านล่าง',
			'value' => htmlspecialchars($data->address),
			'attr' => array('data-altfld' => 'edit-data-areacode'),
		)
	);

	$form->addField(
		'customerphone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'value' => $data->customerphone,
		)
	);

	$form->addField(
		'customermail',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'value' => $data->customermail,
		)
	);

	$form->addField(
		'remark',
		array(
			'type' => 'textarea',
			'label' => 'หมายเหตุ',
			'class' => '-fill',
			'rows' => 2,
			'value' => $data->remark,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	/*
			.'<input id="customerid" type="hidden" name="customerid" value="'.$data->customerid.'" />'
		.'<input id="customername" class="form-text sg-autocomplete -fill -require" type="text" name="customername" value="'.$data->customername.'" placeholder="ชื่อลูกค้า" data-query="'.url('garage/api/customer').'" data-select=\'{"oldid":"value","customerid":"value","customername":"label","customerphone":"phone"}\' />'
		.'<button class="search"><i class="icon -search"></i></button>',
		'<td colspan="2"><input id="customerphone" class="form-text -fill" type="text" name="customerphone" value="'.$data->customerphone.'" placeholder="โทรศัพท์" />'
	*/
	return $ret;
}
?>