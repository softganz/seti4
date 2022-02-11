<?php
function admin_site_mysqldump($self,$table) {
	$form=new Form(NULL,url(q()));

	$post=(object)post();

	$options=array();
	foreach (mydb::table_list() as $item) {
		$options[$item]=$item;
	}
	$form->addField(
						'tables',
						array(
							'type'=>'checkbox',
							'label'=>'Select Tables:',
							'options'=>$options,
							'multiple'=>true,
							'value'=>$post->tables,
							)
						);

	$form->addField(
					'dump',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>Start Dump File</span>',
						)
					);

	$self->theme->sidebar=$form->build();

	foreach ($post->tables as $tableName) {
		$backupFile = cfg('paper.upload.folder').$tableName.'.sql';

		unlink($backupFile);
		$ret.='<p>MySql Dump Table '.$tableName.' to file '.basename($backupFile).'</p>';

		$query="SELECT *
						INTO OUTFILE '$backupFile' FIELDS TERMINATED BY ','
						ENCLOSED BY '\"'
						LINES TERMINATED BY '),\n('
						FROM $tableName";
		$result = mydb::query($query);
		//$ret.=mydb()->_query.'<br />';
	}
					

	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>