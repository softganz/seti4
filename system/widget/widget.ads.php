<?php
/**
 * Widget widget_ads
 *
 * @package core
 * @version 0.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-11-04
 * @modify 2011-11-04
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 *
 * Widget get ads
 * 
 * @param String $para
 * 	data-loc=Ad location
 * 	data-items=Limit (default 1)
 * 	data-order=Order Field
 * 	data-sort=ASC|DESC
 * @return String
 * @example <div class="widget Ads" id="ad-baner" data-loc="banner" data-items="1" data-order="aid" data-sort="ASC"></div>
 */
function widget_ads() {
	$para=$para=para(func_get_args(),'data-header=Ad','data-items=1','data-order=weight ASC, aid','data-sort=DESC','option-header=0');
	$location=$para->{'data-loc'};
	$items=$para->{'data-items'} ? intval($para->{'data-items'}) : 1;

	$today = date('Y-m-d H:i:s');

	$stmt = 'SELECT * FROM `sgz_ad`
					WHERE `location` = :location AND `active` = "yes"
						AND (`start` <= :start AND `stop` >= :stop)
		ORDER BY '.$para->{'data-order'}.' '.$para->{'data-sort'};

	$result = mydb::select($stmt,':location',$location,':start',$today,':stop',$today)->items;

	if (!$result) {
		$stmt = 'SELECT * FROM `sgz_ad` WHERE `location` = :location AND `default`="yes" ';
		$result = mydb::select($stmt,':location',$location)->items;
	}

	srand((float) microtime() * 10000000);
	$rand_keys = count($result)>$items ? ($items==1?array(array_rand($result, $items)):array_rand($result, $items)) : array_keys($result);
	if ($rand_keys) {
		$ret .= '<ul class="ads-content -clearfix">'._NL;
		foreach ($rand_keys as $key) {
			$ret .= '<li>';
			$banner=(array)$result[$key];
			$ad_id[]=$banner['aid'];
			if ($banner['url'] || !empty($banner['body'])) $ret .= '<a href="'.url('ad/'.$banner['aid'].'/click').'" title="'.htmlspecialchars($banner['title']).'">';
			$img=cfg('upload.url').cfg('ad.img_folder').'/'.$banner['file'];
			$ext=strtolower(substr($img,strrpos($img,'.')+1));
			if ($ext=='swf') {
				$ret .='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$banner['width'].'" height="'.$banner['height'].'">
<param name="movie" value="'.$img.'">
<param name="quality" value="high">
<embed src="'.$img.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$banner['width'].'" height="'.$banner['height'].'" /></object>';
			} else if (in_array($ext,array('jpg','jpeg','gif','png'))) {
				$ret .= '<img src="'.$img.'" width="'.$banner['width'].'" height="'.$banner['height'].'" alt="'.htmlspecialchars($banner['title']).'" />';
			} else $ret.=$banner['body'];
		
			if ($banner['url'] || !empty($banner['body'])) $ret .= '</a>';
			$ret .= '</li>'._NL;
		}
		$ret .= '</ul>'._NL;
		mydb::query('UPDATE sgz_ad SET views=views+1 WHERE aid in ('.implode(',',$ad_id).')');
	}
	return array($ret,$para);
}
?>