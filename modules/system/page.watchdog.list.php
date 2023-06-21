<?php
function watchdog_list($self) {
	$para=para(func_get_args(),1);

	$items = SG\getFirst($para->items,100);

	//$ret.=print_o($para,'$para');

	if ($para->ip) mydb::where('w.`ip`=:ip',':ip',ip2long($para->ip));
	if ($para->user) mydb::where('w.`uid`=:uid',':uid',$para->user);
	if ($para->date) mydb::where('DATE_FORMAT(`date`,"%Y-%m-%d")=:date',':date',$para->date);
	if ($para->keyword) mydb::where('`keyword`=:keyword',':keyword',$para->keyword);

	$pagenv = new PageNavigator($items,$para->page,$total_items,q());

	$stmt='SELECT SQL_CALC_FOUND_ROWS
					  w.*
					, u.`name` `user_name`
					FROM %watchdog% w 
						LEFT JOIN %users% AS u USING(`uid`)
					%WHERE%
					ORDER BY w.`wid` DESC
					LIMIT '.$pagenv->FirstItem().",".$items;

	$dbs=mydb::select($stmt);

	//$ret.=$dbs->_query;

	$total_items = $dbs->_found_rows;
	$pagenv = new PageNavigator($items,$para->page,$total_items,q());

	$no=0;
	$is_administer_watchdogs=user_access('administer contents,administer watchdogs');

	$self->theme->title='Watchdog listing';
	user_menu('list','list',url('watchdog/list'));
	$self->theme->navigator=user_menu();
	if (!$para->option->no_page && $pagenv->show) $ret.=$pagenv->show;

	$tables = new Table();
	$tables->addClass('watchdog-list');
	$tables->thead=array('No','Date','Module','Keyword','Key ID','Field','User','IP');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->wid,
											'<a href="'.url('watchdog/list/date/'.sg_date($rs->date,'Y-m-d')).'">'.$rs->date.'</a>',
											$rs->module,
											$rs->keyid,
											$rs->fldname,
											'<a href="'.url('watchdog/list/keyword/'.$rs->keyword).'">'.$rs->keyword.'</a>',
											'<a href="'.url('watchdog/list/user/'.$rs->uid).'" title="Statistics of user '.$rs->user_name.'">'.$rs->user_name.'</a>',
											'<a href="'.url('watchdog/list/ip/'.long2ip($rs->ip)).'" title="Statistics of ip '.long2ip($rs->ip).'">'.long2ip($rs->ip).'</a>',
											);
		$message='<dl class="more">';
		if ($rs->message) $message.='<dd>Message : '.$rs->message.'</dd>';
		$message.='<dd>Url : <a href="'.$rs->url.'" target="_blank">'.$rs->url.'</a></dd>';
		if ($rs->referer) $message.='<dd>Referer : <a href="'.$rs->referer.'" target="_blank">'.$rs->referer.'</a></dd>';
		$message.='<dd>Browser : '.$rs->browser.'</dd>';
		$tables->rows[]=array(
											'',
											'<td colspan="7">'.$message.'</td>',
											);
	}
	$ret.=$tables->build();
	if (!$para->option->no_page && $pagenv->show) $ret.=$pagenv->show;
	return $ret;
}
?>