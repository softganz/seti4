<?php
/**
 * org.init class for init module
 *
 * @package org
 * @version 0.00
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2001-01-01
 * @modify 2017-12-19
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

cfg('org.version','2.10');
cfg('org.release','17.12.19');

define('_ORG_TITLE','ระบบบริหารองค์กร รุ่น '.cfg('org.version'));
define('_QTGROUP_GOGREEN',5);
define('_INBOARD_CODE', 1);

menu('org/gogreen','Go Green','org','__controller',1,true,'static');
menu('org/admin','Organization Administrator','org','__controller',1,'administrator orgs','static');
menu('org','Organization Home','org','__controller',1,'access org content','static');

cfg('org.permission', 'administrator orgs,create org content,edit own org content,access org content');



class org_model {

	/**
	* Check right to edit data
	*
	* @param Integer $orgid
	* @return boolean
	*/
	public static function is_edit($orgid, $uid = NULL) {
		static $myorg;
		if (!$myorg) $myorg = org_model::get_my_org();
		$isEdit = user_access('administrator orgs')
						|| (user_access('create org content') && in_array($orgid,explode(',',$myorg))
						|| (isset($uid) && i()->ok && $uid == i()->uid) );
		return $isEdit;
	}

	/**
	* Get my organization
	*
	* @return String
	*/
	public static function get_my_org($uid = NULL) {
		if (empty($uid)) $uid=i()->uid;
		if (empty($uid)) return;

		$myOrg = mydb::select('SELECT `orgid` FROM %org_officer% WHERE `uid`=:uid',':uid',$uid)->lists->text;
		return $myOrg;
	}

	/**
	 * Get member information
	 *
	 * @param String $name or Integer $mid
	 * @param String $lastname
	 * @return Object
	 */
	public static function get_member($name='',$lname='') {
		$stmt='SELECT p.`uid`, p.`psnid`, p.`prename`, p.`name`, p.`lname`, p.`cid`,
			p.`nickname`, p.`birth`,
			p.`phone`, p.`email`, p.`website`, p.`aptitude`,
			p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`,
			IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname,
			IFNULL(codist.`distname`,p.`t_ampur`) distname,
			IFNULL(cop.`provname`,p.`t_changwat`) provname,
			p.`zip`,
			m.`joindate`
		FROM %org_mjoin% m
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`, p.`ampur`, p.`tambon`)
			LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`, p.`ampur`, p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
			LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
			LEFT JOIN %db_org% o USING(`orgid`) ';
		if (is_numeric($name)) {
			$stmt.='WHERE m.`psnid`='.addslashes($name).' ';
		} else if (is_string($name) && is_string($lname)) {
			$stmt.='WHERE p.`name`="'.$name.'" AND p.`lname`="'.$lname.'" ';
		}
		$stmt.='LIMIT 1';
		$rs=mydb::select($stmt);

		if ($rs->_num_rows) {
			$stmt='SELECT m.*, o.`name` FROM %org_morg% m LEFT JOIN %db_org% o USING(`orgid`) WHERE `psnid`=:psnid';
			$dbs=mydb::select($stmt,':psnid',$rs->psnid);
			$rs->orgs=$dbs->items;
		}
		return $rs;
	}

	/**
	* Get person join of organization
	*
	* @param Intefer $psnid
	* @param Integer $orgid seperate by ,
	* @return Object Record Set
	*/
	public static function get_memberjoin($psnid,$orgid=NULL) {
		if (empty($orgid)) $orgid=org_model::get_my_org();
		if (empty($psnid) || empty($orgid)) return;
		$stmt='SELECT d.* , t.`name` as issue
					FROM %org_dos% do
						LEFT JOIN %org_doings% d USING(`doid`)
						LEFT JOIN %tag% t ON t.`tid`=d.`issue`
					WHERE do.`psnid`=:psnid AND do.`isjoin`=1 AND d.`orgid` IN (:myorg) ORDER BY d.`atdate` DESC';
		$dbs=mydb::select($stmt, ':psnid',$psnid, ':myorg', 'SET:'.$orgid);
		return $dbs;
	}

	/**
	* Search person who join org
	*
	* @param String $q
	* @param Integer/String $orgset
	*/
	public static function search_member($q,$orgset) {
		$q=preg_replace('/[ ]{2,}/',' ',trim($q));

		$isAdmin = user_access('administrator orgs');

		if (empty($q)) return;
		else if ($isAdmin) unset($orgset);
		else if (empty($orgset)) return;

		list($key,$value)=explode(':', $q);

		if (is_numeric($q)) {
			mydb::where('p.`phone` LIKE :phone ',':phone','%'.$q.'%');
		} else if ($key == 'org') {
			mydb::where('mo.`orgid` = :orgid',':orgid',$value);
		} else {
			list($name,$lname) = explode(' ',$q);
			$name = trim($name);
			$lname = trim($lname);
			mydb::where('(p.`name` LIKE :name) '.($lname?' AND (p.`lname` LIKE :lname)':''),':name','%'.$name.'%', ':lname','%'.$lname.'%');
		}
		if ($firstchar) mydb::where('p.`name` LIKE :firstchar', ':firstchar',$firstchar.'%');

		if ($isAdmin) {
				$stmt='SELECT
						p.*
					, mo.`orgid` inorgid
					, o.`name` orgname
					, COUNT(*) orgcount
					, (SELECT COUNT(*) FROM %org_dos% WHERE `psnid`=m.`psnid` AND `isjoin`=1) joins
					FROM %db_person% p
						LEFT JOIN %org_mjoin% AS m USING(`psnid`)
						LEFT JOIN %org_morg% mo USING(`psnid`)
						LEFT JOIN %db_org% o ON o.`orgid`=mo.`orgid`
					%WHERE%
					GROUP BY p.`psnid`
					ORDER BY CONVERT (p.`name` USING tis620) ASC, CONVERT (p.`lname` USING tis620) ASC';
				$dbs=mydb::select($stmt);
		} else {
			if ($orgset) mydb::where('m.`orgid` IN (:orgset)',':orgset','SET:'.$orgset);
			$stmt='SELECT
					p.*
				, m.*
				, mo.`orgid` inorgid
				, o.`name` orgname
				, COUNT(*) orgcount
				, (SELECT COUNT(*) FROM %org_dos% WHERE `psnid`=m.`psnid` AND `isjoin`=1) joins
				FROM %org_mjoin% AS m
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %org_morg% mo USING(`psnid`)
					LEFT JOIN %db_org% o ON o.`orgid`=mo.`orgid`
				%WHERE%
				GROUP BY p.`psnid`
				ORDER BY CONVERT (p.`name` USING tis620) ASC, CONVERT (p.`lname` USING tis620) ASC';
			$dbs=mydb::select($stmt);
		}

		return $dbs;
	}

	/**
	 * Get organization information
	 *
	 * @param Integer $oid
	 * @param Recors set
	 */
	public static function get_org($orgid) {
		$stmt  = 'SELECT o.*, j.`orgid` `joinToOrgId`, j.`joindate`, j.`created` joinCreatedDate , t.`name` `typename`, i.`name` `issuename`
			FROM %db_org% as o
				LEFT JOIN %org_ojoin% j ON j.`jorgid`=o.`orgid`
				LEFT JOIN %tag% AS t ON j.`type`=t.`tid`
				LEFT JOIN %tag% AS i ON j.`issue`=i.`tid`
			WHERE o.`orgid`=:orgid LIMIT 1';
		$rs=mydb::select($stmt,':orgid',$orgid);
		return $rs;
	}

	/**
	* Search person who join org
	*
	* @param String $q
	* @param Integer/String $orgset
	*/
	public static function search_org($q,$orgset) {
		$q=preg_replace('/[ ]{2,}/',' ',trim($q));

		if (user_access('administrator orgs')) unset($orgset);
		else if (empty($orgset)) return;

		if ($orgset) mydb::where('j.`orgid` IN (:orgset)',':orgset','SET:'.$orgset);
		if (is_numeric($q)) {
			mydb::where('p.`phone` LIKE :phone ',':phone','%'.$q.'%');
		} else if ($q) {
			list($name,$lname)=explode(' ',$q);
			$name=trim($name);
			$lname=trim($lname);
			mydb::where('(p.`name` LIKE :name) '.($lname?' AND (p.`lname` LIKE :lname)':''),':name','%'.$name.'%', ':lname','%'.$lname.'%');
		}
		if ($firstchar) mydb::where('p.`name` LIKE :firstchar', ':firstchar',$firstchar.'%');
		$stmt = 'SELECT j.`type`, j.`issue`, j.`joindate`, j.`created`,
			o.*, i.`name` `issue_name`, t.`name` `type_name`,
			(SELECT COUNT(*) FROM %org_morg% mo WHERE mo.`orgid`=j.`jorgid`) members
			FROM %org_ojoin% AS j
				LEFT JOIN %db_org% o ON o.`orgid`=j.`jorgid`
				LEFT JOIN %tag% AS i ON j.`issue`=i.`tid`
				LEFT JOIN %tag% AS t ON j.`type`=t.`tid`
			%WHERE%
			ORDER BY CONVERT (o.name USING tis620) ASC';
		$dbs=mydb::select($stmt);
		return $dbs;
	}

	public static function get_orgofficer($orgid = NULL) {
		if (empty($orgid)) $orgid = org_model::get_my_org();
		if (empty($orgid)) return;
		$result=NULL;
		$stmt='SELECT DISTINCT of.*, u.`username`, u.`name`, o.`name` `orgname`
			FROM %org_officer% of
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_org% o USING (`orgid`)
			WHERE of.`orgid` IN (:orgid)
			ORDER BY of.`uid` ASC';
		$dbs=mydb::select($stmt,':orgid','SET:'.$orgid);
		if ($dbs->_num_rows) $result->uid=NULL;
		foreach ($dbs->items as $rs) {
			$result->uids[$rs->uid]=$rs->uid;
			$result->orgs[$rs->orgid][$rs->uid]=$rs;
		}
		$result->uid=implode(',', $result->uids);
		return $result;
	}

	public static function get_orgproject($orgid = NULL) {
		if (empty($orgid)) $orgid = org_model::get_my_org();
		if (empty($orgid)) return;
		$stmt='SELECT t.*
					FROM %topic% t
					WHERE t.`type` = "project" AND t.`orgid` IN (:orgid)
					ORDER BY CONVERT(t.`title` USING tis620) ASC';
		$dbs=mydb::select($stmt, ':orgid', 'SET:'.$orgid);
		return $dbs;
	}

	public static function get_volunteer($psnid) {
		$result=array();
		if (bigdata::getField('volunteer','org',$psnid)) {
			foreach (bigdata::getField('*','org',$psnid) as $item) {
				$result[$item->fldname]=$item;
			}
		}
		return $result;
	}

	/**
	 * Get org photo
	 *
	 * @param String $id
	 * @return String
	 */
	public static function org_photo($id=NULL) {
		$photo_file='upload/org/org-logo-'.$id.'.jpg';
		if (file_exists($photo_file)) {
			return _URL.$photo_file;
		} else {
			return _img.'photography.png';
		}
		return $photo;
	}
}
?>