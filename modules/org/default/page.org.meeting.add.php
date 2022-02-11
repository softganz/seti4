<?php
/**
*  Add new meeting
*
* @param $_POST
* @return String
*/
function org_meeting_add($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;


	$ret .= '<header class="header">'._HEADER_BACK.'<h3>เพิ่มกิจกรรมขององค์กร</h3></header>';

	$myOrg = org_model::get_my_org();

	if (empty($myOrg)) {
		$ret.='<p class="notify">ท่านยังไม่ได้กำหนด <strong>"องค์กร/หน่วยงาน"</strong> กรุณา <a href="'.url('org/new').'">สร้างหน่วยงานหรือสร้างใหม่</a> ก่อน</p>';
		return $ret;
	}


	if (post('org')) {
		$post = (object)post('org');


		if ($post->orgid && mydb::select('SELECT * FROM %org_officer% WHERE `orgid`=:orgid LIMIT 1',':orgid',$post->orgid)->_num_rows) {
			$ret.='<p class="notify">องค์กรนี้มีเจ้าหน้าที่องค์กรอยู่แล้ว หากท่านเป็นเจ้าหน้าที่ขององค์กรนี้ด้วย กรุณาติดต่อผู้ดูแลระบบเพิ่มกำหนดสิทธิ์เพิ่มเติม</p>';
		} else {
			$post->name=trim($post->name);
			$post->uid=i()->uid;
			$post->created=date('U');

			if (!$post->name) return;

			if ($post->orgid) $orgName=mydb::select('SELECT `name` FROM %db_org% WHERE `orgid`=:orgid LIMIT 1',':orgid',$post->orgid)->name;

			if ($post->name!=$orgName) unset($post->orgid);

			if (!$post->orgid) {
				// Create new org
				$isDup=mydb::select('SELECT `orgid` FROM %db_org% WHERE `name`=:name LIMIT 1',':name',$post->name)->orgid;
				//$ret.='is dup ='.$isDup;
				if ($isDup) {
					$post->orgid=$isDup;
				} else {
					mydb::query('INSERT INTO %db_org% (`name`, `sector`, `uid`, `created`) VALUES (:name, :sector, :uid, :created)', $post);
					$post->orgid=mydb()->insert_id;
				}
			}

			if ($post->orgid) {
				// Add to org
				mydb::query('INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, "ADMIN")',$post);
			}
			location('org/'.$post->orgid.'/meeting.add');
			//$ret.=$orgName.print_o($post,'$post');
			return $ret;
		}
	}


	$post = (object) post('meeting');

	if ($post->doings && $post->atdate) {
		$post->uid=i()->uid;
		$post->atdate=sg_date($post->atdate,'U');
		if (!$post->issue) $post->issue=NULL;
		if (!$post->tpid) $post->tpid=NULL;
		$stmt='INSERT INTO %org_doings% (`orgid`, `tpid`, `calid`, `uid`, `issue`, `doings`, `place`, `atdate`, `fromtime`)
						VALUES
						(:orgid, :tpid, :calid, :uid, :issue, :doings, :place, :atdate, :fromtime) ';
		mydb::query($stmt,$post);
		//$ret.=mydb()->_query.print_o($post,'$post');
		if (mydb()->_error) {
			$ret.=message('error','มีปัญหาในการสร้างกิจกรรมใหม กรุณาติดต่อผู้ดูแลระบบ:ข้อผิดพลาด : '.mydb()->_error);
		} else {
			$doid=mydb()->insert_id;
			location('org/'.$post->orgid.'/meeting.info/'.$doid);
		}
	}

	$post->atdate=SG\getFirst($post->atdate,date('d/m/Y'));
	$post->fromtime=SG\getFirst($post->fromtime,'09:00');

	$form = new Form('meeting',url(q()),'org-add-meeting', 'sg-form');
	$form->addData('checkValid', true);

	$form->addField(
		'orgid',
		array(
			'type' => 'select',
			'label' => 'กิจกรรมขององค์กร:',
			'class' => '-fill',
			'options' => mydb::select(
				'SELECT `orgid`,`name` FROM %db_org% WHERE `orgid` IN (:myorg) ORDER BY CONVERT(`name` USING tis620) ASC;
				-- {key: "orgid", value: "name"}',
				[':myorg' => 'SET:'.$myOrg]
			)->items,
			'value' =>  SG\getFirst($post->orgid,$orgId),
		)
	);

	$optionsProject = ['' => '==เลือกโครงการ=='];
	$orgProject = org_model::get_orgproject();
	foreach ($orgProject->items as $item) $optionsProject[$item->tpid]=$item->title;

	$form->addField(
		'tpid',
		array(
			'type' => 'select',
			'label' => 'โครงการ:',
			'class' => '-fill',
			'options' => $optionsProject,
			'value' => $post->tpid,
		)
	);

	$issues=mydb::select('SELECT `tid` issue,`name` FROM %tag% WHERE `taggroup`="org:issue" AND `ownid` IN (:myorg) ORDER BY CONVERT(`name` USING tis620) ASC',':myorg','SET:'.$myOrg);
	$optionsIssue = array();
	foreach ($issues->items as $item) $optionsIssue[$item->issue]=$item->name;

	$form->addField(
		'issue',
		array(
			'type' => 'select',
			'label' => 'ประเด็น',
			'class' => '-fill',
			'options' => $optionsIssue,
			'value' => $post->issue,
		)
	);

	$form->addField('calid', ['type' => 'hidden', 'value' => $post->calid]);

	$form->addField(
		'doings',
		array(
			'type' => 'text',
			'label' => 'ชื่อกิจกรรม',
			'require' => true,
			'class' => 'sg-autocomplete -fill',
			'value' => htmlspecialchars($post->doings),
			'attr' => array('data-altfld'=>'edit-meeting-calid','data-query'=>url('org/api/meeting'), 'data-callback'=>'orgMeetingSelect'),
		)
	);

	$form->addField(
		'atdate',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'class' => 'sg-datepicker -date',
			'require' => true,
			'value' => htmlspecialchars($post->atdate),
		)
	);

	$form->addField(
		'fromtime',
		array(
			'type' => 'time',
			'label' => 'เวลา',
			'value' => htmlspecialchars($post->fromtime),
		)
	);

	$form->addField(
		'place',
		array(
			'type' => 'text',
			'label' => 'สถานที่',
			'class' => '-fill',
			'value' => htmlspecialchars($post->place),
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -material">done_all</i><span>สร้างกิจกรรม</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();
	return $ret;
}
?>