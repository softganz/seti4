<?php
function admin_content_type_edit($self,$type_id) {
	$type=(object)post('type',_TRIM);
	$type_db=CommonModel::get_topic_type($type_id);

	if ($type->name) {
		if (empty($type->name)) $error[]='Name field is required.';
		if (empty($type->type)) $error[]='Type field is required.';
		if (empty($type->title_label)) $error[]='Title field label field is required.';
		$type->topic_options=(object)$type->topic_options;
		if ($type->type && $type->type!=$type_db->type && CommonModel::get_topic_type($type->type)) $error[]='Type name <b>'.$type->type.'</b> is inused.';
		if ($error) {
			$message=message('error',$error);
		} else {
			$type->has_body=$type->body_label ? 1 : 0;

			$stmt=mydb::create_update_cmd('%topic_types%',$type,' `type`="'.$type_id.'" ');
			mydb::query($stmt,$type);

			if ($type->type!=$type_db->type) {
				$stmt='UPDATE %topic% SET `type`=:newtype WHERE type=:oldtype';
				mydb::query($stmt,':newtype',$type->type, ':oldtype',$type_id);

				$stmt='UPDATE %vocabulary_types% SET `type`=:newtype WHERE type=:oldtype';
				mydb::query($stmt,':newtype',$type->type, ':oldtype',$type_id);

				cfg_db_delete('topic_options_'.$type_id);
			}

			cfg_db('topic_options_'.$type->type,$type->topic_options);
			location('admin/content/type');
			return $ret;
		}
	} else {
		$type=$type_db;
		$type->topic_options=$type_db->topic_options;
	}

	$type->locked=$type_db->locked;
	$ret .= '<h2>'.$type_db->name.'</h2>';
	$ret.=R::View('admin.content.type.form',$type,$message);
	return $ret;
}
?>