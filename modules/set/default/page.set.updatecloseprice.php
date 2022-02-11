<?php
/**
* Update daily close price of year from realtime.bualuang.co.th
*
* @param Integer $year
* @return String
*/
function set_updatecloseprice($self, $year) {
	set_time_limit(0);
	$year = intval($year);
	$day = 1;
	$i = 1;
	echo '<div style="font-size:8pt;">';
	do {
		$date = mktime(0,0,0,1,$day,$year);
		ob_start();
		$str='<h3>'.($i++).' : Update '.date('Y-m-d',$date).' '.date('Y',$date).'<='.$year.' @'.date('H:i:s').'</h3>';
		unset($symbols);
		$symbols=R::Model('set.price.close.get',date('Y-m-d',$date));
		if ($symbols) {
			foreach ($symbols as $rs) {
				$rs['created']=date('U');
				$stmt='INSERT INTO %setdaily% (`date`, `symbol`, `pdate`, `open`, `high`, `low`, `close`, `volume`, `value`, `created`) VALUES (:date, :symbol, :pdate, :open, :high, :low, :close, :volume, :value, :created)';
				mydb::query($stmt,$rs);
				$str.=$rs['symbol'].', ';
				//if (mydb()->_error_no) echo mydb()->_query.'<br />';
			}
		//	$ret=$str.$ret;
		}
		echo $str;
		ob_flush();
		flush();
		ob_end_flush();
		$day++;
	} while ($day<=365); //(date('Y',$date)<=$year);
	echo '</div>';
	return $ret;
}
?>