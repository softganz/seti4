<?php
/**
* Green Smile Land Home Page
* Created 2019-12-09
* Modify  2019-12-09
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_green_land_home($self) {
	$ret = '<header class="header -hidden"><h3>แปลงผลิต</h3></header>'._NL;

	R::View('toolbar',$self,'แปลงผลิต @Green Smile','ibuy.green.shop');

	$isAdmin = user_access('administer ibuys');

	$stmt = 'SELECT
		l.*
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `location`
		, u.`username`
		, u.`name` `posterName`
		FROM %ibuy_farmland% l
			LEFT JOIN %users% u USING(`uid`)
		ORDER BY FIELD(l.`approved`, "ApproveWithCondition", "Approve") DESC, `landid` DESC';

	$dbs = mydb::select($stmt);

	$ret .= '<section>'._NL;

	$landCard = new Ui('div', 'ui-card');

	foreach ($dbs->items as $rs) {
		$headerNav = new Ui();
		$headerNav->addConfig('nav', '{class: "nav -header"}');

		$headerNav->add('<a class="sg-action btn'.($rs->approved == 'Approve' ? ' -success' : '').'" href="'.url('ibuy/green/land/'.$rs->landid).'" data-webview="'.htmlspecialchars($rs->landname).'">'
			. (in_array($rs->approved, array('Approve', 'ApproveWithCondition')) ? '<i class="icon -material">'.($rs->approved == 'Approve' ? 'done_all' : 'done').'</i>' : '')
			. '<span>'.SG\getFirst($rs->standard,'NONE').'</span></a>'
		);

		$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/green/land/'.$rs->landid.'/map', array('options:fullpage,notoolbar'=>'')).'" data-rel="box" data-width="640" data-class-name="-map" data-webview="แผนที่แปลงผลิต" data-refresh="no"><i class="icon -material -land-map'.($rs->location ? ' -active' : '').'">where_to_vote</i><span class="-hidden">แผนที่</span></a>');

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
			'{class: "sg-action", href: "'.url('ibuy/green/land/'.$rs->landid).'", "data-webview": "'.htmlspecialchars($rs->landname).'", onclick: ""}'
		);
	}

	$ret .= $landCard->build();


	$ret .= '</section>';
	return $ret;
}
?>