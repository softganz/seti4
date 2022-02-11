<?php
function admin_site_maintenance($self) {
	$self->theme->title='Site maintenance';

	if (post('save')) {
		$data=post('config');
		cfg_db('web.status',$data['status']);
		cfg_db('web.offline_message',$data['message']);

		cfg('web.status',$data['status']);
		cfg('web.offline_message',$data['message']);

		$ret.=notify('Website has been '.($data['status']?'Online':'Off-line').' now');
	} else if (post('cancel')) {
		location('admin/site');
	}

	$form=new Form('config',url(q()));

	$form->addField(
		'status',
		array(
			'type'=>'radio',
			'label'=>'Site status: ',
			'options'=>array(1=>'Online',0=>'Off-line'),
			'value'=>cfg('web.status'),
			'description'=>'When set to "Online", all visitors will be able to browse your site normally. When set to "Off-line", only users with the "administer site configuration" permission will be able to access your site to perform maintenance; all other visitors will see the site off-line message configured below. Authorized users can log in during "Off-line" mode directly via the user login page.',
			)
		);

	$form->addField(
		'message',
		array(
			'type'=>'textarea',
			'label'=>'Site off-line message',
			'class'=>'-fill',
			'rows'=>10,
			'value'=>cfg('web.offline_message'),
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