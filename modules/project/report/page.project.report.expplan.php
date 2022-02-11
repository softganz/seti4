<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @return String
*/

function project_report_expplan($self) {
	// Data Model
	$getProjectSet = post('prset');
	$prov = post('prov');
	$ampur = post('ampur');
	$year = post('year');
	$fundid = post('fund');
	$status = post('status');

	$yearList = mydb::select('SELECT DISTINCT `pryear` FROM %project% WHERE `prtype`="โครงการ" ORDER BY `pryear` ASC')->lists->text;

	$yearOption = array('' => '==ทุกปี==');
	foreach (explode(',',$yearList) as $item) {
		$yearOption[$item] = 'พ.ศ. '.($item+543);
	}

	$expGroupList = model::get_category('project:expgr','catid');

	// Select project set
	$prsetDbs = mydb::select('SELECT `tpid`,`title`, p.`projectset` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `prtype` IN ("แผนงาน","ชุดโครงการ") AND p.`project_status`="กำลังดำเนินโครงการ" ORDER BY t.`parent` ASC,CONVERT(`title` USING tis620) ASC');

	$projectSetOption = array('' => '==ทุกแผนงาน==');

	foreach ($prsetDbs->items as $item) {
		$projectSetOption[$item->tpid] = ($item->projectset ? '&nbsp;&nbsp;' : '').$item->title;
	}

	$form = new Form(NULL, url('project/report/expplan'), 'project-develop', 'sg-form -sg-flex');
	$form->addData('rel', 'replace:#result');
	$form->addField(
		'prset',
		array(
			'type' => 'select',
			'options' => $projectSetOption,
			'value' => $getProjectSet,
			'attr' => array('style' => 'width: 100px;'),
		)
	);

	if (strpos($yearList,',')) {
		$form->addField(
			'year',
			array(
				'type' => 'select',
				'options' => $yearOption,
				'value' => $year,
			)
		);
	} else {
		$form->addField('year', array('type' => 'hidden', 'value' => $yearList));
	}

	$form->addField(
		'go',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
		)
	);



	// View Model
	$toolbar = new Toolbar($self, 'รายงานงบประมาณ/การจ่ายเงินของโครงการจำแนกตามแผนงาน');
	//R::View('project.toolbar', $self, 'รายงานงบประมาณ/การจ่ายเงินของโครงการจำแนกตามแผนงาน', 'report');
	$mainNav = new Ui();
	$mainNav->add('<a href="'.url('project').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>');
	$mainNav->add('<a href="'.url('project/report').'"><i class="icon -material">assessment</i><span>วิเคราะห์</span></a>');
	$toolbar->addNav('main', $mainNav);
	$toolbar->addNav('form', $form);

	mydb::where('t.`parent` > 0 AND t1.`formid` = "activity" AND t1.`part` = "owner"');

	if ($getProjectSet) {
		$stmt = 'SELECT `tpid` FROM %topic% WHERE `parent` = :parent; -- {reset:false}';
		$childProjectSet = mydb::select($stmt, ':parent', $getProjectSet);

		mydb::where('(t.`parent` = :prset)', ':prset', $getProjectSet, ':child', 'SET:'.$childProjectSet->lists->text);
	} else {
		mydb::where(NULL,':prset',NULL);
	}
	if ($year) mydb::where('p.`pryear`=:year',':year',$year);
	if ($prov) mydb::where('LEFT(t.`areacode`, 2) = :changwat', ':changwat',$prov);
	if ($ampur) mydb::where('p.ampur=:ampur', ':ampur',$ampur);
	if ($fundid) mydb::where('d.fundid=:fundid', ':fundid',$fundid);


	mydb::value('$LABEL$', 'IF(`parent` = :prset, `title`, `projectSetName`)');

	$stmt = 'SELECT
		$LABEL$ `label`
		, a.*
		, SUM(`budget`) totalBudget
		, SUM(`expGr1`) `expGr1`
		, SUM(`expGr2`) `expGr2`
		, SUM(`expGr3`) `expGr3`
		, SUM(`expGr4`) `expGr4`
		, SUM(`expGr5`) `expGr5`
		, SUM(`expGr6`) `expGr6`
		, SUM(`expTotal`) `expTotal`
		FROM (
			SELECT
			  cop.`provname`
			, t.`title`
			, t1.`tpid`, p.`changwat`, p.`ampur`
			, p.`budget`
			, t.`parent`
			, pr.`title` `projectSetName`
			, SUM(t1.`num1`) `expGr1`
			, SUM(t1.`num2`) `expGr2`
			, SUM(t1.`num3`) `expGr3`
			, SUM(t1.`num4`) `expGr4`
			, SUM(t1.`num5`) `expGr5`
			, SUM(t1.`num6`) `expGr6`
			, SUM(t1.`num7`) `expTotal`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %topic% pr ON pr.`tpid` = t.`parent`
				LEFT JOIN %project_tr% t1 ON p.`tpid` = t1.`tpid`
				LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
				LEFT JOIN %co_district% cod ON cod.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			%WHERE%
			GROUP BY `tpid`
			) a
		GROUP BY CONVERT(`label` USING tis620) ASC
		HAVING `expTotal` > 0;
		';

	$dbs = mydb::select($stmt);
	//debugMsg(mydb()->_query);

	$ret .= '<div id="result">';

	$tables = new Table();
	$tables->addClass('project-develop-status');
	$tables->thead['prov']='แผนงาน/โครงการ';
	foreach ($expGroupList as $key=>$value) $tables->thead[$key]=$value;
	$tables->thead['total']='รวมจ่าย';
	$tables->thead['budget']='งบประมาณ';
	$tables->thead['percent']='%';

	$subTotal=$totalBudget=0;
	$totalGr1=$totalGr2=$totalGr3=$totalGr4=$totalGr5=$totalGr6=0;
	foreach ($dbs->items as $rs) {
		$subTotal=$rs->expGr1+$rs->expGr2+$rs->expGr3+$rs->expGr4+$rs->expGr5+$rs->expGr6;

		$label=empty($rs->label)?'N/A':$rs->label;

		if ($getProjectSet && $getProjectSet != $rs->parent) {
			$link = '<a href="'.url('project/report/expplan',array('prset' => $rs->parent, 'year' => $year)).'">';
		} else if ($getProjectSet) {
			$link = '<a href="'.url('project/'.$rs->tpid).'" target="_blank">';
		} else {
			$link = '<a href="'.url('project/report/expplan',array('prset' => $rs->parent, 'year' => $year)).'">';
		}

		$tables->rows[] = array(
			$link.$label.'</a>',
			number_format($rs->expGr1,2).'<span class="percent">('.number_format($rs->expGr1*100/$subTotal,2).'%)</span>',
			number_format($rs->expGr2,2).'<span class="percent">('.number_format($rs->expGr2*100/$subTotal,2).'%)</span>',
			number_format($rs->expGr3,2).'<span class="percent">('.number_format($rs->expGr3*100/$subTotal,2).'%)</span>',
			number_format($rs->expGr4,2).'<span class="percent">('.number_format($rs->expGr4*100/$subTotal,2).'%)</span>',
			number_format($rs->expGr5,2).'<span class="percent">('.number_format($rs->expGr5*100/$subTotal,2).'%)</span>',
			number_format($rs->expGr6,2).'<span class="percent">('.number_format($rs->expGr6*100/$subTotal,2).'%)</span>',
			number_format($subTotal,2),
			number_format($rs->totalBudget,2),
			round($subTotal*100/$rs->totalBudget,2).'%',
			//.($subTotal==$rs->expTotal+0?'':'['.$subTotal.'='.$rs->expTotal.']'),
		);

		$totalGr1 += $rs->expGr1;
		$totalGr2 += $rs->expGr2;
		$totalGr3 += $rs->expGr3;
		$totalGr4 += $rs->expGr4;
		$totalGr5 += $rs->expGr5;
		$totalGr6 += $rs->expGr6;
		$totalBudget += $rs->totalBudget;
	}

	$totalAll = $totalGr1 + $totalGr2 + $totalGr3 + $totalGr4 + $totalGr5 + $totalGr6;

	$tables->tfoot[1] = array(
		'รวม '.$dbs->count().' โครงการ',
		number_format($totalGr1,2),
		number_format($totalGr2,2),
		number_format($totalGr3,2),
		number_format($totalGr4,2),
		number_format($totalGr5,2),
		number_format($totalGr6,2),
		number_format($totalAll,2),
		number_format($totalBudget,2),
		round($totalAll*100/$totalBudget,2).'%',
	);

	$tables->tfoot[2] = array(
		'สัดส่วน(%)',
		round($totalGr1*100/$totalAll,2).'%',
		round($totalGr2*100/$totalAll,2).'%',
		round($totalGr3*100/$totalAll,2).'%',
		round($totalGr4*100/$totalAll,2).'%',
		round($totalGr5*100/$totalAll,2).'%',
		round($totalGr6*100/$totalAll,2).'%',
		round($totalAll*100/$totalAll,2).'%',
		'',
		''
	);

	$ret .= $tables->build();

	$ret .= '<p>หมายเหตุ ร้อยละของแต่ละหมวด คือ ร้อยละค่าใช้จ่ายเมื่อเทียบกับค่าใช้จ่ายของแผนงาน/โครงการ</p>';

	//$ret.='<pre>'.mydb()->_query.'</pre>';
	//$ret.=print_o($dbs,'$dbs');

	head('<script type="text/javascript">
		$(document).on("change","form#project-develop select",function() {
			var $this=$(this)
			if ($this.attr("name")=="prov") $("#input-ampur").val("");
			var para=$this.closest("form").serialize()
			//notify("กำลังโหลด")
			$(this).closest("form").trigger("submit");
			//location.replace(window.location.pathname+"?"+para)
		});
		</script>
		<style type="text/css">
		.item td:nth-child(n+2) {text-align:center;}
		.percent {color:#999;font-size:0.8em;display:block;}
		</style>');
	$ret.='</div><!-- result -->';
	return $ret;
}
?>