<?php
function project_network($self,$tpid,$action=NULL,$trid=NULL) {
	$projectInfo=R::Model('project.get',$tpid,'{data:"info"}');

	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$showButton=$isEdit && (!empty($self) || (empty($self) && $action=='showbutton'));

	//$ret.=$action.(empty($self)?'EMPTY':'NOT EMPTY').($showButton?' SHOW BUTTON':' HIDE BUTTON');

	R::view('project.toolbar',$self,'เครือข่ายเข้าร่วมโครงการ',NULL,$projectInfo);

	switch ($action) {
		case 'update':
			$data=(object)post();
			$data->tpid=$tpid;
			//$data->trid=$trid;
			if ($isEdit && $data->orgname) $result=__project_network_update($data);
			break;

		case 'delete':
			if ($isEdit && $tpid && $trid && SG\confirm()) {
				$result=__project_network_delete($tpid,$trid);
			}
			break;
		
		default:
			# code...
			break;
	}


	$optionsOrgType='ภาครัฐ,ท้องถิ่น,เอกชน,ภาควิชาการ,สื่อสารมวลชน,ภาคประชาสังคม,ชุมชน';

	$optionsOrgIssue='';
	$projectCategory=model::get_category('project:category','catid',ture);
	foreach ($projectCategory as $key => $item) {
		if ($item->process) $optionsOrgIssue.=$key.':'.$item->name.',';
	}
	$optionsOrgIssue=trim($optionsOrgIssue,',');

	$stmt='SELECT
					  `trid`, `tpid`
					, `detail1` `orgname`
					, `detail2` `orgtype`
					, `detail3` `orgissue`
					, `detail4` `leadername`
					, `text1` `address`
					, `text2` `orgdo`
					FROM %project_tr%
					WHERE `tpid`=:tpid AND `formid`="network"';
	$dbs=mydb::select($stmt,':tpid',$tpid); 

	$ret.='<form id="project-network-form" class="sg-form project-network-form" method="post" action="'.url('project/network/'.$tpid.'/update').'" data-rel="replace" data-checkvalid="true">';
	$tables = new Table();
	$tables->addClass('project-network-list');
	$tables->thead=array('ชื่อเครือข่าย','ประเภท','orgissue'=>'ประเด็น','ชื่อแกนนำ','สถานที่ติดต่อ','ศักยภาพเครือข่าย','center'=>'');
	foreach ($dbs->items as $rs) {
		if ($action=='edit' && $rs->trid==$trid) {
			$tables->rows[]=array(
												'<input type="hidden" name="trid" value="'.$rs->trid.'" />'
												.'<input class="form-text -fill -require" type="text" name="orgname" value="'.$rs->orgname.'">',
												'<select class="form-select -fill" name="orgtype">'.__project_network_options($optionsOrgType,$rs->orgtype).'</select>',
												'<select class="form-select" name="orgissue">'.__project_network_options($optionsOrgIssue,$rs->orgissue).'</select>',
												'<input class="form-text -fill" type="text" name="leadername" value="'.$rs->leadername.'">',
												'<input class="form-text -fill" type="text" name="address" value="'.$rs->address.'">',
												'<textarea class="form-textarea -fill" name="orgdo">'.$rs->orgdo.'</textarea>',
												'<button class="btn -primary -nowrap" type="submit"><i class="icon -save -white"></i><span>{tr:Save}</span></button>',
												);
		} else {
			$tables->rows[]=array(
												$rs->orgname,
												$rs->orgtype,
												$projectCategory[$rs->orgissue]->name,
												$rs->leadername,
												$rs->address,
												$rs->orgdo,
												$showButton?'<a class="sg-action" href="'.url('project/network/'.$tpid.'/edit/'.$rs->trid).'" data-rel="replace:#project-network-form"><i class="icon -edit"></i></a> <a class="sg-action" href="'.url('project/network/'.$tpid.'/delete/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบชื่อเครือข่ายนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>':'',
												);
		}
	}
	if ($showButton && $action!='edit') {
		$tables->rows[]=array(
											'<input class="form-text -fill -require" type="text" name="orgname" placeholder="ชื่อเครือข่าย">',
											'<select class="form-select" name="orgtype">'.__project_network_options($optionsOrgType).'</select>',
											'<select class="form-select" name="orgissue">'.__project_network_options($optionsOrgIssue).'</select>',
											'<input class="form-text -fill" type="text" name="leadername" placeholder="ชื่อ นามสกุลแกนนำ 1 ชื่อ">',
											'<input class="form-text -fill" type="text" name="address" placeholder="ที่อยู่">',
											'<textarea class="form-textarea -fill" name="orgdo" placeholder="ศักยภาพเครือข่าย"></textarea>',
											'<button class="btn -primary -nowrap" type="submit"><i class="icon -save -white"></i><span>{tr:Add}</span></button>',
											);
	}
	$ret.=$tables->build();
	$ret.=$result;

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='</form>';

	$ret.='<style type="text/css">
	.project-network-list td {width:16.6667%;}
	.project-network-list .col-orgissue .form-select {width:100%;}
	</style>';
	return $ret;
}

function __project_network_update($data) {
	if (empty($data->trid)) $data->trid=NULL;
	$data->formid='network';
	$data->part='happen';
	$data->uid=$data->modifyby=i()->uid;
	$data->created=$data->modified=date('U');
	$stmt='INSERT INTO %project_tr% 
						(`trid`, `tpid`, `uid`, `formid`, `part`, `detail1`, `detail2`, `detail3`, `detail4`, `text1`, `text2`)
						VALUES
						(:trid, :tpid, :uid, :formid, :part, :orgname, :orgtype, :orgissue, :leadername, :address, :orgdo)
						ON DUPLICATE KEY UPDATE
						  `detail1`=:orgname
						, `detail2`=:orgtype
						, `detail3`=:orgissue
						, `detail4`=:leadername
						, `text1`=:address
						, `text2`=:orgdo
						, `modifyby`=:modifyby
						, `modified`=:modified
						';
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query;

	//$ret.=print_o($data,'$data');
	return $ret;
}

function __project_network_delete($tpid,$trid) {
	$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid AND `tpid`=:tpid AND `formid`="network" LIMIT 1';
	mydb::query($stmt,':trid',$trid, ':tpid',$tpid);
	//$ret=mydb()->_query;
	return $ret;
}

function __project_network_options($options,$value) {
	$ret='';
	foreach (explode(',', $options) as $item) {
		if (strpos($item,':')) list($key,$item)=explode(':', $item);
		else $key=$item; 
		$ret.='<option value="'.$key.'" '.($item===$value?'selected="selected"':'').'>'.$item.'</option>';
	}
	return $ret;
}
?>