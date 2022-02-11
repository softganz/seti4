<?php
/**
 * Symbol setting
 *
 * @param String $symbol
 * @param String $_REQUEST['symbol']
 * @return String
 */
function set_setting($self,$symbol) {
	$symbol=SG\getFirst($symbol,$_REQUEST['symbol']);
	$symbolRS=mydb::select('SELECT * FROM %setwishlist% WHERE `symbol`=:symbol AND `uid`=:uid LIMIT 1',':symbol',$symbol, ':uid',i()->uid);
	$ret.='<h2>Setting : '.$symbol.'</h2>';
	$ret.='<div id="set-setting-wishlist"><h3>Wishlist group</h3>';
	$ret.='<form class="sg-form" method="post" action="'.url('set/addtowishlist').'" data-rel="#set-info" data-type="json"><input type="hidden" name="symbol" value="'.$symbol.'" /><input class="form-text" type="text" name="newwishlist" size="40" placeholder="Enter new wishlist group" /> <button type="submit" class="btn -primary"><i class="icon -save -white"></i><span>Save</span></button>';
	$groupList=explode(',',mydb::select('SELECT gid FROM %setwishlist% WHERE `uid`=:uid AND `symbol`=:symbol',':uid',i()->uid,':symbol',$symbol)->lists->text);
	$dbs=mydb::select('SELECT * FROM %setgroup% WHERE `uid`=:uid',':uid',i()->uid);
	foreach ($dbs->items as $rs) {
		$ret.='<p><label><input type="checkbox" name="wishlist['.$rs->gid.']" value="'.$rs->gid.'" '.(in_array($rs->gid, $groupList)?' checked="checked"' : '' ).' /> '.$rs->name.'</label></p>';
	}
	$ret.='<label>Wish Price : </label><input type="text" name="wishprice" class="form-text" value="'.$symbolRS->wishprice.'" /> <label>Sale Price : </label><input type="text" name="saleprice" class="form-text" value="'.$symbolRS->saleprice.'" /> <button type="submit" class="btn -primary" id="set-setting-save-wish-price"><i class="icon -save -white"></i><span>Save Wish Price</span></button>';
	$ret.='</form>';
	$ret.='</div>';
	$ret.='<div>';
	$ret.='<h3>คำนวณมูลค่าของหุ้น</h3><form id="set-cal-cost" method="get" action="">มูลค่าของหุ้น = <input type="text" class="form-text" name="eps" size="3" placeholder="EPS"> ( 8.5 + 2 x <input type="text" class="form-text" name="g" size="2" placeholder="G"> ) = <input type="text" name="cost" class="form-text" size="6" readonly="readonly" /> <button type="submit" class="btn -primary"><span>คำนวณ</span></button></form>';
	return $ret;
}
?>