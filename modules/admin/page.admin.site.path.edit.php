<?php
function admin_site_path_edit($self,$id) {
	$self->theme->title='URL aliases';
	$path=mydb::select('SELECT * FROM %url_alias% WHERE `pid`=:id LIMIT 1',':id',$id);

	if (post('cancel')) location('admin/site/path');

	if (post('save') && post('path')) {
		$data=(object)post('path');
		$isUsed=mydb::select('SELECT `pid` FROM %url_alias% WHERE `pid`!=:pid AND (`alias`=:alias OR `system`=:system) LIMIT 1',':pid',$id,$data)->pid;
		if (!$isUsed) {
			$stmt='UPDATE %url_alias% SET `alias`=:alias, `system`=:system WHERE `pid`=:pid LIMIT 1';
			mydb::query($stmt, ':pid',$id, $data);
		} else {
			$ret.='<p class="notify">Duplicate alias os system path</p>';
		}
		location('admin/site/path');
	}

	$ret.='<h3>Change alias path of "'.$path->alias.'"</h3>';

	$form=new Form('path',url(q()));

	$form->addField(
		'alias',
		array(
			'type'=>'text',
			'label'=>'Path alias',
			'class'=>'-fill',
			'maxlength'=>128,
			'size'=>40,
			'require'=>true,
			'value'=>htmlspecialchars($path->alias),
			'description'=>'Specify an alternative path by which this data can be accessed. For example, type "about" when writing an about page. Use a relative path and don\'t add a trailing slash or the URL alias won\'t work.',
			)
		);

	$form->addField(
		'system',
		array(
			'type'=>'text',
			'label'=>'Existing system path',
			'class'=>'-fill',
			'maxlength'=>128,
			'size'=>40,
			'require'=>true,
			'value'=>htmlspecialchars($path->system),
			'description'=>'Specify the existing path you wish to alias. For example: paper/28, forum/1, tags/1,2.',
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
				),
			)
		);
	$ret.=$form->build();
	return $ret;
}
?>