<?php
/**
* Save information and cost transaction
*
* @param Object $self
* @param Array $_POST
* @return String
*/
$debug = true;


function icar_edit_cost($self) {
	$fld=trim($_REQUEST['fld']);
	$tr=trim($_REQUEST['tr']);
	list($group,$part)=explode(':',$_REQUEST['group']);
	$value=$_REQUEST['value'];
	$return=$_REQUEST['ret'];

	$ret['tr']=$tr;
	$ret['value']=$retvalue=trim($value);
	$ret['msg']='บันทึกเรียบร้อย';
	$ret['error']='';
	$ret['debug'].='[group='.$group.' , part='.$part.', fld='.$fld.',tr='.$tr.']<br />';
	$ret['debug'].=print_o($_REQUEST,'$_REQUEST');

	$costInfo=mydb::select('SELECT c.*, tg.`name` FROM %icarcost% c LEFT JOIN %tag% tg ON tg.`tid`=c.`costcode` WHERE c.`costid` = :costid LIMIT 1',':costid',$tr);

	$carInfo = R::Model('icar.get', $costInfo->tpid);
	$carId = $carInfo->tpid;

	if (!$carInfo) return $ret + array('error'=>'ERROR : ไม่มีข้อมูลรถ');


	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;
	$isEdit = ($isAdmin || $isShopOfficer);

	if (!$isEdit) return $ret + array('error' => 'ERROR : Access Denied');

	switch ($_REQUEST['action']) {
		case 'get' :
			if ($fld=='costcode') $value=icar_model::category('icar:tr:%',$carInfo->shopid);
			else $value=$costInfo->{$fld};
			return $value;
			break;
		
		case 'save' :
			if (empty($tr) || empty($group) || empty($fld)) $ret['error']='Invalid parameter';
			if ($ret['error']) {
				$ret['msg']=$ret['error'];
				return $ret;
			}
			
			if (is_string($value)) $value=trim(strip_tags($value));
			if (in_array($fld,array('buydate','itemdate'))) {
				// Convert date from dd/mm/yyyy to yyyy-mm-dd
				list($dd,$mm,$yy)=explode('/',$value);
				if ($yy>2400) $yy=$yy-543;
				$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
			}  else if ($return=='numeric') $value=preg_replace('/[^0-9\.\-]/','',$value);

			// Update project transaction
			switch ($group) {
				case 'property' :
					if ($part && $fld && $tpid) property($part.':'.$fld.':'.$tpid,$value);
					$ret['debug'].='<p>Update property</p>';
					break;

				case 'cost' :
					mydb::query('UPDATE %icarcost% SET `'.$fld.'`=:value WHERE `costid`=:costid LIMIT 1',':costid',$tr,':value',$value);
					$ret['debug'].='Update cost transaction'.mydb()->_query;

					$log = array(
						'key'=>'Car Cost Edit',
						'msg'=>'Car Cost Edit : code = '.$costInfo->costcode.' , interest = '.$costInfo->interest.' , amt = '.$costInfo->amt.' , name = '.$costInfo->name,
						'fldname' => $fld.':'.$tr,
					);

					R::Model('icar.get', $carId,'{updatecost: true}');
					break;
			}
			
			// Save value into table
			if ($stmt) {
				mydb::query($stmt,':trid',$tr,':value',$value,$values);
				if (empty($tr)) $tr=$ret['tr']=mydb()->insert_id;
				$ret['debug'].='Query : '.mydb()->_query.'<br />';

				$log = array(
					'key'=>'Car Cost Edit',
					'msg'=>'Form update',
					'sql'=>mydb()->_query.'<br />'.(mydb()->_error?'Error : '.mydb()->_error.'<br />':''),
				);
			}

			// Set return value
			if ($return=='html') $ret['value']=sg_text2html($value);
			else if ($return=='numeric') $ret['value']=number_format($value,2);
			else if ($fld=='costcode') $ret['value']=icar_model::category($value);
			break;
	}

	if ($log) {
		model::watch_log(
			'icar',
			$log['key'],
			$log['msg']
				.'<br />'
				.(isset($oldValue) ? 'Old value='.$oldValue.'<br />' : '')
				.($fld ? 'Set '.$fld.' = '.$value.'<br />' : '')
				.($log['sql'] ? $log['sql'].'<br />':'')
				.($values ? print_o($values,'$values').'<br />' : ''),
			NULL,
			$carInfo->tpid,
			$log['fldname']
		);
	}

	//if (!_AJAX) $ret['location']=array('icar');
	return $ret;
}
?>