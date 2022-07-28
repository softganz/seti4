<?php
function admin_site_init($self) {
	$self->theme->title='Site initial command';

	if (post('save')) {
		$config=(object)post('config',_TRIM);
		cfg_db('web.init',$config->init);

		$ret.=notify('Website initial has been save.');
	} else if (post('cancel')) {
		location('admin/site');
	}

	$form=new Form('config',url(q()),'admin-config-form');

	$form->addField(
		'init',
		array(
			'type'=>'textarea',
			'label'=>'Enter Initial Command',
			'class'=>'-fill',
			'cols'=>60,
			'rows'=>20,
			'value'=>htmlspecialchars(cfg('web.init')),
			'description'=>'Your site\'s initial command include php command.',
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
					'value'=>'<i class="icon -material">done_all</i><span>Save configuration</span>'
					),
				'cancel'=>array(
					'type'=>'cancel',
					'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
					),
				'reset'=>array(
					'type'=>'reset',
					'value'=>'<i class="icon -material">restart_alt</i><span>Reset</span>'
					),
				),
			)
		);

	$ret .= $form->build();

	return $ret;
}
?>