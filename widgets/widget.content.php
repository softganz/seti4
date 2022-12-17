<?php
/**
 * Widget widget_content
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
 * Draw content list in any format
 *
 * @param Argument list in many format
 * @param String id
 * @param String data-show                   Example data-show="style=div;photo=image;"
  * @param String show-url									Example ibuy/$tpid												Default=paper/$tpid
 * @param String show-style								Value = div,short,reply,shortview,detail		Default = div
 * @param String show-style-value
 * @param String show-style-title
 * @param String show-readall								Text:url
 * @param String show-dateformat					Value = d-m-Y H:i													Default = config date format
 * @param String show-new								Value = type[,value] = items | today | hour | minute | day | lastdate | least [,value]
 * @param Int show-start										Default=1
 * @param Int show-count									Default = all rows
 * @param String show-photo							Value = image,slide					Default = none
 * @param Int show-photo-width
 * @param Int show-photo-height
 * @param String show-title									Example '@'.sg_date('Y-m').' : '.$title
 * @param String data-show-readall								Example data-show-readall=text:url
 *
 * @param String option-debug	Value = eval
 *
 * @return String $ret
 *	@usage widget::content(['para1=value1'[,[para2=value2][para3,value3]...)
 * @example <div class="widget Content" id="id1" data-limit="20" show-style-type="div" data-footer="By SoftGanz" data-sort="ASC"></div>
 */
function widget_content() {
/*
special parameter
	- show : can be string or object that contain evaluate sting
		example
			$show->view='$view." view(s)"';
			$show->created='sg_date($created,cfg("dateformat"))';
			view::content_short_list('show',$show);
*/
static $content_list_count=0;
global $today;
	$para=para(func_get_args(),'data-model=get_paper','show-url=paper/$tpid','data-limit=5','show-style=div');

	$ret = '';

	if ($para->{'data-show'}) {
		foreach (explode(';',$para->{'data-show'}) as $showStr) {
			$showKey=substr($showStr,0,strpos($showStr,'='));
			$showValue=substr($showStr,strlen($showKey)+1);
			$para->{'show-'.$showKey}=$showValue;
		}
	}
	$dateformat=($para->{'show-dateformat'}?'':'@').SG\getFirst($para->{'show-dateformat'},cfg('dateformat'));

	$patterns = (Object) [
		'short' => (Object) [],
		'slide' => (Object) [],
		'reply' => (Object) [],
		'shortview' => (Object) [],
		'detail' => (Object) [],
		'div' => (Object) [],
		'ul' => (Object) [],
	];

	$patterns->short->{'show-style'}='ul';
	$patterns->short->value='" <span class=\"timestamp\"><span class=\"date\">".sg_date($created,\''.$dateformat.'\')."</span><span class=\"sep\"> | </span><span class=\"view\">".$view." views</span>".($reply?"<span class=\"sep\"> | </span><span class=\"reply\">".$reply." replies</span>":"")."</span>"';

	$patterns->slide->{'show-style'}='ul';
	$patterns->slide->header='h3';
	$patterns->slide->value='"<div class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')."</div>
<div class=\"summary\"><a href=\"$_url\" title=\"".htmlspecialchars($title)."\">{$photo}</a>{$summary}</div>
<div class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></div>"';

	$patterns->reply->{'show-style'}='ul';
	$patterns->reply->value='" <span class=\"timestamp\">".sg_date($last_reply,\''.$dateformat.'\')." | <span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." replies</span>":"")."</span>"';

	$patterns->shortview->{'show-style'}='ul';
	$patterns->shortview->value='" <span class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')." (<span class=\"view\">".$view."</span>".($reply?"|<span class=\"reply\">".$reply."</span>":"").")</span>"';

	$patterns->detail->{'show-style'}='dl';
	$patterns->detail->header='dt';
	$patterns->detail->value='"<dd class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')."</dd>
<dd class=\"summary\">{$photo}{$summary}</dd>
<dd class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></dd>"';

	$patterns->div->{'show-style'}='div';
	$patterns->div->header='h3';
	$patterns->div->value = '"<div class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')."</div>'
		. '<div class=\"photo\">'
		. '<a '.($para->{'show-webview'} ? 'class=\"sg-action\"' : '').' href=\"$_url\" '.($para->{'show-webview'} ? 'data-webview=\"true\" data-webview-title=\"News\"' : '').' title=\"".htmlspecialchars($title)."\">{$photo}</a>'
		. '</div>'
		. '<div class=\"summary\">{$summary}</div>'
		. '<div class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></div>"';

	$patterns->ul->{'show-style'}='ul';
	$patterns->ul->header='h3';
	$patterns->ul->value='"<div class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')."</div>
<div class=\"photo\"><a href=\"$_url\" title=\"".htmlspecialchars($title)."\">{$photo}</a></div>
<div class=\"summary\">{$summary}</div>
<div class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></div>"';

	$topics = (Object) [];

	if ($para->{'data-model'}) {
		$model = $para->{'data-model'};
		$topics = BasicModel::$model($para);
	}
	// debugMsg('$model = '.$model);
	// debugMsg($para, '$para');
	// debugMsg($topics->_query);
	// debugMsg($topics,'$topics');

	if ($topics->_type == 'record') $topics = mydb::convert_record_to_recordset($topics);
	if ($topics->_empty) return;

	if (is_string($para->{'show-style'})) $pattern=$patterns->{$para->{'show-style'}};
	else if (is_object($para->{'show-style'})) $pattern=$para->{'show-style'};
	else if (is_array($para->{'show-style'})) $pattern=(object)$para->{'show-style'};
	else if (!isset($para->{'show-style'})) $pattern=$patterns->short;

	if (isset($para->{'show-style-value'})) $pattern->value=$para->{'show-style-value'};
	$pattern->title=isset($para->{'show-style-title'})?$para->{'show-style-title'}:'"<a '.($para->{'show-webview'} ? 'class=\"sg-action\"' : '').' href=\"{$topic->_url}\" '.($para->{'show-webview'} ? 'data-webview=\"true\" data-webview-title=\"News\"' : '').'>{$topic->title}</a>"';

	// new condition : items number , today , lastdate , day number , least day(number) , last items(number)
	$new=(object)NULL;
	if ($para->{'show-new'}) {
		list($new->type,$new->text)=explode(',',$para->{'show-new'});
		list($new->value,$new->type)=explode(' ',$new->type);
		if (intval($new->value)==0) {$new->type=$new->value;$new->value=NULL;}
		if (in_array(sg_file_extension($new->text),array('gif','jpg','png'))) $new->text='<img class="new" src="'.$new->text.'" alt="new topic" />';
		if (empty($new->text)) $new->text='<span class="new">Update</span>';

		switch ($new->type) {
			case 'items' : $new->value=intval($new->value); break;
			case 'today' : $new->time=date('U',mktime(0, 0, 0, date('m')+0, date('d')+0, date('Y')+0)); break;
			case 'hour' : $new->time=date('U') - intval($new->value)*60*60; break;
			case 'minute' : $new->time=date('U') - intval($new->value)*60; break;
			case 'day' : $new->time=date('U') - intval($new->value)*24*60*60; break;
			case 'lastdate' :
				$first_topic=array_slice($topics->items,0,1);
				$first_topic=$first_topic[0];
				list($last_date)=explode(' ',$first_topic->created);
				$new->time=sg_date($last_date,'U') - intval($new->value)*24*60*60;
				break;
			case 'least' : $new->time= date('U',mktime(date('H')+0, date('s')+0, date('i')+0, date('m')+0  , date('d')+0 - intval($new->value), date('Y')+0));break;
		}
	}

	/* generate list header */
	$ret .= '<!-- start of widget::content #'.$content_list_count.'-->'._NL;
	if ($pattern->{'show-style'}!='div') $ret .= '<'.$pattern->{'show-style'}.'>'._NL;

	list($last_date)=explode(' ',$topics->items[0]->created);
	$start=SG\getFirst($para->{'show-start'},1);
	$count=SG\getFirst($para->{'show-count'},$topics->_num_rows);
	$no=0;
	$debug=SG\getFirst($para->{'option-debug'}=='eval',debug('eval'));
	if ($para->{'data-field'}=='body,photo' && empty($para->{'show-photo'})) $para->{'show-photo'}='image';
	if ($para->{'show-photo'}) list($para->{'show-photo'},$showPhotoOption)=explode(',',$para->{'show-photo'});

	/* generate each item */
	foreach ($topics->items as $topic) {
		$no++;
		if ($no<$start) continue;
		if ($no>$start+$count-1) break;
		// check is new topic by new condition
		$is_new_topic=false;
		//		$topic->_url=url(preg_replace('/\$([a-zA-Z0-9_]*)/e','$topic->\\1',$para->{'show-url'}));
		$topic->_url=url(preg_replace_callback('/\$([a-zA-Z0-9_]*)/',function($m) use ($topic) {return $topic->{$m[1]};},$para->{'show-url'}));

		if ( $para->{'show-new'} ) {
			$topic_time = sg_date($topic->created,'U');
			if ($new->time && $topic_time >= $new->time) $is_new_topic=true;
			else if ($new->type=='items' && $no<=intval($new->value) ) $is_new_topic=true;
		}

		if ($para->{'show-photo'}) {
			if ($topic->photo->_num_rows) {
				switch ($para->{'show-photo'}) {
					case 'image' :
						$photo=array_shift($topic->photo->items);
						$topic->photo= '<div class="photo-th"><img class="'.$para->{'show-photo'}.'" src="'._URL.$photo->_src.'" alt="" /></div>';break;
					case 'slide' : $topic->photo=view::photo_slide(NULL,$para->{'show-photo-width'},$para->{'show-photo-height'},'get/photoslide/'.$topic->tpid.'/imagerotator');break;
					case 'list' : break;
				}
			} else if ($showPhotoOption=='alway') {
				$topic->photo= '<div class="photo-th"><img class="'.$para->{'show-photo'}.'" src="/css/img/none.gif" alt="" /></div>';
			} else {
				$topic->photo=NULL;
			}
		} else $topic->photo=null;

		switch ($pattern->{'show-style'}) {
			case 'ul' : $ret .= '<li>';break;
			case 'div' : $ret .= '<div id="'.$para->id.'-'.$no.'" class="widget-item widget-item-'.$no.'">';break;
		}

		/* generate each topic title */
		if ($pattern->header) $ret.='<'.$pattern->header.'>';
		$ret .= '<a href="'.$topic->_url.'"'.($pattern->{'show-style'}=='div'?' title="'.htmlspecialchars($topic->title).'"':'').'>';

		$ret .= $para->{'show-style'}=='short'&&$para->{'show-photo'}&&$topic->photo?$topic->photo:'';
		if ($showTitle=SG\getFirst($para->{'show-title'},$pattern->{'show-title'})) {
			// generate each topic title
			$old_error=error_reporting();
			$show= preg_replace('/\$([a-zA-Z0-9_]*)/','$topic->\\1',$showTitle);
			$eval='$show_value='.$show.';';
			eval($eval);
			error_reporting($old_error);
			$ret .= $show_value;
		} else {
			$ret.=$topic->title;
		}
		$ret .= '</a>';
		if ($pattern->header) $ret.='</'.$pattern->header.'>';

		// generate each topic detail
		$old_error=error_reporting();
		$show= preg_replace('/\$([a-zA-Z0-9_]*)/','$topic->\\1',$pattern->value);
		$eval='$show_value='.$show.';';
		if ($debug) print_o($topic,'$topic',1);
		if ($debug) echo '<p>'.htmlspecialchars($eval).'</p>';
		if ($debug) error_reporting(E_ALL); else error_reporting(0);
		eval($eval);
		error_reporting($old_error);
		$ret .= $show_value;
		if ($para->{'show-new'} && $is_new_topic ) $ret .= ' '.$new->text;
		$ret.=_NL;

		switch ($pattern->{'show-style'}) {
			case 'ul' : $ret .= '</li>'._NL;break;
			case 'div' : $ret .= '</div><!--topic-list-'.$no.'-->'._NL;
		}
	}

	if ($pattern->{'show-style'}!='div') $ret .= '</'.$pattern->{'show-style'}.'><!--end of widget-item -->'._NL;
	$showReadAll=SG\getFirst($para->{'data-show-readall'},$para->{'data-cfg-readall'},$para->{'show-readall'});
	if ($showReadAll) {
		$readAllitems=explode(',',$showReadAll);
		if (count($readAllitems)==1) {
			list($readalltext,$readallurl)=explode(':',$showReadAll);
			$ret.='<p class="readall"><a href="'.url($readallurl).'">'.$readalltext.'</a><span class="arrow-right "></span></p>';
		} else {
			$ui=new ui();
			foreach ($readAllitems as $readAllItem) {
				list($readalltext,$readallurl)=explode(':',$readAllItem);
				$ui->add('<a href="'.url($readallurl).'">'.$readalltext.'</a><span class="arrow-right "></span>');
			}
			$ret.='<div class="readall">'.trim($ui->build('ul')).'</div>';
		}
	}
	if ($para->{'data-footer'}) $ret .= $para->{'data-footer'}._NL;
	$ret.='<!--end of widget::content #'.$content_list_count.'-->';
	return array($ret,$para);
}
?>