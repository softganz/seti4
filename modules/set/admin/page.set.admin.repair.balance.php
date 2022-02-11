<?php
/**
* Repair balance
*
* @param Integer $symbol
* @return String
*/
function set_admin_repair_balance($self,$symbol) {
	if (!$symbol) {
		mydb::query('UPDATE %setport% SET `balance`=`volumes` WHERE `bsd` IN ("B")');
	} else {
		mydb::query('UPDATE %setport% SET `balance`=`volumes` WHERE `bsd` IN ("B") AND `uid`=:uid AND `symbol`=:symbol',':symbol',$symbol, ':uid',i()->uid);
	}

	mydb::where('`bsd` IN ("B","S")');
	if ($symbol) {
		mydb::where('`uid` = :uid ',':uid',i()->uid);
		mydb::where('`symbol` = :symbol ',':symbol',$symbol);
	}
	$stmt = 'SELECT DISTINCT
					`uid`, `gid`, `symbol`
					FROM %setport%
					%WHERE%
					ORDER BY `uid`,`gid`,`symbol`';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	foreach ($dbs->items as $portSymbol) {
		$ret.='<h2>Repair uid='.$portSymbol->uid.' gid='.$portSymbol->gid.' symbol='.$portSymbol->symbol.'</h2>';
		$symbolDbs=mydb::select('SELECT * FROM %setport% WHERE `uid`=:uid AND `gid`=:gid AND `symbol`=:symbol ORDER BY `date` ASC, `id` ASC', $portSymbol);
		unset($tables->thead);
		foreach ($symbolDbs->items[0] as $key => $value) {
			$tables->thead[]=$key;
		}
		foreach ($symbolDbs->items as $rs) {
			if ($rs->bsd!='S') continue;
			$ret.='Cut cost of item <strong>'.$rs->id.'</strong> for symbol <strong>'.$rs->symbol.'</strong>'.' amount <strong>'.$rs->volumes.'</strong><br />';
			$allBalance=$rs->volumes;
			foreach ($symbolDbs->items as $key => $value) {
				if ($allBalance==0) break;
				if ($value->balance<=0) continue;
				else if ($value->balance>=$allBalance) {
					$balance=$value->balance;
					$ret.='Case 1 cut '.$balance.' items. ';
					$value->balance-=$allBalance;
					$allBalance=0;
					mydb::query('UPDATE %setport% SET `balance`=:balance WHERE `id`=:id',':id',$value->id,':balance',$value->balance);
				} else {
					$balance=$value->balance;
					$value->balance=$symbolDbs->items[$key]->balance=0;
					$allBalance-=$balance;
					$ret.='Case 2 cut '.$balance.' items. ';
					mydb::query('UPDATE %setport% SET `balance`=:balance WHERE `id`=:id',':id',$value->id,':balance',$value->balance);
				}
				$ret.='Cut balance '.$value->id.' Balance='.$symbolDbs->items[$key]->balance.' All balance='.$allBalance.'<br />';
			}
		}
		$tables->rows=$symbolDbs->items;
		$ret .= $tables->build();
	}
	return $ret;
}
?>