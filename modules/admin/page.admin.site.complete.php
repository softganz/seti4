<?php
function admin_site_complete($self) {
	$self->theme->title='Site completed command';

	if (post('save')) {
		$config=(object)post('config');
		cfg_db('web.complete',$config->complete);

		$ret.=notify('Website complete has been save.');
	} else if (post('cancel')) {
		location('admin/site');
	}

	$form=new Form('config',url(q()),'admin-config-form');

	$form->addField(
		'complete',
		array(
			'type'=>'textarea',
			'label'=>'Enter Website Completed Command',
			'class'=>'-fill',
			'cols'=>60,
			'rows'=>15,
			'value'=>htmlspecialchars(cfg('web.complete')),
			'description'=>'This text and php command will be execute before tag &lt;/body&gt; was show.You can place site counter or analytics script.',
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