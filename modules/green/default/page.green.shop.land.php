<?php
/**
* Green Smile Shop View
*
* @param Object $self
* @param Object $shopInfo
* @return String
*/

$debug = true;

function green_shop_land($self, $shopInfo) {
	$shopId = $shopInfo->shopId;

	if (!R()->appAgent) new Toolbar( $self, $shopInfo->name.' @Green Smile', NULL, $shopInfo);

	if ($shopInfo == '*') {

	} else if (!$shopId) return message('error', 'PROCESS ERROR');

	$ret = '';

	$ret .= '<section class="">';
	$ret .= '<header class="header"><h3>แปลงการผลิต</h3></header>';

	$isAdmin = user_access('administer ibuys');

	if ($shopInfo != '*') mydb::where('l.`orgid` = :orgid', ':orgid', $shopId);
	$stmt = 'SELECT
		l.*
		, u.`username`
		, u.`name` `posterName`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		ORDER BY FIELD(l.`approved`, "ApproveWithCondition", "Approve") DESC, l.`landid` DESC';

	$dbs = mydb::select($stmt, ':shopid', $shopId);


	$landCard = new Ui('div', 'ui-card');

	foreach ($dbs->items as $rs) {
		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');

		$headerNav->add('<a class="sg-action btn'.($rs->approved == 'Approve' ? ' -success' : '').'" href="'.url('green/land/'.$rs->landid).'" data-webview="'.htmlspecialchars($rs->landname).'">'
			. (in_array($rs->approved, array('Approve', 'ApproveWithCondition')) ? '<i class="icon -material">'.($rs->approved == 'Approve' ? 'done_all' : 'done').'</i>' : '')
			. '<span>'.SG\getFirst($rs->standard,'NONE').'</span></a>'
		);

		$headerNav->add('<a class="ag-action btn -link" href="'.url('green/land/'.$rs->landid).'" data-webview="'.htmlspecialchars($rs->landname).'"><i class="icon -material">search</i></a>');
		$headerNav->add('<a class="sg-action btn -link" href="'.url('green/land/'.$rs->landid.'/map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-options=\'{refresh: false}\'><i class="icon -material -land-map'.($rs->location ? ' -active' : '').'">where_to_vote</i><span class="-hidden">แผนที่</span></a>');

		$landCard->add(
			'<div class="header">'
			. '<span class="profile"><img class="poster-photo -sg-32" src="'.model::user_photo($rs->username).'" width="24" height="24" alt="" />'
			. '<h3>'.$rs->landname.'</h3>'
			. '<span class="poster-name">By '.$rs->username.'</span>'
			. '</span><!-- profile -->'
			. $headerNav->build()
			. '</div><!-- header -->'._NL
			. '<div class="detail">'
			. '<p>พื้นที่ '
			. ($rs->arearai > 0 ? $rs->arearai.' ไร่ ' : '')
			. ($rs->areahan > 0 ? $rs->areahan.' งาน ' : '')
			. ($rs->areawa > 0 ? $rs->areawa.' ตารางวา' : '')
			. ($rs->approved ? 'มาตรฐาน '.$rs->standard.' ( '.$rs->approved.' )<br />'._NL : '')
			. 'ประเภทผลผลิต '.$rs->producttype.'</p>'
			//. print_o($rs, '$rs')
			. '</div><!-- detail -->'._NL,
			'{class: "sg-action", href: "'.url('green/land/'.$rs->landid).'", "data-webview": "'.htmlspecialchars($rs->landname).'", onclick: ""}'
		);
	}

	$ret .= $landCard->build();


	$ret .= '</section>';

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>