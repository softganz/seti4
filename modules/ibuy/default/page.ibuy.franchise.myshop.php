<?php
/**
 * Method for view my shop
 */
function ibuy_franchise_myshop($self) {
	return;
	if (!i()->ok) location('ibuy/franchise');
	//return $self->view(i()->username);

	$shop_tpid=mydb::select('SELECT `tpid` FROM %topic% WHERE `type`="franchise" AND `uid`=:uid LIMIT 1',':uid',i()->uid)->tpid;
	if (empty($shop_tpid)) {
		$shop_detail='<p>คุณยังไม่ได้เปิดร้าน <a href="'.url('paper/post/franchise').'">ต้องการเปิดหน้าร้านในเว็บไซท์ของเราไหม?</a></p>';
		$shop_detail.='<p>เรามีบริการให้ท่านสามารถเปิดหน้าร้านของท่านในเว็บของเรา ซึ่งท่านจะสามารถป้อนรายละเอียดของร้านค้าของท่าน รวมทั้งรูปหน้าร้านและรูปสินค้าต่าง ๆ ของท่านได้ ทั้งยังสามารถกำหนดตำแหน่งร้านในแผนที่ได้อีกด้วย ตามตัวอย่างด้านล่าง</p>';
		$shop_map='<iframe width="800" height="450" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="width:100%;" src="http://maps.google.co.th/maps/ms?hl=th&amp;ie=UTF8&amp;msa=0&amp;msid=116487760279255118810.00046d23c17d6197c3acf&amp;ll=7.001987,100.468416&amp;spn=0.009541,0.059397&amp;z=17&amp;t=h&amp;iwloc=000472e7dfc159985fd80&amp;output=embed"></iframe><br /><small>ดู <a href="http://maps.google.co.th/maps/ms?hl=th&amp;ie=UTF8&amp;msa=0&amp;msid=116487760279255118810.00046d23c17d6197c3acf&amp;ll=7.001987,100.468416&amp;spn=0.009541,0.059397&amp;t=h&amp;iwloc=000472e7dfc159985fd80&amp;source=embed" style="color:#0000FF;text-align:left">Songkhla - สงขลา</a> ในแผนที่ขนาดใหญ่กว่า</small>';
	} else {
		$shop_detail=R::Page('paper.view',$self,$shop_tpid);
	}
	$ret.='<div id="ibuy-shop-detail">'.$shop_detail.'</div>';
	$ret.='<div id="ibuy-shop-map">'.$shop_map.'</div>';
	return $ret;
}
?>