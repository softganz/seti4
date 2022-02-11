<?php
/**
 * flood_basin class for Flood Management
 *
 * @package flood
 * @version 0.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2014-08-18
 * @modify 2014-08-18
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class flood_data extends flood_base {

	function __construct() {
		parent::__construct();
	}

	function _home($basin) {
		$ret.='Data';
		return $ret;
	}

	function _hydro() {
		$stations=mydb::select('SELECT DISTINCT `station` FROM %flood_flow%')->items;
		$ui=new ui();
		foreach ($stations as $rs) {
			$ui->add('<a href="'.url('flood/data/hydro',array('station'=>$rs->station)).'">'.$rs->station.'</a>');
		}
		$ret.='Station : '.$ui->build();

		if (post('station')) {
			$stmt='SELECT * FROM %flood_flow% WHERE `station`=:station ORDER BY `trdate` DESC, `trtime` DESC';
			$dbs=mydb::select($stmt,':station',post('station'));

			$tables = new Table();
			$tables->thead=array('Date','Time','Station','Level','Flow');

			foreach ($dbs->items as $rs) {
				$tables->rows[]=array($rs->trdate,$rs->trtime,$rs->station,$rs->level,$rs->flow);
			}

			$ret .= $tables->build();
		}
		return $ret;
	}

	function _update_hydro() {
		$result=$this->_get_hydro();

		$flows=$result['flows'];
		foreach ($flows as $item) {
			$stmt='INSERT INTO %flood_flow% SET `trdate`=:date, `trtime`=:time, `station`=:station, `level`=:level, `flow`=:flow
			ON DUPLICATE KEY UPDATE `level`=:level, `flow`=:flow';
			mydb::query($stmt,$item);
		}


		$ret.=print_o($flows,'$flows');

		return $ret;
	}

	/**
	* Get data from http://hydro-8.com/main/hourly/songkla/hourlysongkla.htm
	*
	* @return String
	*/
	function _get_hydro() {
		$urlList=array(
						//'http://www.songkhla.tmd.go.th/RF/Data/data.php?d=20141016',
						'http://www.hydro-8.com/main/hourly/songkla/hourlysongkla.htm',
					);
		foreach ($urlList as $url) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 300);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


			$page = curl_exec($ch);
			curl_close($ch);
			//			$ret.='Error = '.curl_errno($ch);
			//			$ret.='Result '.($page===false?' False':($page===true?'True':'N/A')).'<br />';
			//			$ret.='Error='.curl_errno().'<br />';
			//			$ret.='$Page of '.$url.' is <br />'.htmlview($page).'<hr />';

			$dom = new DOMDocument;
			$dom->loadHTML( $page );

			$rows = array();
			foreach( $dom->getElementsByTagName( 'tr' ) as $tr ) {
				$cells = array();
				foreach( $tr->getElementsByTagName( 'td' ) as $td ) {
					$cells[] = $td->nodeValue;
				}
				$rows[] = array_map('trim',$cells);
			}
		}
		$stations=array_slice($rows[32], 3);
		$today=date('Y-m-d');
		$yesterday=date('Y-m-d', time() - 60 * 60 * 24);
		for ($i=7; $i<=30;$i++) {
			$row=$rows[$i];
			$time=trim($row[0]);
			$time=str_replace('.', ':', $time);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[0],
												'time'=>$time,
												'level'=>$row[1],
												'flow'=>$row[2],
												);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[1],
												'time'=>$time,
												'level'=>$row[3],
												'flow'=>$row[4],
												);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[2],
												'time'=>$time,
												'level'=>$row[5],
												'flow'=>$row[6],
												);

			$flows[]=array(	'date'=>$today,
												'station'=>$stations[0],
												'time'=>$time,
												'level'=>$row[7],
												'flow'=>$row[8],
												);
			$flows[]=array(	'date'=>$today,
												'station'=>$stations[1],
												'time'=>$time,
												'level'=>$row[9],
												'flow'=>$row[10],
												);
			$flows[]=array(	'date'=>$today,
												'station'=>$stations[2],
												'time'=>$time,
												'level'=>$row[11],
												'flow'=>$row[12],
												);
		}
		$result['flows']=$flows;
		$result['stations']=$stations;
		$result['rows']=$rows;

		return $result;
	}

	function _get_rain() {
		$urlList=array(
						'http://www.songkhla.tmd.go.th/RF/Data/data.php?d=20141016',
					);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://www.songkhla.tmd.go.th/RF/Data/login.php');
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 300);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			curl_setopt_array($ch, array(
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => array(
				'user' => '',
				'pass' => ''
				)
			));
			   curl_setopt($ch, CURLOPT_FAILONERROR, 1);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			$page = curl_exec($ch);
			echo $page;


		foreach ($urlList as $url) {
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 300);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


			$page = curl_exec($ch);
			curl_close($ch);

		}
	}

	function _get_drw() {
		$urlList=array(
							array('http://202.129.59.76/website/ews_all/service_list_xml.php','on_rainsta=9'),
							array('http://202.129.59.76/website/ews_all/service_list_xml.php','on_rainsta=0'),
							);
		foreach ($urlList as $urlItem) {
			list($url,$postPara)=$urlItem;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 300);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
			//curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postPara);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$page = curl_exec($ch);
			curl_close($ch);
/*
			$page1=sg_tis620_to_utf8('<?xml version="1.0" encoding="TIS-620"?>
<system>
<count>2068</count>
<rainstatus>0</rainstatus>
<status>Online</status>
<stnlist>
<stn id="1">
<name>Station 1</name>
<tambon>Tambon 1</tambon>
<amphoe>Amphoe 1</amphoe>
<province>Province 1</province>
<type>STN1053(GD_ID.152)</type>
<warning></warning>
<rain15m>0.0</rain15m>
<rain12h>0.0</rain12h>
<temp>26.00</temp>
<wl>N/A</wl>
<dc>2014/11/26 17:30</dc>
</stn>
<stn id="2">
<name>ºéÒ¹ËÅèÍÂÙ§</name>
<tambon>ËÅèÍÂÙ§</tambon>
<amphoe>µÐ¡ÑèÇ·Øè§</amphoe>
<province>¾Ñ§§Ò</province>
<type>·ÕèµÑé§Ê¶Ò¹Õ STN1052(GD_ID.144)</type>
<warning></warning>
<rain15m>0.0</rain15m>
<rain12h>0.0</rain12h>
<temp>27.60</temp>
<wl>N/A</wl>
<dc>2014/11/26 17:30</dc>
</stn>
</stnlist>
</system>');
*/
/*
			$page = sg_tis620_to_utf8($page);
			$xml = simplexml_load_string($page);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
*/
			$stnImport=array(
							// อู่ตะเภา
							'STN0507','STN0227','STN0212',
							'STN1044','STN1032','STN1030',
							// ทะเลสาบสงขลา
							'STN1023','STN0578','STN0512','STN0511','STN0080','STN0103',
							'STN0080',
							//สงขลา
							'STN1027','STN1024','STN0710','STN0709','STN0708','STN0707',
							'STN0706','STN0292','STN0507',
							// สตูล
							'STN1032','STN1030','STN1028','STN0711','STN0293',
							'STN1031','STN1029',
							// พัทลุง
							'STN0082',
							'STN1022','STN0705','STN0704',

							'STN1026','STN1025','STN0581','STN0155','STN0134','STN0015',

							);
			//$ret.=htmlview(sg_tis620_to_utf8($page));
			$array=xml2array($page);
			$pattern = '/(' . implode('|', $stnImport) . ')/';
			$isOnlyImport=true;
			foreach ($array['system']['stnlist']['stn'] as $id => $item) {
				if ($isOnlyImport && !preg_match($pattern,$item['type'],$out)) {
					unset($array['system']['stnlist']['stn'][$id]);
					continue;
				}
				foreach ($item as $key=>$value)
					if (is_array($value) && empty($value))
						$array['system']['stnlist']['stn'][$id][$key]='';
			}
			//			$ret.='Error = '.curl_errno($ch);
			//			$ret.='Result '.($page===false?' False':($page===true?'True':'N/A')).'<br />';
			//			$ret.='Error='.curl_errno().'<br />';

			$tables = new Table();
			$tables->thead= array_keys(reset($array['system']['stnlist']['stn']));
			$tables->rows=$array['system']['stnlist']['stn'];

			$ret .= $tables->build();
			//$ret.='$Page of '.$url.' is <br />'.print_o($array['system']['stnlist']['stn'],'$stn').'<hr />';

			$dom = new DOMDocument;
			$dom->loadHTML( $page );

			$rows = array();
			foreach( $dom->getElementsByTagName( 'stn' ) as $tr ) {
				$cells = array();
				foreach( $tr as $td ) {
					$cells[] = $td->nodeValue;
				}
				$rows[] = array_map('trim',$cells);
			}
		}

		//$ret.=print_o($rows,'$rows');
		return $ret;
		$stations=array_slice($rows[32], 3);
		$today=date('Y-m-d');
		$yesterday=date('Y-m-d', time() - 60 * 60 * 24);
		for ($i=7; $i<=30;$i++) {
			$row=$rows[$i];
			$time=trim($row[0]);
			$time=str_replace('.', ':', $time);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[0],
												'time'=>$time,
												'level'=>$row[1],
												'flow'=>$row[2],
												);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[1],
												'time'=>$time,
												'level'=>$row[3],
												'flow'=>$row[4],
												);
			$flows[]=array(	'date'=>$yesterday,
												'station'=>$stations[2],
												'time'=>$time,
												'level'=>$row[5],
												'flow'=>$row[6],
												);

			$flows[]=array(	'date'=>$today,
												'station'=>$stations[0],
												'time'=>$time,
												'level'=>$row[7],
												'flow'=>$row[8],
												);
			$flows[]=array(	'date'=>$today,
												'station'=>$stations[1],
												'time'=>$time,
												'level'=>$row[9],
												'flow'=>$row[10],
												);
			$flows[]=array(	'date'=>$today,
												'station'=>$stations[2],
												'time'=>$time,
												'level'=>$row[11],
												'flow'=>$row[12],
												);
		}
		$result['flows']=$flows;
		$result['stations']=$stations;
		$result['rows']=$rows;

		return $result;
	}

}

/**
 * xml2array() will convert the given XML text to an array in the XML structure.
 * Link: http://www.bin-co.com/php/scripts/xml2array/
 * Arguments : $contents - The XML text
 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
 */
function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

	//Get the XML parser of PHP - PHP must have this module for the parser to work
	$parser = xml_parser_create('');
	//xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "TIS-620");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();

        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;

                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }

    return($xml_array);
}
?>