<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_rehab_home($self, $orgId = NULL) {
	$ret .= R::View('imed.toolbox',$self,'iMed@ศูนย์ฟื้นฟูสมรรถภาพ', 'pocenter');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	$ret .= '<div class="imed-sidebar">'.R::View('imed.menu.rehab',NULL)->build().'</div>';


	$ret .= '<div id="imed-app" class="imed-app">'._NL;


	$headerUi = new Ui();
	//$headerUi->add('<a href=""><i class="icon -material">view_list</i><span class="-hidden">คงเหลือ</span></a>');

	$ret .= '<header class="header -imed-pocenter"><nav class="nav -back"><a class="" href="'.url('imed').'"><i class="icon -material">arrow_back</i></a></nav><h3>ศูนย์ฟื้นฟูสมรรถภาพ</h3><nav class="nav">'.$headerUi->build().'</header>';


	mydb::where('s.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT o.`orgid`, o.`name`, o.`house`, o.`areacode`
					FROM %imed_socialmember% s
						LEFT JOIN %db_org% o USING(`orgid`)
					%WHERE%
					ORDER BY CONVERT(o.`name` USING tis620) ASC';
	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs);

	$cardUi = new Ui(NULL, 'ui-card -sg-flex -co-2');
	foreach ($dbs->items as $rs) {
		$cardStr = '<a href="'.url('imed/rehab/'.$rs->orgid).'"><span>';
		$cardStr .= '<h3>'.$rs->name.'</h3>';
		$cardStr .= '<img src="//img.softganz.com/img/disabledonfloor.jpg" width="100%" />';
		$cardStr .= '</span></a>';
		$cardStr .= '<span>'.$rs->house.'</span>';
		$cardStr .= '<nav class="nav -card"><a class="btn -link -fill" href="'.url('imed/rehab/'.$rs->orgid).'"><i class="icon -material">pageview</i><span>VIEW INFO</span></a></nav>';
		$cardUi->add($cardStr);
	}
	if ($dbs->count() % 2) $cardUi->add('&nbsp;', '{class: "-empty"}');
	$ret .= $cardUi->build();

	$ret .= '</div><!-- imed-app -->';


	return $ret;
}
?>