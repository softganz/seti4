<?php
/**
* Project develope create new
*
* @param Object $self
* @return String
*/

function project_develop_create($self, $projectSetId = NULL) {
	R::View('project.toolbar', $self, 'เริ่มพัฒนาโครงการใหม่', 'develop', NULL,'{showPrint: false}');

	$maxDevProjectAllow=cfg('PROJECT.DEVELOP.MAX_PER_USER');

	if (!user_access('create project proposal')) return message('error','access denied');

	if (!user_access('administer contents')) {
		$dbs=mydb::select('SELECT `tpid` FROM %topic% WHERE type="project-develop" AND uid=:uid AND `status` IN (1,2,3)',':uid',i()->uid);
		if ($maxDevProjectAllow==1 && $dbs->_num_rows==1) {
			location('project/develop/'.$dbs->items[0]->tpid);
		} else if ($maxDevProjectAllow == 0 || $dbs->_num_rows < $maxDevProjectAllow) {
			// Allow to add new develop project
		} else {
			location('project/my');
		}
	}



	$ret.='<h3>รายละเอียดโครงการที่จะเริ่มพัฒนาใหม่</h3>';

	$curDate=date('Y-m-d H:i');

	if ( ($curDate>=cfg('project.develop.startdate') && $curDate<=cfg('project.develop.enddate')) ) {
		; // do nothing
	} else {
		$msg='ปิดรับการพัฒนาโครงการ : ขออภัย : ช่วงนี้งดรับพัฒนาโครงการใหม่<br />';

		$startDate=cfg('project.develop.startdate');
		$endDate=cfg('project.develop.enddate');
		if (empty($startDate) || $startDate<$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไปยังไม่ได้กำหนด';
		else if ($startDate>$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไป คือ <strong>'.sg_date($startDate,'ว ดดด ปปปป H:i').' น. - '.sg_date($endDate,'ว ดดด ปปปป H:i').' น.</strong>';

		$ret.=message('error',$msg);

		//.(cfg('project.develop.startdate')?'<br />ช่วงเวลาเปิดรับพัฒนาโครงการคือ '.sg_date(cfg('project.develop.startdate'),'ว ดดด ปปปป H:i').' น. ถึง '.sg_date(cfg('project.develop.enddate'),'ว ดดด ปปปป H:i').' น.':''));
		return $ret;
	}



	$post = (object) post('topic');
	if ($post->title) {
		$data = new stdClass();
		$data->title = $post->title;
		$data->budget = $post->budget;
		$data->changwat = $post->changwat;
		$data->projectset = $post->parent;
		$data->date_approve = post('date_approve') ? sg_date(post('date_approve'), 'Y-m-d') : NULL;
		if ($data->date_approve) {
			$data->pryear = sg_date($data->date_approve,'Y');
		}

		$result = R::Model('project.develop.create', $data);

		if (post('refid')) {
			$stmt = 'UPDATE %project_tr% SET `refid` = :tpid WHERE `trid` = :refid LIMIT 1';
			mydb::query($stmt, ':tpid', $result->tpid, ':refid', post('refid'));
			//$ret.=mydb()->_query;
		}
		//$ret.=print_o($result,'$result');
		//$ret.=print_o($post,'$post');
		//$ret.=print_o($data,'$data');
		//$ret.=print_o($fundInfo,'$fundInfo');
		location('project/develop/'.$result->tpid.'/view/edit');

		/*
		$post->uid=i()->uid;
		$post->type='project-develop';
		$post->status=1;
		$post->parent=SG\getFirst($post->parent,NULL);
		$post->changwat=SG\getFirst($post->changwat,NULL);
		$post->comment=_COMMENT_READWRITE;
		$post->body='';
		$post->created='func.NOW()';
		$post->timestamp='func.NOW()';
		$post->ip = ip2long(GetEnv('REMOTE_ADDR'));

		$stmt='INSERT INTO %topic% (`type`, `parent`, `status`, `title`, `uid`, `changwat`, `created`, `comment`, `ip`) VALUES (:type, :parent, :status, :title, :uid, :changwat, :created, :comment, :ip)';
		mydb::query($stmt,$post);
		$tpid=$post->tpid=mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';

		$stmt='INSERT INTO %topic_revisions% (`tpid`, `uid`, `title`, `body`, `timestamp`) VALUES (:tpid, :uid, :title, :body, :timestamp)';
		mydb::query($stmt,$post);
		$revid=$post->revid=mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';

		$post->pryear=date('Y');
		$stmt='INSERT INTO %project_dev% (`tpid`,`status`,`pryear`) VALUES (:tpid, :status, :pryear)';
		mydb::query($stmt,$post);

		mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid',':tpid',$tpid, ':revid',$revid);
		//$ret.=mydb()->_query.'<br />';

		//$ret.='tpid='.$tpid.'<br />';
		//$ret.=print_o($post,'$post');
		location('project/develop/'.$tpid);
		*/
		return $ret;
	}





	// Show New Development Form
	$form = new Form('topic',url('project/develop/create'),'edit-topic','sg-form');
	$form->addData('checkValid',true);

	if ($projectSetId) {
		$projectParentTitle = mydb::select('SELECT `title` FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$projectSetId)->title;
		$form->addText('<b>ภายใต้ชุดโครงการ : '.$projectParentTitle.'</b>');

		$form->addField(
						'parent',
						array(
							'type'=>'hidden',
							'label'=>'ชุดโครงการ:',
							'value' => $projectSetId,
							)
						);
	} else {
		$stmt = 'SELECT * FROM %project% p
							LEFT JOIN %topic% USING(`tpid`)
						WHERE (`prtype` = "ชุดโครงการ" AND `project_status` = "กำลังดำเนินโครงการ") OR (`tpid` = :projectset)
						ORDER BY `title` ASC';
		$prSets = mydb::select($stmt, ':projectset',$projectSetId);
		if ($prSets->_num_rows) {
			$selectOptions  = array();
			foreach ($prSets->items as $item) $selectOptions[$item->tpid]=$item->title;
			$form->addField(
							'parent',
							array(
								'type'=>$prSets->_num_rows<=5?'radio':'select',
								'label'=>'ชุดโครงการ:',
								'require'=>true,
								'options'=>$selectOptions,
								'value' => $projectSetId,
								)
							);
		}
	}

	$form->addField(
						'title',
						array(
							'type'=>'text',
							'label'=>'ชื่อโครงการที่จะเริ่มพัฒนาใหม่',
							'require'=>true,
							'class'=>'-fill',
							'value'=>htmlspecialchars($post->title),
							'placeholder'=>'ระบุชื่อโครงการที่ต้องการเสนอ',
							)
						);

	$property=property('project');

	$regionList=SG\getFirst(cfg('project.region'),$property['region']);

	unset($selectOptions);
	for ($year=date('Y')-1; $year<=date('Y')+1; $year++) {
		$selectOptions[$year]=$year+543;
	}
	$form->addField(
						'pryear',
						array(
							'type'=>'radio',
							'label'=>'ประจำปี :',
							'require'=>true,
							'options'=>$selectOptions,
							'value'=>SG\getFirst($post->pryear,date('Y'))
							)
						);

	$selectOptions = array(''=>'--- เลือกจังหวัด ---');
	$provinceList=mydb::select('SELECT `provid`, `provname` FROM %co_province% '.($regionList=='all' ? '' : ' WHERE `provid` IN ('.$regionList.') ').' ORDER BY CONVERT(`provname` USING tis620) ASC');
	//if ($provinceList->_num_rows<=10) $form->changwat->type='radio';
	//else $form->changwat->options[-1]='--- เลือกจังหวัด ---';
	foreach ($provinceList->items as $prov) {
		$selectOptions[$prov->provid]=$prov->provname;
	}
	$form->addField(
						'changwat',
						array(
							'type'=>'select',
							'label'=>'จังหวัด :',
							'require'=>true,
							'options'=>$selectOptions,
							'value'=>$topic->post->changwat,
							)
						);


	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>เริ่มพัฒนาโครงการ</span>',
						'container' => array('class'=>'-sg-text-right'),
					)
				);

	$ret .= $form->build();

	$stmt='SELECT t.*, u.`name`, tu.`uid` `tuid`, tu.`membership`, p.`title` `parentTitle`
				FROM %project_dev% d
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %users% u USING(uid)
					LEFT JOIN %topic% p ON t.`parent` = p.`tpid`
					LEFT JOIN %topic_user% tu ON tu.`tpid` = t.`tpid` AND tu.`uid` = :uid
				WHERE t.`type` = "project-develop" AND (t.`uid` = :uid OR tu.`uid` = :uid)
				ORDER BY `changed` DESC';
	$dbs=mydb::select($stmt,':uid',i()->uid);

	if ($dbs->_num_rows) {
		$tables = new Table();;
		$tables->caption = 'รายชื่อโครงการกำลังพัฒนา';
		$tables->thead = array('no'=>'', 'title'=>'ชื่อโครงการพัฒนา', 'date created'=>'วันที่เริ่มพัฒนา', 'date changed'=>'แก้ไขล่าสุด','พัฒนาโดย');
		$no = 0;
		if ($dbs->_num_rows) {
			foreach ($dbs->items as $rs) {
				$tables->rows[] = array(++$no,
													'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
													.($rs->parentTitle ? '<br /><em>'.$rs->parentTitle.'</em>' : ''),
													sg_date($rs->created,'ว ดดด ปปปป'),
													$rs->changed?sg_date($rs->changed,'ว ดดด ปปปป H:i').' น.':'',
													$rs->name
														);
			}
		} else {
			$tables->rows[]=array('<td colspan="3">ไม่มีโครงการที่กำลังพัฒนา <a href="'.url('project/develop/create').'">คลิกที่นี่</a> เพื่อเริ่มต้นพัฒนาโครงการใหม่</td>');
		}
		$ret .= '<section class="section -project-develop">'.$tables->build().'</div>';
	} else {
		$ret.='<p>ไม่มีโครงการที่กำลังพัฒนา</p>';
	}

	$ret .= '<style type="text/css">
	.section.-project-develop {margin-top: 64px;}
	</style>';
	return $ret;
}
?>