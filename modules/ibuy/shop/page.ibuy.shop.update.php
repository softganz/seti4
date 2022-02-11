<?php
function ibuy_shop_update($self,$tpid = NULL) {
	$post=post();
	list($group,$formid,$part)=explode(':',$post['group']);
	$fld=trim($post['fld']);
	$tr=trim($post['tr']);
	$value=trim($post['value']);
	$return=$post['ret'];
	$action=$post['action'];
	$dataType=$post['type'];
	$tpid=SG\getFirst($post['tpid'],$tpid);

	if ($post['ret']=='numeric' || $post['ret']=='money') $value=preg_replace('/[^0-9\.\-]/','',$value);
	$value=preg_replace(array("/\t+/",'/  /'),array(' ',''),$value);

	if (substr($post['ret'],0,4)=='date') {
		list($post['ret'],$dateFormat)=explode(':',$post['ret']);
		$value=sg_date($value,$post['datetype']=='bigint'?'U':'Y-m-d');
	}

	$ret['tr']=$tr;
	$ret['value']=$retvalue=$value;
	$ret['error']='';
	$ret['msg']='บันทึกเรียบร้อย';
	$ret['debug'].='action='.$action.', group='.$group.', fld='.$fld.', tr='.$tr.'<br />';
	$ret['debug'].='Value='.$value;
	$ret['debug'].=print_o($post,'$post');
	$ret['post']=$post;

	if ($tpid) $rs=mydb::select('SELECT t.* FROM %topic% t WHERE t.`tpid`=:tpid LIMIT 1',':tpid',$tpid);

	if ($tpid && $rs->_empty) {
		$ret['msg']='Oop!!! การบันทึกข้อมูลผิดพลาด ไม่มีข้อมูลโครงการที่ระบุ กรุณาแจ้งผู้ดูแลเว็บไซท์';
		return $ret;
	}
	$topicUid=mydb::select('SELECT `uid` FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$post['tpid'])->uid;
	$is_edit=user_access('administer ibuys','edit own product content',$topicUid);

	if (!$is_edit) {
		$ret['error']='Oop!!! สิทธิ์ในการแก้ไขถูกปฏิเสธ ท่านอาจจะออกจากระบบสมาชิกไปแล้ว กรุณาเข้าสู่ระบบสมาชิกอีกครั้ง';
		$ret['msg']='Oop!!! สิทธิ์ในการแก้ไขถูกปฏิเสธ';
		return $ret;
	}

	$isUpdateEdit=false;

	$values['keyname']='project.develop';
	$values['tpid']=$tpid;
	if ($fld) {
		switch ($group) {
			case 'topic' :
				$stmt='UPDATE %topic% SET `'.$fld.'`=:value WHERE `tpid`=:tpid LIMIT 1';
				break;

			case 'product' :
				$stmt='UPDATE %ibuy_product% SET `'.$fld.'`=:value WHERE `tpid`=:tpid LIMIT 1';
				break;

			case 'franchise' :
				$stmt='UPDATE %ibuy_customer% SET `'.$fld.'`=:value WHERE `uid`=:tr LIMIT 1';
				break;

			case 'order' :
				$stmt='UPDATE %ibuy_order% SET `'.$fld.'`=:value WHERE `oid`=:tr LIMIT 1';
				$values['oid']=$post['oid'];
				break;

			/*
			case 'tr-xxx' :
				$stmt='UPDATE %project_tr% SET `'.$fld.'`=:value, `modified`=:modified, `modifyby`=:modifyby WHERE `trid`=:tr LIMIT 1';
				$values['modified']=date('U');
				$values['modifyby']=i()->uid;
				$isUpdateEdit=true;
				break;

			default :
				$group='bigdata';
				$values['fldname']=$fld;
				$values['flddata']=$value;
				$values['keyid']=$tpid;
				$isDup=$tr || mydb::select('SELECT * FROM %bigdata% WHERE `keyid`=:keyid AND `keyname`=:keyname AND `fldname`=:fldname LIMIT 1',$values)->keyid;
				$ret['isdup']=$isDup;
				if ($isDup) {
					$stmt='UPDATE %bigdata% SET
									`flddata`=:value
									, `modified`=:modified
									, `umodified`=:umodified
								WHERE
									'.($tr?'`bigid`=:bigid':'`keyid`=:keyid
									AND `keyname`="project.develop"
									AND `fldname`=:fldname').'
								LIMIT 1';
					$values['umodified']=i()->ok?i()->uid:'func.NULL';
					$values['modified']=date('U');
					$values['bigid']=$tr;
				} else {
					$stmt='INSERT INTO %bigdata% SET
									`keyname`=:keyname,
									`keyid`=:keyid,
									`fldname`=:fldname,
									`flddata`=:flddata,
									`ucreated`=:ucreated,
									`created`=:dcreated';
					$values['ucreated']=i()->ok?i()->uid:'func.NULL';
					$values['dcreated']=date('U');
				}
				$isUpdateEdit=true;
				break;
				*/
		}

		// Save value into table
		if ($stmt) {
			mydb::query($stmt,':tr',$tr,':value',$value,$values);
			if (in_array($group,array('qt','bigdata')) && empty($tr)) {
				$tr=$ret['tr']=mydb()->insert_id;
				$ret['debug'].='Return tr='.$tr;
			}
			$ret['debug'].='stmt : '.str_replace("\r", '<br />', $stmt).'<br />';
			$ret['debug'].='Query : '.str_replace("\r", '<br />', mydb()->_query).'<br />';
			if (mydb()->_error) $ret['debug'].='Error : '.mydb()->_error;

			// Log to watchdog
			/*
			$log=array(	'keyid'=>$tpid,
									'key'=>'history',
									'msg'=>$value,
									'sql'=>mydb()->_error?'Error : '.mydb()->_error.'<br />'.mydb()->_query.'<br />':NULL);
			*/

			if ($log) {
				model::watch_log('project',$log['key'],$log['msg'],NULL,$tpid,$group.','.$formid.','.$part.','.$fld.','.$tr);
			}
		}
		if ($post['ret']=='numeric') $ret['value']=number_format($value);
		else if ($post['ret']=='money') $ret['value']=number_format($value,2);
		else if ($post['ret']=='html') $ret['value']=sg_text2html($value);
		else if ($post['ret']=='none') $ret['value']=NULL;
		else if ($post['type']=='textarea') $ret['value']=nl2br($value);
		else if ($post['ret']=='date') $ret['value']=sg_date($value,$dateFormat);
		return $ret;
	} else {
		$ret['msg']='ไม่มีฟิลด์ข้อมูลให้บันทึก';
	}
	return $ret;
}
?>