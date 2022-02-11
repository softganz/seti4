<?php
/**
* Green My Page Model
* Created 2019-11-04
* Modify  2019-11-04
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_customer_info($self, $customerInfo, $action = NULL, $tranId = NULL) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	$ret = '';

	switch ($action) {
		case 'save':
			if (post('customer')) {
				$data = (Object) post('customer');
				$data->custid = SG\getFirst($customerId);

				$stmt = 'INSERT INTO %ibuy_customer%
				(`custid`, `prename`, `custname`, `custaddress`, `custzip`, `custphone`, `areacode`)
				VALUES
				(:custid, :prename, :custname, :custaddress, :custzip, :custphone, :areacode)
				ON DUPLICATE KEY UPDATE
				`prename` = :prename
				, `custname` = :custname
				, `custaddress` = :custaddress
				, `custzip` = :custzip
				, `custphone` = :custphone
				, `areacode` = :areacode
				';

				mydb::query($stmt, $data);

				//$ret .= mydb()->_query;
			}
			break;

		case 'map.save':
			$location = post('location');
			if ($location) {
				if (strpos($location, '°')) {
					list($lat,$lng) = explode('/', $location);
					if (load_lib('func.external.php','lib')) $location = convertDMSToDecimal($lat).','.convertDMSToDecimal($lng);
				}
				$location = 'func.POINT( '.$location.' )';
			} else {
				$location = NULL;
			}

			$stmt = 'UPDATE %ibuy_customer% SET `location` = :location WHERE `custid` = :customerId LIMIT 1';
			mydb::query($stmt, ':customerId', $customerId, ':location', $location);
			//$ret .= mydb()->_query;
			break;

		case 'ticket.save':
			$post = (Object) post('ticket');
			if ($post->issnid) {
				$post->tickid = SG\getFirst($tranId);
				$post->custid = $customerId;
				$post->orgid = SG\getFirst($post->shopid);
				$post->uid = i()->uid;
				$post->thread = SG\getFirst($post->thread);
				$post->urgency = SG\getFirst($post->urgency);
				$post->status = SG\getFirst($post->status, 'Open');
				$post->productname = SG\getFirst($post->productname);
				$post->problem = SG\getFirst($post->problem);
				$post->created = date('U');
				$stmt = 'INSERT INTO %ticket%
				(`tickid`, `orgid`, `custid`, `issnid`, `uid`, `thread`, `urgency`, `status`, `problem`, `detail`, `created`)
				VALUES
				(:tickid, :orgid, :custid, :issnid, :uid, :thread, :urgency, :status, :problem, :detail, :created)
				ON DUPLICATE KEY UPDATE
				`problem` = :problem
				, `detail` = :detail
				';

				mydb::query($stmt, $post);
				//$ret .= mydb()->_query;
			}

			//$ret .= print_o($post,'$post');
			break;

		case 'ticket.reply':
			$post = new stdClass();
			$post->custid = $customerId;
			$post->issnid = post('issnid');
			$post->uid = i()->uid;
			$post->thread = $tranId;
			$post->detail = post('detail');
			$post->created = date('U');
			$stmt = 'INSERT INTO %ticket%
				(`custid`, `issnid`, `uid`, `thread`, `detail`, `created`)
				VALUES
				(:custid, :issnid, :uid, :thread, :detail, :created)';
			mydb::query($stmt, $post);
			//$ret .= mydb()->_query;
			//$ret .= print_o($post,'$post');
			break;

		case 'ticket.status':
			$getStatus = post('status');
			$closeDate = $getStatus == 'Complete' ? date('U') : 'func.`closedate`';
			$stmt = 'UPDATE %ticket% SET `status` = :status, `closedate` = :closedate  WHERE `tickid` = :tickid AND `custid` = :custid LIMIT 1';
			mydb::query($stmt, ':custid', $customerId, ':tickid', $tranId, ':status', $getStatus, ':closedate', $closeDate);
			//$ret .= mydb()->_query;
			break;

		case 'photo.upload':
			$post = (Object) post();
			$data->prename = 'ticket_'.$customerId.($post->tagname ? '_'.$post->tagname : '').'_';
			$data->tagname = 'ticket'.($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			//$data->orgid = $shopId;
			$data->refid = $tranId;
			$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'ibuy/customer/'.$customerId.'/info/photo.delete/';
			$data->link = $post->link;
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

			if($uploadResult->error) {
				$ret = implode(' ', $uploadResult->error);
			} else {
				$ret = $uploadResult->link;
			}

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			break;

		case 'photo.delete':
			if ($tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'ticket.remove':
			if ($tranId && SG\confirm()) {
				$stmt = 'SELECT `fid`, `refid`, `file` FROM %topic_files% WHERE `tagname` = "ticket" AND (`refid` = :refid OR `refid` IN (SELECT `tickid` FROM %ticket% WHERE `thread` = :refid) )';
				$dbs = mydb::select($stmt, ':refid', $tranId);
				foreach ($dbs->items as $rs) {
					R::Model('photo.delete', $rs->fid);
				}

				$stmt = 'DELETE FROM %ticket% WHERE `custid` = :custid AND (`tickid` = :tickid OR `thread` = :tickid)';
				mydb::query($stmt, ':custid', $customerId, ':tickid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'ticket.thread.remove':
			if ($tranId && SG\confirm()) {
				// Remove photo
				$stmt = 'SELECT `fid`,`file` FROM %topic_files% WHERE `tagname` = "ticket" AND `refid` = :refid';
				$dbs = mydb::select($stmt, ':refid', $tranId);
				foreach ($dbs->items as $rs) {
					R::Model('photo.delete', $rs->fid);
				}

				// Remove ticket
				$stmt = 'DELETE FROM %ticket% WHERE `tickid` = :tickid AND `custid` = :custid LIMIT 1';
				mydb::query($stmt, ':tickid', $tranId, ':custid', $customerId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'serial.save':
			$data = (Object) post('serial');
			if ($data->productname) {
				$data->issnid = $tranId;
				$data->tpid = SG\getFirst($data->tpid);
				$data->custid = $customerId;
				$stmt = 'INSERT INTO %ibuy_serial%
					(`issnid`, `tpid`, `custid`, `serial`, `machineno`, `modelinfo`, `saledate`, `registerdate`, `warrentydate1`, `maintfee`)
					VALUES
					(:issnid, :tpid, :custid, :serial, :machineno, :modelinfo, :saledate, :registerdate, :warrentydate1, :maintfee)
					ON DUPLICATE KEY UPDATE
					`tpid` = :tpid
					, `serial` = :serial
					, `machineno` = :machineno
					, `modelinfo` = :modelinfo
					, `saledate` = :saledate
					, `registerdate` = :registerdate
					, `warrentydate1` = :warrentydate1
					, `maintfee` = :maintfee
					';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}

			//$ret .= print_o(post(),'post()');
			break;

			case 'serial.remove':
				if ($tranId) {
					$stmt = 'DELETE FROM %ticket% WHERE `custid` = :custid AND `issnid` = :issnid';
					mydb::query($stmt, ':custid', $customerId, ':issnid', $tranId);

					$stmt = 'DELETE FROM %ibuy_serial% WHERE `issnid` = :issnid LIMIT 1';
					mydb::query($stmt, ':issnid', $tranId);
					//$ret .= mydb()->_query;
				}
				break;

		default:
			$ret = 'ERROR!!! No Action';
			break;
	}

	return $ret;
}
?>