<?php
/**
 * Widget   :: Inline Slide Scroller Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2011-11-04
 * Modified :: 2026-08-24
 * Version  :: 3
 *
 * @param String $para
 * 	header=Header
 * 	limit=Limit (default all)
 * 	order=Order Field
 * 	sort=ASC|DESC
 * @return String
 */

use Softganz\DB;
use Softganz\SetDataModel;

function widget_scroller() {
	$para = para(func_get_args(),'data-header=Scroller','data-sticky=254','data-items=10','data-order=created','data-sort=ASC','option-header=0','option-dir=left');
	$ret = '';

	$dbs = DB::select([
		'SELECT "/paper/" AS link,`tpid` AS id , 0 AS `sorder`, `created`, `title` FROM %topic% WHERE `sticky` IN (:sticky)
		UNION
		SELECT "/calendar/view/" AS link , id , 1 AS `sorder`, `from_date` AS `created`, CONCAT("กิจกรรมวันนี้ : ",`title`) FROM %calendar% WHERE :currentDate BETWEEN `from_date` AND `to_date`
		UNION
		SELECT "/calendar/view/" AS link,id,2 AS `sorder`, `from_date` AS `created`, CONCAT("กิจกรรม : ",`from_date`, " : ",`title`) FROM %calendar% WHERE :currentDate < `to_date`
		ORDER BY `sorder` ASC, :`ORDER` ::SORT::
		LIMIT ::LIMIT::',
		'var' => [
			':currentDate' => date('Y-m-d'),
			':sticky' => new SetDataModel($para->{'data-sticky'}),
			':`ORDER`' => $para->{'data-order'},
			'::SORT::' => $para->{'data-order'},
			'::LIMIT::' => $para->{'data-order'}
		]
	]);

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