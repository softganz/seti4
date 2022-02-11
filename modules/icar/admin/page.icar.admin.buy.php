<?php
function icar_admin_buy($self) {
	$self->theme->title='Buy transaction';

	$shopid=post('shopid');

	R::View('icar.toolbar', $self);

	$shopList=mydb::select('SELECT * FROM %icarshop%')->items;
	foreach ($shopList as $rs) {
		$ret.='<a href="'.url('icar/admin/buy',array('shopid'=>$rs->shopid)).'">'.$rs->shopname.'</a> | ';
	}

	if ($shopid) mydb::where('s.`shopid`=:shopid',':shopid',$shopid);

	$stmt = 'SELECT c.*, s.*, t.*, tg.`name` `brandName`
		FROM %icar% c
			LEFT JOIN %icarshop% s USING(`shopid`)
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %tag% tg ON tg.`tid`=c.`brand`
		%WHERE%
		ORDER BY t.`tpid` DESC
		LIMIT 200';

	$dbs = mydb::select($stmt);
	
	$tables = new Table();
	$tables->thead = array('date created'=>'วันที่','date buydate'=>'วันที่ซื้อ','ร้านค้า','ยี่ห้อ','ทะเบียน');

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->created,'d-m-Y H:i'),
			sg_date($rs->buydate,'d-m-Y'),
			$rs->shopname,
			$rs->brandName,
			$rs->plate,
			'<a href="'.url('icar/'.$rs->tpid).'">View</a>'
		);
	}
	$ret.=$tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>