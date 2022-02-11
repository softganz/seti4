<?php
function view_watchdog_listing($dbs) {
	$isAdmin = user_access('administer watchdogs');
	$tables = new Table();
	$tables->addClass('watchdog-list');
	$tables->thead=array('wid'=>'No','date'=>'Date','Module','Keyword','Key ID','Field','User', 'ip -hover-parent'=>'IP');
	foreach ($dbs->items as $rs) {
		$no++;
		$ui = new Ui();
		if ($isAdmin) $ui->add('<a class="sg-action" href="'.url('watchdog/'.$rs->wid.'/delete').'" data-rel="none" data-done="remove:.-detail-'.$rs->wid.'" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$tables->rows[]=array($rs->wid,
				$rs->date,
				'<strong>'.$rs->module.'</strong>',
				'<strong>'.$rs->keyword.'</strong>',
				$rs->keyid,
				$rs->fldname,
				'<a href="'.url('watchdog/list/user/'.$rs->uid).'" title="Statistics of user '.$rs->username.'">'.$rs->username.'</a>',
				'<a href="'.url('watchdog/list/ip/'.long2ip($rs->ip)).'" title="Statistics of ip '.long2ip($rs->ip).'">'.long2ip($rs->ip).'</a>'
				.$menu,
				'config'=>array('class'=>'-detail -detail-'.$rs->wid)
			);
		$more='<dl class="more">';
		if ($rs->message) $more .= '<dd>Message : '.$rs->message.'</dd>';
		$more.='<dd>Url : <a href="'.$rs->url.'" target="_blank">'.$rs->url.'</a></dd>';
		if ($rs->referer) $more .= '<dd>Referer : <a href="'.$rs->referer.'" target="_blank">'.$rs->referer.'</a></dd>';
		$more .= '<dd>Browser : '.$rs->browser.'</dd>';

		$tables->rows[]=array(array('colspan'=>8,$more),'config'=>array('class'=>'-detail -detail-'.$rs->wid));
	}
	$ret.=$tables->build();
	return $ret;
}
?>