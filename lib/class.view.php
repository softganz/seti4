<?php
/**
 * view class for CMV
 *
 * @package core
 * @version 0.20
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created :: 2007-12-25
 * @modify  :: 2025-10-27
 * Version  :: 3
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
			$topics = NodeModel::$model($para);
		}
		if ($topics->_type=='record') $topics=mydb::convert_record_to_recordset($topics);
		if (empty($topics->items)) return;

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
		<!-- Twitter share button -->
		<div class="subview"><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="" data-url="'.$link.'" >Tweet</a></div>
		</div><!-- social -->
		<style type="text/css">
		.subview {margin:0 5px 0 0;display:inline-block;}
		</style>
		';
		if (_ON_HOST) {
			head('widgets.js','<script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>');
		}
		return $ret;
	}

} // end of class view
?>