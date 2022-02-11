<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_view_setprice($self,$carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isEdit = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER','OFFICER'))) && empty($carInfo->sold);

	if (!$isEdit) return message('error','access denied');

	$post = (object)post('icar');
	if ($post->pricetosale != '') {
		$post->tpid = $carInfo->tpid;
		$post->pricetosale = sg_strip_money($post->pricetosale);
		$stmt = 'UPDATE %icar% SET `pricetosale` = :pricetosale WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt,$post);
		return location('icar/'.$carInfo->tpid);
	}

	$form = new Form([
		'variable' => 'icar',
		'action' => url('icar/view/setprice/'.$carInfo->tpid),
		'class' => 'icar-sale',
		'title' => 'กำหนดราคาขายหน้าร้าน',
		'children' => [
			'pricetosale' => [
				'type'=>'text',
				'label'=>'ราคาขายหน้าร้าน',
				'class'=>'-money',
				'size'=>20,
				'require'=>true,
				'value'=>number_format($carInfo->pricetosale,2),
				'placeholder'=>'0.00',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();
	//$ret .= print_o($post,'$post');
	return $ret;
}
?>