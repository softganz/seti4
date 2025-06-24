<?php
/**
* Paper   :: Create New Paper
* Created :: 2025-06-24
* Modify  :: 2025-06-24
* Version :: 3
*
* @param String $type
* @return Widget
*
* @usage module/{Id}/method
*/

class PaperPost extends Page {
	var $type;
	var $tagId;
	var $orgId;

	function __construct($type = NULL, $tagId = NULL) {
		parent::__construct([
			'type' => $type,
			'tagId' => $tagId,
			'orgId' => SG\getFirstInt(post('org')),
		]);
	}

	function build() {
		if (cfg('web.readonly')) return false;

		if (!isset($this->type)) return $this->selectType();

		set_time_limit(0);

		$topic = (Object) [
			'tid' => isset($this->tagId) ? $this->tagId : NULL,
			'type' => BasicModel::get_topic_type($this->type), // get type information
			'tag' => NULL,
			'post' => (Object) [],
		];

		// get tag information
		if ($this->tagId) $topic->tag = BasicModel::get_taxonomy($this->tagId);

		// class name for node module
		$moduleName = $topic->type->module;

		if (!(isset($this->type) && isset($topic->type->type))) {
			return $ret . message('error','Invalid topic type');
		}

		// check module permission for create new node
		if (!user_access('administer contents,administer papers,create '.$this->type.' paper,create '.$this->type.' content')) {
			return message('error','Access denied');
		}

		event_tricker('paper.post.init',$this,$topic);

		// debugMsg($moduleName.'.paper.post.permission');

		if ($moduleName && !R::On($moduleName.'.paper.post.permission',$this, $this->type, $this->tagId)) {
			return message('error','Access denied');
		}

		// set header text
		// $self->theme->header->text = \SG\getFirst($topic->type->name);

		// set header description
		$type_description = \SG\getFirst($topic->tag->description,$topic->type->description);
		// $self->theme->header->description = do_php($type_description);

		// set page title
		// $self->theme->title = '<em>'.tr('Submit new topic in').' <strong>'.tr($topic->type->name).'</strong></em>';

		// set content class

		user_menu('home',tr('home'),url());
		user_menu('type',$topic->type->name,url('contents/'.$this->type));
		user_menu('new','post',url('paper/post/'.$this->type));

		//=====================
		// Start save post
		//=====================
		if (post('preview')) return $this->preview();
		else if (post('save') || post('draft')) return $this->save($topic, $moduleName);
		else if (post('cancel')) return $this->cancel($result);

		if ($this->orgId) $topic->org = $this->orgId;
		$topic->post->property['input_format'] = cfg('topic.input_format');

		// $ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>สร้างหัวข้อใหม่</h3></header>';
		// if ($error) $ret .= message('error',$error);


		$form = new PaperPostFormWidget($topic);

		// debugMsg($form,'$form');

		// do external module post form
		R::On($moduleName.'.paper.post.form', $this, $topic, $form);

		event_tricker('paper.post.form', $this, $topic, $form);

		// make form tabs
		// if (!$form->children['tabs']['children']) unset($form->children['tabs']);

		BasicModel::member_menu();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างหัวข้อใหม่',
				'navigator' => user_menu(),
			]), // AppBar
			'body' => new Container([
				'class' => 'content-paper paper-content-'.$topic->type->type.($this->tagId ? ' paper-tag-'.$this->tagId : ''),
				'children' => [
					$form,

					debug('method') ? new DebugMsg($topic, '$topic') : NULL,
					debug('method') ? new DebugMsg($form, '$form') : NULL,
				], // children
			]), // Container
		]);
	}

	private function selectType() {
		user_menu('home', tr('home'), url());
		user_menu('contents', 'contents', url('contents'));
		BasicModel::member_menu();
		if (user_access('administer papers')) user_menu('post', 'Create new topic', url('paper/post'));

		// $types = BasicModel::get_topic_type();
		// // $self->theme->title = 'Create content</h2>';
		// $is_type_post = false;
		// $type_str = '<h3>Choose the appropriate item from the list :</h3>'._NL.'<dl>'._NL;
		// foreach ($types->items as $item) {
		// 	if (user_access('administer contents,administer papers,create '.$item->type.' paper')) {
		// 		$type_str .= '<dt><a href="'.url('paper/post/'.$item->type).'" title="Add a new '.$item->name.' entry.">'.$item->name.'</a></dt>'._NL;
		// 		$type_str .= '<dd>'.$item->description.'</dd>'._NL;
		// 		$is_type_post = true;
		// 	}
		// }
		// $type_str .= '</dl>'._NL;
		// if ($is_type_post) $ret .= $type_str; else $ret .= message('error','Access denied');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Create content',
				'navigator' => user_menu(),
			]),
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Choose the appropriate item from the list :']),
					new Nav([
						'direction' => 'vertical',
						'class' => '-sg-paddingnorm',
						'children' => array_map(
							function($type) {
								if (!user_access('administer contents,administer papers,create '.$type->type.' paper')) return NULL;

								return new Button([
									'type' => 'secondary',
									'href' => url('paper/post/'.$type->type),
									'text' => $type->name,
									'icon' => new Icon('newspaper'),
									'title' => 'Add a new '.$type->name.' entry.',
									'description' => $type->description
								]);
							},
							(Array) BasicModel::get_topic_type()->items
						)
					])
				]
			]), // Widget
		]);		
	}

	private function save($topic, $moduleName) {
		// if set to true , simulate sql (not insert ) and show sql command
		$simulate = debug('simulate');
		$topic->post = (Object) post('topic',_TRIM+_STRIPTAG);

		$result = NodeModel::create($topic, '{debug: false}');

		if ($result->error) {
			$ret .= $result->error;
		} else {
			R::On($moduleName.'.paper.post.save', $this,$topic,$form,$result);

			LogModel::save([
				'module' => 'paper',
				'keyword' => 'Paper post',
				'message' => '<a href="'.url('paper/'.$topic->tpid).'">paper/'.$topic->tpid.'</a>:'.$topic->post->title
			]);

			/*
			if (_ON_HOST && cfg('alert.email')) {
				$mail = BasicModel::send_alert_on_new_post($this,$topic);
			}
			if (_ON_HOST && cfg('alert.twitter')) {
				$twitter = BasicModel::twitter_send(cfg('api.twitter.user'),$topic->post->title,cfg('domain').url('paper/'.$topic->tpid),null);
			}
			*/

			event_tricker('paper.post.complete',$this,$topic);

			if ($simulate) {
				$ret .= print_o($topic,'$topic');
				$ret .= print_o($result,'$result');
				$ret .= print_o($mail,'$mail');
				return $ret;
			} else {
				if ($ret) return $ret;
				R::On($moduleName.'.paper.post.complete', $this,$topic);
				location('paper/'.$topic->tpid);
			}
		}
	}

	private function preview() {
		$ret = '<div id="topic-preview" class="preview">'._NL;
		$ret .= '<h3>Post preview : หัวข้อนี้ยังไม่มีการบันทึกจนกว่าท่านจะเลือก Save เพื่อบันทึกข้อมูล</h3>';
		$ret .= sg_text2html(post('body'));
		$ret .= '</div><!--topic-preview-->'._NL;
		return $ret;
	}

	private function cancel($topic) {
		if (function_exists('module_exists') && module_exists($classname,'__post_cancel')) {
			call_user_func(array($classname,'__post_cancel'),$this,$topic,$form,$result);
		} else {
			location('paper');
		}
	}
}
?>
