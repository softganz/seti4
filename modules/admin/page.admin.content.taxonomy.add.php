<?php
function admin_content_taxonomy_add($self,$vid) {
	$tag=(object)post('tag');
	$parent=SG\getFirst($tag->parent,array());
	$vocab=BasicModel::get_vocabulary($vid);

	$simulate=false;
	if ($_POST) {
		if (empty($tag->name)) $error[]='Tag name field is required.';
		if (mydb::select('SELECT `tid` FROM %tag% WHERE `vid`=:vid AND `name`=:name LIMIT 1',':vid',$vid,':name',$tag->name)->tid) {
			$error[]='Tag name <b>'.$tag->name.'</b> is already defined.';
		}

		if ($error) {
			$message=message('error',$error);
		} else {
			$tag->vid=$vid;
			$tag->ownid=i()->uid;
			$stmt='INSERT INTO %tag%
							(
							  `vid`, `ownid`, `name`, `description`
							, `weight`, `liststyle`, `listclass`
							)
							VALUES
							(
							  :vid, :ownid, :name, :description
							, :weight, :liststyle, :listclass
							)';
			mydb::query($stmt,$tag);
			$tid=mydb()->insert_id;

			//$ret.=mydb()->_query.'<br />';
			//$ret.=print_o($tag,'$tag');

			if (empty($tag->parent)) $tag->parent[]=0;
			foreach ($tag->parent as $item) {
				$stmt = 'INSERT INTO %tag_hierarchy% (`tid`, `parent`) VALUES (:tid,:parent)';
				mydb::query($stmt,':tid',$tid, ':parent',$item);
			}

			if ($tag->synonym) {
				foreach (explode("\n",$tag->synonym) as $synonym) {
					$synonym=trim($synonym);
					$stmt='INSERT INTO %tag_synonym% (tid,name) VALUES (:tid,:synonym)';
					mydb::query($stmt,':tid',$tid, ':synonym',$synonym);
				}
			}

			$ret.=notify('Created new tag <em>'.$tag->name.'</em> completed.');
			$tag=$parent=NULL;
		}
	}


	$self->theme->title='Add tag to <em>'.$vocab->name.'</em>';
	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Add tag to <em>'.$vocab->name.'</em></h2>
<ul class="tabs primary">
<li><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li><a href="'.url('admin/content/taxonomy/list/'.$vid).'">List</a></li>
<li class="-active"><a href="'.url('admin/content/taxonomy/add/'.$vid).'">Add tag</a></li>
</ul>
</div><div class="help"></div>';
	$ret .= R::View('admin.taxonomy.form',$tag,$vocab,$message);
	return $ret;
}
?>