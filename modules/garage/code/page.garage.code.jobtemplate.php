<?php
function garage_code_jobtemplate($self, $action = NULL, $id = NULL, $tranId = NULL) {
	$shopInfo=R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	new Toolbar($self,'แบบสั่งซ่อม');

	switch ($action) {
		case 'edit':
			$data=R::Model('garage.jobtemplate.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_jobtemplate_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=(object)post();
			$saveResult.=__garage_code_jobtemplate_save($shopInfo,$data);
			$ret.=__garage_code_jobtemplate_list($shopInfo,NULL,NULL,$data->templateid);
			$ret.=$saveResult;
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_jobtemplate_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_jobtemplate_list($shopInfo);
			break;

		case 'view':
			$ret .= __garage_code_jobtemplate_view($shopInfo, $id);
			break;

		case 'tran.save':
			if ($id && post('id')) {
				$sorder = mydb::select('SELECT MAX(`sorder`) `lastOrder` FROM %garage_jobtemplatetr% WHERE `shopid` = :shopId AND `templateid` = :templateid LIMIT 1', ':shopId', $shopId, ':templateid', $id)->lastOrder + 1;
				$stmt = 'INSERT INTO %garage_jobtemplatetr%
					(`shopid`, `templateid`, `repairid`, `sorder`)
					VALUES
					(:shopId, :templateid, :repairid, :sorder)
					ON DUPLICATE KEY UPDATE
					`repairid` = `repairid`
					';
				mydb::query($stmt, ':shopId', $shopId, ':templateid', $id, ':sorder', $sorder, ':repairid', post('id'));
			}
			break;

		case 'tran.remove':
			$stmt = 'DELETE FROM %garage_jobtemplatetr% WHERE `shopid` = :shopId AND `templateid` = :templateid AND `repairid` = :repairid LIMIT 1';
			mydb::query($stmt, ':shopId', $shopId, ':templateid', $id, ':repairid', $tranId);
			break;

		case 'tran.order':
			$thisRs = mydb::select('SELECT * FROM %garage_jobtemplatetr% WHERE `shopid` = :shopId AND `templateid` = :templateid AND `repairid` = :repairid LIMIT 1', ':shopId', $shopId, ':templateid', $id, ':repairid', $tranId);

			//$ret .= print_o($thisRs, '$thisRs');

			mydb::where('`shopid` = :shopid AND `templateid` = :templateid');
			if (post('to') == 'up') {
				mydb::where('`sorder` < :thisorder');
				mydb::value('$SORT$', 'DESC');
			} else {
				mydb::where('`sorder` > :thisorder');		
				mydb::value('$SORT$', 'ASC');
			}
			$stmt = 'SELECT * FROM %garage_jobtemplatetr% %WHERE% ORDER BY `sorder` $SORT$ LIMIT 1';
			$toRs = mydb::select($stmt, ':shopid',$shopId, ':templateid', $id, ':thisorder',$thisRs->sorder);
			//$ret .= mydb()->_query.'<br />';
			//$ret .= 'This order ='.$thisRs->sorder.' TO '.$toRs->sorder.'<br />';

			if ($thisRs->sorder && $toRs->sorder) {
				$stmt = 'UPDATE %garage_jobtemplatetr% SET `sorder` = :toorder WHERE `shopid` = :shopId AND `templateid` = :templateid AND `repairid` = :repairid LIMIT 1';
				mydb::query($stmt,':shopId',$thisRs->shopid, ':templateid', $thisRs->templateid, ':repairid', $thisRs->repairid, ':toorder',$toRs->sorder);
				//$ret.=mydb()->_query.'<br />';

				$stmt = 'UPDATE %garage_jobtemplatetr% SET `sorder` = :thisorder WHERE  `shopid` = :shopId AND `templateid` = :templateid AND `repairid` = :repairid LIMIT 1';
				mydb::query($stmt, ':shopId',$toRs->shopid, ':templateid', $toRs->templateid, ':repairid', $toRs->repairid, ':thisorder', $thisRs->sorder);
				//$ret.=mydb()->_query.'<br />';

				mydb::query('SET @f := null, @i = null;');
				mydb::query('UPDATE %garage_jobtemplatetr% SET
					`sorder` = IF(`templateid` = @f, @i := @i+1, @i := 1)
					, `templateid` = (@f := `templateid`)
					WHERE `shopid` = :shopId AND `templateid` = :templateid
					ORDER BY `sorder`;', ':shopId',
					$shopId, ':templateid', $id
				);
				//$ret .= mydb()->_query.'<br />';
			}
			break;

		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_jobtemplate_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_jobtemplate_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$shopid=$shopInfo->shopid;
	$stmt='SELECT * FROM %garage_jobtemplate% WHERE `shopid` IN (:shopid) ORDER BY CONVERT(`templatename` USING tis620) ASC';
	$dbs=mydb::select($stmt,':shopid',$shopid);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/jobtemplate/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-jobtemplate');
	$tables->thead=array('code'=>'รหัส','description'=>'รายละเอียด','');

	$tables->rows[]=array(
		'<input type="hidden" name="oldid" value="'.$data->templateid.'" />'
		.'<input id="codeid" class="form-text -fill -uppercase -require" type="text" name="templateid" value="'.$data->templateid.'" placeholder="รหัส" size="5" maxlength="10" />',
		'<td colspan="2"><input id="templatename" class="form-text -fill -require" type="text" name="templatename" value="'.$data->templatename.'" placeholder="รายละเอียด" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/jobtemplate/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui = new Ui(NULL,'ui-menu');
		$ui->addConfig('nav', '{class: "nav -icons"}');
		$ui->add('<a class="sg-action btn -link" href="'.url('garage/code/jobtemplate/view/'.$rs->templateid).'"><i class="icon -material">find_in_page</i></a>');
		//$ui->add('<a class="sg-action" href="'.url('garage/code/jobtemplate/edit/'.$rs->templateid).'" data-rel="#garage-code-trans" data-callback="garageCodeEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
		//$ui->add('<a class="sg-action" href="'.url('garage/code/jobtemplate/delete/'.$rs->templateid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');

		$config=array();
		if ($trid && $trid==$rs->templateid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->templateid,
			$rs->templatename,
			$ui->build(),
			'config'=>$config,
		);
	}
	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}

function __garage_code_jobtemplate_save($shopInfo,$data) {
	$data->shopid=$shopInfo->shopid;
	$data->templateid=strtoupper($data->templateid);
	$stmt='INSERT INTO %garage_jobtemplate%
					(`shopid`,`templateid`,`templatename`)
					VALUES
					(:shopid,:templateid,:templatename)
					ON DUPLICATE KEY UPDATE
					`templatename`='.($data->oldid?':templatename':'`templatename`');
	mydb::query($stmt,$data);
	//$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->templateid) {
		// Remove old id
		$stmt='DELETE FROM %garage_jobtemplate% WHERE `shopid`=:shopid AND `templateid`=:oldid LIMIT 1';
		mydb::query($stmt,$data);

		$stmt='UPDATE %garage_job% SET `templateid`=:templateid WHERE `shopid`=:shopid AND `templateid`=:oldid';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt='UPDATE %garage_jobtemplatetr% SET `templateid`=:templateid WHERE `shopid`=:shopid AND `templateid`=:oldid';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
	}

	//$ret.=print_o($data,$data);
	return $ret;
}

function __garage_code_jobtemplate_delete($shopInfo,$id) {
	$stmt='DELETE FROM %garage_jobtemplate% WHERE `shopid`=:shopid AND `templateid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}

function __garage_code_jobtemplate_view($shopInfo,$id) {
	$stmt = 'SELECT
		t.*
		, c.`repaircode`
		, c.`repairname`
		FROM %garage_jobtemplatetr% t
			LEFT JOIN %garage_repaircode% c USING(`repairid`)
		WHERE `templateid` = :templateid
		ORDER BY `sorder` ASC';

	$dbs = mydb::select($stmt, ':templateid', $id);

	$tables = new Table();
	$tables->thead = array(
		'no' => '',
		'code -nowrap' => 'รหัส',
		'detail -fill' => 'รายการ',
		'icons -c3 -nowrap' => ''
	);

	foreach ($dbs->items as $rs) {
		$menuUi = new Ui();
		$menuUi->addConfig('nav', '{class: "nav -icons"}');

		$menuUi->add('<a class="sg-action" href="'.url('garage/code/jobtemplate/tran.order/'.$id.'/'.$rs->repairid,array('to'=>'up')).'" data-rel="notify" data-done="load"><i class="icon -up"></i></a>');
		$menuUi->add('<a class="sg-action" href="'.url('garage/code/jobtemplate/tran.order/'.$id.'/'.$rs->repairid,array('to'=>'down')).'" data-rel="notify" data-done="load"><i class="icon -down"></i></a>');
		$menuUi->add('<a class="sg-action" href="'.url('garage/code/jobtemplate/tran.remove/'.$id.'/'.$rs->repairid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');

		$tables->rows[] = array(
			$rs->sorder,
			$rs->repaircode,
			$rs->repairname,
			$menuUi->build(),
		);
	}

	$tables->rows[] = array(
		'<td></td>',
		'<input id="repaircode" class="form-text -fill -disabled" type="text" />',
		'<td colspan="2">'
		. '<form class="sg-form" action="'.url('garage/code/jobtemplate/tran.save/'.$id).'" data-rel="notify" data-done="load">'
		. '<input id="repairid" name="id" type="hidden" value="" />'
		. '<div id="form-item-edit-custname" class="form-item -edit-custname -group">'
		. '<span class="form-group">'
		. '<input id="repairname" class="sg-autocomplete form-text -fill" type="text" placeholder="รหัสสั่งซ่อม หรือ รายละเอียด" data-query="'.url('garage/api/repaircode').'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name"}\' />'
		. '<div class="input-append"><span><button><i class="icon -material -gray">add_circle_outline</i></button></span></div></form>'
		. '</td>',
	);

	//'<input id="repaircode" class="form-text sg-autocomplete -fill -require" type="text" name="repaircode" value="'.$jobTranInfo->repaircode.'" placeholder="รหัสสั่งซ่อม-อะไหล่" size="5" data-query="'.url('garage/api/repaircode').'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name","price":"priceA"}\' data-select-name="repairname" data-callback="garageRepairCodeSelect" data-class="-repaircode" data-width="400" />',

	$ret .= $tables->build();

	//$ret .= print_o($dbs,'$dbs');

	$ret .= '<script type="text/javascript">
	function garageCodeJobTemplateAddTran() {
		para = {}
		para.
		console.log("SAVE")
	}
	</script>';

	return $ret;
}
?>