<?php
/**
* Project Develope Home
*
* @param Object $self
* @return String
*/

function project_develop_home($self) {
	$getYear = post('year');
	$getChangwat = post('prov');

	$year=post('year');
	$prov=post('prov');

	R::View('project.toolbar',$self,'การพัฒนาโครงการ'.($year?'ของปี '.($year+543):'ของทุกปี'),'develop');

	$form = new Form([
		'id' => 'project-develop',
		'class' => 'form-report',
		'method' => 'get',
		'action' => url('project/develop/list'),
		'children' => [
			'year' => [
				'type' => 'select',
				'value' => $year,
				'options' => ['' => '==ทุกปี=='] + mydb::select(
					'SELECT DISTINCT `pryear`, CONCAT("พ.ศ.",`pryear`+543) `bcyear`
					FROM %project_dev%
					ORDER BY `pryear` ASC;
					-- {key: "pryear", value: "bcyear"}'
				)->items
			],
			'prov' => [
				'type' => 'select',
				'value' => $getChangwat,
				'options' => ['' => '==ทุกจังหวัด=='] + mydb::select(
					'SELECT LEFT(t.`areacode`, 2) `changwat`, `provname`, COUNT(*)
					FROM %topic% t
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
					WHERE t.`type` = "project-develop"
					GROUP BY `changwat`
					HAVING `provname` != ""
					ORDER BY CONVERT(`provname` USING tis620) ASC;
					-- {key: "changwat", value: "provname"}'
				)->items
			],
			'view' => [
				'type' => 'button',
				'value' => 'ดูรายชื่อ',
			],
			'devq' => [
				'type' => 'text',
				'class' => 'sg-autocomplete',
				'value' => $_GET['devq'],
				'placeholder' => 'ค้นหาโครงการพัฒนา',
				'attribute' => [
					'data-query' => url('project/api/develop'),
				],
			],
			'search' => [
				'type' => 'button',
				'value' => '<i class="icon -material">search</i><span class="-hidden">ค้นหา</span>',
			],
		], // children
	]);

	$ret .= $form->build();

	if ($year) mydb::where('d.`pryear` = :year',':year',$year);
	if ($prov) mydb::where('LEFT(t.`areacode`, 2) = :changwat', ':changwat',$prov);
	if (post('q')) mydb::where('t.`title` LIKE :search OR r.`email` LIKE :search', ':search','%'.post('q').'%');

	$stmt = 'SELECT cop.`provid`, cop.`provname`, d.`status`, d.`pryear`
		, COUNT(*) `amt`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
		%WHERE%
		GROUP BY `provid`,`status`
		HAVING `provname` != ""
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
		$tables->rows[$rs->provid][$rs->status]='<a href="'.url('project/develop/list',array('prov'=>$rs->provid,'status'=>$rs->status,'year'=>post('year'))).'">'.$rs->amt.'</a>';
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
		$tables->rows[$key]['total']='<a href="'.url('project/develop/list',array('prov'=>$key,'year'=>$year)).'">'.$row['total'].'</a>';
	}
	foreach ($tables->tfoot[1] as $key=>$value) {
		if (in_array($key, array('prov'))) continue;
		$tables->tfoot[1][$key]=$value>0 ? '<a href="'.url('project/develop/list',array('status'=>$key=='total'?NULL:$key,'year'=>$year)).'">'.$value.'</a>' : '-';
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