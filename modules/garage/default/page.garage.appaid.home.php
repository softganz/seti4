<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage garage/appaid/{$paidId}
*/

$debug = true;

function garage_appaid_home($self) {
	new Toolbar($self,'จ่ายชำระหนี้','finance');

	$shopInfo = R::Model('garage.get.shop');

	R::Model('garage.verify', $self, $shopInfo, 'FINANCE');

	mydb::where('(r.`shopid` = :shopid  OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES"))', ':shopid', $shopInfo->shopid);

	$stmt = 'SELECT
		r.*
		, s.`shortname` `shopShortName`
		, a.`apname`
		FROM %garage_appaid% r
			LEFT JOIN %garage_shop% s USING(`shopid`)
			LEFT JOIN %garage_ap% a USING(`apid`)
		%WHERE%
		ORDER BY r.`paidid` DESC
		';
	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query;

	$ret.='<form id="garage-master-form" class="sg-form" method="post" action="'.url('garage/info/*/appaid.new').'" data-checkvalid="true">'._NL;


	$tables = new Table();
	$tables->thead=array('docno -center'=>'เลขที่','date -paiddate'=>'วันที่','name'=>'บริษัท','money -totalprice'=>'จำนวนเงิน','icons -c1'=>'');

	$tables->rows[] = array(
		'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="paidno" value="'.R::Model('garage.nextno',$shopInfo->shopid,'ApPaid','PS')->nextNo.'" placeholder="รหัส" size="5" maxlength="10" readonly="readonly" />',
		'<input id="paiddate" class="form-text sg-datepicker -date -fill -require" type="text" name="paiddate" value="'.sg_date('d/m/Y').'" placeholder="31/12/'.date('Y').'" size="7" maxlength="10" />',
		'<input id="apid" type="hidden" name="apid" value="" /><input id="apname" class="form-text sg-autocomplete -fill -require" type="text" name="apname" value="" placeholder="ชื่อผู้จำหน่าย" size="7" data-query="'.url('garage/api/ap').'" data-altfld="apid" data-select="label" data-callback="loadApRcv" />',
		'<td colspan="2"><button class="btn -primary" type="submit"><i class="icon -material">add</i><span>สร้างใบจ่ายชำระหนี้</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/brand/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	$tables->rows[]='<tr><td colspan="5"><div id="qtlist"></div></td></tr>';

	$tables->rows[]='<header>';

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->paidno,
			sg_date($rs->paiddate,'d/m/ปปปป'),
			$rs->apname,
			$rs->grandtotal?number_format($rs->grandtotal,2):'0',
			'<a href="'.url('garage/appaid/'.$rs->paidid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>',
			'config' => array('class' => '-shop-'.$rs->shopShortName)
		);
	}
	$ret.=$tables->build();

	$ret.='</form>';

	$ret.='<script type="text/javascript">
	function loadApRcv($this,ui) {
		console.log(ui)
		$.get("'.url('garage/api/apmast').'",{id:ui.item.value,cond:"nopaid"},function(html) {
			$("#qtlist").html(html);
		});
	}
	$("body").on("click","#qtlist .item td:not(.checkbox)",function() {
		var $checkBox=$(this).closest("tr").find("input");
		$checkBox.prop("checked", !$checkBox.prop("checked"));
	});
	</script>';

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>