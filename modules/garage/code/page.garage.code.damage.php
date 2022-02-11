<?php
/**
* Garage :: Damage Code
* Created 2020-08-01
* Modify  2020-09-28
*
* @param Object $self
* @param String $action
* @param Int $id
* @return String
*
* @usage garage/code/damage/{$action}/{$Id}
*/

$debug = true;

function garage_code_damage($self, $action = NULL, $id = NULL) {
	$shopInfo = R::Model('garage.get.shop');
	$shopBrance = R::Model('garage.shop.branch',$shopInfo->shopid);

	new Toolbar($self,'รหัสความเสียหาย');

	switch ($action) {
		case 'edit':
			$data = R::Model('garage.damage.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret .= __garage_code_damage_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data = post('data') ? (object) post('data') : (object) post();
			$saveResult .= __garage_code_damage_save($shopInfo,$data);
			//$ret.=__garage_code_damage_list($shopInfo,NULL,NULL,$data->insurerid);
			$ret .= $saveResult;
			//location('garage/code/damage');
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret .= __garage_code_damage_delete($shopInfo,$id);
			break;

		case 'list':
			$ret .= __garage_code_damage_list($shopInfo);
			break;


		default:
			$ret .= '<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_damage_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_damage_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$shopid = $shopInfo->shopid;
	$shopbranch = R::Model('garage.shop.branch',$shopInfo->shopid);

	$stmt = 'SELECT * FROM %garage_damage% WHERE `shopid` IN (:shopbranch) ORDER BY `sorder` ASC';
	$dbs = mydb::select($stmt,':shopbranch','SET:'.implode(',',array_keys($shopbranch)));

	$ret .= '<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/damage/save').'" data-checkvalid="true" data-rel="notify" data-done="load:#garage-code-trans:'.url('garage/code/damage').'">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead = array('รหัส','description'=>'รายละเอียด','center -nowrap'=>'คำนำหน้า','icons -c2'=>'');

	$tables->rows[] = array(
		'<input id="oldid" type="hidden" name="oldid" value="'.$data->damagecode.'" />'
		.'<input id="damagecode" type="hidden" name="damagecode" value="'.$data->damagecode.'" />'
		.'<input id="insurername" class="form-text -fill -uppercase -require" type="text" name="damagecode" value="'.$data->damagecode.'" maxlength="4" placeholder="รหัส" style="width: 5em;" />',
		'<input id="damagename" class="form-text -fill -require" type="text" name="damagename" value="'.$data->damagename.'" placeholder="รายละเอียด" />',
		'<td colspan="2"><input id="pretext" class="form-text -fill" type="text" name="pretext" value="'.$data->pretext.'" placeholder="คำนำหน้า" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'เพิ่ม').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/damage/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui = new Ui(NULL,'ui-menu');
		if ($rs->shopid == $shopInfo->shopid) {
			$ui->add('<a class="sg-action" href="'.url('garage/code/damage/edit/'.$rs->damagecode).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
			$ui->add('<a class="sg-action -disabled" href="'.url('garage/code/damage/delete/'.$rs->damagecode).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');
		}
		$menu = sg_dropbox($ui->build());

		$config = array();
		if ($trid && $trid == $rs->damagecode) $config['class']='-highlight';
		$tables->rows[] = array(
			$rs->damagecode,
			$rs->damagename,
			$rs->pretext,
			$menu,
			'config'=>$config,
		);
	}
	$ret .= $tables->build();
	$ret .= '</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_damage_save($shopInfo, $data) {
	$data->shopid = $shopInfo->shopid;
	$data->shopbranch = $shopInfo->branchId;
	$data->damagecode = strtoupper($data->damagecode);

	if ($data->oldid) {
		$data->sorder = mydb::select('SELECT `sorder` FROM %garage_damage% WHERE `shopid` = :shopid AND `damagecode` = :oldid LIMIT 1',$data)->sorder;
	} else {
		$data->sorder = mydb::select('SELECT MAX(`sorder`) `lastorder` FROM %garage_damage% WHERE `shopid` = :shopid LIMIT 1',$data)->lastorder+1;
	}
	//$ret.=mydb()->_query.'<br />';

	$stmt = 'INSERT INTO %garage_damage%
		(`shopid`, `damagecode`, `damagename`, `pretext`, `sorder`)
		VALUES
		(:shopid, :damagecode, :damagename, :pretext, :sorder)
		ON DUPLICATE KEY UPDATE
		`damagename` = :damagename, `pretext` = :pretext';

	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid != $data->damagecode) {
		// Remove old id
		$stmt = 'DELETE FROM %garage_damage% WHERE `shopid` = :shopid AND `damagecode` = :oldid LIMIT 1';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt = 'UPDATE
				%garage_jobtr% tr
				LEFT JOIN %garage_job% j USING(`tpid`)
			SET tr.`damagecode` = :damagecode
			WHERE j.`shopid` IN (:shopbranch) AND tr.`damagecode` = :oldid';

		mydb::query($stmt, $data);
		//$ret.=mydb()->_query.'<br />';
	}

	//$ret .= print_o($data,'$data');
	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}

function __garage_code_damage_delete($shopInfo,$id) {
	$stmt = 'DELETE FROM %garage_damage% WHERE `shopid` = :shopid AND `damagecode` = :id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}
?>