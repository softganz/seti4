<?php
/**
* Create new paper
*
* @param Object $self
* @param String $type
* @param Int $tid
* @param Array $_POST['topic']
* @return String
*/

import('model:node.php');

function paper_post($self, $type = NULL, $tid = NULL) {
	$ret = '';

	if (cfg('web.readonly')) return false;

	set_time_limit(0);

	$topic = (Object) [
		'tid' => isset($tid) ? $tid : NULL,
		// get type information
		'type' => model::get_topic_type($type),
		'tag' => NULL,
		'post' => (Object) [],
	];

	// get tag information
	if ($tid) $topic->tag = model::get_taxonomy($tid);

	// class name for node module
	$moduleName = $topic->type->module;


	if (!isset($type)) {
		user_menu('home',tr('home'),url());
		user_menu('contents','contents',url('contents'));
		model::member_menu();
		if (user_access('administer papers')) user_menu('post','Create new topic',url('paper/post'));
		$self->theme->navigator = user_menu();

		$types = model::get_topic_type();
		$self->theme->title = 'Create content</h2>';
		$is_type_post = false;
		$type_str = '<h3>Choose the appropriate item from the list :</h3>'._NL.'<dl>'._NL;
		foreach ($types->items as $item) {
			if (user_access('administer contents,administer papers,create '.$item->type.' paper')) {
				$type_str .= '<dt><a href="'.url('paper/post/'.$item->type).'" title="Add a new '.$item->name.' entry.">'.$item->name.'</a></dt>'._NL;
				$type_str .= '<dd>'.$item->description.'</dd>'._NL;
				$is_type_post = true;
			}
		}
		$type_str .= '</dl>'._NL;
		if ($is_type_post) $ret .= $type_str; else $ret .= message('error','Access denied');
		return $ret;
	}



	if (!(isset($type) && isset($topic->type->type))) {
		return $ret . message('error','Invalid topic type');
	}

	// check module permission for create new node
	if (!user_access('administer contents,administer papers,create '.$type.' paper,create '.$type.' content')) {
		return message('error','Access denied');
	}

	event_tricker('paper.post.init',$self,$topic);

	// debugMsg($moduleName.'.paper.post.permission');

	if ($moduleName && !R::On($moduleName.'.paper.post.permission',$self, $type, $tid)) {
		return message('error','Access denied');
	}

	// set header text
	$self->theme->header->text = SG\getFirst($topic->type->name);

	// set header description
	$type_description = SG\getFirst($topic->tag->description,$topic->type->description);
	$self->theme->header->description = do_php($type_description);

	// set page title
	$self->theme->title = '<em>'.tr('Submit new topic in').' <strong>'.tr($topic->type->name).'</strong></em>';

	// set content class
	$self->theme->class = 'content-paper';
	$self->theme->class .= ' paper-content-'.$topic->type->type;
	if ($tid) $self->theme->class .= ' paper-tag-'.$tid;

	user_menu('home',tr('home'),url());
	user_menu('type',$topic->type->name,url('contents/'.$type));
	user_menu('new','post',url('paper/post/'.$type));


	//=====================
	// Start save post
	//=====================
	if (post('preview')) {
		$topic->post=(object)post('topic',_TRIM+_STRIPTAG);
		if ($topic->post->title) $this->theme->title=$topic->post->title;
		$ret.='<div id="topic-preview" class="preview">'._NL;
		$ret.= sg_client_convert('<h3>Post preview : หัวข้อนี้ยังไม่มีการบันทึกจนกว่าท่านจะเลือก Save เพื่อบันทึกข้อมูล</h3>');
		$ret.=sg_text2html($topic->post->body);
		$ret.='</div><!--topic-preview-->'._NL;
	} else if (post('save') || post('draft')) {
		// if set to true , simulate sql (not insert ) and show sql command
		$simulate = debug('simulate');
		$topic->post = (Object) post('topic',_TRIM+_STRIPTAG);

		//debugMsg(post(),'post()');
		// debugMsg($topic,'$topic');

		$result = NodeModel::create($topic, '{debug: false}');

		//debugMsg($result,'$result');

		if ($result->error) {
			$ret .= $result->error;
		} else {
			R::On($moduleName.'.paper.post.save', $self,$topic,$form,$result);

			model::watch_log('paper','Paper post','<a href="'.url('paper/'.$topic->tpid).'">paper/'.$topic->tpid.'</a>:'.$topic->post->title);

			/*
			if (_ON_HOST && cfg('alert.email')) {
				$mail = model::send_alert_on_new_post($self,$topic);
			}
			if (_ON_HOST && cfg('alert.twitter')) {
				$twitter = model::twitter_send(cfg('api.twitter.user'),$topic->post->title,cfg('domain').url('paper/'.$topic->tpid),null);
			}
			*/

			event_tricker('paper.post.complete',$self,$topic);

			if ($simulate) {
				$ret .= print_o($topic,'$topic');
				$ret .= print_o($result,'$result');
				$ret .= print_o($mail,'$mail');
				return $ret;
			} else {
				if ($ret) return $ret;
				R::On($moduleName.'.paper.post.complete', $self,$topic);
				location('paper/'.$topic->tpid);
			}
		}
	} else if (post('cancel')) {
		if (function_exists('module_exists') && module_exists($classname,'__post_cancel')) {
			call_user_func(array($classname,'__post_cancel'),$this,$topic,$form,$result);
		} else {
			location('paper');
		}
	} else {
		if (post('org')) $topic->org = post('org');
		$topic->post->property['input_format'] = cfg('topic.input_format');
	}



	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>สร้างหัวข้อใหม่</h3></header>';
	if ($error) $ret .= message('error',$error);


	$form = R::View('paper.post.form',$topic);

// debugMsg($form->children['tabs'],'$form');

	// do external module post form
	R::On($moduleName.'.paper.post.form',$self,$topic,$form);

	event_tricker('paper.post.form',$self,$topic,$form);

	// make form tabs
	if (!$form->children['tabs']['children']) unset($form->children['tabs']);

	$ret .= $form->build();

	if (debug('method')) $ret .= print_o($topic,'$topic').print_o($form,'$form');

	model::member_menu();

	$self->theme->navigator = user_menu();


	return $ret;
	//call_user_func_array(array($self, '__node_post'), array($type, $tid));
}
?>
