<?php
/**
* Project : Fund Information Model
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @param String $action
* @param Int $tranId
* @return String
*
* @usage project/fund/$orgId/info[/$action][/$tranId]
*/

$debug = true;

function project_fund_info($self, $fundInfo, $action = NULL, $tranId = NULL) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$right = $fundInfo->right;

	$checkRightAction = 'officer.save,proposal.add,follow.add,member.save,member.cancel,member.recall';

	if (!($right->edit || in_array($action, explode(',',$checkRightAction)) )) return 'ERROR: ACCESS DENIED';

	$ret = '';

	//$ret .= 'Action = '.$action. ' Right to edit = '.($right->edit ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($fundInfo, '$fundInfo');

	// Action For Editable
	switch ($action) {
		case 'delete':
			if ($right->is->admin && SG\confirm()) {
				$ret .= R::Model('project.fund.delete',$fundInfo);
			}
			break;

		case 'finance.save':
			$post = (object) post('data');

			if ($post->accbank) {
				$post->openbalance = sg_strip_money($post->openbalance);
				$post->opendate = sg_date($post->opendate, 'Y-m-d');
				$post->uid = i()->uid;
				$post->created = date('U');
				$post->modified = date('U');
				$post->modifyby = i()->uid;

				$stmt = 'UPDATE %project_fund%
					SET
						  `accbank` = :accbank
						, `accname` = :accname
						, `accno` = :accno
						, `openbalance` = :openbalance
						, `openbaldate` = :opendate
					WHERE `orgId` = :orgId
					LIMIT 1';

				mydb::query($stmt, ':orgId', $orgId, $post);

				//$ret .= mydb()->_query.'<br />';
				//$ret .= print_o($post,'$post');
			}
			break;


		case 'area.save':
			$post = (object) post('data');

			if ($post->name) {
				$address = SG\explode_address($post->address, $post->areacode);
				$post->address = SG\implode_address($address);
				$post->house = $address['house'];
				$post->village = $address['village'];
				$post->uid = i()->uid;
				$post->created=date('U');
				$post->modified=date('U');
				$post->modifyby=i()->uid;

				$stmt = 'UPDATE %db_org%
					SET
						  `name` = :name
						, `email` = :orgemail
						, `phone` = :orgphone
						, `fax` = :orgfax
						, `house` = :house
						, `village` = :village
						, `changwat` = :changwat
						, `ampur` = :ampur
						, `tambon` = :tambon
						, `areacode` = :areacode
						, `address` = :address
					WHERE `orgid` = :orgid
					LIMIT 1';

				mydb::query($stmt,':orgid',$fundInfo->orgid,$post);
				//$ret .= mydb()->_query;

				$stmt = 'UPDATE %project_fund%
					SET
						  `orgsize` = :orgsize
						, `openyear` = :openyear
						, `orgemail` = :orgemail
						, `orgphone` = :orgphone
						, `orgfax` = :orgfax
						, `orgaddr` = :address
						, `orgzip` = :orgzip
						, `tambonnum` = :amttambon
						, `moonum` = :moonum
					WHERE `orgId` = :orgId
					LIMIT 1';
				mydb::query($stmt,':orgId',$orgId,$post);
				//$ret .= mydb()->_query;
			}
			break;

		case 'address.save':
			$post = (object) post('data');

			if ($post->address) {
				$address = SG\explode_address($post->address, $post->areacode);
				//$ret .= print_o($address,'$address');
				$post->address = SG\implode_address($address);
				$post->house = $address['house'];
				$post->village = $address['village'];
				$post->uid = i()->uid;
				$post->created=date('U');
				$post->modified=date('U');
				$post->modifyby=i()->uid;

				$stmt = 'UPDATE %db_org%
					SET
						`house` = :house
						, `village` = :village
						, `address` = :address
						, `areacode` = :areacode
						, `changwat` = :changwat
						, `ampur` = :ampur
						, `tambon` = :tambon
					WHERE `orgid` = :orgid
					LIMIT 1';

				mydb::query($stmt,':orgid',$fundInfo->orgid,$post);
				//$ret .= mydb()->_query;

				$stmt = 'UPDATE %project_fund%
					SET
						`orgaddr` = :address
						, `changwat` = :changwat
						, `ampur` = :ampur
						, `tambon` = :tambon
					WHERE `orgId` = :orgId
					LIMIT 1';
				mydb::query($stmt,':orgId',$orgId,$post);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o($post,'$post');
			break;

		case 'population.save':
			$data = (Object) post('data');
			$data->orgid = $fundInfo->orgid;
			$data->fundid = $fundInfo->fundid;
			$data->refcode = $fundInfo->fundid;
			$data->refid = $data->year;
			$data->uid = i()->uid;
			$data->balance = sg_strip_money($data->balance);
			$data->population = sg_strip_money($data->population);
			$data->budgetnhso = sg_strip_money($data->budgetnhso);
			$data->budgetlocal = sg_strip_money($data->budgetlocal);
			$data->created = date('U');
			$data->modified = date('U');
			$data->modifyby = i()->uid;

			// Get trid of year
			$stmt = 'SELECT `trid` FROM %project_tr% WHERE `orgid` = :orgid AND `formid` = "population" AND `refid` = :refid LIMIT 1';
			$data->trid = mydb::select($stmt, $data)->trid;

			//$ret .= 'Tran Id = '.$data->trid.'<br />';

			$stmt = 'UPDATE %project_fund%
				SET
					  `orgincomepcnt` = :orgincomepcnt
					, `population` = :population
					, `estimatenhso` = :budgetnhso
					, `estimatelocal` = :budgetlocal
				WHERE `orgid` = :orgid
				LIMIT 1';

			mydb::query($stmt, $data);
			//$ret.=mydb()->_query.'<br />';

			$data->recordyear = $data->year.'-07-01';
			if (empty($data->haveplan)) $data->haveplan = -1;

			$stmt = 'INSERT INTO %project_tr%
				(
				  `trid`, `uid`, `orgid`, `refid`, `refcode`, `formid`, `part`, `date1`
				, `num2`, `num3`, `num4`
				, `detail2`, `detail3`
				, `text1`, `text2`
				, `created`
				) VALUES (
				  :trid, :uid, :orgid, :refid, :refcode, "population", :fundid, :recordyear
				, :population, :budgetnhso, :budgetlocal
				, :byname, :byposition
				, :orgemail, :orgphone
				, :created
				)
				ON DUPLICATE KEY UPDATE
				  `date1` = :recordyear
				, `num2` = :population
				, `num3` = :budgetnhso
				, `num4` = :budgetlocal
				, `detail2` = :byname
				, `detail3` = :byposition
				, `text1` = :orgemail
				, `text2` = :orgphone
				, `modified` = :modified
				, `modifyby` = :modifyby
				';

			mydb::query($stmt, $data);
			//$ret .= mydb()->_query.'<br />';

			$ret .= 'บันทึกข้อมูลประชากรเรียบร้อย';
			break;

		case 'population.remove':
			if ($tranId && ($deleteYear = post('year')) && SG\confirm()) {
				$ret .= 'ลบข้อมูลประชากร ปี '.($deleteYear + 543).' เรียบร้อย';
				$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :tranId AND `formid` = "population" AND `orgid` = :orgId AND `refid` = :year LIMIT 1';
				mydb::query($stmt, ':tranId', $tranId, ':orgId', $orgId, ':year', $deleteYear);
				//$ret .= mydb()->_query;
			}
			break;

		case 'financial.add':
			if (post('refdate') && post('glcode') && post('debit') != '') {
				$amount = abs(sg_strip_money(post('debit')));
				$data = (Object) [];
				$data->orgid = $fundInfo->orgid;
				$data->refdate = sg_date(post('refdate'),'Y-m-d');

				if ($data->refdate > date('Y-m-d') || $data->refdate < $fundInfo->finclosemonth) {
					// refdate is out of range
				} else if ($refcode = post('refcode')) {
					$data->refcode= $refcode;
					$glTrans=R::Model('project.gl.tran.get',$refcode);
					$data->items=array(
						array('pglid'=>$glTrans->items[0]->pglid,'glcode'=>'10201','amount'=>$amount),
						array('pglid'=>$glTrans->items[1]->pglid,'glcode'=>post('glcode'),'amount'=>-$amount),
					);
					//$ret .=print_o($data,'$data').print_o($glTrans,'$glTrans');
				} else {
					$data->refcode=R::Model('project.gl.getnextref','RCV', true);
					$data->items=array(
						array('glcode'=>'10201','amount'=>$amount),
						array('glcode'=>post('glcode'),'amount'=>-$amount),
					);
				}
				R::Model('project.gl.tran.add',$data);
				//$ret .=print_o($data,'$data');
				//$ret .= print_o($fundInfo, '$fundInfo');
				$ret .= 'Financial Transaction Add/Edit Completed';

				R::Model('project.nhso.obt.update', $fundInfo);
			}
			//$ret .=print_o(post(),'post()');
			break;

		case 'financial.delete':
			if ($tranId && SG\confirm()) {
				$gl = R::Model('project.gl.tran.get', NULL, $tranId);
				if ($gl->refcode) R::Model('project.gl.tran.delete',$gl->refcode);
				$ret .= 'Financial Transaction Deleted';

				R::Model('project.nhso.obt.update', $fundInfo);
			}
			break;

		case 'financial.lock':
			if ($tranId) {
				$closeDate = sg_date($tranId.'-01','Y-m-t');
				$stmt = 'UPDATE %project_fund% SET `finclosemonth` = :closedate WHERE `orgid` = :orgid LIMIT 1';
				mydb::query($stmt,':orgid',$fundInfo->orgid, ':closedate',$closeDate);
				$ret .= 'Financial Locked';
			} else {
				$ret .= 'Financial Lock Error';
			}
			//$ret.=__project_fund_financial_month_list($fundInfo);
			break;

		case 'financial.unlock':
			if ($tranId) {
				$closeDate = date('Y-m-t',strtotime($tranId.'-00'));
				if ($closeDate < $fundInfo->info->openbaldate) $closeDate = NULL;
				$stmt = 'UPDATE %project_fund% SET `finclosemonth` = :closedate WHERE `orgid` = :orgid LIMIT 1';
				mydb::query($stmt,':orgid',$fundInfo->orgid, ':closedate',$closeDate);
				$fundInfo->finclosemonth = $closeDate;
				$ret .= 'Financial Unlocked';
			} else {
				$ret .= 'Financial Unlock Error';
			}
			//$ret.=__project_fund_financial_month_list($fundInfo);
			break;


		case 'eval.save':
			if (post('header')) {
				$data = (Object) post('data');
				$data->orgId = $orgId;
				$header = post('header');
				$rate = post('rate');

				//$ret.=print_o($data,'$data');
				//$ret.=print_o($header,'$header');
				//$ret.=print_o($rate,'$rate');

				$data->qtgroup = 10;
				$data->qtdate = $data->year.'-10-31';
				$result = R::Model('qt.save',$data);

				// $ret .= print_o($result, '$result');

				if ($header['HEADER.EVALDATE']) $header['HEADER.EVALDATE'] = sg_date($header['HEADER.EVALDATE'],'Y-m-d');

				foreach ($header as $key => $value) {
					unset($tran);
					$tran->qtref = SG\getFirst($data->qtRef, $data->qtref);
					$tran->part = $key;
					$tran->value = $value;
					$trResult = R::Model('qt.tran.save',$tran);
					// $ret .= print_o($trResult, '$trResult');
				}

				foreach ($rate as $key => $value) {
					unset($tran);
					$tran->qtref = $data->qtRef;
					$tran->part = $key;
					$tran->rate = $value;
					$trResult = R::Model('qt.tran.save',$tran);
					// $ret .= print_o($tran,'$tran');
					// $ret .= print_o($trResult, '$trResult');
				}
				$ret .= 'บันทึกแบบประเมินเรียบร้อย';
			}
			// $ret .= print_o(post(),'post()');
			break;

		case 'eval.delete':
			if ($tranId && SG\confirm()) {
				R::Model('qt.delete',$tranId);
			}
			break;


		case 'board.save':
			$data = (object) post();
			if (empty($data->id)) $data->id = NULL;

			$data->orgid = $fundInfo->orgid;
			$data->boardposition = mydb::select('SELECT `catparent` FROM %tag% WHERE `taggroup`="project:boardpos" AND `catid` = :catid LIMIT 1',':catid',$data->position)->catparent;

			if ($data->orgid && $data->boardposition && $data->position && $data->name) {
				// Check Current Board Series
				$stmt = 'SELECT `series` FROM %org_board% WHERE `orgid` = :orgid AND `status` = :inboard LIMIT 1';
				$currentSeries = mydb::select($stmt, ':orgid', $fundInfo->orgid, ':inboard', _INBOARD_CODE)->series;

				$data->series = $currentSeries ? $currentSeries : sg_date($data->datein, 'Y');

				list($data->position, $data->posno) = explode(':', $data->position);
				if (empty($data->posno)) $data->posno = NULL;

				$data->datein = sg_date($data->datein,'Y-m-d');
				$data->datedue = sg_date($data->datedue,'Y-m-d');

				$stmt = 'INSERT INTO %org_board% (
					  `brdid`,`orgid`, `boardposition`, `position`, `posno`
					, `prename`, `name`
					, `fromorg`, `datein`, `datedue`
					, `series`
					) VALUES (
					  :id, :orgid, :boardposition, :position, :posno
					, :prename, :name
					, :fromorg, :datein, :datedue
					, :series
					)
					ON DUPLICATE KEY UPDATE
					  `boardposition` = :boardposition
					, `position` = :position
					, `posno` = :posno
					, `prename` = :prename
					, `name` = :name
					, `fromorg` = :fromorg
					, `datein` = :datein
					, `datedue` = :datedue
					';

				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}
			//$ret .= print_o($data,'$data');
			break;

		case 'board.out':
			// Save board out
			if ($tranId && post('outcond') && post('dateout')) {
				$stmt = 'UPDATE %org_board% SET `status`=:outcond, `dateout`=:dateout WHERE `brdid`=:brdid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':brdid', $tranId, ':orgid', $orgId, ':outcond',post('outcond'), ':dateout',sg_date(post('dateout'),'Y-m-d'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'board.beover':
			$outBeOver = 2;
			// Save board out
			if (post('dateout') && SG\confirm()) {
				$stmt = 'UPDATE %org_board%
					SET `status` = :outcond, `dateout` = :dateout
					WHERE `orgid` = :orgid AND `status` = 1';

				mydb::query($stmt, ':orgid',$orgId, ':outcond', $outBeOver, ':dateout', sg_date(post('dateout'), 'Y-m-d'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'board.delete':
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %org_board% WHERE `brdid` = :brdid LIMIT 1';
				mydb::query($stmt, ':brdid', $tranId);
			}
			break;

		case 'board.letter.create':
			if (post('type')) {
				$data = new stdClass;
				$data->uid = i()->uid;
				$data->refid = $orgId;
				$data->refcode = post('type');
				$data->formid = 'fund';
				$data->part = 'boardletter';
				$data->created = date('U');

				// Create letter id
				$stmt = 'INSERT INTO %project_tr% (`uid`, `refid`, `refcode`, `formid`, `part`, `created`) VALUES (:uid, :refid, :refcode, :formid, :part, :created)';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				$letterId = mydb()->insert_id;

				// Update board name with letter id
				$stmt = 'UPDATE %org_board% SET `refid` = :refid, `appointed` = 1 WHERE `orgid` = :orgid AND `status` = 1 AND `appointed` IS NULL';
				mydb::query($stmt, ':refid', $letterId, ':orgid', $orgId);
				//$ret .= mydb()->_query;

				location('project/fund/'.$orgId.'/board.letter.'.$data->refcode.'/'.$letterId);
			}
			break;

		case 'board.letter.del':
			if ($tranId && SG\confirm()) {
				$stmt = 'UPDATE %org_board% SET `refid` = NULL, `appointed` = NULL WHERE `orgid` = :orgid AND `refid` = :refid';
				mydb::query($stmt, ':orgid', $orgId, ':refid', $tranId);

				$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :trid AND `formid` = "fund" AND `part` = "boardletter" AND `refid` = :orgid';
				mydb::query($stmt, ':orgid', $orgId, ':trid', $tranId);

				$ret .= 'Letter Deleted';
			}
			break;

		case 'board.letter.sendnotice':
			$stmt = 'UPDATE %project_tr% SET `flag`=1, `date1` = :date WHERE `trid` = :trid AND `refid` = :orgid AND `formid` = "fund" AND `part` = "boardletter" LIMIT 1';
			mydb::query($stmt, ':trid', $tranId, ':orgid',$orgId, ':date', date('Y-m-d'));
			//$ret .= mydb()->_query;
			break;





		// Action must check right befor process

		case 'officer.save':
			// Add Officer
			if (!$right->createMember) return 'ERROR: ACCESS DENIED';
			if (post('uid') && post('type')) {
				$stmt = 'INSERT INTO %org_officer% (`orgid`,`uid`,`membership`) VALUES (:orgid, :uid, :membership)';
				mydb::query($stmt, ':orgid', $orgId,':uid', post('uid'), ':membership', post('type'));
				//$ret .= mydb()->_query;
			}
			return $ret;
			break;

		case 'member.save':
			if (!$right->createMember) return 'ERROR: ACCESS DENIED';
			$data = (Object) post();

			$data->addusername=trim($data->addusername);
			$data->addpassword=trim($data->addpassword);
			if (empty($data->addpassword)) $data->addpassword=substr(md5(uniqid()), 0, 8);
			$data->name=trim($data->name);
			$data->email=trim($data->email);
			$data->phone=trim($data->phone);
			$data->address=trim($data->address);
			$data->encpassword=sg_encrypt($data->addpassword,cfg('encrypt_key'));
			$data->datein=date('Y-m-d H:i:s');
			$data->status='enable';
			$data->admin_remark='Add by '.i()->username;
			$stmt='INSERT INTO %users% (`username`, `password`, `name`, `status`, `email`, `phone`, `address`, `datein`, `admin_remark`)
						VALUES
						(:addusername, :encpassword, :name, :status, :email, :phone, :address, :datein, :admin_remark)';
			mydb::query($stmt,$data);

			if (!mydb()->_error) {
				$data->uid=mydb()->insert_id;
				$data->orgid=$orgId;
				$data->membership="MEMBER";
				$stmt='INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;

				$ret.='<div class="box">';
				$ret.='<h3>รายละเอียดสมาชิก</h3><br /><br />';
				$ret.='Username : '.$data->addusername.'<br /><br />';
				$ret.='Password : '.$data->addpassword.'<br /><br />';
				$ret.='ชื่อ-นามสกุล : '.$data->name.'<br /><br />';
				$ret.='อีเมล์ : '.$data->email.'<br /><br />';
				$ret.='โทรศัพท์ : '.$data->phone.'<br /><br />';
				$ret.='</div>';
				$ret.='<nav class="nav -no-print"><a class="btn" href="'.url('project/fund/'.$orgId.'/info.member').'"><i class="icon -back"></i><span>รายชื่อสมาชิกทั้งหมด</span></a></nav>';
			} else {
				$ret.='<p class="notify">มีความผิดพลาดในการสร้างสมาชิก</p>';
				$ret .= R::Page('project.fund.member.create', NULL, $fundInfo, $data);
			}
			//$ret.=print_o($data,'$data');
			break;

		case 'member.remove':
			if (!$right->createMember) return 'ERROR: ACCESS DENIED';
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %org_officer% WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'member.cancel':
			if (!$right->createMember) return 'ERROR: ACCESS DENIED';
			if ($tranId) {
				$stmt = 'DELETE FROM %org_officer% WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'member.recall':
			if (!$right->createMember) return 'ERROR: ACCESS DENIED';
			if ($tranId) {
				$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, "MEMBER")';
				mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId);
			}
			break;

		case 'proposal.add':
			if (!$right->createProposal) return 'ERROR: ACCESS DENIED';

			$refId = post('refid');
			if ($refId) {
				$title = mydb::select('SELECT `detail1` FROM  %project_tr% WHERE `trid` = :trid LIMIT 1', ':trid', $refId)->detail1;
			} else {
				$title = post('title');
			}

			if ($title) {
				$data = new stdClass();
				$data->title = $title;
				$data->budget = post('budget');
				$data->orgid = $fundInfo->orgid;
				$data->tambon = $fundInfo->info->tambon;
				$data->ampur = $fundInfo->info->ampur;
				$data->changwat = $fundInfo->info->changwat;
				$data->created = post('created') ? sg_date(post('created'),'Y-m-d') : NULL;
				$data->date_approve = post('date_approve') ? sg_date(post('date_approve'),'U') : NULL;
				if (post('year')) {
					$data->pryear = post('year');
				} else if ($data->date_approve) {
					$data->pryear = sg_date($data->date_approve,'Y')+(sg_date($data->date_approve,'m') >= 10 ? 1 : 0);
				}

				$result = R::Model('project.develop.create',$data, '{debug: false}');
				if ($refId && $result->tpid) {
					$stmt = 'UPDATE %project_tr% SET `refid` = :tpid WHERE `trid` = :refid LIMIT 1';
					mydb::query($stmt, ':tpid', $result->tpid, ':refid', $refId);
					//$ret.=mydb()->_query;
					if (post('group')) {
						$stmt = 'INSERT INTO %project_tr% SET `tpid` = :tpid, `formid`="develop", `part`="supportplan", `refid` = :refid, `uid` = :uid , `created` = :created';
						mydb::query($stmt, ':tpid', $result->tpid, ':refid', post('group'), ':uid', i()->uid, ':created', date('U'));
						//$ret.=mydb()->_query;
					}
				}
				// debugMsg($result,'$result');
				//$ret.=print_o(post(),'post()');
				//$ret.=print_o($data,'$data');
				//$ret.=print_o($fundInfo,'$fundInfo');
				location('project/develop/'.$result->tpid.'/view/edit');
			}
			break;

		case 'follow.add':
			if (!$right->createFollow) return 'ERROR: ACCESS DENIED';

			if (!post('title')) return 'ERROR: NO TITLE';

			$data = new stdClass();
			$data->title = post('title');
			$data->budget = post('budget');
			$data->orgid = $orgId;
			$data->tambon = $fundInfo->info->tambon;
			$data->ampur = $fundInfo->info->ampur;
			$data->changwat = $fundInfo->info->changwat;
			$data->date_approve = post('date_approve') ? sg_date(post('date_approve'),'Y-m-d') : NULL;
			if (post('pryear')) {
				$data->pryear = post('pryear');
			} else if ($data->date_approve) {
				$data->pryear = sg_date($data->date_approve,'Y')+(sg_date($data->date_approve,'m')>=10?1:0);
			}
			$data->supporttype = post('typename');
			$data->supportorg = post('orgname');
			$data->location = $fundInfo->info->location;

			$result = R::Model('project.create', $data);
			$tpid = $result->tpid;
			if ($tpid) {
				$stmt = 'UPDATE %project% SET `supporttype` = :supporttype, `supportorg` = :supportorg WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, $data);

				foreach (post('supportplan') as $key => $value) {
					$planData = (Object) [
						'tpid' => $tpid,
						'refid' => $value,
						'uid' => i()->uid,
						'created' => date('U'),
					];
					$stmt = 'INSERT INTO %project_tr% (`tpid`,`refid`,`formid`,`part`,`uid`,`created`) VALUES (:tpid,:refid,"info","supportplan",:uid,:created)';
					mydb::query($stmt,$planData);
					//$ret.=mydb()->_query.'<br />';
				}
				location('project/'.$tpid);
			}
			//$ret.=print_o($data,'$data');
			//$ret.=print_o(post(),'post()');
			//$ret.=print_o($fundInfo,'$fundInfo');
			break;

		default:
			$ret .= 'NO ACTION';
			break;
	}

	return $ret;
}
?>