<?php
/**
 * Add symbol to wish list
 *
 * @param String $symbol
 * @param String $_REQUEST['symbol']
 * @return String
 */
function set_addtowishlist($self,$symbol = NULL) {
	$symbol = SG\getFirst($symbol, post('symbol'));
	if ($symbol && i()->ok) {
		$groups=post('wishlist');
		$wishprice=post('wishprice')>0?post('wishprice'):'func.NULL';
		$saleprice=post('saleprice')>0?post('saleprice'):'func.NULL';
		if (post('newwishlist')
				&& mydb::select('SELECT COUNT(*)amt FROM %setgroup% WHERE `uid`=:uid AND `name`=:name LIMIT 1',':uid',i()->uid, ':name',post('newwishlist'))->amt==0) {
			mydb::query('INSERT INTO %setgroup% (`uid`, `gtype`, `name`) VALUES (:uid, "Wish List", :name)',':uid',i()->uid, ':name', post('newwishlist'));
			$groups[mydb()->insert_id]=mydb()->insert_id;
		}
		mydb::query('DELETE FROM %setwishlist% WHERE `uid`=:uid AND `symbol`=:symbol',':uid',i()->uid, ':symbol',$symbol);
		foreach ($groups as $key => $value) {
			mydb::query('INSERT INTO %setwishlist% (`gid`, `uid`, `symbol`, `wishprice`, `saleprice`) VALUES (:gid, :uid, :symbol, :wishprice, :saleprice)',':gid',$value, ':uid',i()->uid, ':symbol',$symbol, ':wishprice',$wishprice, ':saleprice', $saleprice);
		}
		//$ret['msg']='Update '.$symbol.' to wish list completed.';
	} else {
		//$ret['msg']='Error : No symbol or not signin.';
	}
	//$ret['html']=$this->_setting($symbol);
	$ret=R::Page('set.setting',$self,$symbol);
	return $ret;
}
?>