<?php
/**
* Paper Model :: Get Topics List
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_paper_get_topics($conditions, $options = '{}') {
	$defaults = '{field: null, debug: false, page: 1, items: 10, sort: "DESC"}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;


	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	if ($options->page < 1) $options->page = 1;
	$items = \SG\getFirst($options->items,10);
	$getFields = explode(',',$options->field);
	$sort = in_array($options->sort,array('ASC','DESC')) ? $options->sort : 'DESC';

	//debugMsg($conditions,'$conditions');
	if ($debug) debugMsg($options,'$options');

	$isFetchPhoto = in_array('photo', $getFields);

	$having = array();

	if ($conditions->tag && strpos($conditions->tag, '+')) {
		$conditions->tag = str_replace('+', ',', $conditions->tag);
		$allTagList = $conditions->tag;
	}

	$fields = '';
	$join = [];

	if ($conditions->category) {
		$tags = explode(',',mydb::select('SELECT `tid` FROM %tag_synonym% WHERE name=:category',':category',$conditions->category)->lists->text);
	}

	if ($conditions->changwat) {
		mydb::where('LEFT(t.`areacode`,2) IN (:areacode)', ':areacode', 'SET:'.$conditions->changwat);
	}

	$fields = '	t.* '._NL;
	$fields .= '	, u.username as username,u.name as owner '._NL;

	if (in_array('detail', $getFields)) $fields .= '	, r.format , r.body , r.property , r.email , r.homepage '._NL;

	if (in_array('comment', $getFields)) $fields.='	,(SELECT COUNT(*) FROM %topic_comments% WHERE `tpid`=t.`tpid`) comments'._NL;
	if ($allTagList) $fields .= '	, GROUP_CONCAT(tp.`tid` ORDER BY tp.`tid`) `allTagList` '._NL;


	//if (module_install('voteit')) $fields.=do_class_method('voteit','get_topic_by_condition','fields',$conditions);

	$join[] = 'LEFT JOIN %users% as u USING(`uid`) ';
	if (in_array('detail', $getFields)) $join[] = 'LEFT JOIN %topic_revisions% as r USING(`revid`) '._NL;
	if ($conditions->tag || $conditions->category) {
		$join[] = 'LEFT JOIN %tag_topic% tp ON tp.`tpid` = t.`tpid`';
		$join[] = 'LEFT JOIN %tag_hierarchy% th ON th.`tid` = tp.`tid`';
	}
	if ($conditions->category) $join[] = ' LEFT JOIN %tag% tg ON tg.`tid` = tp.`tid` ';

	//if (module_install('voteit')) $table_cmd.=do_class_method('voteit','get_topic_by_condition','join',$conditions);

	if ($conditions->type) {
		mydb::where('t.type IN (:type)', ':type', 'SET-STRING:'.$conditions->type);
	}
	if ($conditions->category) {
		//$category = mydb::select('SELECT tid FROM %tag_synonym% WHERE name="'.$conditions->category.'"')->list->text;
		//mydb::where('tg.`tid` IN (:category)', ':category',implode(',',$tags));
	}
	if ($conditions->tag) {
		mydb::where('(tp.`tid` IN ( :tags ) OR th.`parent` IN ( :tags ))', ':tags', 'SET-STRING:'.$conditions->tag);
	}
	if ($conditions->sticky) mydb::where('t.`sticky` IN (:sticky)', ':sticky', 'SET-STRING:'.$conditions->sticky);
	if ($conditions->user) mydb::where('t.`uid` IN (:user)',':user', 'SET-STRING:'.$conditions->user);
	if ($conditions->ip) mydb::where('t.`ip` = :ip', ':ip',ip2long($conditions->ip));
	if ($conditions->year) mydb::where('YEAR(t.`created`) = :year',':year',$conditions->year);
	if ($conditions->q) mydb::where('t.`title` LIKE :q', ':q', '%'.$conditions->q.'%');
	if (i()->ok) {
		if (!user_access('administer contents,administer papers'))
			mydb::where('(t.`status` IN ('._PUBLISH.','._LOCK.') || (t.status IN ('._DRAFT.','._WAITING.') AND t.`uid` = :uid))', ':uid', i()->uid);
	} else {
		mydb::where('t.`status` in ('._PUBLISH.','._LOCK.')');
	}
	if ($conditions->condition) mydb::where($conditions->condition);

	if ($conditions->havephoto) $having[] = '`photofile` IS NOT NULL';
	if ($allTagList) $having[] = '`allTagList` =  "'.$allTagList.'" ';

	mydb::value('$FIELDS$', $fields, false);
	mydb::value('$JOIN$', implode(_NL.'			', $join), false);
	mydb::value('$GROUP BY$', 'GROUP BY t.`tpid`');
	mydb::value('$HAVING$', $having ? 'HAVING '.implode(' AND ', $having).' ' : '', false);
	mydb::value('$ORDER BY$', 'ORDER BY '.\SG\getFirst($options->order,'t.`tpid`').' '.\SG\getFirst($sort,'DESC'), false);


	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		$FIELDS$
		FROM %topic% t
			$JOIN$
		%WHERE%
		$GROUP BY$
		$HAVING$
		$ORDER BY$
		';

	if ($options->limit) {
		$stmt .= '  LIMIT '.$options->limit;
	} else {
		$firstItem = ($options->page-1)*$items;
		$stmt .= '  LIMIT '.$firstItem.','.$items;
	}
	$stmt .= '; -- {debug: false, key: "tpid"}';

	$topics = mydb::select($stmt);
	//debugMsg(mydb()->_query);
	//debugMsg($topics,'$topics');

	$pagePara = is_array($options->pagePara) ? $options->pagePara : array();
	if ($conditions->year) $pagePara['year'] = $conditions->year;
	if ($conditions->user) $pagePara['user'] = $conditions->user;
	if ($conditions->changwat) $pagePara['prov'] = $conditions->changwat;
	if ($condition->q) $pagePara['q'] = $conditions->q;
	$pagePara['page'] = $options->page;
	$pagenv = new PageNavigator($items,$options->page,$topics->_found_rows,q(),true,$pagePara);
	//debugMsg($pagePara,'$pagePara');
	//$pagenv = new PageNavigator($items,$page,$totals,q(),false,$pagePara);

	if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

	$topics->page=$pagenv;
	$topics->_query_count=$count_query;

	$result = $topics;

	foreach ($topics->items as $key=>$topic) {
		$topic_list[] = $topic->tpid;
		$topic->summary = sg_summary_text($topic->body);
			$topic->profile_picture = BasicModel::user_photo($topic->username);
		$result->items[$topic->tpid]=$topic;
	}
	if ($isFetchPhoto && $topic_list) {
		$stmt = 'SELECT * FROM
			(
			SELECT f.`fid`, f.`tpid`, f.`file`, f.`title`
			FROM %topic_files% f
			WHERE f.`tpid` IN ( :tpidList ) AND (f.`cid` = 0 OR f.`cid` IS NULL) AND f.`type` = "photo"
			ORDER BY f.`fid` ASC, f.`tpid` ASC
			) a
			GROUP BY `tpid`
			';

		$photosDbs = mydb::select($stmt, ':tpidList', 'SET:'.implode(',',$topic_list));

		foreach ($photosDbs->items as $photo) {
			$result->items[$photo->tpid]->photo = object_merge_recursive($photo, BasicModel::get_photo_property($photo->file));
		}
	}
	if ($debug) debugMsg($result,'$result');

	return $result;
}
?>