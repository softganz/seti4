<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_rehab_patient($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $isAdmin || $orgInfo->is->socialtype;
	$isRemovePatient = $isAdmin || in_array($isMember,array('MODERATOR','CM'));
	$isCareManager = $isAdmin || $isMember == 'CM';

	if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');


	if ($isAdmin || $isMember) {
		$form = new Form(NULL, url('imed/social/'.$orgId.'/patient.add'),'add-member', 'sg-form -flex imed-social-patient-form');
		$form->addConfig('title', tr('ADD PATIENT'));
		$form->addData('checkValid', true);
		$form->addData('rel','imed-app');
		$form->addData('ret', url('imed/social/'.$orgId.'/patient',array('s'=>'new')));

		$form->addField('psnid',array('type'=>'hidden','name'=>'psnid', 'id'=>'psnid'));

		$form->addField(
						'name',
						array(
							'type'=>'text',
							//'label' => tr('ADD PATIENT'),
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($name),
							'placeholder'=>'+ ชื่อผู้ป่วย ที่ต้องการเพิ่มให้กลุ่มดูแล',
							'attr'=>array(
								'data-query'=>url('imed/api/patients'),
								//'data-callback' => 'submit',
								'data-altfld' => 'psnid',
							),
						)
					);

		$form->addField(
						'button',
						array(
							'type' => 'button',
							'value' => '<i class="icon -addbig -white"></i>',
						)
					);

		$ret .= $form->build();
	}

	$ret .= '<h3>@Patient of Group</h3>';

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
					, (SELECT COUNT(*) FROM %imed_service% WHERE `pid` = p.`psnid`) `serviceAmt`
					FROM %imed_socialpatient% sp
						LEFT JOIN %db_person% p USING(`psnid`)
						LEFT JOIN %users% u ON u.`uid` = sp.`addby`
					%WHERE%
					ORDER BY $ORDER$';
	$dbs = mydb::select($stmt, ':uid', i()->uid);

	$ui = new Ui(NULL,'ui-card -patient -sg-flex -co-2');

	$myUid = i()->uid;
	foreach ($dbs->items as $rs) {
		$isRemoveable = $isRemovePatient || $rs->addby == $myUid;
		$cardUi = new Ui();
		$cardUi->add('<a class="btn" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'"><i class="icon -doctor"></i><span>เยี่ยมบ้าน</span></a>');
		$dropUi = new Ui();
		if ($isRemoveable) {
			$dropUi->add('<a class="sg-action" href="'.url('imed/social/'.$orgId.'/patient.remove/'.$rs->psnid).'" data-rel="none" data-removeparent="ul.ui-card.-patient>.ui-item" data-title="ลบผู้ป่วยออกจากกลุ่ม" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่ม กรุณายืนยัน?">Remove from Group</a>');
		}
		if ($isCareManager) {
			$cardUi->add('<a class="sg-action btn" href="'.url('imed/care/'.$rs->psnid,array('org'=>$orgId)).'" data-rel="#imed-app" data-pid="'.$rs->psnid.'" data-done="moveto:0,1"><i class="icon -material">assignment</i><span>Care Plan</span></a>');
		}

		if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));

		$menu = '<nav class="nav -card -sg-text-right">'
				. $cardUi->build()
				. '</nav>';

		$ui->add('<div class="header -sg-clearfix">'
			. '<a class="" href="'.url('imed', ['pid' => $rs->psnid]).'" role="patient" data-pid="'.$rs->psnid.'" class="sg-tooltip" data-url="'.url('imed/patient/shortinfo/'.$rs->psnid).'">'
			. '<img class="poster-photo -sg-48" src="'.imed_model::patient_photo($rs->psnid).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->prename.' '.$rs->name.'</span>'
			. '</a>'
			. '<span class="timestamp">Added by '.$rs->addByName.' on '.sg_date($rs->created, 'ว ดด ปปปป').'</span>'
			. '</div>'
			. '<div class="detail">เยี่ยมบ้าน '.$rs->serviceAmt.' ครั้ง</div>'
			. $menu
			//. print_o($rs,'$rs')
		);
	}

	if ($dbs->count() % 2) $ui->add('&nbsp;', '{class: "-empty"}');
	$ret .= $ui->build();

	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>