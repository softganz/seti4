<?php
/**
 * view class for CMV
 *
 * @package core
 * @version 0.20
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2007-12-25
 * @modify 2009-11-25
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class View {

	/**
	 * Draw content list in any format
	 *
	 * @param Argument list in many format
	 * @return String $ret
	 *	@usage view::content_short_list(['para1=value1'[,[para2=value2][para3,value3]...)
	 */
	public static function content_list() {
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
		$content_list_count++;
		$args=func_get_args();
		// record set was send as parameter
		if (is_object($args[0])) {
			$topics=$args[0];
			array_shift($args);
		} else if (is_array($args[0])) {
			$topics->_num_rows=1;
			$topics->items[]=$args[0];
			array_shift($args);
		} else {
			$topics = (Object) [
				'_num_rows' => 0,
				'items' => [],
			];
		}

		$para=para($args,'url=paper/$tpid','limit=5');
		$dateformat = SG\getFirst($para->dateformat,cfg('dateformat'));

		$patterns = (Object) [
			'short' => (Object) [
				'list-style' => 'ul',
				'value' => '" <span class=\"timestamp\">".sg_date($created,\''.$dateformat.'\')." | <span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." replies</span>":"")."</span>"',
			],
			'reply' => (Object) [
				'list-style' => 'ul',
				'value' => '" <span class=\"timestamp\">".sg_date($last_reply,\''.$dateformat.'\')." | <span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." replies</span>":"")."</span>"',
			],
			'shortview' => (Object) [
				'list-style' => 'ul',
				'value' => '" <span class=\"timestamp\">@".sg_date($created,\''.$dateformat.'\')." (<span class=\"view\">".$view."</span>".($reply?"|<span class=\"reply\">".$reply."</span>":"").")</span>"',
			],
			'detail' => (Object) [
				'list-style' => 'dl',
				'value' => '"<dd class=\"timestamp\">@".sg_date($created,\''.$dateformat.'\')."</dd>
					<dd class=\"summary\">{$photo}{$summary}</dd>
					<dd class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></dd>"',
			],
			'div' => (Object) [
				'list-style' => 'div',
				'value' => '"<div class=\"timestamp\">@".sg_date($created,\''.$dateformat.'\')."</div>
					<div class=\"summary\"><a href=\"$_url\" title=\"".htmlspecialchars($title)."\">{$photo}</a>{$summary}</div>
					<div class=\"footer\"><span class=\"view\">".$view." views</span>".($reply?" | <span class=\"reply\">".$reply." comments</span>":"")." | <span class=\"readmore\"><a href=\"$_url\">read more &raquo;</a></span></div>"',
			],
		];

		if ($para->model) {
			$model=$para->model;
			$topics = BasicModel::$model($para);
		}
		if ($topics->_type=='record') $topics=mydb::convert_record_to_recordset($topics);
		if ($topics->_empty) return;

		if (is_string($para->{'list-style'})) $pattern=$patterns->{$para->{'list-style'}};
		else if (is_object($para->{'list-style'})) $pattern=$para->{'list-style'};
		else if (is_array($para->{'list-style'})) $pattern=(object)$para->{'list-style'};
		else if (!isset($para->{'list-style'})) $pattern=$patterns->short;

		if (isset($para->{'list-style-value'})) $pattern->value=$para->{'list-style-value'};
		$pattern->title=isset($para->{'list-style-title'})?$para->{'list-style-title'}:'"<a href=\"{$topic->_url}\">{$topic->title}</a>"';

		// new condition : items number , today , lastdate , day number , least day(number) , last items(number)
		$new=(object)NULL;
		if ($para->new) {
			list($new->type,$new->text)=explode(',',$para->new);
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
		$ret .= _NL.'<!-- start of view::content-list #'.$content_list_count.'-->'._NL;
		if ($para->container) $ret.='<div '.$para->container.'>'._NL;
		if ($para->header && $pattern->{'list-style'}!='div') $ret .= '<h3 class="header">'.$para->header.'</h3>'._NL;
		$ret .= '<'.$pattern->{'list-style'}.($para->id?' id="'.$para->id.'"':'').' class="topic-list widget topic'.($para->{'list-style-class'}?' '.$para->{'list-style-class'}:'').'">'._NL;
		if ($para->header && $pattern->{'list-style'}=='div') $ret .= '<h3 class="header">'.$para->header.'</h3>'._NL;

		list($last_date)=explode(' ',$topics->items[0]->created);
		$start = SG\getFirst($para->start,1);
		$count = SG\getFirst($para->count,$topics->_num_rows);
		$no=0;
		$debug = SG\getFirst($para->debug=='eval',debug('eval'));
		/* generate each item */
		foreach ($topics->items as $topic) {
			$no++;
			if ($no<$start) continue;
			if ($no>$start+$count-1) break;
			// check is new topic by new condition
			$is_new_topic=false;

			//			$topic->_url=url(preg_replace('/\$([a-zA-Z0-9_]*)/e','$topic->\\1',$para->url));


			$topic->_url=url(preg_replace_callback('/\$([a-zA-Z0-9_]*)/', function($m) use($topic) {return $topic->{$m[1]}; } ,$para->url));


			if ( $para->new ) {
				$topic_time = sg_date($topic->created,'U');
				if ($new->time && $topic_time >= $new->time) $is_new_topic=true;
				else if ($new->type=='items' && $no<=intval($new->value) ) $is_new_topic=true;
			}

			if ($para->photo && $topic->photo->_num_rows) {
				switch ($para->photo) {
					case 'image' :
						$photo=array_shift($topic->photo->items);
						$topic->photo= '<img class="'.$para->photo.'" src="'._URL.$photo->_src.'" alt="" />';break;
					case 'slide' : $topic->photo=view::photo_slide(NULL,$para->photo_width,$para->photo_height,'get/photoslide/'.$topic->tpid.'/imagerotator');break;
					case 'list' : break;
				}
			} else $topic->photo=null;

			switch ($pattern->{'list-style'}) {
				case 'ul' : $ret .= '<li class="'.$topic->status.($para->photo && $topic->photo ? ' '.$para->photo : '').'">';break;
				case 'dl' : $ret .= '<dt class="title">';break;
				case 'div' : $ret .= '<div id="'.$para->id.'-'.$no.'" class="topic-list-item">'._NL.'<h3 class="title">';break;
			}

			/* generate each topic title */
			$ret .= '<a href="'.$topic->_url.'"'.($pattern->{'list-style'}=='div'?' title="'.htmlspecialchars($topic->title).'"':'').'>';
			$ret .= $pattern->{'list-style'} == 'ul' && $para->photo && $topic->photo ? $topic->photo : '';
			if ($para->title) {
				// generate each topic title
				$old_error=error_reporting();
				$show= preg_replace('/\$([a-zA-Z0-9_]*)/','$topic->\\1',$para->title);
				$eval='$show_value='.$show.';';
				eval($eval);
				error_reporting($old_error);
				$ret .= $show_value;
			} else {
				$ret.=$topic->title;
			}
			$ret .= '</a>';

			//			$topic->_title=url(preg_replace('/\$([a-zA-Z0-9_]*)/','$topic->\\1',$pattern->title));
			//			$ret .= $topic->_title;

			switch ($pattern->{'list-style'}) {
				case 'dl' : $ret .= '</dt>'._NL;break;
				case 'div' : $ret .= '</h3>'._NL;
			}

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
			if ($para->new && $is_new_topic ) $ret .= ' '.$new->text;
			$ret.=_NL;

			switch ($pattern->{'list-style'}) {
				case 'ul' : $ret .= '</li>'._NL;break;
				case 'div' : $ret .= '</div><!--topic-list-'.$no.'-->'._NL;
			}
		}
		if ($para->footer && $pattern->{'list-style'}=='div') $ret .= $para->footer._NL;
		$ret .= '</'.$pattern->{'list-style'}.'><!--end of topic-list -->'._NL;
		if ($para->footer && $pattern->{'list-style'}!='div') $ret .= $para->footer._NL;
		if ($para->container) $ret .= '</div><!--end of container-->'._NL;
		$ret.='<!--end of view::content_list #'.$content_list_count.'-->'._NL._NL;
		return $ret;
	}


	/**
	 * Draw Statistic bar
	 *
	 * @param String $type Display type in short,full default is full
	 * @return String $ret
	 */
	public static function stat_bar($type=NULL) {
		$counter = cfg('counter');

		$today = date('Y-m-d');
		$yesterday = date('Y-m-d', strtotime( '-1 days' ) );

		$stmt  = 'SELECT log_date,hits,users FROM %counter_day% WHERE log_date IN (:yesterday, :today)';
		$dbs = mydb::select($stmt,':yesterday',$yesterday,':today',$today);

		$today_hits = $yesterday_hits = null;
		foreach ($dbs->items as $rs) {
			if ($rs->log_date==$today) $today_hits=$rs;
			else if ($rs->log_date==$yesterday) $yesterday_hits=$rs;
		}

		switch ($type) {
			case 'short' : $ret .= '<div id="stat">
				'.(user_access('access statistic')?'<a href="'.url('stats').'">':'').'<span class="stat">Web Statistics: </span>'.(user_access('access statistic')?'</a>':'')
				.'<span>Current <strong><a href="'.(user_access('access statistic')?url('stats'):'#').'" title="'.(user_access('access statistic')?$counter->online_name:'').'">'.$counter->online_members.'</a></strong> members from <strong>'.number_format($counter->online_count).'</strong>
				persons online. </span>'
				. '<span>Today <strong>'.number_format($today_hits->users).'</strong> persons <strong>'.number_format($today_hits->hits).'</strong> views. </span><span>Yesterday <strong>'.number_format($yesterday_hits->users).'</strong> persons <strong>'.number_format($yesterday_hits->hits).'</strong> views. </span><span>Total view <strong>'.number_format($counter->users_count).'</strong> persons <strong>'.number_format($counter->hits_count).'</strong> views from <strong>'.number_format($counter->members).'</strong> members. Since '.sg_date($counter->created_date,'M,d Y').'.</span></div><!--stat-->';
				break;

			default :
				$ret .= _NL.'<div id="stat">'._NL;
				$ret .= '<p>'.(user_access('access statistic')?'<a href="'.url('stats').'">':'').'<span class="stat">Web Statistics</span>'.(user_access('access statistic')?'</a>':'').' : online <strong><a href="'.(user_access('access statistic')?url('stats'):'#').'" title="'.$counter->online_name.'">'.$counter->online_members.'</a></strong> member(s) of '.$counter->online_count.' user(s)</p>'._NL;
				$ret .= '<p>User count is <strong>'.$counter->users_count.'</strong> person(s) and <strong>'.$counter->hits_count.'</strong> hit(s) ';
				$ret .= 'since '.sg_date($counter->created_date,'M,d Y');
				$ret .=' , Total <strong>'.$counter->members.'</strong> member(s).</p>'._NL;
				$ret .= '</div><!--stat-->'._NL;

				$ret .= '<ul id="credit">
					<li class="credit-softganz"><a href="https://softganz.com" title="Web site powered by SoftGanz.">SoftGanz</a></li>
					<li class="credit-mysql"><a href="https://mysql.org" title="MySql Database Server">MySql</a></li>
					<li class="credit-php"><a href="https://php.net" title="php.net">PHP</a></li>
					<li class="credit-apache"><a href="https://apache.org" title="The Apache">Apache</a></li>
					<li class="credit-firefox"><a href="https://mozilla.com/firefox" title="Best view with Mozilla Firefox">Mozilla Firefox</a></li>'.
					(cfg('server') && cfg('spampoison') ? '<li class="spampoison"><a href="https://thai-129858975298.spampoison.com">Spam Poison</a></li>':'').'
					</ul>'._NL;
					break;
		}

		return $ret;
	}

	/**
	 * Draw photo slide using flash slideshow
	 *
	 * @param String $id
	 * @param Integer $w default is 100%
	 * @param Integer $h default is 100%
	 * @param String $para
	 * @return String $ret
	 */
	public static function photo_slide($id,$w,$h,$imgfile,$para=NULL) {
		$ret = '<div'.($id?' id="'.$id.'"':'').' class="slide" style="'.($w?'width:'.$w.'px;':'').($h?'height:'.$h.'px;':'').'">'._NL
					.'	<object type="application/x-shockwave-flash" data="https://softganz.com/library/imagerotator.swf?file='.$imgfile.'&amp;overstretch=true'.($para?'&amp;'.$para:'').'" width="'.\SG\getFirst($w,'100%').'" height="'.\SG\getFirst($h,'100%').'">'._NL
					.'	<param name="movie" value="https://softganz.com/library/imagerotator.swf?file='.$imgfile.'&amp;overstretch=true'.($para?'&amp;'.$para:'').'" width="'.\SG\getFirst($w,'100%').'" height="'.\SG\getFirst($h,'100%').'" />'._NL
					.'	<embed src="https://softganz.com/library/imagerotator.swf" width="100%" height="100%" flashvars="file='.$imgfile.'&amp;overstretch=true'.($para?'&amp;'.$para:'').'" style="width:100%;height:100%;" />'._NL
					.'	</object>'._NL
					.'</div>';
		return $ret;
	}

	/**
	 * List topic div style
	 *
	 * @param Object $topics
	 * @param Mixed $para
	 * @return String
	 */
	public static function list_style_div($topics,$para) {
		$i=$adsCount=0;
		foreach ($topics->items as $topic ) {
			++$i;
			if (isset($this)) event_tricker('paper.listing.item',$this,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;

			$ret .= '<div class="topic-list -style-div '.($para->{'list-class'}?$para->{'list-class'}.' ':'').'topic-list-'.$i.'">'._NL;
			$ret .= '<h3 class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</h3>'._NL;

			if (!$para->option->no_owner) {
				$ret .= '<div class="timestamp'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'.tr('Submitted by').' '.$topic->poster.' on '.sg_date($topic->created,cfg('dateformat'));
				if ($topic->tag) $ret .= ' Tags: '.$topic->tag;
				$ret .= '</div>'._NL;
			}

			// show detail
			if (!$para->option->no_detail) {
				$ret .= '<div class="summary'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'._NL;

				// topic vote
				if (module_install('voteit')) $ret.=do_class_method('voteit','node',$topic,$para);

				if ($topic->photo) {
					$ret .= '<a href="'.url('paper/'.$topic->tpid).'"><img class="image photo-'.($topic->photo->size->width>$topic->photo->size->height?'wide':'tall').'"'.' src="'.$topic->photo->url.'" alt="'.htmlspecialchars($topic->photo->title).'" /></a>';
				}
				$ret.=preg_match('/<p>/',$topic->summary)?$topic->summary:'<p>'.$topic->summary.'</p>';
				$ret.='</div>'._NL;
			}

			if (!$para->option->no_footer) {
				$ret .= '<div class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
				$ret .= $topic->view.' reads | ';
				$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->tpid).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->tpid).'#comment">'.tr('add new comment').'</a>').' | ';
				$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.tr('read more').' &raquo;</a>';
				$ret .= '</div>'._NL;
			}
			if (isset($GLOBALS['ad']->topic_list) && ++$adsCount<=3) $ret.='<div id="ad-topic_list" class="ads">'.$GLOBALS['ad']->topic_list.'</div>';
			$ret .= '</div><!--topic-list-->'._NL;
		}
		return $ret;
	}

	/**
	 * List topic dl style
	 *
	 * @param Object $topics
	 * @param Mixed $para
	 * @return String
	 */
	public static function list_style_dl($topics,$para) {
		$ret .= '<dl class="topic-list -style-dl">'._NL;

		foreach ($topics->items as $topic ) {
			event_tricker('paper.listing.item',$this,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;

			$ret .= '<dt class="title title-status-'.sg_status_text($topic->status).($topic->sticky==_CATEGORY_STICKY?' sticky':'').'" ><a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</dt>'._NL;

			if (!$para->option->no_owner) {
				$ret .= '<dd class="timestamp'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'.tr('Submitted by').' '.$topic->poster.' on '.sg_date($topic->created,cfg('dateformat'));
				if ($topic->tag) $ret .= ' Tags: '.$topic->tag;
				$ret .= '</dd>'._NL;
			}

			// show detail
			if (!$para->option->no_detail) {
				$ret .= '<dd class="summary'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">'._NL;

				// topic vote
				if (module_install('voteit')) $ret.=do_class_method('voteit','node',$topic,$para);

				if ($topic->photo->exists) {
					$ret .= '<img class="image photo-'.($topic->photo->size->width>$topic->photo->size->height?'wide':'tall').'"'.' src="'.$topic->photo->url.'" alt="'.htmlspecialchars($topic->photo->title).'" />';
				}
				$topic->summary=str_replace('<!--read more-->',' <a href="'.url('paper/'.$topic->tpid).'">('.tr('read more').'...)</a>',$topic->summary);
				$ret.=$topic->summary.'</dd>'._NL;
			}

			if (!$para->option->no_footer) {
				$ret .= '<dd class="footer'.($topic->sticky==_CATEGORY_STICKY?' sticky':'').'">';
				$ret .= $topic->view.' reads | ';
				$ret .= ($topic->reply ? '<a href="'.url('paper/'.$topic->tpid).'#comment">'.$topic->reply.' comments</a>':'<a href="'.url('paper/'.$topic->tpid).'#comment">'.tr('add new comment').'</a>').' | ';
				$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.tr('read more').' &raquo;</a>';
				$ret .= '</dd>'._NL._NL;
			}
		}
		$ret .= '</dl>'._NL;
		return $ret;
	}

	/**
	 * List topic table style
	 *
	 * @param Object $topics
	 * @param Mixed $para
	 * @return String
	 */
	public static function list_style_table($topics,$para) {
		$single_rows_per_item=false;cfg('topic.list.table.rows_per_item')=='single';
		$allcols=$single_rows_per_item?6:4;
		$hot=15;
		$veryhot=25;

		$order = SG\getFirst($para->order,'tpid');
		$sort=in_array($para->sort,array('asc','desc'))?$para->sort:'desc';

		$request=q();
		$arrow=$sort=='desc'?'&dArr;':'&uArr;';
		if (!preg_match('/order\//',$request)) $request.='/order/'.$order;
		if (!preg_match('/sort\//',$request)) $request.='/sort/'.$sort;

		$reg='/order\/[\w]*\//i';
		$hurl[1]=preg_replace($reg,'order/tpid/',$request);
		$hurl[2]=preg_replace($reg,'order/reply/',$request);
		$hurl[3]=preg_replace($reg,'order/view/',$request);
		$hurl[4]=preg_replace($reg,'order/last_reply/',$request);
		$current_url=preg_replace('/sort\/(asc|desc)/i','sort/'.($sort=='desc'?'asc':'desc'),$request);

		$ret .= '<table class="topic-list -style-table'.($single_rows_per_item?' topic-list-single':'').'" cellspacing="1" cellpadding="0" border="0">
		<thead>
		<tr>'.($single_rows_per_item?'<th class="postdate"><a href="'.url($order=='tpid'?$current_url:$hurl[1]).'">Post Date'.($para->order=='tpid'?$arrow:'').'</th>':'').'
			<th class="title"><a href="'.url($order=='tpid'?$current_url:$hurl[1]).'">'.tr('Title').($para->order=='tpid'?$arrow:'').'</th>
			'.($single_rows_per_item?'<th>Post By</th>':'').'
			<th class="view"><a href="'.url($order=='view'?$current_url:$hurl[3]).'">'.tr('Views').($para->order=='view'?$arrow:'').'</a></th>
			<th class="reply"><a href="'.url($order=='reply'?$current_url:$hurl[2]).'">'.tr('Replies').($order=='reply'?$arrow:'').'</a></th>
			<th class="lastreply"><a href="'.url($order=='last_reply'?$current_url:$hurl[4]).'">'.tr('Last reply date').($para->order=='last_reply'?$arrow:'').'</a></th>
			</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="'.($allcols).'">
				<ul class="title-status">
				<li class="title-status-normal">Normal Topic</li>
				<li class="title-status-hot">Hot Topic (More than 15 replies)</li>
				<li class="title-status-veryhot">Very Hot Topic (More than 25 replies)</li>
				</ul>
			</td>
		</tr>
		</tfoot>'._NL;
		$ret .= '<tbody>'._NL;
		$no=0;
		foreach ($topics->items as $topic ) {
			if (isset($this)) event_tricker('paper.listing.item',$this,$topic,$para);
			if (empty($topic)) continue;
			if ( empty($topic->poster) ) $topic->poster = $topic->owner;
			$item_class=($no%2?'odd':'even').($topic->sticky==_CATEGORY_STICKY?' sticky':'');
			$ret .= '<tr class="'.$item_class.'">'._NL;
			if ($single_rows_per_item) $ret.='<td class="timestamp">'.sg_date($topic->created,cfg('dateformat')).'</td>';
			$ret .= '<td '.($single_rows_per_item?'':'colspan="'.($allcols).'" ').'class="title title-status-'.sg_status_text($topic->status).' title-status-'.($topic->reply>$veryhot?'veryhot':($topic->reply>$hot?'hot':'normal')).'" >';
			$ret .= '<a href="'.url('paper/'.$topic->tpid).'">'.$topic->title.'</a>'.(in_array($topic->status,array(_PUBLISH,_LOCK))?'':' <em>('.sg_status_text($topic->status).')</em>').'</td>';
			if (!$single_rows_per_item) {
				$ret.='</tr>'._NL;
				//				if ($topic->pagenv) $ret .= '<tr class="'.$item_class.'"><td colspan="'.($allcols-1).'">'.$topic->pagenv.'</td></tr>';
				$ret .= '<tr class="'.$item_class.'">'._NL;
			}

			$poster='';
			if ($topic->uid && user_access('access user profiles')) $poster.='<a href="'.url('profile/'.$topic->uid).'">';
			$poster.='<span class="poster'.(i()->ok && $topic->uid==i()->uid?' owner':'').'">'.($single_rows_per_item?'':'by ').$topic->poster.'</span>';
			if ($topic->uid && user_access('access user profiles')) $poster.='</a>';
			if (!$single_rows_per_item) $poster.='<span class="timestamp"> @'.sg_date($topic->created,cfg('dateformat')).'</span>';

			$ret .= '<td class="poster">'.$poster.'</td>'._NL;

			$ret .= '<td class="stat stat-view">'.$topic->view.'</td>';
			$ret .= '<td class="stat stat-reply">'.($topic->reply?$topic->reply:'-').'</td>';
			$ret .= '<td class="timestamp">'.($topic->reply?sg_date($topic->last_reply,cfg('dateformat')):'').'</td>'._NL;
			$ret .= '</tr>'._NL;

			$comment_page_items = SG\getFirst(cfg('comment.items'),20);
			if ($topic->comments>1 && ($page_count=ceil($topic->comments/$comment_page_items))>1) {
				$page_str = '<span>Page</span>';
				for ($i=1;$i<=$page_count;$i++) {
					$page_str .= '<a href="'.url('paper/'.$topic->tpid.'/page/'.$i).'">'.$i.' </a>';
				}
				$page_str .= '<a href="'.url('paper/'.$topic->tpid.'/page/'.$page_count).'">last &raquo;</a>';
				$page_str = trim($page_str);
				$ret.='<tr class="'.$item_class.' comment_page">';
				$ret.=($single_rows_per_item?'<td></td><td colspan="2">':'<td>').$page_str.'</td>';
				$ret.='<td colspan="'.($allcols-($single_rows_per_item?3:1)).'"></td></tr>'._NL;
			}
			$ret.=_NL;
			$no++;
		}
		$ret .= '</tbody>'._NL;
		$ret .= '</table>'._NL;
		return $ret;
	}

	/**
	 * List topic ul style
	 *
	 * @param Object $topics
	 * @param Mixed $para
	 * @return String
	 */
	public static function list_style_ul($topics,$para) {
		$ret = '<ul '.($para->id?'id="'.$para->id.'" ':'').'class="topic-list -style-ul">'._NL;
		foreach ($topics->items as $topic) {
			$photo_str=$para->photo && $topic->photo?'<img class="'.$para->photo.'" src="'.$topic->photo->url.'" alt="" />':'';
			$ret .= '<li><a class="title" href="'.url('paper/'.$topic->tpid).'">'.($para->photo && $photo_str ? $photo_str : '').$topic->title.'</a>';
			$ret .= '<span class="poster"> by '.\SG\getFirst($topic->poster,$topic->owner).'</span>';
			$ret .= '<span class="time_stamp">@'.sg_date($topic->created,cfg('dateformat')).'</span>';
			$ret .= '<span class="stat"> | '.$topic->view.' reads'.($topic->reply?' | <strong>'.$topic->reply.'</strong> comment(s)':'').'</span>';
			if ($para->option->detail) $ret .= _NL.'<p class="summary">'.$topic->summary.'</p>'._NL;
			$ret .= '</li>'._NL;
		}
		$ret .= '</ul>'._NL;
		return $ret;
	}

	/**
	 * List all tags
	 *
	 * @return String $ret
	 */
	public static function tags() {
		$para=para(func_get_args());
		$tags=mydb::select('SELECT t.tid,t.name,
												(SELECT COUNT(tid) AS max FROM %tag_topic% GROUP BY tid ORDER BY max DESC LIMIT 1) AS max,
												(SELECT COUNT(*) FROM %tag_topic% tp WHERE tp.tid=t.tid) AS topics
											FROM %tag% t
											ORDER BY t.`name` ASC');
		$tags_topics=mydb::select('SELECT tid,COUNT(*) FROM %tag_topic% GROUP BY tid ORDER BY tid');

		if ($tags->_empty) return false;

		if ($para->container) {
			list($container)=explode(' ',$para->container);
			$ret.='<'.$para->container.'>'._NL;
		}
		if ($para->header) $ret.='<h2>'.$para->header.'</h2>'._NL;
		foreach ($tags->items as $tag) {
			$level=round($tag->topics/$tag->max*4)+1;
			$ret.='<a href="'.url('tags/'.$tag->tid).'" class="tagadelic level'.$level.'">'.$tag->name.'</a> '._NL;
		}
		if ($para->container) $ret.='</'.$container.'>'._NL;
		return $ret;
	}

	/**
	 * Show field for inline edit
	 *
	 * @param String/Array $fld
	 * @param String $text
	 * @param Boolean $is_edit
	 * @param String $input_type
	 * @param Array $data
	 * @return String
	 */
	public static function inlineEdit($fld = [], $text = NULL, $is_edit = NULL, $input_type = 'text', $data = [], $emptytext = '...') {
		return \SG\inlineEdit($fld, $text, $is_edit, $input_type, $data, $emptytext);
	}

	/**
	 * Show field for inline edit
	 *
	 * @param String/Array $fld
	 * @param String $text
	 * @param Boolean $is_edit
	 * @param String $input_type
	 * @param Array $data
	 * @return String
	 */
	public static function show_field($fld,$text,$is_edit,$input_type='text',$data=array()) {
		$emptytext='...';
		if ($is_edit) {
			if (is_string($data)) $data=explode(',','==เลือก==,'.$data);
			else if (is_array($data) && count($data)>0) $data=array('==เลือก==')+$data;
			$attr='';
			if (is_array($fld)) {
				foreach ($fld as $k=>$v) $attr.=$k.'="'.$v.'" ';
			} else if (is_string($fld)) {
				$attr='fld="'.$fld.'"';
			}
			if (is_array($text)) {
				$ret.='<ul>'._NL;
				foreach ($text as $k=>$v) {
					$ret.='<li><span fld="'.$k.':'.$fld.'" input-type="'.$input_type.'" '._NL.'data="'.htmlspecialchars(json_encode($data)).'" data-value="'.htmlspecialchars($v).'">'.\SG\getFirst(trim($v),$emptytext).'</span></li>'._NL;
				}
				$ret.='</ul>'._NL;
			} else {
				$ret.='<span '.$attr.' input-type="'.$input_type.'"'.($data?' data="'.htmlspecialchars(json_encode($data)).'"':'').' data-value="'.htmlspecialchars($text).'">'.\SG\getFirst(trim($text),$emptytext).'</span>';
			}
		} else {
			if (is_array($text)) {
				foreach ($text as $k=>$v) $ret=implode(' , ',$text);
			} else {
				$ret=$text;
			}
		}
		//		$ret.=print_o($text,'$text');
		return $ret;
	}

	public static function social($url) {
		$link=cfg('domain').$url;
		//$link=preg_replace('/http\:\/\/www./i','http://',$link);
		$ret.='
		<div class="social -clear-fix">
		<!-- Facebook share button -->
		<div class="fb-share-button subview" data-href="'.$link.'" data-layout="box_count"></div>
		<!-- Twitter share button -->
		<div class="subview"><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="" data-url="'.$link.'" >Tweet</a></div>
		<!-- Fackbook like button -->
		<div class="fb-like" data-href="'.$link.'" data-send="true" data-width="450" data-show-faces="true"></div>
		</div><!-- social -->
		<style type="text/css">
		.subview {margin:0 5px 0 0;display:inline-block;}
		.fb_share_count_top,.fb_share_count_inner,.FBConnectButton_Small,.FBConnectButton_RTL_Small,.FBConnectButton_Small .FBConnectButton_Text {border-radius:4px;}
		.fb_share_count_top {width:48px !important;}
		.FBConnectButton_Small,.FBConnectButton_RTL_Small {width:49px !important;margin:2px 0 0 0;}
		.FBConnectButton_Small .FBConnectButton_Text {padding:2px 2px 3px !important;font-size:10px;font-weight:normal !important;}
		</style>
		';
		if (_ON_HOST) {
			head('widgets.js','<script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>');
			//head('FB.Share','<script type="text/javascript" src="https://static.ak.fbcdn.net/connect.php/js/FB.Share"></script>');
		}
		return $ret;
	}

} // end of class view
?>