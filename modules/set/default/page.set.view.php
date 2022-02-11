<?php
/**
* Show symbol information
*
* @param String $symbol
* @param String $action
* @return String
*/
function set_view($self, $symbol = NULL, $action = NULL) {
	$symbol = trim(strtoupper(SG\getFirst($symbol,$_REQUEST['symbol'])));

	$ret = '';

	if (strpos($symbol,',')) {
		return R::Page('set.watch',$self,$symbol);
	} else if ($action) {
		switch ($action) {
			case 'card' :
				$ret .= R::Page('set.card',$self,$symbol);
				break;

			case 'info' :
				$ret .= '<iframe class="set-symbol-info" src="//www.set.or.th/set/factsheet.do?language=th&country=TH&symbol='.$symbol.'"></iframe>';
				break;

			case 'statement' :
				$ret .= '<iframe src="//www.set.or.th/set/companyhighlight.do?language=th&country=TH&symbol='.$symbol.'" style="width:100%;height:800px;border:none;"></iframe>';
				break;

			case 'marketanywarechartday' :
				$ret .= '<a href="//app.marketanyware.com/home/chartonly?stock='.$symbol.'&h=900" target="_blank">View in new window</a><iframe src="//app.marketanyware.com/home/chartonly?stock='.$symbol.'&h=600" style="width:100%;height:650px;border:none;"></iframe>';
				break;

			case 'marketanywarechart15min' :
				$ret .= '<iframe src="//app.marketanyware.com/'.$symbol.'" style="width:100%;height:650px;border:none;"></iframe>';
				break;

			case 'import' :
				$ret .= R::Page('set.importdaily',$self,$symbol);
				break;
		}
		$ret .= _NL;
		return $ret;
	}





	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -page", id: "set-nav"}');

	if (user_access('create set content')) {
		$ui->add('<a class="sg-action" href="'.url('set/view/'.$symbol).'" data-rel="#app-output" title=""><strong>'.$symbol.'</strong></a>');
		$ui->add('<a class="sg-action" href="'.url('set/view/'.$symbol.'/card').'" data-rel="#set-info" title="รายการซื้อ/ขาย">การ์ด</a>');
		$ui->add('<a class="sg-action" href="'.url('set/add/'.$symbol).'" data-rel="#set-info" title="บันทึกรายการซื้อ/ขาย">ซื้อ/ขาย</a>');
	}

	$ui->add('<a class="sg-action" href="'.url('set/view/'.$symbol.'/info').'" data-rel="#set-info" title="ข้อมูลหุ้น">ข้อมูลหุ้น</a>');
	$ui->add('<a class="sg-action" href="'.url('set/view/'.$symbol.'/statement/').'" data-rel="#set-info" title="งบการเงิน">งบการเงิน</a>');
	$ui->add('<a href="https://www.google.com/search?q='.$symbol.' ข่าว หุ้น&tbs=qdr:d" target="_blank">ข่าว</a>');
	$ui->add('<a href="https://www.tradingview.com/chart/?symbol=SET:'.$symbol.'" target="_blank">TradingView</a>');

	if (user_access('create set content')) {
		$ui->add('<a class="sg-action" href="'.url('set/setting/'.$symbol).'" data-rel="#set-info">Setting</a>');
	}

	$ret .= $ui->build();

	$ret .= '<div id="set-info">'._NL;
	$ret .= R::Page('set.portstatus',$self,$symbol);

	$chartGraph = cfg('set.graph');

	$ret .= '<div class="set-chart">';

	switch ($chartGraph) {
		case 'tradingview' :
			// If not used, Remove load javascript from manifest
			$ret .= '<!-- TradingView Widget BEGIN -->
				<div class="tradingview-widget-container">
				<div id="tradingview_8968e"></div>
				<div class="tradingview-widget-copyright"><a href="//www.tradingview.com/chart/?symbol=SET:'.$symbol.'" rel="noopener" target="_blank"><!-- <a href="https://www.tradingview.com/symbols/SET-'.$symbol.'/" rel="noopener" target="_blank"> --><span class="blue-text">'.$symbol.' Chart</span></a> by TradingView</div>
				<script type="text/javascript">
				new TradingView.widget(
					{
						"width": "100%",
						"height": 610,
						"symbol": "SET:'.$symbol.'",
						"interval": "D",
						"timezone": "Asia/Bangkok",
						"theme": "light",
						"style": "1",
						"locale": "en",
						"toolbar_bg": "#f1f3f6",
						"enable_publishing": false,
						"allow_symbol_change": true,
						"container_id": "tradingview_8968e"
					}
				)
				</script>
				</div>
				<!-- TradingView Widget END -->';
			break;

		case 'invertor':
			//investor
			$ret .= '<iframe class="set-chart-iframe" src="//chart.investor.co.th/achart/?'.$symbol.'"></iframe>';
			break;

		case 'bidchart/tv':
			// bidchart/tv
			$ret .= '<iframe class="set-chart-iframe" src="//chart.bidschart.com/tv/'.$symbol.'"></iframe>';
			break;

		case 'bidchart/chart':
			// bidchart/chart
			$ret .= '<iframe class="set-chart-iframe" src="//chart.bidschart.com/'.$symbol.'"></iframe>';
			break;

		case 'marketanyware/script':
			// MarketAnyWare web app
			$ret .= '<script src="//marketanyware.com/apijs/marketanyware-chart-container2.js" type="text/javascript" data-config="{width:950,height:550}"></script>';
			$ret .= '<div class="ai-marketanyware-chart-container" width="950" height="550">';
			break;

		case 'marketanyware/app':
			$ret .= '<iframe class="set-chart-iframe" scrolling="no" allowtransparency="true" src="//app.marketanyware.com/home/chartonly?stock='.$symbol.'&amp;h=465" frameborder="0" height="550" width="950"></iframe>';
			break;

		case 'investor.img':
			$ret .= '<img src="https://www.investorz.com/investorzChart.php?mode=pc&Provider=DDC&symbolnsources='.$symbol.'*BK&Size=1080*1188&End=20220114&Start=&period=Daily&interval=1&Cycle=DAY1&Type=3&ticket=1&Scale=0&EMA=10;50&MA=&OVER=TS.MACDX(12,26,9);;OverlayV!;;&IND=MACD(12,26,9);RSI(14);ADX(14);;&COMP=&ShowBar=100&max=400&Skin=InvestorLight&Layout=2Line;Default;Price;HisDate&Width=1&TimeStamp=13;50;1642056633.028" width="100%" />';
			$ret .= '<style>.set-chart {height: auto;}</style>';
			break;

		case 'investorz':
			$ret .= '<iframe class="set-chart-iframe" src="//www.investorz.com/chart/'.$symbol.'.bk/"></iframe>';
			break;

		case 'siamchart':
			$ret .= '<iframe class="set-chart-iframe" src="//siamchart.com/stock-chart/'.$symbol.'/"></iframe>';
			break;
	}


	$chartNav = new Ui();
	$chartNav->addConfig('nav', '{class: "nav -page"}');

	$chartNav->add('<a class="btn -link" href="//www.tradingview.com/chart/?symbol=SET:'.$symbol.'" target="_blank">Trading View</a>');
	$chartNav->add('<a class="btn -link" href="//chart.bidschart.com/'.$symbol.'/" target="_blank">BidsChart</a>');
	$chartNav->add('<a class="btn -link" href="//chart.bidschart.com/tv/'.$symbol.'" target="_blank">BidsChart</a>');
	$chartNav->add('<a class="btn -link" href="//www.investorz.com/chart/'.$symbol.'.bk" target="_blank">Investor</a>');
	$chartNav->add('<a class="btn -link" href="//app.marketanyware.com/'.$symbol.'" target="_blank">MarketAnyware</a>');
	$chartNav->add('<a class="btn -link" href="//siamchart.com/stock-chart/'.$symbol.'/" target="_blank">SiamChart</a>');
	$ret .= $chartNav->build();

	$ret .= '</div><!-- set-chart -->'._NL;

	$ret .= '<div class="set-symbol-side">';
	$ret .= R::Page('set.note',$self,$symbol);
	$ret .= '</div>';

	$ret .= '</div><!-- set-info -->'._NL;

	$ret .= '<script type="text/javascript">$("#set-title>span").html(" - '.$symbol.'");</script>';

	mydb::query('INSERT INTO %sethistory% (`uid`,`symbol`,`msg`,`created`) VALUES (:uid, :symbol, :msg, :created)',':uid',i()->uid, ':symbol',$symbol, ':msg','', ':created',date('U'));
	//$ret .= '<script>$(".set-symbol-side, .set-symbol-marketanyware").mCustomScrollbar({theme:"dark"});</script>';

	return $ret;
}
?>