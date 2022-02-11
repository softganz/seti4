<?php
function admin_site_readonly($self) {
	$self->theme->title='Site readonly';

	if (post('save')) {
		$data=post('config');
		cfg_db('web.readonly',$data['status']);
		cfg_db('web.readonly_message',$data['message']);

		cfg('web.readonly',$data['status']);
		cfg('web.readonly_message',$data['message']);

		$ret.=notify('Website has been '.(cfg('web.readonly')?'Read Only':'Read and Write').' now.');
	} else if (post('cancel')) {
		location('admin/site');
	}

	$form=new Form('config',url(q()));

	$form->addField(
		'status',
		array(
			'type'=>'radio',
			'label'=>'Site status:',
			'options'=>array(1=>'Read Only',0=>'Read/Write'),
			'value'=>cfg('web.readonly'),
			'description'=>'When set to "Read Only", all visitors will be unable to create topic and comment.',
			)
		);

	$form->addField(
		'message',
		array(
			'type'=>'textarea',
			'label'=>'Site readonly message',
			'class'=>'-fill',
			'rows'=>4,
			'value'=>cfg('web.readonly_message'),
			)
		);

	$form->addField(
		'submit',
		array(
			'type'=>'button',
			'items'=>array(
				'save'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -save -white"></i><span>Save configuration</span>'
					),
				'cancel'=>array(
					'type'=>'cancel',
					'value'=>'<i class="icon -cancel"></i><span>Cancel</span>'
					),
				'reset'=>array(
					'type'=>'reset',
					'value'=>'<i class="icon -reset"></i><span>Reset</span>'
					),
				),
			)
		);

	$ret .= $form->build();

	return $ret;
}
?>