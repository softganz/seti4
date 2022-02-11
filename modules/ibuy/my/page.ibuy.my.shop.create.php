<?php
/**
* Create New Shop
* Created 2019-11-06
* Modify  2019-11-06
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_my_shop_create($self) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>สร้างกลุ่ม/ร้านค้า</h3></header>';

	if (!user_access('create own shop')) {
		return message('error','access denied:ขออภัยค่ะ ท่านยังไม่ได้รับสิทธิ์ในการเปิดหน้าร้าน');
	}

	if (post('name')) {
		$stmt = 'INSERT INTO %db_org% (`uid`,`name`,`created`) VALUES (:uid,:name,:created)';

		mydb::query($stmt,':uid',i()->uid, ':name',post('name'), ':created',date('U'));

		if (!mydb()->_error) {
			$orgid = mydb()->insert_id;

			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';

			mydb::query($stmt,':orgid',$orgid, ':uid',i()->uid, ':membership',"ShopOwner");

			$stmt = 'INSERT INTO %ibuy_shop% (`shopid`, `uid`, `created`) VALUES (:shopid, :uid, :created)';
			mydb::query($stmt,':shopid',$orgid, ':uid',i()->uid, ':created', date('U'));

			//location('ibuy/shop/manage/'.$orgid);
		} else {
			$error = 'มีข้อผิดพลาดในการสร้างหน้าร้านใหม่';
		}
		return;
	}

	if ($error) $ret .= message('error', $error);

	$form = new Form(NULL, url(q()), 'ibuy-shop-create', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'reload');

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อร้าน/กลุ่ม',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($name),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>สร้างหน้าร้าน</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>