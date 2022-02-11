<?php
function org_supplier_org($self) {
	$self->theme->title='องค์กรผู้ผลิต';
	$self->theme->sidebar=R::Page('org.supplier.menu');
	$stmt='SELECT s.`orgid`, o.`name`, COUNT(*) totalMember
					, o.`address`
				FROM %org_supplier% s
				LEFT JOIN %db_org% o USING(`orgid`)
				GROUP BY `orgid`
				';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('กลุ่ม/องค์กร','ที่อยู่','amt'=>'สมาชิก');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->name,$rs->address,$rs->totalMember);
	}
	$ret .= $tables->build();
	return $ret;
}
?>