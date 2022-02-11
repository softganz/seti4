<?php
function admin_content_summary($self, $year = NULL, $month = NULL) {
	$ret='<ul class="tabs tabs-secondary">
<li><a href="'.url('admin/content/topic').'">Content</a></li>
<li class="-active"><a href="'.url('admin/content/summary').'">Summary</a></li>
</ul>';
	$sql='SELECT
			DATE_FORMAT(created,"%Y") AS `year`
		, COUNT(*) AS `topics`
		FROM %topic%
		GROUP BY `year`
		ORDER BY `created` ASC;
		-- {sum:"topics"}
		';
	$dbs=mydb::select($sql);

	$tables = new Table();
	$tables->thead=array('date'=>'ปี พศ.','amt'=>'จำนวนหัวข้อ');
	foreach ($dbs->items as $item) {
		$tables->rows[]=array(
			'<a href="'.url('admin/content/summary/'.$item->year).'">'.sg_date($item->year.'-01-01','ปปปป').'</a>',
			$item->topics
		);
	}
	$tables->tfoot[]=array('รวม',$dbs->sum->topics);
	$ret.=$tables->build();

	$ret.='<div id="content-report-show">';
	if ($year) {
		$sql='SELECT
			  DATE_FORMAT(created,"%Y-%m") AS `month`
			, DATE_FORMAT(created,"%m") AS `monthno`
			, COUNT(*) AS `topics`
			FROM %topic%
			WHERE `created` BETWEEN :beginyear AND :endyear
			GROUP BY `month`
			ORDER BY `created` ASC;
			-- {sum:"topics"}
			';
		$dbs=mydb::select($sql,':beginyear',$year.'-01-01', ':endyear',$year.'-12-31');
		$tables = new Table();
		$tables->thead=array('date'=>'เดือน-ปี พศ.','amt'=>'จำนวนหัวข้อ');
		foreach ($dbs->items as $item) {
			$tables->rows[]=array(
				'<a href="'.url('admin/content/summary/'.$year.'/'.$item->monthno).'">'.sg_date($item->month.'-01','ดด ปปปป').'</a>',
				$item->topics
			);
		}
		$tables->tfoot[]=array('รวม',$dbs->sum->topics);
		$ret.=$tables->build();
	}

	if ($month) {
		$sql='SELECT
				t.`tpid`, t.`title`, t.`created`, t.`uid`
			, IF(t.`poster` IS NOT NULL,t.`poster`,u.`name`) AS owner
			, t.`view`, t.`reply`
			FROM %topic% t
				LEFT JOIN %users% u USING(`uid`)
			WHERE t.`created` BETWEEN :startmonth AND :endmonth
			ORDER BY t.`created` ASC';
		$topic_summary=mydb::select($sql,':startmonth',$year.'-'.$month.'-01', ':endmonth',$year.'-'.$month.'-31');
		$tables = new Table();
		$tables->thead=array('date'=>'วัน-เดือน-ปี','หัวข้อ','โดย','amt amt-view'=>'ดู','amt amt-reply'=>'ตอบ');
		foreach ($topic_summary->items as $item) {
			$tables->rows[] = array(
				sg_date($item->created,'ว ดด ปปปป'),
				'<a href="'.url('paper/'.$item->tpid).'">'.$item->title.'</a>',
				($item->uid?'<a href="'.url('paper/user/'.$item->uid).'">':'').$item->owner.($item->uid?'</a>':''),
				$item->view,
				$item->reply
			);
			$total++;
		}
		$ret .= $tables->build();
	}
	$ret.='</div>';
	return $ret;
}
?>