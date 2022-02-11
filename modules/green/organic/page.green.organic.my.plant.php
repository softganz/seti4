<?php
/**
* Green :: Plant On My Land
* Created 2020-09-10
* Modify  2020-09-10
*
* @param Object $self
* @param Int $landId
* @return String
*/

$debug = true;

function green_organic_my_plant($self, $landId = NULL) {
	$getLandId = SG\getFirst($landId, post('land'));

	$orgInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{debug: true,"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/organic/my/plant').'"}');

	$ret = '';

	if (!($orgId = $orgInfo->orgId)) return '<header class="header"><h3>เลือกกลุ่มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$isAdmin = is_admin('green');
	$isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

	//$isAddLand = $isEdit || in_array($orgInfo->is->membership,array('NETWORK'));



	if ($getLandId) {
		$landInfo = mydb::select('SELECT * FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $getLandId);
		$headerTitle = $landInfo->landname;
	} else {
		$headerTitle = $orgInfo->name;
	}

	$toolbar = new Toolbar($self, 'ผลผลิต @'.$headerTitle, NULL, $landInfo);

	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	//$ui->add('<a class="sg-action" href="'.url('green/organic/my/plant').'"><i class="icon -material">nature</i><span>ผัก</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/my/plant/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกผัก</span></a>');

	$toolbar->addNav('main', $ui);

	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการกลุ่ม"><i class="icon -material">settings</i><span>จัดการกลุ่ม</span></a>');
	}
	if ($dropUi->count()) $toolbar->addNav('more', $dropUi);


	$ret .= '<section id="green-my-plant" class="green-plant" data-url="'.url('green/my/tree/'.$landId).'">';

	// Get Tree in my Land
	mydb::where('p.`tagname` = "GREEN,PLANT" AND l.`orgid` = :orgid', ':orgid', $orgId);
	if ($getLandId) mydb::where('l.`landid` = :landid', ':landid', $getLandId);
	if (!$isEdit) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		p.*
		, u.`username`, u.`name` `ownerName`
		, CONCAT(X(l.`location`),",",Y(l.`location`)) `landLocation`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = "GREEN,PLANT" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON u.`uid` = p.`uid`
		%WHERE%
		ORDER BY `plantid` DESC
		';

	$plantDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;

	$plantCardUi = R::View('green.my.plant.list', $plantDbs->items);

	$ret .= $plantCardUi->build();

	$ret .= '<div class="template -hidden">'
		. '<div id="green-org-select">'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/organic/my/plant/$id"}')->build().'</div>'
		. '</div>';

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