<?php
function admin_content_topic($self) {
	$para=para(func_get_args());
	$day=SG\getFirst($_GET['day'],7);

	$from_date=date('Y-m-d 00:00:00', strtotime('-'.$day.' days'));

	$ret .= '<div id="admin_content" class="contentstyle">
<ul class="tabs primary">
<li class="-active"><a href="'.url('admin/content/topic').'">Content</a></li>
<li><a href="'.url('admin/content/summary').'">Summary</a></li>
</ul>';

	$ret .= '<h2>Topic Listing</h2>';
	$ret .= '<form method="get" action="'.url('admin/content/topic').'">';
	$ret .= sg_client_convert('ย้อนหลัง').' <input type="text" name="day" value="'.$day.'" style="width:30px;"> '.sg_client_convert('วัน ');
	$ret .= '<button class="btn -primary" type="submit"><i class="icon -material">find_in_page</i><span> Go </span></button>';
	$ret .= '</form>';

	$stmt='SELECT
			`type` `content`, `tpid`, NULL `cid`, `title`, `created`
			FROM %topic%
			WHERE `created` BETWEEN  :from_date AND :to_date
		UNION
			SELECT
			"comment" `content`, `tpid`, `cid`, `comment`, `timestamp`
			FROM %topic_comments%
			WHERE timestamp BETWEEN :from_date AND :to_date
		ORDER BY `created` DESC';

	$dbs=mydb::select($stmt,':from_date',$from_date, ':to_date',date('Y-m-d H:i:s'));
	//$ret.=print_o($dbs);

	$tables = new Table();
	$tables->thead=array('Type','Title','Date');
	foreach ($dbs->items as $rs) {
		if ($rs->content=='comment') {
			$title=$rs->title;
		} else {
			$title='<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>';
		}
		$tables->rows[]=array(
			$rs->content,
			$title,
			$rs->created,
		);
	}
	$ret.=$tables->build();

	$ret .= '<ul>';
	foreach ($content as $date=>$date_rs) {
		$ret .= '<b>'.$date.'</b>';
		foreach ($date_rs as $rs) {
			$ret .= '<li><a href="'.$rs['url'].'">'.$rs['title'].'</a> - '.$rs['remark'].' @'.sg_date($rs['datetime'],'Y-m-d H:i:s').'</li>';
		}
	}
	$ret .= '</ul>';

	$ret.='</div>';
	return $ret;
}
?>