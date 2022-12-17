<?php
/**
* Get Paper Topic Information
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

import('model:org.php');

function r_paper_get($conditions, $options = '{}') {
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
	mydb::where(NULL, ':revid', SG\getFirst($conditions->revid, 't.revid'));

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
		$result->photos[$key] = object_merge($result->photos[$key],CommonModel::get_photo_property($photo->file));
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
?>