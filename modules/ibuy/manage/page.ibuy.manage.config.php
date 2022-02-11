<?php
function ibuy_manage_config($self) {
	$self->theme->title='Configuration';

	if (post('config')) {
		$post = (object) post('config');
		cfg_db('ibuy.franchise.marketvalue',$post->marketvalue);
		cfg_db('ibuy.franchise.min_total',$post->min_total);
		//			$ret.='result '.preg_replace('/(\w+)/','',$post->min_total);
		location('ibuy/manage');
	}

	$form = new Form([
		'method' => 'post',
		'action' => url(q()),
		'variable' => 'config',
		'id' => 'edit-config',
		'children' => [
			'marketvalue' => [
				'type' => 'text',
				'label' => 'ค่าการตลาด',
				'maxlenght' => 2,
				'value' => cfg('ibuy.franchise.marketvalue'),
				'posttext' => ' %',
			],
			'min_total' => [
				'type' => 'text',
				'label' => 'ยอดซื้อขั้นต่ำที่จะได้รับค่าการตลาด',
				'maxlenght' => 2,
				'value' => cfg('ibuy.franchise.min_total'),
				'posttext' => ' บาท',
			],
			'save' => [
				'type' => 'button',
				'value' => tr('SAVE'),
				'posttext' => ' หรือ <a href="'.url('ibuy/manage').'">ยกเลิก</a>',
			],
		],
	]);

	$ret .= $form->build();
		
	return $ret;
}
?>