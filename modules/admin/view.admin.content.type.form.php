<?php
function view_admin_content_type_form($type=NULL,$message=NULL) {
	if ($message) $ret .= $message;

	$form = new Form([
		'variable' => 'type',
		'action' => url(q()),
		'id' => 'topic-type-form',
		'class' => 'sg-form',
		'checkValid' => true,
		'children' => [
			// Identification
			'<fieldset><legend>Identification</legend>',
			'name' => [
				'type' => 'text',
				'label' => 'Name: ',
				'maxlength' => 128,
				'class' => '-fill',
				'require' => true,
				'value' => $type->name,
				'description' => 'The human-readable name of this content type. This text will be displayed as part of the list on the <em>create content</em> page. It is recommended that this name begins with a capital letter and consists only of letters, numbers, and <strong>spaces</strong>. This name must be unique to this content type.',
			],
			'type' => [
				'type' => 'text',
				'label' => 'Type: ',
				'maxlength' => 32,
				'class' => '-fill',
				'require' => true,
				'readonly' => $type->locked ? true : false,
				'value' => $type->type,
				'description' => 'The machine-readable name of this content type. This text will be used for constructing the URL of the <em>create content</em> page for this content type. This name may consist of only of lowercase letters, numbers, and underscores. Dashes are not allowed. Underscores will be converted into dashes when constructing the URL of the <em>create content</em> page. This name must be unique to this content type.',
			],
			'description' => [
				'type' => 'textarea',
				'label' => 'Description: ',
				'rows' => 5,
				'class' => '-fill',
				'value' => $type->description,
				'description' => 'A brief description of this content type. This text will be displayed as part of the list on the <em>create content</em> page.',
			],
			'</fieldset>',

			// Submission form
			'<fieldset><legend>Submission form</legend>',
			'title_label' => [
				'type' => 'text',
				'label' => 'Title field label: ',
				'require' => true,
				'maxlength' => 128,
				'class' => '-fill',
				'value' => htmlspecialchars($type->title_label),
			],
			'body_label' => [
				'type' => 'text',
				'label' => 'Body field label: ',
				'maxlength' => 200,
				'class' => '-fill',
				'value' => htmlspecialchars($type->body_label),
				'description' => 'To omit the body field for this content type, remove any text and leave this field blank.',
			],
			'min_word_count' => [
				'type' => 'select',
				'label' => 'Minimum number of words: ',
				'value' => $type->min_word_count,
				'options' => [0=>0,10=>10,25=>25,50=>50,75=>75,100=>100,125=>125,150=>150,175=>175,200=>200],
				'description' => 'The minimum number of words for the body field to be considered valid for this content type. This can be useful to rule out submissions that do not meet the site\'s standards, such as short test posts.',
			],
			'help' => [
				'type' => 'textarea',
				'label' => 'Explanation or submission guidelines: ',
				'class' => '-fill',
				'rows' => 5,
				'value' => $type->help,
				'description' => 'This text will be displayed at the top of the submission form for this content type. It is useful for helping or instructing your users.',
			],
			'</fieldset>',

			// Work flow
			'<fieldset><legend>Workflow</legend>',
			'topic_options_publish' => [
				'type' => 'checkbox',
				'label' => 'Default options: ',
				'name' => 'type[topic_options][publish]',
				'options' => ['publish' => 'Published'],
				'value' => $type->topic_options->publish,
			],
			'topic_options_promote' => [
				'type' => 'checkbox',
				'name' => 'type[topic_options][promote]',
				'options' => ['promote' => 'Promoted to front page'],
				'value' => $type->topic_options->promote,
			],
			'topic_options_sticky' => [
				'type' => 'checkbox',
				'name' => 'type[topic_options][sticky]',
				'options' => ['sticky' => 'Sticky at top of lists'],
				'value' => $type->topic_options->sticky,
			],
			'topic_options_revision' => [
				'type' => 'checkbox',
				'name' => 'type[topic_options][revision]',
				'options' => ['revision' => 'Create new revision'],
				'value' => $type->topic_options->revision,
			],
			'topic_options_comment' => [
				'type' => 'radio',
				'name' => 'type[topic_options][comment]',
				'label' => 'Default comment setting: ',
				'options' => [0 => 'Disabled', 1 => 'Read only', 2 => 'Read/Write'],
				'value' => $type->topic_options->comment,
				'description' => 'Users with the <em>administer comments</em> permission will be able to override this setting.',
			],
			'</fieldset>',

			// Setting
			'<fieldset><legend>Setting</legend>',
			'These settings allow you to adjust the display of your forum topics. The content types available for use within a forum may be selected by editing the Content types on the forum vocabulary page.',
			'hottopics' => [
				'type' => 'select',
				'name' => 'type[topic_options][hot_topic]',
				'label' => 'Hot topic threshold: ',
				'value' => \SG\getFirst($type->topic_options->hot_topic,15),
				'options' => [5=>5,10=>10,15=>15,20=>20,25=>25,30=>30,35=>35,40=>40,45=>45,50=>50,60=>60,70=>70,80=>80,90=>90,100=>100,150=>150,200=>200,250=>250,300=>300,350=>350,400=>400,500=>500],
				'description' => 'The number of posts a topic must have to be considered "hot".',
			],
			'topicsperpage' => [
				'type' => 'select',
				'name' => 'type[topic_options][per_page]',
				'label' => 'Topics per page: ',
				'value' => \SG\getFirst($type->topic_options->per_page,25),
				'options' => [10=>10,15=>15,20=>20,25=>25,30=>30,40=>40,50=>50,60=>60,70=>70,80=>80,90=>90,100=>100,200=>200],
				'description' => 'Default number of forum topics displayed per page.',
			],
			'topic_options_order' => [
				'type' => 'radio',
				'name' => 'type[topic_options][order]',
				'label' => 'Default order:  ',
				'options' => [
					1 => 'Date - newest first',
					2 => 'Date - oldest first',
					3 => 'Posts - most active first',
					4 => 'Posts - least active first',
				],
				'value' => \SG\getFirst($type->topic_options->order,1),
				'description' => 'Default display order for topics.',
			],
			'</fieldset>',

			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -material">done_all</i><span>Save content type</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>