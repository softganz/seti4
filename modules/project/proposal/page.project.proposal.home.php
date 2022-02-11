<?php
/**
* Project Develope Home
*
* @param Object $self
* @return String
*/

import('model:project.proposal.php');

function project_proposal_home($self) {
	$getSet = post('set');
	$getYear = post('year');
	$getChangwat = post('prov');
	$getSearch = $getSearch;

	// Init Project Template
	if ($getSet) {
		ProjectProposalModel::get($getSet, '{initTemplate: true}');
		$proposalInfo = (Object) array('info'=>(Object)array('parent' => $getSet));
	}

	R::View('project.toolbar',$self,'การพัฒนาโครงการ'.($getYear ? 'ของปี '.($getYear+543) : 'ของทุกปี'),'proposal', $proposalInfo);



	//<form id="project-develop-search" class="search-box" method="get" action="'.url('project/proposal/list').'" role="search">

	$form = new Form(NULL, url('project/proposal/list'), NULL, '-inlineitem');
	$form->addConfig('method', 'GET');

	$yearOptions = array('' => '==ทุกปี==');

	$getYearList = mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;
	foreach (explode(',',$getYearList) as $item) $yearOptions[$item] = 'พ.ศ. '.($item+543);

	if ($getSet) $form->addField('set',array('type' => 'hidden', 'value' => $getSet));

	$form->addField(
		'year',
		array(
			'type' => 'select',
			'options' => $yearOptions,
			'value' => $getYear,
		)
	);

	$provOptions = array('' => '==ทุกจังหวัด==');
	$getChangwatDb = mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project-develop" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($getChangwatDb->items as $item) $provOptions[$item->changwat] = $item->provname;

	$form->addField(
		'prov',
		array(
			'type' => 'select',
			'options' => $provOptions,
			'value' => $getChangwat,
		)
	);

	$form->addField(
		'searchdev',
		array(
			'type' => 'text',
			'placeholder' => 'ค้นหาโครงการพัฒนา',
		)
	);

	/*
	$form->addField(
		'status',
		array(
			'type' => 'select',
			'options' => $statusOptions,
			'value' => $getStatus,
		)
	);
	*/

	$form->addField(
		'go',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">search</i><span>ดูรายชื่อ</span>',
		)
	);

	$ret.='<nav class="nav -page">'.$form->build().'</nav>';



	if ($getSet) mydb::where('t.`parent` = :parent', ':parent', $getSet);
	if ($getYear) mydb::where('d.`pryear` = :year',':year', $getYear);
	if ($getChangwat) mydb::where('t.changwat = :changwat', ':changwat', $getChangwat);
	if ($getSearch) mydb::where('t.`title` LIKE :search', ':search','%'.$getSearch.'%');

	$stmt = 'SELECT
		cop.`provid`
		, cop.`provname`
		, d.`status`
		, d.`pryear`
		, COUNT(*) `amt`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
		%WHERE%
		GROUP BY `provid`,`status`
		ORDER BY `provname` ASC, `status` ASC';

	$dbs = mydb::select($stmt);


	$tables = new Table();
	$tables->addClass('project-develop-status');
	$tables->thead['prov']='จังหวัด';
	$tables->tfoot[1]['prov']='รวม';
	foreach (project_base::$statusList as $key=>$value) {
		$tables->thead[$key]=$value;
		$tables->tfoot[1][$key]=0;
	}
	$tables->thead['total']='รวม';
	$tables->tfoot[1]['total']=0;

	foreach ($dbs->items as $rs) {
		$data[$rs->provid][$rs->status]=$rs;
	}

	foreach ($data as $rs) {
		$rs=reset($rs);
		unset($row);
		$row['prov']=$rs->provname;
		foreach (project_base::$statusList as $key=>$value) $row[$key]='-';
		$row['total']=0;
		$tables->rows[$rs->provid]=$row;
	}
	foreach ($dbs->items as $rs) {
		$tables->rows[$rs->provid][$rs->status] = '<a href="'.url('project/proposal/list',array('set' => $getSet, 'prov' => $rs->provid, 'status' => $rs->status, 'year' => $getYear)).'">'.$rs->amt.'</a>';
		$tables->rows[$rs->provid]['total']+=$rs->amt;
		$tables->tfoot[1][$rs->status]+=$rs->amt;
		$tables->tfoot[1]['total']+=$rs->amt;
	}

	$total=$tables->tfoot[1]['total'];
	$total_reg=$tables->tfoot[1][-1];
	$total_develop=$tables->tfoot[1][1];
	$total_2=$tables->tfoot[1][2];
	$total_3=$tables->tfoot[1][3];
	$total_pass=$tables->tfoot[1][5];
	$total_notpass=$tables->tfoot[1][8];
	$total_cancel=$tables->tfoot[1][9];
	$total_process=$tables->tfoot[1][10];

	foreach ($tables->rows as $key=>$row) {
		$tables->rows[$key]['total']='<a href="'.url('project/proposal/list',array('set'=>$getSet, 'prov' => $key, 'year' => $getYear)).'">'.$row['total'].'</a>';
	}
	foreach ($tables->tfoot[1] as $key=>$value) {
		if (in_array($key, array('prov'))) continue;
		$tables->tfoot[1][$key]=$value > 0 ? '<a href="'.url('project/proposal/list',array('set' => $getSet, 'status' => $key == 'total' ? NULL : $key, 'year' => $getYear)).'">'.$value.'</a>' : '-';
	}
	$tables->tfoot[2]=array('',
							round($total_reg*100/$total).'%',
							round($total_develop*100/$total).'%',
							round($total_2*100/$total).'%',
							round($total_3*100/$total).'%',
							round($total_pass*100/$total).'%',
							round($total_notpass*100/$total).'%',
							round($total_cancel*100/$total).'%',
							round($total_process*100/$total).'%',
							'100%',
							);


	$ret .= $tables->build();

	$ret.='<p>หมายเหตุ <ul><li>คลิกบนตัวเลขจำนวนโครงการในตารางเพื่อดูรายชื่อโครงการ</li></ul></p>';
//		$ret.=print_o($tables->tfoot,'$tfoot');
//		$ret.=print_o($tables,'$tables');

//		$ret.=print_o($dbs,'$dbs');
	head('<script type="text/javascript">
		$(document).on("change","form#project-develop select",function() {
			var $this=$(this)
			var para=$this.closest("form").serialize()
			notify("กำลังโหลด")
			location.replace(window.location.pathname+"?"+para)
		});
		</script>');
	$ret.='<style type="text/css">
	.item td:nth-child(n+2) {text-align:center;}
	</style>';
	return $ret;
}
?>