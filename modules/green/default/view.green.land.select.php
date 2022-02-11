<?php
/**
* Green :: Select Land
* Created 2020-09-04
* Modify  2020-12-03
*
* @param Object $self
* @return Object Ui
*/

$debug = true;

function view_green_land_select($orgId, $options = '{}') {
	$defaults = '{debug: false, retUrl: null, title: "เลือกแปลงที่ดิน", btnText: "เลือกแปลง"}';
	$options = SG\json_decode($options, $defaults);

	$retUrl = $options->retUrl;

	$cardUi = new Ui('div', 'ui-card green-select -land');
	$cardUi->header('<h3>'.$options->title.'</h3>', NULL, array('preText' => _HEADER_BACK));

	// If not parameter $orgId
	if (!$orgId) {
		$cardUi->add('ไม่มีข้อกลุ่มที่ระบุ');
		return $cardUi;
	}

	$orgId = ($orgInfo = R::Model('green.shop.get', $orgId)) ? $orgInfo->orgId : NULL;

	// If Org not exists
	if (!$orgId) {
		$cardUi->add('ไม่มีข้อกลุ่มที่ระบุ');
		return $cardUi;
	}

	$isAdmin = is_admin('green');
	$isEdit = $isAdmin || $orgInfo->RIGHT & _IS_EDITABLE;

	// Get Tree in my Land
	mydb::where('l.`orgid` = :orgid', ':orgid', $orgId);
	if (!$isEdit) mydb::where('l.`uid` = :uid', ':uid', i()->uid);
	$stmt = 'SELECT
		l.`landid`, l.`landname` `landName`
		, l.`arearai`, l.`areahan`, l.`areawa`
		, l.`standard` `landStandard`
		, l.`approved` `landApproved`
		, l.`detail` `landDetail`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `landLocation`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u ON u.`uid` = l.`uid`
		%WHERE%
		';

	$dbs = mydb::select($stmt);


	foreach ($dbs->items as $rs) {
		$url = url(str_replace('$id', $rs->landid, $retUrl));
		$cardStr = '<div class="header"><h3><i class="icon -material">nature_people</i>'.$rs->landName.'<span>@'.$rs->ownerName.'</span></h3></div>'
			. '<nav class="nav -card -sg-text-center""><a class="btn -primary -fill" href="'.$url.'"><i class="icon -material">done</i><span>'.$options->btnText.'</span></a></nav>'
			;

		$cardUi->add(
			$cardStr,
			array(
				'class' => 'sg-action',
				'href' => $url,
			)
		);
	
	}

	//debugMsg($dbs,'$dbs');
	//debugMsg($orgInfo, '$orgInfo');

	return $cardUi;
}
?>