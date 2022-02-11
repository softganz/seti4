<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_view($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	if (!$carInfo) return message('error','ไม่มีข้อมูล');

	$newid = NULL;

	$self->theme->title = $carInfo->brandname.' <span>'.$carInfo->model.' , '.$carInfo->plate.'</span>';

	//$ret .= print_o($carInfo, '$carInfo');

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = ($isAdmin || $isShopOfficer);
	//$isEdit = ($isAdmin || ($isShopOfficer && $carInfo->iam != 'VIEWER')) && empty($carInfo->sold);

	if (($isEdit || $isShopPartner) && $carInfo->shopstatus != 'ENABLE') {
		R::View('icar.toolbar', $self);
		return message('error', 'ร้านค้า "'.$carInfo->shopname.'" หมดอายุการใช้งาน กรุณาติดต่อผู้ดูแลระบบเพื่อต่ออายุการใช้งาน');
	}

	//$ret .= print_o(post(),'post()');

	if ($isEdit) {
		if (post('cost')) {
			$post = (object)post('cost');
			if ($post->itemdate && ($post->costcode || $post->costname) && $post->amt) {
				$post->tpid = $carId;
				$post->itemdate = sg_date($post->itemdate,'Y-m-d');
				$post->uid = SG\getFirst(i()->uid,'func.NULL');
				$post->created = date('U');
				//$post->interest = $post->interest > 0 ? $post->interest : 0;

				if ($post->costname) {
					if ($post->costcode = mydb::select('SELECT `tid` FROM %tag% WHERE taggroup LIKE "icar:tr%" AND `name` LIKE :name LIMIT 1',':name',$post->costname)->tid) {
						; // Do nothing , use old cost code.
					} else {
						// Create new cost code
						mydb::query('INSERT INTO %tag% SET `shopid`=:shopid, `taggroup`="icar:tr:cost", `name`=:name',':name',$post->costname,':shopid',$carInfo->shopid);
						$post->costcode=mydb()->insert_id;
					}
				}

				$stmt = 'INSERT INTO %icarcost%
					(`tpid`, `uid`, `itemdate`, `costcode`, `detail`, `interest`, `amt`, `created`)
					VALUES
					(:tpid, :uid, :itemdate, :costcode, :detail, :interest, :amt, :created)';
				mydb::query($stmt,$post);

				$newid = mydb()->insert_id;
				//echo 'Save '.$newid.'<br />'.print_o($carInfo,'$carInfo');
				$carInfo = icar_model::get_by_id($carId,true);
			}
		}
		// else if ($_REQUEST['lock']=='no') {
		//	mydb::query('UPDATE %icar% SET `sold`=NULL WHERE `tpid`=:tpid LIMIT 1',':tpid',$carId);
		//	$carInfo->sold=NULL;
		//}
	}

	R::View('icar.toolbar', $self, NULL, NULL, $carInfo);

	$ret .= '<div id="icar-sidebar">'._NL;
	/*
	$ret.='<div class="menu"><ul class="tabs"><li><a class="sg-action" href="'.url('icar/view/info/'.$carId).'" data-rel="#info">{tr:Info}</a></li>'.($isEdit || $isShopPartner?'<li><a class="sg-action" href="'.url('icar/view/sale/'.$carId).'" data-rel="#info">{tr:Buy}-{tr:Sale}</a></li><li><a class="sg-action" href="'.url('icar/view/calculate/'.$carId).'" data-rel="#info">{tr:Calculate}</a></li>':'').'<li><a class="sg-action" href="'.url('icar/view/photo/'.$carId).'" data-rel="#info">{tr:Photo}</a></li></ul></div>'._NL;
	*/
	$ret .= '<div id="info">'._NL;
	$ret .= R::Page('icar.view.info',NULL,$carInfo);
	$ret .= '</div>'._NL;
	$ret .= '<div class="icar-label-retailprice">ราคาหน้าร้าน <span>'.number_format($carInfo->pricetosale,2).'</span> บาท'.($carInfo->sold?'<span class="icar-saled">ขายแล้ว</span>':'').'</div>'._NL;
	$ret .= '</div>'._NL;


	$ret .= '<div id="icar-detail" class="icar-view">';
	
	//$ret.=print_o($_POST,'$_POST');
	if ($isEdit || $isShopPartner) {
		if ($carInfo->sold)
			$ret .= '<div class="icar-label-saled">ปิดการขายแล้ว</div>';
		else if ($carInfo->saledate)
			$ret .= '<div class="icar-label-saled">บันทึกราคาขายแล้ว</div>';
		if ($isEdit)
			$ret .= '<div id="inputform" class="icar-main-form">'.R::Page('icar.view.tran.form', NULL, $carInfo).'</div>';
		$ret .= R::Page('icar.view.tran', NULL, $carInfo);
	} else {
		$ret .= '<h3>Car detail</h3>';
	}



	//$ret.=print_o($carInfo,'$carInfo');

	$ret.='</div><!--icar-detail-->';

	return $ret;
}
?>