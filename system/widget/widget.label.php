<?php
/**
 * Widget widget_label
 *
 * @package core
 * @version 0.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-11-04
 * @modify 2012-10-16
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 *
 * Get tag label
 * 
 * @param String $para
 * 	data-header=Header
 * 	data-limit=Limit (default all)
 * 	data-order=Order Field
 * 	data-sort=ASC|DESC
 * @return String
 */
function widget_label() {
	$para=para(func_get_args(),'data-header=Labels','data-limit=-1','data-order=name','data-sort=ASC');
	$stmt='SELECT t.tid,t.name,
											(SELECT COUNT(tid) max FROM %tag_topic% GROUP BY tid ORDER BY max DESC LIMIT 1) AS max,
											(SELECT COUNT(*) FROM %tag_topic% tp WHERE tp.tid=t.tid) AS topics
										FROM %tag% t
										ORDER BY `'.addslashes($para->{'data-order'}).'` '.addslashes($para->{'data-sort'}).'
										'.($para->{'data-limit'}!=-1?' LIMIT '.addslashes($para->{'data-limit'}):'');
	$tags=mydb::select($stmt);
	foreach ($tags->items as $rs) {
		$level=round(($rs->topics/$rs->max)*4)+1;
		$ret.='<span class="label-size label-size-'.$level.'"><a class=" tagadelic level'.$level.'" href="'.url('tags/'.$rs->tid).'" title="'.$rs->topics.' หัวข้อ">'.$rs->name.'</a></span>'._NL;
	}
	return array($ret,$para);
}
?>