<?php
/**
* API     :: Address API
* Created :: 2022-11-19
* Modify  :: 2022-11-19
* Version :: 2
*
* @return Array
*
* @usage api/address
*/

class AddressApi extends PageApi {
	var $addressText;
	var $page;
	var $items;

	function __construct() {
		parent::__construct([
			'addressText' => SG\getFirst(post('q')),
			'page' => SG\getFirst(post('page'), post('p'), 1),
			'items' => SG\getFirst(post('item'), post('n'), 50)
		]);
	}

	function build() {
		$result = [];

		if (empty($this->addressText)) return $result;

		$addressOut = SG\explode_address($this->addressText);

		$searchText = SG\getFirst($addressOut['tambon'],$addressOut['ampur'],$addressOut['changwat'], $this->addressText);

		// debugMsg('$searchText = '.$searchText);

		if (empty($searchText)) return $result;

		$house = $addressOut['house'].($addressOut['village']?' ม.'.$addressOut['village']:'');

		//print_o($addressOut,'$addressOut',1);

		/*
		if (preg_match('/(.*)(ตำบล|ต\.)(.*)/',$this->addressText,$address)) {
		//		} else if ($address=preg_split('/(ต.|อ.)/',$this->addressText)) {
			$addr=trim($address[1]);
			list($searchText)=explode(' ',trim($address[3]));
			if (preg_match('/([0-9]{5})$/',$this->addressText,$zout)) $zip=$zout[1];
		} else {
			return '[]';
		}
		print_o($address,'$address',1);
		//			foreach ($address as $k=>$v) $result[]=array('value' => 'out['.$k.']','label'=>$k.'='.$v);
		*/

		mydb::value('$LIMIT$', 'LIMIT '.($this->page-1).','.$this->items);
		$stmt = 'SELECT * FROM
			(
			SELECT
				2 `is_tambon`
				, `subdistid` `areacode`
				, SUBSTRING(`subdistid`, 5, 2) `tambonId`
				, `subdistname` `tambonName`
				, SUBSTRING(`distid`, 3, 2) `ampurId`
				, `distname` `ampurName`
				, `provid` `changwatId`
				, `provname` `changwatName`
			FROM %co_subdistrict% co
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`subdistid`,4)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`subdistid`,2)
			WHERE `subdistname` LIKE :q AND RIGHT(`subdistname`,1) != "*"
			-- Select changwat
			UNION
				SELECT
					0 `is_changwat`
					, `provid`
					, NULL
					, NULL
					, NULL
					, NULL
					, `provid`
					, `provname`
				FROM %co_province%
				WHERE `provname` LIKE :q
				-- Select ampur
			UNION
				SELECT
					1 `is_ampur`
					, `distid`
					, NULL
					, NULL
					, `distid`
					, `distname`
					, `provid`
					, `provname`
				FROM %co_district% co
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`distid`,2)
				WHERE `distname` LIKE :q AND RIGHT(`distname`,1) != "*"
			-- Select all tambon in ampur
			UNION
				SELECT
					3 `is_tambon`
					, `subdistid`
					, SUBSTRING(`subdistid`, 5, 2) `tambonId`
					, `subdistname`
					, SUBSTRING(`distid`, 3, 2) `ampurId`
					, `distname`
					, `provid` `changwatId`
					, `provname`
				FROM %co_subdistrict% co
					LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(co.`subdistid`,4)
					LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(co.`subdistid`,2)
				WHERE (`distname` LIKE :q  AND RIGHT(`distname`,1) != "*")
			) a
			GROUP BY `areacode`
			ORDER BY
				`is_tambon` ASC
				, CONVERT(`changwatName` USING tis620) ASC
				, CONVERT(`ampurName` USING tis620) ASC
				, CONVERT(`tambonName` USING tis620) ASC
			$LIMIT$;
			-- {key: "areacode"}
			';

		$dbs = mydb::select($stmt,':q','%'.$searchText.'%');

		// debugMsg('<pre>'.mydb()->_query.'</pre>');
		// debugMsg($address,'$address');
		// debugMsg($dbs,'$dbs');
		foreach ($dbs->items as $rs) {
			$address = SG\implode_address(['house' => $house] + (Array) $rs);
			$label = $address;//SG\implode_address($rs);// $house.' ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname;
			$areacode = $rs->areacode
				. (strlen($rs->areacode) == 6 && $addressOut['village'] != '' ? str_pad($addressOut['village'], 2, '0', STR_PAD_LEFT) : '');
			$result[] = [
				'value' => $areacode,
				'label' => htmlspecialchars($label),
				'address' => htmlspecialchars($address),
				'changwatId' => $rs->changwatId,
				'ampurId' => $rs->ampurId,
				'tambonId' => $rs->tambonId,
				'changwatName' => htmlspecialchars($rs->provname),
				'ampurName' => htmlspecialchars($rs->distname),
				'tambonName' => htmlspecialchars($rs->subdistname),
				'zip' => $addressOut['zip'],
			];
		}
		//print_o($result,'$result',1);
		if (debug('api')) {
			$result[] = ['value' => 'length','label' => 'Charactor length = '.strlen($searchText)];
			$result[] = ['value' => 'query','label' => $dbs->_query];
			$result[] = ['value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).'];
			$result[] = ['value' => 'tambon','label' => $searchText];
			foreach ($address as $k => $v) {
				$result[] = ['value' => 'out['.$k.']','label' => $k.'='.$v];
			}
		}
		return $result;
	}
}
?>