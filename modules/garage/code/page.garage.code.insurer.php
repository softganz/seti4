<?php
function garage_code_insurer($self, $action = NULL, $id = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	new Toolbar($self,'บริษัทประกัน');

	switch ($action) {
		case 'form':
			$data=R::Model('garage.insurer.get',$shopInfo->shopid,$id);
			$ret.=__garage_code_insurer_form($shopInfo,$data);
			break;

		case 'edit':
			$data=R::Model('garage.insurer.get',$shopInfo->shopid,$id);
			//$ret.=print_o($data,'$data');
			$ret.=__garage_code_insurer_list($shopInfo,$data,$action,$id);
			break;
		
		case 'save':
			$data=post('data')?(object)post('data'):(object)post();
			$saveResult.=__garage_code_insurer_save($shopInfo,$data);
			//$ret.=__garage_code_insurer_list($shopInfo,NULL,NULL,$data->insurerid);
			//$ret.=$saveResult;
			location('garage/code/insurer');
			break;

		case 'delete':
			if ($id && SG\confirm()) $ret.=__garage_code_insurer_delete($shopInfo,$id);
			break;

		case 'list':
			$ret.=__garage_code_insurer_list($shopInfo);
			break;


		default:
			$ret.='<div id="garage-code-trans" class="garage-code-trans">'.__garage_code_insurer_list($shopInfo).'</div>';
			break;
	}
	return $ret;
}

function __garage_code_insurer_list($shopInfo, $data = NULL, $action = NULL, $trid = NULL) {
	$shopid=$shopInfo->shopid;
	$stmt='SELECT * FROM %garage_insurer% WHERE `shopid` IN (:shopid) ORDER BY CONVERT(`insurername` USING tis620) ASC';
	$dbs=mydb::select($stmt,':shopid',$shopid);

	$ret.='<form id="garage-job-code-new" class="sg-form" method="post" action="'.url('garage/code/insurer/save').'" data-checkvalid="true" data-rel="#garage-code-trans">'._NL;

	$tables = new Table();
	$tables->addClass('-brand');
	$tables->thead=array('description'=>'ชื่อบริษัทประกัน','phone -nowrap -hover-parent'=>'โทรศัพท์');

	$tables->rows[]=array(
		'<input id="oldid" type="hidden" name="oldid" value="'.$data->insurerid.'" />'
		.'<input id="insurerid" type="hidden" name="insurerid" value="'.$data->insurerid.'" />'
		.'<input id="insurername" class="form-text sg-autocomplete -fill -require" type="text" name="insurername" value="'.$data->insurername.'" placeholder="ชื่อบริษัทประกัน" data-query="'.url('garage/api/insurer').'" data-select=\'{"oldid":"value","insurerid":"value","insurername":"label","insurerphone":"phone"}\' />',
		'<td colspan="2"><input id="insurerphone" class="form-text -fill" type="text" name="insurerphone" value="'.$data->insurerphone.'" placeholder="โทรศัพท์" />'
		.'<br /><button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'เพิ่ม').'</span></button>'
		.($action=='edit'?'<a class="sg-action button-cancel" href="'.url('garage/code/insurer/list').'" data-rel="#garage-code-trans">ยกเลิกแก้ไข</a>':'')
		.'</td>',
		'config'=>array('class'=>'-input -no-print'),
	);

	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('garage/code/insurer/form/'.$rs->insurerid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/code/insurer/delete/'.$rs->insurerid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -material">delete</i></a>');

		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';

		$config=array();
		if ($trid && $trid==$rs->insurerid) $config['class']='-highlight';
		$tables->rows[]=array(
			$rs->insurername,
			$rs->insurerphone
			.$menu,
			'config'=>$config,
		);
	}

	$ret.=$tables->build();

	$ret.='</form>';

	//$ret.=print_o($dbs);
	return $ret;
}



function __garage_code_insurer_form($shopInfo,$data) {
	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>แก้ไขบริษัทประกัน</h3></header>';
	$form=new Form('data',url('garage/code/insurer/save'));
	$form->addField('insurerid',array('type'=>'hidden','value'=>$data->insurerid));
	$form->addField('insurername',array('label'=>'ชื่อบริษัทประกัน','type'=>'text','class'=>'-fill','value'=>$data->insurername));
	$form->addField('insureraddr',array('label'=>'ที่อยู่','type'=>'textarea','class'=>'-fill','value'=>$data->insureraddr,'rows'=>2));
	$form->addField('insurertaxid',array('label'=>'เลขประจำตัวผู้เสียภาษี','type'=>'text','class'=>'-fill','value'=>$data->insurertaxid,'maxlength'=>13));
	$form->addField('insurerbranch',array('label'=>'สาขาลำดับที่','type'=>'text','class'=>'-fill','value'=>$data->insurerbranch));
	$form->addField('insurerphone',array('label'=>'โทรศัพท์','type'=>'text','class'=>'-fill','value'=>$data->insurerphone));
	$form->addField('insureremail',array('label'=>'อีเมล์','type'=>'text','class'=>'-fill','value'=>$data->insureremail));
	$form->addField('insurerweb',array('label'=>'เว็บ','type'=>'text','class'=>'-fill','value'=>$data->insurerweb));
	$form->addField('remark',array('label'=>'หมายเหตุ','type'=>'textarea','class'=>'-fill','value'=>$data->remark,'rows'=>3));
	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
			'container' => '{class: "-sg-text-right"}'
		)
	);

	$ret .= $form->build();
	$ret.='<p>หมายเหตุ : การแก้ไขข้อมูลบริษัทประกันจะไม่มีผลต่อข้อมูลในใบเสร็จรับเงินที่สร้างไว้แล้ว</p>';
	//$ret.=print_o($data,'$data');
	return $ret;
}



function __garage_code_insurer_save($shopInfo,$data) {
	$data->shopid=$shopInfo->shopid;
	$data->insurerid=strtoupper($data->insurerid);
	$data->insureraddr=empty($data->insureraddr)?'':$data->insureraddr;
	$data->insurertaxid=empty($data->insurertaxid)?'':$data->insurertaxid;
	$data->insurerbranch=empty($data->insurerbranch)?'':$data->insurerbranch;
	$data->insurerphone=empty($data->insurerphone)?'':$data->insurerphone;
	$data->insureremail=empty($data->insureremail)?'':$data->insureremail;
	$data->insurerweb=empty($data->insurerweb)?'':$data->insurerweb;
	$data->remark=empty($data->remark)?'':$data->remark;

	$stmt='INSERT INTO %garage_insurer%
		(`shopid`, `insurerid`, `insurername`, `insurerphone`)
		VALUES
		(:shopid, :insurerid, :insurername, :insurerphone)
		ON DUPLICATE KEY UPDATE
		  `insurername`=:insurername
		, `insureraddr`=:insureraddr
		, `insurertaxid`=:insurertaxid
		, `insurerbranch`=:insurerbranch
		, `insurerphone`=:insurerphone
		, `insureremail`=:insureremail
		, `insurerweb`=:insurerweb
		, `remark`=:remark
		';
	mydb::query($stmt,$data);
	$ret.=mydb()->_query.'<br />';

	// Change id
	if ($data->oldid && $data->oldid!=$data->insurerid) {
		// Remove old id
		$stmt='DELETE FROM %garage_insurer% WHERE `shopid`=:shopid AND `insurerid`=:oldid LIMIT 1';
		//mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$stmt='UPDATE %garage_job% SET `insurerid`=:insurerid WHERE `shopid`=:shopid AND `insurerid`=:oldid';
		//mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
	}

	$ret.=print_o($data,'$data');
	return $ret;
}



function __garage_code_insurer_delete($shopInfo,$id) {
	$stmt='DELETE FROM %garage_insurer% WHERE `shopid`=:shopid AND `insurerid`=:id LIMIT 1';
	mydb::query($stmt,':shopid',$shopInfo->shopid,':id',$id);
}
?>