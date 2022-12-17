<?php
function admin_content_taxonomy_edit($seld,$tid) {
	$tag=(object)post('tag');
	$db_tag=CommonModel::get_taxonomy($tid);
	$vocab=CommonModel::get_vocabulary($db_tag->vid);


	if ($_POST) {
		if (empty($tag->name)) $error[]='Tag name field is required.';
		if (mydb::select('SELECT `tid` FROM %tag% WHERE `tid`!=:tid AND `vid`=:vid and `name`=:name LIMIT 1',':tid',$tid, ':vid',$db_tag->vid, ':name',$tag->name)->tid) $error[]='Tag name <b>'.$tag->name.'</b> is already defined.';
		if ($error) {
			$message=message('error',$error);
		} else {
			// update tag detail
			mydb::query(mydb::create_update_cmd('%tag%',$tag,'tid='.$tid));
			//$ret.=mydb()->_query.'<br />';

			if ($vocab->hierarchy==1 && $tag->parent[0] != $db_tag->parent[0]) {
				// update parent on single hierarchy
				mydb::query('UPDATE %tag_hierarchy% SET `parent`=:parent WHERE `tid`=:tid LIMIT 1',':tid',$tid, ':parent',$tag->parent[0]);
					//$ret.=mydb()->_query.'<br />';
			} else if ($vocab->hierarchy==2) {
				// update parent on multiple hierarchy
				if (empty($tag->parent)) $tag->parent[]=0;
				if (empty($db_tag->parent)) $db_tag->parent[0]=0;

				//$ret.=print_o($tag,'$tag');
				//$ret.=print_o($db_tag,'$db_tag');

				// remove unselected parent
				$parent_remove=array_diff($db_tag->parent,$tag->parent);
				//$ret.=print_o($parent_remove,'$parent_remove');
				if ($parent_remove) {
					mydb::query('DELETE FROM %tag_hierarchy% WHERE tid='.$tid.' and parent in ('.implode(',',$parent_remove).')');
					//$ret.=mydb()->_query.'<br />';
				}

				// add new parent
				$parent_add=array_diff($tag->parent,$db_tag->parent);
				//$ret.=print_o($parent_add,'$parent_add');
				if ($parent_add)
					foreach ($parent_add as $item) {
						$stmt='INSERT INTO %tag_hierarchy% (tid,parent) VALUES (:tid,:parent) ON DUPLICATE KEY UPDATE `parent`=:parent';
						mydb::query($stmt,':tid',$tid, ':parent',$item);
						//$ret.=mydb()->_query.'<br />';
					}
			}

			// update tag synonym
			$old_synonym=$db_tag->synonym;
			$new_synonym=array();
			if (trim($tag->synonym)) foreach (explode("\n",trim($tag->synonym)) as $synonym) {
				$synonym=trim($synonym);
				if (empty($synonym)) continue;
				$new_synonym[]=$synonym;
			}
			$synonym_add=array_diff($new_synonym,$old_synonym);
			$synonym_remove=array_diff($old_synonym,$new_synonym);
			$synonym_remain=array_intersect($old_synonym,$new_synonym);
			$synonym_update=array();

			if ($synonym_add && $synonym_remove) {
				foreach ($synonym_add as $key=>$syname) {
					if (empty($synonym_remove)) break;
					list($rkey) = array_keys($synonym_remove);
					$synonym_update[$rkey]=$syname;
					unset($synonym_remove[$rkey]);
					unset($synonym_add[$key]);
				}
			}
			if ($synonym_remove) {
				$sql_cmd='DELETE FROM %tag_synonym% WHERE `tsid` in ('.implode(',',array_keys($synonym_remove)).')';
				mydb::query($sql_cmd);
				mydb::clear_autoid('%tag_synonym%');
			}

			if ($synonym_update) {
				foreach ($synonym_update as $key=>$syname) {
					$sql_cmd='UPDATE %tag_synonym% SET `name`="'.addslashes($syname).'" WHERE tsid='.$key.' LIMIT 1;';
					mydb::query($sql_cmd);
				}
			}

			if ($synonym_add) {
				$sql_cmd='INSERT INTO %tag_synonym% (`tid`,`name`) VALUE ';
				foreach ($synonym_add as $syname) $sql_cmd.='('.$tid.',"'.addslashes($syname).'"),';
				$sql_cmd=trim($sql_cmd,',');
				mydb::query($sql_cmd);
			}

			location('admin/content/taxonomy/list/'.$db_tag->vid);
		}
	} else {
		$tag=$db_tag;
		$tag->synonym=implode(_NL,$tag->synonym);
	}

	$ret .= '<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">'.$vocab->name.'</h2>
<ul class="tabs primary">
<li><a href="'.url('admin/content/taxonomy').'">Vocabulary</a></li>
<li><a href="'.url('admin/content/taxonomy/list/'.$vocab->vid).'">List</a></li>
<li><a href="'.url('admin/content/taxonomy/add/'.$vocab->vid).'">Add tag</a></li>
<li class="-active"><a href="'.url('admin/content/taxonomy/edit/'.$tid).'">Edit tag</a></li>
</ul>
</div><div class="help"></div>';
	$ret .= R::View('admin.taxonomy.form',$tag,$vocab,$message);
	return $ret;
}
?>