<?php
function admin_site_upgrade($self) {
	$self->theme->title = 'Site Upgrade';
	$post = post('upgrade');
	$forceVersion = is_array($post) ? $post['func'] : [];

	if (post('cancel')) location('admin/site');
	if (post('force')) ;

	$ret.='<h3>Site Upgrade from version '.cfg('version.install').' to '.cfg('core.version.install').' release date '.cfg('core.release').'</h3>';

	if (cfg('version.install')==cfg('core.version.install')) $ret.=message('status','Congratulation, Your software is a lastest version.');

	$upgrade_folder=_CORE_FOLDER.'upgrade/';
	// $ret.='<p>Upgrade from folder '.$upgrade_folder.'</p>';
	if (!file_exists($upgrade_folder)) return $ret.message('error',$upgrade_folder.' folder not extsts.');

	set_time_limit(0); // run very long time

	$d = dir($upgrade_folder);
	while (false !== ($entry = $d->read())) {
		if ( $entry=='.' || $entry=='..' ) continue;
		$upver=substr($entry,0,strrpos($entry,'.'));
		$upgrade_file[$upver] = $entry;
	}
	asort($upgrade_file);
	$d->close();

	if ($_POST['start']) {
		$ret .= '<h3>Start upgrading....</h3>';
		set_time_limit(0);

		foreach ($upgrade_file as $upver=>$file) {
			//$ret.='$upver='.$upver.'<br />';
			if ($forceVersion && in_array($upver, $forceVersion)) {
				// donothing
			} else if ($upver<=cfg('version.install')) continue;

			$ret.='<h4>Upgrade to version '.$upver.'</h4>';
			include_once($upgrade_folder.$file);
			$ret.='<dl>';
			foreach ($result[$upver] as $upgrade_result) {
				$ret.='<dt>'.$upgrade_result[0].'</dt>';
				$ret.='<dd>'.$upgrade_result[1].'</dd>';
			}
			$ret.='</dl>'._NL;
		}
		cfg_db('version.install',cfg('core.version.install'));
		//return $ret;
	}

	$ret.='<p>Upgrade version list :</p>';
	$form=new Form([
		'variable' => 'upgrade',
		'action' => url(q()),
		'id' => 'form-upgrade',
		'children' => [
			'func' => [
				'type' => 'checkbox',
				'multiple' => true,
				'options' => (function($upgrade_file) {
					$options = [];
					foreach ($upgrade_file as $upver => $file) {
						$options[$upver]='Force upgrade to version '.$upver;
					}
					return $options;
				})($upgrade_file),
			],
			'start' => [
				'type' => 'button',
				'name' => 'start',
				'value' => '<i class="icon -material">done_all</i><span>START UPGRADE</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('admin').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret.=$form->build();

	//$ret.=print_o($forceVersion,'$forceVersion');
	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>