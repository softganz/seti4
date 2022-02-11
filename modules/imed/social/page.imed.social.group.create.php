<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_group_create($self) {
	$ret = '<header class="header"><h3 class="title -box">Create New Group</h3></header>';

	//$ret .= print_o(post(),'post()');

	$isAdmin = is_admin('imed');

	if (post('name')) {
		$data = new stdClass;
		$data->name = post('name');
		$data->officer = 'ADMIN';
		$data->created = date('U');

		if ($data->orgid) {
			if ($isAdmin) $result->orgid = $data->orgid;
		} else {
			$result = R::Model('org.create', $data);
		}
		//$ret .= print_o($data,'$data').print_o($result,'$result');

		if ($result->orgid) {
			$data->orgid = $result->orgid;
			$data->uid = i()->uid;
			$stmt = 'INSERT INTO %imed_socialgroup% (`orgid`, `uid`, `created`) VALUES (:orgid, :uid, :created)';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query;

			$stmt = 'INSERT INTO %imed_socialmember% (`orgid`, `uid`, `addby`, `membership`, `created`) VALUES (:orgid, :uid, :uid, "ADMIN", :created)';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query;
		}
	}

	$form = new Form(NULL, url('imed/social/group/create'), NULL, 'sg-form');
	//$form->addData('rel','#imed-app');
	//$form->addData('ret',url('imed/social/group'));
	//$form->addData('complete','closebox');
	$form->addData('rel','none');
	$form->addData('done', 'close | load');

	$form->addField('orgid', array('type'=>'hidden'));

	$form->addField(
		'name',
		array(
			'label' => 'Name your group',
			'type' => 'text',
			'class' => '-fill'.($isAdmin ? ' sg-autocomplete' : ''),
			'autocomplete' => 'off',
			'placeholder' => 'Name your group',
			'attr' => array(
				'data-altfld' => $isAdmin ? 'edit-orgid' : '',
				'data-query' => $isAdmin ? url('org/api/org') : ''
			),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>Create</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('imed/social').'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class' => '-sg-text-right'),
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>