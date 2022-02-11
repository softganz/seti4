<?php
function api_photoslide($self,$tpid=NULL,$format=NULL) {
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past

	$upload_folder=cfg('paper.upload.photo.url');
	$pics=mydb::select('SELECT * FROM %topic_files% WHERE `tpid`=:tpid AND `cid`=0 AND `type`="photo" ORDER BY `fid` ASC',':tpid',$tpid);

	//$ret .= print_o($pics,'$pics');
	//debugMsg($pics,'$pics');

	switch ($format) {
		case 'flash-here-slide' :
			$ret='files=';
			foreach ($pics->items as $key=>$pic) {
				$ret.= sg_urlencode($upload_folder.$pic->file).'|';
			}
			break;
		case 'imagerotator' :
			$ret='<playlist xmlns="http://xspf.org/ns/0/" version="1">'._NL;
			$ret.='	<trackList>'._NL;
			foreach ($pics->items as $key=>$pic) {
				$ret .= '		<track>'._NL;
				$ret .= '		<title>'.$pic->pic_photo_file.'</title>'._NL;
				$ret .= '		<creator>SoftGanz</creator>'._NL;
				$ret .= '		<info>'.cfg('domain').'</info>'._NL;
				$ret .= '		<location>'.$upload_folder.$pic->file.'</location>'._NL;
				$ret .= '		</track>'._NL._NL;
			}
			$ret.='	</trackList>'._NL;
			$ret.='</playlist>'._NL;
			break;
	}
	die($ret);
}
?>