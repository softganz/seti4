<?php
/**
* View Paper
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @return String
*/

function paper_view($self, $tpid = NULL, $action = NULL) {
	$para = (Object) [
		'commentPage' => post('page'),
	];

	event_tricker('paper.view.init',$self,$topicInfo,$para);

	$topicInfo = is_object($tpid) ? $tpid : R::Model('paper.get',$tpid);
	//paper_BasicModel::get_topic_by_id($tpid);//R::Model('paper.get',$tpid,$para);
	$tpid = $topicInfo->tpid;
	$body = (Object) [];

	$isAdminPaper = user_access('administer contents,administer papers');
	$isEditPaper = user_access('administer contents,administer papers','edit own paper',$topicInfo->uid);
	// debugMsg($self,'$self');

	// $self = (Object) [
	// 	'theme' => $topicInfo->property->option,
	// 	'header' => (Object) [],
	// 	'ribbon' => (Object) [],
	// ];
	$self->theme->option = $topicInfo->property->option;
	if ($topicInfo->property->option->secondary === false) cfg('web.secondary',false);
	if ($topicInfo->property->option->fullpage === true) cfg('web.fullpage',true);
	event_tricker('paper.view.load',$self,$topicInfo,$para);
	// debugMsg($self,'$self');
	// debugMsg($topicInfo,'$topicInfo');


	// Trick method __view_load of content type
	if (function_exists('module2classname') && module_exists($topicInfo->info->module,'__view_load')) {
		$r=call_user_func(array(module2classname($topicInfo->info->module),'__view_load'),$self,$topicInfo,$para,$body);
	}

	if (debug('method')) $body->debug = print_o($para,'$para').print_o($topicInfo,'$topicInfo');

	if (!$topicInfo) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return $body->debug.message('error','TOPIC NOT FOUND.');
	}


	// echo 'TPID = '.$tpid.'<br />';
	$isTopicUser = mydb::table_exists('%topic_user%') && mydb::select('SELECT `uid` FROM %topic_user% WHERE `tpid` = :tpid AND `uid` = :uid AND `membership` IN ("Owner","Trainer","Manager") LIMIT 1',':tpid',$tpid,':uid',i()->uid)->uid;

	if (in_array($topicInfo->info->status,array(_DRAFT,_WAITING))
			&& !(user_access('administer contents,administer papers')
					|| $topicInfo->uid==i()->uid
					|| $isTopicUser)
			) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		head('googlead','<script></script>');
		return $body->debug.message('error','Access denied');
	} else if ($topicInfo->info->status == _BLOCK && !user_access('administer contents,administer papers,administer '.$topicInfo->info->fid.' paper')) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		head('googlead','<script></script>');
		return $body->debug.message('error','This topic was blocked.');
	} else if ($topicInfo->uid && !in_array($topicInfo->info->owner_status, array('enable','locked'))) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		if (!user_access('administer contents,administer papers,administer '.$topicInfo->info->fid.' paper')) return $body->debug.message('error','Owner of this topic was blocked.');
	} else if ($topicInfo->info->access == 'member') {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return $body->debug.message('error','This topic for member only.');
	} else if ($topicInfo->info->access == 'public') {
		/* do nothing */
	} else if (!user_access('access papers')) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return $body->debug.message('error','Access denied');
	} else if (cfg('topic.close.day') && $topicInfo->info->created < date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d')-cfg('topic.close.day'),date('Y'))) && $topicInfo->info->type != 'page') {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		if (user_access('administer contents')) {
			$topicInfo->title .= ' - This topic was closed.';
		} else {
			return $body->debug.message('error','This topic was closed.');
		}
	}



	// check duplicate content
	if (q() != 'paper/'.$tpid || count($_GET) > 1)  head('<link rel="canonical" href="'.cfg('domain').url('paper/'.$tpid).'" />');


	if ($topicInfo->tags) {
		foreach ($topicInfo->tags as $tvalue) $tags[$tvalue->tid]=$tvalue->name;
	}

	$self->theme->title = $topicInfo->title
		. ($topicInfo->info->status == _PUBLISH || $topicInfo->info->status > -_LOCK ? '' : ' <em>('.sg_status_text($topicInfo->info->status).')</em>');

	$self->theme->id = 'paper-id-'.$tpid;
	$self->theme->class = 'content-paper content-paper-view';
	$self->theme->class .= ' paper-status-'.sg_status_text($topicInfo->info->status);
	if ($topicInfo->archived) $self->theme->class .= ' paper-archive';
	$self->theme->class .= ' paper-content-'.$topicInfo->info->type;
	if ($topicInfo->tags) {
		foreach ($topicInfo->tags as $tag) $self->theme->class .= ' paper-tag-'.$tag->tid;
	}
	if ($topicInfo->info->category) $self->theme->class .= ' paper-category-'.$topicInfo->info->category;

	// debugMsg('AAA = '.SG\getFirst($topicInfo->tags[0]->name,$topicInfo->info->category,$topicInfo->info->forum));
	// $self->theme->header->text = SG\getFirst($topicInfo->tags[0]->name,$topicInfo->info->category,$topicInfo->info->forum,' ');
	$description = SG\getFirst($topicInfo->tags[0]->description,$topicInfo->info->type_description);
	if ($description) {
		ob_start();
		eval ('?>'.$description);
		$self->theme->header->description = ob_get_clean();
	}

	//$self->theme->header->description.='AAA'.$description;

	// set user request comment sort order
	if (array_key_exists('change_comment_order',$_GET)) {
		$comment_order = SG\getFirst($_COOKIE['corder'],cfg('comment.order')) == 'ASC' ? 'DESC' : 'ASC';
		setcookie('corder',$comment_order,time()+60*60*24*365*10, cfg('cookie.path'),cfg('cookie.domain'));
		cfg('comment.order',$comment_order);
	}

	if ($topicInfo->archived) {
		user_menu(
			'edit',
			'<i class="icon -material -sg-16">archive</i>',
			$isAdminPaper ? url('paper/'.$tpid.'/edit/archive') : 'javascript:void(0)',
			'{title: "'.($isAdminPaper ? ' Click to Un' : '').'Archive topic"}'
		);
	} else if ($isEditPaper) {
		$self->theme->option->ribbon = true;
		$self->theme->ribbon->class = 'navigator-manage';
		user_menu(
			'edit',
			'<i class="icon -material -gray -sg-16">edit</i>',
			url('paper/'.$tpid.'/edit'),
			'title','Edit topic detail'
		);
		user_menu(
			'edit',
			'edit_main',
			'จัดการเอกสาร',url('paper/'.$tpid.'/edit.main'),
			'{"class": "sg-action", "data-rel": "box", "data-width":480}'
		);
		user_menu(
			'edit',
			'edit_detail',
			'รายละเอียด',
			url('paper/'.$tpid.'/edit.detail'),
			'{"class": "sg-action", "data-rel": "box"}'
		);

		if (user_access('upload photo'))
			user_menu(
				'edit',
				'edit_photo',
				'ภาพประกอบ',
				url('paper/'.$tpid.'/edit.photo')
			);

		if (user_access('upload document'))
			user_menu(
				'edit',
				'add_doc',
				'เอกสารประกอบ',
				url('paper/'.$tpid.'/edit.docs')
			);

		user_menu(
			'edit',
			'edit_property',
			'รูปแบบการแสดงผล',
			url('paper/'.$tpid.'/edit.prop')
		);

		if ($isAdminPaper || user_access('administer paper tags'))
			user_menu(
				'edit',
				'move_forum',
				'จัดการหมวด',
				url('paper/'.$tpid.'/edit.tag')
			);

		if (module_install('poll') && user_access('administer pools,create poll'))
			user_menu(
				'edit',
				'make_poll',
				'สร้างแบบสำรวจความคิดเห็น',
				url('paper/'.$tpid.'/edit.makepoll')
			);

		user_menu(
			'edit',
			'edit_comment',
			($topicInfo->info->comment ? 'ไม่ให้มี' : 'อนุญาตให้มี').'การแสดงความคิดเห็น',
			url('paper/'.$tpid.'/edit.nocomment')
		);

		if ($isAdminPaper && $topicInfo->_content_type_property->revision)
			user_menu(
				'edit',
				'revision',
				'Revisions',
				url('paper/'.$tpid.'/revision')
			);

		if ($topicInfo->info->status < _LOCK)
			user_menu(
				'delete',
				'<i class="icon -material -gray -sg-16">delete</i>',
				url('paper/'.$tpid.'/delete'),
				'{"class": "sg-action", "data-rel": "box", "data-width" : 480, "title": "Delete topic"}'
			);
	}

	user_menu('home',tr('home'),url());




	if ($topicInfo->info->module) {
		user_menu('type',$topicInfo->info->type_name,url($topicInfo->info->module));
	} else if ($topicInfo->info->type) {
		user_menu('type',$topicInfo->info->type_name,url('contents/'.$topicInfo->info->type));
	}

	if (isset($topicInfo->tags[0])) user_menu('tag',$topicInfo->tags[0]->name,url('tags/'.$topicInfo->tags[0]->tid));
	user_menu('paper_id',$tpid,url('paper/'.$tpid));

	BasicModel::member_menu();



	if ($topicInfo->info->type != 'page' && user_access('administer contents,administer papers,create '.$topicInfo->info->type.' paper,create '.$topicInfo->info->type.' content')) user_menu('new',tr('Create new topic'),url('paper/post/'.$topicInfo->info->type));
	user_menu('qrcode','<i class="icon -material">qr_code</i>', url('qrcode/gen',['url' => url('paper/'.$tpid)]));

	// Process module init
	if ($topicInfo->info->type) R::On($topicInfo->info->module.'.paper.view.init',$self,$topicInfo,$para,$body);


	// Process paper edit command
	if (preg_match('/^edit/i', $action)) {
		$self->theme->navigator = user_menu();
		$isEdit = $topicInfo->RIGHT & _IS_EDITABLE;
		if (!$isEdit) return message('error', 'Access denied');
		$self->theme->sidebar = R::View('paper.edit.menu', $tpid);
		$ret .= R::Page('paper.'.$action, $self, $topicInfo, func_get_arg(3),func_get_arg(4));
		return $ret;
	} else if ($action) {
		$self->theme->navigator = user_menu();
		$actionRet = R::Page('paper.'.$action, $self, $topicInfo, func_get_arg(3),func_get_arg(4));
		if ($actionRet) return $ret.$actionRet;
	}




	// Start view content

	R::Model('reaction.add',$tpid,'TOPIC.VIEW');

	if (!in_array($topicInfo->info->type,array('page','ibuy','project','project-dev'))) {
		$ret .= '<nav class="nav -page -sg-text-right -no-print">'.R::Page('paper.like.status', NULL, $tpid).'</nav>';
	}


	++$topicInfo->info->view;

	// The Open Graph Protocol
	$opengraph = (Object)[];
	$opengraph->title = $topicInfo->title;
	$opengraph->type = 'website';
	$opengraph->url = is_home() ? url() : url('paper/'.$tpid);
	if ($topicInfo->photo->items[0]->_url) $opengraph->image = $_SERVER['REQUEST_SCHEME'].':'.$topicInfo->photo->items[0]->_url;
	$opengraph->description = sg_summary_text($topicInfo->body);
	sg::add_opengraph($opengraph);

	if ($topicInfo->uid && $topicInfo->info->owner_status != 'enable') $body->status .= message('error','This owner was block');

	event_tricker('paper.view.start',$self,$topicInfo,$para);

	if ($topicInfo->info->module) R::Manifest($topicInfo->info->module);

	// Prepare paper content
	$body = object_merge($body, R::View('paper.content.prepare', $topicInfo, $para));



	// do external view from module
	if ($topicInfo->info->module) {
		// do external view from file on.module.paper.view
		$onViewResult = R::On($topicInfo->info->module.'.paper.view',$self,$topicInfo,$para,$body);
	} else if (function_exists('module2classname') && module_exists($topicInfo->info->module,'__view')) {
		call_user_func(array(module2classname($topicInfo->info->module),'__view'),$self,$topicInfo,$para,$body);
	}

	// set user menu to theme
	$self->theme->navigator = user_menu();

	if ($topicInfo->archived) unset($body->comment_form);
	foreach ($body as $str) {
		if (is_string($str)) $ret .= $str;
	}

	// do eventricker paper.view.complete
	event_tricker('paper.view.complete',$self,$topicInfo,$para);

	unset($self->theme->body);

	//$ret .= print_o($topicInfo,'$topicInfo');
	// debugMsg($self,'$self');

	return $ret;
}



/**
* Module :: Description
* Created 2021-10-14
* Modify  2021-10-14
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class XPaperView extends Page {
	var $tpid;
	var $action;
	var $para;

	function __construct($tpid, $action = NULL) {
		$this->tpid = $tpid;
		$this->action = $action;
		$this->para = 	$para = para(func_get_args(),2);
	}

	function build() {
		$para = $this->para;
		event_tricker('paper.view.init',$self,$topicInfo,$para);


		$topicInfo = is_object($tpid) ? $tpid : R::Model('paper.get',$tpid);
		//paper_BasicModel::get_topic_by_id($tpid);//R::Model('paper.get',$tpid,$para);
		$tpid = $topicInfo->tpid;

		$isAdminPaper = user_access('administer contents,administer papers');
		$isEditPaper = user_access('administer contents,administer papers','edit own paper',$topicInfo->uid);

		$self->theme->option = $topicInfo->property->option;
		if ($topicInfo->property->option->secondary === false) cfg('web.secondary',false);
		if ($topicInfo->property->option->fullpage === true) cfg('web.fullpage',true);
		event_tricker('paper.view.load',$self,$topicInfo,$para);


		// Trick method __view_load of content type
		if (function_exists('module2classname') && module_exists($topicInfo->info->module,'__view_load')) {
			$r=call_user_func(array(module2classname($topicInfo->info->module),'__view_load'),$self,$topicInfo,$para,$body);
		}

		if (debug('method')) $body->debug = print_o($para,'$para').print_o($topicInfo,'$topicInfo');

		if (!$topicInfo) {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			return $body->debug.message('error','TOPIC NOT FOUND.');
		}


		// echo 'TPID = '.$tpid.'<br />';
		$isTopicUser = mydb::table_exists('%topic_user%') && mydb::select('SELECT `uid` FROM %topic_user% WHERE `tpid` = :tpid AND `uid` = :uid AND `membership` IN ("Owner","Trainer","Manager") LIMIT 1',':tpid',$tpid,':uid',i()->uid)->uid;

		if (in_array($topicInfo->info->status,array(_DRAFT,_WAITING))
				&& !(user_access('administer contents,administer papers')
						|| $topicInfo->uid==i()->uid
						|| $isTopicUser)
				) {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			head('googlead','<script></script>');
			return $body->debug.message('error','Access denied');
		} else if ($topicInfo->info->status == _BLOCK && !user_access('administer contents,administer papers,administer '.$topicInfo->info->fid.' paper')) {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			head('googlead','<script></script>');
			return $body->debug.message('error','This topic was blocked.');
		} else if ($topicInfo->uid && !in_array($topicInfo->info->owner_status, array('enable','locked'))) {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			if (!user_access('administer contents,administer papers,administer '.$topicInfo->info->fid.' paper')) return $body->debug.message('error','Owner of this topic was blocked.');
		} else if ($topicInfo->info->access == 'member') {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			return $body->debug.message('error','This topic for member only.');
		} else if ($topicInfo->info->access == 'public') {
			/* do nothing */
		} else if (!user_access('access papers')) {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			return $body->debug.message('error','Access denied');
		} else if (cfg('topic.close.day') && $topicInfo->info->created < date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d')-cfg('topic.close.day'),date('Y'))) && $topicInfo->info->type != 'page') {
			header('HTTP/1.0 404 Not Found');
			head('<meta name="robots" content="noarchive" />');
			if (user_access('administer contents')) {
				$topicInfo->title .= ' - This topic was closed.';
			} else {
				return $body->debug.message('error','This topic was closed.');
			}
		}



		// check duplicate content
		if (q() != 'paper/'.$tpid || count($_GET) > 1)  head('<link rel="canonical" href="'.cfg('domain').url('paper/'.$tpid).'" />');


		if ($topicInfo->tags) {
			foreach ($topicInfo->tags as $tvalue) $tags[$tvalue->tid]=$tvalue->name;
		}

		$self->theme->title = $topicInfo->title
			. ($topicInfo->info->status == _PUBLISH || $topicInfo->info->status > -_LOCK ? '' : ' <em>('.sg_status_text($topicInfo->info->status).')</em>');

		$self->theme->id = 'paper-id-'.$tpid;
		$self->theme->class = 'content-paper content-paper-view';
		$self->theme->class .= ' paper-status-'.sg_status_text($topicInfo->info->status);
		if ($topicInfo->archived) $self->theme->class .= ' paper-archive';
		$self->theme->class .= ' paper-content-'.$topicInfo->info->type;
		if ($topicInfo->tags) {
			foreach ($topicInfo->tags as $tag) $self->theme->class .= ' paper-tag-'.$tag->tid;
		}
		if ($topicInfo->info->category) $self->theme->class .= ' paper-category-'.$topicInfo->info->category;

		$self->theme->header->text = SG\getFirst($topicInfo->tags[0]->name,$topicInfo->info->category,$topicInfo->info->forum);
		$description = SG\getFirst($topicInfo->tags[0]->description,$topicInfo->info->type_description);
		if ($description) {
			ob_start();
			eval ('?>'.$description);
			$self->theme->header->description = ob_get_clean();
		}

		//$self->theme->header->description.='AAA'.$description;

		// set user request comment sort order
		if (array_key_exists('change_comment_order',$_GET)) {
			$comment_order = SG\getFirst($_COOKIE['corder'],cfg('comment.order')) == 'ASC' ? 'DESC' : 'ASC';
			setcookie('corder',$comment_order,time()+60*60*24*365*10, cfg('cookie.path'),cfg('cookie.domain'));
			cfg('comment.order',$comment_order);
		}

		if ($topicInfo->archived) {
			user_menu(
				'edit',
				'<i class="icon -material -gray -sg-16">archive</i>',
				$isAdminPaper ? url('paper/'.$tpid.'/edit/archive') : 'javascript:void(0)',
				'{title: "'.($isAdminPaper ? ' Click to Un' : '').'Archive topic"}'
			);
		} else if ($isEditPaper) {
			$self->theme->option->ribbon = true;
			$self->theme->ribbon->class = 'navigator-manage';
			user_menu(
				'edit',
				'<i class="icon -material -gray -sg-16">edit</i>',
				url('paper/'.$tpid.'/edit'),
				'title','Edit topic detail'
			);
			user_menu(
				'edit',
				'edit_main',
				'จัดการเอกสาร',url('paper/'.$tpid.'/edit.main'),
				'{"class": "sg-action", "data-rel": "box", "data-width":480}'
			);
			user_menu(
				'edit',
				'edit_detail',
				'รายละเอียด',
				url('paper/'.$tpid.'/edit.detail'),
				'{"class": "sg-action", "data-rel": "box"}'
			);

			if (user_access('upload photo'))
				user_menu(
					'edit',
					'edit_photo',
					'ภาพประกอบ',
					url('paper/'.$tpid.'/edit.photo')
				);

			if (user_access('upload document'))
				user_menu(
					'edit',
					'add_doc',
					'เอกสารประกอบ',
					url('paper/'.$tpid.'/edit.docs')
				);

			user_menu(
				'edit',
				'edit_property',
				'รูปแบบการแสดงผล',
				url('paper/'.$tpid.'/edit.prop')
			);

			if ($isAdminPaper || user_access('administer paper tags'))
				user_menu(
					'edit',
					'move_forum',
					'จัดการหมวด',
					url('paper/'.$tpid.'/edit.tag')
				);

			if (module_install('poll') && user_access('administer pools,create poll'))
				user_menu(
					'edit',
					'make_poll',
					'สร้างแบบสำรวจความคิดเห็น',
					url('paper/'.$tpid.'/edit.makepoll')
				);

			user_menu(
				'edit',
				'edit_comment',
				($topicInfo->info->comment ? 'ไม่ให้มี' : 'อนุญาตให้มี').'การแสดงความคิดเห็น',
				url('paper/'.$tpid.'/edit.nocomment')
			);

			if ($isAdminPaper && $topicInfo->_content_type_property->revision)
				user_menu(
					'edit',
					'revision',
					'Revisions',
					url('paper/'.$tpid.'/revision')
				);

			if ($topicInfo->info->status < _LOCK)
				user_menu(
					'delete',
					'<i class="icon -material -gray -sg-16">delete</i>',
					url('paper/'.$tpid.'/delete'),
					'{"class": "sg-action", "data-rel": "box", "data-width" : 480, "title": "Delete topic"}'
				);
		}

		user_menu('home',tr('home'),url());




		if ($topicInfo->info->module) {
			user_menu('type',$topicInfo->info->type_name,url($topicInfo->info->module));
		} else if ($topicInfo->info->type) {
			user_menu('type',$topicInfo->info->type_name,url('contents/'.$topicInfo->info->type));
		}

		if (isset($topicInfo->tags[0])) user_menu('tag',$topicInfo->tags[0]->name,url('tags/'.$topicInfo->tags[0]->tid));
		user_menu('paper_id',$tpid,url('paper/'.$tpid));

		BasicModel::member_menu();



		if ($topicInfo->info->type != 'page' && user_access('administer contents,administer papers,create '.$topicInfo->info->type.' paper,create '.$topicInfo->info->type.' content')) user_menu('new',tr('Create new topic'),url('paper/post/'.$topicInfo->info->type));
		user_menu('qrcode','<i class="icon -material">qr_code</i>', url('qrcode/gen',['url' => url('paper/'.$tpid)]));

		// Process module init
		if ($topicInfo->info->type) R::On($topicInfo->info->module.'.paper.view.init',$self,$topicInfo,$para,$body);


		// Process paper edit command
		if (preg_match('/^edit/i', $this->action)) {
			$self->theme->navigator = user_menu();
			$isEdit = $topicInfo->RIGHT & _IS_EDITABLE;
			if (!$isEdit) return message('error', 'Access denied');
			$self->theme->sidebar = R::View('paper.edit.menu', $tpid);
			$ret .= R::Page('paper.'.$this->action, $self, $topicInfo, func_get_arg(3),func_get_arg(4));
			return $ret;
		} else if ($this->action) {
			$self->theme->navigator = user_menu();
			$this->actionRet = R::Page('paper.'.$this->action, $self, $topicInfo, func_get_arg(3),func_get_arg(4));
			if ($actionRet) return $ret.$actionRet;
		}




		// Start view content

		R::Model('reaction.add',$tpid,'TOPIC.VIEW');

		if (!in_array($topicInfo->info->type,array('page','ibuy','project','project-dev'))) {
			$ret .= '<nav class="nav -page -sg-text-right -no-print">'.R::Page('paper.like.status', NULL, $tpid).'</nav>';
		}


		++$topicInfo->info->view;

		// The Open Graph Protocol
		$opengraph = (Object)[];
		$opengraph->title = $topicInfo->title;
		$opengraph->type = 'website';
		$opengraph->url = is_home() ? url() : url('paper/'.$tpid);
		if ($topicInfo->photo->items[0]->_url) $opengraph->image = $_SERVER['REQUEST_SCHEME'].':'.$topicInfo->photo->items[0]->_url;
		$opengraph->description = sg_summary_text($topicInfo->body);
		sg::add_opengraph($opengraph);

		if ($topicInfo->uid && $topicInfo->info->owner_status != 'enable') $body->status .= message('error','This owner was block');

		event_tricker('paper.view.start',$self,$topicInfo,$para);

		if ($topicInfo->info->module) R::Manifest($topicInfo->info->module);

		// Prepare paper content
		$body = object_merge($body, R::View('paper.content.prepare', $topicInfo));



		// do external view from module
		if ($topicInfo->info->module) {
			// do external view from file on.module.paper.view
			$onViewResult = R::On($topicInfo->info->module.'.paper.view',$self,$topicInfo,$para,$body);
		} else if (function_exists('module2classname') && module_exists($topicInfo->info->module,'__view')) {
			call_user_func(array(module2classname($topicInfo->info->module),'__view'),$self,$topicInfo,$para,$body);
		}

		// set user menu to theme
		$self->theme->navigator = user_menu();

		if ($topicInfo->archived) unset($body->comment_form);
		foreach ($body as $str) {
			if (is_string($str)) $ret .= $str;
		}

		// do eventricker paper.view.complete
		event_tricker('paper.view.complete',$self,$topicInfo,$para);

		unset($self->theme->body);

		return new Scaffold([
			'body' => new Widget([
				'children' => [$ret],
			]),
		]);
	}
}
?>