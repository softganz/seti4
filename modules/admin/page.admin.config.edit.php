<?php
/**
* Admin   :: Edit config and save config to db
* Created :: 2023-11-24
* Modify  :: 2023-11-24
* Version :: 2
*
* @param $_GET[name]
* @return Widget
*
* @usage admin/config/edit?name={configName}
*/

class AdminConfigEdit extends Page {
	var $configName;

	function __construct() {
		parent::__construct([
			'configName' => $configName = post('name'),
			'configValue' => cfg($configName),
		]);
	}

	function rightToBuild() {
		return true;
	}

	function build() {
		if (!isset($this->configName)) location('admin/config/view');

		// $ui = new Ui();
		// $ui->add('<a class="sg-action" href="'.url('admin/config/edit',array('name'=>$this->configName)).'" data-rel="box" data-width="480"><i class="icon -material">refresh</i></a>');

		// $ret .= '<header class="header -box">'
		// 	. '<h3'
		// 	. ($configError ? ' style="color:red;"' : '').'>Modify'.($saved ? ' <span style="color: green;">Saved</span>' : '')
		// 	. ($configError ? ' : ERROR!!!!</span>' : '').'</h3>'
		// 	. '<nav class="nav">'.$ui->build().'</nav>'
		// 	. '</header>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Modify configuration',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Form([
				'variable' => 'admin',
				'action' => url('admin/config/edit'._MS_.'update', ['name' => $this->configName]),
				'id' => 'admin-config-form',
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'load',
				'children' => [
					$this->formElementValue(),
					'submit' => [
						'type' => 'button',
						'value' => '<i class="icon -material -white">done_all</i><span>Save configuration</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('admin/config/edit'._MS_.'restore', ['name' => $this->configName,'restore' => 'yes']).'" data-rel="notify" data-done="load" data-title="Reset to default" data-confirm="Reset to default. Plese confirm?"><i class="icon -material -gray">settings_backup_restore</i><span>Reset to default</span></a>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}

	function formElementValue() {
		if (is_array($this->configValue)) return new FormGroup([
			'type' => 'array',
			'children' => [
				'value' => [
					'label'=>'Variable value of  : '.$this->configName.' ( Type is "'.gettype($this->configValue).'" ): ',
					'type'=>'checkbox',
					'options'=>$this->configValue,
					'multiple'=>true,
					'value'=>array_keys($this->configValue)
				],
				'addkey' => [
					'type'=>'text',
					'label'=>'Additional Variable Key:',
					'class'=>'-fill',
				],
				'addvalue' => [
					'type'=>'textarea',
					'label'=>'Additional Variable Value: ',
					'class'=>'-fill',
					'rows'=>3
				],
			], // children
		]);

		if (is_object($this->configValue)) return new FormGroup([
			'children' => [
				'value' => [
					'type' => 'textarea',
					'label' => 'Variable value : '.$this->configName.' ( Type is "'.gettype($this->configValue).'" ): ',
					'class' => '-fill',
					'rows' => 10,
					'value' => SG\json_encode($this->configValue)
				],
			], // children
		]);

		return new FormGroup([
			'children' => [
				'value' => [
					'type' => 'textarea',
					'label' => 'Variable value : '.$this->configName.' ( Type is "'.gettype($this->configValue).'" ): ',
					'class' => '-fill',
					'rows' => 10,
					'value' => $this->configValue
				],
			], // children
		]);
	}

	function restore() {
		cfg_db_delete($this->configName);
		return success('Reset to default complete.');
	}

	function update() {
		$post = (Object) post('admin');

		if (empty($post)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ข้อมูลไม่ครบถ้วน');

		$saved = true;

		if (is_array($this->configValue)) {
			foreach ($this->configValue as $key => $v) {
				if (!in_array($key,$post->value)) unset($this->configValue[$key]);
			}
			if ($post->addvalue!='') {
				if ($post->addkey=='') {
					$this->configValue[]=$post->addvalue;
				} else {
					$this->configValue[$post->addkey]=$post->addvalue;
				}
			}
			cfg_db($this->configName,$this->configValue);
		} else if (is_object($this->configValue)) {
			if ($post->value != '{}') {
				$testResult = (Array) SG\json_decode($post->value);
				if (empty($testResult)) {
					$configError = true;
					$newValue = $post->value;
					$saved = false;
				} else {
					cfg_db($this->configName,$post->value);
				}
			} else {
				cfg_db($this->configName,$post->value);
			}
			//debugMsg($post->value);
		} else if (is_string($this->configValue) || is_null($this->configValue)) {
			cfg_db($this->configName,$post->value);
		} else if (is_bool($this->configValue)) {
			$this->configValue = strtoupper($post->value) == 'TRUE' || is_numeric($post->value) && $post->value > 0 ? true : false;
			//debugMsg('Save boolean '.($post->value?true:false));
			cfg_db($this->configName, $this->configValue);
			//debugMsg(mydb()->_query);
			$newValue = $this->configValue ? 'True' : 'False';
		} else if (is_int($this->configValue)) {
			cfg_db($this->configName,intval($post->value));
		} else if (is_numeric($this->configValue)) {
			cfg_db($this->configName,floatval($post->value));
		}

		return $saved ? success('บันทึกเรียบร้อย') : error(_HTTP_ERROR_NOT_ACCEPTABLE, 'รูปแบบไม่ถูกต้อง');
	}
}
?>