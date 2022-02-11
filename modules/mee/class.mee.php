<?php
/**
* SOFTGANZ :: Class rss
*
* Copyright (c) 2000-2006 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*             : http://www.softganz.com/
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/
/**
--- Created 2007-06-15
--- Modify   2007-06-15
*/
set_time_limit(600);

cfg('mee.version','7.6.15');

menu('mee','My Admin','mee','main',1,'access administrator pages','static');

require_once('class.mee.url.php');

//define('DOMIT_RSS_INCLUDE_PATH',dirname(__FILE__).'/rssfeeder/');
//require_once(DOMIT_RSS_INCLUDE_PATH . 'xml_domit_rss.php');

/***************************************
Class  :: mee
****************************************/

class XmlElement {
  var $name;
  var $attributes;
  var $content;
  var $children;
};

class mee extends module {
var $module='mee';
var $header='SoftGanz Group homepage';
	function mee() { $this->module(); }

	function main($action=NULL,$id=NULL) {
		$para=para(func_get_args(),1);
		if (i()->username != 'softganz') return error('access_deny');

		echo '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<title>'.$this->header.'</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="Content-Language" content="th">
		<meta name="content owner" content="SoftGanz">
		<meta name="programmer" content="designed Team : Little Bear by SoftGanz Group.">
		<style>
		<!--
		body { margin: 0; padding: 0; font-family : "Tahoma";font-size:9pt; color : #003300;}
		a { text-decoration:none; }
		a:hover { color:#ff6600; }
		#page-wrapper {}
		#header-wrapper {margin:0;padding:0 0 5px 0;background-color:#97D300;}
		#header-wrapper h1 {padding:0;margin:0;}
		#header-wrapper ul { margin: 0 0 10px 0; padding: 0px;}
		#header-wrapper ul li { margin:0 20px; font-weight:bold;display:inline;}
		#content-wrapper { margin: 0;padding:0; background-color:#DEEFB3;}
		#left { float: left; width:20%; margin: 0; padding:0;}
		#right {}
		#right h2 { margin:0;padding:0;}
		#footer-wrapper { background-color:#97D300;clear:both; }
		/* @group item */
		table.item {width:100%;margin:0 0 10px 0; border-collapse: collapse;border-spacing: 0; border:none; background: #fff;}
		table.item.-table {width:auto;}
		.item caption {margin:2px 0;padding:4px;font-size:1.1em;color:#333;font-weight:bold; background: #d5d5d5;}
		.item tfoot {font-weight:bold;background:#DDDDDD;}
		.item tr:hover {background: #f5f5f5;}
		.item th {padding:8px 0; background: #e5e5e5; border-bottom:1px #fff solid; border-right:1px #f0f0f0 solid; vertical-align: middle;}
		.item th:last-child {border-right: none;}
		.item td {padding:8px 0;vertical-align:top; border-bottom:1px #ddd solid;}
		.item .subheader {font-weight: bold; background-color: #ddd;}
		.item .subfooter {font-weight:bold; background-color: #eaeaea;}
		.item.-center td {text-align: center;}
		.item.-cols8 td {width:12.5%;}
		.col-center {text-align: center;}
		.col-money {text-align: right;}
		.col-no {text-align:center;}
		.col-no:after {content:".";}
		.col-amt {text-align:center;}
		.col-tool {text-align:center;}
		.col-date {text-align:center;white-space:nowrap;}
		.col-status {text-align:center;}
		.col-poster {text-align:center;}
		.item tbody .order {background: #eee;}

		.item__card thead {display: none;}
		.item__card td {display: block; float:left; width:100%;}
		.item__card td:first-child {font-weight:bold; border-bottom:none;}
		.item__card col {width:100%;}


		.item .stat {text-align:center;}
		/* @end */
		-->
		</style>
		</head>

		<body>
		<div id="page-wrapper">
			<div id="header-wrapper">
				<div id="header"><h1><a href="'.url().'" title="SoftGanz Group homepage.">'.$this->header.'</a></h1></div>
				<ul>
				<li><a href="'.url('mee').'">Home</a></li>
				<li><a href="'.url('mee/stat').'">Statistics</a></li>
				<li><a href="'.url('mee/getversion').'">Version</a></li>
				</ul>
			</div><!--header-wrapper-->

			<div id="content-wrapper">
			<div id="right">';
				if ($action) {
					$main_method='request_'.$action;
					if (method_exists($this,$main_method)) {
						$process_result .= $this->$main_method($para,$option);
					}
				} else $process_result=$this->request_web();
				echo $process_result;
		echo '</div>
			</div><!--content-wrapper-->

			<div id="footer-wrapper">
			<div id="copyright">
				&copy; Copyright 1999 - '.date("Y").' <a href="http://www.softganz.com/">http://www.softganz.com</a> , All Right Reserved.
			</div><!--copyright-->
			</div><!--footer-wrapper-->
		</div><!--page-wrapper-->

		</body>
		</html>';
		die;
	}

	function request_web($para=NULL) {
		$ret .= '<table cellspacing=0 cellpadding=2>';
		$delemiter=false;
		foreach ( $GLOBALS['url_list'] as $key=>$value ) {
			if (!is_array($value)) {
				$delemiter=true;
				continue;
			}
			$domain=$value[0];
			$url=$value[1];
			$surl = preg_replace('/www\./','',$domain);
			$ret .= '<tr>';
			$ret .= '<td nowrap'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="'.url('mee/counter/www/'.$key).'">'.preg_replace('/(^www\.)|(^www2\.)/','',$domain).'</a></td>';
			$ret .= '<td'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="'.$domain.'/admin/comment/list" target="_blank" title="View website">&raquo;</a></td>';
			$ret .= '</tr>';
			$delemiter=false;
		}
		$ret .= '</table>';
		return $ret;
	}

	function xml_to_object($xml) {
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $tags);
		xml_parser_free($parser);

		$elements = array();  // the currently filling [child] XmlElement array
		$stack = array();
		foreach ($tags as $tag) {
		  $index = count($elements);
		  //print_o($tag,'$tag',1);
		  //$index=$tag['tag'];
		  if ($tag['type'] == "complete" || $tag['type'] == "open") {
		    $elements[$index] = new XmlElement;
		    $elements[$index]->name = $tag['tag'];
		    $elements[$index]->attributes = $tag['attributes'];
		    $elements[$index]->content = $tag['value'];
		    if ($tag['type'] == "open") {  // push
		      $elements[$index]->children = array();
		      $stack[count($stack)] = &$elements;
		      $elements = &$elements[$index]->children;
		    }
		  }
		  if ($tag['type'] == "close") {  // pop
		    $elements = &$stack[count($stack) - 1];
		    unset($stack[count($stack) - 1]);
		  }
		}
		return $elements[0];  // the single top-level element
	}

	function XMLToArray($xml) {
	  $parser = xml_parser_create(); // For Latin-1 charset
	  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); // Dont mess with my cAsE sEtTings
	  xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); // Dont bother with empty info
	  xml_parse_into_struct($parser, $xml, $values);
	  xml_parser_free($parser);

	  $return = array(); // The returned array
	  $stack = array(); // tmp array used for stacking
	  foreach($values as $val) {
	    if($val['type'] == "open") {
	      array_push($stack, $val['tag']);
	    } elseif($val['type'] == "close") {
	      array_pop($stack);
	    } elseif($val['type'] == "complete") {
	      array_push($stack, $val['tag']);
	      $this->setArrayValue($return, $stack, $val['value']);
	      array_pop($stack);
	    }//if-elseif
	  }//foreach
	  return $return;
	}//function XMLToArray

	function setArrayValue(&$array, $stack, $value) {
	  if ($stack) {
	    $key = array_shift($stack);
	 		echo '$key='.$key.' => setArrayValue<br />';
	  	if ($key=='item') {
	  		print_o($array,'$array',1);
	  		print_o($stack,'$stack',1);
		    //$keyvalue = array_shift($stack);
		    //$array[$key][]=$key;
		    //return $array;
		    $this->setArrayValue($array[$key], $stack, $value);
		  } else {
		    $this->setArrayValue($array[$key], $stack, $value);
		  }
	    return $array;
	  } else {
		    $array = $value;
	  }//if-else
	}//function setArrayValue

	function request_counter($para=NULL) {
		$timer = new timer();

		$day = isset($para->day) ? $para->day:30;
		$show=isset($para->show) ? $para->show : 'normal';

		$ret.='<table cellspacing=0 cellpadding=2>';
		$delemiter=false;
		foreach ( $GLOBALS['url_list'] as $key=>$item) {
			if (!is_array($item)) { $delemiter=true;continue;}

			$host=$item[0];
			if (substr($host,0,8)=='https://') {
				$httpPrefix='https://';
				$host=substr($host,8);
			} else {
				$httpPrefix='http://';
			}

			$url=$item[1];
			if (empty($url)) {
				$ret.='<tr><td>'.$host.'</td></tr>';
				continue;
			}

			$shorturl=preg_replace('/(^www\.)|(^www2\.)/','',$host);
			$ret .= '<tr align="center"><td align="left" nowrap="nowrap"'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="'.url('mee/counter/www/'.$key).'">'.preg_replace('/(^www\.)|(^www2\.)/','',$host).'</a></td>';
			$ret .= '<td'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="http://'.$host.'/admin/comment/list" target="_blank" title="View website">&raquo;</a></td>';
			$delemiter=false;
			if ($key==0) {
				$ret.='<td valign="top" rowspan="'.count($GLOBALS['url_list']).'">';

				$host=$GLOBALS['url_list'][$para->www][0];
				if (substr($host,0,8)=='https://') {
					$httpPrefix='https://';
					$host=substr($host,8);
				} else {
					$httpPrefix='http://';
				}
				$url = $httpPrefix.$host.'/'.$GLOBALS['url_list'][$para->www][1].$day;
				$timer->start("p");

				$api=getapi($url);

				$xml=$this->xml_to_object($api['result']);

				//$xml=$this->XMLToArray($api['result']);


				$this->header= 'Counter of '.$host;
				$ret .= 'Request url is <a href="'.$url.'">'.$url.'</a><br />';
				$tables=new table();
				$tables->thead=array('Date','Hit(s)','User(s)');
				foreach ($xml->children[0]->children as $item) {
					if (empty($item->children)) continue;
					$title=$item->children[0]->content;
					$content=$item->children[1]->content;
					//$ret.='<div style="text-align:left;">'.$title.'='.$content.'</div>';
					//$ret.='<div style="text-align:left;">'.print_o($item,'$item').'</div>';
					switch ( $title ) {
						case "online" :
							list($date,$member,$user,$name)=explode("/",$content);
							$tables->rows[]=array("<td colspan=3><b>Online</b> : $member member(s) from $user user(s)<br>($name)</td>");
							break;
						case "response time" :
							$tables->rows[]=array("<td colspan=3><b>response time</b> : ".$content."</td>");
							break;
						case "stat" :
							list($date,$hits,$users)=explode("/",$content);
							if ( isset($_GET["show"]) and $_GET["show"]==="short" ) $date=substr($date,8,2)."-".substr($date,5,2);
							$tables->rows[]=array($date,$hits,$users,'config'=>array('class'=>'stat'));
							break;
					}
				}


				$ret.=$tables->build();



				//$ret.='<div style="text-align:left;">'.print_o($xml->children[0]->children,'$x').print_o($xml,'$xml').'</div>';

				//xml_parse_into_struct($p, $xml['result'], $vals, $index);
				//xml_parser_free($p);
				//$ret.=print_o($vals,'$vals');
				//$ret.=print_o($index,'$index');


				/*
				$rssdoc =& new xml_domit_rss_document($url,'none/',0);
				$timer->stop("p");


				$this->header= 'Counter of '.$host;
				$ret .= 'Request url is <a href="'.$url.'">'.$url.'</a><br />';
				//get total number of channels
				$totalChannels = $rssdoc->getChannelCount();

				//loop through each channel
				for ($i = 0; $i < $totalChannels; $i++) {
					//get reference to current channel
					$currChannel =& $rssdoc->getChannel($i);

					//get total number of items
					$totalItems = $currChannel->getItemCount();

					//loop through each item
					$ret .= "<table border=1 cellspacing=0 cellpadding=2>\n";
					$ret .= "<tr><th>Date</th><th>Hit(s)</th><th>User(s)</th></tr>\n";
					for ($j = 0; $j < $totalItems; $j++) {
						//get reference to current item
						$currItem =& $currChannel->getItem($j);

						//echo item info
						$ret .= "<tr>";
						switch ( $currItem->getTitle() ) {
							case "online" :
								list($date,$member,$user,$name)=explode("/",$currItem->getDescription());
								$ret .=  "<td colspan=3><b>Online</b> : $member member(s) from $user user(s)<br>($name)</td></tr>\n";
								break;
							case "response time" :
								$ret .=  "<td colspan=3><b>response time</b> : ".$currItem->getDescription()."</td></tr>\n";
								break;
							case "stat" :
								list($date,$hits,$users)=explode("/",$currItem->getDescription());
								if ( isset($_GET["show"]) and $_GET["show"]==="short" ) $date=substr($date,8,2)."-".substr($date,5,2);
								$ret .= "<tr align=center><td>$date</td><td>$hits</td><td>$users</td></tr>\n";break;
						}
						$ret .= "</tr>\n";
					}
					$ret.="</table>";
				}
				*/
				$ret.='</td>';
			}
			$ret.='</tr>';
		}
		$ret.='</table>';

		return $ret;
	}


	function request_getversion($para=NULL) {
		$timer = new timer();

		$day = isset($para->day) ? $para->day:30;
		$show=isset($para->show) ? $para->show : 'normal';

		$ret.='<table cellspacing=0 cellpadding=2>';
		echo $ret;
		flush();
		$delemiter=false;
		foreach ( $GLOBALS['url_list'] as $key=>$item) {
			if (!is_array($item)) { $delemiter=true;continue;}
			$host=$item[0];
			if (substr($host,0,8)=='https://') {
				$httpPrefix='https://';
				$host=substr($host,8);
			} else {
				$httpPrefix='http://';
			}
			$url=$item[1];
			if (empty($url)) {
				$ret.='<tr><td>'.$host.'</td></tr>';
				continue;
			}

			$shorturl=preg_replace('/(^www\.)|(^www2\.)/','',$host);
			$ret='<tr>'._NL;
			$ret.='<td align="left" nowrap="nowrap"'.($delemiter?' style="border-top:1px gray solid;"':'').'>'.'<a href="'.url('mee/counter/www/'.$key).'">'.preg_replace('/(^www\.)|(^www2\.)/','',$host).'</a></td>'._NL;

			$ret .= '<td'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="'.$httpPrefix.$host.'/admin/comment/list" target="_blank" title="View website">&raquo;</a></td>'._NL;

			$delemiter=false;
			$url = $httpPrefix.$host.'/system/info';

			//$ret.=$url;
			$api = getapi($url);
			// print_o($api, '$api',1);
			$systemInfo = $api['result']; //SG\json_decode($api['result']);
			// print_o($systemInfo, '$systemInfo');
			if ($systemInfo->coreVersion) {
				$ret .= '<td>'.$systemInfo->coreName.' '.$systemInfo->coreVersion.'</td><td>=> Database : '.$systemInfo->databaseVersion.'</td><td>'.($httpPrefix=='https://'?$httpPrefix:'').'</td>'._NL;
			} else {
				$ret.='<td>N/A</td><td>N/A</td>'._NL;
			}

			// if (preg_match('/^[s0-9]/i', $version)) {
			// 	//strtoupper(substr($version,0,1))=='S') {
			// 	list($coreVersion,$installVersion)=explode('/',$version);
			// 	$ret.='<td>'.$coreVersion.'</td><td>=> Database : '.$installVersion.'</td><td>'.($httpPrefix=='https://'?$httpPrefix:'').'</td>'._NL;
			// } else {
			// 	$ret.='<td>N/A</td><td>N/A</td>'._NL;
			// }
			//$ret.=print_o($api,'$api');

			$ret.='</tr>'._NL;
			echo $ret;
			flush();
		}
		$ret.='</table>';

		return $ret;
	}


	function request_stat($para=NULL) {
		global $today;
		$this->header= 'Web statistics '.$today->datetime;

		$timer = new timer();
		$timer->start('all');
		$show=isset($para->show) ? $para->show : 'normal';
		$day=isset($para->day) ? $para->day : 7;

		$ret .= '<table border=1 cellspacing=0 cellpadding=2 width=100%>';

		if ( $show === 'short' ) {
			$ret .= '<tr nowrap><th>site</th>';
			$ret .= '<th>m/u</th>';
			for ( $i=0;$i<$day;$i++) {
				$ret .= '<th nowrap>'.date('M d',mktime (0,0,0,$today->mon ,$today->mday-$i,$today->year)).'</th>';
			}
			$ret .= '</tr>'._NL;
		} else {
			$ret .= '<tr><th rowspan=2>web site</th><th rowspan="2"></th>';
			$ret .= '<th rowspan=2>time<br />(sec)</th>';
			$ret .= '<th colspan=2>online</td>';
			for ( $i=0;$i<$day;$i++) {
				$ret .= '<th colspan=2>'.date('M d',mktime (0,0,0,$today->mon ,$today->mday-$i,$today->year)).'</th>';
			}
			$ret .= '</tr>'._NL;
			$ret .= '<tr>';
			$ret .= '<th>member</th>';
			$ret .= '<th>user</th>';
			for ( $i=0;$i<$day;$i++) $ret .= '<th>hit</th><th>user</th>';
			$ret .= '</tr>'._NL;
		}

		foreach ( $GLOBALS['url_list'] as $key=>$item) {
			if (!is_array($item)) {
				$ret .= '<tr height=5 style="height:5px;"><td colspan=19></td></tr>';
				flush();
				continue;
			}
			if (empty($item[1])) {
				$ret .= '<tr height=5 style="height:5px;"><td><a href="http://'.$item[0].'">'.$item[0].'</a></td><td colspan=18></td></tr>';
				flush();
				continue;
			}

			$timer->start($key);
			$host = $item[0];
			if (substr($host,0,8)=='https://') {
				$httpPrefix='https://';
				$host=substr($host,8);
			} else {
				$httpPrefix='http://';
			}
			$url = $httpPrefix.$host.'/'.$item[1].$day;
			//$rssdoc =& new xml_domit_rss_document($url,'none/',0);
			$api=getapi($url);

			$xml=$this->xml_to_object($api['result']);

			$timer->stop($key);

			$shorturl=preg_replace('/(^www\.)|(^www2\.)/','',$host);
			$ret .= '<tr align="center"><td align="left" nowrap="nowrap"'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="'.url('mee/counter/www/'.$key).'">'.preg_replace('/(^www\.)|(^www2\.)/','',$host).'</a></td>';
			$ret .= '<td'.($delemiter?' style="border-top:1px gray solid;"':'').'><a href="http://'.$host.'" target="_blank" title="View website">&raquo;</a></td>';

			if ($api['error']) { $ret .= '<td colspan=17 align="left"><font color=red>rss feeder error.</td></tr>'; continue; }

			//loop through each item
			if ( $show != "short" ) $ret .= "<td>".number_format($timer->get($key)/1000,2)."</td>";
			$total['time']+=$timer->get($key);
			$date=$member=$user=$name=NULL;
			$hit_count=$user_count=array();

			foreach ($xml->children[0]->children as $item) {
				if (empty($item->children)) continue;
				$title=$item->children[0]->content;
				$content=$item->children[1]->content;

				switch ($title) {
					case "online" :
						list($date,$member,$user,$name)=explode("/",$content);
						if ( $show === "short" ) $ret .=  "<td>$member/$user</td>";
						else $ret .=  "<td>".($member?"<b>$member</b>":"$member")."</td><td>$user</td>";
						$total['member']+=$member;$total['user']+=$user;
						break;
					case "stat" :
						list($date,$hits,$users)=explode("/",$content);
						$hit_count[$date]=$hits;
						$user_count[$date]=$users;
						$total[$date]['hits']+=$hits;$total[$date]['users']+=$users;
						break;
				}
			}
			for ( $i=0;$i<$day;$i++) {
				$date = date("Y-m-d",mktime (0,0,0,$today->mon ,$today->mday-$i,$today->year));
				$ret .= "<td bgcolor=#f5f5f5 nowrap>".(isset($hit_count["$date"]) ? $hit_count["$date"] :"-")."</td>";
				if ( $show != "short" ) $ret .= "<td>".(isset($user_count["$date"]) ? $user_count["$date"] :"-")."</td>";
			}

			$ret .= "</tr>\n";
			if ( $show === "short" ) $all_col = 1+$day; else $all_col = 2+$day*2;
			if ( $show === "short" ) $name=strings::text2wap($name);
			if ( $name ) $ret .= '<tr><td colspan="3">&nbsp;</td><td colspan='.$all_col.'>'.$name.'</td></tr>'._NL;


			echo $ret;
			unset($ret);
			flush();
		}
		$ret .= '<tr align=center><td align=right><b>Total</b></td><td></td><td>'.number_format($total['time']/1000,2).'</td><td>'.$total['member'].'</td><td>'.$total['user'].'</td>';
		for ( $i=0;$i<$day;$i++) {
				$date = date("Y-m-d",mktime (0,0,0,$today->mon ,$today->mday-$i,$today->year));
				$ret .= '<td bgcolor=#f5f5f5 nowrap>'.$total[$date]['hits'].'</td><td>'.$total[$date]['users'].'</td>';
		}
		$ret .= '</table>';
		$timer->stop("all");
		$ret .= "total response time ".number_format($timer->get("all")/1000,2).' sec.';
		return $ret;
	}

} // end of class rss
?>
