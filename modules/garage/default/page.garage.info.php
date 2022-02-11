<?php
/**
* Garage :: Shop Model
* Created 2020-06-16
* Modify  2020-06-16
*
* @param Object $self
* @param Int $id
* @param String $action
* @param Int $tranId
* @return String
*/

$debug = true;

function garage_info($self, $id, $action = NULL, $tranId = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	list($module) = explode('.', $action);

	if ($module == 'recieve' && !R::Model('garage.right',$shopInfo,'finance')) return 'ERROR: RECIEVE ACCESS DENIED';

	$ret = '';

	//debugMsg('$id = '.$id.' $action = '.$action);

	switch ($action) {
		case 'recieve.new':
			// Create New Recieve
			$post = (Object) post();
			if ($post->newbill && $post->rcvdate && $post->insurerid) {
				$rcvId = R::Model('garage.recieve.create',$shopInfo,$post,'{debug:false}');
				//$ret .= '$revId = '.$rcvId.print_o($post,'$post');
				if ($rcvId) location('garage/recieve/'.$rcvId);
			}
			//$ret .= print_o($post,'$post');
			break;

		case 'recieve.save':
			$data = (object)post('data');

			$rcvInfo = R::Model('garage.recieve.get',$shopInfo->shopid,$id,'{debug:false}');

			if ($rcvInfo->shopid != $shopInfo->shopid) return 'SHOP ERROR';

			//__garage_recieve_edit_save($shopInfo,$rcvInfo,$data);

			$data->rcvdate = sg_date($data->rcvdate,'Y-m-d');
			$data->vatrate = sg_strip_money($data->vatrate);
			$data->subtotal = sg_strip_money($data->subtotal);
			$data->vattotal = sg_strip_money($data->vattotal);
			if (empty($data->showno)) $data->showno = 0;
			if (empty($data->showsingle)) $data->showsingle = 0;
			if (empty($data->showinsuno)) $data->showinsuno = 0;

			$data->total = $data->subtotal + $data->vattotal;

			$stmt = 'UPDATE %garage_rcv% SET
				  `rcvdate` = :rcvdate
				, `rcvcustname` = :rcvcustname
				, `rcvaddr` = :rcvaddr
				, `rcvphone` = :rcvphone
				, `rcvtaxid` = :rcvtaxid
				, `rcvbranch` = :rcvbranch
				, `vatrate` = :vatrate
				, `showno` = :showno
				, `showsingle` = :showsingle
				, `showinsuno` = :showinsuno
				, `rcvremark` = :rcvremark
				, `subtotal` = :subtotal
				, `vattotal` = :vattotal
				, `total` = :total
				WHERE `rcvid` = :rcvid
				LIMIT 1
				';

			mydb::query($stmt,':rcvid',$rcvInfo->rcvid,$data);
			//debugMsg(mydb()->_query);
			//debugMsg($data,$data);
			//debugMsg($rcvInfo,'$rcvInfo');
			//location('garage/recieve/'.$rcvId);
			break;

		case 'recieve.addqt':
			$rcvId = $id;
			if ($rcvId && $tranId) {
				$jobId = mydb::select('SELECT `tpid` FROM %garage_qt% WHERE `qtid` = :qtid LIMIT 1',':qtid',$tranId)->tpid;
				$ret .= 'JobId = '.$jobId.'<br />';

				$stmt = 'UPDATE %garage_qt% SET `rcvid` = :rcvid WHERE `qtid` = :qtid LIMIT 1';
				mydb::query($stmt,':rcvid', $rcvId, ':qtid', $tranId);
				//$ret .= mydb()->_query.'<br />';

				$stmt = 'SELECT q.`qtid` `pqtid`, a.*
					FROM %garage_qt% q
						LEFT JOIN %garage_qt% a USING(`tpid`)
					WHERE q.`qtid` = :qtid
					HAVING `rcvid` IS NULL';

				$dbs = mydb::select($stmt,':qtid',$tranId);

				// Update Job Money Recieved, Some QT not recieve = No, All QT recieve = Yes
				$stmt = 'UPDATE %garage_job% SET `isrecieved` = :isrecieved WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt,':tpid',$jobId, ':isrecieved', $dbs->count() ? 'No' : 'Yes');
				//$ret .= mydb()->_query.'<br />';

				R::Model('garage.recieve.vat.update', $rcvId, $tranId);
				//$ret .= mydb()->_query.'<br />';

				$ret .= 'Add QT '.$tranId.' completed.';
				//$ret.=mydb()->_query.'<br />';
				//$ret.=print_o($dbs,'$dbs');
			}
			break;

		case 'recieve.cancel':
			$rcvId = $id;
			$ret .= 'Cancel';
			$stmt = 'UPDATE %garage_rcv% SET `rcvstatus` = :cancel WHERE `rcvid` = :rcvid LIMIT 1';
			mydb::query($stmt,':rcvid',$rcvId, ':cancel',_CANCEL);
			break;

		case 'recieve.delqt':
			$rcvId = $id;
			if ($tranId && SG\confirm()) {
				$stmt = 'UPDATE %garage_qt% SET `rcvid` = NULL, `rcvprice` = NULL, `rcvvat` = NULL WHERE `qtid` = :qtid AND `rcvid` = :rcvid LIMIT 1';
				mydb::query($stmt,':qtid',$tranId, ':rcvid',$rcvId);
				//$ret.=mydb()->_query.'<br />';

				R::Model('garage.recieve.vat.update', $rcvId, $tranId);

				$stmt = 'UPDATE %garage_qt% q LEFT JOIN %garage_job% j USING(`tpid`) SET j.`isrecieved` = "No" WHERE q.`qtid` = :qtid';
				mydb::query($stmt,':qtid',$tranId);
				//$ret.=mydb()->_query.'<br />';
			}
			break;

		case 'recieve.tran.save':
			$rcvId = $id;
			if ($rcvId && $tranId) {
				$price = sg_strip_money(post('price'));
				$vat = sg_strip_money(post('vat'));
				$stmt = 'UPDATE %garage_qt% SET `rcvprice` = :rcvprice, `rcvvat` = :rcvvat WHERE `qtid` = :qtid LIMIT 1';
				mydb::query($stmt, ':qtid', $tranId, ':rcvprice', $price, ':rcvvat', $vat);
				//$ret .= mydb()->_query;

				R::Model('garage.recieve.vat.update', $rcvId, 'SUM');
			}
			break;



		case 'in.code.save':
			$carTypeId = $id;
			$shopId = SG\getFirst($shopInfo->shopparent, $shopInfo->shopid);
			if ($carTypeId && $tranId && $repairId = post('repairid')) {
				$stmt = 'INSERT INTO %garage_carpos%
					(`shopid`, `cartypeid`, `position`, `repairid`)
					VALUES
					(:shopid, :cartypeid, :position, :repairid)
					ON DUPLICATE KEY UPDATE `repairid` = :repairid';
				mydb::query($stmt, ':shopid', $shopId, ':cartypeid', $carTypeId, ':position', $tranId, ':repairid', $repairId);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'in.code.remove':
			$carTypeId = $id;
			$shopId = SG\getFirst($shopInfo->shopparent, $shopInfo->shopid);
			if ($carTypeId && $tranId && $repairId = post('repairid')) {
				$stmt = 'DELETE FROM %garage_carpos% WHERE `shopid` = :shopId AND `cartypeid` = :carTypeId AND `position` = :position AND `repairid` = :repairid LIMIT 1';
				mydb::query($stmt, ':shopId', $shopId, ':carTypeId', $carTypeId, ':position', $tranId, ':repairid', $repairId);
				//$ret .= mydb()->_query;
			}
			break;



		case 'aprcv.create':
			$data = (Object) post();
			if ($data->rcvdate && $data->apid) {
				$rcvId = R::Model('garage.aprcv.create',$shopInfo,$data,'{debug:false}');
				//$ret.='$rcvId='.$rcvId;
				if ($rcvId) location('garage/aprcv/'.$rcvId.'/view');
				else $ret .= 'Error on create';
			}
			break;

		case 'aprcv.tran.save' :
			if (post('stkid') || post('repairname')) {
				$tranId = R::Model('garage.aprcv.tr.save', $id, (Object) post(), '{debug: false}');
			}
			break;

		case 'aprcv.tran.remove':
			if (($rcvId = $id) && $tranId && SG\confirm()) {
				$ret = 'DELETED';
				$rcvInfo = R::Model('garage.aprcv.get', $rcvId);
				$stockId = $rcvInfo->items[$tranId]->stkid;
				$stmt = 'DELETE FROM %garage_stocktran% WHERE `stktrid` = :stktrid LIMIT 1';
				mydb::query($stmt,':stktrid',$tranId);
				//$ret .= mydb()->_query;
				R::Model('garage.apmast.calculate',$rcvInfo);
				if ($stockId) {
					$result .= R::Model('garage.stock.cost.calculate',$rcvInfo->items[$tranId]->stkid);
				}
			}
			break;

		case 'aprcv.repair':
			$ret .= R::Model('garage.apmast.calculate',$rcvInfo,'{debug:false}');
			break;



		case 'req.create' :
			$data = (Object) post();
			if ($data->docdate && $data->jobid) {
				$reqId = R::Model('garage.req.create',$shopInfo,$data,'{debug:false}');
				if ($reqId) location('garage/req/'.$reqId.'/view');
			}
			//$ret.=print_o($data,'$data');
			break;

		case 'req.tran.save':
			$trid = R::Model('garage.req.tr.save', $id, (Object) post());
			break;

		case 'req.tran.remove':
			if ($id && $tranId && SG\confirm()) {
				$reqInfo = R::Model('garage.req.get', $reqId);

				$stmt = 'DELETE FROM %garage_stocktran% WHERE `stktrid` = :stktrid LIMIT 1';

				mydb::query($stmt, ':stktrid', $tranId);

				R::Model('garage.req.calculate', $reqInfo);
				$result = R::Model('garage.stock.cost.calculate', $reqInfo->items[$trid]->stkid);
			}
			break;

		case 'req.repair':
			$ret.=R::Model('garage.stock.cost.calculate',$tranId);
			break;



		case 'order.new':
			$data = (Object) post();
			if ($data->docdate && $data->apid) {
				$orderId = R::Model('garage.order.create',$shopInfo,$data,'{debug:false}');
				if ($orderId) location('garage/order/'.$orderId);
			}
			//$ret.=print_o($data,'$data');
			break;

		case 'order.tran.save' :
			if (post('stkid')) {
				$trid = R::Model('garage.order.tr.save', $id, (Object) post(), '{debug: false}');
			}
			break;

		case 'order.tran.remove':
			if (($orderId = $id) && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %garage_ordtran% WHERE `ordtrid` = :trid AND `ordid` = :ordid LIMIT 1';
				mydb::query($stmt, ':trid', $tranId, ':ordid', $orderId);
				$ret = 'ลบรายการเรียบร้อย';
				//$ret .= mydb()->_query;

				R::Model('garage.order.calculate',$orderId);
			}
			break;



		case 'appaid.new':
			$data = (Object) post();
			if ($data->paiddate && $data->apid) {
				$paidId = R::Model('garage.appaid.create', $shopInfo, $data, '{debug:false}');
				//$ret.='$paidId='.$paidId;
				if ($paidId) location('garage/appaid/'.$paidId);
				else $ret .= '<div class="notify">Error on create.</div>';
			}
			//debugMsg($data,'$data');
			break;

		case 'appaid.tran.save' :
			//$ret.=print_o(post(),'post()');
			if (($paidId = $id) && $tranId) {
				$stmt = 'UPDATE %garage_apmast% SET `paidid` = :paidid WHERE `rcvid` = :rcvid LIMIT 1';
				mydb::query($stmt, ':paidid', $paidId, ':rcvid', $tranId);
				$ret.=mydb()->_query.'<br />';

				$total = mydb::select('SELECT SUM(`grandtotal`) `total` FROM %garage_apmast% WHERE `paidid` = :paidid LIMIT 1',':paidid', $paidId)->total;

				$stmt='UPDATE %garage_appaid% SET `grandtotal` = :total WHERE `paidid` = :paidid LIMIT 1';
				mydb::query($stmt, ':paidid', $paidId, ':total', $total);
				$ret.=mydb()->_query.'<br />';
			}

			return $ret;
			//$ret.=print_o(post(),'post()');
			break;

		case 'appaid.tran.remove':
			if (($paidId = $id) && $tranId && SG\confirm()) {
				$stmt = 'UPDATE %garage_apmast% SET `paidid`=NULL WHERE `rcvid` = :rcvid AND `paidid` = :paidid LIMIT 1';
				mydb::query($stmt,':rcvid',$tranId, ':paidid',$paidId);
				$ret.=mydb()->_query.'<br />';

				$total=mydb::select('SELECT SUM(`grandtotal`) `total` FROM %garage_apmast% WHERE `paidid`=:paidid LIMIT 1',':paidid',$paidId)->total;

				$stmt='UPDATE %garage_appaid% SET `grandtotal`=:total WHERE `paidid`=:paidid LIMIT 1';
				mydb::query($stmt,':paidid',$paidId,':total',$total);
				$ret.=mydb()->_query.'<br />';
			}
			break;

		default:
			$ret = 'NO ACTION';
			break;
	}

	//$ret .= print_o($shopInfo, '$shopInfo');

	return $ret;
}
?>