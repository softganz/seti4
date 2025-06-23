<?php
/**
 * Admin   :: List Topic and Comment
 * Created :: 2020-01-01
 * Modify  :: 2025-06-23
 * Version :: 2
 *
 * @param Object $self
 * @param Int $var
 * @return String
 */

$debug = true;

function admin_comment_list($self) {
	$getItems = \SG\getFirst(post('item'),100);
	$getPage = \SG\getFirst(post('p'),1);
	$getSearch = \SG\getFirst(post('s'),'');
	$getNoConfirm = post('noconfirm');

	$isEdit = user_access('administer comments');

	$self->theme->title = 'Comment list';

	$ret.='<form method="get" action="'.url('admin/comment/list', ['noconfirm' => $getNoConfirm]).'">Search in comment '
		. '<input type="text" name="s" class="form-text" value="'.$getSearch.'" /> '
		. '<button type="submit" class="btn"><i class="icon -material">search</i></button>'
		. '<input type="checkbox" name="noconfirm" value="yes" '.($getNoConfirm ? ' checked="checked"' : '').' />No Confirm on Delete'
		. '</form>';

	$stmt='SELECT
		  c.`tpid`
		, c.`cid`
		, t.`type`
		, t.`title`
		, c.`status`
		, c.`comment`
		, c.`timestamp`
		, c.`uid`
		, IFNULL(u.`name`, c.`name`) `name`
		, u.`status` `userStatus`
		, u.`username`
		FROM %topic_comments%  c
			LEFT JOIN %topic% t USING (tpid)
			LEFT JOIN %users% u ON c.`uid`=u.`uid`
		WHERE (TRIM(c.`subject`) = "")
		'.($getSearch ? ' AND (c.`comment` LIKE :searchStr OR c.`name` LIKE :searchStr)' : '').'
		UNION
		SELECT
		  t.`tpid`
		, NULL
		, t.`type`
		, t.`title`
		, NULL
		, t.`title` `comment`
		, t.`created` `timestamp`
		, t.`uid`
		, IFNULL(u.`name`, t.`poster`) `name`
		, u.`status` `userStatus`
		, u.`username`
		FROM %topic% t
			LEFT JOIN %users% u USING (`uid`)
		'.($getSearch ? 'WHERE t.`title` like :searchStr OR t.`poster` LIKE :searchStr' : '');
	/*
	$stmt.='
		UNION
			SELECT at.tpid, NULL, at.title, NULL, at.title comment, at.created timestamp
				FROM %archive_topic% at
				'.($getSearch?'WHERE at.title LIKE :s OR at.poster LIKE :s':'').'
		UNION
			SELECT ac.tpid, ac.cid, act.title, ac.status, ac.comment, ac.timestamp
			FROM %archive_topic_comments%  ac
				LEFT JOIN %archive_topic% act USING (tpid)
			'.($getSearch?'WHERE ac.comment LIKE :s OR ac.name LIKE :s':'');
	*/
	$stmt .= '
		ORDER BY `timestamp` DESC
		LIMIT '.(($getPage-1)*$getItems).' , '.$getItems;

	$dbs = mydb::select($stmt, ':searchStr', '%'.$getSearch.'%');

	// $ret .= '<pre>'.mydb()->_query.'</pre>';


	$ret .= $page_nv = '<p>Page : '
		. '<a href="'.url(q(),array('s'=>$getSearch, 'noconfirm' => $getNoConfirm)).'">First</a> | '
		. ($getPage > 1 ? '<a href="'.url(q(),array('p'=>$getPage-1,'s'=>$getSearch)).'">Previous</a> | ':'Previous | ').'( <strong>'.$getPage.'</strong> )'.($dbs->_num_rows==$getItems?' | '
		. '<a href="'.url(q(),array('p'=>$getPage+1,'s'=>$getSearch, 'noconfirm' => $getNoConfirm)).'">Next</a>':'')
		. '</p>';

	$tables = new Table();
	$tables->addId('comment-list');
	$tables->caption = 'รายการความคิดเห็นล่าสุด';
	$tables->thead = array('id -amt' => 'หมายเลข','icon -center' => '','รายละเอียด', 'name -nowrap' => 'ผู้โพสท์','create -date'=>'วันที่');

	foreach ($dbs->items as $rs) {
		if ($isEdit) {
			if ($rs->cid) {
				$deleteBtn = '<a class="sg-action" href="'.url('api/paper/'.$rs->tpid.'/comment.delete', ['commentId' => $rs->cid, 'confirm' => $getNoConfirm ? 'yes' : NULL]).'" title="Delete this comment" data-rel="notify" '.($getNoConfirm ? '' : 'data-confirm="Delete this comment?"').' data-before="remove:parent tr"><i class="icon -material">cancel</i></a>';
			} else if ($rs->type == 'forum') {
				$deleteBtn = '<a class="sg-action" href="'.url('paper/'.$rs->tpid.'/delete', ['confirm' => $getNoConfirm ? 'yes' : NULL]).'" title="Delete this paper" data-rel="none" '.($getNoConfirm ? '' : 'data-confirm="Delete this paper?"').' data-before="remove:parent tr"><i class="icon -material">delete</i></a>';
			} else {
				$deleteBtn = '';
			}
		}
		$nodeUrl = '';
		if ($rs->type === 'project') {
			$nodeUrl = url('project/'.$rs->tpid);
		} else {
			$nodeUrl = url('paper/'.$rs->tpid,NULL,$rs->cid?'comment-'.$rs->cid:NULL);
		}
		$tables->rows[] = [
			'<a href="'.$nodeUrl.'" title="'.htmlspecialchars($rs->title).'" target="_blank">'.\SG\getFirst($rs->cid,$rs->tpid).'</a>',
			$deleteBtn,
			sg_text2html($rs->comment),
			$rs->uid ? '<a class="sg-action" href="'.url('profile/'.$rs->uid).'" data-rel="box" data-width="640"><img class="profile-photo" src="'.BasicModel::user_photo($rs->username).'" style="width: 24px; height: 24px;" />'.$rs->name.($rs->userStatus != 'enable' ? ' ('.$rs->userStatus.')' : '').'</a>' : $rs->name,
			$rs->timestamp
		];
	}

	$ret .= $tables->build();
	$ret .= $page_nv;
	return $ret;
}
?>