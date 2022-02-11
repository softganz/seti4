<?php
/**
 * flood_model class for Flood Management
 *
 * @package flood
 * @version 0.10
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-07-26
 * @modify 2011-10-17
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class flood_model {

	/**
	 * Photo url location
	 *
	 * @param String $camname
	 * @param String $photo
	 * @return String
	 *
	 */
	function photo_url($rs) {
		if (empty($rs->name) || empty($rs->photo) || empty($rs->atdate)) return NULL;
		$dateFolder = date('Y',$rs->atdate).'/'.date('m',$rs->atdate);
		$photoUrl = _FLOOD_PHOTO_URL.$dateFolder.'/'.$rs->name.'/'.$rs->photo;
		return $photoUrl;
	}

	/**
	 * Photo file location
	 *
	 * @param String $camname
	 * @param String $photo
	 * @return String
	 *
	 */
	function photo_loc($rs) {
		if (empty($rs->name) || empty($rs->photo) || empty($rs->atdate)) return NULL;
		$dateFolder = date('Y',$rs->atdate).'/'.date('m',$rs->atdate);
		$photoLoc = _FLOOD_PHOTO_FOLDER.$dateFolder.'/'.$rs->name.'/'.$rs->photo;
		return $photoLoc;
	}

	/**
	 * Thumbnail url
	 *
	 * @param String $camname
	 * @param String $photo
	 * @return String
	 *
	 */
	function thumb_url($rs) {
		if (empty($rs->name) || empty($rs->photo) || empty($rs->atdate)) return NULL;
		if (!file_exists(flood_model::thumb_loc($rs))) {
			return flood_model::photo_url($rs);
		}
		$dateFolder = date('Y',$rs->atdate).'/'.date('m',$rs->atdate).'/'.date('d',$rs->atdate);
		$thumbUrl = _FLOOD_THUMB_URL.$dateFolder.'/'.$rs->name.'-'.$rs->photo;
		return $thumbUrl;
	}

	/**
	 * Thumbnail file location
	 *
	 * @param String $camname
	 * @param String $photo
	 * @return String
	 *
	 */
	function thumb_loc($rs) {
		if (empty($rs->name) || empty($rs->photo) || empty($rs->atdate)) return NULL;
		$dateFolder = date('Y',$rs->atdate).'/'.date('m',$rs->atdate).'/'.date('d',$rs->atdate);
		$thumbLoc = _FLOOD_THUMB_FOLDER.$dateFolder.'/'.$rs->name.'-'.$rs->photo;
		return $thumbLoc;
	}

	/**
	 * Photo url location
	 *
	 * @param String $camname
	 * @param String $photo
	 * @return String
	 *
	 */
	function chatphoto_url($rs) {
		$photoUrl = _FLOOD_UPLOAD_URL.'photo/'.$rs->photo;
		return $photoUrl;
	}

	function get_station($station) {
		$stmt='SELECT * FROM %flood_station% WHERE `station`=:station LIMIT 1';
		$rs=mydb::select($stmt,':station',$station);
		return $rs;
	}

	function sensor_status($time,$sec=1800) {
		$status='stop';
		if (!$time) {
			$status='stop';
		} else if (sg_date($time,'U')<date('U')-$sec) {
			$status='stop';
		} else {
			$status='normal';
		}
		return $status;
	}

	function sensor_photo($station,$photo) {
		$photoUrl=$photo?_DOMAIN._URL.'/upload/sensor/'.$photo:_DOMAIN._URL.'file/flood/site/photonotyetupload.png';
		return $photoUrl;
	}

	function flag($waterlevel,$levelyellow,$levelred,$type='flag',$manualFlag=NULL) {
		if (is_string($waterlevel)) $waterlevel=(float) $waterlevel;
		if (is_string($levelyellow)) $levelyellow=(float) $levelyellow;
		if (is_string($levelred)) $levelred=(float) $levelred;

		$flags=array('green'=>'green','yellow'=>'yellow','red'=>'red');
		$texts=array('green'=>'ปกติ','yellow'=>'เฝ้าระวัง','red'=>'เตือนภัย');
		$status='green';
		if ($manualFlag) $status=$manualFlag;
		else if ($levelred && $waterlevel>$levelred) $status='red';
		else if ($levelyellow && $waterlevel>$levelyellow) $status='yellow';
		if ($type=='flag') {
			$result='<img class="flag'.($status?' flag--manual -'.$status:'').'" src="'._URL.'file/flood/site/flag-'.$flags[$status].'.jpg" width="32" />';
		} else {
			$result=$texts[$status];
		}
		return $result;
	}

	function rainavg($basin,$time=NULL) {
		if (!$time) $time=date('Y-m-d H:i:s');
		$timestamp=sg_date($time,'U');
		$rain=array();

		$dbs=mydb::select('SELECT * FROM %flood_station% WHERE `basin`=:basin ORDER BY `sorder` ASC',':basin',$basin);
		$startYesterday=date('Y-m-d 00:00:00',strtotime("-1 days",$timestamp));
		$toYesterday=date('Y-m-d 23:59:59',strtotime("-1 days",$timestamp));

		$startToday=date('Y-m-d 00:00:00',$timestamp);
		$toNow=date('Y-m-d H:i:s',$timestamp);

		$start3Hr=date('Y-m-d H:i:s',strtotime("-3 Hours",$timestamp));

		$start1Hr=date('Y-m-d H:i:s',strtotime("-1 Hours",$timestamp));

		$start15Min=date('Y-m-d H:i:s',strtotime("-30 Minutes",$timestamp));

		$startMonth=date('Y-m-01 00:00:00',$timestamp);
		$startYear=date('Y-01-01 00:00:00',$timestamp);

		foreach ($dbs->items as $rs) {
			// 15 Minute
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime ORDER BY `sid` DESC';
			$rainRs=mydb::select($stmt,':station',$rs->station,':startTime',$start15Min,':toTime',$toNow);
			$rain[$rs->station]['15min']=$rainRs->items[0]->value-$rainRs->items[$rainRs->_num_rows-1]->value;
			$data[$rs->station]['15min']['query']=mydb()->_query;
			$data[$rs->station]['15min']['items']=$rainRs->items;

			// 1 Hour
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime ORDER BY `sid` DESC';
			$rainRs=mydb::select($stmt,':station',$rs->station,':startTime',$start1Hr,':toTime',$toNow);
			$rain[$rs->station]['1hr']=$rainRs->items[0]->value-$rainRs->items[$rainRs->_num_rows-1]->value;
			$data[$rs->station]['1hr']['query']=mydb()->_query;
			$data[$rs->station]['1hr']['items']=$rainRs->items;

			// 3 Hour
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime ORDER BY `sid` DESC';
			$rainRs=mydb::select($stmt,':station',$rs->station,':startTime',$start3Hr,':toTime',$toNow);
			$rain[$rs->station]['3hr']=$rainRs->items[0]->value-$rainRs->items[$rainRs->_num_rows-1]->value;
			$data[$rs->station]['3hr']['query']=mydb()->_query;
			$data[$rs->station]['3hr']['items']=$rainRs->items;

			// Today
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime ORDER BY `sid` DESC';
			$rainRs=mydb::select($stmt,':station',$rs->station,':startTime',$startToday,':toTime',$toNow);
			$rain[$rs->station]['today']=$rainRs->items[0]->value-$rainRs->items[$rainRs->_num_rows-1]->value;
			$data[$rs->station]['today']['query']=mydb()->_query;
			$data[$rs->station]['today']['items']=$rainRs->items;

			// Yesterday
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value` FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime ORDER BY `sid` DESC';
			$rainRs=mydb::select($stmt,':station',$rs->station,':startTime',$startYesterday,':toTime',$toYesterday);
			$rain[$rs->station]['yesterday']=$rainRs->items[0]->value-$rainRs->items[$rainRs->_num_rows-1]->value;
			$data[$rs->station]['yesterday']['query']=mydb()->_query;
			$data[$rs->station]['yesterday']['items']=$rainRs->items;

			// ยกเลิกการคำนวณฝนเดือนนี้ กับปีนี้ เนื่องจากใช้เวลานานเกินในการดึงข้อมูล
			continue;

			// This month
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value`
							FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime
							ORDER BY `sid` LIMIT 1';
			$rain1=mydb::select($stmt,':station',$rs->station,':startTime',$startMonth,':toTime',$toNow);
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value`
							FROM %flood_sensor%
							WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime
							ORDER BY `sid` DESC LIMIT 1';
			$rain2=mydb::select($stmt,':station',$rs->station,':startTime',$startMonth,':toTime',$toNow);

			$rain[$rs->station]['thismonth']=$rain2->value-$rain1->value;
			$data[$rs->station]['thismonth']['query']=mydb()->_query;
			$data[$rs->station]['thismonth']['items'][]=$rain1;
			$data[$rs->station]['thismonth']['items'][]=$rain2;

			// This year
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value`
							FROM %flood_sensor% WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime
							ORDER BY `sid` LIMIT 1';
			$rain1=mydb::select($stmt,':station',$rs->station,':startTime',$startYear,':toTime',$toNow);
			$stmt='SELECT `sid`,`station`,`timeRec`,`sensorName`,`value`
							FROM %flood_sensor%
							WHERE `station`=:station AND `sensorName`="rain" AND `timeRec` BETWEEN :startTime AND :toTime
							ORDER BY `sid` DESC LIMIT 1';
			$rain2=mydb::select($stmt,':station',$rs->station,':startTime',$startYear,':toTime',$toNow);

			$rain[$rs->station]['thisyear']=$rain2->value-$rain1->value;
			$data[$rs->station]['thisyear']['query']=mydb()->_query;
			$data[$rs->station]['thisyear']['items'][]=$rain1;
			$data[$rs->station]['thisyear']['items'][]=$rain2;

		}
		//print_o($data,'$data',1);
		return array($rain,$data);
	}
}
?>