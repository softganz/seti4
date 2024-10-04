<?php
/**
* Admin   :: Load Test Count
* Created :: 2024-10-03
* Modify  :: 2024-10-03
* Version :: 1
*
* @return Widget
*
* @usage admin/log/counter/count
*/

use Softganz\DB;

class AdminLogCounterCount extends Page {
	var $counter = 'normal';
	var $date;
	var $time;
	var $moreThan;

	function __construct() {
		parent::__construct([
			'counter' => post('counter'),
			'date' => sg_date(SG\getFirst(post('date'), date('d/m/Y')), 'Y-m-d'),
			'time' => post('time'),
			'moreThan' => SG\getFirstInt(post('moreThan'))
		]);
	}

	function build() {
		$data = $this->data();

		// debugMsg(mydb()->_query);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Log Counter Count',
				'child' => new Form([
					'class' => 'sg-form form-report',
					'method' => 'GET',
					'action' => url(q()),
					'rel' => '#main',
					'children' => [
						'counter' => [
							'type' => 'select',
							'value' => $this->counter,
							'onChange' => 'submit',
							'options' => ['normal' => 'Web Access', 'loadtest' => 'Load Test']
						],
						'date' => [
							'type' => 'text',
							'class' => 'sg-datepicker -sg-text-center',
							'value' => date('d/m/Y')
						],
						'moreThan' => [
							'type' => 'text',
							'label' => 'จำนวนครั้งมากกว่า',
							'class' => '-sg-text-center',
							'size' => 4,
							'value' => $this->moreThan,
							'placeholder' => '0',
						],
						'go' => [
							'type' => 'button',
							'value' => '<i class="icon -material">search</i>'
						]
					], // children
				]), // Form
			]), // AppBar
			'body' => new Widget([
				'children' => array_map(
					function($item) {
						return new Card([
							'children' => [
								new ListTile([
									'crossAxisAlignment' => 'center',
									'title' => '@'.$item->label.' Amount : '.number_format($item->amt).' hits.',
									'trailing' => new ExpandButton([
										'attribute' => [
											'onClick' => 'loadTime(this)',
											'data-time' => $item->label,
											'data-counter' => $this->counter
										]
									])
								]),
								new Container([
									'class' => '-hidden',
								]),
							], // children
						]);
					},
					$data->items
				),
				$this->script()
			]), // Widget
		]);
	}

	function minute() {
		return new Table([
			'class' => '-center',
			'thead' => ['Time', 'Amount'],
			'children' => $this->dataMinute($this->time)->items
		]);
	}

	private function data() {
		return DB::select([
			'SELECT DATE_FORMAT(`log_date`,"%Y-%m-%d %H:%i") `label`,COUNT(*) `amt`
			FROM $TABLE$
			%WHERE%
			GROUP BY `label`
			$MORETHAN$
			ORDER BY `label` DESC',
			'where' => [
				'%WHERE%' => [
					['`log_date` BETWEEN :startDate AND :endDate', ':startDate' => $this->date.' 00:00:00', ':endDate' => $this->date.' 23:59:59']
				]
			],
			'var' => [
				'$TABLE$' => $this->counter === 'loadtest' ? '%ztest_counter_log%' : '%counter_log%',
				'$MORETHAN$' => $this->moreThan ? 'HAVING `amt` > :moreThan' : '',
				':moreThan' => $this->moreThan,
			]
		]);
	}

	private function dataMinute($time) {
		return DB::select([
			'SELECT `log_date`, COUNT(*) `amt`
			FROM $TABLE$
			WHERE `log_date` BETWEEN :startTime AND :endTime
			GROUP BY `log_date`
			ORDER BY `log_date` DESC',
			'var' => [
				'$TABLE$' => $this->counter === 'loadtest' ? '%ztest_counter_log%' : '%counter_log%',
				':startTime' => $time.':00',
				':endTime' => $time.':59',
			]
		]);
	}

	private function script() {
		head('<script>
		function loadTime(element) {
			let $target = $(element).closest(".widget-card").find(".widget-container");

			if (!$target.hasClass("-loaded")) {
				$.get(SG.url("'.q().'..minute"), {counter: $(element).data("counter"), time : $(element).data("time")})
				.done(function(response) {
					$target.html(response)
				});
			}

			$target.addClass("-loaded");
		}
		</script>');
	}
}
?>