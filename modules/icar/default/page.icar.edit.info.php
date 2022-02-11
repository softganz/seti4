<?php
/**
* Save car information
*
* @param Array $_REQUEST
* @return Mixed
*/

$debug = true;


function icar_edit_info($self) {
	$post = post();
	$fld = trim($post['fld']);
	$tr = trim($post['tr']);
	list($group,$part) = explode(':',$post['group']);
	$value = $post['value'];
	list($returnType,$formatReturn) = explode(':',$post['ret']);


	$ret['tr']=$tr;
	$ret['value']=$retvalue=trim($value);
	$ret['msg']='บันทึกเรียบร้อย';
	$ret['error']='';
	$ret['debug'].='[group='.$group.' , part='.$part.', fld='.$fld.',tr='.$tr.',ret='.$returnType.']<br />';
	$ret['debug'].=print_o($post,'$post');

	$carInfo=icar_model::get_by_id($tr);
	switch ($post['action']) {
		case 'get' :
			if ($fld=='brand') $value=icar_model::category('icar:brand',$carInfo->shopid);
			else if ($fld=='partner') $value=array('-1'=>'ไม่มีผู้ร่วมทุน')+icar_model::category('partner',$carInfo->shopid);
			else if ($fld=='model') {
				$value=array();
				foreach (mydb::select('SELECT DISTINCT `model` FROM %icar% ORDER BY `model` ASC')->items as $irs) $value[]=$irs->model;
				//print_o($value,'$value',1);
			} else $value=$carInfo->{$fld};
			return $value;
			break;
		
		case 'cover' :
			$fid=$_REQUEST['f'];
			$tpid=mydb::select('SELECT tpid FROM %topic_files% WHERE fid=:fid LIMIT 1',':fid',$fid)->tpid;
			mydb::query('UPDATE %topic_files% SET `cover`=NULL WHERE tpid=:tpid',':tpid',$tpid);
			mydb::query('UPDATE %topic_files% SET `cover`="Yes" WHERE fid=:fid LIMIT 1',':fid',$fid);
			$ret['msg']='ดำเนินการเรียบร้อย';
			break;

		case 'addphoto' :
			$tpid=$carInfo->tpid;
			$ret='';

			$photo=$_FILES['photo'];
			if (!is_uploaded_file($photo['tmp_name'])) die("Upload error");
			$upload=new classFile($photo,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
			if (!$upload->valid_format()) die("Upload error");
			if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
				sg_photo_resize($upload->upload->tmp_name,cfg('photo.resize.width'),NULL,NULL,true,cfg('photo.resize.quality'));
			}
			if ($upload->duplicate()) $upload->generate_nextfile();
			$photo_upload=$upload->filename;
			$pics_desc['type'] = 'photo';
			$pics_desc['tpid'] = $tpid;
			$pics_desc['cid'] = 0;
			$pics_desc['title']=$carInfo->title;
			$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
			$pics_desc['file']=$photo_upload;
			$pics_desc['timestamp']='func.NOW()';
			$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

			if ($upload->copy()) {
				$stmt = 'INSERT INTO %topic_files% (`type`, `tpid`, `cid`, `uid`, `file`, `timestamp`, `ip`) VALUES (:type, :tpid, :cid, :uid, :file, :timestamp, :ip)';
				mydb::query($stmt,$pics_desc);
				$fid = mydb()->insert_id;
				if (mydb::select('SELECT COUNT(*) total FROM %topic_files% WHERE tpid=:tpid AND `type`="photo" AND cid=0 AND `cover`="Yes" LIMIT 1',':tpid',$tpid)->total==0) {
					mydb::query('UPDATE %topic_files% SET `cover`="Yes" WHERE `fid`=:fid LIMIT 1',':fid',$fid);
				}
				$photo=model::get_photo_property($upload->filename);
				$ret .= '<a class="sg-action" href="'.$photo->_url.'" data-rel="img">';
				$ret .= '<img src="'.$photo->_url.'" alt="" />';
				$ret .= '</a>';
				$ui = new Ui('span');
				$ui->add('<a class="sg-action" href="'.url('icar/edit/info',array('action'=>'cover','f'=>$fid)).'" title="As Cover" data-rel="none"><i class="icon -save"></i></a>');
				$ui->add('<a class="sg-action" href="'.url('icar/edit/info',array('action'=>'delphoto','f'=>$fid)).'" data-title="DELETE PHOTO" data-confirm="Are you sure to DELETE PHOTO ?" data-rel="none" data-removeparent="li"><i class="icon -cancel"></i></a>');
				$ret .= '<nav class="nav -icons -hover -top-right">'.$ui->build().'</nav>';
			} else {
				$ret.='Upload error';
			}
			die($ret);
			break;

		case 'delphoto' :
			// delete photo
			$fid=$_REQUEST['f'];
			$rs=mydb::select('SELECT f.* FROM %topic_files% f  WHERE f.fid='.$fid.' AND f.`type`="photo" LIMIT 1',':fid',$fid);
			$tpid=$rs->tpid;
			if ($rs->file) {
				mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="photo" LIMIT 1',':fid',$fid);
				$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;
				if (file_exists($filename) and is_file($filename)) {
					$is_photo_inused=db_count('%topic_files%',' file="'.$rs->file.'" AND fid!='.$rs->fid);
					if (!$is_photo_inused) unlink($filename);
					$ret['msg']=$is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
				}
				if (mydb::select('SELECT COUNT(*) total FROM %topic_files% WHERE tpid=:tpid AND `type`="photo" AND cid=0 AND `cover`="Yes" LIMIT 1',':tpid',$tpid)->total==0) {
					$coverid=mydb::select('SELECT MIN(fid) fid FROM %topic_files% WHERE `tpid`=:tpid AND `type`="photo" AND `cid`=0 LIMIT 1',':tpid',$tpid)->fid;
					mydb::query('UPDATE %topic_files% SET `cover`="Yes" WHERE `fid`=:fid LIMIT 1',':fid',$coverid);
				}
			}
			break;
		
		case 'save' :
			if (empty($tr) || empty($group) || empty($fld)) $ret['error']='Invalid parameter';
			if ($ret['error']) {
				$ret['msg']=$ret['error'];
				return $ret;
			}
			
			if (is_string($value)) $value=trim(strip_tags($value));
			if (in_array($fld,array('buydate','itemdate','licenseexppire','insuexpire'))) {
				// Convert date from dd/mm/yyyy to yyyy-mm-dd
				list($dd,$mm,$yy)=explode('/',$value);
				if ($yy>2400) $yy=$yy-543;
				$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
			}  else if ($returnType=='numeric') $value=preg_replace('/[^0-9\.\-]/','',$value);

			// Update project transaction
			switch ($group) {
				case 'property' :
					if ($part && $fld && $tpid) property($part.':'.$fld.':'.$tpid,$value);
					$ret['debug'].='<p>Update property</p>';
					break;

				case 'car' :
					mydb::query('UPDATE %icar% SET `'.$fld.'`=:value WHERE `tpid`=:tpid LIMIT 1',':tpid',$tr,':value',$value);

					$log = array(
						'key' => 'Car Info Edit',
						'msg' => 'Car Info Edit',
						'fldname' => $fld,
					);

					if (in_array($fld,array('brand','model'))) icar_model::update_car_title($tr);

					$ret['debug'].='Update car information'.mydb()->_query;
					break;
			}
			
			// Save value into table
			if ($stmt) {
				mydb::query($stmt,':trid',$tr,':value',$value,$values);
				if (empty($tr)) $tr=$ret['tr']=mydb()->insert_id;
				$ret['debug'].='Query : '.mydb()->_query.'<br />';

				$log = array(
					'key'=>'Form '.$group,
					'msg'=>'Form update',
					'sql'=>mydb()->_query.'<br />'.(mydb()->_error?'Error : '.mydb()->_error.'<br />':''),
				);

			}


			// Set return value
			if ($returnType=='html') $ret['value']=sg_text2html($value);
			else if ($returnType=='br') $ret['value']=nl2br($value);
			else if ($returnType=='numeric') $ret['value']=number_format($value,$formatReturn);
			else if ($fld=='costcode') $ret['value']=icar_model::category($value);
			else if ($fld=='brand') $ret['value']=icar_model::category($value);
			else if ($fld=='partner') $ret['value']=$value==-1?'ไม่มีผู้ร่วมทุน':icar_model::category('partner',$carInfo->shopid,$value);
			//					$ret['debug'].=print_o($carInfo,'$carInfo');
			break;
	}

	if ($log) {
		//$module=NULL,$keyword=NULL,$message=NULL,$uid=NULL,$keyid=NULL,$fldname=NULL
		model::watch_log(
			'icar',
			$log['key'],
			$log['msg']
				.($fld ? ' : Set '.$fld.' = '.$value.'<br />' : '')
				.($log['sql'] ? $log['sql'].'<br />':'')
				.($values ? print_o($values,'$values').'<br />' : ''),
			NULL,
			$carInfo->tpid,
			$log['fldname']
		);
	}

	if (!_AJAX) $ret['location']=array('icar');
	return $ret;
}
?>