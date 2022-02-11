<?php
/**
* Model Name
*
* @param Object $data
* @return Object
*/

function r_project_join_save($data, $options='{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (empty($data->doid)) return NULL;

	$data->uid = i()->uid;
	if (empty($data->regtype)) $data->regtype = 'Register';
	if (empty($data->jointype)) $data->jointype = NULL;
	if (empty($data->joingroup)) $data->joingroup = NULL;
	if (empty($data->foodtype)) $data->foodtype = NULL;
	if (empty($data->tripby)) $data->tripby = NULL;
	if (empty($data->rest)) $data->rest = NULL;
	if (empty($data->withdrawrest)) $data->withdrawrest = 1;
	if (empty($data->refcode)) $data->refcode = strtoupper(__r_project_join_save_getToken(8));
	if (empty($data->hotelmate)) {
		$data->hotelwithpsnid = NULL;
	}
	$data->created = date('U');

	$tripBy = array();
	if (is_array($data->tripby)) $tripBy = $tripBy + $data->tripby;
	if (is_array($data->tripgroup)) $tripBy = $tripBy + $data->tripgroup;
	$data->triplist = implode(',',$tripBy);

	$data->busprice = sg_strip_money($data->busprice);
	$data->airprice = sg_strip_money($data->airprice);
	$data->tripotherprice = sg_strip_money($data->tripotherprice);
	$data->taxiprice = sg_strip_money($data->taxiprice);
	$data->trainprice = sg_strip_money($data->trainprice);
	$data->rentprice = sg_strip_money($data->rentprice);
	$data->hotelprice = sg_strip_money($data->hotelprice);
	$data->hotelnight = sg_strip_money($data->hotelnight);
	if ($data->hotelothername) $data->hotelname = $data->hotelothername;
	$data->remark = strip_tags($data->remark);

	$information = NULL;
	if ($data->airgoline) $information->airgoline = $data->airgoline;
	if ($data->airgofrom) $information->airgofrom = $data->airgofrom;
	if ($data->airgoto) $information->airgoto = $data->airgoto;
	if ($data->airretline) $information->airretline = $data->airretline;
	if ($data->airretfrom) $information->airretfrom = $data->airretfrom;
	if ($data->airretto) $information->airretto = $data->airretto;

	$data->information = sg_json_encode($information);

	$stmt = 'INSERT INTO %org_dos%
					( `psnid`, `doid`, `uid`, `regtype`, `jointype`, `joingroup`, `foodtype`, `tripby`, `rest`, `withdrawrest`, `refcode`, `information`, `created` )
					VALUES
					( :psnid, :doid, :uid, :regtype, ":jointype", :joingroup, :foodtype, :triplist, :rest, :withdrawrest, :refcode, :information, :created )
					ON DUPLICATE KEY UPDATE
						`psnid` = :psnid
						, `regtype` = :regtype
						, `jointype` = ":jointype"
						, `joingroup` = :joingroup
						, `foodtype` = :foodtype
						, `tripby` = :triplist
						, `rest` = :rest
						, `withdrawrest` = :withdrawrest
						, `refcode` = :refcode
						, `information` = :information
					';

	mydb::query($stmt, $data);

	$result->_query[] = mydb()->_query;

	// Save some data to project_tr formid = project.join
	// Field to save : orgname orgtype position tripother
	$oldJoinInfo = R::Model('project.join.get', array('psnid' => $data->psnid, 'doid' => $data->doid));

	//debugMsg($oldJoinInfo, '$oldJoinInfo');

	// Clear คนที่เคยพักกับฉัน
	$stmt = 'UPDATE %project_tr% SET `text4` = NULL, `text3` = NULL WHERE `tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `calid` = :calid AND `text4` = :psnid';
	mydb::query($stmt, $data);
	$result->_query['Clear คนที่พักเคยกับฉัน'] = mydb()->_query;

	// Clear คนที่ฉันเคยพักด้วย
	if ($oldJoinInfo->hotelwithpsnid) {
		$stmt = 'UPDATE %project_tr% SET `text4` = NULL, `text3` = NULL WHERE `tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `calid` = :calid AND `text4` = :hotelwithpsnid';
		mydb::query($stmt, $oldJoinInfo);
		$result->_query['Clear คนที่ฉันเคยพักด้วย'] = mydb()->_query;

	}


	$data->orgtrid = $oldJoinInfo->orgtrid;
	if (empty($data->orgtype)) $data->orgtype = NULL;
	$stmt = 'INSERT INTO %project_tr%
					( `trid`, `tpid`, `calid`, `uid`
					, `refid`, `refcode`, `formid`, `part`
					, `detail1`, `detail2`, `detail3`, `detail4`
					, `text1`, `text8`
					, `text2`, `text3`
					, `text4`
					, `text5`
					, `text6` , `text7`
					, `num1`, `num2`, `num3`, `num4`, `num5`
					, `num8`, `num9`
					, `num6`, `num7`, `num10`
					, `text10`
					, `created` )
					VALUES
					( :orgtrid, :tpid, :calid, :uid
					, :doid, :psnid, "join", "register"
					, :orgname, :orgtype, :position, :tripotherby
					, :carregist, :carregprov
					, :hotelname, :hotelmate
					, :hotelwithpsnid
					, :carwithname
					, :rentregist, :rentpassenger
					, :busprice, :airprice, :tripotherprice, :taxiprice, :trainprice, :rentprice, :tripotherprice
					, :hotelprice, :hotelnight, :localprice
					, :remark
					, :created )
					ON DUPLICATE KEY UPDATE
					  `detail1` = :orgname
					, `detail2` = :orgtype
					, `detail3` = :position
					, `detail4` = :tripotherby
					, `text1` = :carregist
					, `text8` = :carregprov
					, `text2` = :hotelname
					, `text3` = :hotelmate
					, `text4` = :hotelwithpsnid
					, `text5` = :carwithname
					, `text6` = :rentregist
					, `text7` = :rentpassenger
					, `num1` = :busprice
					, `num2` = :airprice
					, `num3` = :tripotherprice
					, `num4` = :taxiprice
					, `num5` = :trainprice
					, `num6` = :hotelprice
					, `num7` = :hotelnight
					, `num8` = :rentprice
					, `num9` = :tripotherprice
					, `num10` = :localprice
					, `text10` = :remark
					, `modified` = :created
					, `modifyby` = :uid
					';
	mydb::query($stmt, $data);
	$result->_query[] = mydb()->_query;




	if ($data->hotelwithpsnid) {
		// Clear คนที่ฉันจะพักด้วย
		$stmt = 'UPDATE %project_tr% SET `text4` = NULL, `text3` = NULL WHERE `tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `calid` = :calid AND `refcode` != :psnid AND `text4` = :hotelwithpsnid LIMIT 1';
		mydb::query($stmt, $data);
		$result->_query['Clear คนที่ฉันจะพักด้วย'] = mydb()->_query;

		// Add คนที่ฉันจะพักด้วย
		$stmt = 'UPDATE %project_tr% SET `text4` = :psnid, `text3` = :fullname WHERE `tpid` = :tpid AND `formid` = "join" AND `part` = "register" AND `calid` = :calid AND `refcode` = :hotelwithpsnid LIMIT 1';
		mydb::query($stmt, $data, ':fullname', $data->prename.' '.$data->firstname.' '.$data->lastname);
		$result->_query['Add คนที่ฉันจะพักด้วย'] = mydb()->_query;
	}


	return $result;
}

if (!function_exists('random_int')) {
    function random_int($min, $max) {
        if (!function_exists('mcrypt_create_iv')) {
            trigger_error(
                'mcrypt must be loaded for random_int to work', 
                E_USER_WARNING
            );
            return null;
        }
        
        if (!is_int($min) || !is_int($max)) {
            trigger_error('$min and $max must be integer values', E_USER_NOTICE);
            $min = (int)$min;
            $max = (int)$max;
        }
        
        if ($min > $max) {
            trigger_error('$max can\'t be lesser than $min', E_USER_WARNING);
            return null;
        }
        
        $range = $counter = $max - $min;
        $bits = 1;
        
        while ($counter >>= 1) {
            ++$bits;
        }
        
        $bytes = (int)max(ceil($bits/8), 1);
        $bitmask = pow(2, $bits) - 1;

        if ($bitmask >= PHP_INT_MAX) {
            $bitmask = PHP_INT_MAX;
        }

        do {
            $result = hexdec(
                bin2hex(
                    mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM)
                )
            ) & $bitmask;
        } while ($result > $range);

        return $result + $min;
    }
}

function __r_project_join_save_getToken($length){
     $token = "";
     $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
     $codeAlphabet.= "0123456789";
     $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[random_int(0, $max-1)];
    }

    return $token;
}
?>