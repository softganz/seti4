<?php
function stats_report_10min($self,$date=NULL) {
	if (!isset($date)) $date=date('Y-m-d');
	$graph_height=isset($_GET['h']) ? $_GET['h'] : NULL;
	$from=$date.' 00:00:00';
	$to=$date.' 23:59:59';

	$self->theme->title='10 Minute Hits of '.$date;
	user_menu('report','10min',url('stats/report/10min'));
	$self->theme->navigator=user_menu();

	$stmt='SELECT FLOOR(UNIX_TIMESTAMP(`log_date`)/600)*600 AS logtime,count(*) AS total FROM %counter_log%
					WHERE `log_date` BETWEEN "'.$from.'" AND "'.$to.'"
					GROUP BY logtime
					ORDER BY logtime ASC ';
	$dbs=mydb::select($stmt);

	$ret.='Query time : '.$last_query_time=mydb()->last_query_time.' ms.';

	foreach ($dbs->items as $rs) {
		$log[$rs->logtime]=$rs->total;
		$max=$max<$rs->total?$rs->total:$max;
	}

	reset($log);
	$first=key($log);
	end($log);
	$last=key($log);

	$ret.=' Max hits = <strong>'.number_format($max).' hits</strong> in 10 min.';
	$ret.='<div><a href="'.url('stats/report/10min',isset($graph_height)?'h='.$graph_height:NULL).'">Today</a> (<a href="'.url('stats/report/10min','h=100').'">Fixed Height</a>)';
	$d=getdate(sg_date($date,'U'));
	for ($i=1;$i<=7;$i++) {
		$ndate=date('Y-m-d',mktime(0,0,0,$d['mon'],$d['mday']-$i,$d['year']));
		$ret.=' | <a href="'.url('stats/report/10min/'.$ndate,isset($graph_height)?'h='.$graph_height:NULL).'">'.$ndate.'</a>';
	}
	$ret.='</div>';
	if ($log) {
		$chr=date('H',$first);
		$col=0;
		$first_row=false;
		$no=0;
		$ret.='<table cellspacing=0 cellpadding=0 style="border-left:1px blue solid;border-bottom:1px blue solid;"><tr valign="bottom">'._NL;
		for ($i=$first;$i<=$last;$i=$i+600) {
			if (date('H',$i)==$chr) {
				$col++;
				$first_row=false;
			} else {
				$label.='<td class="label" colspan="'.$col.'">'.$chr.'</td>'._NL;
				$chr=date('H',$i);
				$col=1;
				$first_row=true;
			}
			$height=isset($graph_height) ? $height=($log[$i]*$graph_height)/$max : $log[$i];
			$ret.='<td'.($first_row?' class="first_row"':'').'>';
			$ret.='<div class="graph" style="height:'.$height.'px;'.($log[$i]==0?'border-top:none;':'').'"></div>';
			$ret.='</td>'._NL;
			if ($log[$i]==0) $zero.='<li>'.date('Y-m-d H:i',$i).'</li>'._NL;
			$table.='<tr class="'.(++$no%2?'odd':'even').'"><td>'.date('Y-m-d H:i',$i).'</td><td>'.$log[$i].'</td></tr>'._NL;
		}
		if ($chr) $label.='<td class="label" colspan="'.$col.'">'.$chr.'</td>'._NL;
		$ret.='</tr>'._NL;
		$ret.='<tr align="center">'.$label.'</tr>'._NL;
		$ret.='</table>'._NL;
		if ($zero) $ret.='<h3>Empty Hits</h3><ul>'.$zero.'</ul>'._NL;
		$ret.='<table class="item"><thead><th>Date</th><th>Hits</th></thead><tbody>'.$table.'</tbody></table>';
	}
	$ret.='<style><!--
.first_row {border-left:1px #eee solid;}
.label {background:#eee;border-left:1px #fff solid;}
.graph {width:4px;background:gray;border-top:1px red solid;}
.item {margin:20px;}
.item td {text-align:center;}
--></style>';
	return $ret;
}
?>