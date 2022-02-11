<?php
/**
* Green : Animal Information
* Created 2020-12-02
* Modify  2020-12-02
*
* @param Object $self
* @param Int $animalId
* @return String
*
* @usage green/rubber/my/rubber/{$Id}
*/

$debug = true;

function view_green_my_animal_weight_form($data, $options = '{}') {
	$data = SG\json_decode($data);
	//debugMsg($data,'$data');

	$form = new Form('data', url('green/my/info/animal.weight.save/'.$data->weightId), NULL, 'sg-form -sg-flex green-my-animal-weight-form');

	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#green-my-animal-view');

	$form->addField('plantId', array('type' => 'hidden', 'value' => $data->plantId));

	$form->addField(
		'date',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'class' => 'sg-datepicker',
			'readonly' => true,
			'value' => sg_date(SG\getFirst($data->date, date('Y-m-d')), 'd/m/Y'),
			'container' => '{class: "-full"}',
		)
	);

	$form->addField(
		'weight',
		array(
			'type' => 'text',
			'label' => 'น้ำหนัก',
			'class' => '-fill',
			'value' => htmlspecialchars($data->weight),
			'posttext' => '<div class="append"><span><span>กก.</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addField(
		'round',
		array(
			'type' => 'text',
			'label' => 'รอบเอว',
			'class' => '-fill',
			'value' => htmlspecialchars($data->round),
			'posttext' => '<div class="append"><span><span>ซ.ม.</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addText('<h4>อาหารที่เลี้ยงตั้งแต่รอบก่อนจนถึงรอบนี้</h4>', array('container' => '{class: "-full"}'));

	$form->addField(
		'grassweight',
		array(
			'type' => 'text',
			'label' => 'หญ้า',
			'class' => '-fill',
			'value' => htmlspecialchars($data->grassweight),
			'posttext' => '<div class="append"><span><span>กก.</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addField(
		'grassmoney',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->grassmoney),
			'posttext' => '<div class="append"><span><span>บาท</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addField(
		'strawweight',
		array(
			'type' => 'text',
			'label' => 'ฟาง',
			'class' => '-fill',
			'value' => htmlspecialchars($data->strawweight),
			'posttext' => '<div class="append"><span><span>กก.</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addField(
		'strawmoney',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->strawmoney),
			'posttext' => '<div class="append"><span><span>บาท</span></span></div>',
			'container' => '{class: "-group"}'
		)
	);

	$form->addField(
		'foodweight',
		array(
			'type' => 'text',
			'label' => 'อาหารข้น',
			'class' => '-fill',
			'value' => htmlspecialchars($data->foodweight),
			'posttext' => '<div class="append"><span><span>กก.</span></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'foodmoney',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน',
			'class' => '-fill',
			'value' => htmlspecialchars($data->foodmoney),
			'posttext' => '<div class="append"><span><span>บาท</span></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'mineralmoney',
		array(
			'type' => 'text',
			'label' => 'แร่ธาตุ',
			'class' => '-fill',
			'value' => htmlspecialchars($data->mineralmoney),
			'posttext' => '<div class="append"><span><span>บาท</span></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'drugmoney',
		array(
			'type' => 'text',
			'label' => 'ยา',
			'class' => '-fill',
			'value' => htmlspecialchars($data->drugmoney),
			'posttext' => '<div class="append"><span><span>บาท</span></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="" data-rel="none" data-done="load->replace:#green-my-animal-view"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-full -sg-text-right"}',
		)
	);

	return $form;
}
?>