<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome_add($self) {
	$self->theme->title='เพิ่มแบบสำรวจ';
	$self->theme->toolbar=R::Page('imed.poorhome.toolbar',$self);

	$stmt='INSERT INTO %poor% (`uid`, `created`) VALUES (:uid, :created)';
	mydb::query($stmt,':uid',i()->uid, ':created',date('U'));

	$poorid=mydb()->insert_id;

	location('imed/poorhome/view/'.$poorid);

	return $ret;
}
?>