<?php
/**
* Project Organization Home
*
* @param Object $self
* @return String
*/
function project_org_view($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId, '{initTemplate:true}');
	$orgId = $orgInfo->orgid;

	$ret = '';

	R::View('project.toolbar', $self, $orgInfo->name, 'org', $orgInfo);


	$isEdit = $orgInfo->info->isEdit || $orgInfo->info->isAdmin;
	$isEditTrainer = $orgInfo->info->isAdmin || in_array($orgInfo->officers[i()->uid], array('ADMIN','TRAINER'));
	$isTrainer = in_array($orgInfo->officers[i()->uid],array('TRAINER'));
	$isAddPopulation = $isEdit && in_array(date('m'),array('07','08','09'));

	$ret = '';

	// Graph
	$graph=array(
						'year-project'=>array(),
						);

	$stmt='SELECT
					  COUNT(IF(p.`project_status`=1,1,NULL)) `activeProject`
					, COUNT(*) `totalProject`
					, SUM(IF(p.`project_status`=1,p.`budget`,0)) `activeBudget`
					, SUM(p.`budget`) `totalBudget`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE p.`prtype`="โครงการ" AND t.`orgid`=:orgid
					LIMIT 1';
	$projectRs=mydb::select($stmt,':orgid',$orgInfo->orgid);
	//$ret .= print_o($projectRs,'$projectRs');

	if ($projectRs->totalProject==0 && $orgInfo->isAdmin) {
		$ret .= '<div class="iconset" style="text-align:right; margin:20px 0;"><a class="btn sg-action" href="'.url('project/fund/'.$orgId.'/info/delete').'" data-rel="none" data-callback="'.url('project/fund').'" data-confirm="คำเตือน : การลบกองทุนจะทำการลบข้อมูลกองทุนและข้อมูลอื่น ๆ ที่เกี่ยวข้องกับกองทุน รวมทั้งข้อมูลการเงิน , ข้อมูลโครงการและกิจกรรม. ท่านต้องการลบกองทุนจริงหรือไม่? กรุณายืนยัน?"><i class="icon -delete"></i> ลบกองทุน !!!</a> '.$menu.'</div>';
	}

	$ret .= '<div class="project-card -project">';
	$ret .= '<h3>โครงการ/กิจกรรม</h3>';
	$stmt='SELECT
					  p.`tpid`, p.`pryear`, t.`title`,p.`budget`
					, SUM(pd.`amount`) `totalPaid`
					, (SELECT SUM(rt.`num1`) FROM %project_tr% rt WHERE rt.`tpid`=p.`tpid` AND rt.`formid`="info" AND `part`="moneyback") `totalMoneyBack`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %project_paiddoc% pd USING(`tpid`)
				WHERE p.`prtype`="โครงการ" AND t.`orgid`=:orgid AND p.`project_status`=1
				GROUP BY `tpid`
				ORDER BY `tpid` DESC;
				-- {sum:"budget,totalPaid,totalMoneyBack"}';
	$dbs=mydb::select($stmt,':orgid',$orgInfo->orgid);
	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->thead=array('amt -year'=>'ปี','ชื่อโครงการ','amt -budget'=>'งบประมาณ','amt -balance'=>'งบคงเหลือ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->pryear+543,
			'<a href="'.url('paper/'.$rs->tpid).'">'.SG\getFirst($rs->title,'===ยังไม่ระบุชื่อโครงการ===').'</a>',
			number_format($rs->budget,2),
			number_format($rs->budget-$rs->totalPaid+$rs->totalMoneyBack,2),
		);
	}

	$tables->tfoot[]=array(
		'',
		'รวม',
		number_format($dbs->sum->budget,2),
		number_format($dbs->sum->budget-$dbs->sum->totalPaid+$dbs->sum->totalMoneyBack,2)
	);

	$ret .= $tables->build();
	$ret .= '<p>จำนวน '.$dbs->_num_rows.' โครงการ ';

	$ret .= '<a class="btn" href="'.url('project/fund/'.$orgInfo->orgid.'/follow/all').'"><i class="icon -list"></i><span>โครงการทั้งหมด</span></a></p>';
	$ret .= '</div>';

	$ret .= '<div class="project-card -summary">';
	$tables = new Table();
	$tables->rows[]=array('โครงการ',number_format($projectRs->activeProject).'/'.number_format($projectRs->totalProject).' โครงการ');
	$tables->rows[]=array('งบประมาณ',number_format($projectRs->activeBudget,2).'/'.number_format($projectRs->totalBudget,2).' บาท');
	//$tables->rows[]=array('งบคงเหลือ','xxx,xxx/xxxxx,xxx บาท');
	$ret .= $tables->build();

	$graphYear = new Table();
	$graphYearProject = new Table();
	$stmt='SELECT
					  p.`pryear`
					, COUNT(*) `totalProject`
					, SUM(p.`budget`) `totalBudget`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE t.`orgid`=:orgid
					GROUP BY `pryear`
					ORDER BY `pryear` ASC';
	$dbs=mydb::select($stmt,':orgid',$orgInfo->orgid);
	//$ret .= print_o($dbs,'$dbs');
	foreach ($dbs->items as $rs) {
		$graphYear->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject,'number:Budget'=>$rs->totalBudget);
		$graphYearProject->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject);
	}
	//$ret .= print_o($graphYear,'$graphYear');
	$ret .= '<div id="year-project" class="sg-chart -project" data-chart-type="col" data-series="2"><h3>จำนวนโครงการ/งบประมาณแต่ละปี</h3>'._NL.$graphYear->build().'</div>';
	//$ret .= '<div id="year-budget" class="sg-chart -project" data-chart-type="col"><h3>จำนวนโครงการ/งบประมาณแต่ละปี</h3>'._NL.$graphYearProject->build().'</div>';
	$ret .= '</div><!-- project-card -->';
	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$ret .= '<div class="project-card -area">';
	if ($isEdit) {
		$ui=new ui();
		$ui->add('<a href="'.url('project/fund/'.$orgId.'/info.area').'"><i class="icon -edit"></i></a>');
		$ret .= $ui->build();
	}
	$ret .= '<h3>รายละเอียดองค์กร</h3>';
	$ret .= '<p>ชื่อองค์กร : <b>'.$orgInfo->name.'</b><br />';
	$ret .= 'ที่อยู่ : <b>'.$orgInfo->orgaddr.' '.$orgInfo->orgzip.'</b><br />';
	$ret .= 'โทรศัพท์ : <b>'.$orgInfo->orgphone.($orgInfo->orgfax?' โทรสาร: '.$orgInfo->orgfax:'').'</b><br />';
	$ret .= 'อีเมล์ : <b>'.$orgInfo->orgemail.'</b><br />';
	//$ret .= 'จำนวนประชากร : <b>'.number_format($orgInfo->population).'</b> คน เมื่อ<b>วันที่ 1 กรกฎาคม 2559</b></p>';
	$ret .= '</div>';



	$ret .= '<div class="project-card -member">';

	$stmt='SELECT u.`uid`, u.`username`, u.`name`, UPPER(tu.`membership`) `membership`
				FROM %org_officer% tu
					LEFT JOIN %users% u USING(`uid`)
				WHERE tu.`orgid`=:orgid AND u.`status`="enable" AND tu.`membership`!="MEMBER"
				ORDER BY FIELD(tu.`membership`,"ADMIN","Owner","OFFICER","Trainer","Manager") ASC';
	$member=mydb::select($stmt,':orgid',$orgId);
	//$ret .= print_o($member,'$member');

	$ret .= '<h3>เจ้าหน้าที่องค์กร</h3>';
	$ret .= '<div id="officer">';
	$tables = new Table();
	foreach ($member->items as $mrs) {
		$tables->rows[]=array(
			'<img src="'.model::user_photo($mrs->username).'" width="32" height="32" alt="'.htmlspecialchars($mrs->name).'" title="'.htmlspecialchars($mrs->name).'" />',
			($isViewMemberProfile?'<span><a class="sg-action" href="'.url('project/fund/'.$orgId.'/trainer/'.$mrs->uid).'" data-rel="box">':'').$mrs->name.($isViewMemberProfile?'</a></span>':''),
			$mrs->membership,
			$isEditTrainer && in_array($mrs->membership,array('TRAINER','MEMBER')) ?'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/member.remove/'.$mrs->uid).'" title="ลบ" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?"><i class="icon -cancel"></i></a> ':''
		);
	}
	$ret .= $tables->build();

	$ret .= '</div><!-- officer -->';

	if ($isEditTrainer || $isTrainer) {
		$ret .= '<p align="right"><a class="btn -primary" href="'.url('project/org/'.$orgId.'/member').'"><i class="icon -people -white"></i>จัดการสมาชิกองค์กร</a></p>';
	}

	$ret .= '</div><!-- project-card -member -->';


	$ret .= '<br clear="all" />';

	//$ret .= print_o($orgInfo);

	$ret .= '<style type="text/css">
	</style>';
	return $ret;
}
?>