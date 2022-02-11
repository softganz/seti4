<?php
function project_issue($self,$tpid=NULL,$action=NULL,$trid=NULL) {
	$stmt='SELECT * FROM %tag% WHERE `taggroup`="project:planning"';
	$dbs=mydb::select($stmt);
	foreach ($dbs->items as $rs) {
		$options[$rs->catid]=$rs->name;
	}
	$form=new Form();
	$form->addField(
						'issue',
						array('type'=>'checkbox','options'=>$options)
					);
	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
						'posttext'=>'<a href="">{tr:Cancel}</a>',
						)
					);

	$ret.=$form->build();
	//$ret.=print_o($options,'$options');
	//$ret.=print_o($dbs);
	return $ret;
}
?>