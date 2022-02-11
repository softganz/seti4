<?php
/**
* Garage : Order Home
* Created 2020-10-20
* Modify  2020-10-20
*
* @param Object $self
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_order_home($self){
	$shopInfo = R::Model('garage.get.shop');

	mydb::where('(o.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);

	$stmt = 'SELECT
		o.*
		, s.`shortname` `shopShortName`
		, a.`apname`
		FROM %garage_ordmast% o
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_ap% a USING(`apid`)
		%WHERE%
		ORDER BY o.`ordid` DESC
	';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	$ret.='<form id="garage-master-form" class="sg-form" method="post" action="'.url('garage/info/*/order.new').'" data-checkvalid="true">'._NL;


	$tables = new Table();
	$tables->thead=array('docno -center'=>'เลขที่','date -docdate'=>'วันที่','name'=>'บริษัท','money -totalprice'=>'จำนวนเงิน','icons -c1'=>'');

	$tables->rows[]=array(
		'<input id="docid" class="form-text -fill -uppercase -require" type="text" name="docno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'Order','ORD')->nextNo.'" placeholder="รหัส" size="5" maxlength="10" readonly="readonly" />',
		'<input id="docdate" class="form-text sg-datepicker -fill -require" type="text" name="docdate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
		'<input id="apid" type="hidden" name="apid" value="" /><input id="apname" class="form-text sg-autocomplete -fill -require" type="text" name="apname" value="" placeholder="ชื่อผู้จำหน่าย" size="7" data-query="'.url('garage/api/ap').'" data-altfld="apid" data-select="label" />',
		'<td colspan="3"><button class="btn -primary" type="submit"><i class="icon -material">add</i><span>สร้างใบสั่งของใหม่</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class' => '-input -no-print'),
	);

	$tables->rows[]='<header>';
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->ordno,
			sg_date($rs->orddate,'d/m/ปปปป'),
			$rs->apname,
			$rs->total?number_format($rs->total,2):'0',
			'<a class="-no-print" href="'.url('garage/order/'.$rs->ordid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>',
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);
	}
	$ret.=$tables->build();
	return $ret;
}
?>