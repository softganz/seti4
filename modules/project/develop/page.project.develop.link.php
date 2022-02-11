<?php
function project_develop_link($self,$tpid) {
	if (!user_access('administer contents')) return message('error','Access denied');


	$stmt = 'SELECT t.*,  u.`username`, u.`name`
		FROM %topic% t
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %topic_revisions% r USING (`revid`)
			LEFT JOIN %project_dev% d ON d.`tpid`=t.`tpid`
			LEFT JOIN %topic% ps ON ps.`tpid`=t.`parent`
			LEFT JOIN %tag% c ON c.`taggroup`="project:category" AND c.`catid`=d.`category`
		WHERE t.`tpid` = :tpid LIMIT 1';

	$rs = mydb::select($stmt,':tpid',$tpid);

	R::View('project.toolbar', $self, $rs->title, 'develop', $rs,'{showPrint: false}');

	$ui=new ui();
	$ui->add('<a href="'.url('project/develop/'.$tpid).'">รายละเอียดโครงการ</a>');
	$ui->add('<span>ผู้พัฒนาโครงการ <a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username).'" width="24" height="24" alt="'.htmlspecialchars($rs->name).'" title="'.htmlspecialchars($rs->name).'" /> '.$rs->name.'</a></span>');
	// Show member of this project
	$member=mydb::select('SELECT u.`uid`, u.`username`, u.`name`, tu.`membership` FROM %topic_user% tu LEFT JOIN %users% u ON u.`uid`=tu.`uid` WHERE `tpid`=:tpid',':tpid',$tpid);
	if ($member->_num_rows) $ui->add('<span>พี่เลี้ยง</span>');
	foreach ($member->items as $mrs) $ui->add('<span><a href="'.url('project/list',array('u'=>$mrs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($mrs->username).'" width="24" height="24" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" /> '.$mrs->name.'</a> '.(user_access('administer projects') || project_model::is_trainer_of($topic->tpid)?'<a class="sg-action" href="'.url('project/edit/removeowner/'.$tpid.'/'.$mrs->uid).'" data-rel="notify" data-confirm="ต้องการลบสมาชิกออกจากโครงการ กรุณายืนยัน?" data-removeparent="li"><i class="icon -cancel -gray"></i></a></span>':''));

	list($oldProject,$oldDevelop)=explode(',', $rs->homepage);
	if ($oldProject) {
		$ui->add('<a href="'.url('project/'.$oldProject.'/eval.valuation').'">ประเมินผลโครงการเดิม</a>');
	}
	if ($oldDevelop) {
		$ui->add('<a href="'.url('project/develop/'.$oldDevelop).'">โครงการพัฒนาเดิม</a>');
	}
	$ret.='<div class="reportbar -no-print">'.$ui->build('ul').'</div>';

	// Update previous project
	$post=(object)post('topic');
	if (property_exists($post,'thread')) {
		if ($post->thread=='' || $post->thread<=0) $post->thread=NULL;
		$stmt='UPDATE %topic% SET `thread` = :thread WHERE `tpid`=:tpid LIMIT 1';
		mydb::query($stmt,':tpid',$tpid, ':thread', $post->thread);
		location('project/develop/'.$tpid);
		return $ret;
	}

	$form = new Form([
		'variable' => 'topic',
		'action' => url('project/develop/link/'.$tpid),
		'id' => 'edit-topic',
		'children' => [
			'thread' => [
				'type' => 'text',
				'label' => 'หมายเลขโครงการเดิม',
				'require' => true,
				'value' => $rs->thread,
			],
			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done</i><span>บันทึกเชื่อมโยงโครงการ</span>',
			],
		],
	]);

	$ret .= $form->build();
	$ret .= '<p>กรุณาป้อนหมายเลขของโครงการเดิมโดยค้นหาโครงการเดิมและนำหมายเลขโครงการจาก url เช่น http://www.example.com/project/<span style="font-size:1.4em;color:red;">2500</span> หมายเลขโครงการคือ <span style="font-size:1.4em;color:red;">2500</span></p>';

	return $ret;
}
?>