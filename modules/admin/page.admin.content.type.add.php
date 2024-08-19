<?php
/**
* Admin   :: Add Content Type
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 3
*
* @return Widget
*
* @usage admin/content/type/add
*/

class AdminContentTypeAdd extends Page {

	function __construct() {
		parent::__construct([
		]);
	}

	function build() {
		$type=(object)post('type');

		if ($type->name) {
			if (empty($type->name)) $error[]='Name field is required.';
			if (empty($type->type)) $error[]='Type field is required.';
			if (empty($type->title_label)) $error[]='Title field label field is required.';
			if (BasicModel::get_topic_type($type->type)) $error[]='Type name <b>'.$type->type.'</b> is inused.';

			if ($error) {
				$message=message('error',$error);
			} else {
				$type->module='paper';
				$type->has_title=$type->title_label ? 1 : 0;
				$type->has_body=$type->body_label ? 1 : 0;
				$type->custom=1;
				$type->modified=1;
				$ret.=print_o($type,'$type');
				$stmt=mydb::create_insert_cmd('topic_types',$type);
				mydb::query($stmt,$type);
				cfg_db('topic_options_'.$type->type,(object)$type->topic_options);

				location('admin/content/type');
				return $ret;
			}
		} else {
			$type->title_label='Title';
			$type->topic_options=array('publish'=>'publish','promote'=>'promote');
			$type->topic_options['comment']=2;
		}

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Content types'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					'<div id="tabs-wrapper" class="clear-block"><h2 class="with-tabs">Content types</h2>
					<ul class="tabs primary">
					<li><a href="'.url('admin/content/type').'">List</a></li>
					<li class="-active"><a href="'.url('admin/content/type/add').'">Add content type</a></li>
					</ul>
					</div><div class="help"></div>',
					R::View('admin.content.type.form',$type,$message),
				], // children
			]), // Widget
		]);
	}
}
?>