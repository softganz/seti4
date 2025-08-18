<?php
/**
* Model.  :: Node Model
* Created :: 2021-09-30
* Modify  :: 2025-08-18
* Version :: 26
*
* @param Array $args
* @return Object
*
* @usage new NodeModel([])
* @usage NodeModel::function($conditions, $options)
*/

use Softganz\DB;
use Softganz\SetDataModel;

class NodeModel {

	public static function get($id) {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['nodeId' => NULL, 'title' => '', 'info' => NULL];

		$result->info = DB::select([
			'SELECT t.`tpid` `nodeId`, t.* FROM %topic% t WHERE t.`tpid` = :nodeId LIMIT 1',
			'var' => [':nodeId' => $id]
		]);

		// debugMsg(mydb()->_query);

		if (empty($result->info->nodeId)) return NULL;

		$result->nodeId = $result->info->nodeId;
		$result->title = $result->info->title;

		return $result;
	}

	public static function getBackend($nodeId) {
		return DB::select([
			'SELECT
				 `topic`.`tpid` `nodeId`
				, `rev`.`phpBackend`
				, `rev`.`css`
				, `rev`.`script`
				, `rev`.`data`
			FROM %topic% `topic`
				LEFT JOIN %topic_revisions% `rev` ON `rev`.`revid` = `topic`.`revid`
			WHERE `topic`.`tpid` = :nodeId
			LIMIT 1',
			'var' => [':nodeId' => $nodeId],
			'onComplete' => function(&$data) {
				$data->data = json_encode(json_decode($data->data), JSON_PRETTY_PRINT  + JSON_UNESCAPED_UNICODE);
			}
		]);
	}

	public static function getData($nodeId) {
		return DB::select([
			'SELECT `json` FROM %topic_data% WHERE `tpid` = :nodeId LIMIT 1',
			'var' => [':nodeId' => $nodeId],
			'options' => [
				'jsonDecode' => [
					['field' => 'json']
				]
			]
		])->json;
	}

	public static function items($conditions) {
		$conditions = (Object) $conditions;
		$defaults = '{debug: false, order: "nodeId", sort: "DESC", items: 10, page: 1, field: "detail"}';
		$options = \SG\json_decode($conditions->options, $defaults);
		$debug = $options->debug;
		unset($conditions->options);

		if (empty($options->page)) $options->page = 1;

		$conditions = (Object) array_merge(
			[
				'nodeId' => NULL,
				'tags' => NULL,
				'type' => NULL,
				'sticky' => NULL,
				'format' => NULL,
				'user' => NULL,
				'ip' => NULL,
				'year' => NULL,
				'searchText' => NULL,
			],
			(Array) $conditions
		);

		if ($conditions->type === '*') unset($conditions->type);
		else if (!$conditions->type) $conditions->type = 'story';
		if ($conditions->tag) {
			$conditions->tags = $conditions->tag;
			unset($conditions->tag);
		}

		$result = (Object) [
			'count' => 0,
			'total' => 0,
			'items' => [],
			'debug' => [],
		];

		$result->debug['CONDITIONS'] = $conditions;
		$result->debug['OPTIONS'] = $options;

		$fields = explode(',', $options->field);
		if (in_array('body', $fields)) {
			$fields[] = 'detail';
		}

		$orderList = [
			'nodeId' => '`nodeId`',
			'title' => '`topic`.`title`',
			'read' => '`topic`.`view`',
			'reply' => '`topic`.`reply`',
			'answer' => '`topic`.`comment`',
			'view' => '`topic`.`last_view`',
			'lastComment' => '`topic`.`last_reply`',
		];

		$orderBy = \SG\getFirst($orderList[$options->order], $orderList['nodeId']);
		$sortBy = strtoupper($options->sort) === 'DESC' ? 'DESC' : 'ASC';
		$page = intval($options->page);
		$items = intval($options->items);

		if ($items) {
			$limit = 'LIMIT '.(($page - 1) * $items).','.$items;
		} else {
			$limit = 'LIMIT 10';
			// $total_items=mydb::select('SELECT COUNT(*) `total` FROM '.$table_cmd.($where_cmd?' WHERE '.$where_cmd:'').' LIMIT 1')->total;
			// $count_query=mydb()->_query;
			// $pagenv = new PageNavigator($items,$para->page,$total_items,q());
			// $sql_cmd .= '  LIMIT '.($pagenv->FirstItem()<0 ? 0 : $pagenv->FirstItem()).','.$items;
		}

		// debugMsg($conditions, '$conditions');
		// debugMsg($options, '$options');

		// if ($conditions->category) {
		// 	$tags=db_query_one_column('SELECT tid FROM %tag_synonym% WHERE name="'.$conditions->category.'"');
		// 	$where[]='tg.tid IN ('.implode(',',$tags).')';
		// }

		// Field
		$fld_cmd = '`topic`.`tpid` `nodeId`, `topic`.`type`, `topic`.`title`, `topic`.`access`, `topic`.`status`, `topic`.`view`, `topic`.`last_view` `lastView`, `topic`.`comment`, `topic`.`reply`, `topic`.`last_reply` `lastReply`, `topic`.`created`'._NL;
		$fld_cmd .= '  , `user`.`username`, `user`.`name` `ownerName` '._NL;
		if (in_array('detail', $fields)) $fld_cmd .= '    , `revision`.`format` , `revision`.`body` , `revision`.`property` , `revision`.`email` , `revision`.`homepage`'._NL;
		if (in_array('comment', $fields)) $fld_cmd .= ' ,(SELECT COUNT(*) FROM %topic_comments% `comment` WHERE `comment`.`tpid` = `topic`.`tpid`) comments'._NL;

		// Join table
		$joins[] = '  LEFT JOIN %users% `user` ON `topic`.`uid` = `user`.`uid` '._NL;
		if (in_array('detail', $fields)) $joins[] = '  LEFT JOIN %topic_revisions% `revision` on `revision`.`revid` = `topic`.`revid` '._NL;
		if ($conditions->tags || $conditions->category) $joins[] = '  LEFT JOIN %tag_topic% `tag_topic` ON `tag_topic`.`tpid` = `topic`.`tpid` '._NL;
		if ($conditions->category) $joins[] = '  LEFT JOIN %tag% `category` ON `category`.tid = `tag_topic`.`tid` '._NL;

		// Condition
		if ($conditions->type) \mydb::where('`topic`.`type` IN ( :type )', ':SET-STRING:type', $conditions->type);
		if ($conditions->nodeId) \mydb::where('`topic`.`tpid` IN ( :nodeId )', ':SET:nodeId', $conditions->nodeId);
		if ($conditions->tags) \mydb::where('`tag_topic`.`tid` IN ( :tags )', ':SET-STRING:tags', $conditions->tags);
		if ($conditions->sticky) \mydb::where('`topic`.`sticky` = :sticky', ':sticky', $conditions->sticky);
		if ($conditions->user) \mydb::where('(`topic`.`uid` = :userId || `topic`.`tpid` IN (SELECT `tpid` FROM %topic_user% WHERE `uid` = :userId))', ':userId', $conditions->user);
		if ($conditions->ip) \mydb::where('`topic`.`ip` = :ip', ':ip', ip2long($conditions->ip));
		if ($conditions->year) \mydb::where('YEAR(`topic`.`created`) = :year', ':year', $conditions->year);
		if ($conditions->format) \mydb::where('`revision`.`property` ->> "$.input_format" = :inputFormat', ':inputFormat', $conditions->format);
		if ($conditions->searchText) \mydb::where('`topic`.`title` LIKE :searchText', ':searchText', '%'.$conditions->searchText.'%');
		if (i()->ok) {
			if (!user_access('administer contents,administer papers')) \mydb::where('(`topic`.`status` IN ('._PUBLISH.','._LOCK.') || (`topic`.`status` IN ('._DRAFT.','._WAITING.') AND `topic`.`uid` = '.i()->uid.'))');
		} else {
			\mydb::where('`topic`.`status` IN ('._PUBLISH.','._LOCK.')');
		}
		if ($conditions->condition) \mydb::where($conditions->condition);

		\mydb::value('$JOINS$', implode($joins), false);
		\mydb::value('$ORDER$', 'ORDER BY '.$orderBy.' '.$sortBy);
		\mydb::value('$LIMIT$', $limit, false);

		$sql_cmd = 'SELECT SQL_CALC_FOUND_ROWS
			'.$fld_cmd.'
			FROM %topic% `topic`
			$JOINS$
			%WHERE%
			$ORDER$
			$LIMIT$;
			-- {key: "nodeId"}';

		$dbs = \mydb::select($sql_cmd);
		// debugMsg($conditions, '$conditions');
		// debugMsg(mydb()->_query);
		// debugMsg($dbs, '$dbs');

		$result->debug['ITEMS'] = mydb()->_query;

		$result->items = $dbs->items;
		$result->count = count((Array) $result->items);
		$result->total = intval($dbs->_found_rows);


		$nodeList = [];
		foreach ($result->items as $nodeId => $topic) {
			$nodeList[] = $topic->nodeId;
			if (in_array('detail', $fields)) $result->items[$nodeId]->summary = sg_summary_text($topic->body);
		}

		if (in_array('tag', $fields) && $nodeList) {
			$tagList = DB::select([
				'SELECT
				`node`.`tpid` `nodeId`
				, `node`.`tid` `tagId`
				, `tag`.`name` `tagName`
				FROM %tag_topic% `node`
					LEFT JOIN %tag% `tag` ON `node`.`tid` = `tag`.`tid`
				%WHERE%',
				'where' => [
					'%WHERE%' => [
						['`node`.`tpid` IN ( :nodeList )', ':nodeList' => new SetDataModel($nodeList)]
					]
				]
			])->items;
			foreach ($tagList as $tag) {
				if (empty($result->items[$tag->nodeId]->tags)) $result->items[$tag->nodeId]->tags = [];
				$result->items[$tag->nodeId]->tags[] = (Object) [
					'id' => $tag->tagId,
					'name' => $tag->tagName,
				];

			}
		}

		if (in_array('photo', $fields) && $nodeList) {
			$photoList = mydb::select(
				'SELECT `fid` `fileId`, `tpid` `nodeId`, `file`, `folder`
				FROM %topic_files%
				WHERE `tpid` IN ( :nodeList ) AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "photo" AND `tagName` IS NULL
				GROUP BY `tpid`
				ORDER BY `tpid` ASC',
				[':nodeList' => 'SET:'.implode(',',$nodeList)]
			)->items;

			$result->debug['PHOTOS'] = mydb()->_query;

			foreach ($photoList as $photo) {
				$result->items[$photo->nodeId]->photo = FileModel::photoProperty($photo->file, $photo->folder);
			}
		}

		if (in_array('doc', $fields) && $nodeList) {
			$docList = DB::select([
				'SELECT `fid` `fileId`, `tpid` `nodeId`, `file`, `folder`, `title`
				FROM %topic_files%
				WHERE `tpid` IN ( :nodeList ) AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "doc" AND `tagName` IS NULL
				-- GROUP BY `tpid`
				ORDER BY `tpid` ASC',
				'var' => [':nodeList' => new SetDataModel($nodeList)]
			])->items;

			$result->debug['DOCS'] = mydb()->_query;

			foreach ($docList as $doc) {
				$prop = FileModel::docProperty($doc->file, $doc->folder);
				$prop->id = $doc->fileId;
				$prop->title = $doc->title;
				$result->items[$doc->nodeId]->doc[] = $prop;
			}
		}

		if (!$debug) unset($result->debug);

		return $result;
	}

	public static function getAlbum($docId, $projectId = NULL) {
		return DB::select([
			'SELECT
			`doc`.`tpid` `nodeId`
			, `doc`.`fid` `docId`
			, `photo`.`fid` `coverId`
			, `doc`.`folder` `docFolder`
			, `doc`.`file` `docFile`
			, `doc`.`tagName`
			, `doc`.`title` `title`
			, `photo`.`folder` `coverFolder`
			, `photo`.`file` `coverPhoto`
			FROM %topic_files% `doc`
				LEFT JOIN %topic_files% `photo` ON `photo`.`tpid` = `doc`.`tpid` AND `photo`.`refId` = `doc`.`fid`
			%WHERE%
			LIMIT 1',
			'where' => [
				'%WHERE%' => [
					['`doc`.`fid` = :docId AND `doc`.`refId` IS NULL', ':docId' => $docId],
					$projectId ? ['`doc`.`tpid` = :projectId', ':projectId' => $projectId] : NULL,
				]
			]
		]);
	}

	public static function getAlbums($conditions = []) {
		$conditions = (Object) array_replace_recursive(
			[
				'nodeId' => NULL, // Int
				'tagNameLike' => NULL, // String
			],
			(Array) $conditions
		);

		return (Array) DB::select([
			'SELECT
			`doc`.`tpid` `nodeId`
			, `doc`.`fid` `docId`
			, `cover`.`fid` `coverId`
			, `doc`.`tagName`
			, `doc`.`title` `title`
			, `doc`.`folder` `docFolder`
			, `doc`.`file` `docFile`
			, `cover`.`file` `coverPhoto`
			, `cover`.`folder` `coverFolder`
			FROM %topic_files% `doc`
				LEFT JOIN %topic_files% `cover` ON `cover`.`tpid` = `doc`.`tpid` AND `cover`.`refId` = `doc`.`fid`
			%WHERE%
			ORDER BY `doc`.`fid` ASC',
			'where' => [
				'%WHERE%' => [
					['`doc`.`tpid` = :nodeId AND `doc`.`refId` IS NULL', ':nodeId' => $conditions->nodeId],
					['`doc`.`tagName` LIKE :tagNameLike', ':tagNameLike' => $conditions->tagNameLike]
				]
			],
			'options' => ['group' => 'tagName']
		])->items;
	}

	public static function countNodeByUserId($userId) {
		return DB::select([
			'SELECT COUNT(*) `count` FROM %topic% WHERE `uid` = :userId LIMIT 1',
			'var' => [':userId' => $userId]
		])->count;
	}

	public static function countCommentByUserId($userId) {
		return DB::select([
			'SELECT COUNT(*) `count` FROM %topic_comments% WHERE `uid` = :userId LIMIT 1',
			'var' => [':userId' => $userId]
		])->count;
	}
	
	/**
	* Create New Node
	* Created 2019-06-10
	* Modify  2022-10-22
	* @param Object $topic
	* @param Object $option
	* @return Object
	*/
	public static function create($topic, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'tpid' => NULL,
			'complete' => false,
			'error' => false,
			'document' => (Object) [],
			'photo' => [],
			'process' => [],
		];

		$error=$field_missing=$document_error=array();

		$result->process[]='NodeModel::save_topic '.($simulate?'<strong>simulation</strong> ':'').'request';

		// Get node type on wmpty
		if (!$topic->type->type) $topic->type = BasicModel::get_topic_type($topic->post->type);

		if (sg_invalid_poster_name($topic->post->poster)) $error[]= 'Duplicate poster name';
		if (!i()->ok && !sg_valid_daykey(5,$_POST['daykey'])) $error[]='Invalid Anti-spam word';

		if (!i()->ok && empty($_POST['daykey'])) $topic->field_missing[]='Anti-spam word';
		if (empty($topic->post->title)) $topic->field_missing[]= 'Title (หัวข้อ)';
		if ($topic->type->min_word_count>0 && strlen($topic->post->body)<$topic->type->min_word_count) $topic->field_missing[]='Body (รายละเอียด อย่างน้อย '.$topic->type->min_word_count.' ตัวอักษร)';
		if (empty($topic->post->poster)) $topic->field_missing[]='Sender (ชื่อผู้ส่ง)';
		if (!i()->ok && cfg('topic.require.mail') && (empty($topic->post->email) || !sg_is_email($topic->post->email))) $topic->field_missing[] = 'E-mail - อีเมล์';
		if (!i()->ok && cfg('topic.require.homepage') && empty($topic->post->website)) $topic->field_missing[] = 'Homepage - เว็บไซท์';

		if (!user_access('upload photo') && preg_match('/\[img\]|<img|\&lt\;img|\!\[.*?\]/i',$topic->post->body)) $error[] = 'ขออภัย ท่านไม่มีสิทธิ์ในการส่งภาพ';

		// do external module post form
		R::On($topic->type->module.'.paper.post.check',$self,$topic,$result);
		//if ($topic->type->module!='paper') do_class_method($topic->type->module,'__post_check',$this,$topic,$result);


		if ($topic->field_missing) $error[]='กรุณาป้อนข้อมูลต่อไปนี้ให้ครบถ้วน : <ul><li>'.implode('</li><li>',$topic->field_missing).'</li></ul>';

		if (sg::is_spam_word($topic->post)) $error[]='มีข้อความที่ไม่เหมาะสมอยู่ในสิ่งที่ป้อนมา';

		//up load file document
		if (user_access('upload document') && is_uploaded_file($_FILES['document']['tmp_name'])) {
			$document = (Object) $_FILES['document'];

			$upload_folder=cfg('paper.upload.document.folder');
			$document->_property=sg_explode_filename($upload_folder.$document->name,'doc');
			$document->title = \SG\getFirst($topic->post->document_title,sg_explode_filename($upload_folder.$document->name)->basename);
			$document->name=$document->_property->name;

			if (cfg('topic.doc.file_ext')) {
				if (!in_array($document->_property->ext,cfg('topic.doc.file_ext'))) $document_error[]='Invalid file format : Only <strong>'.implode(',',cfg('topic.doc.file_ext')).'</strong> file can be send.';
			} else if (cfg('topic.doc.file_type')) {
				if (!in_array($document->type,cfg('topic.doc.file_type'))) $document_error[]='Invalid file format';
			}

			// check duplicate filename and rename to next number
			if (file_exists($document->_property->location) && is_file($document->_property->location)) {
				$new_filename=sg_generate_nextfile($upload_folder,'doc',$document->_property->ext);
				$document->_property=sg_explode_filename($new_filename);
				$document->name=$document->_property->name;
			}

			if ($document_error) {
				$error[]='Document upload error : <ul><li>'.implode('</li><li>',$document_error).'</li></ul>';
			} else {
				$document->file = $document->name;
			}
			$result->document=$document;
		}

		// Save upload video
		if (cfg('topic.video.allow') && user_access('upload video') && is_uploaded_file($_FILES['video']['tmp_name'])) {
			$video=(object)$_FILES['video'];

			$folder=sg_user_folder(i()->username);
			$video->_property=sg_explode_filename($folder.$video->name,'flv');
			$video->name=$video->_property->name;

			if (!(in_array($video->_property->ext,array('flv','f4v')) || in_array($video->type,array('video/x-flv')))) $video_error[]='Invalid video format. Support FLV video format only.';

			// check duplicate filename and rename to next number
			if (file_exists($video->_property->location) && is_file($video->_property->location)) {
				$new_filename=sg_generate_nextfile($folder,'flv',$video->_property->ext);
				$video->_property=sg_explode_filename($new_filename);
				$video->name=$video->_property->name;
			}

			if ($video_error) {
				$error[]='Video upload error : <ul><li>'.implode('</li><li>',$video_error).'</li></ul>';
			} else {
				$video->file = $video->name;
			}
			$result->video=$video;
		}


		$result->process[]='Complete of check invalid input';

		if ($error) {
			$result->process[]='There is invalid input';
			$result->error=message('error',$error);
			return $result;
		}

		$result->process[]='Start <strong>saving</strong> process';



		// start saving process
		$topic->post->type = $topic->type->type;
		$topic->post->promote=$topic->type->topic_options->promote?1:0;
		if ($topic->forum->require_approve=='yes' || !user_access('post paper without approval')) {
			$topic->post->status=_WAITING;
		} else if ($_POST['draft']) {
			$topic->post->status=_DRAFT;
		} else {
			$topic->post->status=_PUBLISH;
		}

		$topic->post->created = $topic->post->timestamp = date('Y-m-d H:i:s');
		$topic->post->ip = ip2long(GetEnv('REMOTE_ADDR'));
		if ($topic->post->ip=="") $topic->post->ip='func.NULL';
		$topic->post->uid = i()->uid;
		if ($topic->post->poster===i()->name) unset($topic->post->poster);
		$topic->post->comment = SG\getFirst($topic->post->comment,$topic->type->topic_options->comment,_COMMENT_READWRITE);

		// topic poperty
		$topic->post->property = (object) $topic->post->property;

		if (!isset($topic->post->property->show_photo)) {
			$topic->post->property->show_photo = cfg('topic.property.show_photo');
		}
		if (!user_access('administer contents') && $topic->post->property->input_format=='php') {
			unset($topic->post->property->input_format);
		}
		$topic->post->property = sg_json_encode($topic->post->property);
		$topic->post->revid = NULL;
		if (empty($topic->post->areacode)) $topic->post->areacode = NULL;

		$error=null;

		// check for clear sticky
		if ($_POST['clear_sticky'] && $topic->post->sticky && user_access('administer contents,administer papers')) {
			$sticky=cfg('sticky');
			$result->process[]='Clear sticky of '.$sticky[$topic->post->sticky];
			$sql_cmd='UPDATE %topic% SET sticky=0 WHERE sticky='.$topic->post->sticky;
			mydb::query($sql_cmd,$simulate);
			$result->process[]=mydb()->_query;
		}

		// save title into topic
		$sql_topic = mydb::create_insert_cmd('%topic%',$topic->post);
		mydb::query($sql_topic,$topic->post);



		//debugMsg(mydb()->_query);

		$result->process[]=mydb()->_query;

		if (mydb()->_error) $error['topic']='Error on create topic query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');
		$nodeId = $topic->tpid = $topic->post->tpid = mydb()->insert_id;
		$result->tpid = $nodeId;

		if ( $simulate ) {
			$nodeId = $topic->tpid = $topic->post->tpid = db_query_one_cell('SELECT MAX(tpid)+1 from %topic%');
		}

		// save detail into revision and update revision in topic
		$sql_detail = mydb::create_insert_cmd('%topic_revisions%',$topic->post);

		if (!isset($error['topic'])) {
			mydb::query($sql_detail,$topic->post);
			if (mydb()->_error) $error['detail']='Error on create detail query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');
			$result->process[]=mydb()->_query;

			$revid=mydb()->insert_id;

			// update revision id into reference topic
			$sql_cmd = 'UPDATE %topic% SET revid='.$revid.' WHERE tpid='.$nodeId.' LIMIT 1';
			mydb::query($sql_cmd);
			$result->process[]=mydb()->_query;
		}

		// add taxonomy into topic_tag table
		if ($topic->post->taxonomy) {
			$topic_tag = array();
			$taxonomy=$topic->post->taxonomy;
			if (array_key_exists('tags',$taxonomy)) {
				$tags=$taxonomy['tags'];
				unset($taxonomy['tags']);
			} else $tags=array();

			foreach ($taxonomy as $vid=>$tids) {
				if (is_array($tids)) {
					foreach ($tids as $tid) if (!empty($tid)) $topic_tag[$tid]=$nodeId .' , '.$vid.' , '. $tid;
				} else if (!empty($tids)) $topic_tag[$tids]=$nodeId .' , '.$vid.' , '. $tids;
			}


			foreach ($tags as $vid=>$tag_desc) {
				if (empty($tag_desc)) continue;
				foreach (explode(',',$tag_desc) as $tag_name) {
					$tag_name=trim($tag_name);
					$tag_db = mydb::select('SELECT tid FROM %tag% WHERE `vid` = :vid AND `name` = :name LIMIT 1', ':vid', $vid, ':name', $tag_name)->tid;
					$result->process[] = mydb()->_query;
					$tid =  $tag_db ? $tag_db : BasicModel::add_taxonomy($vid,$tag_name);
					$topic_tag[$tid] = $nodeId .' , '.$vid.' , '. $tid;
				}
			}

			$result->process[] = print_o($topic_tag, '$topic_tag');
			if ($topic_tag) {
				mydb::query('INSERT INTO %tag_topic% ( `tpid` , `vid` , `tid` ) VALUES ( ' . implode(' ) , ( ',$topic_tag) .' ) ',$simulate);
				$result->process[]=mydb()->_query;

				$stmt = 'SELECT tid,name FROM %tag% WHERE tid IN ('.implode(',',array_keys($topic_tag)).')';
				$topic->tags = mydb::select($stmt)->items;
				$result->process[]=mydb()->_query;
			}

		}
		$result->process[]=print_o($taxonomy,'$taxonomy');

		// Save upload photo to folder and add to table
		if (user_access('upload photo') && !$error) {
			$desc=(object)post('topic[photo_desc]',_TRIM+_STRIPTAG);
			$desc->tpid=$nodeId;
			$desc->cid=0;
			$desc->type='photo';
			$desc->timestamp='func.NOW()';
			$desc->ip = ip2long(GetEnv('REMOTE_ADDR'));
			$desc->uid = i()->uid;

			$photos= array();
			// convert multiple upload file to each upload file
			if (is_string($_FILES['photo']['name'])) {
				$photos[]=(object)$_FILES['photo'];
			} elseif (is_array($_FILES['photo']['name'])) {
				foreach ($_FILES['photo']['name'] as $key=>$name) {
					$photos[$key]->name=$_FILES['photo']['name'][$key];
					$photos[$key]->type=$_FILES['photo']['type'][$key];
					$photos[$key]->tmp_name=$_FILES['photo']['tmp_name'][$key];
					$photos[$key]->error=$_FILES['photo']['error'][$key];
					$photos[$key]->size=$_FILES['photo']['size'][$key];
				}
			}

			$uploads=array();
			foreach ($photos as $photo) {
				if (!is_uploaded_file($photo->tmp_name)) continue;
				$photo_result=$result->photo[] = R::Model('photo.save',$photo);
				if ($photo_result->complete && $photo_result->save->_file) {
					$desc->file=$photo_result->save->_file;
					$sql_cmd = mydb::create_insert_cmd('%topic_files%',$desc);
					mydb::query($sql_cmd,$desc);
					$result->process[]=mydb()->_query;
				}
			}
		}

		// Save document file to folder and add to table
		if ($document->file && !$error) {
			if (!$simulate) {
				if (copy($document->tmp_name,$document->_property->location)) {
					if (cfg('upload.file.chmod')) chmod($document->dest,cfg('upload.file.chmod'));
				}
				$document->tpid = $nodeId;
				$document->cid = 0;
				$document->type = 'doc';
				$document->description = $topic->post->document_description;
				$document->timestamp='func.NOW()';
				$document->ip = ip2long(GetEnv('REMOTE_ADDR'));
				$document->uid = i()->uid;
				$sql_doc = mydb::create_insert_cmd('%topic_files%',$document);
				$result->process[]='Saving upload document to '.$document->dest;
				mydb::query($sql_doc,$document);
				$result->process[]=mydb()->_query;
				$result->document=$document;
			}
		}

		if (cfg('topic.video.allow') && $video->file && !$error) {
			if (!$simulate) {
				if (copy($video->tmp_name,$video->_property->location)) {
					if (cfg('upload.file.chmod')) chmod($video->_property->location,cfg('upload.file.chmod'));
				}
				$video->tpid = $nodeId;
				$video->cid = 0;
				$video->type = 'movie';
				$video->title = $topic->post->document_title;
				$video->description = $topic->post->document_description;
				$video->timestamp='func.NOW()';
				$video->ip = ip2long(GetEnv('REMOTE_ADDR'));
				$video->uid = i()->uid;
				$sql_doc = mydb::create_insert_cmd('%topic_files%',$video);
				$result->process[]='Saving upload document to '.$video->_property->location;
				mydb::query($sql_doc,$video);
				$result->process[]=mydb()->_query;
				$result->video=$video;
			}
		}

		//		if ($topic->type->module!='paper') do_class_method($topic->type->module,'_post',$this,$topic);

		if ($error) {
			$result->error='Create topic error : <ul><li>'.implode('</li><li>',(array)$error).'</li></ul>';
		} else {
			$result->complete=true;
		}
		$result->process[]='NodeModel::save_topic complete';
		return $result;
	}

	/**
	* Node Delete
	*
	* @param Object $nodeId
	* @return Object $options
	*/

	public static function delete($nodeId) {
		$defaults = '{debug: false, simulate: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'complete' => false,
			'error' => false,
			'process' => ['NodeModel:::delete() request'],
		];

		$simulate = $options->simulate;

		$result->data = DB::select([
			'SELECT * FROM %topic%
			WHERE `tpid` = :tpid LIMIT 1',
			'var' => [':tpid' => $nodeId],
		]);

		// delete topic
		$result->process[]='Delete paper topic and re-autoindex';
		DB::query([
			'DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		// delete detail
		$result->process[] = 'Delete paper detail';
		DB::query([
			'DELETE FROM %topic_revisions% WHERE `tpid` = :tpid LIMIT 1',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		// delete tag topic
		$result->process[] = 'Delete Tag Topic';
		DB::query([
			'DELETE FROM %tag_topic% WHERE `tpid` = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		DB::query([
			'DELETE FROM %topic_user% WHERE `tpid` = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		DB::query([
			'DELETE FROM %topic_parent% WHERE `tpid` = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		DB::query([
			'DELETE FROM %reaction% WHERE `action` LIKE "TOPIC.%" AND `refid` = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		// Delete topic property
		$result->process[] = 'Delete topic property';
		DB::query([
			'DELETE FROM %property% WHERE `module` = "paper" AND `propid` = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		// delete photos
		$result->process[] = 'Start delete all photo';
		$photoDbs = DB::select([
			'SELECT * FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		DB::query([
			'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		foreach ($photoDbs->items as $photo) {
			$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$photo->file;
			if (file_exists($filename) and is_file($filename)) {
				if (self::photoInUsed($photo->file, $photo->fid)) {
					$result->process[] = 'File <em>'.$photo->_file.'</em> was used by other item';
				} else {
					$result->process[] = '<em>Delete file '.$filename.'</em>';
					if (!$simulate) unlink($filename);
				}
			}
		}


		// delete documents
		$result->process[] = 'Delete document';
		$docDbs = DB::select([
			'SELECT `file` FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		DB::query([
			'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		foreach ( $docDbs->items as $rs ) {
			$filename = cfg('folder.abs').cfg('upload_folder').'forum/'.$rs->file;
			$result->process[] = '<em>Delete document '.$filename.'</em>';
			if (!$simulate && file_exists($filename) && is_file($filename)) unlink($filename);
		}

		/*
		// delete video
		if ($topic->video->file) {
			$result->process[]='Delete video';
			$stmt = 'DELETE FROM %topic_files% WHERE `tpid` = :tpid AND `type`="movie"';
			mydb::query($stmt, ':tpid', $nodeId);
			$result->process[]=R('query');

			if ($topic->video->_location && file_exists($topic->video->_location) && is_file($topic->video->_location)) {
				if (!$simulate) unlink($topic->video->_location);
				$result->process[]='<em>delete video file '.$topic->video->_location.'</em>';
			}
		}
		*/

		// delete comment post
		$result->process[] = 'Delete comment';

		DB::query([
			'DELETE FROM %topic_comments% WHERE tpid = :tpid',
			'var' => [':tpid' => $nodeId]
		]);
		$result->process[] = R('query');

		// save delete log
		LogModel::save([
			'module' => 'paper',
			'keyword' => 'Paper delete',
			'message' => 'paper/'.$nodeId.' - '.$result->data->title.' was delete'
		]);

		// delete was complete
		$result->complete = true;
		$result->process[] = 'NodeModel::delete() complete';


		// Call node delete complete
		// TODO : Do it later
		//if (function_exists('module_exists') && module_exists($classname,'__delete_complete')) call_user_func(array($classname,'__delete_complete'),$this,$topic,$para,$result);

		return $result;
	}

	/**
	 * Delete comment by comment ID
	 * @param Int $commentId
	 * @return Object
	 */
	public static function deleteCommentById(Int $commentId) {
		$result = (Object) [
			'complete' => false,
			'error' => false,
			'process' => ['Node comment '.$commentId.' delete request'],
		];

		$simulate = debug('simulate');

		if (empty($commentId)) {
			return (Object) [
				'error' => true,
				'message' => 'Comment ID is empty',
			];
		}

		$toDelete = DB::select([
			'SELECT `cid` `commentId`, `tpid` `nodeId` FROM %topic_comments% WHERE `cid` = :commentId LIMIT 1',
			'var' => [':commentId' => $commentId]
		]);

		if (empty($toDelete->commentId)) return (Object) ['error' => true, 'message' => 'Nothing to delete'];

		$result->process[] = 'Process delete file of comment';
		$photoDbs = DB::select([
			'SELECT `fid` `fileId`, `file` FROM %topic_files% WHERE `cid` = :commentId',
			'var' => [':commentId' => $commentId]
		]);

		// Delete comment file
		$result->process[] = R('query');
		foreach ($photoDbs->items as $photo) {
			$result->process[] = 'Delete comment file '.$photo->fileId.' : '.$photo->file;
			$fileResult = FileModel::delete($photo->fileId, $photo->file);
		}

		// Delete comment record
		$result->process[] = 'Delete comment';
		DB::query([
			'DELETE FROM %topic_comments% WHERE `cid` = :commentId LIMIT 1',
			'var' => [':commentId' => $commentId]
		]);
		$result->process[] = R('query');

		// Update topic comment count and last reply
		DB::query([
			'UPDATE %topic% SET
			`reply` = (SELECT COUNT(*) FROM %topic_comments% WHERE `tpid` = :nodeId)
			, `last_reply` = (SELECT MAX(`timestamp`) FROM %topic_comments% WHERE `tpid` = :nodeId)
			WHERE `tpid` = :nodeId LIMIT 1',
			'var' => [':nodeId' => $toDelete->nodeId]
		]);

		$result->process[] = R('query');

		$result->process[] = '== DELETE COMMENT COMPLETED ==';

		LogModel::save([
			'module' => 'paper',
			'keyword' => 'Paper comment delete',
			'message' => 'Delete comment id '.$commentId.' of <a href="'.url('paper/'.$toDelete->nodeId).'">paper/'.$toDelete->nodeId.'</a>'
		]);

		return $result;
	}

	public static function deleteAllUserNode($userId) {
		if (empty($userId)) return false;

		$dbs = DB::select([
			'SELECT `tpid`, `type`, `title`
			 FROM %topic%
			 WHERE `uid` = :uid
			 ORDER BY `created` DESC',
			 'var' => [':uid' => $userId]
		]);

		// Delete node
		foreach ($dbs->items as $rs) {
			if (!in_array($rs->type, ['story', 'page', 'forum'])) continue;
			if (empty($rs->tpid)) continue;

			$nodeDeleteResult = self::delete($rs->tpid);
			if ($nodeDeleteResult->complete) {
				// $ret .= 'Topic '.$rs->tpid.' DELETED<br />';
			}
		}
	}

	public static function deleteAllUserComment(Int $userId) {
		if (empty($userId)) return false;

		$comments = DB::select([
			'SELECT `cid` `commentId`, `tpid`
			 FROM %topic_comments%
			 WHERE `uid` = :userId',
			 'var' => [':userId' => $userId]
		])->items;

		foreach ($comments as $comment) {
			$result = self::deleteCommentById($comment->commentId);
		}
	}

	/**
	 * Get comment by comment ID
	 * @param Int $commentId
	 * @return Object
	 */
	public static function getCommentById(Int $commentId) {
		return DB::select([
			'SELECT
				c.`cid` `commentId`, c.`tpid` `nodeId`
				, c.tpid AS tpid , t.title
				, c.*
				, u.name AS owner
				, p.fid , p.file AS photo , p.title AS photo_title , p.description AS photo_description
			FROM %topic_comments% AS c
				LEFT JOIN %topic% AS t ON t.tpid=c.tpid
				LEFT JOIN %topic_files% as p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
				LEFT JOIN %users% AS u ON u.uid=c.uid
			WHERE c.`cid` = :commentId LIMIT 1',
			'var' => [':commentId' => $commentId]
		]);
	}

	public static function photoInUsed($fileName, $fileId) {
		$fileName = trim($fileName);
		if (empty($fileName) || empty($fileId)) return false;

		return DB::SELECT([
			'SELECT COUNT(*) `amt` FROM %topic_files% WHERE `file` = :fileName AND `fid` != :fileId LIMIT 1',
			'var' => [
				':fileName' => $fileName,
				':fileId' => $fileId
			]
		])->amt;
	}

	public static function members($nodeId) {
		return (Array) DB::select([
			'SELECT a.`uid`, UPPER(a.`membership`) `membership`
			, u.`username`, u.`name`, u.`email`
			FROM
				(
				SELECT t.`uid`, "CREATOR" `membership`
				FROM %topic% t
				WHERE `tpid` = :tpid
				UNION
				 SELECT tu.`uid`, tu.`membership`
				FROM %topic_user% tu
				WHERE `tpid` = :tpid
				) a
				LEFT JOIN %users% u ON u.`uid` = a.`uid`
			GROUP BY `uid`
			ORDER BY FIELD(`membership`,"ADMIN","MANAGER","TRAINER","OWNER","ACCOUTANT","FINANCE","FOLLOWER","COMMENTATOR","VIEWER","REGULAR MEMBER", "DELETED", "") ASC, CONVERT(u.`name` USING tis620) ASC;',
			'var' => [':tpid' => $nodeId],
			'options' => ['key' => 'uid']
		])->items;
	}

	public static function pageNavigator($conditions) {
		$conditions = (Object) $conditions;
		// $pagePara = is_array($options->pagePara) ? $options->pagePara : array();
		// if ($conditions->year) $pagePara['year'] = $conditions->year;
		// if ($conditions->user) $pagePara['user'] = $conditions->user;
		// if ($conditions->changwat) $pagePara['prov'] = $conditions->changwat;
		// if ($condition->q) $pagePara['q'] = $conditions->q;
		// $pagePara['page'] = $options->page;
		$pagenv = new PageNavigator(
			$conditions->items,
			$conditions->page,
			$conditions->total,
			$conditions->url,
			$conditions->cleanUrl,
			$conditions->pagePara
		);
		return $pagenv;
	}

	public static function hideCommentById(Int $commentId) {
		if (empty($commentId)) return false;

		DB::query([
			'UPDATE %topic_comments% SET
			`status` = IF(`status` = :block, :publish, :block)
			WHERE `cid` = :commentId
			LIMIT 1',
			'var' => [
				':commentId' => $commentId,
				':block' => _BLOCK,
				':publish' => _PUBLISH
			]
		]);
		return true;
	}
}
?>