<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

import('model:org.php');

function ibuy_green_my_manage($self) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$ret = '';

	R::View('toolbar',$self, 'จัดการกลุ่ม/ร้านค้า @'.$shopInfo->name,'ibuy.green.my.shop');

	$isAdmin = $shopInfo->RIGHT & _IS_ADMIN;
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;

	$ret .= '<header class="header -hidden"><h3>จัดการกลุ่ม/ร้านค้า</h3></header>';

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('org/edit/info/'.$shopId);
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}
	$inlineAttr['class'] .= 'org-info';

	$ret.='<div id="org-info" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->addClass('-org-info');

	$tables->rows[]=array(
		'ชื่อกลุ่ม/ร้านค้า',
		view::inlineedit(
			array('group'=>'org', 'fld'=>'name', 'tr'=>$shopId, 'class'=>'-fill', 'require' => true),
			$shopInfo->name,
			$isEdit
		)
	);

	$tables->rows[]=array(
		'ที่อยู่',
		view::inlineedit(
			array(
				'group'=>'org',
				'fld'=>'house,areacode',
				'tr'=>$shopId,
				'x-callback'=>'updateMap',
				'class'=>'-fill',
				'require' => true,
				'areacode'=>$shopInfo->info->areacode,
				'ret'=>'address',
				'options' => '{
						onblur: "none",
						autocomplete: {
							minLength: 5,
							target: "areacode",
							query: "'.url('api/address').'"
						},
						placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
					}',
			),
			$shopInfo->info->address,
			$isEdit,
			'autocomplete'
		)
	);

	$tables->rows[]=array(
		'รหัสไปรษณีย์',
		view::inlineedit(array('group'=>'org', 'fld'=>'zipcode', 'tr'=>$shopId,'options'=>'{maxlength:5}'),$shopInfo->info->zipcode,$isEdit)
	);

	$tables->rows[]=array(
		'โทรศัพท์',
		view::inlineedit(
			array('group'=>'org', 'fld'=>'phone', 'tr'=>$shopId, 'class'=>'-fill', 'require' => true),
			$shopInfo->info->phone,
			$isEdit
		)
	);

	$tables->rows[]=array(
		'โทรสาร',
		view::inlineedit(array('group'=>'org', 'fld'=>'fax', 'tr'=>$shopId, 'class'=>'-fill'),$shopInfo->info->fax,$isEdit)
	);

	$tables->rows[]=array(
		'อีเมล์',
		view::inlineedit(array('group'=>'org', 'fld'=>'email', 'tr'=>$shopId, 'class'=>'-fill'),$shopInfo->info->email,$isEdit)
	);

	$tables->rows[]=array(
		'เว็บไซต์',
		view::inlineedit(array('group'=>'org', 'fld'=>'website', 'tr'=>$shopId, 'class'=>'-fill'),$shopInfo->info->website,$isEdit)
	);

	$tables->rows[]=array(
		'เฟซบุ๊ค',
		view::inlineedit(array('group'=>'org', 'fld'=>'facebook', 'tr'=>$shopId, 'class'=>'-fill'),$shopInfo->info->facebook,$isEdit)
	);

	$tables->rows[]=array(
		'พิกัด GIS',
		view::inlineedit(array('group'=>'org', 'fld'=>'location', 'tr'=>$shopId, 'class'=>''),$shopInfo->info->location,$isEdit).' <a class="sg-action" href="'.url('org/'.$shopId.'/info.map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="600" data-class-name="-map" data-webview="แผนที่" data-refresh="no"><i class="icon -material">room</i></a>'
	);

	$tables->rows[]=array('เริ่มความสัมพันธ์เมื่อ',sg_date($shopInfo->info->created,'ว ดดด ปปปป H:i:s'));



	$ret.=$tables->build();


	$ret .= '</div>';

	$ret .= '<section>';
	$ret .= '<header class="header"><h3>ทีมงานบริหารกลุ่ม/ร้านค้า</h3></header>';
	$officers = OrgModel::officers($shopId);
	$tables = new Table();
	$tables->thead = array('ชื่อ-นามสกุล', 'center' => 'สมาชิก', 'date -hover-parent' => 'วันที่');
	if ($officers) {
		foreach ($officers->items as $rs) {
			$ui = new Ui();
			if ($isAdmin) {
				$ui->add('<a class="sg-action" href="'.url('org/info/api/'.$shopId.'/officer.remove/'.$rs->uid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบเจ้าหน้าที่ออกจากองค์กร?"><i class="icon -cancel"></i></a>');
			}

			$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';

			$tables->rows[] = array(
				$rs->name,
				$rs->membership,
				sg_date($rs->datein,'ว ดด ปปปป')
				.$menu
			);
		}
	}

	if ($isAdmin) {
		$ret .= '<form class="sg-form" action="'.url('org/info/api/'.$shopId.'/officer.add').'" method="post" data-rel="notify" data-done="load:#main"><input id="officer-uid" type="hidden" name="uid" value="" />';
		$tables->rows[] = array(
			'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('api/user').'" data-altfld="officer-uid" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
			'<select name="membership" class="form-select -fill"><option value="ADMIN">Admin</option><option value="MANAGER">Manager</option><option value="OFFICER">Officer</option><option value="NETWORK">Network</option><option value="MEMBER" selected="selected">Regular Member</option></select>',
			'<button class="btn"><i class="icon -add"></i><span>เพิ่มเจ้าหน้าที่</span></button>'
		);
	}

	$ret .= $tables->build();

	if ($isAdmin) $ret.='</form>';
	$ret .= '</section>';

	$ret .= '<section>';
	$ret .= '<header class="header"><h3>กลุ่ม/ร้านค้าเครือข่าย</h3></header>';

	$stmt = 'SELECT fg.*, o.`name` FROM %ibuy_farmgroup% fg LEFT JOIN %db_org% o ON o.`orgid` = fg.`orgid` WHERE fg.`parent` = :orgid';
	$dbs = mydb::select($stmt, ':orgid', $shopId);

	$tables = new Table();
	$tables->thead = array('กลุ่ม/ร้านค้าเครือข่าย', 'ผู้ติดต่อ', '');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array('<a href="'.url('ibuy/green/shop/'.$rs->orgid).'">'.$rs->name.'</a>');
	}
	$ret .= $tables->build();
	$ret .= '</section>';


	$ret .= '<section>';
	$ret .= '<header class="header"><h3>ข้อความติดต่อ</h3></header>';

	$tables = new Table();
	$tables->thead = array('วันที่','รายการติดต่อ', 'ผู้ติดต่อ', '');

	$ret .= $tables->build();
	$ret .= '</section>';

	//$ret .= print_o($shopInfo, '$shopInfo');

	//$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn" href="'.url('ibuy/green/shop/'.$shopId.'/field.add').'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i><span>เพิ่มสินค้า</span></a></nav>';

	return $ret;
}
?>