<?php
/**
* Green Smile : My Tree Bank
* Created 2020-09-04
* Modify  2020-09-09
*
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/tree/{id}
*/

$debug = true;

function green_tree($self, $plantId = NULL) {
	if ($plantId) return R::Page('green.tree.view', $self, $plantId);

	$ret = '';



	// Start View

	$toolbar = new Toolbar($self, 'ธนาคารต้นไม้');

	$ret .= '<section id="green-tree" data-url="'.url('green/tree').'">';

	// Show Plant in Land
	mydb::where('p.`tagname` = :tagname', ':tagname', 'GREEN,TREE');
	if ($getOrgId) mydb::where('p.`orgid` = :orgId', ':orgId', $orgId);
	if ($getLandId) mydb::where('p.`landid` = :landid', ':landid', $getLandId);

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = p.`tagname` AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %ibuy_farmland% l USING(`landid`)
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);

	$ret .= $plantCardUi->build();

	//$ret .= print_o($dbs,'$dbs');

	/*
	$ret .= '<div class="-hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/rubber/my/tree?land=$id"}')->build().'</div>'
		. '</div>';
	*/
	$ret .= '</section>';

	return $ret;
}
?>