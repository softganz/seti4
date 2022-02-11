<?php
/**
 * Class  :: ad_model
 *
*/
class ad_model {

	function get_ad_locations() {
		$stmt = 'SELECT lid,description,width,height FROM %ad_location% ORDER BY lid';
		$result=mydb::select($stmt);
		return $result;
	}

	function get_ad_by_id($aid) {
		$stmt = 'SELECT ad.* , o.name as owner ';
		$stmt .= ' FROM %ad% AS ad';
		$stmt .= '  LEFT JOIN %users% AS o ON ad.oid=o.uid ';
		$stmt .= '  WHERE ad.aid='.$aid.' LIMIT 1';
		$rs=mydb::select($stmt);
		return $rs;
	}

	function __edit_default($ad,$para) {
		$result->process[]='ad_manage::__edit_default() request';

		mydb::query('UPDATE %ad% SET `default`="'.($ad->default=='yes'?'no':'yes').'" WHERE aid='.$ad->aid.' LIMIT 1');
		$result->process[]=mydb()->_query;
		$result->body='This ad was '.($ad->default=='yes'?'normal':'default');

		location('ad/'.$ad->aid);
		return $result;
	}

	function __edit_activate($ad,$para) {
		$result->process[]='ad_manage::__edit_activate() request';
		mydb::query('UPDATE %ad% SET `active`="'.($ad->active=='yes'?'no':'yes').'" WHERE aid='.$ad->aid.' LIMIT 1');
		$result->process[]=mydb()->_query;
		$result->body='This ad was '.($ad->active=='yes'?'deactivate':'activate');
		location('ad/'.$ad->aid);
		return $result;
	}

	function __edit_detail($ad,$para) {
		load_lib('class.editor.php','lib');
		$result->process[]='ad_manage::__edit_detail() request';


		if (post('ad')) {
			$data=(object)post('ad',_TRIM+_STRIPTAG);
			$stmt = mydb::create_update_cmd('%ad%',$data,'aid='.$ad->aid.' LIMIT 1');
			mydb::query($stmt);
			$result->process[]=mydb()->_query;

			location('ad/'.$ad->aid);
			return $result;
		}

		$form = new Form([
			'variable' => 'ad',
			'action' => url(q()),
			'children' => [
				'location' => [
					'type' => 'select',
					'label' => 'Ad location',
					'class' => '-fill',
					'require' => true,
					'options' => (function() {
						$ad_locations = ad_model::get_ad_locations();
						$result = [];
						foreach ($ad_locations->items as $location) {
							$result[$location->lid] = $location->description.' ('.$location->width.'x'.$location->height.' pixels)';
						}
						return $result;
					})(),
					'value' => $ad->location,
				],
				'title' => [
					'type' => 'text',
					'label' => 'Title',
					'class' => '-fill',
					'maxlength' => 100,
					'require' => true,
					'value' => htmlspecialchars($ad->title),
				],
				'url' => [
					'type' => 'text',
					'label' => 'Link url',
					'class' => '-fill',
					'maxlength' => 250,
					'value' => htmlspecialchars($ad->url),
				],
				'weight' => [
					'type' => 'select',
					'label' => 'Weight',
					'value' => htmlspecialchars($ad->weight),
					'options' => '1..10',
				],
				'body' => [
					'type' => 'textarea',
					'label' => 'Description',
					'class' => '-fill',
					'rows' => 10,
					'value' => $ad->body,
					'pretext' => editor::softganz_editor('edit-ad-body'),
				],
				'start' => [
					'type' => 'text',
					'label' => 'Publish from date',
					'maxlength' => 20,
					'size' => 20,
					'require' => true,
					'value' => htmlspecialchars($ad->start),
				],
				'stop' => [
					'type' => 'text',
					'label' => 'Publish until date',
					'maxlength' => 20,
					'size' => 20,
					'require' => true,
					'value' => htmlspecialchars($ad->stop),
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'container' => '{class: "-sg-text-right"}',
				],
			],
		]);

		$result->body .= $form->build();
		return $result;
	}

	function __edit_photo($ad,$para) {
		$result->process[]='ad_manage::__edit_photo() request';

		if ($_POST['cancel']) location($_SERVER['HTTP_REFERER']);

		// update image or photo file
		if (is_uploaded_file($_FILES['ad_file']['tmp_name'])) {
			$ad_file=(object)$_FILES['ad_file'];
			if (!in_array($ad_file->type, explode(',', _AD_FORMAT_FILE))) $error[]='Invalid file format';
			if (!user_access('administer ads') && $ad_file->size >cfg('photo.max_file_size')*1024) $error[]='Invalid file size';

			$ad_file->name=sg_valid_filename($ad_file->name);
			$upload_file=ad_model::__get_img_location($ad_file->name);
			if ($ad->file) {
				$old_file=ad_model::__get_img_location($ad->file);
				if (file_exists($old_file) && is_file($old_file)) unlink($old_file);
			}
			if (file_exists($upload_file) && is_file($upload_file)) $error[]='Duplicate upload filename';
			if (!$error && copy($ad_file->tmp_name,$upload_file)) {
				$ad->file=$ad_file->name;
				if (cfg('upload.file.chmod')) chmod($upload_file,cfg('upload.file.chmod'));
			} else {
				$error[]='Saving upload file error';
			}
			if ($error) {
				$result->error=$error;
			} else {
				$stmt = 'UPDATE %ad% SET file="'.$ad_file->name.'" WHERE aid="'.$ad->aid.'" LIMIT 1';
				mydb::query($stmt);
				$result->process[]=mydb()->_query;
			}
			location('ad/'.$ad->aid);
			return $result;
		}

		$form = new Form([
			'variable' => 'ad',
			'action' => url(q()),
			'enctype' => 'multipart/form-data',
			'children' => [
				'ad_file' => [
					'name' => 'ad_file',
					'type' => 'file',
					'label' => 'Photo or Flash file',
					'require' => true,
					'description' => '<strong>ข้อกำหนดในการส่งไฟล์</strong><ul><li>ไฟล์ประเภท <strong>jpg , gif , png ,swf</strong> ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong> </li><li>ชื่อไฟล์ควรเป็นภาษาอังกฤษเท่านั้น</li></ul>',
				],
				'save' => [
					'type' => 'button',
					'value' => 'Save',
				],
			], // children
		]);

		$result->body .= $form->build();
		return $result;
	}

	function __edit_delete($ad,$para) {
		$result->process[]='ad_manage::__edit_delete() request';

		if (!SG\confirm()) {
			$result->body.=message('error','Invalid parameter');
		} else if ($ad->aid && SG\confirm()) {
			mydb::query('DELETE FROM %ad% WHERE aid=:aid LIMIT 1',':aid',$ad->aid);
			$result->process[]=mydb()->_query;
			db_clear_autoid('%ad%');
			$result->process[]=mydb()->_query;

			$img_file=ad_model::__get_img_location($ad->file);
			if (file_exists($img_file) && is_file($img_file)) {
				unlink($img_file);
				$result->process[]='Delete file <em>'.$img_file.'</em>';
			}
			$result->body.=message('status','Delete advertisment complete');
			location('ad/list',array('loc'=>$ad->location));
		}
		return $result;
	}

	function __edit_deletephoto($ad,$para) {
		$result->process[]='ad_manage::__edit_delete() request';

		if (!SG\confirm()) {
			$result->body=message('error','Invalid parameter');
		} else {
			$img_file=ad_model::__get_img_location($ad->file);
			if (file_exists($img_file) && is_file($img_file)) {
				mydb::query('UPDATE %ad% SET `file`="" WHERE `aid`=:aid LIMIT 1',':aid',$ad->aid);
				$result->process[]=mydb()->_query;
				unlink($img_file);
				$result->process[]='Delete file <em>'.$img_file.'</em>';
			}
			$result->body.=message('status','Delete advertisment complete');
			location('ad');
		}
		return $result;
	}


	function __get_img_url($img) { return cfg('upload.url').cfg('ad.img_folder').'/'.$img; }

	function __get_img_location($img) { return cfg('upload.folder').cfg('ad.img_folder').'/'.$img; }

	function __check_upload_folder() {
		$upload_folder=cfg('upload.folder').cfg('ad.img_folder').'/';
		if (!file_exists($upload_folder)) {
			mkdir($upload_folder);
			chmod($upload_folder,0777);
		}
	}

	function __show_img_str($rs) {
		$img = ad_model::__get_img_url($rs->file);
		$ext=strtolower(substr($img,strrpos($img,'.')+1));
		if ($ext==='swf') {
			$ret ='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$rs->width.'" height="'.$rs->height.'">
<param name="movie" value="'.$img.'">
<param name="quality" value="high">
<embed src="'.$img.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$rs->width.'" height="'.$rs->height.'"></embed></object>';
		} else if ($ext==='jpg' || $ext==='gif' || $ext='png') {
			$ret = '<img src="'.$img.'" alt="" />';
		}
		return $ret;
	}
} // end of class ad_model
?>