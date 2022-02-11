<?php
/**
* Green My Page Model
* Created 2019-11-04
* Modify  2020-12-07
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage green/my/info/{action[.subaction]}[/{id}]
*/

$debug = true;

function green_my_info($self, $action = NULL, $tranId = NULL) {
	$ret = '';

	switch ($action) {
		case 'org.create':
			if (!user_access('create own shop')) {
				return message('error','access denied:ขออภัยค่ะ ท่านยังไม่ได้รับสิทธิ์ในการเปิดหน้าร้าน');
			} else if (post('name')) {
				$stmt = 'INSERT INTO %db_org% (`uid`,`name`,`created`) VALUES (:uid,:name,:created)';

				mydb::query($stmt,':uid',i()->uid, ':name',post('name'), ':created',date('U'));

				if (!mydb()->_error) {
					$orgId = mydb()->insert_id;

					$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';

					mydb::query($stmt,':orgid',$orgId, ':uid',i()->uid, ':membership',"ADMIN");

					//$stmt = 'INSERT INTO %ibuy_shop% (`shopid`, `uid`, `created`) VALUES (:shopid, :uid, :created)';
					//mydb::query($stmt,':shopid',$orgId, ':uid',i()->uid, ':created', date('U'));

					$_SESSION['shopid'] = $orgId;
				}
				return;
			}

			break;

		case 'shop.create':
			break;

	}



	//$orgId = get_first(post(''))

	$orgId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : NULL;

	//if (!$orgId) return 'ERROR : No Shop';

	switch ($action) {

		case 'activity.save':
			$ret .= 'Activity Saved';
			$data = (Object) post();
			if ($data->message || $data->productname || $_FILES) {
				$data->message = SG\getFirst($data->message, $data->productname);
				if ($_FILES['photo']) $data->uploadFiles = $_FILES['photo'];

				$result = R::Model('green.activity.save', $data);

				//$ret .= print_o($result, '$result');
			}
			//$ret .= print_o(post(),'post()');
			//$ret .= print_o($_FILES,'$_FILES');
			break;

		case 'activity.delete':
			if ($tranId && SG\confirm()) {
				$ret = 'Activity Deleted.';
				$msgInfo = mydb::select('SELECT m.*, p.`productname` FROM %msg% m LEFT JOIN %ibuy_farmplant% p USING(`plantid`) WHERE m.`msgid` = :msgid LIMIT 1', ':msgid', $tranId);

				$stmt = 'SELECT `fid` FROM %topic_files%
					WHERE (`refid` = :refid AND `tagname` = "GREEN,ACTIVITY")
						OR (`refid` = :plantid AND `tagname` IN ("GREEN,PLANT", "GREEN,RUBBER", "GREEN,TREE", "GREEN,ANIMAL") )';
				foreach (mydb::query($stmt, ':refid', $tranId, ':plantid', $msgInfo->plantid)->items as $rs) {
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

				$stmt = 'DELETE FROM %msg% WHERE `msgid` = :msgid LIMIT 1';
				mydb::query($stmt, ':msgid', $tranId);

				R::On('ibuy.activity.delete', $tranId);

				R::model('watchdog.log',
					'Green',
					'Activity Delete',
					'Avtivity '.$tranId.' was delete by '.i()->username.' :: {msgid: "'.$tranId.'", tag: "'.$msgInfo->tagname.'", name: "'.$msgInfo->productname.'", land:"'.$msgInfo->landid.'", plant:"'.$msgInfo->plantid.'", msg:"'.$msgInfo->message.'"}',
					i()->uid,
					$tranId
				);
			}
			break;

		case 'activity.photo.upload':
			$post = (Object) post();
			$data->prename = 'ibuy'.($post->tagname ? '_'.$post->tagname : '').'_'.$tranId.'_';
			$data->tagname = 'ibuy'.($post->tagname ? ','.$post->tagname : '');
			$data->refid = $tranId;
			$data->deleteurl = $post->delete == 'none' ? NULL : 'green/my/info/activity.photo.delete/';
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
				$msgInfo = mydb::select('SELECT * FROM %msg% WHERE `msgid` = :msgid LIMIT 1', ':msgid', $tranId);
				mydb::where('`msgid` = :msgid', ':msgid', $tranId);
				if (!is_admin('ibuy')) mydb::where('`uid` = :uid', ':uid', i()->uid);
				$stmt = 'DELETE FROM %msg% %WHERE% LIMIT 1';
				mydb::query($stmt);
				//$ret .= mydb()->_query;
				R::model('watchdog.log',
					'Green',
					'Activity Comment Delete',
					'Activity Comment '.$tranId.' was delete by '.i()->username.' :: {msgid: "'.$tranId.'", thread: "'.$msgInfo->thread.'", uid: "'.$msgInfo->uid.'", msg:"'.$msgInfo->message.'"}',
					i()->uid,
					$tranId
				);
			}
			break;

		case 'msg.like':
			$result = R::Model('reaction.add', $tranId, 'MSG.LIKE', '{addType: "toggle", count: "msg:liketimes"}');
			$ret = NULL;
			$ret->liked = $result;
			$ret->liketimes = mydb::select('SELECT `liketimes` FROM %msg% WHERE `msgid` = :msgid LIMIT 1', ':msgid', $tranId)->liketimes;
			break;

		case 'shop.clear':
			unset($_SESSION['shopid']);
			//$ret .= 'Clear SESSION '.$_SESSION['shopid'];
			break;






		// Method must RIGHT
		case 'officer.add':
			if (!$orgId) break;
			if (($orgId = $tranId) && post('uid') && post('membership')) {
				$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership) ON DUPLICATE KEY UPDATE `membership` = :membership';
				mydb::query($stmt, ':orgid', $orgId, ':uid', post('uid'), ':membership', strtoupper(post('membership')));
				//$ret .= mydb()->_query;
			}
			break;

		case 'officer.remove':
			if (!$orgId) break;
			if (($orgId = $tranId) && post(`uid`) && SG\confirm()) {
				mydb::query('DELETE FROM %org_officer% WHERE `uid` = :uid AND `orgid` = :orgid LIMIT 1', ':uid', post('uid'), ':orgid', $orgId);
			}
			break;


		case 'land.save':
			if (!$orgId) break;
			//$ret = 'Save Land In
			$data = (Object) post('data');
			$data->landid = SG\getFirst($data->landid);
			$data->orgid = $orgId;
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
				$address = SG\explode_address($data->address,$data->areacode);
				$data->house = $address['house'];

				$stmt = 'INSERT INTO %ibuy_farmland%
					(`landid`, `orgid`, `uid`, `landname`, `deedno`, `house`, `areacode`, `arearai`, `areahan`, `areawa`, `producttype`, `detail`)
					VALUES
					(:landid, :orgid, :uid, :landname, :deedno, :house, :areacode, :arearai, :areahan, :areawa, :producttype, :detail)
					ON DUPLICATE KEY UPDATE
					  `landname` = :landname
					, `deedno` = :deedno
					, `house` = :house
					, `areacode` = :areacode
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
			if (!$orgId) break;
			if ($tranId && SG\confirm()) {
				$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $tranId);

				$stmt = 'DELETE FROM %ibuy_farmland% WHERE `landid` = :landid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':landid', $tranId, ':orgid', $orgId);
				//$ret .= mydb()->_query;

				R::model('watchdog.log',
					'Green',
					'Land Delete',
					'Land '.$tranId.' was delete by '.i()->username.' :: {land: "'.$tranId.'", org: "'.$landInfo->orgid.'", uid: "'.$landInfo->uid.'", name: "'.$landInfo->landname.'"}',
					i()->uid,
					$tranId
				);
			}
			break;

		case 'land.move':
			if (!$orgId) break;
			if (($landId = $tranId) && ($moveTo = post('moveto'))
				&& ($landInfo = R::Model('green.land.get', $landId, '{data: "orgInfo"}'))
				&& ($orgInfo = R::Model('green.shop.get', $moveTo))
				) {
				$ret .= 'ย้ายไป '.$orgInfo->name;

				$data = new stdClass();
				$data->landId = $landId;
				$data->orgId = $landInfo->orgId;
				$data->moveTo = $moveTo;

				// Move Plant
				$stmt = 'UPDATE %ibuy_farmplant% SET `orgid` = :moveTo WHERE `landid` = :landId';
				mydb::query($stmt, $data);
				//$ret .= '<br />'.mydb()->_query.'<br />';

				// Move File
				$stmt = 'UPDATE %topic_files% SET `orgid` = :moveTo WHERE `orgid` = :orgId AND `tagname` = "GREEN,LAND" AND `refid` = :landId';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';

				//TODO: Set OrgId of table topic_files tagname = GREEN,*

				$stmt = 'UPDATE %ibuy_farmland% SET `orgid` = :moveTo WHERE `landid` = :landId LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';
			}
			break;

		case 'land.owner':
			if (!$orgId) break;
			if (($landId = $tranId) && ($toUid = post('newuid'))) {
				$ret .= 'เปลี่ยนเจ้าของ';
				$data = new stdClass();
				$data->landId = $landId;
				$data->toUid = $toUid;
				$stmt = 'UPDATE %ibuy_farmland% SET `uid` = :toUid WHERE `landid` = :landId LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';
			}
			break;

		case 'plant.save':
			if (!$orgId) break;
			$data = (Object) post('data');
			$data->plantid = $tranId;
			$data->orgid = $orgId;
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
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'location.save':
			if (!$orgId) break;
			$data = (Object) post();

			if ($data->location) {
				if (strpos($data->location, '°')) {
					list($lat,$lng) = explode('/', $data->location);
					if (load_lib('func.external.php','lib')) $data->location = convertDMSToDecimal($lat).','.convertDMSToDecimal($lng);
				}
				$data->location = 'func.POINT( '.$data->location.' )';
			} else {
				$data->location = NULL;
			}
			if ($tranId) {
				if ($data->mapTable == 'plant') {
					$stmt = 'UPDATE %ibuy_farmplant% SET `location` = :location WHERE `plantid` = :tranId LIMIT 1';
				} else if ($data->mapTable == 'land') {
					$stmt = 'UPDATE %ibuy_farmland% SET `location` = :location WHERE `landid` = :tranId LIMIT 1';
				}
				if ($stmt) {
					mydb::query($stmt, ':tranId', $tranId, ':location', $data->location);
					//$ret .= mydb()->_query;
				}
			}
			//$ret .= print_o($data,'$data');
			//$ret .= print_o(post(),'post()');
			break;

		/*
		case 'plant.remove':
			if (!$orgId) break;
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %ibuy_farmplant% WHERE `plantid` = :plantid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':plantid', $tranId, ':orgid', $orgId);
				//$ret .= mydb()->_query;
			}
			break;
		*/

		case 'plant.crop':
			if (!$orgId) break;
			if ($tranId && ($cropDate = post('croped'))) {
				$ret .= 'เก็บเกี่ยวเรียบร้อย';
				$stmt = 'UPDATE %ibuy_farmplant% SET `croped` = :croped WHERE `plantid` = :plantId LIMIT 1';
				mydb::query($stmt, ':plantId', $tranId, ':croped', sg_date($cropDate, 'U'));
			}
			break;

		case 'photo.upload':
			$post = (Object) post();
			$data->tpid = $tpid;
			$data->prename = strtolower(SG\getFirst($post->module,'ibuy_shop').'_'.$orgId.($post->tagname ? '_'.$post->tagname : '').'_');
			$data->tagname = SG\getFirst($post->module,'GREEN').($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			$data->orgid = SG\getFirst($post->orgid);
			$data->refid = $tranId;
			$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'green/my/info/photo.delete/';
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
			if (!$orgId) break;
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
				$bookInfo = mydb::select('SELECT * FROM %ibuy_farmbook% WHERE `bookid` = :bookid LIMIT 1', ':bookid', $tranId);
				$stmt = 'DELETE FROM %ibuy_farmbook% WHERE `bookid` = :bookid LIMIT 1';
				mydb::query($stmt, ':bookid', $tranId);
				//$ret .= mydb()->_query;
				R::model('watchdog.log',
					'Green',
					'Booking Delete',
					'Booking '.$tranId.' was delete by '.i()->username.' :: {id: "'.$tranId.'", uid: "'.$bookInfo->uid.'", plant: "'.$bookInfo->plantid.'", qty: "'.$bookInfo->qty.'"}',
					i()->uid,
					$tranId
				);
			}
			break;

		case 'product.save':
			if (!$orgId) break;
			$data = (Object) post('data');
			$data->shopid = $orgId;
			$result = R::Model('ibuy.product.create', $data, '{debug: false}');
			//$ret .= print_o($result, '$result');
			//$ret .= print_o($data, '$data');
			break;

		case 'mobile.location.save':
			$data = (Object) post();
			$data->bigid = SG\getFirst($data->bigid);
			$data->keyname = 'ibuy.location';
			$data->keyid = i()->uid;
			$data->fldname = 'location';
			$data->fldref = SG\getFirst($data->orgid);
			$data->fldtype = 'json';
			$value = (Object) Array('locname' => $data->locname, 'location' => $data->location);
			$data->flddata = SG\json_encode($value);
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
			if (!$orgId) break;
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
			if (!$orgId) break;
			if ($tranId AND SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId);
			}
			break;

		case 'animal.weight.save':
			$data = (Object) post('data');
			$data->date = sg_date(SG\getFirst($data->date, date('Y-m-d')), 'Y-m-d');
			foreach ($data as $key => $value) {
				if (in_array($key, explode(',','plantId,weight,round,grassweight,grassmoney,strawweight,strawmoney,foodweight,foodmoney,mineralmoney,drugmoney'))) {
					$data->{$key} = sg_strip_money($value);
				}
			}
			$bigData = new stdClass();
			$bigData->bigid = SG\getFirst($tranId);
			$bigData->keyname = 'GREEN,ANIMAL';
			$bigData->keyid = $data->plantId;
			$bigData->fldname = 'weight';
			$bigData->fldtype = 'json';
			$bigData->flddata = SG\json_encode($data);
			$bigData->created = sg_date($data->date, 'U');
			$bigData->ucreated = i()->uid;
			$bigData->modified = date('U');
			$bigData->umodified = i()->uid;

			$stmt = 'INSERT INTO %bigdata%
				(`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`, `modified`)
				VALUES
				(:bigid, :keyname, :keyid, :fldname, :fldtype, :flddata, :created, :ucreated, :modified)
				ON DUPLICATE KEY UPDATE
				`flddata` = :flddata
				, `created` = :created
				, `modified` = :modified
				, `umodified` = :umodified
				';

			mydb::query($stmt, $bigData);
			//$ret .= mydb()->_query.'<br />';

			$stmt = 'UPDATE %ibuy_farmplant% SET `weight` = :weight, `round` = :round WHERE `plantid` = :plantId LIMIT 1';
			mydb::query($stmt, $data);
			//$ret .= mydb()->_query.'<br />';
			//$ret .= print_o($data, '$data');
			break;

		case 'animal.weight.remove':
			if ($tranId && SG\confirm()) {
				$ret .= 'DELETED';
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId);
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