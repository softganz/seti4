<?php
function ibuy_shop_create($self) {
	$self->theme->title='สร้างหน้าร้าน';
	R::Page('ibuy.shop.toolbar',$self,$shopId);

	if (!user_access('create own shop')) {
		return message('error','access denied:ขออภัยค่ะ ท่านยังไม่ได้รับสิทธิ์ในการเปิดหน้าร้าน');
	}

	if (post('name')) {
		$data->name = post('name');
		$data->uid = i()->uid;
		$data->created = date('U');
		$stmt = 'INSERT INTO %db_org% (`uid`,`name`,`created`) VALUES (:uid,:name,:created)';

		mydb::query($stmt, $data);

		if (!mydb()->_error) {
			$data->orgid = mydb()->insert_id;
			$data->membership = 'ShopOwner';

			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';

			mydb::query($stmt, $data);

			$stmt = 'INSERT INTO %ibuy_shop% (`shopid`, `uid`, `created`) VALUES (:orgid, :uid, :created)';
			mydb::query($stmt, $data);

			setcookie('shopid', $data->orgid,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
			$_SESSION['shopid'] = $data->orgid;

			return;
		} else {
			$error = 'มีข้อผิดพลาดในการสร้างหน้าร้านใหม่';
		}
	}

	if ($error) $ret .= message('error', $error);

	$form = new Form(NULL, url(q()), 'ibuy-shop-create', 'sg-form');
	$form->addData('checkValid', true);

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อร้าน',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($name),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => 'สร้างหน้าร้าน',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>