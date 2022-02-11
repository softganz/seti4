<?php
/**
* Project API : Activity
*
* @param Object $self
* @return String
*/
function project_api_activity($self,$tpid=NULL) {
	$projectSet = post('prset');
	$limit = SG\getFirst(post('limit',_ADDSLASHES),5);
	$photoWidth = 240;
	$photoHeight = 144;
	$dateformat = SG\getFirst(post('dateformat'),cfg('dateformat'));

	$ret = [];
	$ret['title'] = 'Activity';
	$ret['host'] = _DOMAIN;
	$ret['html'] = '';
	$ret['data'] = [];
	
	mydb::where('tr.`formid` = "activity" AND tr.`part` IN ("owner","trainer")');
	if ($projectSet) mydb::where('p.projectset IN (:projectset)', ':projectset', $para->{'data-set'});
	mydb::value('$LIMIT$', $limit);

	$stmt = 'SELECT
		tr.`trid`, tr.`calid`, tr.`tpid`
		, p.`projectset`, t.`title`, tr.`text4` `body`
		, GROUP_CONCAT(DISTINCT f.`file`) photos
		, tr.`created`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project% p USING(`tpid`)
			LEFT JOIN %topic_files% f ON f.`tpid` = tr.`tpid`
				AND f.`gallery` = tr.`gallery` AND f.`type` = "photo"
		%WHERE%
		GROUP BY tr.`trid`
		ORDER BY tr.`trid` DESC
		LIMIT $LIMIT$';

	$dbs = mydb::select($stmt);

	$ret['html'] = '<ul class="topic-list">'._NL;
	foreach ($dbs->items as $rs) {
	  list($photo) = explode(',',$rs->photos);
	  $url = _DOMAIN.url('paper/'.$rs->tpid,NULL,'tr-'.$rs->trid);
	  if ($photo) $photo = _DOMAIN.cfg('paper.upload.photo.url').$photo;

	  $data = (Array) $rs;
	  $data['url'] = $url;
	  $data['photos'] = $photo;
	  $data['created'] = sg_date($rs->created,'d ดด ปป H:i');
	  $ret['data'][] = $data;

	  $ret['html'] .= '<li>'
	  	. '<h3><a href="'.$url.'">'.$rs->title.'</a></h3>'
	  	. ($photo?'<a href="'.$url.'"><img src="'.$photo.'" width="'.$photoWidth.'" height="'.$photoHeight.'" alt="'.htmlspecialchars($rs->title).'" /></a>':'')
	  	. '<span class="summary">'.trim(strip_tags(sg_text2html($rs->body))).'</span>'
	  	. '<span class="timestamp">@'.sg_date($rs->created,'d ดด ปป H:i').'</span>'
	  	. '</li>'._NL;
	}

	$ret['html'] .= '</ul>'._NL;

	die(json_encode($ret));
	return $ret;
}
/*
Google API
{"responseData": null, "responseDetails": "The Google Web Search API is no longer available. Please migrate to the Google Custom Search API (https://developers.google.com/custom-search/)", "responseStatus": 403}
*/
?>