<?php
/**
* Organization Officer
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_officer($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	$getUserId = post('uid');
	$getMembership = SG\getFirst(post('membership'),'VIEWER');


	$ret = '';

	if (empty($orgId))
		return message('error','ขออภัย : ไม่มีโครงการอยู่ในระบบ');


	$orgMembership = $orgInfo->is->membership;

	$isAdmin = i()->admin;

	$isEditable = user_access('administer orgs')
						|| in_array($orgMembership, array('MANAGER','ADMIN','OWNER','TRAINER'));

	$ret .= '<div id="org-officer">';
	//$ret .= 'Org Member = '.$orgMember.'<br />'.print_o($orgInfo,'$orgInfo');

	if ($isEditable && $getUserId) {
		$stmt = 'INSERT INTO %org_officer%
						(`orgid`, `uid`, `membership`)
						VALUES
						(:orgid, :uid, :membership)
						ON DUPLICATE KEY UPDATE
						`membership` = :membership;';
		mydb::query($stmt,':orgid',$orgId, ':uid', $getUserId, ':membership', $getMembership);
		//$ret .= mydb()->_query;

		model::watch_log('org','add owner',$user->name.'('.$user->uid.') was added to be an officer of org '.$orgId.' by '.i()->name.'('.i()->uid.')');
	}


	$ret .= '<h3 class="title -box">Who has access</h3>';

	$stmt = 'SELECT
						of.`uid` `officerUid`, u.`uid`, u.`username`, u.`name`, u.`email`, UPPER(of.`membership`) `membership`
					FROM %org_officer% of
						LEFT JOIN %users% u USING(`uid`)
					WHERE `orgid` = :orgid';
	$memberDb = mydb::select($stmt,':orgid', $orgId);
	//$ret .= print_o($memberDb);

	$tables = new Table();
	$tables->addId('org-officer-list');
	$tables->addClass('org-officer-list');
	$tables->colgrp = array('','width="100%"','class="-hover-parent" ');
	foreach ($memberDb->items as $rs) {
		$ui = new Ui();
		if ($isEditable) {
			$ui->add('<a class="sg-action" href="'.url('org/'.$orgId.'/officer.view/'.$rs->uid).'" data-rel="box" data-width="512"><i class="icon -view"></i></a>');
		}
		if ($isEditable && $rs->officerUid != i()->uid) {
			$ui->add('<a class="sg-action" href="'.url('org/setting/'.$orgId.'/officer.remove/'.$rs->uid).'" data-rel="notify" data-removeparent="tr" data-title="ลบชื่อออกจากการองค์กร"  data-confirm="ต้องการลบชื่อออกจากการองค์กร กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>');
		}
		$menu = $ui->count() ? '<nav class="nav -icons -btn">'.$ui->build().'</nav>' : '';
		$tables->rows[] = array(
												'<img class="ownerphoto" src="'.model::user_photo($rs->username).'" width="29" height="29" alt="'.htmlspecialchars($rs->name).'" title="'.htmlspecialchars($rs->name).'" />',
												$rs->name
												.($rs->uid == i()->uid ? ' (is you)' : '')
												.($isEditable ? '<br /><span class="email">'.$rs->email.' ('.$rs->username.')</span>' : '')
												.'</a>',
												($rs->officerUid == i()->uid ? 'Is ' : '').$rs->membership,
												$menu,
											);
	}
	$ret .= '<div style="height: 400px;overflow: auto;">'.$tables->build().'</div>';
	//$ret .= print_o($memberDb,'$memberDb');


	if ($isEditable) {
		$form = new Form(NULL, url('org/'.$orgId.'/officer'),'add-officer', 'sg-form org-officer-form');
		$form->addData('rel','replace:#org-officer');

		$form->addField('uid',array('type'=>'hidden','name'=>'uid', 'id'=>'uid'));

		$form->addField(
						'name',
						array(
							'type'=>'text',
							'class'=>'sg-autocomplete -fill',
							'require'=>true,
							'value'=>htmlspecialchars($name),
							'placeholder'=>'ระบุ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการแบ่งปันการใช้งาน',
							'attr'=>array(
												'data-query'=>url('api/user'),
												//'data-callback' => 'submit',
												'data-altfld' => 'uid',
											),
						)
					);
		$form->addField(
						'membership',
						array(
							'type' => 'select',
							'options' => array('ADMIN'=>'ADMIN','MANAGER'=>'MANAGER','TRAINER'=>'TRAINER','OFFICER'=>'OFFICER','VIEWER'=>'VIEWER','REGULAR MEMBER'=>'REGULAR MEMBER'),
							'value' => 'VIEWER',
						)
					);

		$form->addField(
						'button',
						array(
							'type' => 'button',
							'value' => '<i class="icon -save -white"></i>',
						)
					);

		$ret .= $form->build();
	}


	$ret .= '<style type="text/css">
	.org-officer-list .email {font-size: 0.8em; color: gray;}
	.org-officer-list td:nth-child(1) {width: 29px;}
	.org-officer-list td:nth-child(3) {width: 5em; white-space: nowrap;}
	.org-officer-list td:nth-child(4) {width: 29px;}
	.org-officer-form {display: flex; justify-content: space-between;}
	.org-officer-form .form-item {margin-right: 4px;}
	.org-officer-form .form-item.-edit-name {width:100%;}
	</style>';

	$ret .= '</div><!-- org-officer -->';
	return $ret;
}
?>