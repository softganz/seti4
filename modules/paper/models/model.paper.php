<?php
/**
* Paper   :: Paper Model
* Created :: 2007-11-21
* Modify  :: 2025-06-24
* Version :: 9
*
* @usage import('model:paper.php');
* @usage new PaperModel([])
* @usage PaperModel::function()
*/

namespace Paper\Model;

use Softganz\DB;

class PaperModel extends \NodeModel {

	public static function get($conditions) {
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

		\mydb::where('t.`tpid` = :tpid', ':tpid', $conditions->tpid);
		\mydb::where(NULL, ':revid', \SG\getFirst($conditions->revid, 't.revid'));

		$stmt = 'SELECT
				  t.`tpid` `nodeId`
				, t.*
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

		$rs = \mydb::select($stmt);
		//debugMsg(mydb()->_query);

		if ($rs->_num_rows) {
			$archived = false;
			mydb()->reset();
		} else if ($rs->_num_rows == 0 && \mydb::table_exists('%archive_topic%')) {
			$stmt = preg_replace(array('#%topic%#s','#%topic_revisions%#s'),array('%archive_topic%','%archive_topic_revisions%'),$stmt);
			$rs = \mydb::select($stmt);
			if ($rs->_num_rows) $archived = true;
		}

		if ($rs->_empty) return NULL;

		\mydb::clearProp($rs);

		if ($rs->orgid && $options->initTemplate) \R::Module('org.template', $rs->orgid);

		$result->nodeId = $rs->nodeId;
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
		$result->right = $right = (Object) [
			'admin' => false,
			'owner' => false,
			'edit' => false,
			'editBackend' => is_admin(),
			'editCss' => is_admin(),
			'editScript' => is_admin(),
			'editData' => is_admin()
		];
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

		foreach (\mydb::select('SELECT `uid`, UPPER(`membership`) `membership` FROM %topic_user% WHERE `tpid` = :tpid',':tpid',$tpid)->items as $item) {
			$result->membership[$item->uid] = $item->membership;
		}

		if ($result->orgid && \mydb::table_exists('%org_officer%')) {
			foreach (DB::select([
				'SELECT `uid`, `membership` FROM %org_officer% WHERE `orgId` = :orgId',
				'var' => [':orgId' => $result->orgid]
			])->items as $item) {
				$result->officers[$item->uid] = strtoupper($item->membership);
			}
		}

		$right->admin = $right->isAdmin = user_access('administer papers,administer contents');
		$right->owner = $right->isOwner = i()->ok && ($result->info->uid == i()->uid || in_array($result->membership[i()->uid], ['OWNER']));

		if ($right->isAdmin) $result->membership[i()->uid] = 'ADMIN';

		$result->info->membershipType = $result->membership[i()->uid];
		$result->info->orgMemberShipType = $result->orgid ? \OrgModel::officerType($result->orgid, i()->uid) : NULL;

		$right->edit = $right->isEdit = $right->isAdmin
						|| $right->isOwner
						|| in_array($result->info->membershipType,array('ADMIN','MANAGER','TRAINER','OWNER'))
						|| in_array($result->info->orgMembershipType,array('ADMIN','MANAGER','TRAINER','OFFICER'));

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
		\mydb::value('$TAG_TOPIC$', '%'.($archived ? 'archive_':'').'tag_topic%');
		$result->tags = \mydb::select(
			'SELECT
				tt.`tid`
				, tt.`vid`
				, t.`name`
				, t.`description`
				, v.`name` vocab_name
			FROM $TAG_TOPIC$ tt
				LEFT JOIN %tag% t ON t.`tid` = tt.`tid`
				LEFT JOIN %vocabulary% v ON tt.`vid` = v.`vid`
			WHERE tpid = :tpid;
			-- {key: "tid"}',
			[':tpid' => $tpid]
		)->items;


		// Get photos
		\mydb::value('$TOPIC_FILES$', '%'.($archived ? 'archive_':'').'topic_files%');
		$result->photos = \mydb::select(
			'SELECT *
			FROM $TOPIC_FILES$
			WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "photo"
			ORDER BY fid;
			-- {key: "fid"}',
			[':tpid' => $tpid]
		)->items;
		foreach ($result->photos as $key => $photo) {
			$result->photos[$key] = object_merge($result->photos[$key],\FileModel::photoProperty($photo->file, $photo->folder));
		}

		// Get docs
		\mydb::value('$TOPIC_FILES$', '%'.($archived ? 'archive_':'').'topic_files%');
		$result->docs = \mydb::select(
			'SELECT *
			FROM $TOPIC_FILES$
			WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "doc"
			ORDER BY fid;
			-- {key: "fid"}',
			[':tpid' => $tpid]
		)->items;
		foreach ($result->docs as $key => $doc) {
			$result->docs[$key] = object_merge($result->docs[$key],\FileModel::docProperty($doc->file, $doc->folder));
		}

		// Get Videos
		if (cfg('topic.video.allow')) {
			$stmt = 'SELECT f.*, u.`username`
							FROM %topic_files% f
								LEFT JOIN %users% u ON u.`uid` = f.`uid`
							WHERE tpid = :tpid AND type = "movie"
							LIMIT 1';
			$result->video = \mydb::select($stmt,':tpid',$tpid);
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
			\mydb::query($stmt, ':tpid', $tpid, ':revid', $result->info->revid, ':property', sg_json_encode(unserialize($result->info->property)));
		}

		//debugMsg($result->property,'$result->property');


		if ( $result->info->profile_picture ) $result->info->profile_picture = cfg('url').'upload/member/'.$result->info->profile_picture;

		if (module_install('poll')) {
			$poll = \mydb::select('SELECT * FROM %poll% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid);
			if ($poll->_num_rows) {
				$result->poll = $poll->items;
				foreach (\mydb::select('SELECT * FROM %poll_choice% WHERE `tpid`=:tpid ORDER BY `choice` ASC',':tpid',$tpid)->items as $pollrs) {
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
		\mydb::query(
			'DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		//$max_auto_id = db_query_one_cell('SELECT MAX(tpid) as max_auto_id FROM %topic%');
		//$result->process[]=mydb()->_query;
		//\mydb::query('ALTER TABLE %topic% AUTO_INCREMENT='.$max_auto_id,$simulate);
		//$result->process[]=mydb()->_query;

		// Delete paper revision
		$result->process[] = 'Delete paper revision';
		\mydb::query(
			'DELETE FROM %topic_revisions% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete topic user
		$result->process[] = 'Delete Topic User';
		\mydb::query(
			'DELETE FROM %topic_user% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[]=mydb()->_query;

		// Delete tag topic
		$result->process[] = 'Delete Tag Topic';
		\mydb::query(
			'DELETE FROM %tag_topic% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete all child/parent of topic
		$result->process[] = 'Delete Topic Parent';
		\mydb::query(
			'DELETE FROM %topic_parent% WHERE `tpid` = :tpid OR `parent` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete topic property
		$result->process[]='Delete topic property';
		\mydb::query(
			'DELETE FROM %property% WHERE `module` = "paper" AND `propId` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete comment post
		$result->process[] = 'Delete comment';

		\mydb::query(
			'DELETE FROM %topic_comments% WHERE tpid = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete all files
		$topicFiles = \mydb::select(
			'SELECT f.*, u.`username` FROM %topic_files% f LEFT JOIN %users% u ON u.`uid` = f.`uid` WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);

		if ($topicFiles->items) {
			$result->process[] = 'Start delete all files';
			\mydb::query(
				'DELETE FROM %topic_files% WHERE tpid = :tpid',
				[':tpid' => $tpid]
			);
			$result->process[] = mydb()->_query;

			foreach ($topicFiles->items as $file) {
				switch ($file->type) {
					case 'photo':
						$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$file->file;
						if (file_exists($filename) and is_file($filename)) {
							$is_photo_inused = \mydb::select(
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
		\LogModel::save([
			'module' => 'paper',
			'keyword' => 'Paper delete',
			'message' => 'Paper/'.$tpid.' was delete'
		]);

		// delete was complete
		$result->complete = true;
		$result->process[] = 'PaperModel::delete completed';
		return $result;
	}

	public static function getCommentById($cid) {
		$stmt = 'SELECT
				c.tpid AS tpid , t.title , c.*
				, u.name AS owner
				, p.fid , p.file AS photo , p.title AS photo_title , p.description AS photo_description
			FROM %topic_comments% AS c
				LEFT JOIN %topic% AS t ON t.tpid=c.tpid
				LEFT JOIN %topic_files% as p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
				LEFT JOIN %users% AS u ON u.uid=c.uid
			WHERE c.`cid` = :cid LIMIT 1';
		$rs=\mydb::select($stmt, ':cid', $cid);
		return $rs;
	}

	public static function createArchive() {
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

	public static function updateInfo($topicInfo, $data) {
		$simulate = debug('simulate');

		$result = (Object) [
			'title' => 'R::paper.info.update '.($simulate?'<strong>simulation</strong> ':'').'request',
			'process' => [],
			'query' => [],
			'data' => NULL,
		];

		// Update detail in revision
		if ($data->detail) {
			$result->process[] = 'Update detail';

			if (is_array($data->detail)) $data->detail = (Object) $data->detail;
			$data->detail->uid = i()->uid;
			$data->detail->timestamp = 'func.NOW()';
			$topic_options = cfg('topic_options_'.$topicInfo->info->type);
			$cols = '`'.implode('`,`',array_keys(\mydb::columns('topic_revisions'))).'`';
			$cols = str_replace('`revid`','NULL',$cols);

			if ($topic_options->revision) {
				\mydb::query('INSERT INTO %topic_revisions% SELECT '.$cols.' FROM %topic_revisions% WHERE revid='.$topicInfo->info->revid.' LIMIT 1',$simulate);
				$result->query[] = \mydb()->_query;

				$data->topic->revid = \mydb()->insert_id;
				\mydb::query(\mydb::create_update_cmd('%topic_revisions%',$data->detail,'revid='.$data->topic->revid),$data->detail);
				$result->query[] = \mydb()->_query;

			} else {
				$stmt = \mydb::create_update_cmd('%topic_revisions%',$data->detail,'tpid='.$topicInfo->tpid.' and revid='.$topicInfo->info->revid.' LIMIT 1');
				//$stmt='UPDATE %topic_revisions% SET `body`=:body, `property`=:property, `timestamp`=:timestamp, `uid`=:uid WHERE `tpid`=:tpid AND `revid`=:revid';
				\mydb::query($stmt,':tpid',$topicInfo->tpid,':revid',$topicInfo->info->revid,$data->detail);
				$result->query[] = \mydb()->_query;
			}
		}

		// Update topic
		if ($data->topic) {
			$result->process[] = 'Update topic';
			// check for clear sticky
			if ($data->clear_sticky && $data->topic['sticky'] && user_access('administer contents')) {
				$sticky = cfg('sticky');
				$result->process[] = 'Clear sticky of '.$sticky[$data->topic->sticky];
				$stmt = 'UPDATE %topic% SET sticky = 0 WHERE sticky = :sticky';
				\mydb::query($stmt, ':sticky', $data->topic['sticky']);
				$result->query[] = \mydb()->_query;
			}

			//unset($data->topic->uid);
			$data->topic['changed'] = date('Y-m-d H:i:d');
			$stmt = \mydb::create_update_cmd('%topic%', $data->topic, 'tpid = '.$topicInfo->tpid.' LIMIT 1');
			\mydb::query($stmt,$simulate);
			$result->query[] = \mydb()->_query;
		}

		// Update photo
		if ($data->photoinfo) {
			$result->process[] = 'Update photo information';
			$stmt = \mydb::create_update_cmd('%topic_files%', $data->photoinfo, 'fid = :fid LIMIT 1');
			\mydb::query($stmt, $data->photoinfo);
			$result->query[] = \mydb()->_query;
		}

		$result->data = $data;

		if ($simulate) {
			return print_o($result,'$result');
		}

		return $result;
	}
} // end of class PaperModel
?>