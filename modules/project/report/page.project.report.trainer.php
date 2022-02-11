<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_trainer($self) {
	R::View('project.toolbar', $self, 'รายชื่อพี่เลี้ยง', 'report');

	$stmt='SELECT tu.`uid`, u.`username`, u.`name`, COUNT(*) projects
					FROM %topic_user% tu
						LEFT JOIN %users% u USING(uid)
					WHERE `membership`="Trainer"
					GROUP BY tu.`uid`
					ORDER BY name ASC';
	$dbs=mydb::select($stmt);

	$ret.='<ul class="profile-list">';
	foreach ($dbs->items as $rs) $ret.='<li><a href="'.url('project/list','trainer='.$rs->uid).'" ><img src="'.model::user_photo($rs->username).'" alt="'.htmlspecialchars($rs->name).'" /><strong>'.$rs->name.'</strong></a><br />'.$rs->projects.' โครงการ</li>';
	$ret.='</ul>';
	return $ret;
}
?>