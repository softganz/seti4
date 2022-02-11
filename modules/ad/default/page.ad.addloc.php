<?php
/**
* Add new Ad Location Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad_addloc($self) {
	$ret = '';

	$post = post('loc');
	if ($post) {
		$stmt = 'INSERT INTO %ad_location% (`lid`, `description`, `width`, `height`) VALUES (:id, :desc, :width, :height)';
		mydb::query($stmt, $post);
		location('ad');
	}

	$form = new Form([
		'variable' => 'loc',
		'action' => url('ad/addloc'),
		'class' => 'sg-form',
		'title' => 'Add new Ad Location',
		'checkValid' => true,
		'children' => [
			'id' => [
				'type' => 'text',
				'label' => 'Ad Location ID',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ex. A1',
			],
			'desc' => [
				'type' => 'text',
				'label' => 'Location Description',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ex. Top of home page',
			],
			'width' => [
				'type' => 'text',
				'label' => 'Width in pixel',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ex. 200',
			],
			'height' => [
				'type' => 'text',
				'label' => 'Height in pixel',
				'class' => '-fill',
				'require' => true,
				'placeholder' => 'ex. 100',
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>Create Location</span>',
			],
		], // children
	]);

	//A1	A1 - Promotion Left	728x400

	$ret .= $form->build();
	return $ret;
}
?>