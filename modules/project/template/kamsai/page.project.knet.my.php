<?php
function project_knet_my($self) {
	R::View('project.toolbar', $self, '@'.i()->name, 'knet');

	// Check for owner fund
	if (!i()->ok) {
		$ret.=R::View('signform');
		return $ret;
	}

	$stmt='SELECT of.`orgid`, o.*, cop.`provname` `changwat`
				FROM %org_officer% of
					LEFT JOIN %db_org% o USING(`orgid`)
					RIGHT JOIN %school% s USING(`orgid`)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
				WHERE of.`uid`=:uid
				ORDER BY CONVERT(`name` USING tis620) ASC';
	$orgMember=mydb::select($stmt,':uid',i()->uid);


	if ($orgMember->count()==0) {
		//location('project/knet');
		$ret .= message('notify','ท่านยังไม่มีโรงเรียนในความรับผิดชอบ');
	} if ($orgMember->count()==1) {
		$orgInfo = $orgMember->items[0];
		location('project/knet/'.$orgInfo->orgid);
	} else {
		$ui = new Ui(NULL,'ui-card school-card -flex -sg-text-center');
		foreach ($orgMember->items as $rs) {
			$ui->add('<a href="'.url('project/knet/'.$rs->orgid).'"><img src="//img.softganz.com/img/school-01.png" width="200" /><h3 class="card-title">'.$rs->name.'</h3></a><p class="card-detail">จังหวัด'.($rs->changwat ? $rs->changwat : '???').'</p>');

		}
		$ret.=$ui->build();
	}

	$ret .= '<style type="text/css">
	.school-card.-flex {display: flex; flex-wrap: wrap;}
	.school-card .card-title {font-size: 1.2em;}
	.school-card .ui-item {width: 240px; margin: 0 32px 32px 0;}
	.school-card .ui-item img {width: 60%; margin: 16px;}
	</style>';
	//$ret .= print_o($orgMember);
	return $ret;
}
?>