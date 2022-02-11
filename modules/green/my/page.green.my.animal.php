<?php
/**
* Green Smile : My Animal Farm
* Created 2020-09-10
* Modify  2020-12-04
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/my/animal[/{id}]
*/

$debug = true;

function green_my_animal($self, $landId = NULL) {
	$getLandId = SG\getFirst($landId, post('land'));

	$orgInfo = R::Model('green.shop.get', 'my', '{debug: false}');

	$orgSelectCard = R::View('green.my.select.org', '{"href": "'.url('green/organic/my/org/$id').'", "data-rel": "none", "data-done": "reload: '.url('green/my/animal').'", "title": "เลือกฟาร์ม", "btnText": "เลือกฟาร์ม"}');

	if (!($orgId = $orgInfo->shopId)) return '<header class="header"><h3>เลือกฟาร์มสำหรับจัดการข้อมูล?</h3></header>'.$orgSelectCard->build();

	$isAdmin = is_admin('green');
	$isOrgAdmin = $isAdmin || $orgInfo->RIGHT & _IS_EDITABLE;

	// Get land of group
	$landList = R::Model(
		'green.land.get',
		'{orgId: '.$orgId.', me: '.($isOrgAdmin ? 'false' : 'true').'}',
		'{debug: false, limit: "*"}'
	);

	// Create land option for new animal form
	$landOptions = array('' => '== เลือกคอก ==');
	foreach ($landList->items as $rs) {
		$landOptions[$rs->landid] = $rs->landname;
	}

	// Get animal in farm
	mydb::where('p.`orgid` = :orgId AND p.`tagname` = :tagname', ':orgId', $orgId, ':tagname', 'GREEN,ANIMAL');
	if ($getLandId) mydb::where('p.`landid` = :landid', ':landid', $getLandId);
	if (!$isOrgAdmin) mydb::where('p.`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT
		p.*, l.`landname` `landName`
		, u.`username`, u.`name` `ownerName`
		, (SELECT `file` FROM %topic_files% f WHERE f.`tagname` = "GREEN,ANIMAL" AND f.`refid` = m.`msgid` ORDER BY f.`cover` DESC, f.`fid` ASC LIMIT 1) `coverPhoto`
		FROM %ibuy_farmplant% p
			LEFT JOIN %msg% m ON m.`plantid` = p.`plantid`
			LEFT JOIN %ibuy_farmland% l ON l.`landid` = p.`landid`
			LEFT JOIN %users% u ON p.`uid` = u.`uid`
		%WHERE%
		ORDER BY p.`startdate` DESC, p.`plantid` DESC';

	$plantDbs = mydb::select($stmt);





	// Start View
	$toolbar = new Toolbar($self, 'ปศุสัตว์ @'.$orgInfo->name,'my.animal',$landInfo);

	$ui = new Ui(NULL, 'ui-nav -main');
	$dropUi = new Ui();

	$ui->add('<a class="sg-action" href="#green-org-select" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>ฟาร์ม</span></a>');
	$ui->add('<a class="sg-action" href="#green-land-select" data-rel="box" data-width="480"><i class="icon -material">nature_people</i><span>คอก</span></a>');
	$ui->add('<a class="-add" onClick="$(\'.green-plant-form-short\').toggle(); return true;"><i class="icon -material">add</i><span>เพิ่มโค</span></a>');
	$toolbar->addNav('main', $ui);

	$dropUi->add('<a class="sg-action" href="'.url('green/my/manage').'" data-webview="จัดการฟาร์ม"><i class="icon -material">settings</i><span>จัดการฟาร์ม</span></a>');

	if ($dropUi->count()) $toolbar->addNav('more', $dropUi);

	$ret .= '<section id="green-my-animal" data-url="'.url('green/my/animal').'">';

	$form = new Form(NULL, url('green/my/info/activity.save'), NULL, 'sg-form green-plant-form-short -animal -hidden');
	$form->addConfig('title', '<i class="icon -material">add</i>เพิ่มโค');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'moveto: 0,0 | load');

	$form->addField('orgid',array('type' => 'hidden', 'value' => $orgId));
	$form->addField('tagname',array('type' => 'hidden', 'value' => 'GREEN,ANIMAL'));
	$form->addField('message',array('type' => 'hidden', 'value' => 'เพิ่มโค'));
	//$form->addField('catid', array('type'=>'hidden','value'=>1));
	$form->addField('qty', array('type' => 'hidden', 'value' => 1));
	$form->addField('unit', array('type' => 'hidden', 'value' => 'ตัว'));
	$form->addField('startdate', array('type' => 'hidden', 'value' => date('Y-m-d')));
	$form->addField('cropdate', array('type' => 'hidden', 'value' => ''));

	$form->addField(
		'landid',
		array(
			'type' => 'select',
			'label' => 'คอก (หรือแปลงที่ดินสำหรับเลี้ยง):',
			'class' => '-fill',
			'require' => true,
			'options' => $landOptions,
			'value' => $data->landid,
			'container' => '{class: "-label-in"}',
		)
	);

	$form->addField(
		'productname',
		array(
			'type' => 'text',
			'label' => 'ชื่อพันธุ์โค (จำนวน 1 ตัว)',
			'class' => '-fill',
			'require' => true,
			'value' => '',
			'placeholder' => 'ระบุชื่อโคหรือชื่อพันธุ์',
			'container' => '{class: "-label-in"}',
		)
	);

	$form->addField(
		'startage',
		array(
			'label' => 'อายุ ณ วันที่เริ่มเลี้ยง (เดือน)',
			'type' => 'select',
			'class' => '-fill',
			'value' => $data->startage,
			'options' => '0..24',
			'container' => '{class: "-label-in"}',
		)
	);

	$form->addField(
		'productcode',
		array(
			'type' => 'text',
			'label' => 'หมายเลขโค',
			'class' => '-fill',
			'maxlength' => 20,
			'value' => $data->productcode,
			'placeholder' => 'ระบุหมายเลขโค',
			'posttext' => '<div class="append"><span><button class="btn -primary" type="submit"><i class="icon -material">done</i><span>{tr:SAVE}</button></div>',
			'container' => '{class: "-group -label-in"}',
		)
	);


	$ret .= $form->build();

	// Show Plant in Land

	$ret .= R::View('green.my.animal.list', $plantDbs->items)->build();

	//$ret .= print_o($orgInfo, '$orgInfo');

	$ret .= '<div class="-hidden">'
		. '<div id="green-org-select">'.$orgSelectCard->build().'</div>'
		. '<div id="green-land-select">'.R::View('green.land.select', $orgId, '{retUrl: "green/my/animal?land=$id", title: "เลือกคอก", btnText: "เลือกคอก"}')->build().'</div>'
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