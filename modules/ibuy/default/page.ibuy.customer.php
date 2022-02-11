<?php
/**
 * Listing Customer
 *
 * @param Argument list in parameter format
 * @return String
 */
function ibuy_customer($self, $customerId = NULL, $action = NULL, $tranId = NULL) {
	$isAdmin = user_access('administer ibuys');
	$isOfficer = $isAdmin || user_access('access ibuys customer');
	
	if (!$isOfficer) return message('error', 'Access Denied');

	if ($customerId == '*') {

	} else if (is_numeric($customerId)) {
		$customerInfo = R::Model('ibuy.customer.get', $customerId);
	} else {
		// Action as customerId and clear
		$action = $customerId;
		unset($customerId);
	}

	switch ($action) {
		case 'create.save':
			if (post('customer')) {
				$data = (Object) post('customer');

				$stmt = 'INSERT INTO %ibuy_customer%
				(`prename`, `custname`, `custaddress`, `custzip`, `custphone`, `areacode`)
				VALUES
				(:prename, :custname, :custaddress, :custzip, :custphone, :areacode)';

				mydb::query($stmt, $data);
				$customerId = mydb()->insert_id;

				$ret .= R::Page('ibuy.customer',NULL,$customerId);
			}
			break;

		default:
			if (empty($action) && empty($customerId)) $action = 'home';
			else if (empty($action) && $customerId) $action = 'view';
			if (empty($customerInfo)) $customerInfo = $customerId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$customerId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'ibuy.customer.'.$action,
								$self,
								$customerInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= R::Page('project.'.$action, $self, $customerId);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}

	return $ret;












	$self->theme->title='รายชื่อลูกค้าทั่วไป';
	if (!user_access('administer ibuys')) return message('error','access denied');


	$stmt = 'SELECT f.`custname` , f.`custtype`, u.`uid`, u.`username` , p.`name` province
		FROM %ibuy_customer% f
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %province% p USING(`pid`)
		WHERE DATE_FORMAT(`datein`,"%Y-%m-%d")="'.date('Y-m-d').'"';

	$todays = mydb::select($stmt);

	if ($todays->_num_rows) {
		$ret .= '<p>สมาชิกสมัครใหม่ของวันนี้ : ';
		foreach ($todays->items as $rs) {
			$ret .= '<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="'.ibuy_define::custtype($rs->custtype).'">'.$rs->custname.' ('.strtoupper(substr($rs->custtype,0,1)).') - '.$rs->province.'</a> , ';
		}
		$ret = trim($ret,' , ').'</p>';
	}

	$stmt = 'SELECT
			f.`custname` , f.`custtype` , f.`latlng` , f.`uid` ,  f.`discount`  ,
			u.`uid`, u.`username`,p.`provname` province_name , f.`custaddress`,f.`custphone` ,
			t.`tpid`
		FROM %ibuy_customer% f
			LEFT JOIN %co_province% p ON p.`provid` = LEFT(f.`areacode`,2)
			LEFT JOIN %users% u ON u.`uid`=f.`uid`
			LEFT JOIN %topic% t ON t.`type`="franchise" AND t.`uid`=f.`uid`
		WHERE `custtype` NOT IN ("franchise","resaler")
		ORDER BY f.`custname` ASC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array(
		'no' => 'no',
		'ชื่อ',
		'T',
		'province' => 'จังหวัด',
		'',
		'ที่อยู่',
		'โทรศัพท์',
	);
	if (user_access('administer ibuys') && cfg('ibuy.resaler.discount')>0) {
		$tables->thead['money discount']='ส่วนลดสะสม';
	}

	foreach ($dbs->items as $rs) {
		if (in_array($rs->uid,array(1))) continue;
		unset($rows);
		$rows[] = ++$no;
		$rows[] = '<a href="'.url('ibuy/franchise/'.$rs->uid).'" title="'.ibuy_define::custtype($rs->custtype).'">'.SG\getFirst($rs->custname,'ไม่ระบุ').'</a>';
		$rows[] = $rs->custtype;
		$rows[] = $rs->province_name;
		$rows[] = ($rs->latlng?'<img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|EEEEEE|FFFFFF" height="24" alt="Map" title="บันทึกแผนที่แล้ว"/>':'');
		$rows[] = $rs->custaddress;

		$rows[] = user_access('access user profiles') ? $rs->custphone : '**';
		if (user_access('administer ibuys') && cfg('ibuy.resaler.discount')>0) {
			$rows[] = $rs->discount>0?number_format($rs->discount,2):'-';
		}
		$rows['config'] = array('class'=>$rs->custtype);
		$tables->rows[] = $rows;
	}

	$ret .= $tables->build();
	
	return $ret;
}
?>