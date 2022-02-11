<?php
/**
* Port management
*
* @param String $_REQUEST['newport']
* @param Integer $_REQUEST['delete']
* @return Array
*/
function set_createport($self) {
	$ret.='<h2>My port folio</h2>';
	if (post('newport')) {
		mydb::query('INSERT INTO %setgroup% (`uid`, `gtype`, `name`) VALUES (:uid, "Port", :name)',':uid',i()->uid, ':name', post('newport'));
	} else if ($gid=post('delete')) {
		$isUseInPort=mydb::select('SELECT COUNT(*) total FROM %setport% WHERE `gid`=:gid LIMIT 1',':gid',$gid)->total;
		$isUseInWishlist=mydb::select('SELECT COUNT(*) total FROM %setwishlist% WHERE `gid`=:gid LIMIT 1',':gid',$gid)->total;
		if (($isUseInPort || $isUseInWishlist) && post('pconfirm')!='yes') {
			$ret.='<p><strong>คำเตือน :</strong> ท่านกำลังจะลบพอร์ตที่มีข้อมูลบันทึกการซื้อ/ขายหลักทรัพย์อยู่ หรือมีรายการใน Wish List ต้องการลบจริงหรือไม่? <a href="'.url('set/createport',array('delete'=>$gid,'pconfirm'=>'yes')).'" class="sg-action button" data-rel="#app-output" data-type="json">ยืนยันการลบรายการ</a></p><p><strong>หมายเหตุ :</strong> ยืนยันการลบรายการจะทำการลบข้อมูลการซื้อ/ขายและ Wish List ทั้งหมดของพอร์ตนี้';
		} else {
			mydb::query('DELETE FROM %setgroup% WHERE `gid`=:gid AND `uid`=:uid LIMIT 1',':gid',$gid, ':uid',i()->uid);
			if ($isUseInPort) mydb::query('DELETE FROM %setport% WHERE `gid`=:gid AND `uid`=:uid',':gid',$gid, ':uid',i()->uid);
			if ($isUseInWishlist) mydb::query('DELETE FROM %setwishlist% WHERE `gid`=:gid AND `uid`=:uid',':gid',$gid, ':uid',i()->uid);
		}
	}
	$ret.='<div id="set-setting-wishlist">';
	$ret.='<form class="sg-form" method="post" action="'.url('set/createport').'" data-rel="#app-output"><label>Create new port : </label><input type="text" name="newport" class="form-text" placeholder="Enter new port name" /> <button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>Create New Port</span></button>';
	$dbs=mydb::select('SELECT * FROM %setgroup% WHERE `uid`=:uid AND `gtype`="Port" ORDER BY `name` ASC',':uid',i()->uid);
	$ret.='<h3>รายชื่อพอร์ต</h3>';
	$ret.='<ul>';
	foreach ($dbs->items as $rs) {
		$ret.='<li>'.$rs->name.' <a class="sg-action" href="'.url('set/createport','delete='.$rs->gid).'" data-rel="none" data-removeparent="li" data-confirm="ต้องการลบจริงหรือไม่?"><i class="icon -delete"></i></a></li>';
	}
	$ret.='</ul>';
	$ret.='</form>';
	$ret.='</div>';
	return $ret;
}
?>