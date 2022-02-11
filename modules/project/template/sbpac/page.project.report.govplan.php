<?php

	/**
	 * Send Document Report
	 *
	 */
	function project_report_govplan($self) {
		$self->theme->title='รายงานโครงการแยกตามความสอดคล้องตามแผนปฏิบัติการแก้ไขปัญหาและพัฒนาของรัฐบาล';

		$relId=post('rel');
		$year=SG\getFirst(post('y'));
		$province=post('p');

		if (!$relId) {
			$stmt='SELECT t.`title`, tr.`part`, tg.`tid` relId, tg.`name` `relName`, tg.`taggroup`,
					COUNT(*) amt, SUM(`budget`) `totalBudget`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %project_tr% tr ON tr.`tpid`=t.`tpid` AND tr.`formid`="info" AND tr.`part`="rel"
					LEFT JOIN %tag% tg ON tg.`tid`=tr.`parent`
				WHERE `taggroup`="project:rel-govplan"
				GROUP BY `taggroup`,tr.`parent`';
			$dbs=mydb::select($stmt);

			$tables = new Table();
			$tables->thead=array('กลุ่มภารกิจ', 'amt projects'=>'โครงการ','money budgets'=>'งบประมาณ(บาท)');
			foreach ($dbs->items as $rs) {
				$departmentName=SG\getFirst($rs->relName,'ไม่ระบุ');
				$tables->rows[]=array('<a href="'.url('project/report/govplan',array('rel'=>$rs->relId)).'" class="sg-action" data-rel="#info">'.$departmentName.'</a>',$rs->amt,number_format($rs->totalBudget,2));
				$totalProjects+=$rs->amt;
				$totalBudgets+=$rs->totalBudget;
			}
			$tables->rows[]=array('<strong>รวม</strong>','<strong>'.$totalProjects.'</strong>','<strong>'.number_format($totalBudgets,2).'</strong>');
			
			$ret .= $tables->build();

			$ret.='<div id="info"></div>';
			return $ret;
		}

		/*
		$ui=new ui();
		$dbs=mydb::select('SELECT DISTINCT cop.`provid`, `provname` FROM %project_prov% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`provid` ORDER BY CONVERT(`provname` USING tis620) ASC');

		$ret.='<form method="get" action="'.url('project/report/govplan').'"><input type="hidden" name="rel" value="'.$relId.'" /><input type="hidden" name="year" value="'.$year.'" />';
		$ret.='<select name="p" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
		foreach ($dbs->items as $rs) {
			$ret.='<option value="'.$rs->provid.'"'.($rs->provid==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$ret.='</select>';
		$ret.=' <select name="org" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกหน่วยงาน</option>';
		foreach (mydb::select('SELECT DISTINCT `orgid`,`name` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %db_org% o USING(`orgid`) WHERE o.`orgid` IS NOT NULL ORDER BY o.`sector` ASC, CONVERT(`name` USING tis620) ASC')->items as $item) {
		$ret.='<option value="'.$item->orgid.'"'.(post('org')==$item->orgid?' selected="selected"':'').'>'.$item->name.'</option>';
		}
		$ret.='</select>';
		$ret.='</form>';
		$ui->add($provSelect);
		$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';
		*/

		mydb::where('`taggroup` = "project:rel-govplan"');
		if ($relId) mydb::where('tg.`tid` = :relId',':relId',$relId);
		if (post('org')) mydb::where('t.`orgid` = :orgid',':orgid',post('org'));
		$stmt='SELECT *, o.`name` `orgName`
			FROM %project% project
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_tr% tr ON tr.`tpid`=t.`tpid` AND tr.`formid`="info" AND tr.`part`="rel"
				LEFT JOIN %tag% tg ON tg.`tid`=tr.`parent`
			%WHERE%
			ORDER BY CONVERT(t.`title` USING tis620) ASC
		';

		$dbs= mydb::select($stmt);

		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อโครงการ', 'หน่วยงาน', 'money budgets'=>'งบประมาณ(บาท)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
				$rs->orgName,
				number_format($rs->budget,2)
			);
			$totalBudgets+=$rs->budget;
		}
		$tables->rows[]=array('','<strong>รวมงบประมาณ</strong>','','<strong>'.number_format($totalBudgets,2).'</strong>');

		$ret .= $tables->build();

		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}
?>