<?php
/**
* Paper   :: Paper Model
* Created :: 2007-11-21
* Modify  :: 2023-05-07
* Version :: 2
*
* @usage new Model([])
* @usage Model::function($conditions, $options)
*/

import('model:org.php');

class PaperModel {

	public static function get($conditions, $options = '{}') {
		$defaults = '{debug: false; data: "info,all"}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [];

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object)$conditions;
		else {
			$tpid = $conditions;
			$conditions = (Object)[
				'RIGHT' => 0,
				'RIGHTBIN' => '',
			];
			$conditions->tpid = $tpid;
		}

		$tpid = $conditions->tpid;

		mydb::where('t.`tpid` = :tpid', ':tpid', $conditions->tpid);
		mydb::where(NULL, ':revid', \SG\getFirst($conditions->revid, 't.revid'));

		$stmt = 'SELECT
				  t.*
				, ty.`name` `type_name`
				, ty.`module`
				, ty.`description` `type_description`
				, u.`username` as `username`
				, u.`name` as `owner`
				, u.`status` `owner_status`
				, r.`format`
				, r.`body`
				, r.`property`
				, r.`email`
				, r.`homepage`
				, r.`redirect`
			FROM %topic% t
				LEFT JOIN %topic_revisions% r ON r.`revid` = t.`revid`
				LEFT JOIN %users% u ON t.`uid` = u.`uid`
			LEFT JOIN %topic_types% ty ON ty.`type` = t.`type`
			%WHERE%
			LIMIT 1;
			-- {reset:false}';

		$rs = mydb::select($stmt);
		//debugMsg(mydb()->_query);

		if ($rs->_num_rows) {
			$archived = false;
			mydb()->reset();
		} else if ($rs->_num_rows == 0 && mydb::table_exists('%archive_topic%')) {
			$stmt = preg_replace(array('#%topic%#s','#%topic_revisions%#s'),array('%archive_topic%','%archive_topic_revisions%'),$stmt);
			$rs = mydb::select($stmt);
			if ($rs->_num_rows) $archived = true;
		}

		if ($rs->_empty) return NULL;

		mydb::clearProp($rs);

		if ($rs->orgid && $options->initTemplate) R::Module('org.template', $rs->orgid);

		$result->tpid = $rs->tpid;
		$result->orgid = $rs->orgid;
		$result->title = $rs->title;
		$result->uid = $rs->uid;
		$result->RIGHT = NULL;
		$result->RIGHTBIN = NULL;
		$result->archived = $archived;
		$result->info = $rs;
		$result->membership = NULL;
		$result->officers = NULL;
		$result->right = $right = new stdClass();
		$result->options = NULL;
		$result->is = NULL;


		// Check no post comment on topic was created more than comment.close.day
		if (cfg('comment.close.day')) {
			$dateclose = date('Y-m-d H:i:s',mktime(0,0,0,date('m'),date('d') - cfg('comment.close.day'),date('Y')));
			if ($result->info->created < $dateclose) {
				$result->info->comment = _COMMENT_READ;
			}
		}


		if ($result->info->uid) $result->membership[$result->info->uid] = 'OWNER';

		foreach (mydb::select('SELECT * FROM %topic_user% WHERE `tpid` = :tpid',':tpid',$tpid)->items as $item) {
			$result->membership[$item->uid] = strtoupper($item->membership);
		}

		if ($result->orgid) {
			foreach (mydb::select('SELECT * FROM %org_officer% WHERE `orgid` = :orgid',':orgid',$result->orgid)->items as $item) {
				$result->officers[$item->uid] = strtoupper($item->membership);
			}
		}

		$right->isAdmin = user_access('administer papers,administer contents');
		$right->isOwner = i()->ok && ($result->info->uid == i()->uid || $result->membership[i()->uid] == 'OWNER');

		if ($right->isAdmin) $result->membership[i()->uid] = 'ADMIN';

		$result->info->membershipType = $result->membership[i()->uid];
		$result->info->orgMemberShipType = $result->orgid ? OrgModel::officerType($result->orgid, i()->uid) : NULL;

		$right->isEdit = $right->isAdmin
						|| $right->isOwner
						|| in_array($result->info->membershipType,array('ADMIN','MANAGER','OWNER'))
						|| in_array($result->info->orgMembershipType,array('ADMIN','MANAGER','OFFICER'));

		if ($right->isAdmin) $result->RIGHT = $result->RIGHT | _IS_ADMIN;
		if ($right->isOwner) $result->RIGHT = $result->RIGHT | _IS_OWNER;
		if ($right->isTrainer) $result->RIGHT = $result->RIGHT | _IS_TRAINER;
		if ($right->isRight) $result->RIGHT = $result->RIGHT | _IS_ACCESS;
		if ($right->isEdit) $result->RIGHT = $result->RIGHT | _IS_EDITABLE;
		if ($right->isEditDetail) $result->RIGHT = $result->RIGHT | _IS_EDITDETAIL;


		//$result->RIGHT = $right;
		$result->RIGHTBIN = decbin($result->RIGHT);


		if ($options->data == 'info') return $result;




		// Get tags of topic
		mydb::value('$TAG_TOPIC$', '%'.($archived ? 'archive_':'').'tag_topic%');
		$stmt = 'SELECT
								tt.`tid`
								, tt.`vid`
								, t.`name`
								, t.`description`
								, v.`name` vocab_name
							FROM $TAG_TOPIC$ tt
								LEFT JOIN %tag% t ON t.`tid` = tt.`tid`
								LEFT JOIN %vocabulary% v ON tt.`vid` = v.`vid`
							WHERE tpid = :tpid;
							-- {key: "tid"}';

		$result->tags = mydb::select($stmt, ':tpid', $tpid)->items;


		// Get photos
		mydb::value('$TOPIC_FILES$', '%'.($archived ? 'archive_':'').'topic_files%');
		$stmt = 'SELECT *
						FROM $TOPIC_FILES$
						WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "photo"
						ORDER BY fid;
						-- {key: "fid"}';
		$result->photos = mydb::select($stmt, ':tpid', $tpid)->items;
		foreach ($result->photos as $key => $photo) {
			$result->photos[$key] = object_merge($result->photos[$key],BasicModel::get_photo_property($photo->file));
		}


		// Get Videos
		if (cfg('topic.video.allow')) {
			$stmt = 'SELECT f.*, u.`username`
							FROM %topic_files% f
								LEFT JOIN %users% u ON u.`uid` = f.`uid`
							WHERE tpid = :tpid AND type = "movie"
							LIMIT 1';
			$result->video = mydb::select($stmt,':tpid',$tpid);
			if ($result->video->file) {
				if (preg_match('/^http\:\/\//',$result->video->file)) {
					$result->video->_url = $result->video->file;
					$result->video->_location = NULL;
				} else {
					//$result->video->_url=cfg('domain').cfg('upload.url').$result->video->username.'/'.$result->video->file;
					$result->video->_url = cfg('upload.url').$result->video->username.'/'.$result->video->file;
					$result->video->_location = sg_user_folder($result->info->username).$result->video->file;
				}
			}
		}


		// Set topic property
		$result->property = sg_json_decode($result->info->property, cfg('topic.property'));

		//debugMsg($result->info->property);

		// Update old property to json
		if (substr($result->info->property,0,1) == 'O') {
			$stmt = 'UPDATE %topic_revisions% SET `property` = :property WHERE `tpid` = :tpid AND `revid` = :revid LIMIT 1';
			mydb::query($stmt, ':tpid', $tpid, ':revid', $result->info->revid, ':property', sg_json_encode(unserialize($result->info->property)));
		}

		//debugMsg($result->property,'$result->property');


		if ( $result->info->profile_picture ) $result->info->profile_picture = cfg('url').'upload/member/'.$result->info->profile_picture;

		if (module_install('poll')) {
			$poll = mydb::select('SELECT * FROM %poll% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid);
			if ($poll->_num_rows) {
				$result->poll = $poll->items;
				foreach (mydb::select('SELECT * FROM %poll_choice% WHERE `tpid`=:tpid ORDER BY `choice` ASC',':tpid',$tpid)->items as $pollrs) {
					$result->poll->{$pollrs->choice} = $pollrs;
				}
			}
		}

		return $result;
	}

	public static function delete($tpid) {
		$result = (Object) [
			'complete' => false,
			'error' => false,
			'process' => ['PaperModel::delete request'],
		];

		if (empty($tpid)) {
			$result->error = 'Empty topic';
			return $result;
		}

		// if set to true , simulate sql (not insert ) and show sql command
		$simulate = debug('simulate');

		// Delete paper topic
		$result->process[] = 'Delete paper topic';
		mydb::query(
			'DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		//$max_auto_id = db_query_one_cell('SELECT MAX(tpid) as max_auto_id FROM %topic%');
		//$result->process[]=mydb()->_query;
		//mydb::query('ALTER TABLE %topic% AUTO_INCREMENT='.$max_auto_id,$simulate);
		//$result->process[]=mydb()->_query;

		// Delete paper revision
		$result->process[] = 'Delete paper revision';
		mydb::query(
			'DELETE FROM %topic_revisions% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete topic user
		$result->process[] = 'Delete Topic User';
		mydb::query(
			'DELETE FROM %topic_user% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[]=mydb()->_query;

		// Delete tag topic
		$result->process[] = 'Delete Tag Topic';
		mydb::query(
			'DELETE FROM %tag_topic% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete all child/parent of topic
		$result->process[] = 'Delete Topic Parent';
		mydb::query(
			'DELETE FROM %topic_parent% WHERE `tpid` = :tpid OR `parent` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete topic property
		$result->process[]='Delete topic property';
		mydb::query(
			'DELETE FROM %property% WHERE `module` = "paper" AND `propId` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete comment post
		$result->process[] = 'Delete comment';

		mydb::query(
			'DELETE FROM %topic_comments% WHERE tpid = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete all files
		$topicFiles = mydb::select(
			'SELECT f.*, u.`username` FROM %topic_files% f LEFT JOIN %users% u ON u.`uid` = f.`uid` WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);

		if ($topicFiles->items) {
			$result->process[] = 'Start delete all files';
			mydb::query(
				'DELETE FROM %topic_files% WHERE tpid = :tpid',
				[':tpid' => $tpid]
			);
			$result->process[] = mydb()->_query;

			foreach ($topicFiles->items as $file) {
				switch ($file->type) {
					case 'photo':
						$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$file->file;
						if (file_exists($filename) and is_file($filename)) {
							$is_photo_inused = mydb::select(
								'SELECT `fid` FROM %topic_files% WHERE `file` = :file AND `fid` != :fid LIMIT 1',
								[
									':file' => $file->file,
									':fid' => $file->fid
								]
							)->fid;
							$result->process[] = mydb()->_query;

							if ($is_photo_inused) {
								$result->process[] = 'File <em>'.$file->_file.'</em> was used by other item';
							} else {
								$result->process[] = '<em>Delete file '.$filename.'</em>';
								if (!$simulate) unlink($filename);
							}
						}
						break;

					case 'doc':
						$filename = cfg('folder.abs').cfg('upload_folder').'forum/'.$file->file;
						$result->process[]= '<em>Delete document '.$filename.'</em>';
						if (!$simulate && file_exists($filename) && is_file($filename)) unlink($filename);
						break;

					case 'audio':
					case 'movie':
						$movieLocation = sg_user_folder($file->username).$file->file;

						if ($movieLocation && file_exists($movieLocation) && is_file($movieLocation)) {
							if (!$simulate) unlink($movieLocation);
							$result->process[]='<em>Delete video file '.$movieLocation.'</em>';
						}
						break;

					default:
						$result->process[] = 'Invalid file type "'.$file->type.'" of '.$file->file;
				}
			}
		}

		// Save delete log
		BasicModel::watch_log('paper','Paper delete','Paper/'.$tpid.' was delete');

		// delete was complete
		$result->complete = true;
		$result->process[] = 'PaperModel::delete completed';
		return $result;
	}

	public static function get_photo_property($file,$photo) {
		if (isset($photo)) $property=$photo;
		$property->_src=_URL.cfg('upload_folder').'pics/'.sg_urlencode($file);
		$property->_file=$photo_location=cfg('folder.abs').cfg('upload_folder').'pics/'.$file;
		$property->_url=cfg('url.abs').cfg('upload_folder').'pics/'.sg_urlencode($file);
		//		echo 'url.abs='.cfg('url.abs').' : '.$property->_url.'<br />';
		$property->_filesize=filesize($photo_location);
		if (file_exists($photo_location)) {
			$size=getimagesize($photo_location);
			$property->_exists=true;
			$property->_size->width=$size[0];
			$property->_size->height=$size[1];
			$property->_size->attr=$size[3];
			$property->_size->bits=$size['bits'];
			$property->_size->channels=$size['channels'];
			$property->_size->mime=$size['mime'];
		} else $property->_exists=false;
		return $property;
	}

	public static function get_topic_by_id($tpid,$para=NULL,$revid=NULL) {
		$sql_cmd = 'SELECT
			  t.*
			, ty.`name` type_name
			, ty.`module`
			, ty.`description` type_description
			, u.`username` as username
			, u.`name` as owner
			, u.`status` owner_status
			, r.`format`
			, r.`body`
			, r.`property`
			, r.`email`
			, r.`homepage`
			, r.`redirect` ';

		if (module_install('voteit')) $sql_cmd.=do_class_method('voteit','get_topic_by_condition','fields',$para,$tpid);

		$sql_cmd .= '  FROM %topic% t
				LEFT JOIN %topic_revisions% r ON r.revid='.\SG\getFirst($revid,'t.revid ').'
				LEFT JOIN %users% u ON t.uid=u.uid
				LEFT JOIN %topic_types% ty ON ty.type=t.type ';

		if (module_install('voteit')) $sql_cmd.=do_class_method('voteit','get_topic_by_condition','join',$para,$tpid);

		$sql_cmd .= '  WHERE t.tpid='.$tpid.'
							LIMIT 1';


		$rs = mydb::select($sql_cmd);
		if ($rs->_num_rows) {
			$rs->_archive=false;
		} else if ($rs->_num_rows==0 && mydb::table_exists('%archive_topic%')) {
			$sql_cmd=preg_replace(array('#%topic%#s','#%topic_revisions%#s'),array('%archive_topic%','%archive_topic_revisions%'),$sql_cmd);
			$rs = mydb::select($sql_cmd);
			if ($rs->_num_rows) $rs->_archive=true;
		}

		if ( $rs->_num_rows ) {
			$tags=mydb::select('SELECT tt.`tid`,tt.`vid`,
					t.`name`, t.`description`,
					v.`name` vocab_name
				FROM %'.($rs->_archive?'archive_':'').'tag_topic% tt
					LEFT JOIN %tag% t ON t.tid=tt.tid
					LEFT JOIN %vocabulary% v ON tt.`vid`=v.`vid`
				WHERE tpid='.$rs->tpid);
			foreach ($tags->items as $tag) $rs->tags[]=(object)array('tid'=>$tag->tid,'name'=>$tag->name,'vid'=>$tag->vid,'vocab_name'=>$tag->vocab_name,'description'=>$tag->description?$tag->description:null);

			$rs->photo = mydb::select('SELECT * FROM %'.($rs->_archive?'archive_':'').'topic_files% WHERE `tpid`='.$rs->tpid.' AND `cid`=0 AND `type`="photo" ORDER BY fid');
			foreach ($rs->photo->items as $key=>$photo) $rs->photo->items[$key]=object_merge($rs->photo->items[$key],BasicModel::get_photo_property($photo->file));

			if (cfg('topic.video.allow')) {
				$rs->video=mydb::select('SELECT f.*,u.username FROM %topic_files% f LEFT JOIN %users% u ON u.uid=f.uid WHERE tpid=:tpid AND type="movie" LIMIT 1',':tpid',$tpid);
				if ($rs->video->file) {
					if (preg_match('/^http\:\/\//',$rs->video->file)) {
						$rs->video->_url=$rs->video->file;
						$rs->video->_location=NULL;
					} else {
						//$rs->video->_url=cfg('domain').cfg('upload.url').$rs->video->username.'/'.$rs->video->file;
						$rs->video->_url=cfg('upload.url').$rs->video->username.'/'.$rs->video->file;
						$rs->video->_location=sg_user_folder($rs->username).$rs->video->file;
					}
				}
			}

			// Set topic property
			$rs->property = sg_json_decode($rs->property, cfg('topic.property'));

			if ( $rs->profile_picture ) $rs->profile_picture = cfg('url').'upload/member/'.$rs->profile_picture;
			if (module_install('poll')) {
				$poll=mydb::select('SELECT * FROM %poll% WHERE `tpid`=:tpid LIMIT 1',':tpid',$rs->tpid);
				if ($poll->_num_rows) {
					$rs->poll=$poll;
					foreach (mydb::select('SELECT * FROM %poll_choice% WHERE `tpid`=:tpid ORDER BY `choice` ASC',':tpid',$rs->tpid)->items as $pollrs) {
						$rs->poll->items->{$pollrs->choice}=$pollrs;
					}
				}
			}

			// do external module post form
			$rs->_content_type_property=cfg('topic_options_'.$rs->type);
			if (function_exists('module2classname')) {
				$classname=module2classname($rs->module);
				if (module_exists($classname,'__get_topic_by_id')) call_user_func(array($classname,'__get_topic_by_id'),NULL,$rs,$para);
			}
		}
		return $rs;
	}

	public static function get_topic_by_condition($para) {
		$items = SG\getFirst($para->items,10);
		$field=option($para->field);
		$sort=in_array($para->sort,array('asc','desc'))?$para->sort:'desc';

		//debugMsg($para,'$para');
		if ($para->category) {
			$tags=explode(',',mydb::select('SELECT `tid` FROM %tag_synonym% WHERE name=:category',':category',$para->category)->lists->text);
			//debugMsg($tags,'$tags');
		}

		$fld_cmd .= ' DISTINCT t.* ';
		$fld_cmd .= '  , u.username as username,u.name as owner ';
		if ($field->detail) $fld_cmd .= '    , r.format , r.body , r.property , r.email , r.homepage ';
		if ($field->comment) $fld_cmd.=' ,(SELECT COUNT(*) FROM %topic_comments% WHERE `tpid`=t.`tpid`) comments';
		//		if ($field->photo) $fld_cmd .= '    , pics.fid as pic_id , pics.file as photo , pics.title as photo_name , pics.description as photo_description';

		if (module_install('voteit')) $fld_cmd.=do_class_method('voteit','get_topic_by_condition','fields',$para);

		$table_cmd = ' %topic% as t ';
		$table_cmd .= '  LEFT JOIN %users% as u ON t.uid=u.uid ';
		if ($field->detail) $table_cmd .= '  LEFT JOIN %topic_revisions% as r on r.revid=t.revid ';
		if ($para->tag || $para->category) $table_cmd .= '  LEFT JOIN %tag_topic% tp ON tp.tpid=t.tpid ';
		if ($para->category) $table_cmd .= '  LEFT JOIN %tag% tg ON tg.tid=tp.tid ';
		//		if ($field->photo) $table_cmd .= '  LEFT JOIN ( %topic_files% AS pics
		//														LEFT JOIN %topic_files% as pic2 ON pic2.tpid=pics.tpid AND pics.cid=0 AND pic2.type="photo" AND pics.fid>pic2.fid
		//														) ON pics.tpid=t.tpid AND pics.cid=0 AND pics.type="photo" ';

		if (module_install('voteit')) $table_cmd.=do_class_method('voteit','get_topic_by_condition','join',$para);

		$where=array();
		if ($para->type) $where[]=strpos(',',$para->type)?'t.type in ("'.implode('","',explode(',',$para->type)).'")':'t.`type`="'.$para->type.'"';
		if ($para->category) {
			$tags=db_query_one_column('SELECT tid FROM %tag_synonym% WHERE name="'.$para->category.'"');
			$where[]='tg.tid in ('.implode(',',$tags).')';
		}
		if ($para->tag) $where[]='tp.tid in ('.$para->tag.')';
		if ($para->sticky) $where[]='sticky='.$para->sticky;
		if ($para->user) $where[]='t.uid="'.$para->user.'"';
		if ($para->ip) $where[]='t.ip='.ip2long($para->ip);
		if ($para->year) $where[]='YEAR(t.created)="'.addslashes($para->year).'"';
		if (i()->ok) {
			if (!user_access('administer contents,administer papers')) $where[]='t.status in ('._PUBLISH.','._LOCK.') || (t.status in ('._DRAFT.','._WAITING.') AND t.uid='.i()->uid.')';
		} else {
			$where[]='t.status in ('._PUBLISH.','._LOCK.')';
		}
		if ($para->condition) $where[]=$para->condition;

		//		if ($field->photo) $where[]='pic2.fid IS NULL';

		$where_cmd = $where ? '('.implode(') AND (',$where).')' : null;

		$sql_cmd = 'SELECT '.$fld_cmd.' FROM '.$table_cmd.($where_cmd?' WHERE '.$where_cmd:'');
		$sql_cmd .= ' ORDER BY '.\SG\getFirst($para->order,'t.tpid').' '.\SG\getFirst($sort,'DESC');

		if ($para->limit) {
			$sql_cmd .= '  LIMIT '.$para->limit;
		} else {
			$total_items=mydb::select('SELECT COUNT(*) `total` FROM '.$table_cmd.($where_cmd?' WHERE '.$where_cmd:'').' LIMIT 1')->total;
			$count_query=mydb()->_query;
			$pagenv = new PageNavigator($items,$para->page,$total_items,q());
			$sql_cmd .= '  LIMIT '.($pagenv->FirstItem()<0 ? 0 : $pagenv->FirstItem()).','.$items;
		}

		$topics=mydb::select($sql_cmd);
		$topics->page=$pagenv;
		$topics->_query_count=$count_query;

		$result=sg_clone($topics);
		$result->items=array();
		foreach ($topics->items as $key=>$topic) {
			$topic_list[]=$topic->tpid;
			$topic->summary=sg_summary_text($topic->body);
			//			if ($topic->photo) $topic->photo=BasicModel::get_photo_property($topic->photo);
 			$topic->profile_picture=BasicModel::user_photo($topic->username);
			//			if ($topic->profile_picture ) $topic->profile_picture = cfg('url').'upload/member/'.$topic->profile_picture;
			$result->items[$topic->tpid]=$topic;
		}
		if ($field->photo && $topic_list) {
			$sql_cmd = 'SELECT `tpid`,`file` FROM %topic_files% WHERE tpid in ('.implode(',',$topic_list).') AND `cid`=0 AND `type`="photo" GROUP BY `tpid` ORDER BY `tpid` ASC';
			$photos=mydb::select($sql_cmd);
			//			echo mydb()->_query;
			//			print_o($photos,'$photos',1);
			foreach ($photos->items as $photo) {
				$result->items[$photo->tpid]->photo=BasicModel::get_photo_property($photo->file);
			}
		}
		//		print_o($topic_list,'$topic_list',1);
		//debugMsg($result,'$result');
		return $result;
	}

	public static function get_comment_by_id($cid) {
		$stmt = 'SELECT
				c.tpid AS tpid , t.title , c.*
				, u.name AS owner
				, p.fid , p.file AS photo , p.title AS photo_title , p.description AS photo_description
			FROM %topic_comments% AS c
				LEFT JOIN %topic% AS t ON t.tpid=c.tpid
				LEFT JOIN %topic_files% as p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
				LEFT JOIN %users% AS u ON u.uid=c.uid
			WHERE c.`cid` = :cid LIMIT 1';
		$rs=mydb::select($stmt, ':cid', $cid);
		return $rs;
	}

	public static function modify_photo($photo,$post) {
		$result->error=false;
		$result->process[]='PaperModel::modify_photo request';
		$result->post=print_o($post,'$post');
		$result->photo=print_o($photo,'$photo');

		$photo_description=$post;

		if ($post->photo) {
			$result->upload = R::Model('photo.save',$post->photo);
			if ($result->upload->complete) {
				$photo_description->file=$result->upload->save->_file;
				if ($result->upload->save->_file != $photo->file &&
						file_exists($photo->_file) && is_file($photo->_file) &&
						mydb::select('SELECT COUNT(*) `total` FROM %topic_files% WHERE `file`=:file AND `type`="photo" LIMIT 1',':file',$photo->file)->total<=1) {
					$result->process[]='Delete old photo file <em>'.$photo->_file.'</em>';
					unlink($photo->_file);
				}
			} else $result->error=$result->upload->error;
		}

		unset($photo_description->photo);
		$sql_cmd=mydb::create_update_cmd('%topic_files%',$photo_description,'fid='.$photo->fid.' LIMIT 1');
		mydb::query($sql_cmd,$photo_description);
		$result->query[]=mydb()->_query;
		if (mydb()->_error) $result->error[]='Query error';
		return $result;
	}

	public static function delete_photo($photo_id=array(),$is_simulate=false) {
		$result->error=false;
		$result->process[]='PaperModel::delete_photo request';

		if (empty($photo_id)) return $result;
		if (is_string($photo_id)) {$id=$photo_id;unset($photo_id);$photo_id[]=$id;}

		$photos=mydb::select('SELECT * FROM %topic_files% WHERE `fid` IN (:fid) AND `type`="photo"',':fid','SET:'.implode(',',$photo_id));
		$result->query[]=mydb()->_query;
		if ($photos->_num_rows<=0) {
			$result->error='No photo file to delete';
			return $result;
		}

		$result->process[]=($is_simulate?'Simulation ':'').'Process starting to delete '.$photos->_num_rows.' item(s).';
		$stmt = 'DELETE FROM %topic_files% WHERE `fid` IN ('.implode(',',$photo_id).') AND `type`="photo"';
		mydb::query($stmt,$is_simulate);
		$result->query[]=mydb()->_query;

		foreach ($photos->items as $item) {
			$photo=BasicModel::get_photo_property($item->file);
			$result->process[]='Start delete file <em>'.$photo->_file.'</em>';
			if (file_exists($photo->_file) and is_file($photo->_file)) {
				$is_inused=mydb::select('SELECT * FROM %topic_files% WHERE `file`=:file AND fid!=:fid AND `type`="photo" LIMIT 1',':file',$item->file,':fid',$item->fid)->fid;
				$result->query[]=mydb()->_query;
				if ($is_inused) {
					$result->process[]='File <em>'.$photo->_file.'</em> was inused by other item.';
				} else {
					if (!$is_simulate) unlink($photo->_file);
					$result->process[]='File <em>'.$photo->_file.'</em> has been deleted.';
					$result->deleted->id[]=$item->fid;
					$result->deleted->file[]=$item->file;
				}
			}
		}

		if ($result->deleted->file) $result->deleted->name=implode(',',$result->deleted->file);
		$result->process[]='PaperModel::delete_photo request complete';
		return $result;
	}

	public static function toggle_topic_comment_status($tpid) {
		$sql_cmd = 'UPDATE %topic% SET comment=IF(comment=0,2,0) WHERE tpid='.$tpid.' LIMIT 1';
		mydb::query($sql_cmd);
		return;
	}



	public static function create_category_list($paras="") {
		$sql_cmd  = 'SELECT  f.fid as fid , f.name as forum_name , c.cid as cid , c.name as cat_name ';
		$sql_cmd .= '  FROM %forum_categorys% as c ';
		$sql_cmd .= '  LEFT JOIN %forum_id% as f ON c.fid=f.fid ';
		$sql_cmd .= '  ORDER BY f.sort_order,c.sort_order ASC';
		$cat_list= db_query_array($sql_cmd);


		foreach ( $cat_list as $rs ) $forum[$rs["fid"]][]=$rs;

		$nl = "\n";
		$tab = "\t";
		$script = "[ ['',''],['',''],".$nl;
		$no = 0;

		foreach ( $forum as $fid=>$cat) {
			$script .= "$tab ['$fid','{$cat[0]["forum_name"]}',$nl";
			foreach ( $cat as $cat_rs ) {
				$script .= "$tab $tab ['{$cat_rs["cid"]}' , '{$cat_rs["cat_name"]}'],$nl";
			}
			$script .= "$tab ],$nl$nl ";
		}
		$script .= "]";
		return $script;
	}

	public static function create_archive() {
		/*
		SELECT tpid,created  FROM `sgz_topic` WHERE DATE_FORMAT(`created`,"%Y-%m-%d")<"2008-07-01" ORDER BY created DESC LIMIT 1

		DELETE FROM `sgz_topic` WHERE tpid<=10845;
		DELETE FROM `sgz_tag_topic` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_revisions` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_comments` WHERE tpid<=10845;
		DELETE FROM `sgz_topic_files` WHERE tpid<=10845;

		DELETE FROM `sgz_archive_topic` WHERE tpid>10845;
		DELETE FROM `sgz_archive_tag_topic` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_revisions` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_comments` WHERE tpid>10845;
		DELETE FROM `sgz_archive_topic_files` WHERE tpid>10845;
		*/
	}

} // end of class PaperModel
?>