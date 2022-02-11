<?php
/**
* Green :: Rubber My Land
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function green_my_land_all($self) {
	$isAdmin = is_admin('green');
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;
	$isAddLand = $isEdit || in_array($orgInfo->is->membership,array('NETWORK'));

	$toolbar = new Toolbar($self, '@แปลงที่ดิน');

	$stmt = 'SELECT
		l.*
		, o.`name` `orgName`
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `latlng`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_org% o USING(`orgid`)
		%WHERE%
		ORDER BY CONVERT(l.`landname` USING tis620) ASC
		';

	$landList = mydb::select($stmt);


	$ret .= '<section class="green-land-card">';

	$landUi = new Ui(NULL, 'ui-card -land');
	$landUi->addConfig('container', '{tag: "div", class: ""}');

	foreach ($landList->items as $rs) {
		$isItemEdit = $isEdit || $rs->uid == i()->uid;
		$linkUrl = url('green/rubber/my/land/'.$rs->landid,array('done' => 'close | load'));

		$cardStr = '<div class="header"><i class="icon -material">nature_people</i><h3>'.$rs->landname.'</h3><span style="display: block; flex: 0 0 100%;color:grey;font-weight:normal;font-size:0.8em;">@'.$rs->orgName.'</span></div>'
			. '<nav class="nav -more-detail"><a class="btn -link" href="'.$linkUrl.'">รายละเอียดแปลง</a></nav>';
		$landUi->add(
			$cardStr,
			array(
				'class' => 'sg-action',
				'href' => $linkUrl,
				'data-rel' => 'box',
				'data-width' => 640,
				'data-webview' => $rs->landname,
			)
		);
	}

	$ret .= $landUi->build();

	// Show Plant in Land
	$stmt = 'SELECT p.*, l.`landname` `landName`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
		ORDER BY p.`plantid` DESC';

	$plantDbs = mydb::select($stmt, ':orgId', $orgId);
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();

	return $ret;
}
?>
