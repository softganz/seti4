<?php
function org_seedfund_need($self,$action = NULL,$id = NULL) {
	$self->theme->title='กองทุนเมล็ดพันธุ์';
	R::Page('org.seedfund.toolbar',$self);

	if ($id) $needInfo=R::Model('org.seedfund.get',$id);
	$isEdit=$needInfo->isEdit;

	//$ret.=print_o($needInfo,'$needInfo');
	//$ret.=print_o(post(),'post()');

	switch ($action) {
		case 'form' :
			return __org_seedfund_need_form();
			break;

		case 'edit' :
			$stmt='SELECT * FROM %org_seedfundneed% WHERE `sfnid`=:sfnid LIMIT 1';
			$rs=mydb::select($stmt,':sfnid',$id);
			$ret.=__org_seedfund_need_form($rs);
			return $ret;

		case 'add' :
			$id=__org_seedfund_need_add((object)post('data'));
			$ret.='<p class="notify">บันทึกข้อมูลเรียบร้อย</p>';
			$needInfo=R::Model('org.seedfund.get',$id);
			$ret.=__org_seedfund_need_view($needInfo);
			return $ret;
			break;

		case 'view' :
			$ret.=__org_seedfund_need_view($needInfo);
			return $ret;
			break;

		case 'delete' :
			if ($id && $isEdit && SG\confirm()) {
				$stmt='DELETE FROM %org_seedfundneed% WHERE `sfnid`=:id LIMIT 1';
				mydb::query($stmt,':id',$id);
			}
			return $ret;
			break;
	}
	$ret.=__org_seedfund_need_form();
	return $ret;
}

function __org_seedfund_need_view($needInfo) {
	$ret.='<p><b>ชื่อบุคคล/กลุ่ม :</b><br />'.$needInfo->who.'</p>';
	$ret.='<p><b>สถานที่ :</b><br />'.$needInfo->address.'</p>';
	$ret.='<b>ความต้องการ :</b>'.sg_text2html($needInfo->need);
	$ret.='<p><b>ความเร่งด่วน :</b><br />'.$needInfo->urgency.'</p>';
	$ret.='<p><b>ภายในวันที่ :</b><br />'.sg_date($needInfo->dateuse,'ว ดด ปปปป').'</p>';
	$ret.='<b>สถานการณ์โดยสังเขป :</b>'.sg_text2html($needInfo->situation);
	$ret.='<b>ช่องทางการติดต่อ :</b>'.sg_text2html($needInfo->attention);
	$ret.='<p align="right">';
	if ($needInfo->name) $ret.='โพสท์โดย '.$needInfo->name;
	$ret.=' เมื่อ '.sg_date($needInfo->daterequest,'ว ดด ปปปป H:i').' น.</p>';
	// debugMsg($needInfo,'$needInfo');
	return $ret;
}

function __org_seedfund_need_add($data) {
	if (empty($data->sfnid)) $data->sfnid=NULL;
	$data->uid=i()->uid;
	if (empty($data->daterequest)) $data->daterequest=date('Y-m-d H:i:s');
	$data->dateuse=empty($data->dateuse)?date('Y-m-d H:i:s'):sg_date($data->dateuse,'Y-m-d');
	if (empty($data->urgency)) $data->urgency=1;
	$data->created=date('U');
	$stmt='INSERT INTO %org_seedfundneed%
					(`sfnid`, `uid`, `daterequest`, `dateuse`, `who`, `address`, `need`, `urgency`, `situation`, `attention`, `created`)
					VALUES
					(:sfnid, :uid, :daterequest, :dateuse, :who, :address, :need, :urgency, :situation, :attention, :created)
					ON DUPLICATE KEY UPDATE
					  `dateuse`=:dateuse
					, `who`=:who
					, `address`=:address
					, `need`=:need
					, `urgency`=:urgency
					, `situation`=:situation
					, `attention`=:attention
					';
	mydb::query($stmt,$data);
	$id=mydb()->insert_id;
	//echo mydb()->_query;
	//print_o($data,'$data',1);
	//debugMsg(mydb()->_query);
	//debugMsg($data,'$data');
	return $id;
}

function __org_seedfund_need_form($data = NULL) {
	R::Page('org.seedfund.toolbar',$self);

	$form = new Form([
		'variable' => 'data',
		'action' => url('org/seedfund/need/add'),
		'id' => 'need',
		'title' => 'แจ้งความต้องการ',
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => _AJAX ? '#org-seedfund-info' : NULL,
		'children' => [
			'sfnid' => $data->sfnid ? ['type' => 'hidden', 'value' => $data->sfnid] : NULL,
			'who' => [
				'type' => 'text',
				'label' => 'ชื่อบุคคล/กลุ่ม',
				'class' => '-fill',
				'require' => true,
				'value' => $data->who,
			],
			'address' => [
				'type' => 'text',
				'label' => 'สถานที่/พื้นที่',
				'class' => '-fill',
				'require' => true,
				'value' => $data->address,
			],
			'need' => [
				'type' => 'textarea',
				'label' => 'ความต้องการ',
				'class' => '-fill',
				'rows' => 2,
				'require' => true,
				'value' => $data->need,
			],
			'urgency' => [
				'type' => 'radio',
				'label' => 'ความเร่งด่วน :',
				'options' => [1=>'ไม่เร่งด่วน',2=>'ปานกลาง',3=>'เร่งด่วนมาก'],
				'require' => true,
				'value' => $data->urgency,
			],
			'dateuse' => [
				'type' => 'text',
				'label' => 'ภายในวันที่',
				'value' => date('d/m/Y'),
				'class' => 'sg-datepicker',
				'value' => $data->dateuse?sg_date($data->dateuse,'d/m/Y'):date('d/m/Y'),
			],
			'situation' => [
				'type' => 'textarea',
				'label' => 'สถานการณ์โดยสังเขป',
				'class' => '-fill',
				'rows' => 2,
				'require' => true,
				'value' => $data->situation,
			],
			'attention' => [
				'type' => 'textarea',
				'label' => 'ช่องทางการติดต่อ',
				'class' => '-fill',
				'rows' => 2,
				'require' => true,
				'value' => $data->attention,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>บันทึกความต้องการ</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret.=$form->build();
	//$ret.='<script>function saveNeed() {alert("Complete");}</script>';
	return $ret;
}
?>