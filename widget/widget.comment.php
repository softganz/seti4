<?php
/**
 * Widget   :: Inline comment Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2012-09-03
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * @param Array $args
 * @param Argument list in many format
 * 
 * @param String id

 * @param String show-url									Example ibuy/$tpid												Default=paper/$tpid
 * @param String show-style								Value = div,short,reply,shortview,detail		Default = div
 * @param String show-style-value
 * @param String show-style-title
 * @param String show-dateformat					Value = d-m-Y H:i													Default = config date format
 * @param String show-new								Value = type[,value] = items | today | hour | minute | day | lastdate | least [,value]
 * @param Int show-start										Default=1
 * @param Int show-count									Default = all rows
 * @param String show-photo							Value = image,slide					Default = none
 * @param Int show-photo-width		
 * @param Int show-photo-height
 * @param String show-title									Example '@'.sg_date('Y-m').' : '.$title 
 * 
 * @param String option-debug	Value = eval
 * 
 * @return String $ret
 *	@usage widget::content(['para1=value1'[,[para2=value2][para3,value3]...)
 * @example <div class="widget Content" id="id1" data-limit="20" show-style-type="div" data-footer="By SoftGanz" data-sort="ASC"></div>
 */

function widget_comment() {
	$comments=mydb::select('SELECT tpid,title,last_reply,UNIX_TIMESTAMP(last_reply) AS replytime FROM `sgz_topic` t WHERE t.status='._PUBLISH.' ORDER BY last_reply DESC LIMIT 10');
	$ret.=view::content_list($comments,'list-style=shortview','list-style-value=" <span class=\"timestamp\">".sg_remain2day('.date('H:i:s').'-$replytime)."</span>"','url=paper/$tpid/page/last');
	return array($ret,$para);
}
?>