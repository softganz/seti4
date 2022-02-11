<?php
function view_admin_vocabulary_form($vocab=array(),$message=NULL) {
	$ret.=$message;

	$form=new Form([
		'variable' => 'vocab',
		'action' => url(q()),
		'class' => 'sg-form',
		'checkValid' => true,
	]);

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'Vocabulary name',
							'class'=>'-fill',
							'require'=>true,
							'maxlength'=>255,
							'value'=>$vocab->name,
							'description'=>'The name for this vocabulary, e.g., <em>"Tags"</em>'
							)
						);

	$form->addField(
						'description',
						array(
							'type'=>'textarea',
							'label'=>'Description',
							'class'=>'-fill',
							'rows'=>5,
							'value'=>$vocab->description,
							'description'=>'Description of the vocabulary; can be used by modules.'
							)
						);

	$form->addField(
						'help',
						array(
							'type'=>'text',
							'label'=>'Help text',
							'class'=>'-fill',
							'maxlength'=>128,
							'value'=>$vocab->help,
							'description'=>'Instructions to present to the user when selecting terms, e.g., "Enter a comma separated list of words".</em>'
							)
						);

	$typeList=model::get_topic_type()->items;
	$optionsType=array();
	foreach (model::get_topic_type()->items as $item) {
		$optionsType[$item->type]=$item->name;
	}
	$form->addField(
						'topics',
						array(
							'type'=>'checkbox',
							'label'=>'Content types:',
							'options'=>$optionsType,
							'value'=>$vocab->topics,
							'multiple'=>true,
							'description'=>'Select content types to categorize using this vocabulary.'
							)
						);

	$form->addField(
						'hierarchy',
						array(
							'type'=>'radio',
							'label'=>'Hierarchy:',
							'options'=>array(0=>'Disabled','Single','Multiple'),
							'value'=>$vocab->hierarchy,
							'description'=>'Allows <a href="'.url('admin/help/taxonomy').'">a tree-like hierarchy</a> between terms of this vocabulary.'
							)
						);

	$form->addField(
						'relations',
						array(
							'type'=>'checkbox',
							'options'=>array(1=>'Related terms'),
							'value'=>$vocab->relations,
							'description'=>'Allows <a href="'.url('admin/help/taxonomy').'">related terms</a> in this vocabulary.'
							)
						);

	$form->addField(
						'tags',
						array(
							'type'=>'checkbox',
							'options'=>array(1=>'Free Tags'),
							'value'=>$vocab->tags,
							'description'=>'Terms are created by users when submitting posts by typing a comma separated list.'
							)
						);

	$form->addField(
					'multiple',
					array(
						'type'=>'checkbox',
						'options'=>array(1=>'Multiple select'),
						'value'=>$vocab->multiple,
						'description'=>'Allows posts to have more than one term from this vocabulary (always true for tags).'
						)
					);

	$form->addField(
				'required',
				array(
					'type'=>'checkbox',
					'options'=>array(1=>'Required'),
					'value'=>$vocab->required,
					'description'=>'At least one term in this vocabulary must be selected when submitting a post.'
					)
				);

	$optionsWeight=array();
	for ($i=-10;$i<=10;$i++) $optionsWeight[$i]=$i;
	$form->addField(
				'weight',
				array(
					'type'=>'select',
					'label'=>'Weight:',
					'options'=>$optionsWeight,
					'value'=>SG\getFirst($vocab->weight,0),
					'description'=>'Vocabularies are displayed in ascending order by weight.'
					)
				);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>Save Vocabulary</span>',
						)
					);

	$ret.=$form->build();

	//$ret.=print_o($vocab,'$vocab');
	return $ret;
}
?>