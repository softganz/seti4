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

function ibuy_my_info($self, $action = NULL, $tranId = NULL) {
	$ret = '';

	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : NULL;

	//if (!$shopId) return 'ERROR : No Shop';

	switch ($action) {
		
		case 'activity.save':
			$ret .= 'Activity Saved';
			$data = (Object) post();
			if ($data->message || $data->productname || $_FILES) {
				$data->message = SG\getFirst($data->message, $data->productname);
				if ($_FILES['photo']) $data->uploadFiles = $_FILES['photo'];

				$result = R::Model('ibuy.activity.save', $data);

				//$ret .= print_o($result, '$result');
			}
			//$ret .= print_o(post(),'post()');
			//$ret .= print_o($_FILES,'$_FILES');
			break;

		case 'activity.delete':
			$ret = 'Activity Deleted.';
			if ($tranId && SG\confirm()) {
				$msgInfo = mydb::select('SELECT * FROM %msg% WHERE `msgid` = :msgid LIMIT 1', ':msgid', $tranId);

				$stmt = 'DELETE FROM %msg% WHERE `msgid` = :msgid LIMIT 1';
				mydb::query($stmt, ':msgid', $tranId);

				$stmt = 'SELECT `fid` FROM %topic_files% WHERE `refid` = :refid AND `tagname` = "ibuy,activity"';
				foreach (mydb::query($stmt, ':refid', $tranId)->items as $rs) {
					R::Model('photo.delete',$rs->fid);
				}

				$stmt = 'DELETE FROM %msg% WHERE `thread` = :thread';
				mydb::query($stmt, ':thread', $tranId);

				$stmt = 'DELETE FROM %reaction% WHERE `refid` = :refid AND `action` = "MSG.LIKE"';
				mydb::query($stmt, ':refid', $tranId);

				// Delete Plant
				if ($msgInfo->plantid) {
					$stmt = 'DELETE FROM %ibuy_farmplant% WHERE `plantid` = :plantid LIMIT 1';
					mydb::query($stmt, ':plantid', $msgInfo->plantid);

					$stmt = 'DELETE FROM %bigdata% WHERE `keyname` = "ibuy.tree" AND `keyid` = :plantid';
					mydb::query($stmt, ':plantid', $msgInfo->plantid);
				}

				R::On('ibuy.activity.delete', $tranId);
			}
			break;

		case 'activity.photo.upload':
			$post = (Object) post();
			$data->prename = 'ibuy'.($post->tagname ? '_'.$post->tagname : '').'_'.$tranId.'_';
			$data->tagname = 'ibuy'.($post->tagname ? ','.$post->tagname : '');
			$data->refid = $tranId;
			$data->deleteurl = $post->delete == 'none' ? NULL : 'ibuy/my/info/activity.photo.delete/';
			$data->link = $post->link;
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

			if($uploadResult->error) {
				$ret = implode(' ', $uploadResult->error);
			} else {
				$ret = $uploadResult->link;

				R::On('ibuy.activity.update', $tranId);
			}

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			break;

		case 'activity.photo.delete':
			if ($tranId && ($fileId = post('fid')) && SG\confirm()) {
				$result = R::Model('photo.delete',$fileId);
				$ret .= 'Photo Deleted!!!';

				R::On('ibuy.activity.update', $tranId);
			}
			break;

		case 'activity.comment.save':
			$data = (Object) post();
			$data->message = trim($data->message);
			if ($data->message) {
				$ret .= 'Comment Saved';
				$data->msgid = SG\getFirst($data->msgid);
				$data->thread = $tranId;
				$data->tagname = 'GREEN,COMMENT';
				$data->uid = i()->uid;
				$data->replyto = SG\getFirst($data->replyto);
				$data->privacy = 'public';
				$data->created = date('U');
				$stmt = 'INSERT INTO %msg%
					(`msgid`, `thread`, `tagname`, `uid`, `replyto`, `privacy`, `message`, `created`)
					VALUES
					(:msgid, :thread, :tagname, :uid, :replyto, :privacy, :message, :created)
					ON DUPLICATE KEY UPDATE
					`message` = :message';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			break;

		case 'activity.comment.delete':
			if ($tranId && SG\confirm()) {
				mydb::where('`msgid` = :msgid', ':msgid', $tranId);
				if (!is_admin('ibuy')) mydb::where('`uid` = :uid', ':uid', i()->uid);
				$stmt = 'DELETE FROM %msg% %WHERE% LIMIT 1';
				mydb::query($stmt);
				//$ret .= mydb()->_query;
			}
			break;

		case 'msg.like':
			$result = R::Model('reaction.add', $tranId, 'MSG.LIKE', '{addType: "toggle", count: "msg:liketimes"}');
			$ret = NULL;
			$ret->liked = $result;
			$ret->liketimes = mydb::select('SELECT `liketimes` FROM %msg% WHERE `msgid` = :msgid LIMIT 1', ':msgid', $tranId)->liketimes;
			break;

		case 'land.save':
			if (!$shopId) break;
			//$ret = 'Save Land In
			$data = (Object) post('data');
			$data->landid = SG\getFirst($data->landid);
			$data->orgid = $shopId;
			$data->uid = i()->uid;
			if ($data->location) {
				if (strpos($data->location, '°')) {
					list($lat,$lng) = explode('/', $data->location);
					if (load_lib('func.external.php','lib')) $data->location = convertDMSToDecimal($lat).','.convertDMSToDecimal($lng);
				}
				$data->location = 'func.POINT( '.$data->location.' )';
			} else {
				$data->location = NULL;
			}
			if ($data->landname) {
				$stmt = 'INSERT INTO %ibuy_farmland%
					(`landid`, `orgid`, `uid`, `landname`, `arearai`, `areahan`, `areawa`, `producttype`, `detail`)
					VALUES
					(:landid, :orgid, :uid, :landname, :arearai, :areahan, :areawa, :producttype, :detail)
					ON DUPLICATE KEY UPDATE
					  `landname` = :landname
					, `arearai` = :arearai
					, `areahan` = :areahan
					, `areawa` = :areawa
					, `producttype` = :producttype
					, `detail` = :detail
					';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				if (empty($data->landid)) $data->landid = mydb()->insert_id;
			}

			if ($data->landid) {
				$stmt = 'UPDATE %ibuy_farmland% SET `location` = :location WHERE `landid` = :landid LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}

			//$ret .= print_o($data,'$data');
			//$ret .= print_o(post(),'post()');
			break;

		case 'land.remove':
			if (!$shopId) break;
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':landid', $tranId, ':orgid', $shopId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'plant.save':
			if (!$shopId) break;
			$data = (Object) post('data');
			$data->plantid = $tranId;
			$data->orgid = $shopId;
			$data->landid = SG\getFirst(post('land'), $data->landid);
			$data->tagname = SG\getFirst($data->tagname);

			$landInfo = mydb::select('SELECT `standard`, `approved` FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $data->landid);

			$data->standard = $landInfo->standard;
			$data->approved = $landInfo->approved;
			$data->startdate = sg_date($data->startdate, 'Y-m-d');
			$data->cropdate = sg_date($data->cropdate, 'Y-m-d');
			$data->safety = SG\getFirst($data->safety);
			$data->qty = sg_strip_money($data->qty);
			$data->saleprice = sg_strip_money($data->saleprice);
			$data->bookprice = sg_strip_money($data->bookprice);
			$data->uid = i()->uid;
			$data->created = date('U');

			if ($data->saleprice == 0) $data->saleprice = NULL;
			if ($data->bookprice == 0) $data->bookprice = NULL;

			if ($data->productname && $data->landid) {
				$stmt = 'INSERT INTO %ibuy_farmplant%
					(
					`plantid`, `orgid`, `uid`, `landid`, `standard`, `approved`
					, `tagname`
					, `catid`, `productname`
					, `startdate`, `cropdate`, `qty`, `unit`
					, `saleprice`, `bookprice`
					, `safety`, `detail`
					, `created`
					)
					VALUES
					(
					:plantid, :orgid, :uid, :landid, :standard, :approved
					, :tagname, :catid, :productname
					, :startdate, :cropdate, :qty, :unit
					, :saleprice, :bookprice
					, :safety, :detail
					, :created
					)
					ON DUPLICATE KEY UPDATE
					  `catid` = :catid
					, `productname` = :productname
					, `startdate` = :startdate
					, `cropdate` = :cropdate
					, `qty` = :qty
					, `unit` = :unit
					, `saleprice` = :saleprice
					, `bookprice` = :bookprice
					, `safety` = :safety
					, `detail` = :detail
					, `created` = :created
					';

				mydb::query($stmt, $data);
				$ret .= mydb()->_query;
			}
			$ret .= print_o(post(),'post()');
			break;

		case 'plant.remove':
			if (!$shopId) break;
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %ibuy_farmplant% WHERE `plantid` = :plantid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':plantid', $tranId, ':orgid', $shopId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'photo.upload':
			if (!$shopId) break;
			$post = (Object) post();
			$data->tpid = $tpid;
			$data->prename = strtolower(SG\getFirst($post->module,'ibuy_shop').'_'.$shopId.($post->tagname ? '_'.$post->tagname : '').'_');
			$data->tagname = SG\getFirst($post->module,'IBUY').($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			$data->orgid = $shopId;
			$data->refid = $tranId;
			$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'ibuy/my/info/photo.delete/';
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
			if (!$shopId) break;
			if ($tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'book.save':
			if (i()->ok && $tranId) {
				$data = new stdClass();
				$data->bookid = NULL;
				$data->plantid = $tranId;
				$data->uid = i()->uid;
				$data->qty = post('qty');
				$data->bookprice = mydb::select('SELECT `bookprice` FROM %ibuy_farmplant% WHERE `plantid` = :plantid LIMIT 1', ':plantid', $tranId)->bookprice;
				$data->detail = post('detail');
				$data->created = date('U');
				$stmt = 'INSERT INTO %ibuy_farmbook%
					(`bookid`, `plantid`, `uid`, `qty`, `bookprice`, `detail`, `created`)
					VALUES
					(:bookid, :plantid, :uid, :qty, :bookprice, :detail, :created)';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'book.remove':
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %ibuy_farmbook% WHERE `bookid` = :bookid LIMIT 1';
				mydb::query($stmt, ':bookid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'product.save':
			if (!$shopId) break;
			$data = (Object) post('data');
			$data->shopid = $shopId;
			$result = R::Model('ibuy.product.create', $data, '{debug: false}');
			//$ret .= print_o($result, '$result');
			//$ret .= print_o($data, '$data');
			break;

		case 'location.save':
			$data = (Object) post();
			$data->bigid = SG\getFirst($data->bigid);
			$data->keyname = 'ibuy.location';
			$data->keyid = i()->uid;
			$data->fldname = 'location';
			$data->fldref = SG\getFirst($data->orgid);
			$data->fldtype = 'json';
			$value = (Object) Array('locname' => $data->locname, 'location' => $data->location);
			$data->flddata = sg_json_encode($value);
			$data->created = date('U');
			$data->ucreated = i()->uid;

			$stmt = 'INSERT INTO %bigdata%
				(`bigid`, `keyname`, `keyid`, `fldname`, `fldref`, `fldtype`, `flddata`, `created`, `ucreated`)
				VALUES
				(:bigid, :keyname, :keyid, :fldname, :fldref, :fldtype, :flddata, :created, :ucreated)';
			mydb::query($stmt, $data);

			//$ret .= mydb()->_query;
			//$ret .= print_o($data, '$data');

			$result = new stdClass();
			$result->locid = mydb()->insert_id;
			$result->locname = $data->locname;
			$result->location = $data->location;

			$ret = $data->result == 'json' ? $result : $ret;
			break;

		case 'tree.round.save':
			if (!$tranId) break;
			$data = (Object) post();
			$data->bigid = SG\getFirst($data->bigid);
			$data->keyname = 'GREEN,TREE';
			$data->keyid = $data->plantid = $tranId;
			$data->fldname = 'round';
			$data->round = SG\getFirst($data->round);
			$data->height = SG\getFirst($data->height);
			$data->flddata = json_encode(array('round' => $data->round, 'height' => $data->height));
			$data->created = date('U');
			$data->ucreated = i()->uid;

			$stmt = 'INSERT INTO %bigdata%
				(`bigid`, `keyname`, `keyid`, `fldname`, `flddata`, `created`, `ucreated`)
				VALUES
				(:bigid, :keyname, :keyid, :fldname, :flddata, :created, :ucreated)
				ON DUPLICATE KEY UPDATE
				`flddata` = :flddata';

			mydb::query($stmt, $data);

			$stmt = 'UPDATE %ibuy_farmplant% SET `round` = :round, `height` = :height WHERE `plantid` = :plantid LIMIT 1';
			mydb::query($stmt, $data);
			break;

		case 'tree.round.remove':
			if ($tranId AND SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId);
			}
			break;

		default:
			$ret = 'ERROR!!! No Action';
			break;
	}

	return $ret;
}
?>