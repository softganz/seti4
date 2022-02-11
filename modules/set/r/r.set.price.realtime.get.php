<?php
function r_set_price_realtime_get($date = NULL, $symbolList = array()) {
	$result=array();

	$unPrice = $symbolList;
	if ($date) {
		$dbs=mydb::select('SELECT `symbol`, "" `sign`, `open`, `high`, `low`, `close` `last`, "" `bid`, "" `offer`, `volume`, `value` FROM %setdaily% WHERE `date`=:date ORDER BY `symbol` ASC',':date',$date);
		//print_o($dbs,'$dbs',1);
		foreach ($dbs->items as $rs) {
			$result[$rs->symbol]=(array)$rs;
		}
		return $result;
	}
	//		$page=file_get_contents(__DIR__.'/price.html');

	// Get file from camera with curl function
	$urlList=array(
							'https://marketdata.set.or.th/mkt/commonstocklistresult.do?market=SET',
							'https://marketdata.set.or.th/mkt/commonstocklistresult.do?market=mai',
							//'//www.settrade.com/C13_MarketSummaryStockType.jsp?type=W'
						);
	// close price http://realtime.bualuang.co.th/myeasy/realtime/quotation/txt/10012014.txt
	foreach ($urlList as $urlId=>$urlName) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlName);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);

		$page = curl_exec($ch);
		curl_close($ch);
		//echo 'Get from '.$urlName.'<br />$Page='.htmlspecialchars($page).'<br />';
		if ($urlId==0 || $urlId==1) {
			preg_match_all('|<tr>\s*?(<td style=\"text-align: left;\">.*?</td>).*?</tr>|s',$page,$matches);
			foreach ($matches[0] as $key => $priceItem) {
				$priceItem=trim($priceItem);
				preg_match_all('|<td.*?>(.*?)</td>|s',$priceItem,$symboleMatchs);
				foreach ($symboleMatchs[1] as $k1=>$v1) $symboleMatchs[1][$k1]=str_replace(',','',trim(strip_tags($v1)));
				if (in_array($symboleMatchs[1][0],array('SET','SET50','SET100','SETHD','mai'))) {
					$symboleMatchs[1]['symbol']=$symboleMatchs[1][0];
					$symboleMatchs[1]['sign']='';
					$symboleMatchs[1]['open']='';
					$symboleMatchs[1]['high']=(float) $symboleMatchs[1][4];
					$symboleMatchs[1]['low']=(float) $symboleMatchs[1][5];
					$symboleMatchs[1]['last']=(float) $symboleMatchs[1][1];
					$symboleMatchs[1]['change']=$symboleMatchs[1][2];
					$symboleMatchs[1]['percentChange']=$symboleMatchs[1][3];
					$symboleMatchs[1]['bid']='';
					$symboleMatchs[1]['offer']='';
					$symboleMatchs[1]['volume']=(float) $symboleMatchs[1][6];
					$symboleMatchs[1]['value']=(float) $symboleMatchs[1][7];
					//print_o($symboleMatchs,'$symboleMatchs',1);
					$result[$symboleMatchs[1][0]]=$symboleMatchs[1];
					if ($symboleMatchs[1]['last']=='-') $unPrice[]=$symboleMatchs[1]['symbol'];
				} else {

					$symboleMatchs[1]['symbol']=$symboleMatchs[1][0];
					$symboleMatchs[1]['sign']=$symboleMatchs[1][1];
					$symboleMatchs[1]['open']=(float) $symboleMatchs[1][2];
					$symboleMatchs[1]['high']=(float) $symboleMatchs[1][3];
					$symboleMatchs[1]['low']=(float) $symboleMatchs[1][4];
					$symboleMatchs[1]['last']=(float) $symboleMatchs[1][5];
					$symboleMatchs[1]['change']=$symboleMatchs[1][6];
					$symboleMatchs[1]['percentChange']=$symboleMatchs[1][7];
					$symboleMatchs[1]['bid']=in_array($symboleMatchs[1][8],array('ATO','ATC'))?$symboleMatchs[1][8]:(float) $symboleMatchs[1][8];
					$symboleMatchs[1]['offer']=in_array($symboleMatchs[1][9],array('ATO','ATC'))?$symboleMatchs[1][9]:(float) $symboleMatchs[1][9];
					$symboleMatchs[1]['volume']=(float) $symboleMatchs[1][10];
					$symboleMatchs[1]['value']=(float) $symboleMatchs[1][11];

					$symbol = $symboleMatchs[1]['symbol'];


					// IEC Only because it SP, remove line on normal sign
					if ($symbol == 'IEC' AND $symboleMatchs[1][1] == 'SP NP NC') {
						$symboleMatchs[1]['last'] = 0.02;
					}

					$result[$symbol] = $symboleMatchs[1];

					//echo $symboleMatchs[1]['symbol'].'='.$symboleMatchs[1][5].'='.$symboleMatchs[1]['last'].'<br />';


					if (empty($symboleMatchs[1]['last'])) {
						$unPrice[$symbol]=$symbol;
					} else {
						unset($unPrice[$symbol]);
					}

				}
			}
		} else if ($urlId==2) {
			preg_match_all('|<div class=tablecolor1>\s*?(<div class=\"fl\" .*?>.*?</div>).*?</div>|s',$page,$matches);
			//print_o($matches,'$matches',1);
		}
	}

	ksort($result);

	//debugMsg($result,'$result');


	if ($unPrice) {
		//debugMsg($unPrice,'$unPrice');
		$lastDate=mydb::select('SELECT MAX(`date`) lastDate FROM %setdaily% LIMIT 1')->lastDate;

		$dbs=mydb::select('SELECT `symbol`, `close` FROM %setdaily% d WHERE d.`date`=:date AND `symbol` IN (:unPrice) ORDER BY `symbol` ASC',':date',$lastDate, ':unPrice','SET-STRING:'.implode(',',$unPrice));

		//debugMsg($dbs,'$dbs');

		foreach ($dbs->items as $rs) {
			$result[$rs->symbol]['last']=$rs->close;
			$result[$rs->symbol]['oldClose']=true;
		}
	}
	return $result;
}
?>