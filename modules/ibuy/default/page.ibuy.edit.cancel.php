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

function ibuy_edit_cancel($self, $productInfo) {
	if (!$productInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $productInfo->tpid;

	$ret .= '<header class="header -box"><h3>สถานะสินค้า</h3></header>';

	$form = new Form('topic', url('ibuy/'.$tpid.'/edit/status.update'), 'edit-topic', 'sg-form');
	$form->addData('rel', 'refresh');
	$form->addData('done', 'close');

	$form->addField(
			'outofsale',
			array(
				'type' => 'radio',
				'name' => 'outofsale',
				'label' => 'สถานะสินค้า',
				'options' => array('N' => 'ทำงาน', 'O' => 'สินค้าหมด', 'Y' => 'ไม่ทำงาน'),
				'value' => $productInfo->info->outofsale,
			)
		);
	
	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

	$ret .= $form->build();
	//$ret.=print_o(post(),'post()').print_o($topic,'$topic');
	return $ret;
}
?>