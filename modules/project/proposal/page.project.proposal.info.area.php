<?php
/**
* Project Proposal Area Management
* Created 2019-02-28
* Modify  2019-09-22
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_proposal_info_area($self, $proposalInfo, $action = NULL) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $proposalInfo->RIGHT & _IS_EDITABLE;
	$isEdit = $isEditable && $action == 'edit';

	$ret = '<div id="project-proposal-area-wrapper">';

	//$ret.=print_o($proposalInfo,'$proposalInfo');

	$provinceAreaList[0]='ทั้งประเทศ';
	$provinceAreaList[1]='ภาคกลาง';
	$provinceAreaList[3]='ภาคตะวันออกเฉียงเหนือ';
	$provinceAreaList[5]='ภาคเหนือ';
	$provinceAreaList[8]='ภาคใต้';

	$ret.='<form class="sg-form -area" method="post" action="'.url('project/proposal/'.$tpid.'/info/area.save').'" data-rel="notify" data-done="load->replace:#project-proposal-area-wrapper:'.url('project/proposal/'.$tpid.'/info.area/edit').'">';
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
	$tables->addClass('project-proposal-area');
	$tables->thead=array('จังหวัด','อำเภอ','ตำบล','area -hover-parent'=>'ลักษณะพื้นที่');

	$stmt='SELECT
					  p.*
					, c.`provname` `changwatName`
					, d.`distname` `ampurName`
					, s.`subdistname` `tambonName`
					FROM %project_prov% p
						LEFT JOIN %co_province% c ON c.`provid`=p.`changwat`
						LEFT JOIN %co_district% d ON d.`distid`=CONCAT(p.`changwat`,p.`ampur`)
						LEFT JOIN %co_subdistrict% s ON s.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					WHERE `tpid`=:tpid AND `tagname` = :tagname';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':tagname', _PROPOSAL_TAGNAME);


	foreach ($dbs->items as $rs) {
		$row = array(
			SG\getFirst($rs->changwatName,$provinceAreaList[$rs->changwat]),
			$rs->ampurName,
			$rs->tambonName,
			$rs->areatype
			. '<nav class="nav iconset -hover -no-print"><a id="project-info-area-pin-link-'.$rs->autoid.'" class="sg-action" href="'.url('project/'.$tpid.'*/info.map/'.$rs->autoid).'" data-rel="box" data-width="640" data-class-name="-map"><i class="icon -material '.($rs->location ? '-green' : '-gray').'">place</i></a> '
			. ($isEdit ? '<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info/area.delete/'.$rs->autoid).'" data-rel="notify" data-done="remove:parent tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
			. '</nav>',
			''
		);

		$tables->rows[]=$row;
	}

	if ($isEdit) {
		$tables->rows[]=array(
			'<div class="form-item"><select id="changwat" class="form-select -fill -showbtn sg-changwat" name="changwat"><option value="">** เลือกจังหวัด **</option>'.$provinceOptions.'</select></div>',
			'<div class="form-item" style="display:none;"><select id="ampur" class="form-select -fill -hidden sg-ampur" name="ampur" style="display:none;"><option value="">** เลือกอำเภอ **</option></select></div>',
			'<div class="form-item" style="display:none;"><select id="tambon" class="form-select -fill sg-tambon -hidden" name="tambon" style="display:none;"><option value="">** เลือกตำบล **</option></select><select id="village" class="form-select -hidden" style="display:none;"></select></div>',
			'<div class="form-item" style="display:none;"><select class="form-select -fill" name="areatype"><option value="">** เลือกลักษณะพื้นที่ **</option>'.$areaTypeOptions.'</select></div>'
			.'<div class="form-item -sg-text-right" style="display:none;"><button class="btn -primary -nowrap" type="submit"><i class="icon -material">add_circle</i><span>เพิ่มพื้นที่</span></button></div>',
			'config'=>array('class'=>'-no-print')
			);
	}
	$ret.=$tables->build();
	$ret.='</form>';
	//$ret.=print_o(post(),'post()');

	$ret .= '<!-- project-proposal-area-wrapper -->';

	$ret .= '<style type="text/css">
	.project-proposal-area td {width: 25%;}
	</style>';

	$ret .= '</div>';

	return $ret;
}
?>