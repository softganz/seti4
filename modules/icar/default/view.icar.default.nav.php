<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_icar_default_nav($carInfo, $options) {
	$shop = icar_model::get_my_shop();

	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;


	$ret = '';

	//$ret .= print_o($carInfo,'$carInfo');

	if ($shop->shopid || $isAdmin) {
		$ui = new Ui(NULL,'ui-nav -sg-text-center');
		$ui->add('<a href="'.url('icar/my').'" title="หน้าร้าน"><i class="icon -material">home</i>{tr:Shop}</a>');
		$ui->add('<a href="'.url('icar/buy').'" title="บันทึกการซื้อรถ"><i class="icon -material">note_add</i>{tr:Buy}</a>');

		$ui->add('<sep>');

		$ui->add('<a href="'.url('icar/my/list/table',array('shop'=>$_REQUEST['shop'],'q'=>$_REQUEST['q'])).'" title="รายการรถ"><i class="icon -material">view_list</i>Table</a>');
		$ui->add('<a href="'.url('icar/my/list/thumbnail',array('shop'=>$_REQUEST['shop'],'q'=>$_REQUEST['q'])).'" title="รายการรถ" class="icon-thumbnail"><i class="icon -material">view_module</i>Thumb</a>');
		$ui->add('<a href="'.url('icar/my/list/icons',array('shop'=>$_REQUEST['shop'],'q'=>$_REQUEST['q'])).'" title="รายการรถ" class="icon-icons"><i class="icon -material">dashboard</i>Icons</a>');
		if ($carInfo) {
			$ui->add('<sep>');
			$ui->add('<a class="sg-action" href="'.url('icar/view/info/'.$carId).'" data-rel="#info"><i class="icon -material">assignment</i>{tr:Info}</a>');
			if ($isAdmin || $isShopOfficer || $isShopPartner) {
				//$ui->add('<a class="sg-action" href="'.url('icar/view/sale/'.$carId).'" data-rel="#info">{tr:Buy}-{tr:Sale}</a>');
				$ui->add('<a class="sg-action" href="'.url('icar/view/calculate/'.$carId).'" data-rel="#info"><i class="icon -material">money</i>{tr:Calculate}</a>');
			}
			$ui->add('<a class="sg-action" href="'.url('icar/view/photo/'.$carId).'" data-rel="#info"><i class="icon -material">image</i>{tr:Photo}</a>');
		}

		if ($options->showPrint) {
			$ui->add('<sep>');
			$ui->add('<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>');
		}

		$ret .= $ui->build();

		$dropboxUi = new Ui(NULL,'ui-dropbox');

		if ($carInfo->tpid && (($shop->shopid && $carInfo->shopid==$shop->shopid) || user_access('administrator icars'))) {
			$isEdit = empty($carInfo->sold);
			if ($carInfo->sold) {
				$dropboxUi->add('<a href="'.url('icar/'.$carInfo->tpid).'" title="ดูรายละเอียดรายการบันทึก"><i class="icon -material">viewdoc</i><span>ดูรายการบันทึก</span></a>');
				$dropboxUi->add('<a href="'.url('icar/view/closejob/'.$carInfo->tpid,'lock=no').'" title="ยกเลิกการปิดการขายรถ"><i class="icon -material">cancel</i><span>ยกเลิกการปิดการขายรถ</span></a>');
			} else {
				$dropboxUi->add('<a class="sg-action" href="'.url('icar/'.$carInfo->tpid).'" title="ลงบันทึกรายการต้นทุน,รับ-จ่าย,เงินดาวน์"><i class="icon -material">edit</i><span>ลงบันทึกรายการ</span></a>');
				$dropboxUi->add('<a class="sg-action" href="'.url('icar/view/setprice/'.$carInfo->tpid).'" data-rel="#inputform" title="กำหนดราคาขายหน้าร้าน"><i class="icon -material">attach_money</i><span>กำหนดราคาขายหน้าร้าน</span></a>');
				$dropboxUi->add('<a class="sg-action" href="'.url('icar/view/saleform/'.$carInfo->tpid).'" data-rel="#inputform" title="บันทึกราคาขายและเงินดาวน์"><i class="icon -material">attach_money</i><span>บันทึกราคาขาย</span></a>');
				$dropboxUi->add('<a class="" href="'.url('icar/view/closejob/'.$carInfo->tpid,'lock=yes').'" title="ปิดการขายและลงบันทึกรายการ"><i class="icon -material">done_all</i><span>ปิดการขาย</span></a>');
				$dropboxUi->add('<sep>');
				$dropboxUi->add('<a class="sg-action" href="'.url('icar/'.$carInfo->tpid.'/delete').'" title="ลบรายการ" data-title="ลบต้องมูลรถ" data-confirm="ต้องการลบข้อมูลรถพร้อมทั้งรายการที่บันทึกไว้ทั้งหมด กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบข้อมูลรถ</span></a>');
				
				if ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER'))) {
					$dropboxUi->add('<sep>');
					$dropboxUi->add('<a href="'.url('icar/history/'.$carInfo->tpid).'"><i class="icon -material">view_list</i><span>ประวัติการแก้ไขข้อมูล</span></a>');
				}
			}
			$dropboxUi->add('<sep>');
		}

		$dropboxUi->add('<a href="'.url('icar/my','sold=yes').'"><i class="icon -material">assessment</i><span>รายการรถที่ขายแล้ว</span></a>');
		$dropboxUi->add('<a href="'.url('icar/report').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>');
		$dropboxUi->add('<a href="'.url('icar/setting').'" title="Setting" class="icon-setting"><i class="icon -material">settings</i><span>Setting</span></a>');

		$ret .= sg_dropbox($dropboxUi->build('ul'),'{class:"leftside -atright"}');
	} else {
		$ui = new Ui(NULL,'ui-nav -sg-text-center');
		$ui->add('<a href="'.url('icar').'" title="หน้าร้าน"><i class="icon -material">home</i>{tr:Home}</a>');
		$ui->add('<a href="'.url('icar',array('s'=>'table')).'" title="รายการรถ"><i class="icon -material">view_list</i>Table</a>');
		$ui->add('<a href="'.url('icar',array('s'=>'thumbnail')).'" title="รายการรถ" class="icon-thumbnail"><i class="icon -material">view_module</i>Thumb</a>');
		$ui->add('<a href="'.url('icar',array('s'=>'icons')).'" title="รายการรถ" class="icon-icons"><i class="icon -material">dashboard</i>Icons</a>');
		$ret .= $ui->build();
	}

	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>