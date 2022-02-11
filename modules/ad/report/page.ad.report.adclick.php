<?php
function ad_report_adclick($self,$adid = NULL) {
	$self->theme->title='Click Report :: Advertisment Report';

	/*
	$ret.='<div class="as-tile-container">'._NL;
	$ret.='<as-adclick-card class="">'._NL;
	$ret.='<div class="as-content-container">'._NL;
	$ret.='<div class="as-card-main-header">Estimated ad clicks</div>'._NL;
	$ret.='<section class="as-card-content">'._NL;
	$ret.='<as-adclick-card-panel" class="">'._NL;
	$ret.='<as-tooltip" class="as-adclick-card-panel-name-tooltip">Today click so far</as-tooltip>'._NL;
	$ret.='<div class="as-adclick-card-panel-value">100</div>'._NL;
	$ret.='<div class="as-adclick-card-panel-diff-value">v -10 (-10%)</div>'._NL;
	$ret.='<div class="as-adclick-card-panel-qualifier">vs same day last week</div>'._NL;
	$ret.='</as-adclick-card-panel">'._NL;
	$ret.='</div>'._NL;
	$ret.='</div>'._NL;
	$ret.='</as-adclick-card>'._NL;
	$ret.='</div>'._NL;
	*/

	$stmt='SELECT * FROM %ad% WHERE `clicks`>0 ORDER BY `location`,`title`';
	$dbs=mydb::select($stmt);

	$ret.='<div style="width:50%;float:left;">';
	$tables = new Table();
	$tables->thead=array('Ad Loc','Ad Title','center -status'=>'Active','amt -views'=>'Views','amt -clicks'=>'Clicks','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->location,
											'<a class="sg-action" href="'.url('ad/'.$rs->aid).'" data-rel="box">'.$rs->title.'</a>',
											$rs->active=='yes'?'Yes':'',
											number_format($rs->views),
											number_format($rs->clicks),
											'<a href="'.url('ad/report/adclick/'.$rs->aid).'"><i class="icon -view"></i></a>'
											);
	}
	$ret.=$tables->build();
	$ret.='</div>';

	$ret.='<div style="width:50%;float:right;">';
	if ($adid) {
		$ad=mydb::select('SELECT `location`,`title` FROM %ad% WHERE `aid`=:adid LIMIT 1',':adid',$adid);
		$self->theme->title=$ad->location.' :: '.$ad->title.' :: '.$self->theme->title;
	}

	$stmt='SELECT w.`keyid` `aid`, a.`location`, a.`title`, w.`module`, w.`keyword`, DATE_FORMAT(w.`date`,"%Y-%m-01") `month`, COUNT(*) `totalClick`
				FROM %watchdog% w
					LEFT JOIN %ad% a ON a.`aid`=w.`keyid`
				WHERE w.`module`="ad" AND w.`keyword`="click" '.($adid?'AND w.`keyid`=:adid':'').'
				GROUP BY w.`keyid`,`month`
				ORDER BY `location`,`title`,`month` DESC;
				-- {sum:"totalClick"}';
	$dbs=mydb::select($stmt,':adid',$adid);

	$tables = new Table();
	$tables->thead=array('Ad Loc.','Ad Title','เดือน-ปี','amt -clicks'=>'Clicks');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											$rs->location,
											'<a href="'.url('ad/report/adclick/'.$rs->aid).'">'.$rs->title.'</a>',
											sg_date($rs->month,'ดด ปปปป'),
											number_format($rs->totalClick)
											);
	}
	$tables->tfoot[]=array('','','',number_format($dbs->sum->totalClick));
	$ret.=$tables->build();
	$ret.='</div>';


	return $ret;
}
?>