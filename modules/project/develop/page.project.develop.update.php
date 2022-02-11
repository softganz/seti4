<?php
import('model:org.php');

function project_develop_update($self, $tpid = NULL) {
	$post = post();
	list($group,$formid,$part) = explode(':',$post['group']);
	$fld = trim($post['fld']);
	$tr = trim($post['tr']);
	$parent = post('parent');
	$sorder = SG\getFirst(post('sorder'),0);
	$calid = trim($post['calid']);
	$refid = trim($post['refid']);
	$value = trim($post['value']);
	//$value = is_null($post['value']) ? NULL : trim($post['value']);
	$return = $post['ret'];
	$action = $post['action'];
	$dataType = $post['type'];
	$tpid = SG\getFirst($post['tpid'], $tpid);

	list($returnType,$formatReturn) = explode(':',$post['ret']);

	//if (!is_null($value)) {
	if (in_array($returnType, array('money', 'numeric'))) {
		$value = sg_strip_money($value);
	}

	$value = preg_replace(array("/\t+/",'/  /'),array(' ',''),$value);

	$ret['tr'] = $tr;
	$ret['value'] = $retvalue = $value;
	$ret['error'] = '';
	$ret['msg'] = 'บันทึกเรียบร้อย';
	$ret['debug'] .= 'DEBUG of Project.Develop.Update ';
	$ret['debug'] .= 'action='.$action.', group='.$group.', fld='.$fld.', tr='.$tr.'<br />';
	$ret['debug'] .= 'Value='.$value.'<br />';
	$ret['debug'] .= print_o($post,'$post');
	$ret['post'] = $post;

	$topic = mydb::select('SELECT t.* FROM %topic% t WHERE t.`tpid` = :tpid LIMIT 1', ':tpid', $tpid);

	if ($topic->_empty) {
		$ret['msg'] = 'Oop!!! การบันทึกข้อมูลผิดพลาด ไม่มีข้อมูลโครงการที่ระบุ กรุณาแจ้งผู้ดูแลเว็บไซท์';
		return $ret;
	}



	// OLD Right
	$isEditable=user_access('administer projects') || ((in_array($topic->status,array(1,3)) || ($topic->status==2 && $cdate>='2014-04-19 16:00:00' && $cdate<='2014-04-27 16:00:00') ) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_trainer_of($tpid)) || (substr($fld,0,8)=='comment-' && user_access('comment project')));

	$isEditable = user_access('administer projects','edit own project content',$topic->uid)
		|| project_model::is_owner_of($tpid)
		|| project_model::is_trainer_of($tpid)
		|| ($topic->orgid && i()->ok && OrgModel::officerType($topic->orgid,i()->uid))
		|| (substr($fld,0,8)=='comment-' && user_access('comment project'))
		;


	// CURRENT Right
	$devInfo = R::Model('project.develop.get', $tpid);
	$isEditable = $devInfo->RIGHT & _IS_RIGHT;


	if (0 && !$isEditable) {
		$ret['error']='Oop!!! สิทธิ์ในการแก้ไขถูกปฏิเสธ ท่านอาจจะออกจากระบบสมาชิกไปแล้ว กรุณาเข้าสู่ระบบสมาชิกอีกครั้ง';
		$ret['msg']='Oop!!! สิทธิ์ในการแก้ไขถูกปฏิเสธ';
		return $ret;
	}

	$isUpdateEdit=false;

	if (is_string($value)) {
		$value=trim(strip_tags($value));
		// Function deprecate in php 8
		// $magic_quote=get_magic_quotes_gpc();
		// if ( $magic_quote == 1 ) $value = StripSlashes($value);
	}

	if (substr($fld,0,4)=='date' || $dataType=='datepicker') {
		// Convert date from dd/mm/yyyy to yyyy-mm-dd
		list($dd,$mm,$yy)=explode('/',$value);
		if ($yy>2400) $yy=$yy-543;
		$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
	} else if ($fld=='location') {
		$value='func.PointFromText("POINT('.preg_replace('/,/',' ',$value).')")';
	}

	$values['keyname'] = SG\getFirst($formid,'project.develop');
	$values['tpid']=$tpid;
	if ($fld) {
		switch ($group) {
			case 'topic' :
				$stmt = 'UPDATE %topic% SET `'.$fld.'` = :value WHERE `tpid` = :tpid LIMIT 1';
				break;

			case 'dev' :
				$stmt = 'UPDATE %project_dev% SET `'.$fld.'` = :value WHERE `tpid` = :tpid LIMIT 1';
				break;

			case 'target':
				$values['tpid'] = $tpid;
				$values['tagname'] = 'develop';
				$values['tgtid'] = $post['tgtid'];
				if ($fld == 'tgtid') {
					$stmt = 'INSERT INTO %project_target% (`tpid`, `tagname`, `tgtid`) VALUES (:tpid, :tagname, :tgtid) ON DUPLICATE KEY UPDATE `'.$fld.'` = :value';
				} else {
					$stmt = 'INSERT INTO %project_target% (`tpid`, `tagname`, `tgtid`, `'.$fld.'`) VALUES (:tpid, :tagname, :tgtid, :value) ON DUPLICATE KEY UPDATE `'.$fld.'` = :value';
				}
				break;

			case 'tr' :
				if ($tr) {
					//$oldValue=mydb::select('SELECT `'.$fld.'` oldvalue FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr)->oldvalue;
					$values['modified']=date('U');
					$values['modifyby']=i()->ok?i()->uid:'func.NULL';
					$stmt='UPDATE %project_tr%
									SET `'.$fld.'` = :value
									, `modified` = :modified
									, `modifyby` = :modifyby
									WHERE `trid` = :tr LIMIT 1';
				} else {
					$values['tpid']=$tpid;
					$values['formid']=$formid;
					$values['period']=SG\getFirst($period,NULL);
					$values['part']=SG\getFirst($part,NULL);
					$values['sorder']=SG\getFirst($sorder,0);
					$values['calid']=SG\getFirst($calid,NULL);
					if ($fld!='refid') $values['refid']=SG\getFirst($refid,NULL);
					$values['parent']=SG\getFirst($parent,NULL);
					$values['uid']=i()->ok?i()->uid:NULL;
					$values['created']=date('U');
					$stmt='INSERT INTO %project_tr%
									SET `tpid` = :tpid
									, `parent` = :parent
									, `sorder` = :sorder'.($fld!='refid'?', `refid` = :refid':'').'
									, `formid` = :formid
									, `part` = :part
									, `period` = :period
									, `calid` = :calid
									, `uid` = :uid
									, `created` = :created
									, `'.$fld.'` = :value';
				}
				/*
				$stmt='UPDATE %project_tr% SET `'.$fld.'`=:value, `modified`=:modified, `modifyby`=:modifyby WHERE `trid`=:tr LIMIT 1';
				$values['modified']=date('U');
				$values['modifyby']=i()->uid;
				*/
				$isUpdateEdit=true;


				break;

			default :
				$group = 'bigdata';
				$values['fldname'] = $fld;
				$values['flddata'] = SG\getFirst($value);
				$values['keyid'] = $tpid;
				$values['fldref'] = SG\getFirst(post('fldref'));

				if ($tr) {
					$isDupId = mydb::select('SELECT `bigId` FROM %bigdata% WHERE `bigId` = :bigid LIMIT 1', ':bigid', $tr)->bigId;
				} else if ($post['optiondupfield']) {
					$checkDuplicateField = explode(',', $post['optiondupfield']);
					if (in_array('keyName', $checkDuplicateField)) mydb::where('`keyName` = :keyname');
					if (in_array('keyId', $checkDuplicateField)) mydb::where('`keyId` = :keyid');
					if (in_array('fldName', $checkDuplicateField)) mydb::where('`fldName` = :fldname');
					if (in_array('fldRef', $checkDuplicateField)) mydb::where('`fldRef` = :fldref');
					$isDupId = mydb::select('SELECT `bigId` FROM %bigdata% %WHERE% LIMIT 1', $values)->bigId;
					$ret['debug'] .= 'CHECK DUPLICATE : '.$post['optiondupfield'].' : '.mydb()->_query.'<br />';
					$ret['debug'] .= 'CHECK DUPLICATE : BIGID = '.$isDupId.'<br />';
				} else {
					$isDupId = mydb::select('SELECT `bigId` FROM %bigdata% WHERE `keyname` = :keyname AND `keyid` = :keyid AND `fldname` = :fldname'.($values['fldref'] ? ' AND `fldref` = :fldref' : '').' LIMIT 1',$values)->bigId;
					$ret['debug'] .= mydb()->_query;
				}

				$ret['isdup'] = $isDupId;
				if ($isDupId) $tr = $isDupId;

				if ($isDupId) {
					$stmt = 'UPDATE %bigdata% SET
							`fldRef` = :fldref
							, `fldData` = :flddata
							, `modified` = :modified
							, `umodified` = :umodified
						WHERE
							'.($tr ? '`bigid` = :bigid' : '`keyid` = :keyid
							AND `keyname` = :keyname
							AND `fldname` = :fldname').'
						LIMIT 1';

					$values['umodified'] = i()->ok?i()->uid:'func.NULL';
					$values['modified'] = date('U');
					$values['bigid'] = $tr;
				} else {
					$stmt = 'INSERT INTO %bigdata% SET
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

				//$isDup = $tr || mydb::select('SELECT * FROM %bigdata% WHERE `keyid` = :keyid AND `keyname` = :keyname AND `fldname` = :fldname LIMIT 1',$values)->keyid;
				//$ret['isdup'] = $isDup;

				/*

				if ($isDup) {
					$stmt = 'UPDATE %bigdata% SET
							`flddata` = :value
							, `modified` = :modified
							, `umodified` = :umodified
						WHERE
							'.($tr?'`bigid` = :bigid':'`keyid`=:keyid
							AND `keyname` = :keyname
							AND `fldname` = :fldname').'
						LIMIT 1';

					$values['umodified']=i()->ok?i()->uid:'func.NULL';
					$values['modified']=date('U');
					$values['bigid'] = $tr;
				} else {
					$stmt = 'INSERT INTO %bigdata% SET
						`keyname` = :keyname,
						`keyid` = :keyid,
						`fldname` = :fldname,
						`flddata` = :flddata,
						`ucreated` = :ucreated,
						`created` = :dcreated';

					$values['ucreated'] = i()->ok?i()->uid:'func.NULL';
					$values['dcreated'] = date('U');
				}
				*/
				$isUpdateEdit = true;
				break;
		}

		// Save value into table
		if ($stmt) {
			if ($fld=='project-title') {
				mydb::query('UPDATE %topic% SET `title`=:title WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':title',$value);
			}

			mydb::query($stmt,':tr',$tr,':value',$value,$values);
			if (in_array($group,array('tr','qt','bigdata')) && empty($tr)) {
				$tr=$ret['tr']=mydb()->insert_id;
				$ret['debug'].='Return tr='.$tr.'<br />';
			}

			$ret['debug'].='stmt : '.str_replace("\r", '<br />', $stmt).'<br />';
			$ret['debug'].='Query : '.str_replace("\r", '<br />', mydb()->_query).'<br />';

			// Remove empty value from bigdata
			//$ret['debug'].='DEBUG group='.$group.' : removeempty='.$post['removeempty'].' : id='.$post['id'].' : formid='.$formid.' : fld='.$post['fld'].' : value='.$post['value'].'<br />';
			if ($post['removeempty']=='yes' && empty($value)) {
				if ($group == 'bigdata' && $tr) {
					mydb::query('DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1',':bigid',$post['tr']);
				} else if ($group == 'bigdata') {
					$stmt = 'DELETE FROM %bigdata% WHERE `keyid` = :keyid AND `keyname` = :keyname AND `fldname` = :fldname LIMIT 1';
					mydb::query($stmt,':keyid',$tpid, ':keyname', $values['keyname'], ':fldname',$post['fld']);
				} else if($group == 'tr') {
					$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :trid AND `tpid` = :tpid LIMIT 1';
					mydb::query($stmt,':tpid',$tpid, ':trid', $tr);
				}

			/*
			$post['id'] && $post['fld'] && $post['value']=='') {
				if ($group=='bigdata') {
					} else if (in_array($group,array('tr'))) {
						mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',$tr);
					}
				//if ($group=='bigdata') ;
				*/
				$ret['tr']='';
				$ret['debug'].='Remove '.$post['id'].' : '.mydb()->_query.'<br />';
			}


			// Log to watchdog
			$log = [
				'keyid' => $tpid,
				'key' => 'history',
				'msg' => $value,
				'sql' => mydb()->_error?'Error : '.mydb()->_error.'<br />'.mydb()->_query.'<br />':NULL
			];

			// Update change time
			if (preg_match('/^comment\-hsmi\-/', $fld)) {
				mydb::query('UPDATE %topic% SET `commenthsmidate`=:changed WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
			} else if ($fld=='comment-sss') {
				mydb::query('UPDATE %topic% SET `commentsssdate`=:changed WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
			} else if ($isUpdateEdit) {
				R::On('project.proposal.change',$tpid,'update',$ret);
			}

			if ($log) {
				model::watch_log('project',$log['key'],$log['msg'],NULL,$tpid,$group.','.$formid.','.$part.','.$fld.','.$tr);
			}
		}
		if ($return=='numeric') $ret['value']=number_format($value);
		else if ($return=='money') $ret['value']=number_format($value,2);
		else if ($return=='html') $ret['value']=sg_text2html($value);
		else if ($return=='none') $ret['value']=NULL;
		else if (substr($return,0,4)=='date') {
			list($return,$format)=explode(':',$return);
			$ret['value']=sg_date($value,$format);
		} else if ($post['type']=='textarea') $ret['value']=nl2br($value);
	} else {
		$ret='';
		$ret=print_o($topic,'$topic');
		$ret.=print_o($post,'$post');
	}
	return $ret;
}
?>