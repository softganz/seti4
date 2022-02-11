<?php

/**
 * Send Document Report
 *
 */
function project_report_budget($self) {
	R::View('project.toolbar', $self, 'รายชื่อโครงการแยกตามงบการเงิน', 'report');

	$year=SG\getFirst(post('y'));
	$province=post('p');

	if (post('bg')) {
		$back=SG\getFirst(post('bk'),0);
		$backArray=explode(':', $back);
		array_pop($backArray);
		$backStr=implode(':',$backArray);
		$backPr=array_pop($backArray);
		if ($backPr!='') $ret.='<a class="sg-action" href="'.url('project/report/budget',array('pr'=>$backPr,'bg'=>post('bg'),'bk'=>$backStr)).'" data-rel="box">Back</a>';
		//$ret.='<br />back='.$back.'<br />backPr='.$backPr.'<br />backStr='.$backStr.'<br />'.print_o($backArray,'$backArray');

		if (post('pr')>0) {
			$mrs=mydb::select('SELECT t.`title` FROM %topic% t WHERE t.`tpid`=:tpid LIMIT 1',':tpid',post('pr'));
			$ret.='<h3>แผนงาน/ชุดโครงการ/โครงการภายใต้ "'.$mrs->title.'"</h3>';
		}
		$stmt = 'SELECT tp.*, t.`title`, p.`prtype`,
				(SELECT COUNT(*) FROM %topic_parent% WHERE `parent`=tp.`tpid`) subProject
			FROM %topic_parent% tp
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project% p USING(`tpid`)
			WHERE tp.`parent`=:parent AND tp.`bdgroup`=:bdgroup
			ORDER BY CONVERT(`title` USING tis620) ASC';
		$dbs=mydb::select($stmt,':parent',post('pr'), ':bdgroup',post('bg'));

		$tables = new Table();
		$tables->thead=array('no'=>'','แผนงาน/ชุดโครงการ/โครงการ','amt'=>'โครงการย่อย','money'=>'งบประมาณ(บาท)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				($rs->subProject==0?'<a href="'.url('paper/'.$rs->tpid).'">':'<a class="sg-action" href="'.url('project/report/budget',array('pr'=>$rs->tpid,'bg'=>$rs->bdgroup,'bk'=>$back.':'.$rs->tpid)).'" data-rel="box" data-width="50%" data-max-width="60%">').SG\getFirst($rs->title,'???').'</a><br />('.$rs->prtype.')',
				$rs->subProject==0?'-':$rs->subProject,
				number_format($rs->budget,2)
			);
			$totalBudget+=$rs->budget;
		}
		$tables->tfoot[]=array('','รวมงบประมาณ','',number_format($totalBudget,2));

		$ret .= $tables->build();

		//$ret.=print_o($dbs,'$dbs');
		return $ret;
	}

	$stmt='SELECT
			tg.`tid`, tg.`name`, tg.`process`, p.`parent`
			, IF(p.`tpid` IS NULL,0,COUNT(*)) amt
			, SUM(`budget`) `totalBudget`
		FROM %tag% tg
			LEFT JOIN %topic_parent% p ON p.`bdgroup`=tg.`tid`
		WHERE tg.`taggroup`="project:bdgroup" AND p.`parent`=0
		GROUP BY tg.`tid`';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('ชุดงบประมาณโครงการ', 'amt projects'=>'โครงการ','money budgets'=>'งบประมาณ');
	foreach ($dbs->items as $rs) {
		$departmentName=SG\getFirst($rs->name,'ไม่ระบุ');
		if ($rs->process===0) {
			$tables->rows[]=array('<strong>'.$departmentName.'</strong>','','');
			continue;
		}
		$tables->rows[]=array(
			'<a class="sg-action" href="'.url('project/report/budget',array('pr'=>$rs->parent,'bg'=>$rs->tid)).'" data-rel="box">'.$departmentName.'</a>',
			$rs->amt,
			number_format($rs->totalBudget,2)
		);
		$totalProjects+=$rs->amt;
		$totalBudgets+=$rs->totalBudget;
	}
	$tables->rows[]=array('<strong>รวม</strong>','<strong>'.$totalProjects.'</strong>','<strong>'.number_format($totalBudgets,2).'</strong>');

	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>