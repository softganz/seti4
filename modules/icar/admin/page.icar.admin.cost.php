<?php
function icar_admin_cost($self) {
	$self->theme->title='Cost transaction';

	$shopid=post('shopid');
	R::View('icar.toolbar', $self);

	$shopList=mydb::select('SELECT * FROM %icarshop%')->items;
	foreach ($shopList as $rs) {
		$ret.='<a href="'.url('icar/admin/cost',array('shopid'=>$rs->shopid)).'">'.$rs->shopname.'</a> | ';
	}

	if ($shopid) mydb::where('s.`shopid` = :shopid',':shopid',$shopid);

	$stmt = 'SELECT s.`shopname`, tr.*, t.`name` costname, c.`plate`
		FROM %icarcost% tr
			LEFT JOIN %tag% t ON t.tid=tr.costcode
			LEFT JOIN %icar% c USING(tpid)
			LEFT JOIN %icarshop% s ON c.shopid=s.shopid
		%WHERE%
		ORDER BY `costid` DESC
		LIMIT 1000';

	$dbs = mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');
	
	$tables = new Table();
	$tables->thead=array('itemdate -date'=>'วันที่','shop -nowrap' => 'ร้านค้า','ทะเบียน','รายการ','interest -amt' => 'ด/บ','money' => 'จำนวนเงิน','created -date -hover-parent'=>'วันที่ป้อน');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->itemdate,'d-m-Y'),
			$rs->shopname,
			$rs->plate,
			$rs->costname
			. ($rs->detail ? ' ('.$rs->detail.')' : ''),
			$rs->interest > 0 ? $rs->interest : '',
			number_format($rs->amt,2),
			sg_date($rs->created,'d-m-Y H:i')
			.'<nav class="nav iconset -hover"><a href="'.url('icar/'.$rs->tpid).'"><i class="icon -viewdoc"></i></a></nav>'
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>