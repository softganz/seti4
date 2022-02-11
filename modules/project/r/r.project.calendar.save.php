<?php
/**
 * Get project activity by tpid
 * @param Object $projectInfo
 * @param Object $data
 * @param Object $options
 * @return Data Set
 */
function r_project_calendar_save($projectInfo, $data = NULL, $options = '{}') {
	$tpid = $projectInfo->tpid;
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	//debugMsg($options,'$options');

	if (empty($tpid)) return;

	if ($debug) debugMsg($data, '$data');


	$result = (Object) [
		'calid' => NULL,
		'refid' => NULL,
		'data' => $data,
		'_query' => [],
	];

	$error = false;

	$data->tpid = $tpid;
	$data->calid = empty($data->calid) ? NULL : $data->calid;
	$data->isNewCalendar = empty($data->calid);
	$data->owner = SG\getFirst(i()->uid, NULL);
	if (empty($data->title)) $data->title = '';
	if (empty($data->detail)) $data->detail = '';

	// Change BC to DC on year > 2500
	$data->DCfrom_date = $data->from_date ? sg_date($data->from_date, 'Y-m-d') : date('Y-m-d');
	$data->DCto_date = $data->to_date ? sg_date($data->to_date, 'Y-m-d') : $data->DCfrom_date;

	if (empty($data->from_time)) $data->from_time = NULL;
	if (empty($data->to_time)) $data->to_time = NULL;
	if (empty($data->latlng)) $data->latlng = NULL;

	$data->ip = ip2long(GetEnv('REMOTE_ADDR'));
	$data->created_date = 'func.NOW()';
	$data->category = SG\getFirst($data->category, NULL);
	$data->reminder = SG\getFirst($data->reminder, 'no');
	$data->repeat = SG\getFirst($data->repeat, 'no');
	$data->privacy = SG\getFirst($data->privacy, 'public');

	if (empty($data->location)) $data->location = NULL;
	$address = SG\explode_address($data->location);
	$data->changwat = substr($data->areacode, 0, 2);
	$data->ampur = substr($data->areacode, 2, 2);
	$data->tambon = substr($data->areacode, 4, 2);
	$data->village = $address['village'] ? sprintf('%02d', $address['village']) : '';

	$stmt = 'INSERT INTO %calendar%
			(`id`, `tpid`, `owner`, `privacy`, `category`, `title`, `location`, `latlng`, `village`, `tambon`, `ampur`, `changwat`, `from_date`, `from_time`, `to_date`, `to_time`, `detail`, `reminder`, `repeat`, `ip`, `created_date`)
		VALUES
			(:calid, :tpid, :owner, :privacy, :category, :title, :location, :latlng, :village, :tambon, :ampur, :changwat, :DCfrom_date, :from_time, :DCto_date, :to_time, :detail, :reminder, :repeat, :ip, :created_date)
		ON DUPLICATE KEY UPDATE
			  `title` = :title
			, `location` = :location, `latlng` = :latlng
			, `village` = :village, `tambon` = :tambon
			, `ampur` = :ampur, `changwat` = :changwat
			, `from_date` = :DCfrom_date
			, `from_time` = :from_time
			, `to_date` = :DCto_date
			, `to_time` = :to_time
			, `detail` = :detail';

	mydb::query($stmt, $data);

	$result->_query[] = mydb()->_query;

	if ($debug) debugMsg(mydb()->_query);

	if (mydb()->_error) return $result;


	if (empty($data->calid)) $data->calid = mydb()->insert_id;
	$result->calid = $data->calid;




	// Create Project Activity on project_tr
	$activity = (Object) [
		'tpid' =>  $tpid,
		'activityid' => empty($data->activityid) ? NULL : $data->activityid,
		'parent' => $data->parent == 'group' ? NULL : SG\getFirst($data->parent, NULL),
		'tagname' => $data->parent == 'group' ? 'group' : NULL,
		'calid' => $data->calid,
		'serieNo' => SG\getFirst($data->serieNo),
		'uid' => i()->uid,
		'formid' => 'info',
		'part' => 'activity',
		'from_date' => $data->DCfrom_date,
		'to_date' => $data->DCto_date,
		'budget' => sg_strip_money($data->budget),
		'targetpreset' => sg_strip_money($data->targetpreset),
		'title' => $data->title,
		'detail' => $data->detail,
		'outputoutcome' => $data->outputoutcome,
		'created' => date('U'),
		'sorder' => mydb::select('SELECT MAX(`sorder`) `lastOrder` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "activity" LIMIT 1', ':tpid', $tpid)->lastOrder + 1,
	];

	$stmt = 'INSERT INTO %project_tr%
		(
		  `trid`, `tpid`, `parent`, `calid`, `sorder`, `uid`
		, `formid`, `part`
		, `tagname`
		, `refcode`
		, `date1`, `date2`
		, `num1`
		, `num2`
		, `detail1`
		, `text1`
		, `text3`
		, `created`
		)
		VALUES
		(
		  :activityid, :tpid, :parent, :calid, :sorder, :uid
		, :formid, :part
		, :tagname
		, :serieNo
		, :from_date, :to_date
		, :budget
		, :targetpreset
		, :title
		, :detail
		, :outputoutcome
		, :created
		)
		ON DUPLICATE KEY UPDATE
		  `tpid` = :tpid
		, `refcode` = :serieNo
		, `detail1` = :title
		, `parent` = :parent
		, `date1` = :from_date
		, `date2` = :to_date
		, `num1` = :budget
		, `num2` = :targetpreset
		, `text1` = :detail
		, `text3` = :outputoutcome
		';

	mydb::query($stmt, $activity);
	$result->_query[] = mydb()->_query;
	if ($debug) debugMsg(mydb()->_query);

	if (!mydb()->_error) $result->refid = mydb()->insert_id;



	// Create Project Activity on table project_activity
	if (empty($data->calowner)) $data->calowner = 1;
	if (empty($data->targetdetail)) $data->targetdetail = '';
	$data->targetpreset = intval(abs(sg_strip_money($data->targetpreset)));
	$data->budget = abs(sg_strip_money($data->budget));
	$data->parent = empty($data->parent) ? NULL : $data->parent;

	$stmt = 'INSERT INTO %project_activity%
		(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
		VALUES
		(:calid, :calowner, :parent, :targetpreset, :targetdetail, :budget)
		ON DUPLICATE KEY UPDATE
		`calowner` = :calowner, `mainact` = :parent, `targetpreset` = :targetpreset, `target` = :targetdetail, `budget` = :budget';

	mydb::query($stmt, $data);

	$result->_query[] = mydb()->_query;
	if ($debug) debugMsg(mydb()->_query);

	if ($data->color) property('calendar:color:'.$data->calid, $data->color);

	model::watch_log(
		'project',
		'Calendar '.($data->isNewCalendar ? 'add' : 'edit'),
		($data->isNewCalendar ? 'เพิ่ม' : 'แก้ไข').'กิจกรรมย่อย หมายเลข '.$data->calid.' : ' .$data->title,
		NULL,
		$data->tpid
	);

	$result->data = $data;

	return $result;

}


























/*

	// OLD PROCESS
	// Can remove


	// Start save data
	// Create new item on calendar when no calid, no $_REQUEST[calid] and not select calendar item from list

	if (empty($data->calid) && $data->activityname) {
		$calendar->tpid=$data->tpid;
		$calendar->owner=$data->uid;
		$calendar->privacy='public';
		$calendar->title=$data->activityname;
		$calendar->from_date=$data->date1;
		$calendar->from_time=$data->detail1;
		$calendar->detail=$data->text2;
		$calendar->ip=ip2long(GetEnv('REMOTE_ADDR'));
		$calendar->created_date=date('Y-m-d H:i:s');
		$stmt='INSERT INTO %calendar% (`tpid`, `owner`, `privacy`, `title`, `from_date`, `from_time`, `detail`, `ip`, `created_date`) VALUES (:tpid, :owner, :privacy, :title, :from_date, :from_time, :detail, :ip, :created_date)';
		mydb::query($stmt,$calendar);

		$result->query[]=mydb()->_query;

		$data->calid=mydb()->insert_id;
	}
	unset($data->detail);

	return $result;


	$data->trid=$post->trid;
	if (empty($data->trid)) $data->trid=NULL;
	$data->tpid=$tpid;
	$data->refid=$post->refid;
	$data->formid='activity';
	$data->part=$post->part;
	$data->action_date=$post->action_date?sg_date($post->action_date,'Y-m-d'):NULL;
	$data->action_time=$post->action_time;
	$data->uid=i()->uid;
	$data->flag=_PROJECT_COMPLETEPORT;
	$data->rate=NULL;
	if (empty($post->rate)) $data->rate=0;
	else if ($post->rate==-1) $data->rate=NULL;
	else $data->rate=$post->rate;
	$data->created=date('U');
	$data->modified=date('U');
	$data->modifyby=i()->uid;


	foreach (array('num1','num2','num3','num4','num5','num6','num7','num8','budget','targetpreset') as $k) {
		if (isset($post->{$k})) $post->{$k}=preg_replace('/[^0-9\.\-]/','',$post->{$k});
	}
	$data->targetjoinamt=abs(intval($post->targetjoinamt));
	$data->targetjoindetail=$post->targetjoindetail;

	$data->title=$post->title;
	$data->objective=$post->objective;

	$data->exp_meed=$post->exp_meed;
	$data->exp_wage=$post->exp_wage;
	$data->exp_supply=$post->exp_supply;
	$data->exp_material=$post->exp_material;
	$data->exp_utilities=$post->exp_utilities;
	$data->exp_other=$post->exp_other;
	$data->exp_total=$post->exp_total;

	$data->real_do=$post->real_do;
	$data->outputoutcome=$post->outputoutcome;

	$result->data=$data;

	//$ret.=print_o($data,'$data');





	$data->exp_meed=$post->exp_meed;
	$data->exp_wage=$post->exp_wage;
	$data->exp_supply=$post->exp_supply;
	$data->exp_material=$post->exp_material;
	$data->exp_utilities=$post->exp_utilities;
	$data->exp_other=$post->exp_other;
	$stmt='INSERT INTO %project_tr%
					(
					`trid`, `tpid`, `refid`
					, `formid`
					, `part`
					, `flag`
					, `uid`
					, `date1`
					, `detail1`
					, `detail3`
					, `rate1`
					, `num1`, `num2`, `num3`, `num4`, `num5`, `num6`, `num7`
					, `num8`
					, `text9`
					, `text2`
					, `text4`
					, `created`
					)
				VALUES
					(
					:trid, :tpid, :refid
					, :formid, :part
					, :flag, :uid
					, :action_date
					, :action_time
					, :objective
					, :rate
					, :exp_meed, :exp_wage, :exp_supply
					, :exp_material, :exp_utilities, :exp_other
					, :exp_total
					, :targetjoinamt
					, :targetjoindetail
					, :real_do
					, :outputoutcome
					, :created
					)
				ON DUPLICATE KEY UPDATE
					  `date1`=:action_date
					, `detail1`=:action_time
					, `detail3`=:objective
					, `rate1`=:rate
					, `num1`=:exp_meed, `num2`=:exp_wage, `num3`=:exp_supply
					, `num4`=:exp_material, `num5`=:exp_utilities, `num6`=:exp_other
					, `num7`=:exp_total
					, `num8`=:targetjoinamt
					, `text9`=:targetjoindetail
					, `text2`=:real_do
					, `text4`=:outputoutcome
					, `modified`=:modified, modifyby=:modifyby
					;';
	mydb::query($stmt,$data);
	$result->querys[]=$stmt;
	$result->querys[]=mydb()->_query;

	$trid=is_null($data->trid)?mydb()->insert_id:$data->trid;








	$stmt='SELECT
					tr.`tpid`, tr.`trid`, tr.`refid`, tr.`parent`, tr.`calid`, tr.`gallery`,
					tr.`formid`, tr.`period`, tr.`part`, tr.`flag`, tr.`uid`,
					c.`title`,
					t.`title` projectTitle,
					tr.`rate1` rate,
					tr.`date1` action_date,
					tr.`detail1` action_time,
					a.`budget`,
					a.`mainact`,
					m.`detail1` mainact_detail,
					a.`targetpreset`,
					a.`target` target,
					tr.`text3` targetPresetDetail,
					tr.`num8` targetjoin,
					tr.`text9` targetjoindetail,
					tr.`detail3` objective,
					tr.`text1` goal_do,
					tr.`text2` real_do,
					m.`text3` `presetOutputOutcome`,
					tr.`text4` real_work,
					tr.`text5` problem,
					tr.`text6` recommendation,
					tr.`text7` support,
					tr.`text8` followerrecommendation,
					tr.`detail2` followername,
					tr.`num1` exp_meed,
					tr.`num2` exp_wage,
					tr.`num3` exp_supply,
					tr.`num4` exp_material,
					tr.`num5` exp_utilities,
					tr.`num6` exp_other,
					tr.`num7` exp_total,
					c.`detail` goal_dox,
					u.`username`, u.`name` ownerName,
					GROUP_CONCAT(DISTINCT p.`fid`, "|" , p.`file`) photos
				FROM
					(
					SELECT *
						FROM %project_tr%
						%WHERE%
					) tr
					LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
					LEFT JOIN %users% u ON u.`uid`=tr.`uid`
					LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
					LEFT JOIN %project_activity% a ON a.`calid`=tr.`calid`
					LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
					LEFT JOIN %topic_files% p
						ON tr.`gallery` IS NOT NULL
						AND p.`tpid`=tr.`tpid`
						AND p.`gallery`=tr.`gallery`
						AND p.`type`="photo"
				GROUP BY tr.`trid`
				ORDER BY '.$order.';
				-- {key:"trid"}';
	//$dbs=mydb::select($stmt);
	//debugMsg('<pre>'.mydb()->_query.'</pre>');
	return $dbs;
}
*/
?>