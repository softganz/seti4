<?php
/**
 * view class for CMV
 *
 * @package core
 * @version 0.20
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created :: 2007-12-25
 * @modify  :: 2026-08-23
 * Version  :: 4
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class View {

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