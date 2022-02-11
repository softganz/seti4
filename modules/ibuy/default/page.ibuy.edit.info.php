<?php
/**
* Cancel Product
* Created 2019-06-04
* Modify  2019-06-04
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_edit_info($self, $productInfo) {
	if (!$productInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $productInfo->tpid;

	$ret .= '<header class="header -box"><h3>แก้ไขรายละเอียด</h3></header>';

	$form = new Form('topic', url('ibuy/'.$tpid.'/edit/info.update'), 'edit-topic', 'sg-form');
	$form->addData('rel', 'refresh');
	$form->addData('done', 'close');

	$form->addField(
			'stockid',
			array(
				'type' => 'text',
				'label' => 'รหัสสินค้า',
				'class' => '-fill',
				'maxlength' => 64,
				'require' => true,
				'value' => htmlspecialchars($productInfo->info->stockid),
			)
		);

	$form->addField(
			'title',
			array(
				'type' => 'text',
				'label' => 'ชื่อสินค้า',
				'class' => '-fill',
				'maxlength' => 255,
				'require' => true,
				'value' => htmlspecialchars($productInfo->info->title),
			)
		);

	$form->addField(
			'body',
			array(
				'type' => 'textarea',
				'label' => 'รายละเอียดสินค้า',
				'class' => '-fill',
				'rows' => 8,
				'value' => $productInfo->info->body,
			)
		);

	$form->addField(
			'forbrand',
			array(
				'type' => 'text',
				'label' => 'สำหรับรุ่น',
				'class' => '-fill',
				'value' => htmlspecialchars($productInfo->info->forbrand),
			)
		);

	foreach (cfg('ibuy.price.use') as $key => $value) {
		$form->addField(
			$key,
			array(
				'type' => 'text',
				'label' => $value->label.' (บาท)',
				'class' => '-money',
				'maxlength' => 10,
				'value' => number_format($productInfo->info->{$key},2),
			)
		);
	}

	$form->addField(
			'minsaleqty',
			array(
				'type' => 'text',
				'label' => 'จำนวนสั่งซื้อขั้นต่ำ (ชิ้น)',
				'class' => '-numeric',
				'value' => $productInfo->info->minsaleqty > 1 ? $productInfo->info->minsaleqty : '',
			)
		);

	if (cfg('ibuy.stock.use')) {
		$form->addField(
				'balance',
				array(
					'type' => 'text',
					'label' => 'ยอดคงเหลือ (ชิ้น)',
					'maxlength' => 7,
					'value' => number_format($productInfo->info->balance,2),
				)
			);
	}

	if (cfg('ibuy.resaler.discount') > 0) {
		$form->addField(
				'isdiscount',
				array(
					'type' => 'radio',
					'label' => 'การคำนวณส่วนลด',
					'options' => array(0 => 'ไม่ ไม่นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณส่วนลดเมื่อมีการสั่งซื้อสินค้า'),
					'value' => $productInfo->info->isdiscount,
				)
			);
	}
	
	if (cfg('ibuy.franchise.marketvalue') > 0) {
		$form->addField(
				'ismarket',
				array(
					'type' => 'radio',
					'label' => 'คำนวณค่าการตลาด',
					'options' => array(0 => 'ไม่ ไม่นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณค่าการตลาดเมื่อมีการสั่งซื้อสินค้า'),
					'value' => $productInfo->info->ismarket,
				)
			);
	}

	if (cfg('ibuy.franchise.franchisor') > 0) {
		$form->addField(
				'isfranchisor',
				array(
					'type' => 'radio',
					'label' => 'คำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์',
					'options' => array(0 => 'ไม่ ไม่นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า', 1 => 'ไช่ นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์เมื่อมีการสั่งซื้อสินค้า'),
					'value' => $productInfo->info->isfranchisor,
				)
			);
	}
	
	$form->addField(
			'isnew',
			array(
				'type' => 'radio',
				'label' => 'แสดงในรายการสินค้ามาใหม่',
				'options' => array(1 => 'แสดง', 0 => 'ไม่แสดง'),
				'display' => 'inline',
				'value' => $productInfo->info->isnew,
			)
		);

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right -box-bottom"}',
			)
		);

	$ret .= $form->build();

	//$ret .= print_o($productInfo,'$productInfo');

	return $ret;
}
?>