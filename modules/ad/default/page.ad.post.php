<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad_post($self) {
	if (!user_access('create ad content')) return message('error','access denied');

	$para=para(func_get_args());

	$location = post('id');

	if ($_POST['cancel']) location('ad');

	$self->theme->title='Create advertisment';
	user_menu('home',tr('home'),url());
	user_menu('ad',tr('ad'),url('ad'));
	model::member_menu();
	if (user_access('create ad content')) user_menu('new',tr('Create new advertisment'),url('ad/post'));
	$self->theme->navigator=user_menu();

	$adInfo=(object)post('ad',_TRIM+_STRIPTAG);

	if ($adInfo->title) {

		if (empty($adInfo->location)) $field_requires[]='Ad location';
		if (empty($adInfo->title)) $field_requires[]='Title';
		if (empty($adInfo->start)) $field_requires[]='Publish from date';
		if (empty($adInfo->stop)) $field_requires[]='Publish until date';
		if ($field_requires) $error[]='Input field missing:<ul><li>'.implode('</li><li>',$field_requires).'</li></ul>';

		// start save new item
		$is_simulate=debug('simulate');
		if (!$error) {
			// save photo upload file
			if (is_uploaded_file($_FILES['ad_file']['tmp_name'])) {
				$ad_file=(object)$_FILES['ad_file'];
				if (!in_array($ad_file->type, explode(',',_AD_FORMAT_FILE))) $error[]='Invalid file format';
				if (!user_access('administer contents') && $ad_file->size >cfg('photo.max_file_size')*1024) $error[]='Invalid file size';
				$ad_file->name=sg_valid_filename($ad_file->name);
				$upload_file = ad_model::__get_img_location($ad_file->name);
				ad_model::__check_upload_folder();
				if (file_exists($upload_file) && is_file($upload_file)) $error[]='Duplicate upload filename';
				if (!$error && copy($ad_file->tmp_name,$upload_file)) {
					$adInfo->file=$ad_file->name;
					if (cfg('upload.file.chmod')) chmod($upload_file,cfg('upload.file.chmod'));
				} else {
					$error[]='Saving upload file error';
				}
			}
		}
		if (!$error) {
			if (i()->ok) $adInfo->uid=$adInfo->oid=i()->uid;
			$adInfo->active='yes';
			$adInfo->created='func.NOW()';

			// get ad size
			$location_size=mydb::select('SELECT * FROM %ad_location% WHERE lid="'.$adInfo->location.'" LIMIT 1');
			$adInfo->width=$location_size->width;
			$adInfo->height=$location_size->height;

			// get ad_default
			//				$adInfo->default=db_count('%ad%','location="'.$adInfo->location.'" and default="yes"') ?'no':'yes';
			$adInfo->default='no';

			$stmt = mydb::create_insert_cmd('%ad%',$adInfo);
			mydb::query($stmt, $adInfo);

			if ($is_simulate) $ret .= '<b>ad sql :</b> '.mydb()->_query.'<br /><br />';

			$adInfo->aid=mydb()->insert_id;

			if (!$is_simulate) location('ad/'.$adInfo->aid);
		}
	}

	if (empty($adInfo->start)) $adInfo->start=date('Y-m-d 00:00');
	if (empty($adInfo->stop)) $adInfo->stop=date('Y-m-d 23:59');

	if ($error) $ret.=message('error',$error);


	$form = new Form([
		'variable' => 'ad',
		'action' => url(q()),
		'id' => 'edit-podcast',
		'enctype' => 'multipart/form-data',
		'children' => [
			'location' => [
				'type' => 'select',
				'label' => tr('Ad location'),
				'require' => true,
				'class' => '-fill',
				'options' => (function() {
					$ad_locations=ad_model::get_ad_locations();
					$options = [];
					foreach ($ad_locations->items as $locationRs)
						$options[$locationRs->lid] = $locationRs->description.' ('.$locationRs->width.'x'.$locationRs->height.' pixels)';
					return $options;
				})(),
				'value' => SG\getFirst($adInfo->location, $location),
			],
			'title' => [
				'type' => 'text',
				'label' => tr('Title'),
				'class' => '-fill',
				'maxlength' => 100,
				'require' => true,
				'value' => htmlspecialchars($adInfo->title),
			],
			'ad_file' => [
				'name' => 'ad_file',
				'type' => 'file',
				'label' => '<i class="icon -view"></i>เลือก'.tr('Photo or Flash file'),
				'class' => 'btn',
				//'container' => array('class' => 'btn -upload'),
				'description' => '<strong>ข้อกำหนดในการส่งไฟล์</strong><ul><li>ไฟล์ประเภท <strong>jpg , gif , png ,swf</strong> ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong> </li><li>ชื่อไฟล์ควรเป็นภาษาอังกฤษเท่านั้น</li></ul>',
			],
			'url' => [
				'type' => 'text',
				'label' => tr('Link url'),
				'class' => '-fill',
				'maxlength' => 250,
				'value' => htmlspecialchars($adInfo->url),
			],
			'weight' => [
				'type' => 'select',
				'label' => tr('Weight'),
				'value' => htmlspecialchars($adInfo->weight),
				'options' => '0,1,2,3,4,5,6,7,8,9,10',
			],
			'body' => [
				'type' => 'textarea',
				'label' => tr('Description'),
				'class' => '-fill',
				'rows' => 5,
				'value' => $adInfo->body,
			],
			'start' => [
				'type' => 'text',
				'label' => tr('Publish from date'),
				'maxlength' => 20,
				'size' => 20,
				'require' => true,
				'value' => htmlspecialchars($adInfo->start),
			],
			'stop' => [
				'type' => 'text',
				'label' => tr('Publish until date'),
				'maxlength' => 20,
				'size' => 20,
				'require' => true,
				'value' => htmlspecialchars($adInfo->stop),
			],
			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>{tr:Save}</span>',
				'pretext' => '<a class="btn -link" href=""><i class="icon -cancel -gray"></i><span>{tr:Cancel}</a> ',
				'container' => array('class' => '-sg-text-right'),
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}

?>