<?php
/**
 * Camery edit
 *
 * @param String $camId
 * @return String
 */
function flood_camera_edit($self, $camId) {
	$rs=R::Model('flood.camera.get',$camId);

	if ($rs->_empty) return message('error','Data not found');
	else if (!user_access('administrator floods,operator floods','edit own flood content',$rs->uid)) return message('error','access denied');
	else if ($_POST['cancel']) location('flood/cam/'.$camId);


	//return print_o(post(),'post');
	if (post('switch')) {
		$ret .= '<h3>SWITCH '.$camId.' to '.post('switch').'</h3>';
		$stmt = 'UPDATE %flood_cam% t, %flood_cam% t2 SET t.`camid` = 0, t2.`camid` = t.`camid`, t.`camid` = t2.`camid` WHERE t.`camid` = :from AND t2.`camid` = :to';

		//$stmt = 'update %flood_cam% t1 set t1.`camid` = (case when t1.camid = :from then :to else :from end) where t1.`camid` in (:from, :to)';

		$stmt = 'UPDATE %flood_cam% SET `camid` = 0 WHERE `camid` = :from';
		mydb::query($stmt, ':from', $camId, ':to', post('switch'));
		$ret .= mydb()->_query.'<br />';

		$stmt = 'UPDATE %flood_cam% SET `camid` = :from WHERE `camid` = :to';
		mydb::query($stmt, ':from', $camId, ':to', post('switch'));
		$ret .= mydb()->_query.'<br />';

		$stmt = 'UPDATE %flood_cam% SET `camid` = :to WHERE `camid` = 0';
		mydb::query($stmt, ':from', $camId, ':to', post('switch'));
		$ret .= mydb()->_query.'<br />';

		return $ret;
	} else if (post('camera')) {
		$post = (object)post('camera');
		if (empty($post->title)) $error[]='กรุณาป้อน "Camera title" ';
		if ($error) {
			http_response_code(406);
			return 'ERROR : '.implode(',',$error);
		} else {
			$post->uid = SG\getFirst(i()->uid,'func.NULL');
			$post->created = date('U');
			$stmt = 'UPDATE %flood_cam% SET
				`title` = :title
				, `location` = :location
				, `camip` = :camip
				, `imgurl` = :imgurl
				, `port` = :port
				, `uname` = :uname
				, `passwd` = :passwd
				, `desc` = :desc
				, `sponsor_name` = :sponsor_name
				, `sponsor_text` = :sponsor_text
				, `sponsor_url` = :sponsor_url
				, `sponsor_logo` = :sponsor_logo
				, `options` = :options
				, `adminremark` = :adminremark
				WHERE `camid` = :camid LIMIT 1';

			mydb::query($stmt,$post,':camid',$camId);

			return 'UPDATEED';
		}
	} else {
		$post=$rs;
	}



	R::View('flood.toolbar',$self,tr('Camera Edit','แก้ไขรายละเอียดกล้อง'),NULL,$rs);



	if ($error) $ret.=message('error',$error);

	$stmt = 'SELECT * FROM %flood_cam% ORDER BY `title` ASC';
	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) $cameraList[$rs->camid] = $rs->title;

	$form = new Form(NULL, url('flood/camera/edit/'.$camId), NULL, 'sg-form');
	$form->addField(
		'switch',
		array(
			'type' => 'select',
			'label' => 'Switch Camera '.$post->title.' to:',
			'class' => '-fill',
			'options' => array(''=>'== เลือกกล้อง ==')+$cameraList,
			'posttext' => '<div class="input-append"><span><button class="btn"><i class="icon -material">arrow_forward</i><span>SWITCH CAMERA</span></button></span></div>',
			'container' => '{class: "-group"}',
		)
	);
	$ret .= $form->build();


	$form = new Form('camera', url('flood/camera/edit/'.$camId), 'camera-add', 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'reload:'.url('flood/cam/'.$camId));
	$form->addData('checkValid', true);

	$form->AddConfig('title','Camera name : <strong>'.$post->name.'</strong>');

	$form->addField(
		'title',
		array(
			'type'=>'text',
			'label'=>'Camera title',
			'class'=>'-fill',
			'require'=>true,
			'value'=>$post->title,
		)
	);

	$form->addField(
		'camip',
		array(
			'type'=>'text',
			'label'=>'Camera IP',
			'class'=>'-fill',
			'value'=>$post->camip,
		)
	);

	$form->addField(
		'imgurl',
		array(
			'type'=>'text',
			'label'=>'Camera image url',
			'class'=>'-fill',
			'value'=>$post->imgurl,
		)
	);

	$form->addField(
		'port',
		array(
			'type'=>'text',
			'label'=>'Camera port',
			'class'=>'-fill',
			'value'=>$post->port,
		)
	);

	$form->addField(
		'uname',
		array(
			'type'=>'text',
			'label'=>'Camera username',
			'class'=>'-fill',
			'value'=>$post->uname,
		)
	);

	$form->addField(
		'passwd',
		array(
			'type'=>'text',
			'label'=>'Camera password',
			'class'=>'-fill',
			'value'=>$post->passwd,
		)
	);

	$form->addField(
		'location',
		array(
			'type'=>'text',
			'label'=>'Camera Lat/Lng',
			'class'=>'-fill',
			'value'=>$post->location
		)
	);

	$form->addField(
		'desc',
		array(
			'type'=>'textarea',
			'label'=>'Description',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->desc),
		)
	);

	$form->addField(
		'sponsor_name',
		array(
			'type'=>'text',
			'label'=>'Sponsor Name',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->sponsor_name),
		)
	);

	$form->addField(
		'sponsor_text',
		array(
			'type'=>'text',
			'label'=>'Sponsor Text',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->sponsor_text),
		)
	);

	$form->addField(
		'sponsor_url',
		array(
			'type'=>'text',
			'label'=>'Sponsor Url',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->sponsor_url),
		)
	);

	$form->addField(
		'sponsor_logo',
		array(
			'type'=>'text',
			'label'=>'Sponsor Logo',
			'class'=>'-fill',
			'value'=>htmlspecialchars($post->sponsor_logo),
		)
	);

	$form->addField(
		'options',
		array(
			'type'=>'textarea',
			'label'=>'Options',
			'class'=>'-fill',
			'value'=>$post->options,
		)
	);

	$form->addField(
		'adminremark',
		array(
			'type'=>'textarea',
			'label'=>'Admin Remark',
			'class'=>'-fill',
			'value'=>$post->adminremark,
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
			'pretext'=>'<a class="btn -link -cancel" href="'.url('flood/cam/'.$camId).'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret.=$form->build();
	return $ret;
}
?>