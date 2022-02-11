<?php
function project_develop_area($self,$tpid,$action=NULL,$trid=NULL) {
	$tagname='develop';
	$devInfo=R::Model('project.develop.get',$tpid);
	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;

	$ret='';

	//$ret.=print_o($devInfo,'$devInfo');

	if ($isEdit && $action=='delete' && $trid) {
		$stmt='DELETE FROM %project_prov% WHERE `tpid`=:tpid AND `autoid`=:trid AND `tagname`="develop" LIMIT 1';
		mydb::query($stmt,':tpid',$tpid, ':trid',$trid, ':tagname',$tagname);
	}

	if ($isEdit && post('changwat')!='') {
		$data=(object)post();
		$data->tagname=$tagname;
		$stmt='INSERT INTO %project_prov%
					(`tpid`,`tagname`,`changwat`,`ampur`,`tambon`,`areatype`)
					VALUES
					(:tpid,:tagname,:changwat,:ampur,:tambon,:areatype)';
		mydb::query($stmt,':tpid',$tpid,$data);
		//$ret.=mydb()->_query;
	}

	$provinceAreaList[0]='ทั้งประเทศ';
	$provinceAreaList[1]='ภาคกลาง';
	$provinceAreaList[3]='ภาคตะวันออกเฉียงเหนือ';
	$provinceAreaList[5]='ภาคเหนือ';
	$provinceAreaList[8]='ภาคใต้';

	$ret.='<form class="sg-form" method="post" action="'.url('project/develop/area/'.$tpid).'" data-rel="parent">';
	$provinceOptions='';
	$provinceOptions.='<optgroup label="----------"></optgroup>';
	$provinceOptions.='<option value="0">++ ทั้งประเทศ</option>';
	$provinceOptions.='<optgroup label="ระดับภาค">';
	$provinceOptions.='<option value="1">++ ภาคกลาง</option>';
	$provinceOptions.='<option value="3">++ ภาคตะวันออกเฉียงเหนือ</option>';
	$provinceOptions.='<option value="5">++ ภาคเหนือ</option>';
	$provinceOptions.='<option value="8">++ ภาคใต้</option>';
	$provinceOptions.='</optgroup>';
	$provinceOptions.='<optgroup label="ระดับจังหวัด/อำเภอ/ตำบล">';
	$stmt='SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$provinceOptions.='<option value="'.$rs->provid.'">'.$rs->provname.'</option>';
	}
	$provinceOptions.='</optgroup>';

	$areaTypeOptions='';
	$areaTypeOptionsList=array('ในเมือง','ชนบท','ชานเมือง','พื้นที่เฉพาะ:ลุ่มน้ำ','พื้นที่เฉพาะ:ชายแดน','พื้นที่เฉพาะ:พื้นที่สูง','พื้นที่เฉพาะ:ชุมชนแออัด','อื่น ๆ');
	foreach ($areaTypeOptionsList as $item) {
		$areaTypeOptions.='<option value="'.$item.'">'.$item.'</option>';
	}


	$tables = new Table();
	$tables->addClass('project-develop-area');
	$tables->thead=array('จังหวัด','อำเภอ','ตำบล', 'area' => 'ลักษณะพื้นที่');
	if ($isEdit) $tables->thead['icons -hover-parent']='';

	$stmt='SELECT
					  p.*
					, c.`provname` `changwatName`
					, d.`distname` `ampurName`
					, s.`subdistname` `tambonName`
					FROM %project_prov% p
						LEFT JOIN %co_province% c ON c.`provid`=p.`changwat`
						LEFT JOIN %co_district% d ON d.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% s ON s.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					WHERE `tpid`=:tpid AND `tagname`=:tagname';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':tagname',$tagname);


	foreach ($dbs->items as $rs) {
		$row=array(
						SG\getFirst($rs->changwatName,$provinceAreaList[$rs->changwat]),
						$rs->ampurName,
						$rs->tambonName,
						$rs->areatype,
						);
		if ($isEdit) $row[]='<nav class="nav iconset -hover"><a class="sg-action" href="'.url('project/develop/area/'.$tpid.'/delete/'.$rs->autoid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>';
		$tables->rows[]=$row;
	}

	if ($isEdit) {
		$tables->rows[]=array(
			'<div class="form-item"><select id="changwat" class="sg-changwat form-select -fill -showbtn" name="changwat"><option value="">** เลือกจังหวัด **</option>'.$provinceOptions.'</select></div>',
			'<div class="form-item" style="display:none;"><select id="ampur" class="sg-ampur form-select -fill -hidden" name="ampur" style="display:none;"><option value="">** เลือกอำเภอ **</option></select></div>',
			'<div class="form-item" style="display:none;"><select id="tambon" class="sg-tambon form-select -fill -hidden" name="tambon" style="display:none;"><option value="">** เลือกตำบล **</option></select><select id="village" class="sg-village form-select -hidden" style="display:none;"></select></div>',
			'<div class="form-item" style="display:none;"><select class="form-select -fill" name="areatype"><option value="">** เลือกลักษณะพื้นที่ **</option>'.$areaTypeOptions.'</select></div>',
			'<div class="form-item" style="display:none;"><button class="btn -nowrap" type="submit"><i class="icon -add"></i><span>เพิ่มพื้นที่</span></button></div>',
			'config'=>array('class'=>'-no-print')
			);
	}
	$ret.=$tables->build();
	$ret.='</form>';
	//$ret.=print_o(post(),'post()');

	return $ret;
}
?>