<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad_list($self) {
	$isAdmin = user_access('administer ads');
	if (!$isAdmin) return message('error', 'access denied');

	$para=para(func_get_args());
	$orderBy = SG\getFirst(post('order'),'aid');
	$sortBy = SG\getFirst(post('sort'),'DESC');
	$itemsLimit = SG\getFirst(post('items'),100);

	$ret .= '<div class="ad-loc">';
	$tables = new Table();
	$tables->thead=array('adid -center'=>'AD ID','description', 'active -amt' => 'Active', 'inactive -amt' => 'Inactive', 'center -hover-parent' => 'w x h');
	$stmt = 'SELECT
						l.*
						, (SELECT COUNT(*) FROM %ad% WHERE `location` = l.`lid` AND `active` = "yes") `adActive`
						, (SELECT COUNT(*) FROM %ad% WHERE `location` = l.`lid` AND `active` = "no") `adInactive`
						FROM %ad_location% l
						ORDER BY  lid ASC';
	foreach (mydb::select($stmt)->items as $rs) {
		$menu = '<nav class="nav iconset -hover"><a href="'.url('ad/post', array('id' => $rs->lid)).'" title="เพิ่มโฆษณาใหม่ในตำแหน่งนี้"><i class="icon -add"></i></a></nav>';
		$tables->rows[] = array(
												$rs->lid,
												'<a href="'.url('ad/list','loc='.$rs->lid).'">'.$rs->description.'</a>',
												$rs->adActive,
												'<a href="'.url('ad/list',array('loc'=>$rs->lid, 'active'=>'no')).'">'.$rs->adInactive.'</a>',
												$rs->width.'x'.$rs->height
												. $menu,
											);
	}
	$ret .= $tables->build();
	if ($isAdmin) {
		$ret .= '<nav class="nav"><a href="'.url('ad/addloc').'"><i class="icon -add"></i><span>Add New Ad Location</span></a></nav>';
	}
	$ret .= '</div>';


	if (post('loc')) {
		mydb::where('ad.location = :loc', ':loc', post('loc'));
		$para->order='weight';
		$para->sort='ASC';
	}
	if (post('active')) {
		mydb::where('ad.`active` = :active', ':active', post('active'));
	} else {
		mydb::where('ad.`active` = "yes"');
	}
	if (post('default')) mydb::where('ad.`default` = "yes"');
	if (post('user')) mydb::where('o.username = :user', ':user', post('user'));
	if (post('date') == 'current') mydb::where('`start` BETWEEN :startdate AND `stop` >= :stopdate', ':startdate', date('Y-m-d H:i:s'), ':stopdate', date('Y-m-d H:i:s'));

 	mydb::value('$ORDER', 'ORDER BY '.$orderBy);
 	mydb::value('$SORT', $sortBy);
 	mydb::value('$LIMIT', 'LIMIT 0,'.$itemsLimit);

	$stmt = 'SELECT ad.* , o.`name` as owner
						FROM %ad% AS ad
							LEFT JOIN %users% AS o ON ad.`oid` = o.`uid`
						%WHERE%
						$ORDER $SORT
						$LIMIT';
	$result = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$self->theme->title = 'Advertisment';
	user_menu('home',tr('home'),url());
	user_menu('ad',tr('ad'),url('ad'));
	if (user_access('create ad content')) {
		user_menu('report',tr('Report'),url('ad/report'));
		user_menu('new',tr('Create new advertisment'),url('ad/post'));
	}
	$self->theme->navigator = user_menu();

	$no=1;
	$cardUi = new Ui(NULL, 'ui-card');
	foreach ($result->items as $rs) {
		$cardStr = '<header><h3>'.$rs->title.'</h3></header>'._NL;
		$cardStr .= '<div class="photo"><a href="'.url('ad/'.$rs->aid).'">'.ad_model::__show_img_str($rs).'</a></div>'._NL;
		$cardStr .= '<div class="summary">';
		$cardStr .= '<strong>'.$rs->location.'</strong> | ';
		if ($rs->active=='yes') $cardStr .='<font color="red"><strong>Active</strong></font> | ';
		if ($rs->default=='yes') $cardStr .='<font color="green"><strong>Default</strong></font> | ';
		$cardStr .= substr($rs->start,0,10).' to '.substr($rs->stop,0,10);
		$cardStr .= '</div>'._NL;
		$cardStr .= '<div class="footer">Uploaded by '.$rs->owner.' on '.sg_date($rs->created,'M j, Y').' | '.$rs->views.' views |
		<a href="'.url('ad/'.$rs->aid).'">View advertisment detail</a>
		</div>'._NL._NL;
		$cardUi->add($cardStr);
		$no++;
	}

	$ret .= '<div class="ad-list">'.$cardUi->build().'</div>'._NL;

	$ret .= '<style type="text/css">
	.ad-loc {width: 340px; float: left;}
	.ad-list {margin-left:360px;}
	.ad-list>.ui-card>.ui-item {margin-bottom: 64px;}
	.ad-list h3 {background-color: #666; color: #fff; padding: 8px;}
	div.photo {clear: none;}
	</style>';
	return $ret;
}
?>