<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_app_team($self) {
	project_model::init_app_mainpage();

	$stmt='SELECT tu.`uid`, u.`username`, u.`name`, COUNT(*) projects
					FROM %topic_user% tu
						LEFT JOIN %users% u USING(uid)
					WHERE `membership` IN ("Owner","Trainer")
					GROUP BY tu.`uid`
					ORDER BY name ASC';
	$dbs=mydb::select($stmt);
	$ret.='<ul class="profile-list">';
	foreach ($dbs->items as $rs) $ret.='<li><a href="javascript:void(0)" ><img src="'.model::user_photo($rs->username).'" alt="'.htmlspecialchars($rs->name).'" /><strong>'.$rs->name.'</strong></a><br />'.$rs->projects.' โครงการ</li>';
	$ret.='</ul>';

	return $ret;
}
?>