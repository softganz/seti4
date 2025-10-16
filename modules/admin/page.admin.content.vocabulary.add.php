<?php
function admin_content_vocabulary_add($self) {
	$vocab=(object)post('vocab',_TRIM);

	if (!empty($vocab->name)) {
		if (empty($vocab->topics)) $error[]='Content types is required.';
		$isUsed=mydb::select('SELECT `vid` FROM %vocabulary% WHERE `name`=:name LIMIT 1',':name',$vocab->name)->vid;
		if ($isUsed) $error[]='Vocabulary name <b>'.$vocab->name.'</b> is inused.';

		if ($error) {
			$message=message('error',$error);
		} else {
			$vocab->hierarchy = SG\getFirst($vocab->hierarchy,0);
			$vocab->relations = SG\getFirst($vocab->relations,0);
			$vocab->tags = SG\getFirst($vocab->tags,0);
			$vocab->multiple = SG\getFirst($vocab->multiple,0);
			$vocab->required = SG\getFirst($vocab->required,0);

			$stmt='INSERT INTO %vocabulary%
				(
				  `name`, `description`, `help`, `relations`
				, `hierarchy`, `multiple`, `required`, `tags`, `weight`
				)
				VALUES
				(
				  :name, :description, :help, :relations
				, :hierarchy, :multiple, :required, :tags, :weight
				)';
			mydb::query($stmt,$vocab);
			$vid=mydb()->insert_id;
			//$ret.=mydb()->_query.'<br />';

			foreach ($vocab->topics as $type) {
				$stmt='INSERT INTO %vocabulary_types% (vid,type) VALUES (:vid, :type)';
				mydb::query($stmt,':vid',$vid, ':type',$type);
				//$ret.=mydb()->_query.'<br />';
			}

			location('admin/content/taxonomy');
			return $ret;
		}
	}


	$ret.='<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Taxonomy</h2>
<ul class="tabs primary">
<li><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li class="-active"><a href="'.url('admin/content/vocabulary/add').'">Add vocabulary</a></li>
</ul>
</div><div class="help">Define how your vocabulary will be presented to administrators and users, and which content types to categorize with it. Tags allows users to create terms when submitting posts by typing a comma separated list. Otherwise terms are chosen from a select list and can only be created by users with the "administer taxonomy" permission.</div>';

	$ret.=R::View('admin.vocabulary.form',$vocab,$message);
	return $ret;
}
?>