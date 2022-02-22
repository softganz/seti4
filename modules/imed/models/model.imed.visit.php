<?php
/**
* iMed :: Visit Model
* Created 2021-08-20
* Modify  2021-08-02
*
* @param Array $args
* @return Object
*
* @usage import('model:model.visit')
* @usage new ImedVisitModel([])
* @usage ImedVisitModel::method()
*/

$debug = true;

class ImedVisitModel {
	public static function create($post, $options = '{}') {
		$defaults = '{debug:false,start:0,item:100}';
		$options = sg_json_decode($options,$defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($options,'$options');

		$result = (Object) [
			'seqId' => NULL,
			'psnId' => NULL,
			'error' => false,
			'msg' => NULL,
		];

		$post->seq = SG\getFirst($post->seqId);
		$post->pid = SG\getFirst($post->psnId);
		$post->visitType = SG\getFirst($post->visitType);
		$post->msg = SG\getFirst($post->msg);
		$post->uid = i()->uid;
		$post->timedata = sg_date($post->timedata ? $post->timedata : date('U'),'U');
		$post->created = date('U');
		$post->service = SG\getFirst($post->service);
		//$ret .= print_o(R()->appAgent,'R()->appAgent');
		if (R()->appAgent) {
			$post->appsrc = R()->appAgent->OS;
			$post->appagent = R()->appAgent->dev.'/'.R()->appAgent->ver.' ('.R()->appAgent->type.';'.R()->appAgent->OS.')';
		} else if (preg_match('/imed\/app/',$_SERVER["HTTP_REFERER"])) {
			$post->appsrc = 'Web App';
			$post->appagent = 'Web App';
		} else {
			$post->appsrc = 'Web';
			$post->appagent = 'Web';
		}
		//$ret .= 'SET appsrc = '.$post->appsrc.' '.$post->appagent.'<br />';

		$stmt = 'INSERT INTO %imed_service%
				(`seq`, `pid`, `uid`, `visitType`, `service`, `appsrc`, `appagent`, `rx`, `timedata`, `created`)
					VALUES
				(:seq, :pid, :uid, :visitType, :service, :appsrc, :appagent, :msg, :timedata, :created)
				ON DUPLICATE KEY UPDATE
				`rx` = :msg
				';
		mydb::query($stmt, $post);

		if (empty($post->seq)) $post->seq = mydb()->insert_id;

		if ($debug) debugMsg('<pre>'.mydb()->_query.'</pre>');

		$result->seqId = $post->seq;

		// Save service complete
		if (mydb()->affected_rows == 1) {
			$stmt = 'INSERT INTO %imed_patient% (`pid`, `uid`, `created`) VALUES (:pid, :uid, :created) ON DUPLICATE KEY UPDATE `service` = `service` + 1';
			mydb::query($stmt, $post);
			//$ret .= mydb()->_query;
		}

		$result->msg = 'บันทีกการเยี่ยมบ้านเรียบร้อย';

		if ($debug) debugMsg($post,'$post');
		// $ret .= print_o($dataFB,'$dataFB');
		if ($debug) debugMsg($result,'$result');
		return $result;
	}

	public static function get($psnId, $seqId = NULL, $options = '{}') {
		$defaults = '{debug:false,start:0,item:100}';
		$options = sg_json_decode($options,$defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($options,'$options');

		if (!$psnId) return NULL;

		$result = (Object) [
			'seqId' => $seqId,
			'psnId' => $psnId,
			'RIGHT' => NULL,
			'RIGHTBIN' => NULL,
			'error' => NULL,
		];

		mydb::query('SET @@group_concat_max_len = 4096;');

		mydb::where(' s.`pid` = :psnId AND s.`service` IN ("Treatment","Home Visit","Web Distance Treatment","Care Plan" ,"Care Service")',':psnId',$psnId);
		if ($seqId) mydb::where('s.`seq` = :seqId',':seqId',$seqId);

		$stmt = 'SELECT
			  s.`seq` `seqId`, s.`pid` `psnId`
			, s.*
			, FROM_UNIXTIME(s.`timedata`, "%Y-%m-%d") `visitDate`
			, FROM_UNIXTIME(s.`timedata`, "%H:%i") `visitTime`
			, s.`rx` `detail`
			, u.`username`, u.`name` `ownerName`
			, p.`prename`
			, CONCAT(p.`name`," ",p.`lname`) patient_name
			, b.`score`
			, q2.`q2_score`, q2.`q9_score`
			, GROUP_CONCAT(CONCAT(`fid`,"|",`file`)) photos
			, (SELECT GROUP_CONCAT(`needid`) FROM %imed_need% WHERE `seq` = s.`seq`) `needItems`
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
				LEFT JOIN %imed_barthel% b USING(`seq`)
				LEFT JOIN %imed_2q9q% q2 USING(`seq`)
				LEFT JOIN %imed_files% f ON f.`seq` = s.`seq` AND f.`type` = "photo"
			%WHERE%
			GROUP BY `seq`
			ORDER BY s.`seq` DESC
			LIMIT '.($seqId ? '1' : $options->start.','.$options->item);
		$dbs=mydb::select($stmt);


		if ($debug) debugMsg($dbs,'$dbs');

		if (empty($dbs->_num_rows)) return NULL;


		if (!$debug) mydb::clearprop($dbs);

		if ($seqId) {
			$result = (Object) ((Array) $result + (Array) $dbs);

			$right = 0;

			$isOwner = i()->ok && $result->uid == i()->uid;
			$isAdmin = user_access('administer imeds');
			$isAccess = false;
			$isEdit = false;
			//user_access('administer imeds','edit own imed content',$result->info->uid) || $isOwner;
			if ($isAdmin || $isOwner) {
				$isAccess = true;
				$isEdit = true;
			} else  if ($zones = imed_model::get_user_zone(i()->uid,'imed')) {
				$psnRight = imed_model::in_my_zone($zones,$result->info->changwat,$result->info->ampur,$result->info->tambon);
				if (!$psnRight) {
					$isAccess = false;
					$isEdit = false;
				} else if (in_array($psnRight->right, ['edit','admin'])) {
					$isAccess = true;
					$isEdit = false;
				} else if (in_array($psnRight->right, ['view'])) {
					$isAccess = true;
					$isEdit = false;
				}
			} else {
				$isAccess = false;
				$isEdit = false;
			}


			if ($isAdmin) $right = $right | _IS_ADMIN;
			if ($isOwner) $right = $right | _IS_OWNER;
			if ($isAccess) $right = $right | _IS_ACCESS;
			if ($isEdit) $right = $right | _IS_EDITABLE;

			$result->RIGHT = $right;
			$result->RIGHTBIN = decbin($right);
		} else {
			$result->items = $dbs->items;
		}

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false, order: "s.`seq`", sort: "DESC", start: 0, items: 10, debugResult: false}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		if ($debug) debugMsg($conditions, '$conditions');
		if ($debug) debugMsg($options, '$options');

		if ($conditions['orgId']) {
			import('model:org');
			$orgInfo = OrgModel::get($conditions['orgId']);

			$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
			$isViewHomeVisit = $isAdmin || in_array($orgInfo->is->socialtype, array('ADMIN','MODERATOR','CM','PHYSIOTHERAPIST'));

			$configGroupViewMemberVisit = false;
		}

		$result = (Object) [
			'count' => 0,
			'conditions' => SG\json_encode($conditions),
			'options' => SG\json_encode($options),
			'query_time' => 0,
		];

		mydb::query('SET @@group_concat_max_len = 4096;');

		if ($conditions['psnId']) mydb::where('s.`pid` = :psnId', ':psnId', $conditions['psnId']);
		if ($conditions['userId']) mydb::where('s.`uid` = :userId', ':userId', $conditions['userId']);
		if ($conditions['reqId']) mydb::where('s.`reqId` = :reqId', ':reqId', $conditions['reqId']);
		if ($conditions['orgId']) {
			mydb::where('(s.`pid` IN (SELECT `psnid` FROM %imed_socialpatient% WHERE `orgid` = :orgid)'
				. ($configGroupViewMemberVisit && $isViewHomeVisit ?
					' OR s.`uid` IN (SELECT `uid` FROM %imed_socialmember% WHERE `orgid` = :orgid OR `orgid` IN (SELECT `orgid` FROM %imed_socialparent% WHERE `parent` = :orgid))'
					 : '').' )',
				':orgid', $conditions['orgId']
			);
		}

		mydb::value('$ORDER$', $options->order);
		mydb::value('$SORT$', $options->sort);
		mydb::value('$LIMIT$', $options->items == '*' ? '' : 'LIMIT '.$options->start.','.$options->items);

		$stmt = 'SELECT
			  s.`seq` `seqId`, s.`pid` `psnId`
			, s.*
			, s.`rx` `visitDetail`
			, cs.`name` `servName`
			, u.`username`, u.`name` `ownerName`
			, CONCAT(p.`name`," ",p.`lname`) `patient_name`
			, b.`score`
			, COUNT(DISTINCT photo.`fid`) `photoCount`
			, GROUP_CONCAT(DISTINCT CONCAT(photo.`fid`,"|",photo.`file`) SEPARATOR ", ") `photos`
			, COUNT(DISTINCT need.`needid`) `needCount`
			, GROUP_CONCAT(DISTINCT need.`needid`) `needItems`
			FROM %imed_service% s
				LEFT JOIN %imed_code_serv% cs USING(`servId`)
				LEFT JOIN %users% u USING (`uid`)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
				LEFT JOIN %imed_barthel% b USING(`seq`)
				LEFT JOIN %imed_files% photo ON photo.`seq` = s.`seq` AND `type` = "photo"
				LEFT JOIN %imed_need% need ON need.`seq` = s.`seq`
			%WHERE%
			GROUP BY `seq`
			ORDER BY $ORDER$ $SORT$
			$LIMIT$
			';

		$result->items = mydb::select($stmt)->items;
		$result->count = count($result->items);
		$result->query_time = mydb()->_last_query_time;

		if ($debug) debugMsg(nl2br(mydb()->_query));

		if ($options->debugResult) debugMsg($result, '$result');

		return $result;
	}

	// Call firebase function visitAdd
	public static function firebaseAdded($psnId, $seqId, $options = '{}') {
		$firebaseCfg = cfg('firebase');
		$firebase = new Firebase('sg-imed', $firebaseCfg['visit']);
		$dataFB = [
			'refDb' => $firebaseCfg['visit'],
			'psnid' => intval($psnId),
			'uid' => i()->uid,
			'seq' => intval($seqId),
			'token' => 'Adse#4fsd',
			'time' => array('.sv' => 'timestamp'),
			'changed' => 0,
		];
		$funcResult = $firebase->functions('visitAdd',$dataFB);
		return $funcResult;
	}

	// Call firebase function visitUpdate
	public static function firebaseChanged($psnId, $seqId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (!cfg('imed.visit.realtime.change.update')) return;

		$firebaseCfg = cfg('firebase');
		$firebase = new Firebase('sg-imed', $firebaseCfg['visit']);
		$dataFB = [
			'refDb' => $firebaseCfg['visit'],
			'psnid' => intval($psnId),
			'seq' => intval($seqId),
			'changed' => ['.sv' => 'timestamp'],
		];

		$funcResult = $firebase->functions('visitUpdate', $dataFB);

		return $funcResult;
	}

}
?>