<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

/**
 * Show car information
 *
 * @param Record Set $carInfo
 * @param Boolean $isEdit
 * @return String
 */
function icar_view_info($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = $isShopOfficer && $carInfo->iam != 'VIEWER' && empty($carInfo->sold);

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('icar/edit/info');
		$inlineAttr['data-tpid'] = $carInfo->tpid;
		//$inlineAttr['data-refresh-url'] = url('icar/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$ret .= '<div id="icar-info" '.sg_implode_attr($inlineAttr).'>'._NL;


	$tables = new Table();
	$tables->addClass('icar-view-info');
	$tables->caption = '{tr:Car Information}';
	if ($isShopOfficer || $isShopPartner) {
		$tables->rows[] = array('{tr:Ref. no.}', $carInfo->refno);
		$tables->rows[] = array('{tr:Buy Date}', view::inlineedit(array('group'=>'car','fld'=>'buydate','tr'=>$carInfo->tpid),sg_date($carInfo->buydate,'d/m/Y'),$isEdit,'datepicker'));
		$tables->rows[] = array('{tr:Shop}',$carInfo->shopname.($isAdmin ? '('.$carInfo->shopid.')' : ''));
		$tables->rows[] = array(
			'{tr:Partnership}',
			view::inlineedit(
				array(
					'group' => 'car',
					'fld' => 'partner',
					'tr' => $carInfo->tpid,
					'loadurl' => url('icar/edit/info',array('action'=>'get','fld'=>'partner','selected'=>$carInfo->brand))
				),
				$carInfo->partnername,
				$isEdit,
				'select'
			)
		);
	}

	$tables->rows[] = array('{tr:Car Type}', $carInfo->cartypeName);

	$tables->rows[] = array('{tr:License Plate}', view::inlineedit(array('group'=>'car','fld'=>'plate','tr'=>$carInfo->tpid),$carInfo->plate,$isEdit,'text'));

	$tables->rows[] = array(
		'{tr:Brand}',
		view::inlineedit(
			array(
				'group'=>'car',
				'fld'=>'brand',
				'tr'=>$carInfo->tpid,
				'value' => $carInfo->brand,
				'loadurl'=>url('icar/edit/info',array('action'=>'get','fld'=>'brand'))
			),
			$carInfo->brandname,
			$isEdit,
			'select'
		)
	);

	$tables->rows[] = array('{tr:Model}', view::inlineedit(array('group'=>'car','fld'=>'model','tr'=>$carInfo->tpid),$carInfo->model,$isEdit,'text'));

	$tables->rows[] = array(
		'{tr:Year}',
		view::inlineedit(
			array('group'=>'car','fld'=>'year','tr'=>$carInfo->tpid, 'options'=>'{class: "-fill", maxlength: 4}')
			,$carInfo->year,
			$isEdit,
			'text'
		)
	);

	$tables->rows[]=array('{tr:Color}', view::inlineedit(array('group'=>'car','fld'=>'color','tr'=>$carInfo->tpid),$carInfo->color,$isEdit,'text'));

	$tables->rows[]=array('{tr:Gear type}', view::inlineedit(array('group'=>'car','fld'=>'geartype','tr'=>$carInfo->tpid),$carInfo->geartype,$isEdit,'select','Manual,Auto'));

	$tables->rows[] = array(
			'{tr:Mileage number}',
			view::inlineedit(
				array(
					'group'=>'car',
					'fld'=>'mileno',
					'tr'=>$carInfo->tpid,
					'ret'=>'numeric:0'
				),
				$carInfo->mileno != '' ? number_format($carInfo->mileno,0) : '',
				$isEdit,
				'text'
			)
		);

	if ($isShopOfficer || $isShopPartner) {
		$tables->rows[]=array('{tr:Machine number}', view::inlineedit(array('group'=>'car','fld'=>'enginno','tr'=>$carInfo->tpid),$carInfo->enginno,$isEdit,'text'));
		$tables->rows[]=array('{tr:Chassis number}', view::inlineedit(array('group'=>'car','fld'=>'bodyno','tr'=>$carInfo->tpid),$carInfo->bodyno,$isEdit,'text'));
		$tables->rows[]=array('{tr:License expire}', view::inlineedit(array('group'=>'car','fld'=>'licenseexppire','tr'=>$carInfo->tpid),$carInfo->licenseexppire ? sg_date($carInfo->licenseexppire,'d/m/Y') : '',$isEdit,'datepicker'));
		$tables->rows[]=array('{tr:Insurance expire}', view::inlineedit(array('group'=>'car','fld'=>'insuexpire','tr'=>$carInfo->tpid),$carInfo->insuexpire ? sg_date($carInfo->insuexpire,'d/m/Y') : '',$isEdit,'datepicker'));
		$tables->rows[]=array('{tr:จากสาขา}', view::inlineedit(array('group'=>'car','fld'=>'frombranch','tr'=>$carInfo->tpid,'class'=>'-fill'),$carInfo->frombranch,$isEdit));
		$tables->rows[]=array('{tr:Car Location}', view::inlineedit(array('group'=>'car','fld'=>'stklocname','tr'=>$carInfo->tpid,'class'=>'-fill'),$carInfo->stklocname,$isEdit));

	}


	$ret .= $tables->build();

	if ($isShopOfficer) {
		$ret .= '<b>หมายเหตุ</b><br />'
				.view::inlineedit(
				array('group'=>'car','fld'=>'shopremark','tr'=>$carInfo->tpid,'class'=>'-fill', 'ret'=>'br', 'value'=>$carInfo->shopremark),
				nl2br($carInfo->shopremark),
				$isEdit,
				'textarea');
	}

	$ret .= '</div>';

	if ($isShopOfficer || $isShopPartner) {
		$tables = new Table();
		$tables->rows[]=array('<tr><th colspan="3">ข้อมูลการขาย</th></tr>');
		$tables->rows[]=array('วันที่ขาย', '<td colspan="2">'.($carInfo->saledate?sg_date($carInfo->saledate,'d/m/Y'):'-').'</td>');
		$tables->rows[]=array('ราคาขาย', number_format($carInfo->saleprice,2),'บาท');
		$tables->rows[]=array('จัดไฟแนนส์', number_format($carInfo->financeprice,2),'บาท');
		$tables->rows[]=array('เงินดาวน์', number_format($carInfo->saledownprice,2),'บาท');
		$tables->rows[]=array('รับเงินดาวน์', number_format($carInfo->saledownpaid,2),'บาท');
		$tables->rows[]=array('ค้างชำระเงินดาวน์', number_format($carInfo->saledownprice-$carInfo->saledownpaid,2),'บาท');
		$ret .= $tables->build();
	}

	return $ret;
}
?>