<?php
/**
* Module :: Description
* Created 2022-02-14
* Modify  2022-02-14
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class SaveupTreatSummary extends Page {
	var $year;
	var $order;

	function __construct($arg1 = NULL) {
		$this->year = SG\getFirst(post('year'), date('Y'));
		$this->order = SG\getFirst(post('o'), 'id');
	}

	function build() {
		// R::View('saveup.toolbar',$self,'สรุปรายการเบิกค่ารักษาพยาบาลประจำปี '.($this->year+543),'treat');

		switch ($this->order) {
			case 'name' : $order = 'name';break;
			case 'amount' : $order = 'amount';break;
			default : $order = 'tr.mid';break;
		}

		$sql_cmd = 'SELECT
			DATE_FORMAT(`date`,"%Y-%m") `date`
			, tr.`mid`
			, CONCAT(fu.`firstname`," ",fu.`lastname`) `name`
			, SUM(tr.`amount`) `amount`
			FROM %saveup_treat% AS tr
				LEFT JOIN %saveup_member% fu ON fu.`mid` = tr.`mid`
			WHERE YEAR(`date`) = :year
			GROUP BY `date`, tr.`mid`
			ORDER BY CONVERT('.$order.' USING tis620) ASC,tr.date ASC';
		$query = mydb::select($sql_cmd, [':year' => $this->year, ':order' => $order]);

		$grids = [];
		foreach ($query->items as $rs) {
			$grids[$rs->mid][$rs->date] += $rs->amount;
			$names[$rs->mid] = $rs->name;
			$totals[$rs->date] += $rs->amount;
		}

		$total = 0;

		$tables = new Table([
			'class' => 'saveup-treat-list',
			'caption' => $self->theme->title,
			'thead' => (function() {
				$thead = ['id -nowrap' => 'รหัส', 'name -nowrap' => 'สมาชิก'];
				for ($i = 1; $i <= 12; $i++) $thead['money month-'.$i] = sprintf('%02d',$i);
				$thead['money total'] = 'รวม';
				return $thead;
			})(),
		]);
		foreach ($grids as $mid => $row) {
			$rows = [$mid, $names[$mid]];
			$subtotal = 0;
			for ($i = 1; $i <= 12; $i++) {
				$mykey = $this->year.'-'.sprintf('%02d',$i);
				$rows[] = ($row[$mykey] ? number_format($row[$mykey],2) : '-');
				$subtotal += $row[$mykey];
			}
			$rows[] = '<strong>'.number_format($subtotal,2).'</strong>';
			$tables->children[] = $rows;
			$total += $subtotal;
		}
		$tables->tfoot = [
			(function($totals, $total) {
				$tfoot = ['<td></td>','<td align="right"><strong>รวมทั้งสิ้น</strong></td>'];
				// let's print the international format for the en_US locale
				for ($i=1;$i<=12;$i++) {
					$tfoot[] = preg_replace('/^0\.00/','-',number_format($totals[$this->year.'-'.sprintf('%02d',$i)],2));
				}
				$tfoot[] = number_format($total,2);
				return $tfoot;
			})($totals, $total)
		];



		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สรุปรายการเบิกค่ารักษาพยาบาลประจำปี '.($this->year+543),
				'navigator' => [R::View('saveup.treat.nav')],
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Form([
						'class' => 'form-report',
						'method' => 'get',
						'action' => url('saveup/treat/summary'),
						'children' => [
							'year' => [
								'type' => 'select',
								'value' => $this->year,
								'options' => mydb::select(
									'SELECT YEAR(`date`) `year`, CONCAT("ปี พ.ศ.",YEAR(`date`) + 543, " จำนวน ", FORMAT(SUM(`amount`), 2)," บาท") `bcYear` FROM %saveup_treat% t GROUP BY YEAR(`date`);
									-- {key: "year", value: "bcYear"}'
								)->items,
							],
							'o' => [
								'type' => 'select',
								'value' => $this->order,
								'options' => ['id' => 'เรียงตามรหัส', 'name' => 'เรียงตามชื่อ'],
							],
							'go' => [
								'type' => 'button',
								'value' => '<i class="icon -search -white"></i><span>ดู</span>',
							],
						], // children
					]), // form

					$query->_empty ? 'ไม่มีรายการเบิกค่ารักษาพยาบาลตามเงื่อนไขที่กำหนด' : new ScrollView([
						'child' => $tables,
					]), // ScrollView

					'<p><strong>รวมทั้งสิ้น '.$query->_num_rows.' ครั้ง '.count($tables->children).' คน เป็นจำนวนเงิน '.number_format($total,2).' บาท</strong></p>',
				], // children
			]), // Widget
		]);
	}
}
?>