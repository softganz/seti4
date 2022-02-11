<?php
/**
 * saveup class for saveup management
 *
 * @package saveup
 * @version 0.10a4
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2008-05-21
 * @modify 2011-06-29
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

/***********************************
 * Class model
 ***********************************/
class saveup_model {

	function get_member($mid) {
		$stmt='SELECT * FROM %saveup_member% WHERE `mid`=:mid LIMIT 1';
		$rs=mydb::select($stmt,':mid',$mid);
		return $rs;
	}

	/**
	 * Get member photo
	 *
	 * @param String $pid
	 * @return String
	 */
	function member_photo($pid=NULL) {
		$photo_file='upload/saveup/profile-'.$pid.'.jpg';
		if (file_exists($photo_file)) {
			return _URL.$photo_file;
		} else {
			return _img.'photography.png';
		}
		return $photo;
	}

	/**
	 * Get all header of member line
	 *
	 * @return record set
	 */
	function get_lines() {
		$stmt = 'SELECT
							  l.`mid`, l.`lid`, l.`parent`
							, CONCAT(m.`firstname`," ",m.`lastname`) AS `name`
							FROM %saveup_line% l
								LEFT JOIN %saveup_member% m ON l.`lid` = m.`mid` 
							WHERE m.`status` = "active" AND l.`parent` = 0
							ORDER BY `name` ASC';
		$lines = mydb::select($stmt);
		return $lines;
	}

	/**
	 * Get member detail my mid
	 *
	 * @param String $mid
	 * @return record
	 */
	function get_user_detail($mid) {
		$stmt='SELECT m.*,
										g.gis, CONCAT(X(g.`latlng`),",",Y(g.`latlng`)) latlng, X(g.`latlng`) lat, Y(g.`latlng`) lnt
						FROM %saveup_member% AS m
							LEFT JOIN %gis% g ON m.gis=g.gis
						WHERE m.mid="'.$mid.'" LIMIT 1';
		$member=mydb::select($stmt);
		if ($member->_num_rows) {
			// Get member line
			$stmt='SELECT l.*,CONCAT(u.firstname," ",u.lastname) AS line_name FROM %saveup_line% l LEFT JOIN %saveup_member% u ON l.lid=u.mid WHERE l.mid=:mid LIMIT 1';
			$line=mydb::select($stmt,':mid',$mid);
			if ($line->_num_rows) {
				$member->line_id=$line->lid;
				$member->line_parent=$line->parent;
				$member->line_name=$line->line_name;
			}
		}
		return $member;
	}

	/**
	 * Get treat by id
	 *
	 * @param Integer $id
	 * @return record
	 */
	function get_treat_by_id($id) {
		$sql_cmd='SELECT tr.*,CONCAT(fu.firstname," ",fu.lastname) name
							FROM %saveup_treat% AS tr
								LEFT JOIN %saveup_member% fu ON fu.mid=tr.mid
							WHERE tr.tid=:tid LIMIT 1';
		$rs=mydb::select($sql_cmd,':tid',$id);
		return $rs;
	}

	/**
	 * Process & status log
	 *
	 * @param String $keyword
	 * @param Integer $kid
	 * @param BigInt $date
	 * @param Integer $status
	 * @param String $detail
	 */
	public function log() {
		$para=para(func_get_args(),'keyword=','kid=0','status=-1','created='.date('U'),'detail=รายละเอียด','process=-1','amt=func.NULL');
		$para->uid=SG\getFirst(i()->uid,'func.NULL');
		$para->poster=SG\getFirst($para->poster,'');
		unset($para->_src);
		$stmt='INSERT INTO %saveup_log%
							(`uid`, `keyword`, `kid`, `status`, `poster`, `amt`, `process`, `created`, `detail`)
						VALUES
							(:uid, :keyword, :kid, :status, :poster, :amt, :process, :created, :detail)';
		mydb::query($stmt,$para);
	}

	/**
	 * Get next recieve number
	 *
	 * @return String
	 */
	function get_next_no($type) {
		$no['RCV']=array('saveup_rcvmast','rcvno');
		$no['LON']=array('saveup_loan','loanno');
		$table=$no[$type][0];
		$fld=$no[$type][1];
		$stmt='SELECT MAX('.$fld.') lastno FROM %'.$table.'% LIMIT 1';
		$lastno=mydb::select($stmt)->lastno;
		$lastnum=empty($lastno) ? 0 : substr($lastno,strlen($type));
		$nextno=$type.sprintf('%07d',$lastnum+1);
		return $nextno;
	}

	/**
	 * Init application as main page
	 * @param $self
	 */
	function init_app_mainpage($self = NULL) {
		define('_AJAX',false);

		cfg('navigator',R::Page('saveup.app.nav.main'));
		cfg('web.footer','&copy; Copyright SoftGanz.');

		set_theme('app');
		cfg('theme.stylesheet.para','?v='.date('U'));
		unset($self->theme->toolbar);
		unset($self->theme->option->title);
		$self->theme->option->container=false;
		$self->theme->toolbar=NULL;
	}
} // end of class saveup_model

?>