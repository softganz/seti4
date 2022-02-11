<?php
function r_set_price_close_get($date) {
	if (!$date) return;
	$requestDate=sg_date($date,'dmY');
	//		$url='http://realtime.bualuang.co.th/myeasy/realtime/quotation/txt/13012014.txt';
	$url = 'https://realtime.bualuang.co.th/myeasy/realtime/quotation/txt/'.$requestDate.'.txt';

	//debugMsg('Load url = '.$url);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);

	$page = trim(curl_exec($ch));
	curl_close($ch);
	if (preg_match('/<TITLE>404 Not Found<\/TITLE>/i', $page)) return;

	$page = str_replace('S & J', 'S&J', $page);
	// debugMsg('$Page='.htmlspecialchars($page));
	//		preg_match_all("/[a-zA-Z0-9](.*?)[\s](.*?)\n/im",$page,$matches);

	preg_match_all("/[a-zA-Z0-9](.*?)[\s](.*?)\n/im",$page,$matches);
	$matches = preg_split("/\R+/", $page);

	// debugMsg($matches, '$matches');

	foreach ($matches as $key => $priceItem) {
		$priceItem=trim($priceItem);
		$symboleMatchs=preg_split("/\s+/",$priceItem);
		//print_o($symboleMatchs,'$symboleMatchs',1);
		$symboleMatchs[0]=preg_replace('/S\&J/', 'S & J', $symboleMatchs[0]);
		$pdate=$symboleMatchs[1];
		$symbol['symbol']=$symboleMatchs[0];
		$symbol['date']=sg_date($date,'Y-m-d');
		$symbol['pdate']=(substr($pdate,0,2)>70?'19':'20').substr($pdate,0,2).'-'.substr($pdate,2,2).'-'.substr($pdate,'4,2');
		$symbol['open']=$symboleMatchs[2];
		$symbol['high']=$symboleMatchs[3];
		$symbol['low']=$symboleMatchs[4];
		$symbol['close']=$symboleMatchs[5];
		$symbol['volume']=$symboleMatchs[6];
		$symbol['value']=$symboleMatchs[7];
		if (empty($symbol['close'])) continue;
		$result[$symbol['symbol']] = $symbol;
		//			if ($key==5) break;
	}

	// debugMsg($result, '$result');

	return $result;
}
?>