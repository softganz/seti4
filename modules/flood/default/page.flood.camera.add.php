<?php
/**
 * Add new camera
 *
 * @param $_POST
 * @return String
 */
function flood_camera_add($self) {
	$post=(object)post('camera');
	if ($_POST) {
		if (empty($post->name)) $error[]='กรุณาป้อน "Camera name" ';
		if (empty($post->title)) $error[]='กรุณาป้อน "Camera title" ';
		if ($error) {
		} else {
			$post->uid=SG\getFirst(i()->uid,'func.NULL');
			$post->created=date('U');
			$stmt='INSERT INTO %flood_cam%
								(`uid`, `name`, `title`, `location`, `camip`, `imgurl`, `port`, `uname`, `passwd`, `created`)
							VALUES
								(:uid, :name, :title, :location, :camip, :imgurl, :port, :uname, :passwd, :created)';
			mydb::query($stmt,$post);
			location('flood/cam/'.mydb()->insert_id);
		}
	}

	if ($error) $ret.=message('error',$error);

	R::View('flood.toolbar',$self,'เพิ่มกล้อง');

	$form=new Form('camera',url('flood/camera/add'),'camera-add');

	$form->addField(
						'name',
						array(
							'type'=>'text',
							'label'=>'Camera Name',
							'class'=>'-fill',
							'require'=>true,
							'value'=>SG\getFirst($post->name,uniqid()),
						)
					);

	$form->addField(
						'title',
						array(
							'type'=>'text',
							'label'=>'Camera Title',
							'class'=>'-fill',
							'require'=>true,
							'value'=>htmlspecialchars($post->title),
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
							'label'=>'Camera Image URL',
							'class'=>'-fill',
							'value'=>htmlspecialchars($post->imgurl),
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
							'require'=>true,
							'value'=>$post->location,
						)
					);

	$form->addField(
			'save',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
				)
			);

	$ret.=$form->build();
	return $ret;
}
?>