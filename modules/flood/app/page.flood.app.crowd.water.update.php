<?php
/** Update water level using crowdsourcing
 *
 * @param
 * @retuern Array
 */
function flood_app_crowd_water_update($self) {
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
?>