<?php
/**
* Watch symbol group status
*
* @param Integer $gid
* @param String $gid Symbols seperate by ,
* @return String
*/
function set_watch($self,$gid) {
	if (strpos($gid,',')) {
		foreach (explode(',',$gid) as $symbol) $dbs->items[]=(object)array('symbol'=>trim($symbol));
		$dbs->_empty=false;
	} else {
		$stmt='SELECT * FROM %setwishlist% w
						LEFT JOIN %setgroup% g USING(`gid`)
					WHERE w.`uid`=:uid AND w.`gid`=:gid
					ORDER BY w.`symbol`';
		$dbs=mydb::select($stmt,':uid',i()->uid,':gid',$gid);
	}
	if ($dbs->_empty) return;

	$ret.='<h2>'.$dbs->items[0]->name.'</h2>';
	$currentPrice=R::Model('set.price.realtime.get');

	$ul.='<ul class="set-symbol-list-price">';

	$tables->class='item set-symbol-list-price';
	$tables->caption='Current Port Status @'.date('H:i:s');
	$tables->thead=array('Symbol','<span title="High / Open / Low">O</span>','Volume','Cost','Last','<span title="Change">Ch</span>','UnP/L','Un+P/L','Bid','Offer','<span title="Volume(Shares)">Volume</span>','<span title="Value(000 Baht)">Value</span>','');
	foreach ($dbs->items as $rs) {
		$curSymbol=$currentPrice[$rs->symbol];
		$sign=strlen(trim($curSymbol['change']))==1 ? '' : substr($curSymbol['change'],0,1);

		$marketPrice=$curSymbol['last'];
		$balanceCost=$rs->buyPrice-$rs->salePrice;
		$balanceVolumes=$rs->buyVolumes-$rs->saleVolumes;

		unset($row);
		$ports[$portId]->portname=$rs->portname;
		$cost=$balanceCost/$balanceVolumes;
		$marketValue=$balanceVolumes*$marketPrice;
		$itemMargin=($marketPrice-$cost)*$balanceVolumes;

		$ports[$portId]->allCost+=$balanceCost;
		$ports[$portId]->marketValues+=$marketValue;
		$ports[$portId]->marginPL+=$marketValue-$amountPrice;
		$ports[$portId]->currentMarginPL+=$marketValue-$rs->costBalance;
		$ports[$portId]->currentCost+=$rs->costBalance;

		$row['symbol']='<a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output">'.$rs->symbol.'</a>'.($curSymbol['sign']?'('.$curSymbol['sign'].')':'');
		$row['open']='<span class="set-symbol-high">'.$curSymbol['high'].'</span><span class="set-symbol-open">'.$curSymbol['open'].'</span><span class="set-symbol-low">'.$curSymbol['low'].'</span>';
		$row['volume']=number_format($balanceVolumes);

		$row['cost']=number_format($cost,3).'<span>'.number_format($balanceCost,2).'</span>';
		$row['last']=$curSymbol['last'].'<span>'.number_format($balanceVolumes*$curSymbol['last'],2).'</span>';
		$row['change']=$curSymbol['change'].($curSymbol['percentChange']!='-'?'<span>('.$curSymbol['percentChange'].'%)</span>':'<span>-</span>');
		//				$row[]=$curSymbol['percentChange'];

		$currentMargin=$marketPrice*$rs->netBalance-$rs->costBalance;
		$row['margin']=($currentMargin > 0 ? '<i style="color:green">+' : '<i style="color:red;">').number_format($currentMargin,2).'</i>'.($currentMargin > 0 ? '<span style="color:green">(+' : '<span><span style="color:red;">(').number_format($currentMargin*100/($rs->costBalance),2).'%)</span>';
		$row['allmargin']=($itemMargin > 0 ? '<i style="color:green">+' : '<i style="color:red;">').number_format($itemMargin,2).'</i>'.($itemMargin > 0 ? '<span style="color:green">(+' : '<span><span style="color:red;">(').number_format($itemMargin*100/($cost*$balanceVolumes),2).'%)</span>';

		//				$row[]=($itemMargin > 0 ? '<span style="color:green">+' : '<span style="color:red;">').number_format($itemMargin*100/($cost*$balanceVolumes),2).'</span>';
		$row['bid']=$curSymbol['bid'];
		$row['offer']=$curSymbol['offer'];
		$row['mvolume']=$curSymbol['volume'];
		$row['mvalue']=$curSymbol['value'];
		$row['add']='< class="sg-action"a href="'.url('set/add/'.$rs->symbol).'" data-rel="#app-output">+</a>';
		$row['config']=array('class'=>$sign=='+'?'set-plus':($sign=='-'?'set-minus':'set-normal'));
		$ports[$portId]->rows[]=$row;

		$ul.='<li class="'.$row['config']['class'].'">';
		$ul.='<h3'.($row['last']<=$rs->wishprice?' class="set-buy-alert"':'').'>'.$row['symbol'].($rs->wishprice?' ('.$rs->wishprice.')':'').'</h3>';
		$ul.='<div class="set-symbol-list-price-last"><p>'.number_format($row['last'],2).'</p><p>'.$row['change'].'</p></div>'._NL;
		$ul.='<div class="set-symbol-list-price-price"><ul><li><span>High<br />Open<br />Low</span><span>'.$row['open'].'</span></li><li><span>Bid</span><span>'.$row['bid'].'</span></li><li><span>Offer</span><span>'.$row['offer'].'</span></li><li><span>Volume</span><span>'.number_format($row['mvolume']).'</span></li><li><span>Value</span><span>'.number_format($row['mvalue']).'</span></li></ul></div>'._NL;
		$ul.='<a href="//marketdata.set.or.th/mkt/realtimestockchart?symbol='.$rs->symbol.'&type=accVolume" class="sg-show"><img src="https://marketdata.set.or.th/mkt/realtimestockchart?symbol='.$rs->symbol.'&type=accVolume" width="100%" height="100" /></a>';
		$ul.='</li>'._NL;

	}
	$ul.='</ul>';
	$ret.=$ul;
	return $ret;
}
?>