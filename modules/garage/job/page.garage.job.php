<?php
/**
* Garage Job Controller
*
* @param Object $self
* @param Int $jobId
* @param String $action
* @param Int $tranId
* @return String
*/

$debug = true;

function garage_job($self, $jobId = NULL, $action = NULL, $tranId = NULL) {
	new Toolbar($self,'สั่งซ่อม','job');

	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;
	//$ret.=print_o($shopInfo,'$shopInfo');

	$post = (object) post();
	if ($post->newjob && $post->jobno && $post->plate && $post->brandid && $post->customerid) {
		//$ret.='Create new job';
		$jobId = R::Model('garage.job.create', $shopInfo->shopid, $post);
		if ($jobId) location('garage/job/'.$jobId);
	}

	if ($jobId && $jobId != '*') {
		$jobInfo = R::Model('garage.job.get', $shopInfo, $jobId);
		$jobId = $jobInfo->tpid;
		if (empty($jobId)) return message('error', 'ไม่มีข้อมูลใบสั่งซ่อม');
		//$jobInfo->shopInfo = $shopInfo;
	}

	switch ($action) {

		case 'invoice.create':
			$post = (Object) post();
			if ($tranId && array_key_exists($tranId, $jobInfo->qt)) {
				$post->date = date('Y-m-d');
				$post->insurerid = $jobInfo->insurerid;
				$post->qtid[] = $tranId;
			}
			if ($post->date && $post->insurerid && $post->qtid) {
				$invoiceId = R::Model('garage.invoice.create',$shopInfo, $post, '{debug:false}');
				if ($invoiceId) location('garage/invoice/'.$invoiceId);
			}
			//$ret .= 'Invoice Id = '.$invoiceId.'<br />'.print_o($post,'$post');
			break;

		case 'invoice.save':
			if ($tranId && post('data')) {
				$data = (Object) post('data');

				$data->invoiceid = $tranId;
				$data->docdate = sg_date($data->docdate,'Y-m-d');
				$data->vatrate = sg_strip_money($data->vatrate);
				if (empty($data->showno)) $data->showno = 0;
				if (empty($data->showsingle)) $data->showsingle = 0;
				if (empty($data->showinsuno)) $data->showinsuno = 0;

				$stmt = 'UPDATE %garage_invoice% SET
					  `docdate` = :docdate
					, `custname` = :custname
					, `address` = :address
					, `phone` = :phone
					, `taxid` = :taxid
					, `branch` = :branch
					, `vatrate` = :vatrate
					, `showno` = :showno
					, `showsingle` = :showsingle
					, `showinsuno` = :showinsuno
					, `remark` = :remark
					WHERE `invoiceid` = :invoiceid
					LIMIT 1
					';

				mydb::query($stmt, $data);

				//debugMsg(mydb()->_query);
				//debugMsg($data,$data);
				return $ret;
			}
			break;

		case 'invoice.qt.remove':
			// $tranId is qtid
			if ($tranId && SG\confirm()) {
				$stmt = 'UPDATE %garage_qt% SET `invoiceid` = NULL WHERE `tpid` = :tpid AND `qtid` = :qtid LIMIT 1';
				mydb::query($stmt, ':tpid', $jobId, ':qtid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'invoice.qt.add':
			// $tranId is invoiceid
			if ($qtId = post('qt')) {
				$stmt = 'UPDATE %garage_qt% SET `invoiceid` = :invoiceid WHERE `qtid` = :qtid LIMIT 1';
				mydb::query($stmt, ':invoiceid', $tranId, ':qtid', $qtId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'invoice.cancel':
			if ($tranId && SG\confirm()) {
				$stmt = 'UPDATE %garage_invoice% SET `docstatus` = :status WHERE `invoiceid` = :invoiceid LIMIT 1';
				mydb::query($stmt, ':invoiceid',$tranId, ':status',_CANCEL);
				//$ret .= mydb()->_query;
			}
			break;

		case 'invoice.recall':
			if ($tranId && SG\confirm()) {
				$stmt = 'UPDATE %garage_invoice% SET `docstatus` = :status WHERE `invoiceid` = :invoiceid LIMIT 1';
				mydb::query($stmt, ':invoiceid',$tranId, ':status',1);
				//$ret .= mydb()->_query;
			}
			break;

		case 'in.create':
			$post = (Object) post();
			$post->jobno = R::Model('garage.nextno',$post->shop,'job')->nextNo;
			$post->templateid = post('type');
			$post->cartype = post('type');
			$post->rcvby = i()->uid;
			$jobId = R::Model('garage.job.create', $post->shop, $post, '{debug: false}');
			if ($jobId) location('garage/job/'.$jobId.'/in');
			//$ret .= print_o($post,'$post');
			break;

		case 'in.save':
			if (post('plate')) {
				$stmt = 'UPDATE %garage_job% SET `plate` = :plate WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid', $jobId, ':plate', post('plate'));
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'in.save.bigdb':
			//$ret = array();
			//$ret['value'] = post();
			//$ret['debug'] = $tranId.print_o(post(),'post()');

			$data = new stdClass();
			$data->bigid = $tranId;

			$stmt = 'SELECT * FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1';
			$bigData = mydb::select($stmt, ':bigid', $tranId);
			$fldData = json_decode($bigData->flddata);
			foreach ($fldData->items as $key => $value) $code[$key] = $value;
			/*
			$data->flddata = json_encode(
				array(
					'cartype' => post('cartype'),
					'items' => $code,
				)
			);
			*/
			$fldData->items = $code;
			$fldData->items[post('trid')] = array('repairid' => post('repairid'), 'damage' => post('value'));

			$data->flddata = json_encode($fldData);

			$stmt = 'UPDATE %bigdata% SET `flddata` = :flddata WHERE `bigid` = :bigid LIMIT 1';

			mydb::query($stmt, $data);
			$ret .= mydb()->_query;
			$ret .= print_o($data, '$data');

			break;

		default:
			if (empty($action) && empty($jobId)) $action = 'home';
			if (empty($action) && $jobId) $action = 'view';

			if (empty($jobInfo)) $jobInfo = $jobId;

			$argIndex = 3; // Start argument

			//debugMsg('PAGE GARAGE JOB = '.$jobId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'garage.job.'.$action,
				$self,
				$jobInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	return $ret;
}
?>