<?php
/**
* Vew personal individual information
*
* @param Integer $psnId
* @return String
*/
function imed_patient_view($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');


	$ret = '';

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	//if (!$isAccess) return message('error',$psnInfo->error);

	if (!$isAccess) {
		unset($psnInfo->info->house, $psnInfo->info->village);
	}


	// Patient menu
	$ui = new Ui();
	$dropUi = new Ui();

	$backUrl = post('back');
	$patientUrl = callFromApp() ? '<a class="sg-action" href="'.url('imed/app/'.$psnId).'" data-webview="'.$psnInfo->fullname.'">' : '<a href="'.url('imed', ['pid' => $psnId]).'" target="_blank">';
	$ui->add($patientUrl.($isAccess ? '<i class="icon -material">accessible</i>' : '<i class="icon -doctor"></i>').'<span>'.($isAccess ? 'ข้อมูลผู้ป่วย' : 'เยี่ยมบ้าน').'</span></a>');

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="'.($backUrl ? url($backUrl) : 'javascript:void(0)').'" data-rel="back" data-width="640"><i class="icon -material">arrow_back</i></a></nav><h3>'.$psnInfo->fullname.'</h3><nav class="nav">'.$ui->build().'</nav></header>';


	$tables = new Table();
	$tables->colgroup = array(
		'label -nowrap -sg-text-right' => '{width:"30%"}',
		'value' => '{width: "70%"}'
	);

	$tables->rows[] = array(
		'ชื่อ-นามสกุล',
		$psnInfo->fullname.($isAccess && $psnInfo->info->nickname ? ' ('.$psnInfo->info->nickname.')' : '')
	);

	if ($isAccess) $tables->rows[]=array('หมายเลขบัตรประชาชน', $psnInfo->info->cid);
	$tables->rows[]=array('เพศ', $psnInfo->info->sex);

	if ($isAccess) {
		$tables->rows[] = array(
			'วันเกิด',
			$psnInfo->info->birth ? 
			sg_date($psnInfo->info->birth,'ว ดดด ปปปป')
			.($psnInfo->info->birth ? ' อายุ '.(date('Y')-sg_date($psnInfo->info->birth,'Y')).' ปี' : '')
			:
			''
		);
	} else {
		$tables->rows[] = array('อายุ',
					$psnInfo->info->birth ? ' อายุ '.(date('Y')-sg_date($psnInfo->info->birth,'Y')).' ปี' : ''
					);
	}

	if ($isAccess) {
		$tables->rows[] = array('ที่อยู่ปัจจุบัน',$psnInfo->info->address);
		if ($psnInfo->info->raddress) $tables->rows[] = array('ที่อยู่ตามทะเบียนบ้าน',$psnInfo->info->raddress);
		$tables->rows[]=array('โทรศัพท์',$psnInfo->info->phone);
	} else {
		$tables->rows[] = array('ที่อยู่ปัจจุบัน', SG\implode_address($psnInfo->info));
	}


	$ret .= $tables->build();


	$ret.='<p><small>สร้างโดย '.$psnInfo->info->created_by.' เมื่อ '.sg_date($psnInfo->info->created_date,'ว ดด ปปปป').($psnInfo->info->modify?' แก้ไขล่าสุดโดย '.$psnInfo->info->modify_by.' เมื่อ '.sg_date($psnInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small></p>';
	//$ret.=print_o($psnInfo,'$psnInfo');

	return $ret;
}
?>