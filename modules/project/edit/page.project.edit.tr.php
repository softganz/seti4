<?php
/**
 * Edit follow form
 *
 * @param Integer $tpid
 * @return JSON and die
 */

import('model:org.php');

function project_edit_tr($self) {
	$post = post();
	$tpid = intval(trim(SG\getFirst($post['id'], $post['tpid'])));
	if ($tpid <= 0) $tpid = NULL;
	$period = $post['period'];
	$fld = trim($post['fld']);
	$calid = trim($post['calid']);
	$refid = trim($post['refid']);
	$tr = intval(trim($post['tr']));
	$parent = post('parent');
	$sorder = SG\getFirst(post('sorder'), 0);
	$value = $post['value'];
	$dataType = $post['type'];
	$action = post('action');
	$inputBlankAsValue = post('blank');
	list($group, $part) = explode(':', $post['group']);
	list($returnType,$formatReturn) = explode(':',$post['ret']);

	$isPreserveTab = $post['preservtab'];

	//debugMsg(print_o(post(),'post()'));

	if ($value=='...') return array('value'=>$value);

	if ($value && (in_array($returnType, array('money', 'numeric'))
		|| in_array($fld, array('num1', 'num2', 'num3', 'num4', 'num5', 'num6', 'num7', 'num8', 'budget')))) {
		$value = sg_strip_money($value);
	}

	if (!$isPreserveTab) {
		$value = preg_replace(array("/\t+/",'/  /'),array(' ',' '),$value);
	}

	if ($inputBlankAsValue != '' && $value==='') {
		if ($inputBlankAsValue == 'NULL')
			$value = NULL;
		else
			$value = $inputBlankAsValue;
	}

	$ret['tr']=$tr;
	$ret['value']=$retvalue=$value;
	$ret['msg']='บันทึกเรียบร้อย';
	$ret['error']='';
	$ret['debug'].='[group='.$group.' , part='.$part.', tpid='.$tpid.',fld='.$fld.',tr='.$tr.', parent='.$parent.', sorder='.$sorder.']<br />';
	$ret['debug'].='$_REQUEST='.print_r($_REQUEST,1).'<br />';



	$isValidParameter = $action == 'save' && ($tpid || is_null($tpid)) && $group && $fld;

	if (empty($post['orgid'])) {
		$orgid = mydb::select('SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid)->orgid;
	} else {
		$orgid = $post['orgid'];
	}

	$isEditable = user_access('administer projects')
		|| ($orgid && i()->ok && OrgModel::officerType($orgid,i()->uid))
		|| ($tpid && i()->ok && R::Model('paper.membership.get',$tpid,i()->uid));

	if ($_REQUEST['cancel']) $ret['err']='ยกเลิกการแก้ไข';
	else if (!($isEditable)) {
		$ret['error']='{tr:Access denied}';
		return $ret;
	}


	$dateFieldList=array('date1','date2','date_from','date_end','rdate_from','rdate_end');

	$rs=array($tpid);






	// Save data when action is save
	if ($action == 'save') {
		if (!$isValidParameter) $ret['error'] = 'ERROR : INVALID PARAMETER'.'Action='.$action.print_o($ret,'$ret').print_o(post(),'post()');

		if ($ret['error']) {
			$ret['msg']=$ret['error'];
			return $ret;
		}

		$values=array();
		if (is_string($value)) {
			$value=trim(strip_tags($value));
			// Function deprecate in php 8
			// $magic_quote=get_magic_quotes_gpc();
			// if ( $magic_quote == 1 ) $value = StripSlashes($value);
		}
		if (in_array($fld,$dateFieldList) || $dataType=='datepicker') {
			// Convert date from dd/mm/yyyy to yyyy-mm-dd
			list($dd,$mm,$yy)=explode('/',$value);
			if ($yy>2400) $yy=$yy-543;
			//$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
			$value = sg_date($value, SG\getFirst($post['convert'],'Y-m-d'));
		} else if ($returnType == 'point' || ($group=='project' && $fld=='location')) {
			// Convert 8° 7' 3" N / 99° 40' 9" => 8.1175,99.669166666667
			if (strpos($value, '°')) {
				$ret['debug'] .= 'Convert from '.$value.' ';
				list($lat,$lng) = explode('/', $value);
				if (load_lib('func.external.php','lib')) $value = convertDMSToDecimal($lat).','.convertDMSToDecimal($lng);
				$ret['debug'] .= 'to '.$value.'<br />';
			}
			$value = 'func.PointFromText("POINT('.preg_replace('/,/',' ',$value).')")';
		}

		// Update project transaction
		switch ($group) {
			case 'topic' :
				$values['tpid']=$tpid;
				$stmt='UPDATE %topic% SET `'.$fld.'`=:value WHERE `tpid`=:tpid LIMIT 1';
				break;

			case 'revision' :
				$values['tpid'] = $tpid;
				$stmt='UPDATE %topic_revisions% SET `'.$fld.'` = :value WHERE `tpid` = :tpid AND `revid` = :trid LIMIT 1';
				break;

			case 'project' :
				$values['tpid'] = $tpid;
				if ($fld == 'area') {
					// Update address
					$address = SG\explode_address($value, $post['areacode']);
					$values['house'] = $address['house'];
					$values['village'] = $address['village'];
					$values['tambon'] = $address['tambonCode'];
					$values['ampur'] = $address['ampurCode'];
					$values['changwat'] = $address['changwatCode'];

					$stmt = 'UPDATE %project% SET `village` = :village, `tambon` = :tambon, `ampur` = :ampur, `changwat` = :changwat, `area` = :value WHERE `tpid` = :tpid LIMIT 1';

					mydb::query(
						'UPDATE %topic% SET `areacode` = :areacode, `changwat` = :changwat WHERE `tpid` = :projectId',
						':projectId', $tpid,
						':areacode', $post['areacode'],
						':changwat', $values['changwat']
					);

					/*
					list($address,$tambon)=explode('|', $value);
					$values['area'] = $address;
					if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
						$stmt='UPDATE %project% SET `village`=:village, `area`=:area WHERE `tpid`=:tpid LIMIT 1';
						//$tambon=$value;//['tambon'];
						$out[3]=trim($out[3]);
						$values['house']=trim($out[1]);
						$values['village']=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
						if ($tambon) {
							$values['tambon']=substr($tambon,4,2);
							$values['ampur']=substr($tambon,2,2);
							$values['changwat']=substr($tambon,0,2);
							$stmt='UPDATE %project% SET `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat, `area`=:area WHERE `tpid`=:tpid LIMIT 1';
						}
					}
					*/
				} else {
					$stmt='UPDATE %project% SET `'.$fld.'`=:value WHERE `tpid`=:tpid LIMIT 1';
				}
				break;

			case 'prov' :
				$values['tpid'] = $tpid;
				if ($fld == 'location') {
					$firstProv = mydb::select('SELECT `autoid` FROM %project_prov% WHERE `tpid` = :tpid ORDER BY `autoid` ASC LIMIT 1', ':tpid', $tpid)->autoid;
					$ret['debug'] .= 'COUNT = '.$provCount.'<br />';
					if ($tr && $firstProv == $tr) {
						mydb::query('UPDATE %project% SET `location` = :value WHERE `tpid` = :tpid LIMIT 1', ':value', $value, $values);
						$ret['debug'] .= mydb()->_query;
					}
				}

				$stmt = 'UPDATE %project_prov% SET `'.$fld.'` = :value WHERE `tpid` = :tpid AND `autoid` = :trid LIMIT 1';
				break;

			case 'photo' :
				$stmt='UPDATE %topic_files% SET `'.$fld.'`=:value WHERE `fid`=:trid LIMIT 1';
				break;

			case 'property' :
				if ($part && $fld && $tpid) property($part.':'.$fld.':'.$tpid,$value);
				$ret['debug'].='<p>Update property</p>';
				break;

			case 'calendar' :
				$stmt='UPDATE %calendar% SET `'.$fld.'`=:value WHERE `id`=:trid LIMIT 1';
				break;

			case 'activity' :
				$stmt='UPDATE %project_activity% SET `'.$fld.'`=:value WHERE `calid`=:trid LIMIT 1';
				break;

			case 'org' :
				$stmt='UPDATE %db_org% SET `'.$fld.'`=:value WHERE `orgid`=:trid LIMIT 1';
				break;

			case 'doings' :
				$values['doid'] = $tr;
				if ($fld == 'areacode') $values['value'] = post('areacode');
				$stmt='UPDATE %org_doings% SET `'.$fld.'` = :value WHERE `doid` = :doid LIMIT 1';
				break;

			case 'paiddoc' :
				$values['tpid']=$tpid;
				$stmt='UPDATE %project_paiddoc% SET `'.$fld.'`=:value WHERE `tpid`=:tpid AND `paidid`=:trid LIMIT 1';
				break;

			case 'target' :
				if (post('removeempty') && $value === '') {
					mydb::query('DELETE FROM %project_target% WHERE `tpid`=:tpid AND `tgtid`=:tgtid LIMIT 1',':tpid',$tpid, ':tgtid',$tr);
					$ret['debug'] .= 'Query : '.mydb()->_query.'<br />';
				} else {
					$values['tpid'] = $tpid;
					$values['tagname'] = SG\getFirst(post('tagname'),'');
					$values['part'] = SG\getFirst($part,'');
					$stmt = 'INSERT INTO %project_target% (`tpid`, `tagname`, `tgtid`, `'.$fld.'`) VALUES (:tpid, :tagname, :part, :value) ON DUPLICATE KEY UPDATE `'.$fld.'` = :value';
				}
				//							$stmt='INSERT INTO %project_target% (`tpid`,`tgtid`,`'.$fld.'`) VALUES (:tpid,:trid,:value) ON DUPLICATE KEY UPDATE `'.$fld.'`=:value';
				break;

			case 'parent' :
				if (empty($value)) {
					mydb::query('DELETE FROM %topic_parent% WHERE `tpid`=:tpid AND `parent`=:planid AND `tgtid`=:tgtid LIMIT 1',':tpid',$tpid, ':planid',post('planid'), ':tgtid',$tr);
					$ret['debug'].='Query : '.mydb()->_query.'<br />';
				} else {
					$values['tpid']=$tpid;
					$stmt='INSERT INTO %topic_parent% (`tpid`,`parent`,`tgtid`) VALUES (:tpid,:value,:trid) ON DUPLICATE KEY UPDATE `parent`=:value';
				}
				break;

			case 'child' :
				if (empty($value)) {
					mydb::query('DELETE FROM %project_child% WHERE `tpid`=:tpid AND `planid`=:planid AND `tgtid`=:tgtid LIMIT 1',':tpid',$tpid, ':planid',post('planid'), ':tgtid',$tr);
					$ret['debug'].='Query : '.mydb()->_query.'<br />';
				} else {
					$values['tpid']=$tpid;
					$stmt='INSERT INTO %project_child% (`tpid`,`planid`,`tgtid`) VALUES (:tpid,:value,:trid) ON DUPLICATE KEY UPDATE `planid`=:value';
				}
				break;

			case 'moneyplan' :
				$stmt = 'UPDATE %project_fundmoneyplan% SET `'.$fld.'` = :value WHERE `orgId` = :orgId AND `budgetYear` = :budgetYear LIMIT 1';
				break;

			case 'bigdata' :
				//$group='bigdata';
				$values['keyid'] = $tpid;
				$values['keyname'] = $part;
				$values['fldname'] = $fld;
				$values['fldref'] = post('fldref');

				if ($tr) {
					$dupRs = mydb::select('SELECT * FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1', ':bigid', $tr);
				} else {
					$dupRs = mydb::select('SELECT * FROM %bigdata% WHERE `keyname` = :keyname AND `keyid` = :keyid AND `fldname` = :fldname'.($values['fldref'] ? ' AND `fldref` = :fldref' : '').' LIMIT 1',$values);
				}

				$isDup = $dupRs->keyid;

				if (post('key')) {
					$jsonData = sg_json_decode($dupRs->flddata);
					$jsonData->{post('key')} = $value;
					$values['flddata'] = sg_json_encode($jsonData);
				} else {
					$values['flddata'] = $value;
				}

				$ret['isdup'] = $isDup;

				if ($isDup) {
					$stmt = 'UPDATE %bigdata% SET
							`flddata` = :flddata
							, `modified` = :modified
							, `umodified` = :umodified
						WHERE
							'.($tr?'`bigid` = :bigid':'`keyid` = :keyid
							AND `keyname` = :keyname
							AND `fldname` = :fldname').'
						LIMIT 1';
					$values['umodified'] = i()->ok?i()->uid:'func.NULL';
					$values['modified'] = date('U');
					$values['bigid'] = $tr;
				} else {
					$stmt='INSERT INTO %bigdata% SET
						`keyname` = :keyname,
						`keyid` = :keyid,
						`fldname` = :fldname,
						`fldref` = :fldref,
						`flddata` = :flddata,
						`ucreated` = :ucreated,
						`created` = :dcreated';
					$values['ucreated'] = i()->ok?i()->uid:'func.NULL';
					$values['dcreated'] = date('U');
				}
				$isUpdateEdit = true;
				break;

			default :
				if ($tr) {
					if ($fld=='phototitle') {
						$stmt='UPDATE %topic_files% SET `title`=:value WHERE `fid`=:trid LIMIT 1';
					} else {
						$oldValue=mydb::select('SELECT `'.$fld.'` oldvalue FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr)->oldvalue;
						$values['modified']=date('U');
						$values['modifyby']=i()->ok?i()->uid:'func.NULL';
						$stmt='UPDATE %project_tr% SET `'.$fld.'`=:value, `modified`=:modified, `modifyby`=:modifyby WHERE `trid`=:trid LIMIT 1';
					}
				} else {
					$values['tpid']=$tpid;
					$values['formid']=$group;
					$values['period']=SG\getFirst($period,'func.NULL');
					$values['part']=SG\getFirst($part,'func.NULL');
					$values['sorder']=SG\getFirst($sorder,'func.NULL');
					$values['calid']=SG\getFirst($calid,'func.NULL');
					if ($fld!='refid') $values['refid']=SG\getFirst($refid,NULL);
					$values['parent']=SG\getFirst($parent,'func.NULL');
					$values['uid']=i()->ok?i()->uid:'func.NULL';
					$values['created']=date('U');
					$stmt='INSERT INTO %project_tr% SET `tpid`=:tpid, `parent`=:parent, `sorder`=:sorder'.($fld!='refid'?', `refid`=:refid':'').', `formid`=:formid, `period`=:period, `part`=:part,`calid`=:calid, `uid`=:uid, `created`=:created, `'.$fld.'`=:value';
				}
				break;
		}

		// Save value into table
		$ret['stmt'] = $stmt;
		if ($stmt) {
			mydb::query($stmt,':trid',$tr,':value',$value,$values);

			if (mydb()->_error) $ret['msg'] = 'ERROR ON UPDATE DATA!!!';

			$ret['query'] = mydb()->_query;
			if (empty($tr)) $tr = $ret['tr'] = mydb()->insert_id;

			$ret['debug'] .= 'stmt : '.$stmt.'<br />';
			$ret['debug'] .= 'Query : '.mydb()->_query.'<br />';
			$log = array(
				'key' => 'Form '.$group,
				'msg' => 'Form update',
				'sql' => mydb()->_query.'<br />'.(mydb()->_error?'Error : '.mydb()->_error.'<br />':'')
			);
		}

		$ret['debug'] .= 'Check delete '.$group.':'.$post['removeempty'].':'.$tr.':'.$post['fld'].':'.$post['value'].'<br />';

		if (in_array($group, array('info','eval-hia','qt','bigdata')) && $post['removeempty'] == 'yes' && $tr && $post['fld'] && $post['value'] == '') {
			if ($group == 'bigdata') {
				mydb::query('DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1',':bigid',$tr);
			} else if (in_array($group,array('info','eval-hia'))) {
				mydb::query('DELETE FROM %project_tr% WHERE `trid` = :trid LIMIT 1',':trid',$tr);
			}
			//if ($group=='bigdata') mydb::query('DELETE FROM %bigdata% WHERE `bigid`=:bigid LIMIT 1',':bigid',$post['tr']);
			$ret['tr'] = 0;
			$ret['debug'] .= 'Remove '.$post['id'].' : '.mydb()->_query.'<br />';
		}

		// Get updated partient information
		$ret['debug'].=$ret['fld'];

		// Set return value
		if ($returnType=='nl2br') {
			$ret['value']=nl2br($value);
		} else if ($returnType=='html') {
			$ret['value']=sg_text2html($value);
		} else if ($returnType=='text') {
			$ret['value']=nl2br($value);
		} else if ($returnType=='date') {
			$ret['value']=sg_date($value,$formatReturn);
		} else if ($returnType=='money') {
			$ret['value']=number_format($value,2);
		} else if (substr($returnType,0,1)=='%') {
			$ret['value']=sprintf($returnType,$value);
		}
	} else if ($action) {
		// Other Action
		switch ($action) {
			case 'get' :
				if ($group=='project' && $fld && $tpid) {
					$ret=mydb::select('SELECT '.$fld.' value FROM %project% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->value;
					if (in_array($fld,$dateFieldList)) $ret=sg_date($ret?$ret:date('Y-m-d'),'d/m/Y');
				} else if ($group=='property' && $part && $fld && $tpid) {
					$ret=property($part.':'.$fld.':'.$tpid);
				} else if ($fld && $tr) {
					$ret=mydb::select('SELECT '.$fld.' value FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr)->value;
					if (in_array($fld,array('date1','date2'))) $ret=sg_date($ret,'d/m/Y');
				} else $ret='';
				break;

			case 'add' :
				//create new project transaction
				$values['tpid']=$tpid;
				$values['formid']=$group;
				$values['period']=SG\getFirst($period,'func.NULL');
				$values['part']=$part;
				$values['calid']=SG\getFirst($calid,'func.NULL');
				$values['uid']=i()->ok?i()->uid:'func.NULL';
				$values['created']=date('U');
				if ($_REQUEST['activity']) {
					$values['detail1']=$_REQUEST['activity'];
					$values['detail2']=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:detail1 LIMIT 1',$values)->title;
				}
				$stmt='INSERT INTO %project_tr% SET `tpid`=:tpid, `formid`=:formid, `period`=:period, `part`=:part, `calid`=:calid, `uid`=:uid, `created`=:created'.($values['detail1']?', `detail1`=:detail1':'').($values['detail2']?', `detail2`=:detail2':'');
				mydb::query($stmt,$values);
				$ret['msg']='เพิ่มรายการเรียบร้อย';
				break;

			case 'del' :
				$trrs=mydb::select('SELECT * FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr);
				$stmt='DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
				mydb::query($stmt,':trid',$tr);
				$log=array('key'=>'Form '.$trrs->formid, 'msg'=>'Remove project_tr item trid='.$tr,'sql'=>print_o($trrs,'$rs'));
				$ret['msg']='ลบรายการเรียบร้อย';
				break;

			case 'photo' :
				$is_new_gallery=false;
				$ret='';

				$rs=mydb::select('SELECT `tpid`,`gallery` FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr);
				$gallery=$rs->gallery;
				$tpid=$rs->tpid;
				if (empty($gallery)) {
					$gallery=mydb::select('SELECT MAX(gallery) lastgallery FROM %topic_files% LIMIT 1')->lastgallery+1;
					$is_new_gallery=true;
				}

				// Multiphoto file upload
				$uploadPhotoFiles=array();

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
					if (!is_uploaded_file($postFile['tmp_name'])) {
						$ret.="Upload error : No upload file";
						continue;
					}

					$ext=strtolower(sg_file_extension($postFile['name']));
					if (in_array($ext,array('jpg','jpeg','png'))) {
						// Upload photo
						$upload=new classFile($postFile,cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
						if (!$upload->valid_format()) {
							$ret.="Upload error : Invalid photo format";
							continue;
						}
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
						$pics_desc['title'] = $postFile['name'];
						$upload=new classFile($postFile,cfg('paper.upload.document.folder'),cfg('topic.doc.file_ext'));
						if (!$upload->valid_extension()) {
							$ret.="Upload error : Invalid file format";
							continue;
						}
						if ($upload->duplicate()) $upload->generate_nextfile();
						$photo_upload=$upload->filename;
					}

					$pics_desc['tpid'] = $tpid;
					$pics_desc['refid'] = $tr;
					$pics_desc['cid'] = 'func.NULL';
					$pics_desc['gallery'] = $gallery;
					$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
					$pics_desc['file']=$photo_upload;
					$pics_desc['tagname'] = SG\getFirst(post('tag'));
					$pics_desc['timestamp']='func.NOW()';
					$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

					if ($upload->copy()) {
						$stmt='INSERT INTO %topic_files% (`type`, `tpid`, `cid`, `refid`, `gallery`, `uid`, `file`, `tagname`, `title`, `timestamp`, `ip`) VALUES (:type, :tpid, :cid, :refid, :gallery, :uid, :file, :tagname, :title, :timestamp, :ip)';
						mydb::query($stmt,$pics_desc);

						$fid = mydb()->insert_id;

						if ($is_new_gallery) mydb::query('UPDATE %project_tr% SET gallery=:gallery WHERE `trid`=:trid LIMIT 1',':trid',$tr,':gallery',$gallery);
						if ($pics_desc['type']=='photo') {
							$photo=model::get_photo_property($upload->filename);
							$ret.='<a class="sg-action" data-group="photo'.$tr.'" href="'.$photo->_url.'" data-rel="img" title="">';
							$ret.='<img class="photo" src="'.$photo->_url.'" alt="" />';
							$ret.='</a>';
							$ret.='<nav class="nav iconset -hover"><a class="sg-action" href="'.url('project/edit/delphoto/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -delete"></i></a></nav>';
							$ret.='<span onclick="" data-group="photo" data-fld="title" data-tr="'.$fid.'" data-placeholder="..." class="inline-edit-field" data-type="text" data-value="" title="คลิกเพื่อแก้ไข">...</span>';
						} else {
							$uploadUrl = cfg('paper.upload.document.url').$upload->filename;
							$navUi = new Ui('span');
							$navUi->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/docs.delete/'.$fid).'" data-rel="none" data-title="ลบไฟล์" data-confirm="ต้องการลบไฟล์นี้ กรุณายืนยัน?" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a>');
							$ret.='<a href="'.$uploadUrl.'" target="_blank"><img class="doc-logo -pdf" src="http://img.softganz.com/icon/icon-file.png" width="63" style="display: block; padding: 16px; margin: 0 auto; background-color: #eee; border-radius: 50%;" />'.$pics_desc['title'].'</a><nav class="nav -icons -hover -top-right">'.$navUi->build().'</nav>';
						}
					} else {
						$ret.='Upload error : Cannot save upload file';
					}
					$ret.='</li><li class="-hover-parent">';
				}
				$ret=rtrim($ret,'</li><li class="-hover-parent">');
				die($ret);
				break;

			default :
				$ret['error'] = 'ERROR : INVALID ACTION';
				break;
		}
	}

	if ($log) {
		R::Model(
			'watchdog.log',
			'project',
			$log['key'],
			$log['msg'].'<br />tpid='.$tpid.' , trid='.$tr.' , formid='.$group.' , period='.$values['period'].' , part='.$values['part'].' , calid='.$values['calid'].'<br />'.(isset($oldValue)?'Old value='.$oldValue.'<br />':'').'Set '.$fld.' = '.$value.'<br />'.($log['sql']?$log['sql'].'<br />':'').($values?print_o($values,'$values').'<br />':''),
			NULL,
			$tpid
		);

	}

	//debugMsg(print_o($ret,'$ret'));
	//if (!_AJAX) $ret['location']=array('paper/'.$id);
	return $ret;
}
?>