<?php
function org_supplier_add($self,$psnid = NULL) {
	$self->theme->title='ข้อมูลผู้ผลิต';
	$self->theme->sidebar=R::Page('org.supplier.menu');
	$ret='<h3 class="title">รายละเอียดข้อมูลผู้ผลิต</h3>';
	$post=(object)post('person');

	if ($post->fullname) {
		if (empty($post->psnid)) $post->psnid=NULL;
		$post->uid=i()->uid;
		list($post->name,$post->lname)=sg::explode_name(' ',$post->fullname);
		$addr=SG\explode_address($post->address,$post->areacode);
		$post->house=$addr['house'];
		$post->village=$addr['village'];
		$post->tambon=$addr['tambonCode'];
		$post->ampur=$addr['ampurCode'];
		$post->changwat=$addr['changwatCode'];
		$post->zip=$addr['zip'];
		$post->created=date('U');

		$stmt='INSERT INTO %db_person%
						(`psnid`, `uid`, `prename`, `name`, `lname`, `house`, `village`, `tambon`, `ampur`, `changwat`, `phone`, `email`)
					VALUES
						(:psnid, :uid, :prename, :name, :lname, :house, :village, :tambon, :ampur, :changwat, :phone, :email)
					ON DUPLICATE KEY UPDATE `prename`=:prename, `name`=:name, `lname`=:lname, `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `phone`=:phone, `email`=:email';
		mydb::query($stmt,$post);
		if (!$post->psnid) $post->psnid=mydb()->insert_id;
		//$ret.=mydb()->_query.mydb()->_error.'<br />';

		if ($post->orgname && empty($post->orgid)) {
			mydb::query('INSERT INTO %db_org% (`uid`, `name`) VALUES (:uid, :orgname)',$post);
			$post->orgid=mydb()->insert_id;
			//$ret.=mydb()->_query;
		}

		$stmt='INSERT INTO %org_supplier% (`psnid`, `orgid`, `argtype`) VALUES (:psnid, :orgid, :argtype) ON DUPLICATE KEY UPDATE `orgid`=:orgid, `argtype`=:argtype';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.'<br />';
		//$ret.=print_o($post,'$post');
		location('org/supplier');
	}
	if ($psnid) {
		$stmt='SELECT p.`psnid`, p.`cid`, p.`prename`, CONCAT(p.`name`," ",p.`lname`) fullname,
				s.`orgid`, s.`argtype`,
				o.`name` orgname,
				p.`phone`, p.`email`,
				p.`house`, p.`village`,
				IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname,
				IFNULL(codist.`distname`,p.`t_ampur`) distname,
				IFNULL(copv.`provname`,p.`t_changwat`) provname,
				CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) areacode
			FROM %db_person% p
				LEFT JOIN %org_supplier% s USING(`psnid`)
				LEFT JOIN %db_org% o USING (`orgid`)
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
			WHERE p.`psnid`=:psnid
			LIMIT 1';
		$rs=mydb::select($stmt,':psnid',$psnid);
		$rs->address=SG\implode_address($rs);
		$post=$rs;
	}
	$ret.=__new_member_form($post);
	return $ret;
}

function __new_member_form($post) {
	if (empty($post)) $post=(object)post('person');

	$form = new Form([
		'variable' => 'person',
		'action' => url(q()),
		'id' => 'org-add-person',
		'class' => 'sg-form',
		'rel' => '#main',
		'children' => [
			'psnid' => ['type'=>'hidden','value'=>$post->psnid],
			'areacode' => ['type' => 'hidden', 'value' => $post->areacode],
			'orgid' => ['type' => 'hidden', 'value' => $post->orgid],
			'prename' => [
				'type' => 'text',
				'label' => 'คำนำหน้านาม',
				'size' => 10,
				'value' => $post->prename,
			],
			'fullname' => [
				'type' => 'text',
				'label' => 'ชื่อ - นามสกุล',
				'require' => true,
				'class' => 'sg-autocomplete -fill',
				'attr' => 'data-altfld="edit-person-psnid" data-query="'.url('org/api/person').'" data-callback="orgSupplierAddMember"',
				'value' => $post->fullname,
			],
			'address' => [
				'type' => 'text',
				'label' => 'ที่อยู่',
				'class' => 'sg-address -fill',
				'attr' => 'data-altfld="edit-person-areacode"',
				'value' => $post->address,
			],
			'orgname' => [
				'type' => 'text',
				'label' => 'สังกัดกลุ่ม/องค์กร',
				'class' => '-fill',
				'attr' => 'data-altfld="edit-person-orgid"',
				'value' => $post->orgname,
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์',
				'class' => '-fill',
				'value' => $post->phone,
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์',
				'class' => '-fill',
				'value' => $post->email,
			],
			'argtype' => [
				'type' => 'radio',
				'label' => 'ประเภทเกษตรกร:',
				'options' => ['รายย่อย'=>'รายย่อย','รวมกลุ่ม'=>'รวมกลุ่ม','องค์กรนิติบุคคล'=>'องค์กรนิติบุคคล'],
				'value' => SG\getFirst($post->argtype,'รายย่อย'),
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('org/supplier').'"><i class="icon -material">cancel></i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	$ret.='<script type="text/javascript">
	$("#edit-person-fullname").focus()
	function orgSupplierAddMember($this,ui) {
		//notify("Callback "+ui.item.desc)
		$.getJSON(url+"org/api/person?id="+ui.item.value,function(data) {
			$("#edit-person-prename").val(data.prename)
			$("#edit-person-address").val(data.address)
			$("#edit-person-phone").val(data.phone)
			$("#edit-person-email").val(data.email)
			$("#edit-person-areacode").val(data.areacode)
			$("#edit-person-orgname").focus()
		});
	}
	</script>';
	return $ret;
}

?>