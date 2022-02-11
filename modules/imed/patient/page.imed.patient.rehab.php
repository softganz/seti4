<?php
/**
 * iMed care rehab
 *
 * @param Integer $psnId
 * @return String
 */
function imed_patient_rehab($self, $psnId = NULL) {
	$psnId = SG\getFirst($psnId,post('id'));

	$psnInfo = is_object($psnId) ? $psnId : R::Model('imed.patient.get', $psnId, '{}');
	$psnId = $psnInfo->psnId;

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูล');

	$action = post('action');

	$isAccess = $psnInfo->RIGHT & _IS_ACCESS;
	$isEdit = $psnInfo->RIGHT & _IS_EDITABLE;

	if (!$isAccess) return message('error',$psnInfo->error);



	$isRehab = mydb::select('SELECT * FROM %imed_care% WHERE `pid` = :pid AND `careid` IN ( :careid ) LIMIT 1', ':pid',$psnId, ':careid', 'SET:'._IMED_CARE_WAIT_REHAB.','._IMED_CARE_REHAB)->pid;

	if (!$isRehab) {
		$ret .= '<div class="-sg-text-center" style="padding: 32px 0;">'
			. '<p class="notify"><strong>'.$psnInfo->fullname.'</strong> ไม่ได้อยู่ในกลุ่มของผู้ป่วยรอการฟื้นฟู</p>'
			. ($isEdit ? '<p style="padding: 32px 0;">ต้องการเพิ่ม <strong>'.$psnInfo->fullname.'</strong> เข้าไว้ในกลุ่มผู้ป่วยรอการฟื้นฟูหรือไม่?</p><p><a class="sg-action btn -primary" href="'.url('imed/patient/'.$psnId.'/info/rehab.add').'" data-rel="#imed-app" data-ret="'.url('imed/patient/rehab/'.$psnId).'"><i class="icon -addbig -white"></i><span>เพิ่มเข้ากลุ่มผู้ป่วยรอการฟื้นฟู</span></a>' : '')
			. '</div>';
		return $ret;
	}


	$ui = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		if ($psnInfo->care->rehab) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/rehab.remove').'" data-rel="notify" data-done="load:#imed-app:'.url('imed/patient/'.$psnId.'/rehab').'" data-title="ลบชื่อออกจากกลุ่มผู้ป่วยรอการฟื้นฟู" data-confirm="ต้องการลบชื่อออกจากกลุ่มผู้ป่วยรอการฟื้นฟู กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ลบออกจากกลุ่มผู้ป่วยรอการฟื้นฟู</span></a>');
		}
	}
	if ($dropUi->count()) $ui->add(sg_dropbox($dropUi->build()));

	$ret .= '<header class="header"><h3>ข้อมูลผู้ป่วยรอการฟื้นฟู'.($psnInfo->info->dischar == 1 ? ' (เสียชีวิต)' : '').'</h3><nav class="nav -page -sg-text-right">'.$ui->build().'</nav></header>';


	include_once 'modules/imed/assets/qt.rehab.php';

	$inlineAttr['class'] = 'imed-qt -rehab ';

	if ($isEdit) {
		$inlineAttr['class'] .= 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('imed/edit/patient');
		$inlineAttr['data-psnid'] = $psnId;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}


	$ret.='<div id="imed-care-rehab" '.sg_implode_attr($inlineAttr).'>'._NL;



	foreach (explode("\n", $qtText) as $key) {
		if (preg_match('/^\t/', $key)) continue;
		$key=trim($key);
		if (empty($key)) continue;
		if (strpos($key,',')) {
			//$jStr='{"a":1,"b":2,"c":3,"d":4,"e":5}';
			$jStr='{'.$key.'}';
			$json=json_decode($jStr,true);

			if ($json) {
				$key=$json['key'];
				$json['label']=SG\getFirst($json['label'],$key);
				$json['group']='qt';
				unset($json['key']);
				$qt[$key]=$json;
			}
			//$ret.=$jStr.'<br />'.print_o($json,'$json');
		} else {
			$qt[$key]=array('label'=>$value,'type'=>'text','group'=>'qt','class'=>'w-5');
		}
	}

	foreach ($qtRadio as $key => $value) {
		$qt[$key]=array('label'=>$key, 'type'=>'radio','group'=>'qt','option'=>$value);
	}


	foreach ($qt as $key=>$value) {
		if ($value['section']) {
			$ret .= '<h3>'.$value['section'].'</h3>';
			continue;
		} else if ($value['subsection']) {
			$ret .= '<h4>'.$value['subsection'].'</h4>';
			continue;
		} else if ($value['text']) {
			$ret .= $value['text'];
			continue;
		}

		if ($value['question']) {
			$ret .= '<div><strong>'.$value['question'].'</strong></div>';
		}
		$ret .= '<div class="qt-item'.($value['subqt'] ? ' -subqt' : '').'">'
					. '<label class="label">'.SG\getFirst($value['label'],$key).'</label>'
					. '<span class="value">'
					. imed_model::qt($key,$qt,$psnInfo->qt,$isEdit)
					. '</span>'
					. '</div>';
	}



	$ret .= '<p>วันที่เข้าสู่ระบบ '.($psnInfo->info->created_date?sg_date($psnInfo->info->created_date,'ว ดดด ปปปป H:i:s'):'');

	$ret.='</div>';

	//$ret.=print_o($psnInfo,'$psnInfo');

	return $ret;
}
?>
