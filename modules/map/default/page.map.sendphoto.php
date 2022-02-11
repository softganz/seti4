<?php
/**
 * Send photo
 *
 * @param Integer $mapid
 * @return String
 */
function map_sendphoto($self,$mapid) {
	$is_new_gallery=false;
	$ret='';

	$rs=mydb::select('SELECT `mapid`,`gallery` FROM %map_networks% WHERE `mapid`=:mapid LIMIT 1',':mapid',$mapid);
	$gallery=$rs->gallery;
	if (empty($gallery)) {
		$gallery=mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1;
		$is_new_gallery=true;
	}

	$photo=$_FILES['photo'];
	if (!is_uploaded_file($photo['tmp_name'])) die("Upload error : No upload file");

	$ext=strtolower(sg_file_extension($photo['name']));
	if (in_array($ext,array('jpg','jpeg'))) {
		// Upload photo
		$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
		if (!$upload->valid_format()) die("Upload error : Invalid photo format");
		if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
			sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
		}
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
		$pics_desc['type'] = 'photo';
		$pics_desc['title']=$rs->activityname;
	} else {
		// Upload file
		$pics_desc['type'] = 'doc';
		$pics_desc['title']=$photo['name'];
		$upload=new classFile($photo,cfg('paper.upload.document.folder'),cfg('topic.doc.file_ext'));
		if (!$upload->valid_extension()) die("Upload error : Invalid file format");
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
	}

	$pics_desc['tpid'] = 0;
	$pics_desc['cid'] = 0;
	$pics_desc['gallery'] = $gallery;
	$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
	$pics_desc['file']=$photo_upload;
	$pics_desc['timestamp']='func.NOW()';
	$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

	if ($upload->copy()) {
		$stmt='INSERT INTO %topic_files% (`type`, `tpid`, `cid`, `gallery`, `uid`, `file`,`title`, `timestamp`, `ip`) VALUES (:type, :tpid, :cid, :gallery, :uid, :file, :title, :timestamp, :ip)';
		mydb::query($stmt,$pics_desc);
		if ($is_new_gallery) mydb::query('UPDATE %map_networks% SET gallery=:gallery WHERE `mapid`=:mapid LIMIT 1',':mapid',$mapid,':gallery',$gallery);
		if ($pics_desc['type']=='photo') {
			$photo=model::get_photo_property($upload->filename);
			$ret.='<img src="'.$photo->_url.'" height="60" alt="" />';
		} else {
			$ret.='อัพโหลดไฟล์เรียบร้อย';
		}
	} else {
		$ret.='Upload error : Cannot save upload file';
	}
	return $ret;
}
?>