<?php
/**
 * flood class for Flood Management
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

class flood extends flood_base {

	function __construct() {
		$this->theme->title=tr('Hatyai Flood Monitor','เฝ้าระวังน้ำท่วมหาดใหญ่');
		parent::__construct();
//		cfg('page_id','flood');
	}

	function permission() { return 'administrator floods,operator floods,create flood content,edit own flood content,access flood content,access flood command center';}

	/**
	 * Module install
	 *
	 * @return null
	 */
	function install() {}



	/** Update water level using crowdsourcing
	 *
	 * @param
	 * @retuern Array
	 */
	function _crowd_water_update() {
		$id=intval(trim($_REQUEST['id']));
		$value=$_REQUEST['value'];
		if ($value=='...') return array('value'=>$value);

		$ret['value']=sg::sealevel($value);
		$ret['msg']='บันทึกเรียบร้อย';
		$ret['error']='';
		$ret['debug'].='[group='.$group.' , part='.$part.', pid='.$pid.',fld='.$fld.',tr='.$tr.']<br />';
		$ret['debug'].=print_o($_REQUEST,'$_REQUEST');

		if (empty($id) || empty($value)) $ret['error']='Invalid parameter';

		$values['camid']=$id;
		$values['uid']=i()->ok?i()->uid:'func.NULL';
		$values['rectime']=date('U');
		$values['waterlevel']=$ret['value'];
		$values['created']=date('U');
		$values['source']='crowd';
		$values['priority']=-5;

		$stmt='INSERT INTO %flood_level% (`camid`, `uid`, `rectime`, `waterlevel`, `source`, `priority`, `created`) VALUES (:camid, :uid, :rectime, :waterlevel, :source, :priority, :created)';
		mydb::query($stmt,':autoid',$tr,':value',$value,$values);

		$ret['rectime']=sg_date($values['rectime'],'ว ดด ปป H:i');
		$ret['debug'].=mydb()->_query;
		if (!_AJAX) $ret['location']=array('flood/cam/'.$id);
		return $ret;
	}

} // end of class flood

?>