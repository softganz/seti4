<?php
/**
* sg utility class
*
* @package none
* @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
* @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
* @created 2009-08-26
* @modify 2009-08-26
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/

class SG {

	public static $number = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่','ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
	public static $numberScale = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');

	// arabic and thai number
	public static $arabicNumber = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	public static $thaiNumber = array('๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙');



	/**
	 * PHP Evaluate
	 *
	 * @param String $str
	 * @param String $prefix
	 * @param String $postfix
	 *
	 * @return String
	 */
	public static function eval_php($str=null,$prefix=NULL,$postfix=NULL) {
		if (!isset($str)) return false;
		ob_start();
		eval('?>'.$str);
		return $prefix.ob_get_clean().$postfix;
	}



	public static function title_encode($title) {
		$title=trim(preg_replace('/[ .]/','-',$title));
		$title=preg_replace('/[^0-9a-z\-'.chr(0xe80).'-'.chr(0xefe).']/i','',$title); // remove charactor not numeric,letter,thai charactor
		$title=preg_replace('/[-]{2,}/','-',$title);
		$title=rtrim($title,'-');
		return $title;
	}



	/**
	 * Get memory useage and peek in string
	 *
	 * @return String
	 */
	public static function memory_get_usage() {
		$usage=memory_get_usage();
		$peek=memory_get_peak_usage();
		$ret='<a href="#" title="current = '.number_format($usage,0).' bytes.">'.number_format($usage/1048576,3).'</a>';
		$ret.='/';
		$ret.='<a href="#" title="peek = '.number_format($peek,0).' bytes.">'.number_format($peek/1048576,3).'</a>';
		return $ret;
	}



	/**
	 * Extract name into name and lastname seperate by space
	 *
	 * @param String $name
	 * @return Array(name,lastname)
	 */
	public static function explode_name($sep=' ',$name=null) {
		$name=preg_replace('/['.$sep.']{2,}/',' ',$name);
		list($firstname)=explode($sep,$name);
		$firstname=trim($firstname);
		$lastname=trim(substr($name,strlen($firstname)+1));
		return array($firstname,$lastname);
	}


	/**
	 * Add tag Open Graph to head wrapper
	 *
	 * @param Object or Array $type
	 * @retuen null
	 */
	public static function add_opengraph($type) {
		head(
			'<meta property="og:title" content="'.str_replace('"','',strip_tags($type->title)).'">'._NL
			. '<meta property="og:type" content="'.$type->type.'">'._NL
			. '<meta property="og:url" content="'.cfg('domain').$type->url.'">'
		);
		if ($type->image) {
			$imgUrl=(preg_match('/^http\:\/\/|https\:\/\/|\/\//i',$type->image)?'':cfg('domain')).$type->image;
			head('<meta property="og:image" content="'.$imgUrl.'">');
			head('<link rel="image_src" href="'.$imgUrl.'">');
		}
		if ($type->description) head('<meta property="og:description" content="'.trim(str_replace(array('"','\'',"\r","\n",'  '),array('','',' ',' ',' '),strip_tags($type->description))).'">');
	}



	/**
	 * Sea Level
	 *
	 * @param Numeric $level
	 * @param Integer $digit
	 * @return String;
	 */
	public static function seaLevel($level,$digit=2,$ref_level=null) {
		if (is_null($level)) return 'ต่ำกว่าระดับ'.($ref_level?' '.$ref_level:'วัดได้');
		if ($level>0) $sign='+';
		else if ($level<0) $sign='-';
		else $sign='';
		$result=$sign.number_format(abs($level),$digit);
		return $result;
	}



	/**
	 * Credits: http://www.bitrepository.com
	 * URL: http://www.bitrepository.com/web-programming/php/simple-age-calculator.html
	 */
	public static function determine_age($birth_date) {
		list($birth_year, $birth_month, $birth_day) = explode('-', $birth_date);

		$now = time();
		$current_year = date('Y');

		$years_old = $current_year - $birth_year;

		return $years_old;
	}



	public static function arabic2Thai($input) {
		$input = strval($input);

		return str_replace(sg::$arabicNumber, sg::$thaiNumber, $input);
	}



	public static function thai2Arabic($input) {
		$input = strval($input);

		return str_replace(sg::$arabicNumber, sg::$thaiNumber, $input);
	}




	/**
	 * Add condition to where
	 *
	 * @param Array $where
	 * @param String $cond
	 * @param String $key
	 * @param Mixed $value
	 */
	static function add_condition($where=array(),$cond='',$key=NULL,$value=NULL) {
		if ($cond) $where['cond'][]=$cond;
		$args=func_get_args();
		for ($i=2; $i<count($args); $i=$i+2) {
			if (isset($args[$i])) $where['value'][$args[$i]]=isset($args[$i+1])?$args[$i+1]:NULL;
		}
		return $where;
	}



	/**
	 * Check spam word in array
	 *
	 * @param Array $arr
	 * @param Mixed $patterns
	 * @return String
	 */
	public static function is_spam_word($arr,$patterns=NULL) {
		if (!isset($patterns)) $patterns=cfg('spam.word');
		if (is_string($patterns)) $patterns=explode(',',$patterns);
		if (!is_array($patterns)) return false;
		$patterns_flattened = implode('|', $patterns);
		if (is_string($arr)) $arr=array($arr);
		foreach ($arr as $msg) {
			if (!is_string($msg)) continue;
			$msg=preg_replace('/[^A-Za-z0-9ก-ฮเ]*/','',$msg);
			if ( preg_match('/'. $patterns_flattened .'/i', $msg, $matches) )
			if ($matches) {
				$spam_word=$matches[0];
				break;
			}
		}
		return $spam_word;
	}



	/**
	 * Convert Thai date (5 ม.ค. 2556) to Y-m-d format
	 * @param String str
	 * @return String
	 */
	public static function convert_thai_date($str) {
		$thaiMonths=array('มกราคม'=>'01','กุมภาพันธ์'=>'02','มีนาคม'=>'03','เมษายน'=>'04','พฤษภาคม'=>'05','มิถุนายน'=>'06','กรกฎาคม'=>'07','สิงหาคม'=>'08','กันยายน'=>'09','ตุลาคม'=>'10','พฤศจิกายน'=>'11','ธันวาคม'=>'12','ม.ค.'=>'01','ก.พ.'=>'02','มี.ค.'=>'03','เม.ย.'=>'04','พ.ค.'=>'05','มิ.ย.'=>'06','ก.ค.'=>'07','ส.ค.'=>'08','ก.ย.'=>'09','ต.ค.'=>'10','พ.ย.'=>'11','ธ.ค.'=>'12');
		$result='';
		$str=preg_replace('/[ ]/',' ',$str);
		list($d,$m,$y)=explode(' ',$str);
		$y-=543;
		$result=sprintf('%04d',$y).'-'.$thaiMonths[$m].'-'.sprintf('%02d',$d);
		return $result;
	}



	public static function UTMtoGeog($east, $north, $utmZone) {
		// This is the lambda knot value in the reference
		$LngOrigin = Deg2Rad($utmZone * 6 - 183);

		// The following set of class constants define characteristics of the
		// ellipsoid, as defined my the WGS84 datum.  These values need to be
		// changed if a different dataum is used.

		$FalseNorth = 0;   // South or North?
		//if (lat < 0.) FalseNorth = 10000000.  // South or North?
		//else          FalseNorth = 0.

		$Ecc = 0.081819190842622;       // Eccentricity
		$EccSq = $Ecc * $Ecc;
		$Ecc2Sq = $EccSq / (1. - $EccSq);
		$Ecc2 = sqrt($Ecc2Sq);      // Secondary eccentricity
		$E1 = ( 1 - sqrt(1-$EccSq) ) / ( 1 + sqrt(1-$EccSq) );
		$E12 = $E1 * $E1;
		$E13 = $E12 * $E1;
		$E14 = $E13 * $E1;

		$SemiMajor = 6378137.0;         // Ellipsoidal semi-major axis (Meters)
		$FalseEast = 500000.0;          // UTM East bias (Meters)
		$ScaleFactor = 0.9996;          // Scale at natural origin

		// Calculate the Cassini projection parameters

		$M1 = ($north - $FalseNorth) / $ScaleFactor;
		$Mu1 = $M1 / ( $SemiMajor * (1 - $EccSq/4.0 - 3.0*$EccSq*$EccSq/64.0 - 5.0*$EccSq*$EccSq*$EccSq/256.0) );

		$Phi1 = $Mu1 + (3.0*$E1/2.0 - 27.0*$E13/32.0) * sin(2.0*$Mu1);
			+ (21.0*$E12/16.0 - 55.0*$E14/32.0)           * sin(4.0*$Mu1);
			+ (151.0*$E13/96.0)                          * sin(6.0*$Mu1);
			+ (1097.0*$E14/512.0)                        * sin(8.0*$Mu1);

		$sin2phi1 = sin($Phi1) * sin($Phi1);
		$Rho1 = ($SemiMajor * (1.0-$EccSq) ) / pow(1.0-$EccSq*$sin2phi1,1.5);
		$Nu1 = $SemiMajor / sqrt(1.0-$EccSq*$sin2phi1);

		// Compute parameters as defined in the POSC specification.  T, C and D

		$T1 = tan($Phi1) * tan($Phi1);
		$T12 = $T1 * $T1;
		$C1 = $Ecc2Sq * cos($Phi1) * cos($Phi1);
		$C12 = $C1 * $C1;
		$D  = ($east - $FalseEast) / ($ScaleFactor * $Nu1);
		$D2 = $D * $D;
		$D3 = $D2 * $D;
		$D4 = $D3 * $D;
		$D5 = $D4 * $D;
		$D6 = $D5 * $D;

		// Compute the Latitude and Longitude and convert to degrees
		$lat = $Phi1 - $Nu1*tan($Phi1)/$Rho1 * ( $D2/2.0 - (5.0 + 3.0*$T1 + 10.0*$C1 - 4.0*$C12 - 9.0*$Ecc2Sq)*$D4/24.0 + (61.0 + 90.0*$T1 + 298.0*$C1 + 45.0*$T12 - 252.0*$Ecc2Sq - 3.0*$C12)*$D6/720.0 );

		$lat = Rad2Deg($lat);

		$lon = $LngOrigin + ($D - (1.0 + 2.0*$T1 + $C1)*$D3/6.0 + (5.0 - 2.0*$C1 + 28.0*$T1 - 3.0*$C12 + 8.0*$Ecc2Sq + 24.0*$T12)*$D5/120.0) / cos($Phi1);

		$lon = Rad2Deg($lon);

		return array('lat'=>$lat, 'lon'=>$lon);
	}

	/**
	* Convert UTM Coordinates to Geographic
	* Original version: http://www.uwgb.edu/dutchs/usefuldata/ConvertUTMNoOZ.HTM
	* @param Integer $Easting
	* @param Integer $Northing
	* @param Integer $UtmZone
	* @param Boolean $SouthofEquator
	* @return Array
	*/
	function UTMtoGeog_v1($Easting,$Northing,$UtmZone,$SouthofEquator=false) {
		//Declarations
		//Symbols as used in USGS PP 1395: Map Projections - A Working Manual
		$k0 = 0.9996;//scale on central meridian
		$a = 6378137.0;//equatorial radius, meters.
		$f = 1/298.2572236;//polar flattening.
		$b = $a*(1-$f);//polar axis.
		$e = sqrt(1 - $b*$b/$a*$a);//eccentricity
		$drad = pi()/180;//Convert degrees to radians)
		$phi = 0;//latitude (north +, south -), but uses phi in reference
		$e0 = $e/sqrt(1 - $e*$e);//e prime in reference

		$lng = 0;//Longitude (e = +, w = -)
		$lng0 = 0;//longitude of central meridian
		$lngd = 0;//longitude in degrees
		$M = 0;//M requires calculation
		$x = 0;//x coordinate
		$y = 0;//y coordinate
		$k = 1;//local scale
		$zcm = 0;//zone central meridian
		//End declarations


		//Convert UTM Coordinates to Geographic

		$k0 = 0.9996;//scale on central meridian
		$b = $a*(1-$f);//polar axis.
		$e = sqrt(1 - ($b/$a)*($b/$a));//eccentricity
		$e0 = $e/sqrt(1 - $e*$e);//Called e prime in reference
		$esq =(1 - ($b/$a)*($b/$a));//e squared for use in expansions
		$e0sq =$e*$e/(1-$e*$e);// e0 squared - always even powers
		$x = $Easting;

		/*
		if ($x<160000 || $x>840000)
			echo "($x,$y) Outside permissible range of easting values \n Results may be unreliable \n Use with caution\n<br />";
		$y = $Northing;
		if ($y<0)
			echo "Negative values not allowed \n Results may be unreliable \n Use with caution\n";
		if ($y>10000000)
			echo "Northing may not exceed 10,000,000 \n Results may be unreliable \n Use with caution\n";
		*/

		$zcm =3 + 6*($UtmZone-1) - 180;//Central meridian of zone
		$e1 =(1 - sqrt(1 - $e*$e))/(1 + sqrt(1 - $e*$e));//Called e1 in USGS PP 1395 also
		$M0 =0;//In case origin other than zero lat - not needed for standard UTM
		$M =$M0 + $y/$k0;//Arc length along standard meridian.
		if ($SouthofEquator === true)
		$M=$M0+($y-10000000)/$k;
		$mu =$M/($a*(1 - $esq*(1/4 + $esq*(3/64 + 5*$esq/256))));
		$phi1 =$mu + $e1*(3/2 - 27*$e1*$e1/32)*sin(2*$mu) + $e1*$e1*(21/16 -55*$e1*$e1/32)*sin(4*$mu);//Footprint Latitude
		$phi1 =$phi1 + $e1*$e1*$e1*(sin(6*$mu)*151/96 + $e1*sin(8*$mu)*1097/512);
		$C1 =$e0sq*pow(cos($phi1),2);
		$T1 =pow(tan($phi1),2);
		$N1 =$a/sqrt(1-pow($e*sin($phi1),2));
		$R1 =$N1*(1-$e*$e)/(1-pow($e*sin($phi1),2));
		$D =($x-500000)/($N1*$k0);
		$phi =($D*$D)*(1/2 - $D*$D*(5 + 3*$T1 + 10*$C1 - 4*$C1*$C1 - 9*$e0sq)/24);
		$phi =$phi + pow($D,6)*(61 + 90*$T1 + 298*$C1 + 45*$T1*$T1 -252*$e0sq - 3*$C1*$C1)/720;
		$phi =$phi1 - ($N1*tan($phi1)/$R1)*$phi;
		//Longitude
		$lng =$D*(1 + $D*$D*((-1 -2*$T1 -$C1)/6 + $D*$D*(5 - 2*$C1 + 28*$T1 - 3*$C1*$C1 +8*$e0sq + 24*$T1*$T1)/120))/cos($phi1);
		$lngd = $zcm+$lng/$drad;

		return array(floor(1000000*$phi/$drad)/1000000,floor(1000000*$lngd)/1000000); //Latitude,Longitude
	}
} /* End of class sg */













class BigData {
	var $keyname;
	var $keyid;
	var $_error;
	var $_query;
	function setKey($keyName,$keyId) {
		$this->keyname=$keyName;
		$this->keyid=$keyId;
	}

	public static function addField($fldName,$fldData,$fldType='string',$keyName=NULL,$keyId=NULL) {
		$data = [
			'keyname' => isset($keyName)?$keyName:$this->keyname,
			'keyid' => isset($keyId)?$keyId:$this->keyid,
			'fldtype' => $fldType,
			'fldname' => $fldName,
			'flddata' => $fldData,
			'created' => date('U'),
			'ucreated' => \SG\getFirst(i()->uid,'func.NULL'),
		];

		mydb::query(
			'INSERT INTO %bigdata% SET `keyname`=:keyname, `keyid`=:keyid, `fldname`=:fldname, `fldtype`=:fldtype, `flddata`=:flddata, `created`=:created, `ucreated`=:ucreated',
			$data
		);

		//$this->_error=mydb()->_error;
		//$this->_query=mydb()->_query;
		$bigid = mydb()->_error ? NULL : mydb()->insert_id;
		//echo mydb()->_query.'<br />';
		return $bigid;
	}

	public static function removeField($fldName,$keyName=NULL,$keyId=NULL) {
		$stmt='DELETE FROM %bigdata% WHERE `keyname`=:keyname AND `keyid`=:keyid AND `fldname`=:fldname';
		mydb::query($stmt,':keyname',$keyName, ':keyid',$keyId, ':fldname',$fldName);
		//echo mydb()->_query;
	}

	public static function getField($fldName,$keyName=NULL,$keyId=NULL) {
		$stmt='SELECT * FROM %bigdata% WHERE `keyname`=:keyname'.($keyId=='*' ? '' : ' AND `keyid`=:keyid').($fldName=='*' ? '':' AND `fldname`=:fldname').' ORDER BY `bigid` ASC';
		$dbs=mydb::select($stmt,':keyname',$keyName, ':keyid',$keyId, ':fldname',$fldName);
		//echo mydb()->_query;
		return $dbs->items;
	}

	public static function get($conditions) {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['count' => 0, 'items' => []];

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$id = $conditions;
			$conditions = (Object) ['id' => $id];
		}

		$result = (Object) [
			'id' => NULL,
			'data' => (Object) [],
			'info' => (Object) [],
		];

		mydb::where('b.`bigId` = :bigId', ':bigId', $conditions->id);

		$result->info = mydb::select(
			'SELECT `bigId` `autoId`, `keyName`, `keyId`, `fldName` `field`
			, `fldType` `type`, `fldRef` `ref`, `fldData` `data`
			, `created`, `ucreated` `ownerId`, `modified`, `umodified` `modifyBy`
			FROM %bigdata% b
			%WHERE%
			LIMIT 1'
		);

		$result->id = $result->info->autoId;
		if ($result->info->type === 'JSON') $result->data = \SG\json_decode($result->info->data);
		$result->info = mydb::clearProp($result->info);
		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, start: 0, items: 50, order: "b.`bigId`", sort: "DESC"}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['count' => 0, 'items' => []];

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$id = $conditions;
			$conditions = (Object) ['id' => $id];
		}

		if ($debug) debugMsg($conditions, '$conditions');

		if ($conditions->keyName) mydb::where('b.`keyName` = :keyName', ':keyName', $conditions->keyName);
		if ($conditions->field) mydb::where('b.`fldName` = :fldName', ':fldName', $conditions->field);
		if ($conditions->ref) mydb::where('b.`fldRef` = :fldRef', ':fldRef', $conditions->ref);

		mydb::value('$ORDER$', 'ORDER BY '.$options->order);
		mydb::value('$SORT$', $options->sort);

		$result->items = mydb::select(
			'SELECT `bigId` `autoId`, `keyName`, `keyId`, `fldName` `field`
			, `fldType` `type`, `fldRef` `ref`, `fldData` `data`
			, `created`, `ucreated` `ownerId`, `modified`, `umodified` `modifyBy`
			FROM %bigdata% b
			%WHERE%
			$ORDER$ $SORT$'
		)->items;

		$result->count = count($result->items);
		return $result;
	}

	public static function Add($data = [], $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [];

		if (is_string($data) && preg_match('/^{/',$data)) {
			$data = \SG\json_decode($data);
		} else if (is_object($data)) {
			// Do nothing
		} else if (is_array($data)) {
			$data = (Object) $data;
		}

		if ($debug) debugMsg($data, '$data');

		$data->autoId = \SG\getFirst($data->autoId);
		$data->keyName = \SG\getFirst($data->keyName);
		$data->keyId = \SG\getFirst($data->keyId);
		$data->field = \SG\getFirst($data->field);
		$data->type = \SG\getFirst($data->type);
		$data->ref = \SG\getFirst($data->ref);
		if ($data->type === 'JSON') {
			$data->data = \SG\json_encode($data->data);
		}	else {
			$data->data = \SG\getFirst($data->data);
		}
		$data->created = $data->modified = date('U');
		$data->owner = $data->umodified = i()->uid;


		mydb::query(
			'INSERT INTO %bigdata%
			(`bigid`, `keyname`, `keyid`, `fldname`, `fldref`, `fldtype`, `flddata`, `created`, `ucreated`)
			VALUES
			(:autoId, :keyName, :keyId, :field, :ref, :type, :data, :created, :owner)
			ON DUPLICATE KEY UPDATE
			`fldRef` = :ref
			, `fldData` = :data
			, `modified` = :modified
			, umodified = :umodified
			',
			$data
		);
		if ($debug) debugMsg(mydb()->_query);

		$result->data = $data;
		return $result;
	}

	public static function updateKeyId($conditions = []) {
		mydb::query(
			'UPDATE %bigdata% SET `keyId` = :keyId WHERE `bigId` = :autoId LIMIT 1',
			$conditions
		);
	}
}

?>