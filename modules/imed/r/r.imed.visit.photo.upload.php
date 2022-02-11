<?php
function r_imed_visit_photo_upload($photoFiles, $data = NULL, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result=array();

	//$uploadFolder=cfg('paper.upload.photo.folder');
	$uploadFolder=cfg('paper.upload.folder').'imed/photo/';
	//$uploadFolder=cfg('paper.upload.url').'imed/';
	$photoPrename=SG\getFirst($data->prename,'paper_'.$data->tpid.'_');
	$photoFilenameLength = SG\getFirst($options->fileNameLength,30);

	$deleteurl = $data->deleteurl;
	//$ret='Upload photo of orgid '.$orgid.' tagName='.$tagName.' photoPrename '.$photoPrename.'<br />';


	// Multiphoto file upload
	if (is_array($photoFiles['name'])) {
		foreach ($photoFiles['name'] as $key => $value) {
			$uploadPhotoFiles[$key]['name']=$photoFiles['name'][$key];
			$uploadPhotoFiles[$key]['type']=$photoFiles['type'][$key];
			$uploadPhotoFiles[$key]['tmp_name']=$photoFiles['tmp_name'][$key];
			$uploadPhotoFiles[$key]['error']=$photoFiles['error'][$key];
			$uploadPhotoFiles[$key]['size']=$photoFiles['size'][$key];
		}
	} else {
		$uploadPhotoFiles[]=$photoFiles;
	}

	//$ret.=print_o($data,'$data');
	$ret.=print_o($uploadPhotoFiles,'$uploadPhotoFiles');
	//echo $ret;
	//return;

	foreach ($uploadPhotoFiles as $postFile) {
		//echo print_o($postFile,'$postFile');
		if (!is_uploaded_file($postFile['tmp_name'])) {
			$result['error'].='Upload error : No upload file ('.$postFile['name'].')<br />';
			continue;
		}

		$ext=strtolower(sg_file_extension($postFile['name']));
		//$ret.='ext='.$ext;
		if (!in_array($ext,array('jpg','jpeg','png','pdf'))) {
			$result['error'].='Upload error : Invalid File Type ('.$postFile['name'].')<br />';
			continue;
		}

		// Upload photo
		if ($ext=='pdf') {
			$docUploadFolder=cfg('paper.upload.document.folder');
			$upload=new classFile($postFile,$docUploadFolder);
		} else {
			$upload=new classFile($postFile,$uploadFolder,cfg('photo.file_type'));
			if (!$upload->valid_format()) {
				$result['error'].='Upload error : Invalid photo format ('.$postFile['name'].')';
				continue;
			}
			if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
				sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
			}
		}

		$upload->generate_nextfile($photoPrename,$photoFilenameLength);

		$result['upload']=print_o($upload,'$upload');

		$photo_upload=$upload->filename;

		$post->uid=SG\getFirst(i()->uid,'func.NULL');
		$post->created=date('U');
		$post->seq=$data->seq;
		$post->tagname=$data->tagname?$data->tagname:NULL;
		$post->file=$photo_upload;
		$post->type=$ext=='pdf'?'doc':'photo';
		$post->ip=ip2long(GetEnv('REMOTE_ADDR'));

		if ($upload->copy()) {
			//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';
			$stmt='INSERT INTO %imed_files%
							(`seq`, `uid`, `type`, `tagname`, `file`, `created`, `ip`)
							VALUES
							(:seq, :uid, :type, :tagname, :file, :created, :ip)';
			mydb::query($stmt,$post);
			$result['query'][]=mydb()->_query;

			$fid=mydb()->insert_id;
			$result['post'][]=$post;


		
			if ($post->type=='photo') {
				$photo=model::get_photo_property($upload->filename,$uploadFolder);
				$result['photo'][]=$photo;

				$photoInfo = imed_model::upload_photo($upload->filename);
				$photoUrl = $photoInfo->_url;
				//$ret='<img src="'.imed_model::upload_photo($result->save->_file).'" height="100" />';

				$result['link'].='<a class="sg-action" data-group="photo" href="'.$photoUrl.'" data-rel="img">';
				$result['link'].='<img class="photoitem" src="'.$photoUrl.'" alt="" />';
				$result['link'].='</a>';
				$ui=new Ui('span','');
				if ($deleteurl) {
					$ui->add('<a class="sg-action" href="'.$deleteurl.$fid.'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i></a>');
				}
				$result['link'].='<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$upload->filename;
				$result['link'].='<li><a href="'.$uploadUrl.'"><img src="http://img.softganz.com/icon/pdf-icon.png" /></a></li>';
			}
		
		} else {
			$result['error'].='Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
		}
	}

	$result['link']=rtrim($result['link'],'</li><li>');

	$result['debug'].=print_o($photoFiles,'$photoFiles');
	return $result;
}
?>