<?php
function org_supplier($self,$psnid = NULL) {
	$self->theme->title='ข้อมูลผู้ผลิต';
	$self->theme->sidebar=R::Page('org.supplier.menu');

	$isEdit=true;
	$ret='';
	if ($psnid) {
		switch (post('action')) {
			case 'remove' :
				mydb::query('DELETE FROM %org_supplier% WHERE `psnid`=:psnid LIMIT 1',':psnid',$psnid);
				return 'ลบข้อมูลเรียบร้อย';
				break;
			default :
				$ret.=__org_supplier_info($psnid);
				return $ret;
				break;
		}
	}
	$stmt='SELECT s.*,p.*, o.`name` orgName
					FROM %org_supplier% s
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %db_org% o USING(`orgid`)
					ORDER BY CONVERT(CONCAT(p.`name`,`lname`) USING tis620) ASC
					';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="24" height="24" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" />',
											'<a class="sg-action" href="'.url('org/supplier/'.$rs->psnid).'" data-rel="box">'.$rs->name.' '.$rs->lname.'</a>',
											$rs->orgName,
											$rs->phone,
											$isEdit ? '<span class="sg-dropbox hover left -no-print"><a href="#"><i class="icon icon-dropdown">'._CHAR_3DOTS.'</i></a><div class="-hidden"><ul><li><a class="sg-action" href="'.url('org/supplier/'.$rs->psnid).'" data-rel="box">รายละเอียด</a></li><li><a href="'.url('org/supplier/add/'.$rs->psnid,array('action'=>'edit','psnid'=>$rs->psnid)).'" class="" title="แก้ไข">แก้ไข</a></li><li><a class="sg-action" href="'.url('org/supplier/'.$rs->psnid,array('action'=>'remove')).'" data-confirm="คุณต้องการลบรายการนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr">ลบ</a></li></ul></div></span>':'',
											);
	}
	$ret .= $tables->build();
	return $ret;
}

function __org_supplier_info($psnid) {
	$ret.='<a class="back sg-action" href="#close">&#10094;</a><h3 class="title" data-rel="box">ข้อมูลผู้ผลิต</h3>';
	$stmt='SELECT p.`psnid`, p.`cid`, p.`prename`, CONCAT(p.`name`," ",p.`lname`) fullname,
			s.`argtype`,
			o.`name` orgName,
			p.`phone`, p.`email`,
			p.`house`, p.`village`,
			IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname,
			IFNULL(codist.`distname`,p.`t_ampur`) distname,
			IFNULL(copv.`provname`,p.`t_changwat`) provname,
			CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) areacode
		FROM %db_person% p
			LEFT JOIN %org_supplier% s USING(`psnid`)
			LEFT JOIN %db_org% o USING (`orgid`)
				LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
		WHERE p.`psnid`=:psnid
		LIMIT 1';
	$rs=mydb::select($stmt,':psnid',$psnid);
	$rs->address=SG\implode_address($rs);

	$tables = new Table();
	$tables->rows[]=array('ประเภทเกษตรกร',$rs->argtype);
	$tables->rows[]=array('ชื่อเกษตรกร',$rs->fullname);
	$tables->rows[]=array('กลุ่ม',$rs->orgName);
	$tables->rows[]=array('ที่อยู่',$rs->address);
	$tables->rows[]=array('โทรศัพท์',$rs->phone);
	$tables->rows[]=array('อีเมล์',$rs->email);
	$ret .= $tables->build();
	return $ret;
}
?>