<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function flood_admin($self) {
	$self->theme->title='Flood Administrator';
	$self->theme->sidebar=R::Page('flood.admin.menu');

	$ui=new ui();
	$ui->add('<form id="search-member" class="search-box sg-form" method="get" action="'.url('flood/admin/member').'" role="search"><input type="hidden" name="id" id="id" /><input class="sg-autocomplete form-text" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email"><button type="submit" class="btn"><i class="icon -search"></i></button></form>');

	$ret.='<nav class="nav -page"><header class="header -hidden"><h3>Flood Administrator</h3></header>'._NL;
	$ret.=$ui->build();
	$ret.='</nav><!--nav-->'._NL;

	$stmt = 'SELECT u.*, f.`shop_name`, f.`shop_attn`, f.`shop_type`
		FROM %users% AS u
		WHERE DATE(`datein`)=CURDATE()
		GROUP BY u.`uid`
		ORDER BY `uid` DESC';

	$dbs= mydb::select($stmt);

	$ret.='<h3>สมาชิกใหม่วันนี้</h3>';

	$tables = new Table();
	$tables->addClass('user-list');
	$tables->caption='รายชื่อสมาชิก';
	$tables->thead=array('name'=>'ชื่อ','date'=>'วันที่สมัคร');

	foreach ($dbs->items as $rs) {
		if ($rs->uid==1) continue;
		$tables->rows[]=array(
			'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box" title="User Information"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
			sg_date($rs->datein,'d-m-Y G:i'),
			'config'=>array('class'=>'user-'.$rs->status,'title'=>'User was '.$rs->status)
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>