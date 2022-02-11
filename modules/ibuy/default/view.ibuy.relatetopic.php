<?php
/**
* View Relate Topic
* Created 2019-06-05
* Modify  2019-06-05
*
* @param 
* @return String
*/

$debug = true;

function view_ibuy_relatetopic($topicInfo) {
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
					, p.*
					, (SELECT `file` FROM %topic_files% f WHERE f.`tpid` = t.`tpid` ORDER BY f.`fid` LIMIT 1) `photo`
				FROM %topic% t
					LEFT JOIN %ibuy_product% p USING(`tpid`)
					LEFT JOIN %tag_topic% tg USING(`tpid`)
				%WHERE%
				ORDER BY tg.`tpid` DESC
				$LIMIT$;';

	$ref_dbs = mydb::select($stmt);
	//debugMsg($ref_dbs);

	if ($ref_dbs->_empty) return NULL;

	$ret .='<div class="ibuy -relate-topics'.(cfg('topic.relate.detail.length')?' -detail':'').'">'._NL;
	if ($showAd && isset($GLOBALS['ad']->relate_topic)) {
		$ret .='<div id="ad-relate_topic" class="ads -relate-topics"><h3>ลิงก์ผู้สนับสนุน</h3>'.$GLOBALS['ad']->relate_topic.'</div>';
	}
	$ret .=model::get_ad('relate_topic');
	$ret .='<h3>'.tr('Relate Products').'</h3>'._NL;

	$ui = new Ui(NULL, 'ui-card');

	foreach ($ref_dbs->items as $rs) {
		$cardStr = '';
		if ($rs->photo) $photo = model::get_photo_property($rs->photo);
		$cardStr .= '<div class="photo-th'.($photo->_url ? '' : ' -no-photo').'">';
		if ($photo->_url) {
			$cardStr .= '<a href="'.url('ibuy/'.$rs->tpid).'">';
			$cardStr .= '<img class="photo" src="'.$photo->_url.'" height="140" />';
			$cardStr .= '</a>';
		}
		$cardStr .= '</div>';
		$cardStr .= '<a href="'.url('ibuy/'.$rs->tpid).'">';
		$cardStr .= '<h3 class="title">'.$rs->title.'</h3>';
		$cardStr .= '</a> ';
		//$cardStr .= (cfg('topic.relate.detail.length')?'<span class="detail">'.strip_tags(sg_text2html(strip_tags($ref_dbs->body[$rs->tpid]->body))).'</span>':'');
		$cardStr .= R::View('ibuy.price.label', $rs);
		$cardStr .= R::View('ibuy.sale.label', $rs);

		//$cardStr .= print_o($photo,'$photo');
		//$cardStr .= print_o($rs,'$rs');

		$ui->add($cardStr);
	}

	if ($ref_dbs->_num_rows % 4) {
		for ($i = $ref_dbs->_num_rows % 4; $i < 4; $i++) {
			$ui->add('&nbsp;','{class: "-empty"}');
		}
	}

	$ret .= $ui->build();

	$ret .='</div><!--relate-topics-->'._NL._NL;
	//$ret .= 'Count '.($ref_dbs->_num_rows % 4);

	return $ret;
}
?>