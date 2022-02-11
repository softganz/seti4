<?php
function project_money_createpaid($self,$projectInfo,$trid) {
	$tpid=$projectInfo->tpid;
	$psnid=post('psnid');
	$doingInfo=R::Model('org.doing.get',$trid,'{data: "info"}');
	$personInfo=R::Model('org.person.get',$psnid,'{debug:true}');

	if (empty($doingInfo->doid)) return 'ERROR';

	$data->doid=$trid;
	$data->psnid=$psnid;
	$data->paiddate=date('Y/m/d');
	$data->agrno=$projectInfo->info->agrno;
	$data->projecttitle=$projectInfo->info->title;
	$data->address=SG\implode_address($personInfo->info);
	$data->paidname='';

	R::Model('org.dopaid.save',$data);

	if ($data->_error) {
		$ret.='ERROR : '.$data->_error;
	} else {
		location('project/money/'.$tpid.'/dopaidview/'.$data->dopid);
	}

	//$ret.=print_o($data,'$data');
	//$ret.=print_o($personInfo,'$personInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>