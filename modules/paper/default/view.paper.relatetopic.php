<?php
/**
* View Relate Topic
* Created 2019-06-05
* Modify  2019-06-05
*
* @param 
* @return String
*/

import('model:ad.php');

function view_paper_relatetopic($topicInfo) {
	$ret = '';

	if (!($topicInfo->property->option->related && $topicInfo->tags)) return NULL;
	foreach ($topicInfo->tags as $tag) $ref_tag[]=$tag->tid;
	$reftag = implode(',',$ref_tag);

	/******** Slow query **************/

	mydb::where('t.`tpid` != :tpid', ':tpid', $topicInfo->tpid);
	mydb::where('t.`status` IN ( :show )', ':show', 'SET:'._PUBLISH.','._LOCK);
	mydb::where('tg.`tid` IN ( :reftag)', ':reftag', 'SET:'.$reftag);
	mydb::value('$LIMIT$', 'LIMIT '.cfg('topic.relate.items'));

	$stmt = 'SELECT DISTINCT
					  tg.`tpid`
					, t.`title`
					, t.`status`
					, t.`created`
				FROM %topic% t
					LEFT JOIN %tag_topic% tg USING(`tpid`)
				%WHERE%
				ORDER BY tg.`tpid` DESC
				$LIMIT$';

	$ref_dbs = mydb::select($stmt);
	//debugMsg($ref_dbs);

	if ($ref_dbs->_empty) return NULL;


	foreach ($ref_dbs->items as $key=>$ref_rs) {
		// clear current or not publish topic
		if ($ref_rs->tpid==$topicInfo->tpid || !in_array($ref_rs->status,array(_PUBLISH,_LOCK))) {
			unset($ref_dbs->items[$key]);
			continue;
		}
		$ref_dbs->tpid[]=$ref_rs->tpid;
	}

	if (cfg('topic.relate.detail.length') && $ref_dbs->tpid) {
		$stmt = 'SELECT r.`tpid`,LEFT(r.`body`,'.cfg('topic.relate.detail.length').') body,(SELECT f.`file` FROM %topic_files% f WHERE f.tpid=r.tpid LIMIT 1) photo
					FROM %topic_revisions% r
					WHERE r.tpid IN ('.implode(',',$ref_dbs->tpid).')';

		foreach (mydb::select($stmt)->items as $ref_body_rs) {
			$ref_dbs->body[$ref_body_rs->tpid] = $ref_body_rs;
			if ($ref_body_rs->photo) {
				$ref_dbs->body[$ref_body_rs->tpid]->photo = model::get_photo_property($ref_body_rs->photo);
			}
		}
	}
	$ret .='<div class="paper -relate-topics'.(cfg('topic.relate.detail.length')?' -detail':'').'">'._NL;
	if ($showAd && isset($GLOBALS['ad']->relate_topic)) {
		$ret .='<div id="ad-relate_topic" class="ads -relate-topics"><h3>ลิงก์ผู้สนับสนุน</h3>'.$GLOBALS['ad']->relate_topic.'</div>';
	}

	$ret .= AdModel::getAd('relate_topic');
	$ret .= '<h3>'.tr('Relate topics').'</h3>'._NL;
	$ret .= '<ul class="topic-list -relate-topics">'._NL;
	$no=0;
	foreach ($ref_dbs->items as $ref_rs) {
		$ret .='<li><a href="'.url(($topicInfo->_relate_url?$topicInfo->_relate_url:'paper').'/'.$ref_rs->tpid).'">';
		$ret .=(cfg('topic.relate.detail.length') && $ref_dbs->body[$ref_rs->tpid]->photo?'<img src="'.$ref_dbs->body[$ref_rs->tpid]->photo->_url.'" width="140" />':'');
		$ret .='<span class="title">'.$ref_rs->title.'</span>';
		$ret .=(cfg('topic.relate.detail.length')?'<span class="detail">'.strip_tags(sg_text2html(strip_tags($ref_dbs->body[$ref_rs->tpid]->body))).'</span>':'');
		$ret .='</a> ';
		$ret .='<span class="timestamp">'.sg_date($ref_rs->created,cfg('dateformat')).'</span></li>'._NL;
		if (++$no>=cfg('topic.relate.items')) break;
	}
	$ret .='</ul>'._NL;
	$ret .='</div><!--relate-topics-->'._NL._NL;

	return $ret;
}
?>