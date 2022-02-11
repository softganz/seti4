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
*/

$debug = true;

function green_rubber_my_rubber($self, $plantId = NULL) {
	if ($plantId) return R::Page('green.rubber.my.rubber.view', $self, $plantId);

	$shopInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/rubber/my/rubber').'"}');

	if (!($orgId = $shopInfo->shopId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$getLandId = SG\getFirst($landId, post('land'));

	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $shopInfo->name;
	}

	$isAdmin = is_admin('green');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;



	// Start View

	$toolbar = new Toolbar($self, 'สวนยาง @'.$headerTitle, NULL, $landInfo);

	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/rubber').'"><i class="icon -material">nature</i><span>ต้นยาง</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/rubber/my/rubber/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกยาง</span></a>');
	$toolbar->addNav('main', $ui);

	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการกลุ่ม"><i class="icon -material">settings</i><span>จัดการกลุ่ม</span></a>');
	}
	if ($dropUi->count()) $toolbar->addNav('more', $dropUi);

	$ret = '';
	//$ret .= $toolbar->build();

	$ret .= '<section id="green-my-tree" data-url="'.url('green/rubber/my/tree/'.$landId).'">';

	// Show Plant in Land
	mydb::where('p.`orgid` = :orgId AND p.`tagname` = :tagname', ':orgId', $orgId, ':tagname', 'GREEN,RUBBER');
	if ($getLandId) mydb::where('p.`landid` = :landid', ':landid', $getLandId);
	if (!$isEdit) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = "GREEN,RUBBER" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret .= print_o($plantDbs);

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);
	$plantCardUi->header('<h3>ผลผลิต</h3>');

	$ret .= $plantCardUi->build();



	//$ret .= print_o($dbs,'$dbs');


	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '<div class="-hidden">'
		. '<div id="green-org-select"><header class="header">'._HEADER_BACK.'<h3>เลือกกลุ่ม</h3></header>'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/rubber/my/rubber?land=$id"}')->build().'</div>'
		. '</div>';

	$ret .= '</section>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshResume: true}
		return options
	}
	</script>');

	return $ret;
}
?>