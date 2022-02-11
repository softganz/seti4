<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function green_follow_home($self) {
	$ret = '<header class="header -box"><h3>ติดตาม</h3></header>';

	$ret .= '<header class="header"><h4>รายการจอง</h4></header>';

	$stmt = 'SELECT
		b.*, p.`unit`, p.`productname`
		, o.`name` `shopName`
		FROM %ibuy_farmbook% b
			LEFT JOIN %ibuy_farmplant% p USING(`plantid`) 
			LEFT JOIN %db_org% o USING(`orgid`)
		WHERE b.`uid` = :uid';

	$dbs = mydb::select($stmt, ':plantid', $plantId, ':uid', i()->uid);

	$myBookUi = new Ui(NULL, 'ui-menu');
	foreach ($dbs->items as $rs) {
		$myBookUi->add('- ฉันจอง <b>'.$rs->productname.'</b> จาก <b>'.$rs->shopName.'</b> @'.sg_date($rs->created, _DATE_FORMAT).' จำนวน '.$rs->qty.' '.$rs->unit);
	}
	$ret .= $myBookUi->count() ? $myBookUi->build() : 'ยังไม่มีการจอง';

	//$ret .= print_o($dbs, '$dbs');
	return $ret;
}
?>