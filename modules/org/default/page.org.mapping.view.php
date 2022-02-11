<?php
/**
* Organization Mapping
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function org_mapping_view($self, $orgId, $action = NULL, $mapId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self, 'Mapping');

	$isEditable = $orgInfo->RIGHT & _IS_EDITABLE;
	$isEditDetail = $isEditable && $action == 'edit';

	$ret = '';

	if (is_numeric($action)) {$mapId = $action; unset($action);}

	mydb::where('n.`mapid` = :mapid', ':mapid', $mapId);
	$stmt = 'SELECT n.*, s.`catparent` `sectorParent`
					FROM %map_networks% n
						LEFT JOIN %tag% s ON s.`taggroup` = "sector" AND `catid` = n.`sector`
					%WHERE%
					LIMIT 1
					';
	$mapInfo = mydb::select($stmt);

	if ($isEditable) $ret .= '<nav class="nav -box -sg-text-right"><a class="sg-action btn -link" href="'.url('org/'.$orgId.'/mapping.view/edit/'.$mapId).'" data-rel="box" data-width="600" data-esc-key="false"><i class="icon -edit"></i></a> <a class="sg-action btn -link" href="'.url('org/'.$orgId.'/mapping/delete/'.$mapId).'" data-rel="notify" data-done="close" data-title="ลบแผนที่" data-confirm="ต้องการลบแผนที่นี้ พร้อมรายการที่เกี่ยวข้องทั้งหมด กรุณายืนยัน?"><i class="icon -delete -gray"></i></a></nav>';

	//$ret .= '<a class="sg-action btn -link" href="'.url('org/'.$orgId.'/mapping.view/edit/'.$mapId).'" data-rel="box" data-width="600" data-confirm="ยืนยันว่าจะเปิดจริง?">BOX WITH CONFIRM</a>';

	$ret .= '<h3 class="title -box">'.$mapInfo->dowhat.'</h3>';


	$inlineAttr['class'] = 'org-mapping-view';

	if ($isEditable && $action == 'edit') {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('org/edit/info/'.$doid);
		if (debug('inline')) $inlineAttr['data-debug']='inline';
	}
	$ret.='<div id="org-mapping-view" '.sg_implode_attr($inlineAttr).'>';

	$sectorList = R::Model('category.get','sector','catid','{result: "tree"}');

	//$ret .= print_o($sectorList,'$sectorList');
	/*
	$form=new Form(NULL,'a');
	$form->addField('a',array('type'=>'select','options'=>array(''=>'=== เลือก ===')+$sectorList,'value'=>$mapInfo->sector));
	$selectSector = $form->get();
	$ret .= 'selectSector = '.print_o($selectSector,'$selectSector');
	*/

	//$sectorList = array('i1'=>'Item 1','i101'=>'--Item 101','i2'=>'Item 2','i3'=>'Item 3');
	//$ret .= sg_json_encode($sectorList);

	//$ret .= print_o($mapInfo,'$mapInfo');

	$tables = new Table();
	$tables->addClass('-info');
	$tables->rows[] = array(
										'โครงการ',
										view::inlineedit(array('group'=>'map','fld'=>'dowhat','tr'=>$mapInfo->mapid, 'options'=>'{class:"-fill"}'),$mapInfo->dowhat,$isEditDetail)
									);
	/*
	$tables->rows[] = array(
										'องค์กรรับผิดชอบ',
										view::inlineedit(array('group'=>'map','fld'=>'who','tr'=>$mapInfo->mapid, 'options'=>'{class:"-fill"}'),$mapInfo->who,$isEditDetail)
									);
	*/
	/*
	$tables->rows[] = array(
										'ชื่อองค์กร/หน่วยงาน',
										view::inlineedit(
											array(
												'group'=>'map',
												'fld'=>'orgname',
												'tr'=>$mapInfo->mapid,
												'value'=>'',
												'reforg'=>'',
												'options'=>'{
													class:"-fill",
													autocomplete: {
														minLength: 2,
														target: "areacode",
														query: "'.url('api/org').'",
														target: {"reforg": "orgid"}
													},
													placeholder: "ระบุชื่อองค์กร แล้วเลือกจากรายการแสดง หรือคลิกปุ่มเพิ่ม"
												}'
											),
											$mapInfo->orgname,
											$isEditDetail,
											'autocomplete'
										)
										.$mapInfo->who
										.'<a ><i class="icon -add"></i></a>'
									);
	*/
	$stmt = 'SELECT b.*, o.`orgid`, o.`name` `title`
					FROM %bigdata% b
						LEFT JOIN %db_org% o ON o.`orgid` = b.`fldref`
					WHERE b.`keyname` = "map" AND `keyid` = :mapId AND `fldname`  = "orgid"
					ORDER BY `bigid`';
	$dbs = mydb::select($stmt, ':mapId', $mapId);


	$orgTables = new Table();
	$orgTables->addId('org-mapping-view-orgname');
	//$orgTables->thead = array('no'=>'','title -hover-parent'=>'โครงการ');
	$orgTables->colgroup = array('title -hover-parent'=>array('class'=>'title -hover-parent'));
	foreach ($dbs->items as $rs) {
		$menu = '';
		$orgUi = new Ui();
		$orgUi->add('<a class="" href="'.url('org/'.$rs->orgid).'" target="_blank"><i class="icon -view"></i></a>');
		if ($isEditDetail) $orgUi->add('<a class="sg-action" href="'.url('org/'.$orgId.'/mapping/org.delete/'.$rs->bigid).'" data-rel="none" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel -gray"></i></a>');
		$menu = '<nav class="nav -icons -hover">'.$orgUi->build().'</nav>';
		$orgTables->rows[] = array(
												$rs->title
												//view::inlineedit(array('group'=>'bigdata:map','fld'=>'flddata','tr'=>$rs->bigid, 'keyid'=>$mapId, 'ret'=>'html','options'=>'{class:"-fill"}'),$rs->title,$isEditDetail,'textarea')
												.$menu,
										);
	}

	if ($isEditDetail) $orgForm .= '<form class="sg-form" action="'.url('org/'.$orgId.'/mapping/org.add/'.$mapId).'" style="margin: 0; position: relative;" data-rel="none" data-checkvalid="1" data-data-type="json" data-callback="orgMapViewOnAddOrg"><input id="reforg" type="hidden" name="reforg" value="" /><input class="sg-autocomplete form-text -fill -require" type="text" name="orgname" placeholder="ระบุชื่อองค์กร/หน่วยงาน" data-query="'.url('api/org').'" data-altfld="reforg" data-xselect=\'{"reforg":"orgid"}\' data-callback="submit" /><button class="btn" style="position: absolute; right: 0px;"><i class="icon -add"></i></button></form>';

	$tables->rows[] = array(
											'ชื่อองค์กร/หน่วยงาน',
											$orgTables->build()
											.$orgForm
											.($isEditable ? '<em style="color: gray;">('.$mapInfo->who.')</em>' : '')
										);
	//ย้ายไปไว้ในองค์กร
	/*
	$tables->rows[] = array(
										'ภาคส่วน',
										view::inlineedit(array('group'=>'map', 'fld'=>'sector', 'tr'=>$mapInfo->mapid, 'value'=>$mapInfo->sector.':'.$mapInfo->sectorParent, 'options'=>'{class:"-fill"}'),$sectorList[$mapInfo->sector.':'.$mapInfo->sectorParent],$isEditDetail,'select', $sectorList)
									);
									*/
	$tables->rows[] = array('กลไก',
										R::Page('org.mapping.mechanism',NULL, $orgInfo, $mapId, $action)
									);
	$tables->rows[] = array(
										'ประเด็นการทำงาน',
										R::Page('org.mapping.subject',NULL, $orgInfo, $mapId, $action)
									);
	$tables->rows[] = array('ปี ',
										view::inlineedit(array('group'=>'map','fld'=>'yearstart','tr'=>$mapInfo->mapid, 'options'=>'{class: "-inline -sg-text-center", maxlength: 4, placeholder: "ปี ค.ศ."}'),$mapInfo->yearstart,$isEditDetail,'number')
										.' - '
										.view::inlineedit(array('group'=>'map','fld'=>'yearend','tr'=>$mapInfo->mapid, 'options'=>'{class: "-inline -sg-text-center", maxlength: 4, placeholder: "ปี ค.ศ."}'),$mapInfo->yearend,$isEditDetail,'number')
									);
	




	// Show area
	$stmt = 'SELECT b.`bigid`, b.`flddata` `house`, CAST(SUBSTR(b.`fldref`,7,2) AS UNSIGNED) `village`, cos.`subdistname`, cod.`distname` `ampurName`, cop.`provname` `changwatName`
					FROM %bigdata% b
						LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(b.`fldref`,2)
						LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(b.`fldref`,4)
						LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(b.`fldref`,6)
					WHERE b.`keyname` = "map" AND `keyid` = :mapId AND `fldname`  = "areacode"
					ORDER BY `bigid`';
	$dbs = mydb::select($stmt, ':mapId', $mapId);

	$areaTables = new Table();
	$areaTables->addId('org-mapping-view-area');
	//$orgTables->thead = array('no'=>'','title -hover-parent'=>'โครงการ');
	$areaTables->colgroup = array('title -hover-parent'=>array('class'=>'title -hover-parent'));
	foreach ($dbs->items as $rs) {
		$menu = '';
		$orgUi = new Ui();
		if ($isEditDetail) $orgUi->add('<a class="sg-action" href="'.url('org/'.$orgId.'/mapping/area.delete/'.$rs->bigid).'" data-rel="none" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel -gray"></i></a>');
		$menu = '<nav class="nav -icons -hover">'.$orgUi->build().'</nav>';
		$areaTables->rows[] = array(
												SG\implode_address($rs)
												//view::inlineedit(array('group'=>'bigdata:map','fld'=>'flddata','tr'=>$rs->bigid, 'keyid'=>$mapId, 'ret'=>'html','options'=>'{class:"-fill"}'),$rs->title,$isEditDetail,'textarea')
												.$menu,
										);
	}

	if ($isEditDetail) $areaForm .= '<form class="sg-form" action="'.url('org/'.$orgId.'/mapping/area.add/'.$mapId).'" style="margin: 0; position: relative;" data-rel="none" data-checkvalid="1" data-data-type="json" data-callback="orgMapViewOnAddArea"><input id="areacode" type="hidden" name="areacode" value="" /><input class="sg-autocomplete form-text -fill -require" type="text" name="address" placeholder="0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง" data-query="'.url('api/address').'" data-altfld="areacode" data-xselect=\'{"reforg":"orgid"}\' data-callback="submit" /></form>';

	$tables->rows[] = array(
											'พื้นที่ปฎิบัติงาน',
											$areaTables->build()
											.$areaForm
											.('<em style="color:gray">'.$mapInfo->address.'</em>')
										);
	/*
	$tables->rows[] = array(
			'พื้นที่ปฎิบัติงาน',
			view::inlineedit(
				array(
					'group' => 'map',
					'fld' => 'address,areacode',
					'tr' => $mapInfo->mapid,
					'areacode' => $mapInfo->areacode,
					'ret' => 'address',
					'options' => '{
						class: "-fill'.(empty($mapInfo->areacode) ? ' -incomplete' : '').'",
							onblur: "none",
							autocomplete: {
								minLength: 5,
								target: "areacode",
								query: "'.url('api/address').'"
							},
							placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
						}',
				),
				$mapInfo->address,
				$isEditDetail,
				'autocomplete'
			)
		);
	*/

	$tables->rows[] = array(
										'หัวหน้าโครงการ',
										view::inlineedit(array('group'=>'map','fld'=>'contactname','tr'=>$mapInfo->mapid,'options'=>'{class:"-fill"}'),$mapInfo->contactname,$isEditDetail)
									);
	$tables->rows[] = array(
										'เบอร์โทรโครงการ',
										view::inlineedit(array('group'=>'map','fld'=>'contactphone','tr'=>$mapInfo->mapid,'options'=>'{class:"-fill"}'),$mapInfo->contactphone,$isEditDetail)
									);
	$tables->rows[] = array(
										'อีเมล์โครงการ',
										view::inlineedit(array('group'=>'map','fld'=>'contactemail','tr'=>$mapInfo->mapid,'options'=>'{class:"-fill"}'),$mapInfo->contactemail,$isEditDetail)
									);
	$tables->rows[] = array(
										'ข้อมูลอื่น ๆ',
										view::inlineedit(array('group'=>'map','fld'=>'detail','tr'=>$mapInfo->mapid, 'ret'=>'html','options'=>'{class:"-fill"}'),$mapInfo->detail,$isEditDetail,'textarea')
									);






	// Show person of mapping
	$stmt = 'SELECT b.*, p.`psnid`, CONCAT(p.`prename`," ",p.`name`," ",p.`lname`) `fullname`
					FROM %bigdata% b
						LEFT JOIN %db_person% p ON p.`psnid` = b.`fldref`
					WHERE b.`keyname` = "map" AND `keyid` = :mapId AND `fldname`  = "psnid"
					ORDER BY `bigid`';
	$dbs = mydb::select($stmt, ':mapId', $mapId);

	if ($dbs->count() || $isEditDetail) {
		$peopleTables = new Table();
		$peopleTables->addId('org-mapping-view-person');
		$peopleTables->thead = array('title -hover-parent'=>'ชื่อ นามสกุล','โทร','email -hover-parent'=>'อีเมล์');
		//$peopleTables->colgroup = array('title -hover-parent'=>array('class'=>'title -hover-parent'));
		foreach ($dbs->items as $rs) {
			$menu = '';
			$orgUi = new Ui();
			$orgUi->add('<a class="" href="'.url('org/member/'.$rs->psnid).'" target="_blank"><i class="icon -view"></i></a>');
			if ($isEditDetail) $orgUi->add('<a class="sg-action" href="'.url('org/'.$orgId.'/mapping/person.delete/'.$rs->bigid).'" data-rel="none" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel -gray"></i></a>');
			$menu = '<nav class="nav -icons -hover">'.$orgUi->build().'</nav>';
			$peopleTables->rows[] = array(
													$rs->fullname,
													//view::inlineedit(array('group'=>'bigdata:map','fld'=>'flddata','tr'=>$rs->bigid, 'keyid'=>$mapId, 'ret'=>'html','options'=>'{class:"-fill"}'),$rs->title,$isEditDetail,'textarea')
													'',
													''
													.$menu,
											);
		}

		if ($isEditDetail) $peopleForm .= '<form class="sg-form" action="'.url('org/'.$orgId.'/mapping/person.add/'.$mapId).'" style="margin: 0; position: relative;" data-rel="none" data-checkvalid="1" data-data-type="json" data-callback="orgMapViewOnAddPerson"><input id="psnid" type="hidden" name="psnid" value="" /><input class="sg-autocomplete form-text -fill -require" type="text" name="fullname" placeholder="ระบุ ชื่อ นามสกุล (ไม่ต้องใส่คำนำหน้านาม) แล้วเลือกจากรายการแสดง หรือคลิกปุ่มเพิ่มในกรณีที่เป็นชื่อใหม่" data-query="'.url('org/api/person').'" data-altfld="person" data-xselect=\'{"reforg":"orgid"}\' data-callback="submit" /><button class="btn" style="position: absolute; right: 0px;"><i class="icon -add"></i></button></form>';

		$tables->rows[] = array(
												'ชื่อผู้รับผิดชอบ',
												$peopleTables->build()
												.$peopleForm
											);
	}

	$ret .= $tables->build();




	/*
	$stmt = 'SELECT b.*, b.`flddata` `title`
					FROM %bigdata% b
					WHERE b.`keyname` = "map" AND `keyid` = :mapId AND `fldname`  = "project"
					ORDER BY `bigid`';
	$dbs = mydb::select($stmt, ':mapId', $mapId);
	//$ret .= mydb()->_query;

	$dbs = mydb::select($stmt,':mapId',$mapId);

	$ret .= '<h3>โครงการ</h3>';
	$tables = new Table();
	$tables->addId('org-mapping-view-project');
	$tables->thead = array('no'=>'','title -hover-parent'=>'โครงการ');
	foreach ($dbs->items as $rs) {
		$menu = '';
		if ($isEditDetail) $menu = '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('org/'.$orgId.'/mapping/project.delete/'.$rs->bigid).'" data-rel="none" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-removeparent="tr"><i class="icon -cancel -gray"></i></a></nav>';
		$tables->rows[] = array(
												++$no,
												view::inlineedit(array('group'=>'bigdata:map','fld'=>'flddata','tr'=>$rs->bigid, 'keyid'=>$mapId, 'ret'=>'html','options'=>'{class:"-fill"}'),$rs->title,$isEditDetail,'textarea')
												.$menu,
										);
	}
	$ret .= $tables->build();

	if ($isEditDetail) $ret .= '<form class="sg-form" action="'.url('org/'.$orgId.'/mapping/project.add/'.$mapId).'" style="margin: 0 0 32px 0; position: relative;" data-rel="none" data-checkvalid="1" data-data-type="json" data-callback="orgMapViewOnAddProject"><input class="form-text -fill -require" type="text" name="projectname" placeholder="ระบุชื่อโครงการ" /><button class="btn" style="position: absolute; right: 0px;"><i class="icon -add"></i></button></form>';
	*/
	$ret.='</div><!-- org-mapping-view -->';

	if ($isEditable && $action == 'edit') $ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('org/'.$orgId.'/mapping.view/'.$mapId).'" data-rel="box" data-width="600"><i class="icon -save -white"></i><span>DONE</span></a></nav>';
	//$ret .= print_o($rs,'$rs');
	$ret .= '<style type="text/css">
	.org-mapping-view td {padding: 2px 2px 4px 2px;}
	.org-mapping-view .item.-info>tbody>tr>td:first-child {padding-right: 16px; white-space: nowrap; vertical-align: middle; font-weight: bold;}
	.org-mapping-view .item.-info>tbody>tr>td:nth-child(2) {width: 100%;}
	.org-mapping-view .item tbody tr:hover {background: #fbfbfb;}
	.org-mapping-view .item .item {margin:0 0 2px 0;}

	/* .inline-edit-field.-textarea {min-height: 24px;} */
	.org-mapping-view h3 {background-color: #eee; padding: 4px 8px; font-size: 1.4em; margin: 8px 0;}
	.form-item.-edit-mechanism,.form-item.-edit-subject {width: 120px;}
	.sg-form.-inlineitem .form-select {height: 28px; background: #ffefde;}
	</style>

	<script type="text/javascript">
	function orgMapViewOnAddOrg($this,data) {
		var $target = $("#org-mapping-view-orgname tbody")
		var rowCount = $("#org-mapping-view-orgname tbody tr").length+1;
		var $srcElement = srcData = $this.find(".form-text")
		var navText = "<nav class=\"nav -icons -hover\"><a class=\"sg-action\" href=\"'.url('org/'.$orgId.'/mapping/org.delete').'/" + data.bigid + "\" data-rel=\"none\" data-confirm=\"ต้องการลบรายการนี้ กรุณายืนยัน?\" data-removeparent=\"tr\"><i class=\"icon -cancel -gray\"></i></a></nav>"
		var newRowContent = "<tr><td class=\"-hover-parent\">"+data.orgname+navText+"</td></tr>"
		$target.append(newRowContent);
		$srcElement.val("")
		//console.log(data.update)
	}

	function orgMapViewOnAddArea($this,data) {
		var $target = $("#org-mapping-view-area tbody")
		var rowCount = $("#org-mapping-view-area tbody tr").length+1;
		var $srcElement = srcData = $this.find(".form-text")
		var navText = "<nav class=\"nav -icons -hover\"><a class=\"sg-action\" href=\"'.url('org/'.$orgId.'/mapping/area.delete').'/" + data.bigid + "\" data-rel=\"none\" data-confirm=\"ต้องการลบรายการนี้ กรุณายืนยัน?\" data-removeparent=\"tr\"><i class=\"icon -cancel -gray\"></i></a></nav>"
		var newRowContent = "<tr><td class=\"-hover-parent\">"+data.address+navText+"</td></tr>"
		$target.append(newRowContent);
		$srcElement.val("")
	}

	function orgMapViewOnAddPerson($this,data) {
		var $target = $("#org-mapping-view-person tbody")
		var rowCount = $("#org-mapping-view-person tbody tr").length+1;
		var $srcElement = srcData = $this.find(".form-text")
		var navText = "<nav class=\"nav -icons -hover\"><a href=\"'.url('org/member').'/" + data.psnid + "\" target=\"_blank\"><i class=\"icon -view -gray\"></i></a><a class=\"sg-action\" href=\"'.url('org/'.$orgId.'/mapping/person.delete').'/" + data.bigid + "\" data-rel=\"none\" data-confirm=\"ต้องการลบรายการนี้ กรุณายืนยัน?\" data-removeparent=\"tr\"><i class=\"icon -cancel -gray\"></i></a></nav>"
		var newRowContent = "<tr><td>"+data.fullname+"</td><td></td><td class=\"-hover-parent\">"+navText+"</td></tr>"
		$target.append(newRowContent);
		$srcElement.val("")
	}

	function orgMapViewOnAddProject($this,data) {
		var $target = $("#org-mapping-view-project tbody")
		var rowCount = $("#org-mapping-view-project tbody tr").length+1;
		var $srcElement = srcData = $this.find(".form-text")
		var srcData = data.editText + "<nav class=\"nav -icons -hover\"><a class=\"sg-action\" href=\"'.url('org/'.$orgId.'/mapping/project.delete').'/" + data.bigid + "\" data-rel=\"none\" data-confirm=\"ต้องการลบรายการนี้ กรุณายืนยัน?\" data-removeparent=\"tr\"><i class=\"icon -cancel -gray\"></i></a></nav>"
		var newRowContent = "<tr><td class=\"col -no\">"+rowCount+"</td><td class=\"-hover-parent\">"+srcData+"</td></tr>"
		$target.append(newRowContent);
		$srcElement.val("")
	}

	</script>';
	return $ret;
}
?>