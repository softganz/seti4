<?php
/**
* Project Org Following
*
* @param Object $self
* @param Int $var
* @return String
*/

function project_org_follow($self, $orgId = NULL, $action = NULL, $trid = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId, '{initTemplate:true}');
	$orgId = $orgInfo->orgid;

	$getProv = post('prov');
	$ret = '';

	if (!$orgInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$isAdmin = user_access('administer projects') || $orgInfo->isAdmin;
	$isOwner = $orgInfo->isOwner;
	$isEdit = $isAdmin || $isOwner || (cfg('project.trainer.canaddproject') && $orgInfo->isTrainer);
	$isCreatable = $isAdmin || array_key_exists(i()->uid, $orgInfo->officers);

	R::view('project.toolbar',$self,'ติดตามและประเมินผลโครงการ @'.$orgInfo->name,'org',$orgInfo);


	$ret='';

	$isCreateProject = $isCreatable
		&&  in_array('project/org/follow', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

	if ($isCreateProject) {
		$ret .= '<nav class="nav btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/create',array('orgid'=>$orgId)).'" title="Create New Project" data-rel="box" data-width="480"><i class="icon -material">add</i></a></nav>';
	}

	mydb::where('p.`prtype` = "โครงการ" AND t.`orgid` = :orgid',':orgid',$orgId);
	if ($action == 'end') mydb::where('p.`project_status` != :status',':status',1);
	else if ($action == 'all') ;
	else mydb::where('p.`project_status` = :status',':status',1);

	if ($getProv) mydb::where('p.`changwat` = :changwat', ':changwat', $getProv);

	$stmt = 'SELECT
			  t.`tpid`, t.`title`
			, p.`pryear`, p.`budget`, p.`project_status`, p.`date_approve`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
		%WHERE%
		ORDER BY `tpid` DESC';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	$tables = new Table();
	$tables->addClass('-projectlist');
	$tables->id = 'project-fund-list';
	$tables->thead = array('date' => 'วันที่อนุมัติ','โครงการ/กิจกรรม','amt' => 'งบประมาณ','-icons -c1' => '');

	$prevrs = NULL;
	$totalBudget = 0;
	foreach ($dbs->items as $rs) {
		$statusIcon = '<i class="icon -material -green" title="'.$rs->project_status.'">done<i>';
		if ($rs->project_status == 'ดำเนินการเสร็จสิ้น') {
			$statusIcon = '<i class="icon -material -green" title="'.$rs->project_status.'">done_all<i>';
		} else if ($rs->project_status == 'ยุติโครงการ') {
			$statusIcon = '<i class="icon -material -red" title="'.$rs->project_status.'">cancel<i>';
		} else if ($rs->project_status == 'ระงับโครงการ') {
			$statusIcon = '<i class="icon -material" title="'.$rs->project_status.'">block<i>';
		}
		//'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น','ยุติโครงการ','ระงับโครงการ'
		$tables->rows[] = array(
			($rs->date_approve?sg_date($rs->date_approve,'ว ดด ปปปป'):'??????')
			.'<br />('.($rs->pryear+543).')',
			'<a href="'.url('paper/'.$rs->tpid).'">'.SG\getFirst($rs->title,'***** ยังไม่ระบุชื่อโครงการ *****').'</a>',
			number_format($rs->budget,2),
			$statusIcon,
		);

		$totalBudget += $rs->budget;
		$prevrs = $rs;
	}
	$tables->tfoot[] = array('','รวม',number_format($totalBudget,2),'');
	$ret .= $tables->build();
	if ($isEdit) $ret .= '</form>';

	//$ret.=print_o($orgInfo,'$orgInfo');

	return $ret;
	}
?>