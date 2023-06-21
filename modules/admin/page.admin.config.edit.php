<?php
/**
 * Edit config and save config to db
 * 
 * @param $_REQUEST['n']
 * @return String
 */
function admin_config_edit($self) {
	$self->theme->title = 'Modify configuration';
	$name = post('name');
	if (!isset($name)) location('admin/config/view');

	$value = cfg($name);

	$ui = new Ui();
	$ui->add('<a class="sg-action" href="'.url('admin/config/edit',array('name'=>$name)).'" data-rel="box" data-width="480"><i class="icon -material">refresh</i></a>');

	if (post('restore')) {
		cfg_db_delete($name);
		location('admin/config/edit',array('name'=>$name));
	}
	
	if (post('admin')) {
		$post = (Object) post('admin');
		//$ret .= print_o(post(),'post()');
		$saved = true;
		if (is_array($value)) {
			foreach ($value as $k=>$v) {
				if (!in_array($k,$post->value)) unset($value[$k]);
			}
			if ($post->addvalue!='') {
				if ($post->addkey=='') {
					$value[]=$post->addvalue;
				} else {
					$value[$post->addkey]=$post->addvalue;
				}
			}
			cfg_db($name,$value);
		} else if (is_object($value)) {
			if ($post->value != '{}') {
				$testResult = (array) sg_json_decode($post->value);
				if (empty($testResult)) {
					$configError = true;
					$newValue = $post->value;
					$saved = false;
				} else {
					cfg_db($name,$post->value);
				}
			} else {
				cfg_db($name,$post->value);
			}
			//debugMsg($post->value);
		} else if (is_string($value) || is_null($value)) {
			cfg_db($name,$post->value);
		} else if (is_bool($value)) {
			$value = strtoupper($post->value) == 'TRUE' || is_numeric($post->value) && $post->value > 0 ? true : false;
			//debugMsg('Save boolean '.($post->value?true:false));
			cfg_db($name, $value);
			//debugMsg(mydb()->_query);
			$newValue = $value ? 'True' : 'False';
		} else if (is_int($value)) {
			cfg_db($name,intval($post->value));
		} else if (is_numeric($value)) {
			cfg_db($name,floatval($post->value));
		}
	}

	$ret .= '<header class="header -box">'
		. '<h3'
		. ($configError ? ' style="color:red;"' : '').'>Modify'.($saved ? ' <span style="color: green;">Saved</span>' : '')
		. ($configError ? ' : ERROR!!!!</span>' : '').'</h3>'
		. '<nav class="nav">'.$ui->build().'</nav>'
		. '</header>';

	$value = cfg($name);


	$form = new Form('admin',url('admin/config/edit', array('name'=>$name)), 'admin-config-form', 'sg-form');
	$form->addData('rel', 'box');
	$form->addData('done', 'load::{url:admin/config/view}');

	
	if (is_array($value)) {
		$form->addField(
			'value',
			array(
				'label'=>'Variable value of  : '.$name.' ( Type is "'.gettype($value).'" ): ',
				'type'=>'checkbox',
				'options'=>$value,
				'multiple'=>true,
				'value'=>array_keys($value)
				)
		);

		$form->addField(
			'addkey',
			array(
				'type'=>'text',
				'label'=>'Additional Variable Key:',
				'class'=>'-fill',
				)
		);

		$form->addField(
			'addvalue',
			array(
				'type'=>'textarea',
				'label'=>'Additional Variable Value: ',
				'class'=>'-fill',
				'rows'=>3
				)
		);
		
	} else {
		if (is_object($value)) $value = sg_json_encode($value);

		$form->addField(
			'value',
			array(
				'type'=>'textarea',
				'label'=>'Variable value : '.$name.' ( Type is "'.gettype($value).'" ): ',
				'class'=>'-fill',
				'rows'=>10,
				'value'=>\SG\getFirst($newValue,$value)
				)
		);
	}

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material -white">done_all</i><span>Save configuration</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('admin/config/edit', array('name'=>$name,'restore'=>'yes')).'" data-rel="box" data-width="480" data-done="load::{url:admin/config/view}"><i class="icon -material -gray">settings_backup_restore</i><span>Reset to defaults</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();
	
	return $ret;
}
?>