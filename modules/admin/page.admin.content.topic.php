<?php
/**
* Admin   :: Content by topic
* Created :: 2016-11-08
* Modify  :: 2025-03-17
* Version :: 2
*
* @return Widget
*
* @usage admin/content/topic
*/

use Softganz\DB;

class AdminContentTopic extends Page {
	var $backDay = 7;
	var $year;
	var $month;

	function __construct() {
		parent::__construct([
			'backDay' => SG\getFirstInt(post('day'), $this->backDay),
			'year' => SG\getFirstInt(post('year')),
			'month' => post('month'),
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Contents'
			]), // AdminAppBarWidget
			'body' => new TabBar([
				'children' => [
					// Show do information
					[
						'id' => 'content',
						'active' => true,
						'action' => new Button(['href' => url('admin/content/topic..list'), 'text' => 'Topics']),
						'content' => $this->list()
					], // Tab 1

					[
						'id' => 'summary',
						'action' => new Button(['href' => url('admin/content/topic..summary'), 'text' => 'Summary']),
						'content' => $this->summary(),
					], // Tab 2
				], // children
			]), // TabBar
		]);
	}

	public function list() {
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

		return new Widget([
			'children' => [
				new ListTile(['title' => 'Topic Listing']),
				new Form([
					'class' => 'sg-form form-report',
					'method' => 'get',
					'action' => url('admin/content/topic..list'),
					'rel' => '#content',
					'children' => [
						'day' => [
							'type' => 'text',
							'class' => '-numeric',
							'label' => 'ย้อนหลัง',
							'value' => $this->backDay,
							'posttext' => ' วัน ',
						],
						'go' => [
							'type' => 'button',
							'value' => '<i class="icon -material">find_in_page</i>'
						],
					], // children
				]), // Form
				new Table([
					'thead' => ['Type', 'Title', 'created -date' => 'Date'],
					'children' => array_map(
						function($topic) {
							if ($topic->content === 'comment') {
								$title = $topic->title;
							} else {
								$title = new Button([
									'href' => url('paper/'.$topic->nodeId),
									'text' => $topic->title,
									'target' => '_blank'
								]);
							}
							return [
								$topic->content,
								$title,
								$topic->created,
							];

						},
						$this->topicData()
					)
				]),
				// $ret
			]
		]);
	}

	public function summary() {
		// $tables = new Table();
		// $tables->thead=array('date'=>'ปี พศ.','amt'=>'จำนวนหัวข้อ');
		// foreach ($dbs->items as $item) {
		// 	$tables->rows[]=array(
		// 		'<a href="'.url('admin/content/summary/'.$item->year).'">'.sg_date($item->year.'-01-01','ปปปป').'</a>',
		// 		$item->topics
		// 	);
		// }
		// $tables->tfoot[]=array('รวม',$dbs->sum->topics);
		// $ret.=$tables->build();

		// $ret.='<div id="content-report-show">';
		// if ($year) {
		// 	$sql='SELECT
		// 		  DATE_FORMAT(created,"%Y-%m") AS `month`
		// 		, DATE_FORMAT(created,"%m") AS `monthno`
		// 		, COUNT(*) AS `topics`
		// 		FROM %topic%
		// 		WHERE `created` BETWEEN :beginyear AND :endyear
		// 		GROUP BY `month`
		// 		ORDER BY `created` ASC;
		// 		-- {sum:"topics"}
		// 		';
		// 	$dbs=mydb::select($sql,':beginyear',$year.'-01-01', ':endyear',$year.'-12-31');
		// 	$tables = new Table();
		// 	$tables->thead=array('date'=>'เดือน-ปี พศ.','amt'=>'จำนวนหัวข้อ');
		// 	foreach ($dbs->items as $item) {
		// 		$tables->rows[]=array(
		// 			'<a href="'.url('admin/content/summary/'.$year.'/'.$item->monthno).'">'.sg_date($item->month.'-01','ดด ปปปป').'</a>',
		// 			$item->topics
		// 		);
		// 	}
		// 	$tables->tfoot[]=array('รวม',$dbs->sum->topics);
		// 	$ret.=$tables->build();
		// }

		// if ($month) {
		// 	$sql='SELECT
		// 			t.`tpid`, t.`title`, t.`created`, t.`uid`
		// 		, IF(t.`poster` IS NOT NULL,t.`poster`,u.`name`) AS owner
		// 		, t.`view`, t.`reply`
		// 		FROM %topic% t
		// 			LEFT JOIN %users% u USING(`uid`)
		// 		WHERE t.`created` BETWEEN :startmonth AND :endmonth
		// 		ORDER BY t.`created` ASC';
		// 	$topic_summary=mydb::select($sql,':startmonth',$year.'-'.$month.'-01', ':endmonth',$year.'-'.$month.'-31');
		// 	$tables = new Table();
		// 	$tables->thead=array('date'=>'วัน-เดือน-ปี','หัวข้อ','โดย','amt amt-view'=>'ดู','amt amt-reply'=>'ตอบ');
		// 	foreach ($topic_summary->items as $item) {
		// 		$tables->rows[] = array(
		// 			sg_date($item->created,'ว ดด ปปปป'),
		// 			'<a href="'.url('paper/'.$item->tpid).'">'.$item->title.'</a>',
		// 			($item->uid?'<a href="'.url('paper/user/'.$item->uid).'">':'').$item->owner.($item->uid?'</a>':''),
		// 			$item->view,
		// 			$item->reply
		// 		);
		// 		$total++;
		// 	}
		// 	$ret .= $tables->build();
		// }
		// $ret.='</div>';

		return new Container([
			'class' => '-sg-flex',
			'style' => 'gap: 16px',
			'children' => [
				new Container([
					'style' => 'flex: 1',
					'child' => new Table([
						'thead' => ['year -date' => 'ปี พศ.', 'total -amt' => 'จำนวนหัวข้อ'],
						'children' => array_map(
							function($item) {
								return [
									new Button([
										'class' => 'sg-action',
										'href' => url('admin/content/topic..month.summary', ['year' => $item->year]),
										'text' => sg_date($item->year.'-01-01','ปปปป'),
										'rel' => '#month',
									]),
									number_format($item->topics)
								];
							},
							$this->summaryData()
						),
						'tfoot' => [
							['รวม', number_format($this->totalTopic)]
						]
					]),
				]), // Container
				new Container([
					'id' => 'month',
					'style' => 'flex: 1',
				]), // Container
			], // children
		]);
	}

	function monthSummary() {
		if (empty($this->year)) return NULL;

		$dbs = DB::select([
			'SELECT
			  DATE_FORMAT(`created`, "%Y-%m") AS `month`
			, DATE_FORMAT(`created`, "%m") AS `monthno`
			, COUNT(*) AS `topics`
			FROM %topic%
			WHERE `created` BETWEEN :beginyear AND :endyear
			GROUP BY `month`
			ORDER BY `created` ASC',
			'var' => [
				':beginyear' => $this->year.'-01-01',
				':endyear' => $this->year.'-12-31'
			],
			'options' => ['sum' => 'topics']
		]);

		return new Table([
			'thead' => ['date'=>'เดือน-ปี พศ.','total -amt'=>'จำนวนหัวข้อ'],
			'children' => array_map(
				function($item) {
					return [
						new Button([
							'class' => 'sg-action',
							'href' => url('admin/content/topic..month.topic', ['month' => $this->year.'-'.$item->monthno]),
							'text' => sg_date($item->month.'-01','ดด ปปปป'),
							'rel' => 'box',
							'attribute' => ['data-width' => 'full']
						]),
						number_format($item->topics)
					];
				},
				$dbs->items
			),
			'tfoot' => [
				['รวม', number_format($dbs->sum->topics)]
			],
		]);
	}

	function monthTopic() {
		if (empty($this->month)) return NULL;

		$topic_summary = DB::select([
			'SELECT
				t.`tpid`, t.`title`, t.`created`, t.`uid`
			, IF(t.`poster` IS NOT NULL,t.`poster`,u.`name`) AS owner
			, t.`view`, t.`reply`
			FROM %topic% t
				LEFT JOIN %users% u USING(`uid`)
			WHERE t.`created` BETWEEN :startmonth AND :endmonth
			ORDER BY t.`created` ASC',
			'var' => [
				':startmonth' => $this->month.'-01',
				':endmonth' => $this->month.'-31',
			]
		]);

		return new Table([
			'thead' => ['date'=>'วัน-เดือน-ปี','หัวข้อ','โดย','amt amt-view'=>'ดู','amt amt-reply'=>'ตอบ'],
			'children' => array_map(
				function($item) {
					return [
						sg_date($item->created,'ว ดด ปปปป'),
						new Button([
							'href' => url('paper/'.$item->tpid),
							'text' => $item->title,
							'target' => '_blank'
						]),
						($item->uid?'<a href="'.url('paper/user/'.$item->uid).'">':'').$item->owner.($item->uid?'</a>':''),
						number_format($item->view),
						number_format($item->reply)
					];
				},
				(Array) $topic_summary->items
			)
		]);
	}

	private function topicData() {
		$dbs = DB::select([
			'SELECT
				`type` `content`, `tpid` `nodeId`, NULL `cid`, `title`, `created`
				FROM %topic%
				WHERE `created` BETWEEN  :from_date AND :to_date
			UNION
				SELECT
				"comment" `content`, `tpid`, `cid`, `comment`, `timestamp`
				FROM %topic_comments%
				WHERE timestamp BETWEEN :from_date AND :to_date
			ORDER BY `created` DESC',
			'var' => [
				':from_date' => date('Y-m-d 00:00:00', strtotime('-'.$this->backDay.' days')),
				':to_date' => date('Y-m-d H:i:s')
			]
		]);

		return (Array) $dbs->items;
	}

	private function summaryData() {
		$dbs = DB::select([
			'SELECT
			DATE_FORMAT(created,"%Y") AS `year`
			, COUNT(*) AS `topics`
			FROM %topic%
			GROUP BY `year`
			ORDER BY `created` ASC',
			'options' => [
				'sum' => 'topics'
			]
		]);

		$this->totalTopic = $dbs->sum->topics;
		return (Array) $dbs->items;
	}
}
?>