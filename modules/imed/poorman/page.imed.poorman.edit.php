<?php
function imed_poorman_edit($self,$qtref=NULL,$action=NULL) {
	$qtInfo=R::Model('imed.qt.get',$qtref);
	$data=(object)post('data');
	//$ret.=print_o($qtInfo,'$qtInfo');
	//$ret.=print_o(post(),'post()');
	switch ($action) {
		case 'cancel':
			if ($data->{'qt:PROOF.CANCEL.MSG'}) {
				// Save comment and cancel form
				$stmt='UPDATE %qtmast% SET `qtstatus`=:qtstatus WHERE `qtref`=:qtref LIMIT 1';
				mydb::query($stmt, ':qtref',$qtref, ':qtstatus',_CANCEL);
				$data->qtref=$qtref;
				$data->psnid=$qtInfo->psnid;
				//$ret.=print_o($data,'$data');
				$result=R::Model('imed.poorman.save',$data);
				//$ret.=print_o($result,'$result');
				$ret.='ยกเลิกแบบสอบถาม '.$qtref.' เรียบร้อย @'.date('Y-m-d H:i:s');
			} else {
				$ret.=__imed_poorman_edit_cancel($qtref,$qtInfo);
			}
			break;

		default:
			# code...
			break;
	}
	return $ret;
}

function __imed_poorman_edit_cancel($qtref,$qtInfo) {
	$ret.='<h2 class="title -box">ยกเลิกแบบสอบถาม</h2>';

	$form=new Form('data',url(q()),NULL,'sg-form');
	$form->addData('checkValid',true);
	$form->addData('complete','closebox');
	if (_AJAX) $form->addData('rel','refresh:#main');

	$form->addField(
						'qt:PROOF.CANCEL.MSG',
						array(
							'type'=>'textarea',
							'label'=>'เหตุผลที่ต้องการยกเลิกแบบสอบถาม',
							'class'=>'-fill',
							'require'=>true,
							'value'=>$qtInfo->tr['PROOF.CANCEL.MSG']->value,
							)
						);
	$form->addField(
				'cancel',
				array(
					'type'=>'button',
					'name'=>'cencel',
					'value'=>'<i class="icon -save -white"></i><span>ยืนยันการยกเลิกแบบสอบถาม</span>',
					)
				);
	$ret.=$form->build();
	return $ret;
}
?>