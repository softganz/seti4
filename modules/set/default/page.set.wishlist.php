<?php
/**
* Show wish list group and symbol
*
* @param Integer $_REQUEST['gid'] Group ID
* @return String
*/
function set_wishlist($self) {
	$gid=$_REQUEST['gid'];
	if (!$gid && !$_REQUEST['show']) {
		$dbs = mydb::select('SELECT * FROM %setgroup% WHERE `uid` = :uid',':uid',i()->uid);
		$ret.='<ul>'._NL;
		foreach ($dbs->items as $rs) {
			$ret.='<li><a class="sg-action" href="'.url('set/watch/'.$rs->gid).'" data-rel="#app-output">'.$rs->name.'</a><span><a class="sg-action" href="'.url('set/wishlist',array('gid'=>$rs->gid)).'" data-rel="#app-sidebar-content"><i class="icon -down"></i></a></span></li>'._NL;
		}
		$ret.='<li><a class="sg-action" href="'.url('set/wishlist',array('show'=>'all')).'" data-rel="#app-sidebar-content">All symbol</a></li>';
		$ret.='</ul>'._NL;
	} else {
		if ($gid) {
			$grs = mydb::select('SELECT * FROM %setgroup% WHERE `uid` = :uid AND `gid` = :gid LIMIT 1', ':uid', i()->uid, ':gid', $gid);
			$ret.='<h4>'.$grs->name.'</h4>';
		}

		mydb::where('`uid` = :uid ',':uid',i()->uid);
		if ($gid) mydb::where('`gid` = :gid ',':gid',$gid);

		$stmt = 'SELECT DISTINCT `uid`,`symbol` FROM %setwishlist% %WHERE% ORDER BY `symbol` ASC';
		$wishList = mydb::select($stmt);

		if ($wishList->_num_rows) {
			$ret.='<ul class="set-wishlist-symbol">'._NL;
			foreach ($wishList->items as $rs) $ret.='<li><a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output">'.$rs->symbol.'</a></li>'._NL;
			$ret.='</ul>'._NL;
		}
	}
	return $ret;
}
?>