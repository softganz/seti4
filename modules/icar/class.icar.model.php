<?php
/**
 * icar_model class for car cost management
 *
 * @package icar
 * @version 0.00a
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2012-11-18
 * @modify 2012-11-25
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

class icar_model {
	/**
	 * Get information by id
	 *
	 * @param Integer $id
	 * @return Record Set
	 */
	function get_by_id($id,$updatecost=false) {
		$stmt='SELECT
						  t.`title` `carname`
						, t.`uid`
						, t.`title`
						, i.*
						, ct.`name` `cartypeName`
						, s.`shopname`
						, b.`name` `brandname`
						, p.`pshopid`
						, IFNULL(p.`name`,"ไม่มีผู้ร่วมทุน") `partnername`
						, p.`share` `pshare`
						, (SELECT UPPER(`membership`) FROM %icarusr% WHERE `shopid` = i.`shopid` AND `uid` = :uid) `iam`
						FROM %icar% i
							LEFT JOIN %topic% t USING(tpid)
							LEFT JOIN %tag% b ON b.`tid` = i.`brand`
							LEFT JOIN %icarshop% s ON i.`shopid` = s.`shopid`
							LEFT JOIN %icarpartner% p USING(partner)
							LEFT JOIN %tag% ct ON ct.`taggroup` = "icar:cartype" AND i.`cartype` = ct.`catid`
						WHERE i.`tpid` = :tpid LIMIT 1';
		$rs = mydb::select($stmt,':tpid',$id, ':uid', i()->uid);

		if ($rs->_num_rows) {
			$rs->costcalculate=0;
			$rs->interest=0;
			$rs->notcost=0;
			$rs->photo = mydb::select('SELECT * FROM %topic_files% WHERE `tpid`='.$rs->tpid.' AND `cid`=0 AND `type`="photo" ORDER BY fid');
			foreach ($rs->photo->items as $key=>$photo) $rs->photo->items[$key]=object_merge($rs->photo->items[$key],model::get_photo_property($photo->file));
			// Get cost transaction
			$stmt='SELECT c.*, cid.name costname, cid.taggroup, cid.process
						FROM %icarcost% c
							LEFT JOIN %tag% cid ON cid.tid=c.costcode
						WHERE `tpid`=:tpid
						ORDER BY `itemdate` ASC, `costid` ASC';
			$rs->tr=mydb::select($stmt,':tpid',$rs->tpid)->items;

			$notcost = $costtotal = $saledownpaid = $financeprice = $rcv = $exp = 0;
			$saledate=$rs->saledate?$rs->saledate:date('Y-m-d');
			
			foreach ($rs->tr as $irs) {
				$irs->interestday=0;
				$irs->interestamt=0;
				if ($irs->interest>0) {
					$irs->interestday=(sg_date($saledate,'U')-sg_date($irs->itemdate,'U'))/(24*60*60)+1;
					$irs->interestamt=round(($irs->interestday*$irs->amt*$irs->interest)/(30*100));
				}
				$interesttotal+=$irs->interestamt;
				if ($irs->process==2) $notcost+=$irs->amt;
				if ($irs->taggroup=='icar:tr:cost') $costtotal+=$irs->amt;
				if ($irs->taggroup=='icar:tr:down') $saledownpaid+=$irs->amt;
				if ($irs->taggroup=='icar:tr:finance') $financeprice+=$irs->amt;
				if ($irs->taggroup=='icar:tr:rcv') $rcv+=$irs->amt;
				if ($irs->taggroup=='icar:tr:exp') $exp+=$irs->amt;
			}
			$rs->costcalculate=$costtotal;
			$rs->interest=$interesttotal;
			$rs->notcost=$notcost;

			// Update cost to database
			if ($updatecost) {
				$update->costprice=$rs->costprice=$rs->costcalculate;
				$update->saledownpaid=$rs->saledownpaid=$saledownpaid;
				$update->financeprice=$rs->financeprice=$financeprice;
				$update->rcvtransfer=$rs->rcvtransfer=$rcv;
				$update->paytransfer=$rs->paytransfer=$exp;

				$stmt='UPDATE %icar% SET `costprice`=:costprice, `saledownpaid`=:saledownpaid,
								`financeprice`=:financeprice, `rcvtransfer`=:rcvtransfer, `paytransfer`=:paytransfer
								WHERE `tpid`=:tpid LIMIT 1';
				mydb::query($stmt,':tpid',$id,$update);
			}
		}
		return $rs;
	}

	/**
	 * Get shop information
	 *
	 * @param Integer $id
	 * @return Record Set
	 */
	function get_shop($id) {
		$stmt = 'SELECT *
						, (SELECT UPPER(`membership`) FROM %icarusr% WHERE `shopid` = s.`shopid` AND `uid` = :uid) `iam`
						FROM %icarshop% s
						WHERE s.`shopid` = :shopid
						LIMIT 1';

		$rs = mydb::select($stmt,':shopid',$id, ':uid', i()->uid);
		mydb::clearprop($rs);
		return $rs;
	}

	/**
	 * Get next refno
	 *
	 * @param Integer $shopid
	 * @return String
	 */
	function get_next_refno($shopid) {
		$prefix=sg_date('ปป').'/';
		$lastno=mydb::select('SELECT max(`refno`) lastno FROM %icar% WHERE `shopid`=:shopid AND LEFT(refno,'.strlen($prefix).')=:prefix LIMIT 1',':shopid',$shopid,':prefix',$prefix)->lastno;
		$lastno_prefix=substr($lastno,0,strlen($prefix));
		$lastno=substr($lastno,strlen($prefix));
		$refno=$prefix.sprintf('%05d',$lastno+1);
		return $refno;
	}

	/**
	 * Get category
	 *
	 * @param String $cat_group
	 * @param Array
	 */
	function category($taggroup,$shopid=NULL,$id=NULL,$options = '{}') {
		$defaults = '{debug: false, key: "id"}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		//debugMsg($options,'$options');

		$defaultValue=NULL;
		$result=array();
		switch ($taggroup) {
			case 'partner' :
				if ($shopid && $id) $result=mydb::select('SELECT `name` FROM %icarpartner% WHERE `partner`=:partner LIMIT 1',':partner',$id)->name;
				else $dbs=mydb::select('SELECT `partner` id, `name`, 0 `isdefault` FROM %icarpartner% WHERE `shopid`=:shopid ORDER BY `name` ASC',':shopid',$shopid);
				break;
			default :
//			echo $taggroup;
				if (is_numeric($taggroup)) {
					$result=mydb::select('SELECT `name` FROM %tag% WHERE `tid`=:tid LIMIT 1',':tid',$taggroup)->name;
				} else {
					$dbs=mydb::select('SELECT `tid` id, `catid`, `name`, `isdefault` FROM %tag% WHERE `taggroup` LIKE :taggroup AND (`shopid` IS NULL'.($shopid?' OR `shopid`="'.$shopid.'"':'').') ORDER BY CONVERT(`name` USING tis620) ASC',':taggroup',$taggroup);
				}
				//debugMsg($dbs,'$dbs');
				break;
		}
		if ($dbs) {
			foreach ($dbs->items as $rs) {
				$keyValue = ''.($rs->{$options->key});
				$result[$keyValue]=$rs->name;
				if (!$defaultValue && $rs->isdefault) $defaultValue=$rs->id;
			}
			//$result['_query'] = mydb()->_query;
			if ($option=='default') $result=$defaultValue;
		}
		//debugMsg($result,'$result');
		return $result;
	}

	/**
	 * Get shop member
	 *
	 * @param String $membership
	 * @param Integer $shopid
	 * @param Integer $uid
	 * @return Mixed Boolean/Array
	 */
	function is_shop_member($membership,$shopid,$uid=NULL) {
	static $items=array();
		if (!isset($uid)) $uid=i()->uid;
		if (!isset($items[$shopid])) {
			$stmt='SELECT * FROM %icarusr% WHERE `shopid`=:shopid';
			$dbs=mydb::select($stmt,':shopid',$shopid);
			$items[$shopid]=array();
			foreach ($dbs->items as $rs) $items[$shopid][$rs->membership][$rs->uid]=$rs->uid;
		}
		return $membership&&$shopid?array_key_exists($uid,$items[$shopid][$membership]):$items;
	}

	function is_partner_of($carInfo) {
		if (empty($carInfo->pshopid)) return false;
		$myshop = $carInfo->pshare && (icar_model::is_shop_member('owner',$carInfo->pshopid) || icar_model::is_shop_member('officer',$carInfo->pshopid));
		//echo '<br /><br />MyShop '.($myshop?' Yes':'No');

		//print_o($carInfo,'$carInfo',1);
		return $myshop;
	}

	/**
	 * Get shop information of current user or specify user
	 *
	 * @param Integer $uid
	 * @return Record Set
	 */
	function get_my_shop($uid=NULL) {
	static $items=array();
		$rs=array();
		if (!isset($uid)) $uid=i()->uid;
		if (empty($uid)) ; // Do nothing
		else if (array_key_exists($uid,$items)) $rs=$items[$uid];
		else {
			$stmt='SELECT * FROM %icarusr% u LEFT JOIN %icarshop% s USING(shopid) WHERE uid=:uid LIMIT 1';
			$items[$uid]=$rs=mydb::select($stmt,':uid',$uid);
			if ($rs->_numrows) $items[$uid]=$rs;
		}
		return $rs;
	}

	/**
	 * Update car title
	 *
	 * @param Integer $tpid
	 */
	function update_car_title($tpid) {
		mydb::query('UPDATE %topic% SET `title`=(SELECT CONCAT(`name`," ",model) FROM %icar% c LEFT JOIN %tag% m ON m.tid=c.brand where `tpid`=:tpid LIMIT 1) WHERE `tpid`=:tpid LIMIT 1 ',':tpid',$tpid);
	}

}
?>
