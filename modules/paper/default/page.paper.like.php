<?php
function paper_like($self,$tpid,$action,$actionType) {
	$self->theme->title='<a href="'.url('paper/like').'">Paper like</a>';

	if (i()->ok && $tpid && $action) {
		if ($action=='remove' && $actionType) {
			$stmt='DELETE FROM %topic_like% WHERE `tpid`=:tpid AND `uid`=:uid AND `action`=:action LIMIT 1';
			mydb::query($stmt,':tpid',$tpid, ':uid',i()->uid,':action',$actionType);
			//$ret.=mydb()->_query;
		} else {
			$data->tpid=$tpid;
			$data->uid=i()->uid;
			$data->action=$action;
			$data->created=date('U');
			$stmt='INSERT INTO %topic_like% (`tpid`,`uid`,`action`,`created`) VALUES (:tpid,:uid,:action,:created) ON DUPLICATE KEY UPDATE `action`=:action';
			mydb::query($stmt,$data);
			//$ret.=mydb()->_query;
		}
	}

	$stmt='SELECT l.*,t.`title`, GROUP_CONCAT(l.`action`) `actionList`
					FROM %topic_like% l
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE l.`uid`=:uid
					GROUP BY `tpid`
					ORDER BY l.`created` DESC';
	$dbs=mydb::select($stmt,':uid',i()->uid);
	$ret.='<ul>';
	foreach ($dbs->items as $rs) {
		$ret.='<li><a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a> ';
		foreach (explode(',',$rs->actionList) as $item) {
			$ret.=' <a href="'.url('paper/like/'.$rs->tpid.'/remove/'.$item).'">'.$item.'</a>';
		}
		$ret.='</li>';
	}
	$ret.='</ul>';
	return $ret;
}
?>