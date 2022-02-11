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
function ibuy_admin($self) {
	$self->theme->title = 'iBuy Administrator';
	$self->theme->sidebar = R::Page('ibuy.admin.menu');

	$isEdit = user_access('administer ibuys');

	$ui = new Ui();
	$ui->add('<form id="search-member" class="search-box sg-form" method="get" action="'.url('ibuy/admin/member').'" role="search"><input type="hidden" name="id" id="id" /><input class="sg-autocomplete" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email"><input type="submit" class="button" value="ค้นหาสมาชิก"></form>');

	$ret.='<header class="header -hidden"><h3>iBuy Administrator</h3></header>'._NL;
	$ret.=$ui->build()._NL;

	$stmt = 'SELECT u.*, f.`custname`, f.`custattn`, f.`custtype`
						FROM %users% AS u
							LEFT JOIN %ibuy_customer% f USING(`uid`)
						-- WHERE DATE(`datein`)=CURDATE()
						WHERE `custtype` IS NULL OR `custtype` = ""
						GROUP BY u.`uid`
						ORDER BY `uid` DESC';

		$dbs= mydb::select($stmt);

		$ret.='<h3>สมาชิกใหม่วันนี้/สมาชิกยังไม่กำหนดระดับราคา</h3>';

		$tables = new Table();
		$tables->addClass('ibuy-member-list sg-inline-edit');
		$tables->caption='รายชื่อสมาชิก';
		$tables->thead=array('name'=>'ชื่อ','amt'=>'ระดับราคา','shop'=>'ร้าน / ที่อยู่','โทรศัพท์','amt orders'=>'สั่งสินค้า', 'amt score'=>'คะแนน'	, 'date'=>'วันที่สมัคร');


		$memberLevelList = array(''=>'ไม่กำหนด');
		foreach (cfg('ibuy.price.use') as $key => $value) {
			if ($key == 'cost') continue;
			$memberLevelList[$key] = $value->label;
		}

		foreach ($dbs->items as $rs) {
			if ($rs->uid==1) continue;
			$tables->rows[]=array(
											'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box" title="User Information"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /><br /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
											view::inlineedit(array('group'=>'franchise','fld'=>'custtype','tr'=>$rs->uid),$memberLevelList[$rs->custtype],$isEdit,'select',$memberLevelList),
											'<a href="'.url('ibuy/franchise/'.$rs->username).'"><strong>'.$rs->custname.'</strong></a><br />'.$rs->custaddress,
											$rs->custphone,
											$rs->orderTotal,
											number_format($rs->score),
											$rs->datein?sg_date($rs->datein,sg_date($rs->datein,'Y-m-d')==date('Y-m-d')?'G:i':'d-m-Y G:i'):'',
											'config'=>array('class'=>'user-'.$rs->status,'title'=>'User was '.$rs->status)
										);
			if ($rs->admin_remark) $tables->rows[]=array('','<td colspan="3"><p><font color="#f60">Admin remark : '.$rs->admin_remark.'</font></p></td>');
		}

		head('<script>var tpid='.$tpid.'</script>');
		$inlinePara['data-update-url']=url('ibuy/admin/update');
		if (post('debug')) $inlinePara['data-debug']='yes';
		$tables->attr=$inlinePara;

		$ret .= $tables->build();
	return $ret;
}
?>