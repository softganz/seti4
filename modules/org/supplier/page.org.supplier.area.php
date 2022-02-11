<?php
function org_supplier_area($self) {
	$self->theme->title='องค์กรผู้ผลิต';
	$self->theme->sidebar=R::Page('org.supplier.menu');
	$stmt='SELECT p.`changwat`, p.`ampur`, COUNT(*) totalMember
				, codist.`distname`
				, copv.`provname`
			FROM %org_supplier% s
					LEFT JOIN %db_person% p USING(`psnid`)
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				GROUP BY `changwat`,`ampur`
				';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('จังหวัด','อำเภอ','amt'=>'สมาชิก');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->provname,$rs->distname,$rs->totalMember);
	}
	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>