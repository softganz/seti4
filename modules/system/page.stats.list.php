<?php
/**
* Stats : List Counter Log
* Created 2018-12-15
* Modify  2020-10-22
*
* @param Object $self
* @return String
*
* @usage stats/list
*/

$debug = true;

function stats_list($self) {
	$getId = \SG\getFirst(post('id'),0);
	$getIp = post('ip');
	$getUser = post('user');
	$getDate = post('date');
	$items = intval(\SG\getFirst(post('items'),100));
	$page = intval(\SG\getFirst(post('page'),1));
	$getEid = post('eid');
	$getIncludeBot = post('bot');

	$is_administer_watchdogs = user_access('administer contents,administer watchdogs');

	$isEmptyPara = !$getIp && !$getUser && !$getDate;
	//$ret .= '$isEmptyPara = '.$isEmptyPara;

	$self->theme->title = 'Access log listing';
	user_menu('list','list',url('stats/list'));
	$self->theme->navigator = user_menu();

	$dbs = mydb::select('show table status like "sgz_counter_log"');
	$total_items = $dbs->items[0]->Rows;
	//$ret .= print_o($dbs,'$dbs');

	if ($getIp) mydb::where('l.`ip` = :ip',':ip',ip2long($getIp));
	if ($getUser) mydb::where('l.`user` = :user',':user',$getUser);
	if ($getDate) mydb::where('DATE_FORMAT(l.`log_date`,"%Y-%m-%d") = :date',':date',$getDate);
	if (!$getIncludeBot) mydb::where('l.`referer` NOT LIKE "%bot%"');


	$pagePara = array();
	$pagePara['user'] = $getUser;
	$pagePara['ip'] = $getIp;
	$pagePara['date'] = $getDate;
	if ($getId) $pagePara['id'] = $getId;
	if ($getIncludeBot) $pagePara['bot'] = 'yes';

	if ($isEmptyPara) {
		$rs = mydb::select('SELECT MIN(`id`) `minid`, MAX(`id`) `maxid` FROM %counter_log% LIMIT 1; -- {reset: false}');
		$minId = $rs->minid;
		$maxId = $rs->maxid;

		//if ($isEmptyPara) {
			$startId = $maxId - ($page * $items) + 1;
			mydb::where('l.`id` >= :id', ':id', $startId);
			mydb::value('$LIMIT$', 'LIMIT '.$items);
		//} else {
		//	$minId = \SG\getFirst($getEid,$rs->minid);
		//	$startId = $minId + (($page - 1) * $items);
		//	mydb::value('$LIMIT$', 'LIMIT '.$items);
		//}

		//$ret .= 'Min id = '.$minId.' Max id = '.$maxId.' Srart id = '.$startId.'<br />';

		// FAST Query but bug on condition, cannot get all found rows
		$stmt = 'SELECT log.*, u.`name` `user_name`
			FROM
			(
				SELECT *
				FROM %counter_log% l
				%WHERE%
				ORDER BY l.`id` ASC
				$LIMIT$
			) AS log
			LEFT JOIN %users% AS u ON log.`user` = u.`uid`
			ORDER BY `id` DESC;
			-- {key: "id"}';

		$dbs = mydb::select($stmt);

		//if ($isEmptyPara) {
		//	$total_items = $maxId - $minId;
		//} else {
			//$total_items = $dbs->count() < $items ? $page*$items :  $maxId - $minId;
		//}
		$pagePara['eid'] = end($dbs->items)->id;
	} else {
		$start = ($page - 1) * $items;
		mydb::value('$LIMIT$', 'LIMIT '.$start.','.$items);

		$stmt = 'SELECT
			log.*
			, u.`name` `user_name`
			FROM
			(SELECT
				l.*
				FROM %counter_log% AS l
				%WHERE%
				ORDER BY l.`id` DESC
				$LIMIT$
			) log
				LEFT JOIN %users% AS u ON log.`user` = u.`uid`
			';

		$dbs = mydb::select($stmt);
		//$ret .= mydb()->_query.'<br />';
		//$total_items = $dbs->_found_rows;
	}
	//$ret .= mydb()->_query.'<br />';
	//$ret .= 'TOTAL = '.$total_items.'<br />';
	//$ret .= print_o($dbs,'$dbs');


	$pagenv = new PageNavigator($items,$page,$total_items,q(),NULL,$pagePara);

	if (!$para->option->no_page && $pagenv->show) {
		$ret .= '<div class="-sg-flex">'
			. '<div>'
			. '<form method="post" action="'.url(q(), $pagePara).'">'
			. '<label><input type="checkbox" name="bot" value="yes" '.($getIncludeBot ? 'checked="checked"' : '').' />Include Bot</label>'
			. '<button class="btn -link"><i class="icon -material">search</i></button>'
			. '</form>'
			. '</div>'
			. '<div>'
			. $pagenv->show
			. '</div>'
			. '</div>';
	}

	$no = 0;

	$tables = new Table();
	$tables->thead = array('no' => 'no','logid','log date','user','ip');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			'<font color=brown>'.$rs->id.'</font>',
			'<em><font color=brown>'.$rs->log_date.'</font></em>'.($rs->new_user? '<img src="'._img.'new.1.gif" title="new user" alt="new" />':''),
			($is_administer_watchdogs ? '<font color=brown><a class="sg-action" href="'.url('stats/list',array('user'=>$rs->user)).'" data-rel="box" title="Statistics of user '.$rs->user_name.'">'.$rs->user_name.'</a></font>':'<font color=brown>'.$rs->user_name.'</font>'),
			($is_administer_watchdogs ? '<a href="'.url('stats/list',array('ip'=>long2ip($rs->ip))).'" title="Statistics of ip '.long2ip($rs->ip).'">'.long2ip($rs->ip).'</a>' : sg_sub_ip(long2ip($rs->ip))),
		);

		$tables->rows[] = array(
			'<td></td>',
			'',
			'<td colspan="3">'
			. ($is_administer_watchdogs ? '<font color=#A7A7A7>url:</font><a class="sg-action" href="'.$rs->url.'" data-rel="box" data-width="100%" data-height="100%">'.urldecode($rs->url).'</a><br />':'')
			. (user_access(true) ? '<font color=#A7A7A7>referer:</font><a href="'.$rs->referer.'" target=_blank><font color=#A7A7A7>'.urldecode($rs->referer).'</font></a><br />':'')
			. '<font color=#A7A7A7>browser:'.$rs->browser.'</font></td>',
		);
	}

	$ret .= $tables->build();

	if (!$para->option->no_page && $pagenv->show) $ret.=$pagenv->show;

	return $ret;
}
?>