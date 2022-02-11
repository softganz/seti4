<?php
/**
* Module Method
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_info($self, $productInfo = NULL, $action = NULL, $tranId = NULL) {
	if (!($productId = $productInfo->tpid)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$isAdmin = user_access('administer ibuys');
	$isOfficer = $isAdmin || ($productInfo->RIGHT & _IS_EDITABLE) || user_access('access ibuys customer');

	if (!$isOfficer) return message('error', 'Access Denied');


	switch ($action) {
		case 'photo.upload':
			$post = (Object) post();
			$data->tpid = $productId;
			$data->prename = 'ibuy_'.$productId.($post->tagname ? '_'.$post->tagname : '').'_';
			$data->tagname = 'ibuy'.($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			//$data->orgid = $shopId;
			//$data->refid = $tranId;
			//$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'ibuy/'.$productId.'/info/photo.delete/';
			$data->link = $post->link;
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data, '{showDetail: false}');

			if($uploadResult->error) {
				$ret = implode(' ', $uploadResult->error);
			} else {
				$ret = $uploadResult->link;
			}
			//$ret .= '<img class="photoitem -wide" src="" />';
			break;

		case 'photo.delete':
			if ($tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'field':
			$ret = __ibuy_info_update_field(post());
			break;

		default:
			$ret = 'ERROR!!! No Action';
			break;
	}

	return $ret;
}

function __ibuy_info_update_field($post) {
	$tpid = intval(trim(SG\getFirst($post['id'], $post['tpid'])));
	if ($tpid <= 0) $tpid = NULL;
	$period = $post['period'];
	$fld = trim($post['fld']);
	$calid = trim($post['calid']);
	$refid = trim($post['refid']);
	$tranId = intval(trim($post['trid']));
	$parent = post('parent');
	$sorder = SG\getFirst(post('sorder'), 0);
	$value = $post['value'];
	$dataType = $post['type'];
	$action = post('action');
	$inputBlankAsValue = post('blank');
	list($group, $part) = explode(':', $post['group']);
	list($returnType,$formatReturn) = explode(':',$post['ret']);

	$isPreserveTab = $post['preservtab'];

		if ($value=='...') return array('value'=>$value);

	if ($value && (in_array($returnType, array('money', 'numeric')))) {
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

	$ret['trid'] = $tranId;
	$ret['value'] = $retvalue = $value;
	$ret['msg'] = 'บันทึกเรียบร้อย';
	$ret['error'] = '';
	$ret['debug'] .= '[group='.$group.' , part='.$part.', tpid='.$tpid.',fld='.$fld.',tr='.$tranId.', parent='.$parent.', sorder='.$sorder.']<br />';
	$ret['debug'] .= '$_REQUEST = '.print_r($_REQUEST,1).'<br />';


	$values['tpid'] = $tpid;
	mydb::value('$FIELD$', '`'.$fld.'`');

	// Update project transaction
	switch ($group) {
		case 'topic' :
			$stmt = 'UPDATE %topic% SET $FIELD$ = :value WHERE `tpid` = :tpid LIMIT 1';
			break;

		case 'revision' :
			$stmt = 'UPDATE %topic_revisions% SET $FIELD$ = :value WHERE `tpid` = :tpid AND `revid` = :trid LIMIT 1';
			break;

		case 'product' :
			$stmt = 'UPDATE %ibuy_product% SET $FIELD$ = :value WHERE `tpid`=:tpid LIMIT 1';
			break;

		}

	// Save value into table
	$ret['stmt'] = $stmt;
	if ($stmt) {
		mydb::query($stmt, ':trid', $tranId, ':value', $value, $values);

		if (mydb()->_error) $ret['msg'] = 'ERROR ON UPDATE DATA!!!';

		$ret['query'] = mydb()->_query;
		if (empty($tranId)) $tranId = $ret['tr'] = mydb()->insert_id;

		$ret['debug'] .= 'stmt : '.$stmt.'<br />';
		$ret['debug'] .= 'Query : '.mydb()->_query.'<br />';
		$log = array(
			'key' => 'Form '.$group,
			'msg' => 'Form update',
			'sql' => mydb()->_query.'<br />'.(mydb()->_error?'Error : '.mydb()->_error.'<br />':'')
		);
	}


	// Get updated partient information
	$ret['debug'] .= $ret['fld'];

	// Set return value
	if ($returnType == 'nl2br') {
		$ret['value'] = nl2br($value);
	} else if ($returnType == 'html') {
		$ret['value'] = sg_text2html($value);
	} else if ($returnType == 'text') {
		$ret['value'] = nl2br($value);
	} else if ($returnType == 'date') {
		$ret['value'] = sg_date($value,$formatReturn);
	} else if ($returnType == 'money') {
		$ret['value'] = number_format($value,2);
	} else if (substr($returnType,0,1) == '%') {
		$ret['value'] = sprintf($returnType,$value);
	}
	return $ret;
}
?>