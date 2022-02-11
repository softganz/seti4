<?php
/**
* Show stock card of symbol
*
* @param String $symbol
* @param Integer $_REQUEST['delete'] Id of card to delete
* @return String
*/
function set_card($self,$symbol) {
	if ($symbol && $_REQUEST['delete']) {
		mydb::query('DELETE FROM %setport% WHERE `id`=:id AND `uid`=:uid LIMIT 1',':id',$_REQUEST['delete'], ':uid',i()->uid);
		R::Model('set.balance.repair',$symbol);
		return;
	}
	$ret.=R::Page('set.portstatus',$self,$symbol);
	$stmt='SELECT p.*,
						g.`name` portname, d.`close`,
						o.`name` `ownerName`
					FROM %setport% p
						LEFT JOIN %setgroup% g USING(gid)
						LEFT JOIN %setdaily% d ON p.`symbol`=d.`symbol` AND p.`date`=d.`date`
						LEFT JOIN %users% o ON o.`uid`=p.`ownid`
					WHERE p.`symbol`=:symbol AND p.`uid`=:uid
					ORDER BY g.`name` ASC, p.`date` ASC, p.`id` ASC';
	$dbs=mydb::select($stmt,':symbol',$symbol,':uid',i()->uid);
	$totals=$volumes=$costBalance=0;

	if ($dbs->_empty) return $ret;
	$tables = new Table();
	$tables->caption='Stock Card';
	$tables->class='item set-card';
	$tables->thead=array('Port','วันที่','เจ้าของ','bsd','จำนวนหุ้น','ต้นทุน/หน่วย','ต้นทุนซื้อ','ต้นทุนขาย','จำนวนคงเหลือ','ต้นทุนคงเหลือ','กำไร/ขาดทุน','เฉลี่ย','<a class="sg-action" href="'.url('set/repairbalance/'.$symbol).'" data-rel="#set-info">Repair</a>');
	foreach ($dbs->items as $rs) {
		$ports[$rs->gid][]=$rs;
	}
	foreach ($ports as $cards) {
		unset($tables->rows,$tables->tfoot);
		$totalBuy=$totalSale=$volumes=$costBalance=0;
		$tables->id='set-card';

		$graph=NULL;
		$graph->title=$symbol.' : Price Average';
		$graph->items[]=array('Date', 'Cost Buy/Sale', 'Cost AVG','Market');

		foreach ($cards as $rs) {
			$sign=$rs->bsd=='S'?'-':'+';
			if ($rs->bsd=='S') {
				$totalSale+=$rs->netamount;
				$volumes-=$rs->volumes;
				$costBalance-=$rs->netamount;
			} else {
				$totalBuy+=$rs->netamount;
				$volumes+=$rs->volumes;
				$costBalance+=$rs->netamount;
			}
			if ($volumes==0) {
				$totalMargin-=$costBalance;
				$costBalance=0;
			}
			$symbolTitle='Amount='.number_format($rs->volumes*$rs->price)._NL.' Commission='.$rs->commission._NL.' Trading fee='.$rs->tradingfee._NL.' Charging fee='.$rs->chargingfee._NL.' vat='.$rs->vat._NL.' Net amount='.$rs->netamount._NL.'Balance='.$rs->balance;
			$tables->rows[]=array(
													$rs->portname,
													sg_date($rs->date,'ว ดด ปปปป'),
													$rs->ownerName,
													$rs->bsd,
													$sign.number_format($rs->volumes),
													number_format($rs->price,2),
													in_array($rs->bsd,array('B','D'))?number_format($rs->netamount,2).'<sup title="'.$symbolTitle.'">?</sup>':'',
													$rs->bsd=='S'?number_format($rs->netamount,2).'<sup title="'.$symbolTitle.'">?</sup>':'',
													number_format($volumes),
													number_format($costBalance,2),
													number_format($totalMargin,2),
													number_format($costBalance/$volumes,3),
													'<a href="'.url('set/card/'.$symbol,'delete='.$rs->id).'" class="sg-action" data-rel="#app-output" data-ret="'.url('set/view/'.$symbol).'" data-confirm="ยืนยันการลบรายการ?">X</a>',
													);
			if ($costBalance==0) {
				$lastGraph=end($graph->items);
				$avgCost=$lastGraph['2'];
			} else {
				$avgCost=$costBalance/$volumes;
			}
			$graph->items[]=array(sg_date($rs->date,'d M y'),round($rs->price,2),round($avgCost,2),round($rs->close,2));
			if ($costBalance==0) $graph->items[]=array('Solded', NULL,NULL,NULL);
		}
		$tables->tfoot[]=array('','','','','คงเหลือ','','','', number_format($volumes),number_format($costBalance,2),number_format($totalMargin,2),number_format($costBalance/$volumes,3),'');
		$tables->tfoot[]=array('','','','','สุทธิ','',number_format($totalBuy,2),number_format($totalSale,2), number_format($volumes),number_format($totalBuy-$totalSale,2),'',number_format(($totalBuy-$totalSale)/$volumes,3),'');
		if ($volumes==0) $tables->tfoot[]=array('','','','','','','','','',number_format($totalSale-$totalBuy,2),'','','');
		$lastPrice=mydb::select('SELECT * FROM %setdaily% WHERE `date`=(SELECT MAX(`date`) lastdate FROM %setdaily%) AND `symbol`=:symbol LIMIT 1',':symbol',$symbol);
		$lastGraph=end($graph->items);
		$lastGraph[0]=sg_date($lastPrice->date,'d M y');
		$lastGraph[3]=round($lastPrice->close,2);
		$graph->items[]=$lastGraph;
		//$ret.=print_o($lastGraph,'$lastGraph');
		//$ret.=print_o($lastPrice,'$lastPrice');
		$ret.='<div id="chart_div" style="width: 100%; height: 400px;"></div>';
		$ret.=$tables->build();
		//$ret.=print_o($graph,'$graph');
	}

	$ret.='<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"], callback: drawChart});
	var graph='.json_encode($graph).'
	function drawChart() {
		if (graph) {
			var data = google.visualization.arrayToDataTable(graph.items);
			var options = {title: graph.title,hAxis: {title: "Date" , slantedText:true, slantedTextAngle:90,textStyle: {fontSize: 10} }};
			var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
			chart.draw(data, options);
		}
	}
	</script>';

	return $ret;
}
?>