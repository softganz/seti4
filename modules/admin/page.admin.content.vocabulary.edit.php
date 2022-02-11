<?php
function admin_content_vocabulary_edit($self,$vid) {
	//$this->nav.=$this->sep.' <a href="'.url('admin/content/taxonomy').'">Categories</a> ';
	$db_vocab=model::get_vocabulary($vid);

	/*
	$db_vocab=(array) mydb::select('SELECT * FROM %vocabulary% WHERE vid='.$vid.' LIMIT 1');
	$topics=mydb::select('SELECT type FROM %vocabulary_types% WHERE vid='.$vid);
	$db_topic=array();
	foreach ($topics->items as $item) $db_topic[$item->type]=$item->type;
	*/

	$vocab=(object)post('vocab',_TRIM);
	//$ret.=print_o($db_vocab,'$db_vocab');
	//$ret.=print_o($vocab,'$vocab');

	if (!empty($vocab->name)) {
		if (empty($vocab->name)) $error[]='Vocabulary name field is required.';
		if ($vocab->name != $db_vocab->name
			&& mydb::select('SELECT `vid` FROM %vocabulary% WHERE `name`=:name LIMIT 1',':name',$vocab->name)->vid) {
			$error[]='Vocabulary name <b>'.$vocab->name.'</b> is inused.';
		}
		if ($error) {
			$message=message('error',$error);
		} else {
			// update vocabulary
			$vocab->hierarchy=SG\getFirst($vocab->hierarchy,0);
			$vocab->relations=SG\getFirst($vocab->relations,0);
			$vocab->tags=SG\getFirst($vocab->tags,0);
			$vocab->multiple=SG\getFirst($vocab->multiple,0);
			$vocab->required=SG\getFirst($vocab->required,0);
			$vocab->vid=$vid;
			$stmt='UPDATE %vocabulary% SET
							  `name`=:name, `description`=:description
							, `help`=:help, `hierarchy`=:hierarchy
							, `weight`=:weight, `tags`=:tags, `multiple`=:multiple
							, `required`=:required, `relations`=:relations
							WHERE `vid`=:vid LIMIT 1';
			mydb::query($stmt,$vocab);
			//$ret.=mydb()->_query.'<br />';

			if (empty($vocab->topics)) $vocab->topics=array();
			if (empty($db_vocab->topics)) $db_vocab->topics=array();

			$topic_remove=array_diff($db_vocab->topics,$vocab->topics);
			//$ret.=print_o($topic_remove,'$topic_remove');

			// remove vocabulary_types
			if ($topic_remove) {
				mydb::query('DELETE FROM %vocabulary_types% WHERE `vid`=:vid and `type` in ("'.implode('","',$topic_remove).'")',':vid',$vid);
				//$ret.=mydb()->_query.'<br />';
			}

			// add vocabulary_types
			$topic_add=array_diff($vocab->topics,$db_vocab->topics);

			//$ret.=print_o($topic_add,'$topic_add');
			if ($topic_add) {
				foreach ($topic_add as $item) {
					mydb::query('INSERT INTO %vocabulary_types% (`vid`,`type`) VALUES (:vid, :type)',':vid',$vid, ':type', $item);
					//$ret.=mydb()->_query.'<br />';
				}
			}
			location('admin/content/taxonomy');
			return $ret;
		}
	} else {
		$vocab=$db_vocab;
	}
	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Categories</h2>
<ul class="tabs primary">
<li><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li><a href="'.url('admin/content/vocabulary/add').'">Add vocabulary</a></li>
<li class="-active"><a href="'.url('admin/content/vocabulary/edit/'.$vid).'">Edit vocabulary</a></li>
</ul>
</div><div class="help"></div>';
	$ret.=R::View('admin.vocabulary.form',$vocab,$message);
	return $ret;
}
?>