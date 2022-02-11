<?php
/**
* Show and update symbol close price from realtime.bualuang.co.th
*
* @param Date $date
* @return String
*/
function set_closeprice($self, $date = NULL) {
	$date = SG\getFirst($date, post('d'), date('Y-m-d'));

	$ret = '<form class="sg-form" method="get" action="'.url('set/closeprice').'" data-rel="parent"><label>วันที่ </label><input class="form-date sg-datepicker" type="text" name="d" value="'.sg_date($date,'d/m/Y').'" /> <button class="btn -primary" type="submit"><i class="icon -search -white"></i><span>Go</span></button></form>';


	$tables = new Table();
	$tables->children = R::Model('set.price.close.get', $date);

	if (empty($tables->children)) {
		$ret .= message('error', 'ไม่มีข้อมูลราคาปิดสำหรับวันที่ '.$date);
	} else {
		foreach (array_keys(reset($tables->children)) as $key)
			$tables->thead[] = $key;

		$ret .= '<h2>Update SET Close Price of '.$date.' total '.number_format(count($tables->children)).' items.</h2>';

		foreach ($tables->children as $rs) {
			$rs['created'] = date('U');
			$stmt = 'INSERT INTO %setdaily% (
					`date`, `symbol`, `pdate`, `open`, `high`, `low`, `close`, `volume`, `value`, `created`
				) VALUES (
					:date, :symbol, :pdate, :open, :high, :low, :close, :volume, :value, :created
				)
				ON DUPLICATE KEY UPDATE
					  `pdate` = :pdate
					, `open` = :open
					, `high` = :high
					, `low` = :low
					, `close` = :close
					, `volume` = :volume
					, `value` = :value
					, `created` = :created
					';
			mydb::query($stmt, $rs);
			// debugMsg(mydb()->_query);
		}

		// Update SET Symbol
		$stmt = 'INSERT IGNORE INTO %setsymbol%
				SELECT DISTINCT `symbol`, NULL, NULL FROM %setdaily%';
		mydb::query($stmt);

		$ret .= $tables->build();

	}

	return $ret;
}
?>