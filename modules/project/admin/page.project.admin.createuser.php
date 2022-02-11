<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_admin_createuser($self) {
	$q=post('q');
	$uid=post('id');
	$order=SG\getFirst($para->order,post('o'),'CONVERT(u.`name` USING tis620)');
	$sort=SG\getFirst($para->sort,post('s'),'ASC');

	R::View('project.toolbar',$self,'Create New User','admin');
	$self->theme->sidebar=R::View('project.admin.menu','member');

	$ui=new ui();
	$ui->add(
				'<form id="search-member" class="search-box sg-form" method="get" action="'.url('project/admin/user').'" role="search">'
				.'<input type="hidden" name="u" id="id" />'
				.'<input class="sg-autocomplete" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email" />'
				.'<button class="btn" type="submit"><i class="icon -search"></i><span class="-hidden">ค้นหาสมาชิก</span></button>'
				.'</form>'
			);

	$navbar='<nav class="nav -page"><header class="header -hidden"><h3>User Management</h3></header>'._NL;
	$navbar.=$ui->build();
	$navbar.='</nav><!--navbar-->'._NL;
	$self->theme->navbar=$navbar;


	$post=(object)post('user');
	if ($post->username) {
		if (mydb::select('SELECT `uid` FROM %users% WHERE `username` = :username LIMIT 1',':username', $post->username)->uid) {
			$error[]='ชื่อสมาชิก (Username) <strong><em>'.$post->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว'; //-- duplicate username
		}
		if (empty($post->password) || empty($post->name)) $error[]='กรุณาป้อนข้อมูลให้ครบถ้วน';
		if (!$error) {
			$post->username=strtolower(trim($post->username));
			$post->password=trim($post->password);
			if (empty($post->password)) $post->password=substr(md5(uniqid()), 0, 8);
			$post->name=trim($post->name);
			$post->email=trim($post->email);
			$post->roles=str_replace('+',',',trim($post->roles));
			$post->phone=trim($post->phone);
			$post->epassword=sg_encrypt($post->password,cfg('encrypt_key'));
			$post->datein=date('Y-m-d H:i:s');
			$post->status='enable';
			if (empty($post->name)) $post->name=$post->username;
			$stmt='INSERT INTO %users%
						(`username`, `password`, `name`, `status`, `roles`, `email`, `phone`, `datein`)
						VALUES
						(:username, :epassword, :name, :status, :roles, :email, :phone, :datein)';
			mydb::query($stmt,$post);
			if (!mydb()->_error) {
				$uid=$post->uid=mydb()->insert_id;
				// Create organization
				if (!$post->orgid && $post->orgname) {
					$stmt='INSERT INTO %db_org% (`name`, `sector`, `created`) VALUES (:orgname, :sector, :created)';
					mydb::query($stmt,':orgname',$post->orgname, ':sector',9, ':created',date('U'));
					$post->orgid=mydb()->insert_id;
				}
				// Add member to organization
				if ($post->orgid) {
					mydb::query('INSERT INTO %org_officer% (`orgid`,`uid`,`membership`) VALUES (:orgid,:uid,:membership)',$post);
				}

				// Create member first project
				if ($post->title) {
					$topic->type='project';
					$topic->status=_LOCK;
					$topic->orgid=$post->orgid;
					$topic->uid=$uid;
					$topic->title=$post->title;
					$topic->created=$topic->timestamp=date('Y-m-d H:i:s');
					$topic->ip=ip2long(GetEnv('REMOTE_ADDR'));
					$stmt='INSERT INTO %topic% (`type`,`status`,`orgid`,`uid`,`title`,`created`,`ip`) VALUES (:type,:status,:orgid,:uid,:title,:created,:ip)';
					mydb::query($stmt,$topic);

					if (!mydb()->_error) {
						$tpid=$topic->tpid=mydb()->insert_id;

						// Create topic_revisions
						$stmt='INSERT INTO %topic_revisions% (`tpid`,`uid`,`title`,`timestamp`) VALUES (:tpid,:uid,:title,:timestamp)';
						mydb::query($stmt,$topic);
						$revid=$topic->revid=mydb()->insert_id;
						mydb::query('UPDATE %topic% SET `revid`=:revid WHERE `tpid`=:tpid LIMIT 1',$topic);

						// Create topic_user
						mydb::query('INSERT INTO %topic_user% (`tpid`,`uid`,`membership`) VALUES (:tpid,:uid,"Owner")',$topic);

						// Create project
						$project->tpid=$tpid;
						$project->pryear=$post->pryear;
						$stmt='INSERT INTO %project% (`tpid`,`pryear`) VALUES (:tpid,:pryear)';
						mydb::query($stmt,$project);
					}

					//$ret.='tpid='.$tpid.' revid='.$revid.'<br />'.print_o($topic,'$topic');
				}
			}
			location('project/admin/user/'.$uid);
		}
	}

	if ($error) $ret.=message('error',$error);
	$ret.=__project_admin_user_form($post);

	//$ret.=print_o(post(),'post');
	return $ret;
}

function __project_admin_user_form($post) {
	$form=new Form([
		'variable' => 'user',
		'action' => url('project/admin/createuser'),
		'id' => 'org-add-user',
		'class' => 'sg-form',
		'checkValid' => true,
		'children' => [
			'orgid' => ['type' => 'hidden', 'value' => $post->orgid],
			'<h3>รายละเอียดสมาชิก</h3>',
			'username' => [
				'type' => 'text',
				'label' => 'ชื่อสมาชิก (Username)',
				'require' => true,
				'class' => '-fill',
				'value' => $post->username,
				'style' => 'text-transform: lowercase !important;',
			],
			'password' => [
				'type' => 'text',
				'label' => 'รหัสผ่าน (Password)',
				'require' => true,
				'class' => '-fill',
				'value' => $post->password,
			],
			'name' => [
				'type' => 'text',
				'label' => 'ชื่อ-นามสกุลจริง (Real Name)',
				'require' => true,
				'class' => '-fill',
				'value' => $post->name,
			],
			'email' => [
				'type' => 'text',
				'label' => 'อีเมล์ (E-Mail)',
				'class' => '-fill',
				'value' => $post->email,
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์ (Phone)',
				'class' => '-fill',
				'value' => $post->phone,
			],
			'orgname' => [
				'type' => 'text',
				'label' => 'ชื่อหน่วยงานต้นสังกัด (Organization)',
				'require' => true,
				'value' => $post->orgname,
				'description' => 'กรุณาป้อนชื่อหน่วยงานและเลือกจากรายการที่แสดง',
				'class' => 'sg-autocomplete -fill',
				'attr' => ['data-altfld'=>'edit-user-orgid','data-query'=>url('org/api/org','sectorX=other')],
			],
			'membership' => [
				'type' => 'select',
				'label' => 'ประเภทสมาชิก:',
				'options' => ['MEMBER'=>'Regular Member', 'OFFICER'=>'Officer','TRAINER'=>'Trainer','ADMIN'=>'Admin'],
				'value' => $post->membership,
			],
			'roles' => [
				'type' => 'radio',
				'label' => 'กลุ่มสมาชิก',
				'options' => (function() {
					$result = [];
					foreach (cfg_db('roles') as $key => $value) {
						if (in_array($key,array('anonymous','member','admin'))) continue;
						$result[$key]=$key;
					}
					return $result;
				})(),
				'value' => $post->roles,
			],
			'<h3>รายละเอียดโครงการ</h3>',
			'pryear' => [
				'type' => 'radio',
				'label' => 'ประจำปีงบประมาณ',
				'options' => (function() {
					$result = [];
					for ($year=date('Y')-1; $year<=date('Y')+1; $year++) {
						$result[$year]=$year+543;
					}
					return $result;
				})(),
				'display' => 'inline',
				'value' => SG\getFirst($post->pryear,date('Y')),
			],
			'title' => [
				'label' => 'ชื่อโครงการ/กิจกรรม (กรณีต้องการสร้างติดตามโครงการให้โดยอัตโนมัต)',
				'type' => 'text',
				'class' => '-fill',
				'value' => $post->title,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);
	$ret .= $form->build();
	return $ret;
}
?>