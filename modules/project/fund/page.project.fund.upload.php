<?php
/**
* Project :: Fund Upload
* Created 2018-12-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/upload
*/

$debug = true;

function project_fund_upload($self,$fundInfo,$data=NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$ret='';
	$tagName=post('tagname');
	$refid=post('refid');
	$photoTitleLists=array(
		'letterofappointment'=>'หนังสือแต่งตั้งกรรมการ',
		'projectfundrcv'=>'ใบเสร็จรับเงิน',
		'population'=>'เอกสารประชากรประจำปี',
	);

	$photoFileNameLists=array(
		'letterofappointment'=>'loapp',
		'projectfundrcv'=>'fundrcv',
		'population'=>'population',
	);

	$photoTitle=$photoTitleLists[$tagName];
	//$ret.=print_o($_FILES,'$_FILES');

	$uploadFolder=cfg('paper.upload.photo.folder');
	$photoPrename='project_'.$photoFileNameLists[$tagName].'_'.$orgId.'_';
	$photoFilenameLength=30;

	//$ret='Upload photo of orgid '.$orgId.' tagName='.$tagName.' photoPrename '.$photoPrename.'<br />';


	// Multiphoto file upload
	if (is_array($_FILES['photo']['name'])) {
		foreach ($_FILES['photo']['name'] as $key => $value) {
			$uploadPhotoFiles[$key]['name']=$_FILES['photo']['name'][$key];
			$uploadPhotoFiles[$key]['type']=$_FILES['photo']['type'][$key];
			$uploadPhotoFiles[$key]['tmp_name']=$_FILES['photo']['tmp_name'][$key];
			$uploadPhotoFiles[$key]['error']=$_FILES['photo']['error'][$key];
			$uploadPhotoFiles[$key]['size']=$_FILES['photo']['size'][$key];
		}
	} else {
		$uploadPhotoFiles[]=$_FILES['photo'];
	}
	//$ret.=print_o(post(),'post').print_o($uploadPhotoFiles,'$uploadPhotoFiles');

	foreach ($uploadPhotoFiles as $postFile) {
		//$ret.=print_o($postFile,'$postFile');
		if (!is_uploaded_file($postFile['tmp_name'])) {
			$ret.='Upload error : No upload file ('.$postFile['name'].')<br />';
			continue;
		}

		$ext=strtolower(sg_file_extension($postFile['name']));
		//$ret.='ext='.$ext;
		if (!in_array($ext,array('jpg','jpeg','png','pdf'))) {
			$ret.='Upload error : Invalid File Type ('.$postFile['name'].')<br />';
			continue;
		}

		// Upload photo
		if ($ext=='pdf') {
			$uploadFolder=cfg('paper.upload.document.folder');
			$upload=new classFile($postFile,$uploadFolder);
			//$ret.=print_o($upload,'$upload');
		} else {
			$upload=new classFile($postFile,$uploadFolder,cfg('photo.file_type'));
			if (!$upload->valid_format()) {
				$ret.='Upload error : Invalid photo format ('.$postFile['name'].')';
				continue;
			}
			if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
				sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
			}
		}

		$upload->generate_nextfile($photoPrename,$photoFilenameLength);
		$photo_upload=$upload->filename;
		$pics_desc['type']=$ext=='pdf'?'doc':'photo';
		$pics_desc['title']=$photoTitle;
		$pics_desc['tagname']=$tagName;

		$pics_desc['orgid'] = $orgId;
		$pics_desc['uid']=i()->uid;
		$pics_desc['file']=$photo_upload;
		$pics_desc['refid']=empty($refid)?NULL:$refid;
		$pics_desc['timestamp']='func.NOW()';
		$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

		if ($upload->copy()) {
			//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';
			$stmt='INSERT INTO %topic_files% (`type`, `orgid`, `uid`, `refid`, `tagname`, `file`,`title`, `timestamp`, `ip`) VALUES (:type, :orgid, :uid, :refid, :tagname, :file, :title, :timestamp, :ip)';
			mydb::query($stmt,$pics_desc);
			//$ret.=mydb()->_query;

			$fid=mydb()->insert_id;

			if ($pics_desc['type']=='photo') {
				$photo=model::get_photo_property($upload->filename);
				$ret.='<a class="sg-action" data-group="photo" href="'.$photo->_url.'" data-rel="img" title="">';
				$ret.='<img class="photoitem" src="'.$photo->_url.'" alt="" />';
				$ret.='</a>';
				$ui=new ui('span','iconset -hover');
				$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
				$ret.=$ui->build().'</li>'._NL.'<li class="-hover-parent">';
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$upload->filename;
				$ret.='<li><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a></li>';
			}
		} else {
			$ret.='Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
		}
	}

	$ret=rtrim($ret,'</li><li class="-hover-parent">');
	//$ret.=print_o(post(),'post').print_o($_FILES,'$_FILES').print_o($rs,'$rs');
	//die($ret);
	return $ret;
}
?>