<?php
/**
 * Save map
 *
 * @param Array $_POST
 * @return String
 */
function map_update($self) {
	$ret['html'] = 'Update';
	//debugMsg(post(),'post');
	$post = (object)post('mapping');

	if (empty($post->who)) return;

	$post->mapid = SG\getFirst($post->mapid);
	$post->mapgroup = SG\getFirst($post->mapgroup,1);
	$post->dowhat = trim($post->dowhat,',');
	$post->created = date('U');
	$post->uid = i()->uid;
	$post->prepare = $post->when['prepare'] ? 'Yes' : NULL;
	$post->during = $post->when['during'] ? 'Yes' : NULL;
	$post->after = $post->when['after'] ? 'Yes' : NULL;
	$post->status = SG\getFirst($post->status, cfg('map-default-status'));
	$post->poster = SG\getFirst($post->poster);
	$post->email = SG\getFirst($post->email);
	$post->phone = SG\getFirst($post->phone);

	$addressList = SG\explode_address($post->address,$post->areacode);
	//$ret['html'] .= print_o($addressList,'$addressList');

	$post->ip = ip2long(GetEnv('REMOTE_ADDR'));
	//$ret['html'] .= print_o($post,'$post');
	//return $ret;


	if ($post->latlng) {
		list($x,$y)=explode(',',$post->latlng);
		if (is_numeric($x) && is_numeric($y)) {
			$post->point='func.PointFromText("POINT('.$x.' '.$y.')")';
		}
	} else {
		$post->latlngPoint=NULL;
	}

	if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$post->address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$post->address,$out)) {
		$post->village = (in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3])) ? $out[3] : $post->village;
	}

	if ($post->mapid) {
		$rs = R::Model('map.get',$post->mapid);

		$isEdit = $rs->RIGHT & _IS_EDITABLE;

		if ($rs->status == 'lock' && !$isEdit) {
			$ret['html'] = message('error','access denied');
			$ret['id'] = $rs->mapid;
			$ret['marker'] = R::Model('map.who.get',$rs->mapid);
			$ret['msg'] = 'Cancel';
			return $ret;
		}
	}

	$stmt='INSERT INTO %map_networks%
					(
					  `mapid`, `mapgroup`, `uid`, `status`
					, `who`, `dowhat`, `prepare`, `during`, `after`
					, `address`, `areacode`
					, `detail`, `privacy`, `latlng`
					, `poster`, `email`, `phone`
					, `ip`, `created`, `modified`
					) VALUES (
					  :mapid, :mapgroup, :uid, :status
					, :who, :dowhat, :prepare, :during, :after
					, :address, :areacode
					, :detail, :privacy, :point
					, :poster, :email, :phone
					, :ip, :created, NULL
					) ON DUPLICATE KEY UPDATE
					  `mapgroup`=:mapgroup, `who`=:who, `dowhat`=:dowhat
					, `prepare`=:prepare, `during`=:during, `after`=:after
					, `address`=:address, `areacode` = :areacode
					, `detail`=:detail, `privacy`=:privacy
					, `latlng`=:point, `modified`=:created
					';
	mydb::query($stmt,$post);
	//$debug.=mydb()->_query.'<br />'.mydb()->_errmsg.'<br />'.print_o($post,'$post');
	//$ret['html'].=$debug;

	// Update modify history
	if ($post->mapid) {
		//$ret['html'].=print_o($rs,'$rs');
		$fieldChecks = array('gid','who','dowhat','prepare','during','after','address','detail','privacy','latlng');
		$modify->mapid = $post->mapid;
		$modify->uid = i()->uid;
		foreach ($fieldChecks as $fld) {
			if ($rs->{$fld} != $post->{$fld}) {
				$modify->fld = $fld;
				$modify->from = $rs->{$fld}?$rs->{$fld}:'';
				$modify->to = $post->{$fld}?$post->{$fld}:'';
				mydb::query('INSERT INTO %map_history% (`mapid`, `uid`, `fld`, `from`, `to`) VALUES (:mapid, :uid, :fld, :from, :to)',$modify);
				//						$ret['html'].=mydb()->_query.print_o($modify,'$modify');
			}
		}
	}

	$ret['id'] = $mapid = $post->mapid ? $post->mapid : mydb()->insert_id;

	$ret['marker'] = R::Model('map.who.get',$mapid);
	$ret['msg'] = 'Updated';
	//			$debug.='ID='.$mapid.'<br />'.print_o($post,'$post');

	//		$ret['location']=array('map','id='.$mapid,NULL);
	return $ret;
}
?>