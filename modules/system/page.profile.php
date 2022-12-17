<?php
function profile($self,$uid=NULL) {
	R::View('profile.toolbar',$self);

	if (is_numeric($uid)) return R::Page('profile.view',$uid);

	if (!user_access('access user profiles')) return message('error','Access denied');


	$para=para(func_get_args(),0);
	$items=50;

	$self->theme->title='ประวัติ - สมาชิกล่าสุด';

	/*
	user_menu('home','home',url());
	user_menu('profile','profile',url('profile'));
	$self->theme->navigator=user_menu();
	*/

	head('<meta name="robots" content="noindex,nofollow">');

	$ret='<h3>สมาชิกล่าสุด</h3>';

	$pagenv=new PageNavigator($items,$para->page,$total_items,q());

	$stmt='SELECT SQL_CALC_FOUND_ROWS *
				FROM %users% as u 
				ORDER BY `uid` DESC
				LIMIT '.$pagenv->FirstItem().','.$items;
	$dbs=mydb::select($stmt);
	$total_items = mydb()->found_rows();
	$pagenv=new PageNavigator($items,$para->page,$total_items,q());


	$ret .= $pagenv->show;
	$ui=new Ui(NULL,'ui-card profile-list');
	foreach ($dbs->items as $rs) {
		if ($rs->username==='root') continue;
		$card='<h3><a href="'.url('profile/'.$rs->uid).'">'.$rs->name.'</a></h3>'._NL;
		$card .= '<img src="'.BasicModel::user_photo($rs->username).'" alt="'.htmlspecialchars($rs->name).'" />'._NL;
		if ($rs->real_name) $card.=$rs->real_name.($rs->mid_name?' ('.$rs->mid_name.')':'').' '.$rs->last_name.'<br />'._NL;
		if ($rs->organization) $card.=$rs->organization ? $rs->organization.'<br />'._NL:'';
		$card.='<a href="'.url('profile/'.$rs->uid).'">มีต่อ &raquo;</a>'._NL;
		$card.='</li>'._NL._NL;

		$ui->add($card);
	}
	$ret.=$ui->build();
	$ret.=$pagenv->show;

	return $ret;
}
?>