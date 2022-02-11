<?php
/**
* Show port status
*
* @param date $_GET['d'] Date to show port status
* @param Integer $_GET['u'] User ID for admin only
* @return String
*/
function set_portstatus($self, $symbol = NULL) {
	$uid = i()->uid;
	if (user_access('administrator sets') && post('u')) {
		$uid = post('u');
	}
	if (post('d')) {
		$date = post('d');
	}
	if (post('prev')) {
		$date = mydb::select('SELECT `date` FROM %setdaily% WHERE `date` < :date ORDER BY `date` DESC LIMIT 1',':date',post('prev'))->date;
	}
	if (post('next')) {
		$date = mydb::select('SELECT `date` FROM %setdaily% WHERE `date` > :date ORDER BY `date` ASC LIMIT 1',':date',post('next'))->date;
	}



	// Show alert monitor for all view
	if (empty($symbol)) {
		$ret .= '<div id="set-tabs" class="sg-tabs"><ul class="tabs"><li class="-active"><a href="#set-portstatus-1">Current</a></li><li><a href="#set-portstatus-2">Card</a></li><li><a href="#set-portstatus-3">Sold</a></li></ul><div id="set-portstatus-1">';

		// Show wish symbol
		$alertPercent = 5;
		$stmt = 'SELECT DISTINCT `symbol`, `wishprice` FROM %setwishlist% WHERE `uid`=:uid AND `wishprice`>0 ORDER BY `symbol` ASC';
		$dbs = mydb::select($stmt,':uid',i()->uid);

		if ($dbs->_num_rows) {
			foreach ($dbs->items as $rs) {
				$curSymbol=$currentPrice[$rs->symbol];
				if (empty($curSymbol['last'])) continue;
				$sign=strlen(trim($curSymbol['change']))==1 ? '' : substr($curSymbol['change'],0,1);
				$alertPrice=$rs->wishprice+$rs->wishprice*$alertPercent/100;
				if ($curSymbol['last']>$alertPrice) continue;
				if ($curSymbol['change']=='-') {
					$setClass='set-notchange';
				} else if ($curSymbol['change']>=0) {
					$setClass='set-up';
				} else {
					$setClass='set-down';
				}
				$alertSymbol.='<li>';
				$alertSymbol.='<h3'.($curSymbol['last']<=$rs->wishprice?' class="set-buy-alert"':'').'><a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output" data-webview="'.$rs->symbol.'">'.$curSymbol['symbol'].'</a><span>'.($rs->wishprice?' ('.$rs->wishprice.')':'').'</span></h3>';
				$alertSymbol.='<div class="set-symbol-list-price-last '.$setClass.'">'.number_format($curSymbol['last'],2).'<br />('.$curSymbol['change'].')</div>'._NL;
				//$alertSymbol.='<a href="http://marketdata.set.or.th/mkt/realtimestockchart?symbol='.$rs->symbol.'&type=accVolume" class="sg-show"><img src="https://marketdata.set.or.th/mkt/realtimestockchart?symbol='.$rs->symbol.'&type=accVolume" width="100%" height="100" /></a>';
				$alertSymbol.='</li>'._NL;
			}
			if ($alertSymbol) $ret.='<ul class="set-symbol-card set-price-monitor">'.$alertSymbol.'</ul>';
		}
	}



	mydb::where('p.`uid` = :uid ',':uid',$uid);
	mydb::where('p.`symbol` NOT IN ("D","Interest")');
	if ($symbol) mydb::where('p.`symbol` = :symbol',':symbol',$symbol);
	if ($date) mydb::where('p.`date` <= :date',':date',$date);

	$stmt = 'SELECT
		p.`gid`, p.`symbol`, p.`date`
		, g.`name` portname
		, w.`wishprice`, w.`saleprice`,
		SUM(CASE bsd WHEN "B" then `volumes` END) `buyVolumes`,
		SUM(CASE bsd WHEN "S" then `volumes` END) `saleVolumes`,
		SUM(CASE bsd WHEN "B" then `netamount` END) `buyPrice`,
		SUM(CASE bsd WHEN "S" then `netamount` END) `salePrice`,
		SUM(CASE bsd WHEN "B" then `balance` END) `netBalance`,
		SUM(CASE bsd WHEN "B" then `balance`*(`netamount`/`volumes`) END) `costBalance`
		FROM %setport% p
			LEFT JOIN %setgroup% g USING(`gid`)
			LEFT JOIN %setwishlist% w ON w.`gid`=g.`gid` AND w.`symbol`=p.`symbol`
		%WHERE%
		GROUP BY p.`gid`, p.`symbol`
		ORDER BY g.`name` ASC, p.`symbol` ASC';

	$dbs = mydb::select($stmt,$where['value']);

	$symbolList = array();
	foreach ($dbs->items as $rs) $symbolList[$rs->symbol] = $rs->symbol;

	// Get Current Price from SET
	$currentPrice=R::Model('set.price.realtime.get',$date, $symbolList);
	//$ret.=print_o($currentPrice,'$currentPrice');

	$setIndex = $currentPrice['SET'];

	if ($setIndex['change'] == '-') {
		$setClass = 'set-notchange';
	} else if ($setIndex['change'] >= 0) {
		$setClass = 'set-up';
	} else {
		$setClass = 'set-down';
	}

	$setStr = '<span class="set-index"><a class="sg-action" href="'.url('set/portstatus').'" data-rel="#app-output">SET</a> '.number_format($currentPrice['SET']['last'],2).' ('.$currentPrice['SET']['change'].') '.number_format($setIndex['value']).'M</span>';



	// If no symbol, Show ???
	if ($dbs->_empty) {
		$curSymbol = $currentPrice[$symbol];
		$sign = strlen(trim($curSymbol[6]))==1 ? '' : substr($curSymbol[6],0,1);
		$tables = new Table();
		$tables->addClass('set-symbol-list-price');
		$tables->thead = array(
			'Symbol',
			'<span title="High / Open / Low">O</span>',
			'Last',
			'<span title="Change">Ch</span>',
			'Bid',
			'Offer',
			'<span title="Volume(Shares)">Volume</span>',
			'<span title="Value(000 Baht)">Value</span>',
			''
		);

		unset($row);
		$row['symbol']='<a class="sg-action" href="'.url('set/view/'.$symbol).'" data-rel="#app-output" data-webview="'.$rs->symbol.'">'.$symbol.'</a>'
											.($curSymbol['sign']?'('.$curSymbol['sign'].')':'');
		$row['open']='<span class="set-symbol-high">'.$curSymbol['high'].'</span><span class="set-symbol-open">'.$curSymbol['open'].'</span><span class="set-symbol-low">'.$curSymbol['low'].'</span>';
		$row['last']=number_format($curSymbol['last'],2);
		$row['change']=$curSymbol['change'].($curSymbol['percentChange']!='-'?'<span>('.$curSymbol['percentChange'].'%)</span>':'<span>-</span>');
		$row['bid']=is_numeric($curSymbol['bid'])?number_format($curSymbol['bid'],2):$curSymbol['bid'];
		$row['offer']=is_numeric($curSymbol['offer'])?number_format($curSymbol['offer'],2):$curSymbol['offer'];
		$row['mvolume']=number_format($curSymbol['volume']/1000000,3).'M';
		$row['mvalue']=number_format($curSymbol['value']/1000,1).'M';
		$row['add']='<a class="sg-action" href="'.url('set/add/'.$rs->symbol).'" data-rel="#app-output" title="บันทึกรายการซื้อ/ขาย">+</a>';
		$row['config']=array('class'=>$sign=='+'?'set-plus':($sign=='-'?'set-minus':'set-normal'));

		$tables->rows[]=$row;
		$ret.=$tables->build();
		return $ret;
	}




	// Show symbol port summary and status
	foreach ($dbs->items as $rs) {
		$portId = $rs->gid;
		$curSymbol = $currentPrice[$rs->symbol];
		$sign = strlen(trim($curSymbol[6]))==1 ? '' : substr($curSymbol[6],0,1);

		$marketPrice = $curSymbol['last'];
		$balanceCost = $rs->buyPrice-$rs->salePrice;
		$balanceVolumes = $rs->buyVolumes-$rs->saleVolumes;

		$openPrice = '<span class="-set-up">'.($curSymbol['high'] ? number_format($curSymbol['high'],2) : '-').'</span>'
			. '<span class="-set-normal">'.($curSymbol['open'] ? number_format($curSymbol['open'],2) : '-').'</span>'
			. '<span class="-set-down">'.($curSymbol['low'] ? number_format($curSymbol['low'],2) : '-').'</span>';

		unset($row);
		// Close round
		if ($balanceVolumes == 0) {
			$portClosed[$portId]->portname = $rs->portname;
			$itemMargin = $rs->salePrice - $rs->buyPrice;
			$portClosed[$portId]->closeBuy += $rs->buyPrice;
			$portClosed[$portId]->closeSale += $rs->salePrice;
			$row = array(
				'<a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output" data-webview="'.$rs->symbol.'">'.$rs->symbol.'</a>',
				// High/Open/Low Price
				$openPrice,
				// Port Volume
				number_format($rs->buyVolumes),
				// Cost
				number_format($rs->buyPrice/$rs->buyVolumes,4)
				.'<span>'.number_format($rs->buyPrice,2).'</span>',
				// Last
				number_format($curSymbol['last'],2),
				// Change
				$curSymbol['change'].($curSymbol['percentChange']!='-'?'<span>('.$curSymbol['percentChange'].'%)</span>':'<span>-</span>'),
				// Un P/L
				number_format($rs->salePrice/$rs->buyVolumes,4)
				. '<span>'.number_format($rs->salePrice,2).'</span>',
				// Un + P/L
				'<span class="-'.($itemMargin > 0 ? '-plus' : '-minus').'">'
				. ($itemMargin > 0 ? '+' : '').number_format($itemMargin,2)
				. '<span>('.($itemMargin > 0 ? '+' : '').number_format($itemMargin*100/$rs->buyPrice,2).'%)'.'</span>'
				. '</span>',
				// Bid
				is_numeric($curSymbol['bid'])?number_format($curSymbol['bid'],2):$curSymbol['bid'],
				// Offer
				is_numeric($curSymbol['offer'])?number_format($curSymbol['offer'],2):$curSymbol['offer'],
				// Volume
				number_format($curSymbol['volume']/1000000,$curSymbol['volume']>1000000?1:3).'M',
				// Value
				number_format($curSymbol['value']/1000,$curSymbol['value']>1000?1:3).'M',
				'<a class="sg-action" href="'.url('set/add/'.$rs->symbol).'" data-rel="#app-output">+</a>',
				'config'=>array('class'=>$sign=='+'?'set-plus':($sign=='-'?'set-minus':'set-normal')),
			);
			$portClosed[$portId]->rows[] = $row;
		} else {
			// If symbol in port
			$ports[$portId]->portname = $rs->portname;
			$itemCost = $rs->costBalance/$rs->netBalance;
			$allCost = $balanceCost/$balanceVolumes;
			$marketValue = $balanceVolumes*$marketPrice;
			$itemMargin = ($marketPrice-$allCost)*$balanceVolumes;

			$currentMargin = $marketPrice*$rs->netBalance-$rs->costBalance;

			$ports[$portId]->allCost += $balanceCost;
			$ports[$portId]->marketValues += $marketValue;
			$ports[$portId]->marginPL += $marketValue-$amountPrice;
			$ports[$portId]->currentMarginPL += $marketValue-$rs->costBalance;
			$ports[$portId]->currentCost += $rs->costBalance;
			$ports[$portId]->symbol[$rs->symbol] = $rs;
			$ports[$portId]->symbol[$rs->symbol]->marketPrice = $marketPrice;
			$ports[$portId]->symbol[$rs->symbol]->currentMargin = $currentMargin;
			$ports[$portId]->symbol[$rs->symbol]->currentMarginPercent = number_format($currentMargin*100/($rs->costBalance),2);

			$isSale = $rs->saleprice&&$curSymbol['last']>=$rs->saleprice;
			$isWish = $rs->wishprice&&$curSymbol['last']<=$rs->wishprice;

			$row = array();

			$row['symbol'] = '<a class="sg-action" href="'.url('set/view/'.$rs->symbol).'" data-rel="#app-output" data-webview="'.$rs->symbol.'">'.$rs->symbol.'</a>'
				.($curSymbol['sign']?'('.$curSymbol['sign'].')':'')
				.($rs->saleprice || $rs->wishprice ? '<span>'.($isWish ? '<span class="-set-down">' : '').'B:'.number_format($rs->wishprice,2).($isWish?'</span>':'').'&nbsp;'.($isSale?'<span class="-set-down">':'').'S:'.number_format($rs->saleprice,2).($isSale?'</span>':'').'</span>' : '');

			$row['open'] = $openPrice;

			$row['volume'] = number_format($balanceVolumes);

			$row['cost'] = number_format($itemCost,2).'<span title="Cost='.number_format($itemCost,4).'">'.number_format($rs->costBalance,2).'</span>';

			$row['last'] = number_format($curSymbol['last'],2).'<span>'.number_format($balanceVolumes*$curSymbol['last'],2).'</span>';

			$row['change -set-'.($curSymbol['percentChange'] >= 0 ? 'up' : 'down')] = $curSymbol['change']
				. '<span>('.($curSymbol['percentChange'] != '-'?''.$curSymbol['percentChange'].'%':'-').')</span>';

			$row['margin -set-'.($currentMargin > 0 ? 'up' : 'down')] = ($currentMargin > 0 ? '+' : '')
				. number_format($currentMargin,2)
				. '<span>('.($currentMargin > 0 ? '+' : '').number_format($currentMargin*100/($rs->costBalance),2).'%)</span>';

			$row['margin-all -set-'.($itemMargin > 0 ? 'up' : 'down')] = ($itemMargin > 0 ? '+' : '')
				. number_format($itemMargin,2)
				. '<span>('.($itemMargin > 0 ? '+' : '').number_format($itemMargin*100/($allCost*$balanceVolumes),2).'%)</span>';

			//				$row[]=($itemMargin > 0 ? '<span style="color:green">+' : '<span style="color:red;">').number_format($itemMargin*100/($cost*$balanceVolumes),2).'</span>';
			$row['bid']=is_numeric($curSymbol['bid'])?number_format($curSymbol['bid'],2):$curSymbol['bid'];

			$row['offer']=is_numeric($curSymbol['offer'])?number_format($curSymbol['offer'],2):$curSymbol['offer'];

			$row['mvolume']=number_format($curSymbol['volume']/1000000,$curSymbol['volume']>1000000?1:3).'M';

			$row['mvalue']=number_format($curSymbol['value']/1000,$curSymbol['value']>1000?1:3).'M';

			$row['add']='<a class="sg-action" href="'.url('set/add/'.$rs->symbol).'" data-rel="#app-output" title="บันทึกรายการซื้อ/ขาย">+</a>';

			$row['config']=array('class'=>$sign=='+'?'-set-up':($sign=='-'?'-set-down':'-set-normal'));

			$ports[$portId]->rows[$rs->symbol] = $row;
		}
	}



	// Show symbol in each port
	$tables = new Table();
	$tables->addClass('set-symbol-list-price active');
	$tables->attr='cellspacing="0" cellpadding="0"';

	foreach ($ports as $portId => $portValue) {
		$allPortCostValue+=$portValue->currentCost;
		$allPortMarketValue+=$portValue->marketValues;
		$margin=$portValue->marketValues-$portValue->allCost;
		$curMargin=$marketPrice*$portValue->netBalance-$portValue->costBalance;
		$tables->rows[]=array('<th colspan="14">'.$portValue->portname.'<span> : '.$setStr.'</span></th>');
		$ul.='<h2>'.$portValue->portname.' : '.$setStr;
		if (!$symbol) {
			$ul.=' '.($portValue->currentMarginPL>=0?'<span style="color:green">Margin +':'<span style="color:red;">Margin ').number_format($portValue->currentMarginPL,2).'</span>'.($portValue->currentMarginPL>=0?'<span><span style="color:green">(+':'<span style="color:red;">(').number_format($portValue->currentMarginPL*100/$portValue->currentCost,2).'%)</span></span>';
		}
		$ul.='</h2>';
		$ul.='<ul class="set-symbol-list-price">';
		//$ret.=print_o($portValue,'$portValue');
		foreach ($portValue->rows as $key=>$row) {
			$percentOfPort=number_format($portValue->symbol[$key]->costBalance*100/$portValue->currentCost,2);
			//$percentOfMargin=$portValue->symbol[$key]->currentMarginPercent;
			$percentOfMargin=number_format($portValue->symbol[$key]->currentMargin*100/$portValue->currentMarginPL,2);
			$row['symbol']='<div class="set-symbol-percent--cost" style="width:'.$percentOfPort.'%;" title="Cost '.$percentOfPort.'% of port.">'.$percentOfPort.'</div><div class="set-symbol-percent--margin'.($percentOfMargin<0?' minus':'').'" style="width:'.(abs($percentOfMargin)>100?99:abs($percentOfMargin)).'%;" title="Margin '.$percentOfMargin.'% of port.">'.$percentOfMargin.'</div>'.$row['symbol'];
			$tables->rows[]=$row;
			$ul.='<li class="'.$row['config']['class'].'">';
			$ul.='<h3>'.$row['symbol'].'</h3>';
			$ul.='<div class="set-symbol-list-price-last"><p>'.$row['last'].'</p><p>'.$row['change'].'</p></div>'._NL;
			$ul.='<div class="set-symbol-list-price-cost"><div><h4>Cost</h4>'.$row['cost'].'</div><div><h4>Market</h4>'.$row['last'].'</div><div><h4>Un P/L</h4>'.$row['margin'].'</div></div>'._NL;
			$ul.='<div class="set-symbol-list-price-price"><ul><li><span>High<br />Open<br />Low</span><span>'.$row['open'].'</span></li><li><span>Bid</span><span>'.$row['bid'].'</span></li><li><span>Offer</span><span>'.$row['offer'].'</span></li><li><span>Volume</span><span>'.$row['mvolume'].'</span></li><li><span>Value</span><span>'.$row['mvalue'].'</span></li></ul></div>'._NL;
			$ul.='<a href="http://marketdata.set.or.th/mkt/realtimestockchart?symbol='.$key.'&type=accVolume" class="sg-show"><img src="https://marketdata.set.or.th/mkt/realtimestockchart?symbol='.$key.'&type=accVolume" width="100%" height="100" /></a>';
			$ul.='</li>'._NL;
		}
		unset($tables->tfoot);

		if (!$symbol) {
			$tables->rows[] = array(
				'Total','','',
				'cost' => number_format($portValue->currentCost,2).'<br />('.number_format($portValue->allCost,2).')',
				'last' => number_format($portValue->marketValues,2).'<br />&nbsp;',
				'',
				'margin -set-'.($portValue->currentMarginPL>=0 ? 'up' : 'down') => ($portValue->currentMarginPL >= 0 ? '+' : '').number_format($portValue->currentMarginPL,2)
					. '<span>('.($portValue->currentMarginPL >= 0 ? '+' : '')
					. number_format($portValue->currentMarginPL*100/$portValue->currentCost,2)
					. '%)</span>',
				'margin-all -set-'.($margin>=0 ? 'up' : 'down') => ($margin >= 0 ? '+' : '').number_format($margin,2)
					. '<span>('
					. ($margin >= 0 ? '+' : '')
					. number_format($margin*100/$portValue->allCost,2)
					. '%)</span>',
				'',
				'',
				'',
				'',
				'',
				'config' => array('class' => 'subtotal'),
				);
		}
		$ul.='</ul><br clear="all" />';
	}
	//if (i()->username=='softganz') $ret.=print_o($ports,'$ports');
	$allPortMargin = $allPortMarketValue-$allPortCostValue;

	if (empty($symbol)) {
		$tables->caption = '<span class="-set-'.($setIndex['change']>0?'up':'down').'">'
			. $setStr
			. ' @'.($date?$date:date('H:i:s'))
			. ' <a class="sg-action btn -link set-day-nav -prev" href="'.url('set/portstatus',array('prev'=>SG\getFirst($date,date('Y-m-d')))).'" data-rel="#app-output"><i class="icon -material">keyboard_arrow_left</i></a> '
			. '<a class="sg-action btn -link set-day-nav -next" href="'.url('set/portstatus',array('next'=>SG\getFirst($date,date('Y-m-d')))).'" data-rel="#app-output"><i class="icon -material">keyboard_arrow_right</i></a> '
			. '</span>'
			. '<span class="-set-'.($allPortMargin>=0 ? 'up' : 'down').'">Cost='.number_format($allPortCostValue,2).' Market='.number_format($allPortMarketValue,2).' Margin='.number_format($allPortMargin,2).' ('.number_format($allPortMargin*100/$allPortCostValue,2).'%)</span>';
	}

	$tables->thead = array(
		'Symbol',
		'<span title="High / Open / Low">O</span>',
		'Volume',
		'Cost',
		'Last',
		'<span title="Change">Ch</span>',
		'UnP/L',
		'Un+P/L',
		'Bid',
		'Offer',
		'<span title="Volume(Shares)">Volume</span>',
		'<span title="Value(000 Baht)">Value</span>',
		''
	);

	if ($ports) $ret .= $tables->build();

	if (!$symbol) $ret.='</div><!-- tabs 1 -->'._NL;
	// Show symbol card
	if (!$symbol) $ret.='<div id="set-portstatus-2" class="-hidden">'.$ul.'</div><!-- tabs 2 -->';

	// Show portfolio symbol closed
	if (post('show')=='closed') unset($ret);
	//$ret.=print_o($portClosed,'$portClosed');
	$tables = new Table();
	$tables->addClass('set-symbol-list-price -closed');
	$tables->attr='cellspacing="0" cellpadding="0"';
	$tables->thead=array('Symbol','<span title="High / Open / Low">O</span>','Volume','Cost','Last','<span title="Change">Ch</span>','Saled Price','P/L','Bid','Offer','<span title="Volume(Shares)">Volume</span>','<span title="Value(000 Baht)">Value</span>','');
	$tables->caption='หุ้นที่ขายหมดแล้ว';
	unset($tables->tfoot);
	foreach ($portClosed as $portId => $portValue) {
		//$ret.=print_o($portValue,'$portValue');
		$margin=$portValue->marketValues-$portValue->allCost;
		$tables->rows[]=array('<th colspan="15">'.$portValue->portname.'</th>');
		foreach ($portValue->rows as $row) $tables->rows[]=$row;
		unset($tables->tfoot);
		if (!$symbol) {
			$closeMarginPL=$portValue->closeSale-$portValue->closeBuy;
			$tables->rows[] = array(
				'<b>Total</b>',
				'',
				'',
				number_format($portValue->closeBuy,2),
				'',
				'',
				number_format($portValue->closeSale,2),
				($closeMarginPL>=0?'<span style="color:green">+':'<span style="color:red;">').number_format($closeMarginPL,2).'</span><br />'
				.($closeMarginPL>=0?'<span style="color:green">(+':'<span style="color:red;">').number_format($closeMarginPL*100/$portValue->closeBuy,2).'%)</span>',
				'',
				'',
				'',
				'',
				'',
				'',
				'config'=>array('class'=>'subtotal'),
			);
		}
	}
	//$ret.=$tables->build();
	if ($portClosed) $ret.='<div id="set-portstatus-3" class="'.(post('show')=='closed'?'':'-hidden').'">'.$tables->build().'</div>';
	if (!$symbol) $ret.='</div>';
	return $ret;
}
?>