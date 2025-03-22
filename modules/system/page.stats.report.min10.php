<?php
/**
* Stats   :: Report in 10 Minute
* Created :: 2017-07-29
* Modify  :: 2025-03-22
* Version :: 4
*
* @param String $date
* @return Widget
*
* @usage stats/report/min10
*/

use Softganz\DB;

class StatsReportMin10 extends Page {
	var $date;
	var $graphHeight;

	function __construct($date = NULL) {
		parent::__construct([
			'date' => SG\getFirst($date, date('Y-m-d')),
			'graphHeight' => intval(post('h')),
		]);
	}

	function build() {
		$log = [];

		$dbs = $this->getData();

		$last_query_time = mydb()->_last_query_time;

		foreach ($dbs->items as $rs) {
			$log[$rs->logtime] = $rs->total;
			$max = $max < $rs->total ? $rs->total : $max;
		}

		if ($log) {
			reset($log);
			$first = key($log);
			end($log);
			$last = key($log);

			$chr = date('H',$first);
			$col = 0;
			$first_row = false;
			$no = 0;

			$graphTable = '<table cellspacing=0 cellpadding=0 style="border-left:1px blue solid;border-bottom:1px blue solid;"><tr valign="bottom">'._NL;
			$table = '';
			$label = '';
			$zero = '';

			for ($i = $first; $i <= $last; $i = $i + 600) {
				if (date('H',$i) == $chr) {
					$col++;
					$first_row = false;
				} else {
					$label .= '<td class="label" colspan="'.$col.'">'.$chr.'</td>'._NL;
					$chr = date('H',$i);
					$col = 1;
					$first_row = true;
				}

				$height = $this->graphHeight ? $height = ($log[$i]*$this->graphHeight)/$max : $log[$i];
				$graphTable .= '<td'.($first_row?' class="first_row"':'').'>';
				$graphTable .= '<div class="graph" style="height:'.$height.'px;'.($log[$i]==0?'border-top:none;':'').'"></div>';
				$graphTable .= '</td>'._NL;
				if ($log[$i] == 0) $zero .= '<li>'.date('Y-m-d H:i',$i).'</li>'._NL;
				$table .= '<tr class="'.(++$no%2?'odd':'even').'"><td>'.date('Y-m-d H:i',$i).'</td><td>'.$log[$i].'</td></tr>'._NL;
			}

			if ($chr) $label .= '<td class="label" colspan="'.$col.'">'.$chr.'</td>'._NL;
			$graphTable .= '</tr>'._NL;
			$graphTable .= '<tr align="center">'.$label.'</tr>'._NL;
			$graphTable .= '</table>'._NL;
		}


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '10 Minute Hits of '.$this->date,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'Query time : '.$last_query_time.' ms.',
					' Max hits = <strong>'.number_format($max).' hits</strong> in 10 min.',
					new ScrollView([
						'child' => new Row([
							'style' =>'gap: 8px',
							'children' => [
								'-2' => new Button([
									'type' => 'secondary',
									'class' => 'sg-action',
									'href' => url('stats/report/min10', ['h' => $this->graphHeight ? $this->graphHeight : NULL]),
									'text' => 'Today',
									'rel' => '#main'
								]),
								'-1' => new Button([
									'type' => 'secondary',
									'class' => 'sg-action',
									'href' => url('stats/report/min10',['h' => 200]),
									'text' => 'Fixed Height',
									'rel' => '#main',
								]),
							] + array_map(
								function($i) {
									$d = getdate(sg_date($this->date,'U'));
									$ndate = date('Y-m-d',mktime(0,0,0,$d['mon'],$d['mday']-$i,$d['year']));
									return new Button([
										'type' => 'secondary',
										'class' => 'sg-action',
										'href' => url('stats/report/min10/'.$ndate,  ['h' => $this->graphHeight ? $this->graphHeight : NULL]),
										'text' => $ndate,
										'rel' => '#main'
									]);
								},
								range(1,7)
							), // children
						]), // Row
					]), // ScrollView

					new ScrollView(['child' => $graphTable]),

					$zero ? '<h3>Empty Hits</h3><ul>'.$zero.'</ul>' : NULL,
					'<table class="widget-table"><thead><th>Date</th><th>Hits</th></thead><tbody>'.$table.'</tbody></table>',

					'<style type="text/css">
					.first_row {border-left:1px #eee solid;}
					.label {background:#eee;border-left:1px #fff solid;}
					.graph {width:4px;background:gray;border-top:1px red solid;}
					.widget-table {margin:20px;}
					.widget-table td {text-align:center;}
					</style>',
				], // children
			]), // Widget
		]);
	}

	private function getData() {
		return DB::select([
			'SELECT
				FLOOR(UNIX_TIMESTAMP(`log_date`)/600)*600 AS `logtime`
				, COUNT(*) AS `total`
			FROM %counter_log%
			WHERE `log_date` BETWEEN :from AND :to
			GROUP BY `logtime`
			ORDER BY `logtime` ASC',
			'var' => [
				':from' => $this->date.' 00:00:00',
				':to' => $this->date.' 23:59:59',
			]
		]);
	}
}
?>