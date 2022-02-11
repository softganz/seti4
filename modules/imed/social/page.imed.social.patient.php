<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_patient($self, $orgId = NULL, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, page: "web"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $isAdmin || $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($orgInfo->is->socialtype,array('MODERATOR','CM'));
	$isCareManager = $isAdmin || in_array($isMember,array('CM','MODERATOR','PHYSIOTHERAPIST'));

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');

	//R::View('imed.toolbar',$self,'@Patient of Groups','none');

	$ret .= '<section id="imed-social-patient" data-url="'.url('imed/social/'.$orgId.'/patient').'"><!-- Start of imed-social-patient -->';


	$ret .= '<header class="header -box"><h3>@Patient of Group</h3></header>';


	if ($isAdmin || $isMember) {
		$form = new Form(NULL, url('imed/social/'.$orgId.'/patient.add'),'add-member', 'sg-form -flex imed-social-patient-form');
		$form->addData('checkValid', true);
		$form->addData('rel','none');
		$form->addData('done', 'load->replace:#imed-social-patient');
		//$form->addData('ret', url('imed/social/'.$orgId.'/patient',array('s'=>'new')));

		$form->addField('psnid',array('type'=>'hidden','name'=>'psnid', 'id'=>'psnid'));

		$form->addField(
			'name',
			array(
				'type'=>'text',
				'label' => tr('ADD PATIENT'),
				'class'=>'sg-autocomplete -fill',
				'require'=>true,
				'value'=>htmlspecialchars($name),
				'placeholder'=>'+ ชื่อผู้ป่วย ที่ต้องการเพิ่มให้กลุ่มดูแล',
				'posttext' => '<div class="input-append"><span><button class="btn -primary"><i class="icon -material">add</i></button></span></div>',
				'container' => '{class: "-group -label-in"}',
				'attr'=>array(
					'data-query'=>url('imed/api/patients'),
					'data-altfld' => 'psnid',
				),
			)
		);

		$ret .= $form->build();
	}

	mydb::where('sp.`orgid` = :orgid', ':orgid', $orgId);

	if (post('s') == 'new') {
		mydb::value('$ORDER$','sp.`created` DESC');
	} else {
		mydb::value('$ORDER$','CONVERT(p.`name` USING tis620) ASC,  CONVERT(p.`lname` USING tis620) ASC');
	}

	$stmt = 'SELECT
		  sp.`psnid`
		, p.`prename`
		, CONCAT(p.`name`," ",p.`lname`) `name`
		, p.`sex`
		, sp.`addby`
		, u.`name` `addByName`
		, sp.`created`
		, (SELECT COUNT(*) FROM %imed_service% sv WHERE sv.`pid` = p.`psnid`) `serviceAmt`
		, (SELECT COUNT(*) FROM %imed_careplan% cp WHERE cp.`psnid` = p.`psnid` AND `orgid` = sp.`orgid`) `planAmt`
		FROM %imed_socialpatient% sp
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %users% u ON u.`uid` = sp.`addby`
		%WHERE%
		ORDER BY $ORDER$';

	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$isCallFromApp = R()->appAgent->dev == 'Softganz' || $options->page == 'app';
	$ui = new Ui(NULL,'ui-card -patient');

	$myUid = i()->uid;
	foreach ($dbs->items as $rs) {
		$isRemoveable = $isRemovePatient || $rs->addby == $myUid;
		$patientUrl = $isCallFromApp ? url('imed/app/'.$rs->psnid) : url('imed', ['pid' => $rs->psnid]);

		$cardUi = new Ui();
		$dropUi = new Ui();
		$headerUi = new Ui();

		$cardUi->add('<a class="sg-action btn" href="'.$patientUrl.'" data-webview="'.$rs->name.'" role="patient" data-pid="'.$rs->psnid.'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');

		if ($isRemoveable) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient.remove/'.$rs->psnid).'" data-rel="none" data-removeparent="ul.ui-card.-patient>.ui-item" data-title="ลบผู้ป่วยออกจากกลุ่ม" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่ม กรุณายืนยัน?"><i class="icon -material -gray">cancel</i><span>Remove from Group</span></a>');
		}
		if ($isCareManager) {
			$cardUi->add('<a class="sg-action btn" href="'.url('imed/care/'.$rs->psnid,array('org'=>$orgId)).'" data-rel="#imed-app" data-pid="'.$rs->psnid.'" data-done="moveto:0,1" data-webview="แผนการดูแล"><i class="icon -material'.($rs->planAmt>0 ? '' : ' -gray').'">assignment</i><span>Care Plan</span></a>');
		}


		if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

		$menu = '<nav class="nav -card -sg-text-right">'
				. $cardUi->build()
				. '</nav>';

		$ui->add(
			'<div class="header">'
			. '<a class="sg-action" href="'.$patientUrl.'" data-webview="'.$rs->name.'" role="patient" data-pid="'.$rs->psnid.'">'
			. '<img class="poster-photo -sg-48" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->prename.' '.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'
			. '</div>'
			. '<div class="detail">เยี่ยมบ้าน '.$rs->serviceAmt.' ครั้ง'
			. ($rs->planAmt ? ' '.$rs->planAmt . ' แผนการดูแล' : '')
			. '</div>'
			. $menu
			//. print_o($rs,'$rs')
		);
	}

	$ret .= $ui->build();

	//$ret .= print_o($orgInfo,'$orgInfo');
	//$ret .= $isRemovePatient ? 'YES' : 'NO';
	//$ret .= $orgInfo->is->socialtype;
	$ret .= '<!-- End of imed-social-patient --></section>';
	return $ret;
}
?>