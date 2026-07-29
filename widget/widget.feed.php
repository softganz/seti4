<?php
/**
 * Widget   :: Inline feed Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2011-11-04
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * @param String $para
 * 	header=Header
 * 	limit=Limit (default all)
 * 	order=Order Field
 * 	sort=ASC|DESC
 * @return String
 */
function widget_feed() {
	$para=$para=para(func_get_args(),'header=Feeds','data-items=10');
	$ret='<a href="'.$para->url.'">Loding...</a>';
	return array($ret,$para);
}
?>