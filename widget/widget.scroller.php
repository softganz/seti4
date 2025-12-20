<?php
/**
 * Widget widget_scroller
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
 * Widget for show scroller
 *
 * @param String $para
 * 	header=Header
 * 	limit=Limit (default all)
 * 	order=Order Field
 * 	sort=ASC|DESC
 * @return String
 */
function widget_scroller() {
	$para=$para=para(func_get_args(),'data-header=Scroller','data-sticky=254','data-items=10','data-order=created','data-sort=ASC','option-header=0','option-dir=left');
	$stmt='SELECT "/paper/" AS link,`tpid` AS id , 0 AS `sorder`, `created`, `title` FROM %topic% WHERE `sticky` IN (:sticky)
			UNION
		SELECT "/calendar/view/" AS link , id , 1 AS `sorder`, `from_date` AS `created`, CONCAT("กิจกรรมวันนี้ : ",`title`) FROM %calendar% WHERE "'.date('Y-m-d').'" BETWEEN `from_date` AND `to_date`
			UNION
		SELECT "/calendar/view/" AS link,id,2 AS `sorder`, `from_date` AS `created`, CONCAT("กิจกรรม : ",`from_date`, " : ",`title`) FROM %calendar% WHERE "'.date('Y-m-d').'" < `to_date`
		ORDER BY `sorder` ASC, `'.addslashes($para->{'data-order'}).'` '.addslashes($para->{'data-sort'}).'
		LIMIT '.addslashes($para->{'data-items'});
	$dbs=mydb::select($stmt,':sticky','SET:'.$para->{'data-sticky'});
	//$ret.=print_o($dbs,'$dbs');
	if ($dbs->items) {
		$today='<div id="today">';
		$today.='กิจกรรม ';
		$no=0;
		foreach ($dbs->items as $rs) {
			++$no;
			$today.='<a href="'.$rs->link.$rs->id.'" title="'.str_replace('"','',strip_tags($rs->title)).'">'.$no.'</a> , ';
			$signs[]='<a href="'.$rs->link.$rs->id.'" title="'.str_replace('"','',strip_tags($rs->title)).'">'.$rs->title.'</a>';
		}
		$today=trim($today,' , ');
		$today.='</div>';
	} else {
		$signs[]='ยิ น ดี ต้ อ น รั บ สู่ เ ว็ บ ไ ซ ท์ '.cfg('web.title');
	}
	if (count($signs)==1) $signs[]=$signs[0];
	$ret.='<div id="scroller" class="sg-slider"><ul><li>'.implode('</li><li>',$signs).'</li></ul></div>';
	$ret.=$today;
	return array($ret,$para);
}
?>