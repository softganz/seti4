<?php
/**
* Create join recieve
* Created 2019-05-16
* Modify  2019-07-29
*
* @param Object $self
* @param Object $projectInfo
* @param Int $psnId
* @return String
*/

$debug = true;

function project_join_rcv_create($self, $projectInfo, $psnId = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	$tripKMrate = 4;


	if (!$isEdit) return message('error', 'Access Denied');

	if ($psnId) {
		$joinInfo = R::Model('project.join.get', array('calid' => $calId, 'psnid' => $psnId) );
	}

	$codeRef = array();
	foreach (mydb::select('SELECT * FROM %tag% WHERE `taggroup` = "project:expcode" AND `liststyle` IS NOT NULL')->items as $rs) {
	 	$codeRef[$rs->liststyle] = $rs->catid;
	}
	if (empty($codeRef)) return message('error', 'ERROR: ยังไม่ได้กำหนดรหัสค่าใช้จ่ายอ้างอิง');


	// View Model
	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>สร้างใบสำคัญรับเงิน'.(!$psnId ? 'โดยไม่ลงทะเบียน' : '').'</h3></header>';

	$form = new Form('rcv', url('project/join/'.$tpid.'/'.$calId.'/rcv.save/'.$psnId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'box');
	$form->addData('callback', 'projectJoinRcvCreate');

	$paidName = trim($joinInfo->prename.' '.trim($joinInfo->firstname).' '.trim($joinInfo->lastname));
	$paidAddress = SG\implode_address($joinInfo);

	$form->addField(
		'paiddate',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'class' => 'sg-datepicker -fill',
			'value' => $projectInfo->doingInfo->paiddocdate ? sg_date($projectInfo->doingInfo->paiddocdate,'d/m/Y') : date('d/m/Y'),
		)
	);

	$form->addField(
		'paidname',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้รับเงิน',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($paidName),
		)
	);

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'เลขประจำบัตรประชาชน',
			'class' => '-fill',
			'readonly' => $psnId ? true : '',
			'maxlength' => 13,
			'value' => htmlspecialchars($joinInfo->cid),
			)
	);

	$form->addField(
		'address',
		array(
			'type' => 'text',
			'label' => 'ที่อยู่',
			'class' => '-fill',
			'value' => htmlspecialchars($paidAddress),
		)
	);

	$rcvFormList = array('' => 'Default');
	foreach ($projectInfo->doingInfo->options->rcvForms as $key => $value) {
		$rcvFormList[$key] = $value->title;
	}

	if (count($rcvFormList) > 1) {
		$form->addField(
			'formid',
			array(
				'type' => 'select',
				'label' => 'แบบฟอร์ม:',
				'class' => '-fill',
				'options' => $rcvFormList,
			)
		);
	}

	$tables = new Table();
	$tables->thead=array('รายการ','amt'=>'จำนวนเงิน (บาท)');

	$tables->rows[] = array(
		'ค่าตอบแทนวิทยากร <input type="hidden" name="tr['.$codeRef['วิทยากร'].'][catid]" value="'.$codeRef['วิทยากร'].'" /><input class="form-text -fill" type="text" name="tr['.$codeRef['วิทยากร'].'][detail]" placeholder="ระบุรายละเอียด" />',
		'<input class="form-text -money" type="text" name="tr['.$codeRef['วิทยากร'].'][amt]" size="8" placeholder="0.00" />'
	);

	$restPrice = 0;
	if ($joinInfo->withdrawrest > 0) {
		if ($joinInfo->rest == 'พักเดี่ยว') {
			$restPrice = $joinInfo->hotelprice * $joinInfo->hotelnight;
		} else if ($joinInfo->rest == 'พักคู่') {
			$restPrice = $joinInfo->hotelprice * $joinInfo->hotelnight;
		}

		$totalPrice = $tripPrice + $restPrice;

		$tables->rows[] = array(
			'ค่าที่พัก ('.$joinInfo->rest.($joinInfo->hotelnight ? ' '.$joinInfo->hotelnight.' คืน' : '').')'
			. '<input type="hidden" name="tr['.$codeRef['ที่พัก'].'][catid]" value="'.$codeRef['ที่พัก'].'" /><input class="form-text -fill" type="text" name="tr['.$codeRef['ที่พัก'].'][detail]" value="'.($joinInfo->hotelmate ? 'พักคู่ '.htmlspecialchars($joinInfo->hotelmate) : '').'" placeholder="ระบุรายละเอียด" />',
			'<input class="form-text -money" type="text" name="tr['.$codeRef['ที่พัก'].'][amt]" size="8" placeholder="0.00" value="'.number_format($restPrice,2).'" />'
		);
	}


	$tripPrice = 0;

	$tripbyList = explode(',', $joinInfo->tripby);
	foreach ($tripbyList as $tripBy) {
		$tripId = NULL;
		$tripPrice = 0;
		$tripDetail = '';
		switch ($tripBy) {
			case 'รถยนต์ส่วนตัว':
				$tripId = $codeRef['รถยนต์'];
				$tripPrice = $joinInfo->fixprice ? $joinInfo->fixprice : $joinInfo->distance * 2 * $tripKMrate;
				$tripDetail = ($joinInfo->carregist ? 'ทะเบียน '.$joinInfo->carregist.' ' : '')
					. ($joinInfo->carregprov ? ' '.$joinInfo->carregprov.' ' : '')
					. 'เดินทางจาก อ.'.$joinInfo->ampurName.' จ.'.$joinInfo->changwatName.($projectInfo->doingInfo->areacode ? ' ถึง อ.'.$projectInfo->doingInfo->doAmpur.' จ.'.$projectInfo->doingInfo->doChangwat : '').' '
					. ($joinInfo->fixprice ? ' (เหมาจ่าย)' : '')
					. ' ระยะทาง '.($joinInfo->distance).' กม. x 2 เที่ยว (ไป-กลับ)';
				break;
			case 'เดินทางร่วม':
				break;
			case 'รถโดยสารประจำทาง':
				$tripId = $codeRef['รถโดยสาร'];
				$tripPrice = $joinInfo->busprice;
				$tripDetail = 'ประเภท ... จากจังหวัด '.$joinInfo->changwatName.' (ไป-กลับ)';
				break;
			case 'รถรับจ้าง':
				$tripId = $codeRef['รถรับจ้าง'];
				$tripPrice = $joinInfo->taxiprice;
				$tripDetail = '';
				break;
			case 'เครื่องบิน':
				$tripId = $codeRef['เครื่องบิน'];
				$tripPrice = $joinInfo->airprice;
				$tripDetail = 'เที่ยวไป สายการบิน '.$joinInfo->airgoline.' จาก '.$joinInfo->airgofrom.' ถึง '.$joinInfo->airgoto.' เที่ยวกลับ สายการบิน '.$joinInfo->airretline.' จาก '.$joinInfo->airretfrom.' ถึง '.$joinInfo->airretto;
				break;
			case 'รถไฟ':
				$tripId = $codeRef['รถไฟ'];
				$tripPrice = $joinInfo->trainprice;
				$tripDetail = '';
				break;
			case 'รถตู้เช่า':
				$tripId = $codeRef['รถเช่า'];
				$tripPrice = $joinInfo->rentprice;
				$tripDetail = 'ทะเบียนรถ '.$joinInfo->rentregist.' รายชื่อผู้โดยสาร '.$joinInfo->rentpassenger;
				break;
			case 'ค่าเดินทางเหมาจ่ายในพื้นที่':
				$tripId = $codeRef['เหมาจ่าย'];
				$tripPrice = $joinInfo->localprice;
				$tripDetail = 'ค่าเดินทางเหมาจ่ายในพื้นที่';
				break;
			case 'อื่นๆ':
				$tripId = $codeRef['รถอื่น'];
				$tripPrice = $joinInfo->tripotherprice;
				$tripDetail = $joinInfo->tripotherby;
				break;
			case 'ไม่เบิกค่าเดินทาง':
				break;
			default:
				$tripId = NULL;
				$tripPrice = 0;
				$tripDetail = '';
		}

		$totalPrice += $tripPrice;
		if ($tripId) {
			$tables->rows[] = array(
				'ค่าเดินทาง - '.$tripBy
				. '<input type="hidden" name="tr['.$tripId.'][catid]" value="'.$tripId.'" /><input class="form-text -fill" type="text" name="tr['.$tripId.'][detail]" placeholder="ระบุรายละเอียด" value="'.htmlspecialchars($tripDetail).'" />',
				'<input class="form-text -money" type="text" name="tr['.$tripId.'][amt]" size="8" placeholder="0.00" value="'.number_format($tripPrice,2).'" />'
			);
		}

	}




	$tables->rows[] = array(
		'ค่าใช้จ่ายอื่น ๆ <input type="hidden" name="tr['.$codeRef['อื่นๆ'].'][catid]" value="'.$codeRef['อื่นๆ'].'" /><input class="form-text -fill" type="text" name="tr['.$codeRef['อื่นๆ'].'][detail]" placeholder="ระบุรายละเอียด" />',
		'<input class="form-text -money" type="text" name="tr['.$codeRef['อื่นๆ'].'][amt]" size="8" placeholder="0.00" value="0.00" />'
	);

	$tables->tfoot[]=array('('.sg_money2bath($totalPrice,2) .')',number_format($totalPrice,2));

	/*
	foreach ($dopaidInfo->trans as $joinInfo) {
		if ($isEdit) {
			$menu='<a href="'.url('project/money/'.$tpid.'/dopaidedittr/'.$joinInfo->doptrid).'"><i class="icon -edit -gray"></i></a>';
			$menu.='<a class="sg-action" href="'.url('project/money/'.$tpid.'/dopaiddeltr/'.$joinInfo->doptrid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';
		};
		$tables->rows[]=array(
			$joinInfo->name.($joinInfo->detail?'<div class="bill-trdetail">'.nl2br($joinInfo->detail).'</div>':''),
			number_format($joinInfo->amt,2),
			$menu,
		);
	}
	$tables->tfoot[]=array('('.sg_money2bath($dopaidInfo->total,2) .')',number_format($dopaidInfo->total,2),'');
	*/

	$form->addText($tables->build());

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -addbig -white"></i><span>สร้างใบสำคัญรับเงิน</span>',
			'pretext' => '<a class="sg-action btn -link" href="'.url('project/join/'.$tpid.'/'.$calId.'/money').'" data-rel="close"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></a>',
			'containerclass' => '-sg-text-right',
		)
	);

	$form->addText('หมายเหตุ : ค่าใช้จ่ายอื่น ๆ สามารถเพิ่มเติมได้หลังจากสร้างใบสำคัญรับเงินเสร็จเรียบร้อยแล้ว');
	$ret .= $form->build();


	//$ret .= print_o($joinInfo, '$joinInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret.='<style type="text/css">
	.module-project .box {background-color: #fff;}
	.module-project .box h3 {text-align: center; background-color: transparent; color:#333;}
	.bill-trdetail {color:#666; font-size: 0.9em;}

	@media print {
		.module-project .box {margin:0; padding:0; box-shadow:none; border:none;}
		.module-project .box h3 {color:#000; background-color:#fff;}
		.module-project .-billsign {position:absolute; bottom:0.5cm;}
		.module-project .-footermsg {margin-bottom:1cm;}
		.module-project .bill-trdetail {color:#000; font-size: 0.9em;}
	}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinRcvCreate() {
		$.post(window.location.href, function(html) {
			$("#main").html(html)
		});
	}
	</script>';
	return $ret;
}
?>