<?php
function view_admin_taxonomy_form($tag=NULL,$vocab=array(),$message=NULL) {
	$ret.=$message;

	$form=new Form([
		'variable' => 'tag',
		'action' => url(q()),
		'class' => 'sg-form',
		'checkValid' => true,
	]);

	if ($vocab->hierarchy) {
		$options[0]='&lt;root&gt;';
		$tree = model::get_taxonomy_tree($vocab->vid);
		//$ret.=print_o($tree,'$tree');
		foreach ($tree as $term) {
			if ($term->tid==$tag->tid) continue;
			$options[$term->tid]=str_repeat('--', $term->depth).$term->name;
		}

		$form->addField(
							'parent',
							array(
								'type'=>'select',
								'label'=>'Parents:',
								'name'=>'tag[parent][]',
								'options'=>$options,
								'multiple'=>$vocab->hierarchy==2,
								'size'=>$vocab->hierarchy==2?6:1,
								'value'=>$tag->parent,
								'description'=>'Parent terms</a>.'
								)
							);
	}


	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'Tag name',
							'class'=>'-fill',
							'require'=>true,
							'maxlength'=>255,
							'value'=>$tag->name,
							'description'=>'The name of this tag.'
							)
						);

	$form->addField(
						'description',
						array(
							'type'=>'textarea',
							'label'=>'Description',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$tag->description,
							'description'=>'A description of the tag. To be displayed on taxonomy/term pages and RSS feeds.'
							)
						);

	$form->addField(
						'synonym',
						array(
							'type'=>'textarea',
							'label'=>'Synonyms',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>$tag->synonym,
							'description'=>'Synonyms of this tag, one synonym per line.'
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
							'value'=>SG\getFirst($tag->weight,0),
							'description'=>'Tags are displayed in ascending order by weight.</a>.'
							)
						);

	$form->addField(
						'liststyle',
						array(
							'type'=>'select',
							'label'=>'List style type:',
							'options'=>array(''=>'Default','dl'=>'Directory','div'=>'Division','table'=>'Table','ul'=>'List'),
							'value'=>$tag->liststyle,
							'description'=>'Tags are displayed in ascending order by weight.</a>.'
							)
						);

	$form->addField(
						'listclass',
						array(
							'type'=>'text',
							'label'=>'List class',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$tag->listclass,
							'description'=>'Class on topic listing.'
							)
						);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>Save Tag</span>',
						)
					);

	$ret.=$form->build();
	//$ret.=print_o($tag,'$tag');
	return $ret;
}
?>