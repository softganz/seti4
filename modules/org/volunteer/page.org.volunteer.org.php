<?php
function org_volunteer_org($self) {
	$isAdmin=user_access('administrator orgs');
	if ($isAdmin) $self->theme->title='ฐานข้อมูลองค์กร';
	else {
		unset($self->theme->title);
		$ret.='<h2 class="title">ฐานข้อมูลองค์กร</h2>';
	}
	$stmt='SELECT j.`type`, j.`issue`, j.`joindate`, j.`created`,
						o.*, i.`name` `issue_name`, t.`name` `type_name`,
						(SELECT COUNT(*) FROM %org_morg% mo WHERE mo.`orgid`=j.`jorgid`) members
						FROM %org_ojoin% AS j
							LEFT JOIN %db_org% o ON o.`orgid`=j.`jorgid`
							LEFT JOIN %tag% AS i ON j.`issue`=i.`tid`
							LEFT JOIN %tag% AS t ON j.`type`=t.`tid`
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						ORDER BY CONVERT (o.name USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->addClass('-org');
	$tables->thead=array('โลโก้','ชื่อ','เว็บไซต์','ที่อยู่','จังหวัด','เบอร์ติดต่อ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
												'<a href="'.url('org/'.$rs->orgid).'"><img class="logo" src="'.org_model::org_photo($rs->orgid).'" width="96" height="96" /></a>',
												'<a href="'.url('org/'.$rs->orgid).'"><strong>'.$rs->name.'</strong></a>',
												$rs->website?'<a href="'.$rs->website.'" target="_blank">'.$rs->website.'</a>':'',
												$rs->address,
												$rs->province,
												$rs->phone);
	}

	$ret .= $tables->build();
	$ret.='<style type="text/css">
		.item.-org td {vertical-align:middle}
		</style>';
	return $ret;
}
?>