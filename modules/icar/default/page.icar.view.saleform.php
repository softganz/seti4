<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_view_saleform($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isEdit = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER','OFFICER')));


	if (!$isEdit) return message('error','access denied');

	$post=(object)post('icar');
	if ($_POST['icar']) {
		$post->tpid=$carInfo->tpid;
		$post->saledate=sg_date($post->saledate,'Y-m-d');
		$stmt = 'UPDATE %icar%
			SET
			`saledate` = :saledate,
			`saleprice` = :saleprice,
			`saledownprice` = :saledownprice,
			`customer` = :customer,
			`address` = :address,
			`phone` = :phone
			WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt,$post);
		return location('icar/'.$carInfo->tpid);

		if ($_POST['save']) {
			mydb::query('UPDATE %icar% SET `sold`="Yes" WHERE `tpid`=:tpid LIMIT 1',':tpid',$carInfo->tpid);
			return location('icar/'.$carInfo->tpid);
		}
		$carInfo=icar_model::get_by_id($id);
	} else {
		$post->saledate=$carInfo->saledate?$carInfo->saledate:date('Y-m-d');
		$post->saleprice=$carInfo->saleprice;
		$post->comfinance=$carInfo->comfinance;
		$post->rcvtransfer=$carInfo->rcvtransfer;
		$post->paytransfer=$carInfo->paytransfer;
		$post->saledownprice=$carInfo->saledownprice;
		$post->saledownpaid=$carInfo->saledownpaid;
		$post->customer=$carInfo->customer;
		$post->address=$carInfo->address;
		$post->phone=$carInfo->phone;
	}





	$form = new Form('icar', url('icar/view/saleform/'.$carInfo->tpid), 'icar-sale', 'sg-form icar-sale-form');
	$form->addConfig('title', 'บันทึกราคาขาย');
	$form->addData('checkValid', true);
	$form->addData('rel', '#main');

	$form->addField(
		'saledate',
		array(
			'type'=>'text',
			'label'=>'วันที่ขาย',
			'class'=>'sg-datepicker',
			'require'=>true,
			'value'=>SG\getFirst($post->saledate?sg_date($post->saledate,'d/m/Y'):NULL,date('d/m/Y'))
		)
	);

	$form->addField(
		'saleprice',
		array(
			'type'=>'text',
			'label'=>'ราคาขาย',
			'require'=>true,
			'value'=>htmlspecialchars($post->saleprice),
			'placeholder'=>'0.00'
		)
	);

	//		$form->comfinance=array('type'=>'text', 'label'=>'Com Finance', 'size'=>10, 'value'=>htmlspecialchars($post->comfinance),'placeholder'=>'0.00');
	//		$form->rcvtransfer=array('type'=>'text', 'label'=>'รับค่าโอน', 'size'=>10, 'value'=>htmlspecialchars($post->rcvtransfer),'placeholder'=>'0.00');
	//		$form->paytransfer=array('type'=>'text', 'label'=>'ค่าใช้จ่ายในการโอน', 'size'=>10, 'value'=>htmlspecialchars($post->paytransfer),'placeholder'=>'0.00');

	$form->addField(
		'saledownprice',
		array(
			'type'=>'text',
			'label'=>'เงินดาวน์',
			'value'=>htmlspecialchars($post->saledownprice),
			'placeholder'=>'0.00',
		)
	);

	//		$form->saledownpaid=array('type'=>'text', 'label'=>'จ่ายเงินดาวน์', 'size'=>10, 'value'=>htmlspecialchars($post->saledownpaid),'placeholder'=>'0.00');

	$form->addField(
		'customer',
		array(
			'type' => 'text',
			'label' => 'ชื่อลูกค้า',
			'class' => '-fill-width',
			'value' => htmlspecialchars($post->customer),
		)
	);

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class' => '-fill-width',
			'value' => htmlspecialchars($post->address),
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill-width',
			'value' => htmlspecialchars($post->phone),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'container' => array('class' => '-sg-text-right'),
		)
	);
	//		$form->submit->items->save='ปิดการขาย';

	$ret .= $form->build();

	//		$ret.=print_o($_POST,'$_POST');
	//		$ret.=print_o($post,'$post');
	//		$ret.=print_o($carInfo,'$carInfo');
	$ret.='<script type="text/javascript">
		$(document).ready(function() {
			$("#edit-icar-saledate").datepicker({
				dateFormat: "dd/mm/yy",
				disabled: false,
				monthNames: thaiMonthName
			});
		});
		</script>';
	return $ret;
}
?>