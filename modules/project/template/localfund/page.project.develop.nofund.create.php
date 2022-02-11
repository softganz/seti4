<?php
/**
* Project develope create new
*
* @param Object $self
* @return String
*/

function project_develop_nofund_create($self) {
	R::View('project.toolbar',$self,'เริ่มพัฒนาโครงการใหม่','develop');

	$maxDevProjectAllow=cfg('PROJECT.DEVELOP.MAX_PER_USER');

	if (!user_access('create project proposal')) return message('error','access denied');
	if (!user_access('administer contents')) {
		$dbs=mydb::select('SELECT `tpid` FROM %topic% WHERE type="project-develop" AND uid=:uid AND `status` IN (1,2,3)',':uid',i()->uid);
		if ($maxDevProjectAllow==1 && $dbs->_num_rows==1) {
			location('project/develop/'.$dbs->items[0]->tpid);
		} else if ($maxDevProjectAllow==0 || $dbs->_num_rows<$maxDevProjectAllow) {
			// Allow to add new develop project
		} else {
			location('project/my');
		}
	}

	$ret.='<h3>รายละเอียดโครงการที่จะเริ่มพัฒนาใหม่โดยไม่ระบุกองทุน</h3>';

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

	// Insert new development project
	$post=(object)post('topic');
	if ($post->title) {
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
		$post->fundid=SG\getFirst($post->fundid,NULL);
		$stmt='INSERT INTO %project_dev% (`tpid`, `fundid`, `status`, `pryear`, `changwat`, `ampur`, `tambon`) VALUES (:tpid, :fundid, :status, :pryear, :changwat, :ampur, :tambon)';
		mydb::query($stmt,$post);

		mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid',':tpid',$tpid, ':revid',$revid);
		//$ret.=mydb()->_query.'<br />';

		//$ret.='tpid='.$tpid.'<br />';
		//$ret.=print_o($post,'$post');
		location('project/develop/'.$tpid);
		return $ret;
	}





	// Show New Development Form
	$form = new Form([
		'variable' => 'topic',
		'action' => url('project/develop/create'),
		'id' => 'edit-topic',
		'class' => 'sg-form',
		'checkValid' => true,
		'children' => [
			'title' => [
				'type'=>'text',
				'label'=>'ชื่อโครงการที่จะเริ่มพัฒนาใหม่',
				'require'=>true,
				'class'=>'-fill',
				'value'=>htmlspecialchars($post->title)
			],
			'pryear' => [
				'type' => 'radio',
				'label' => 'สำหรับปีงบประมาณ:',
				'require' => true,
				'options' => (function() {
					$options = [];
					for ($year=date('Y')-1; $year<=date('Y')+1; $year++) {
						$options[$year]=$year+543;
					}
					return $options;
				})(),
				'value' => SG\getFirst($post->pryear,date('m')>=9 ? date('Y')+1 : date('Y'))
			],
			'changwat' => [
				'type' => 'select',
				'label' => 'จังหวัด :',
				'require' => true,
				'options' => (function() {
					$options[''] = '== เลือกจังหวัด ==';
					$provinceList=mydb::select('SELECT `provid`, `provname` FROM %project_fund% f LEFT JOIN %db_org% o USING(`orgid`) LEFT JOIN %co_province% cop ON cop.`provid`=o.`changwat` HAVING `provid` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
					foreach ($provinceList->items as $prov) {
						$options[$prov->provid] = $prov->provname;
					}
					return $options;
				})(),
				'value' => $topic->post->changwat,
			],
			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>เริ่มพัฒนาโครงการ</span>',
				'container' => array('class'=>'-sg-text-right'),
				'pretext' => '<a class="btn -link" href="'.url('project/develop/my').'"><i class="icon -cancel -gray"></i><span>ยกเลิก</span></a>',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>