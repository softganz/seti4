<?php
/**
* Org :: Information
* Created 2021-10-13
* Modify  2021-12-04
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.view
*/

import('widget:org.nav.php');
import('widget:org.pa.map.nav.php');
class OrgPaMapView extends Page {
	var $orgId;
	var $isEditMode;
	var $orgInfo;
	var $right;

	function __construct($orgInfo = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->action = post('action');
		$this->right = (Object) [
			'edit' => $orgInfo->RIGHT & _IS_EDITABLE,
			'admin' => $orgInfo->is->orgadmin,
		];
		$this->isEditMode = $this->right->edit && $this->action === 'edit';
	}

	function build() {
		if (!$this->orgId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลตามที่ระบุ']);

		$orgInfo = $this->orgInfo;

		$isViewOnly = $this->action === 'view';
		$isEditMode = $this->right->edit && $this->action === 'edit';

		$orgConfig = cfg('org');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->orgInfo->name,
				// 'leading' => _HEADER_BACK,
				//'navigator' => new OrgNavWidget($this->orgInfo),
				'navigator' => new OrgPaMapNavWidget(),
				// 'boxHeader' => true,
			]),
			'body' => new Container([
				'class' => 'org-info'.($isEditMode ? ' sg-inline-edit' : ''),
				'id' => 'org-info',
				'attribute' => $isEditMode ? [
					'data-tpid' => $this->orgId,
					'data-update-url' => url('org/edit/info'),
					'data-url' => url('org/'.$this->orgId.'/info.view', ['action' => 'edit']),
					'data-debug' => debug('inline') ? 'inline' : NULL,
					] : [
						'data-url' => url('org/'.$this->orgId.'/info.view'),
					],
				'children' => [
					new Table([
						'class' => '-org-info',
						'children' => [
							[
								'ชื่อองค์กร',
								view::inlineedit(array('group'=>'org', 'fld'=>'name', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->name,$isEditMode),
							],
							$orgConfig->editShortName ? [
								'ชื่อย่อ (ไทย)',
								view::inlineedit(
									['group'=>'org', 'fld'=>'shortname', 'tr'=>$orgInfo->orgid],
									$orgInfo->info->shortname,
									$isEditMode
								),
								'config' => ['class' => '-th-short-name'],
							] : NULL,
							$orgConfig->editShortName ? [
								'ชื่อย่อ (English)',
								view::inlineedit(
									['group'=>'org', 'fld'=>'enshortname', 'tr'=>$orgInfo->orgid],
									$orgInfo->info->enshortname,
									$isEditMode
								),
								'config' => ['class' => '-en-short-name'],
							] : NULL,

							// $sectorList = R::Model('org.sector.list');
							// $tables->rows[] = [
							[	'กลุ่มเครื่อข่าย',
								view::inlineedit(
									['group'=>'org', 'fld'=>'sector', 'tr'=>$orgInfo->orgid, 'value' => 'เดินวิ่ง'], // key of value
									'เดินวิ่ง', // text of value
									$isEditMode,
									'checkbox',
									['เดินวิ่ง','ปั่นจักรยาน','สุขภาวะ','NCD','เปิดรับทั่วไป']
								),
								'config' => ['class' => '-sector'],
							],
							// ]; group=> table 
							

														// $sectorList = R::Model('org.sector.list');
							// $tables->rows[] = [
							// 	'ภาคส่วน',
							// 	view::inlineedit(
							// 		['group'=>'org', 'fld'=>'sector', 'tr'=>$orgInfo->orgid, 'value' => $orgInfo->info->sector],
							// 		$sectorList[$orgInfo->info->sector],
							// 		$isEditMode,
							// 		'select',
							// 		$sectorList
							// 	),
							// 	'config' => ['class' => '-sector'],
							// ];

							[
								'ที่อยู่',
								view::inlineedit(
									[
										'group' => 'org',
										'fld' => 'house,areacode',
										'tr' => $orgInfo->orgid,
										'x-callback' => 'updateMap',
										'class' => '-fill',
										'areacode' => $orgInfo->info->areacode,
										'ret' => 'address',
										'options' => '{
												onblur: "none",
												autocomplete: {
													minLength: 5,
													target: "areacode",
													query: "'.url('api/address').'"
												},
												placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
											}',
									],
									$orgInfo->info->address,
									$isEditMode,
									'autocomplete'
								)
							],
							[
								'รหัสไปรษณีย์',
								view::inlineedit(array('group'=>'org', 'fld'=>'zipcode', 'tr'=>$orgInfo->orgid,'options'=>'{maxlength:5}'),$orgInfo->info->zipcode,$isEditMode)
							],
							[
								'โทรศัพท์',
								view::inlineedit(array('group'=>'org', 'fld'=>'phone', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->phone,$isEditMode)
							],
							[
								'โทรสาร',
								view::inlineedit(array('group'=>'org', 'fld'=>'fax', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->fax,$isEditMode)
							],
							[
								'อีเมล์',
								view::inlineedit(array('group'=>'org', 'fld'=>'email', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->email,$isEditMode)
							],
							[
								'เว็บไซต์',
								view::inlineedit(array('group'=>'org', 'fld'=>'website', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->website,$isEditMode)
							],
							[
								'เฟซบุ๊ค',
								view::inlineedit(array('group'=>'org', 'fld'=>'facebook', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->facebook,$isEditMode)
							],
							[
								'หัวหน้าองค์กร',
								view::inlineedit(array('group'=>'org', 'fld'=>'managername', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->managername,$isEditMode)
							],
							[
								'ผู้ประสานงานขององค์กร',
								view::inlineedit(array('group'=>'org', 'fld'=>'contactname', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->contactname,$isEditMode)
							],
							// [
							// 	'ผู้ประสานงานข้อมูล',
							// 	view::inlineedit(array('group'=>'org', 'fld'=>'contactdbname', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->contactdbname,$isEditMode)
							// ],

							// $orgSubjectStr=R::Page('org.subject',NULL,$orgInfo);

							// [
							// 	'ประเด็นการทำงาน',
							// 	$orgSubjectStr
							// ],

							// [
							// 	'ภาระกิจ',
							// 	view::inlineedit(array('group'=>'org', 'fld'=>'mission', 'tr'=>$orgInfo->orgid, 'class'=>'-fill','ret'=>'html'),$orgInfo->info->mission,$isEditMode,'textarea')
							// ],
							[
								'พิกัด GIS',
								view::inlineedit(array('group'=>'org', 'fld'=>'location', 'tr'=>$orgInfo->orgid, 'class'=>''),$orgInfo->info->location,$isEditMode).' <a class="sg-action" href="'.url('org/'.$this->orgId.'/info.map').'" data-rel="box" data-width="600" data-class-name="-map"><i class="icon -material">room</i></a>'
							],
						], // children
					]), // Table
					new FloatingActionButton([
						'child' => (function() {
							if ($isViewOnly) {
								// Do nothing
							} else if ($this->isEditMode) {
									return '<a class="sg-action btn -primary -circle48" href="'.url('org/'.$this->orgId.'/info.view', ['debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -material">done_all</i></a>';
							} else if ($this->right->edit) {
								return '<a class="sg-action btn -floating -circle48" href="'.url('org/'.$this->orgId.'/info.view', ['action'=> 'edit', 'debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -material">edit</i></a>';
							}
						})(), // child
					]), // FloatingActionButton
				], // children
			]), // Container
		]);
	}
}
?>
<?php
/**
* Module Method
*
* @param Object $self
* @param Int $orgId
* @param String $action
* @param Int $actionId
* @return String
*/

import('model:org.php');

function org_info_view($self, $orgId = NULL, $action = NULL, $actionId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	R::View('org.toolbar',$self,'Info', NULL, $orgInfo);

	$isViewOnly = $action == 'view';
	$isEditable = $orgInfo->RIGHT & _IS_EDITABLE;
	$isEdit = $isEditable && $action == 'edit';
	$isAddOrg = user_access('create org content');
	$isAdmin = $orgInfo->is->orgadmin;

	$ret = '';

	// switch ($action) {
	// 	case 'officer.add':
	// 		if ($isEditable && post('uid')) {
	// 			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership) ON DUPLICATE KEY UPDATE `membership` = :membership';
	// 			mydb::query($stmt, ':orgid', $orgId, ':uid', post('uid'), ':membership', strtoupper(post('membership')));
	// 			//$ret .= mydb()->_query;
	// 		}
	// 		return $ret;
	// 		break;

	// 	case 'removeofficer':
	// 		if ($isEditable && $actionId && SG\confirm()) {
	// 			mydb::query('DELETE FROM %org_officer% WHERE `uid` = :uid AND `orgid` = :orgid LIMIT 1', ':uid', $actionId, ':orgid', $orgId);
	// 		}
	// 		return $ret;
	// 		break;

	// 	case 'deletemember':
	// 		if ($isEditable && $actionId && SG\confirm()) {
	// 			mydb::query('DELETE FROM %org_morg% WHERE `psnid`=:psnid AND `orgid`=:orgid LIMIT 1',':psnid',$actionId, ':orgid',$orgId);
	// 		}
	// 		return $ret;
	// 		break;

	// 	case 'deletejoinorg':
	// 		if ($isEditable && $actionId && SG\confirm()) {
	// 			mydb::query('DELETE FROM %org_ojoin% WHERE `jorgid`=:jorgid AND `orgid`=:orgid LIMIT 1',':jorgid',$orgId, ':orgid',$actionId);
	// 		}
	// 		return $ret;
	// 		break;

	// 	case 'deleteojoin':
	// 		if ($isEditable && $actionId && SG\confirm()) {
	// 			mydb::query('DELETE FROM %org_ojoin% WHERE `orgid`=:orgid AND `jorgid`=:jorgid LIMIT 1',':orgid',$orgId, ':jorgid',$actionId);
	// 		}
	// 		return $ret;
	// 		break;

	// 	default:
	// 		# code...
	// 		break;
	// }








	$inlineAttr['class'] = 'org-info';
	if ($isEditMode) {
		$inlineAttr['class'] .= ' sg-inline-edit ';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('org/edit/info');
		$inlineAttr['data-url'] = url('org/'.$orgId.'/info.view/edit');
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	} else {
		$inlineAttr['data-url'] = url('org/'.$orgId.'/info.view');
	}

	$ret.='<div id="org-info" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<header class="header -box"><nav class="nav -back -hidden"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>ข้อมูลองค์กร'.($isEditMode?' (แก้ไข)':'').'</h3></header>'._NL;

	$tables = new Table();
	$tables->addClass('-org-info');

	$tables->rows[]=array(
		'ชื่อองค์กร',
		'<span class="big">'.view::inlineedit(array('group'=>'org', 'fld'=>'name', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->name,$isEditMode).'</span>'
	);

	$tables->rows[] = [
		'ชื่อย่อ (ไทย)',
		view::inlineedit(
			['group'=>'org', 'fld'=>'shortname', 'tr'=>$orgInfo->orgid],
			$orgInfo->info->shortname,
			$isEditMode
		),
		'config' => ['class' => '-th-short-name'],
	];

	$tables->rows[] = [
		'ชื่อย่อ (English)',
		view::inlineedit(
			['group'=>'org', 'fld'=>'enshortname', 'tr'=>$orgInfo->orgid],
			$orgInfo->info->enshortname,
			$isEditMode
		),
		'config' => ['class' => '-en-short-name'],
	];

	$sectorList = R::Model('org.sector.list');
	$tables->rows[] = [
		'ภาคส่วน',
		view::inlineedit(
			['group'=>'org', 'fld'=>'sector', 'tr'=>$orgInfo->orgid, 'value' => $orgInfo->info->sector],
			$sectorList[$orgInfo->info->sector],
			$isEditMode,
			'select',
			$sectorList
		),
		'config' => ['class' => '-sector'],
	];

	$tables->rows[] = [
		'ที่อยู่',
		view::inlineedit(
			[
				'group' => 'org',
				'fld' => 'house,areacode',
				'tr' => $orgInfo->orgid,
				'x-callback' => 'updateMap',
				'class' => '-fill',
				'areacode' => $orgInfo->info->areacode,
				'ret' => 'address',
				'options' => '{
						onblur: "none",
						autocomplete: {
							minLength: 5,
							target: "areacode",
							query: "'.url('api/address').'"
						},
						placeholder: "0 ซอย ถนน ม.0 ต.ตัวอย่าง แล้วเลือกจากรายการแสดง"
					}',
			],
			$orgInfo->info->address,
			$isEditMode,
			'autocomplete'
		)
	];

	$tables->rows[]=array(
		'รหัสไปรษณีย์',
		view::inlineedit(array('group'=>'org', 'fld'=>'zipcode', 'tr'=>$orgInfo->orgid,'options'=>'{maxlength:5}'),$orgInfo->info->zipcode,$isEditMode)
	);

	$tables->rows[]=array(
		'โทรศัพท์',
		view::inlineedit(array('group'=>'org', 'fld'=>'phone', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->phone,$isEditMode)
	);

	$tables->rows[]=array(
		'โทรสาร',
		view::inlineedit(array('group'=>'org', 'fld'=>'fax', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->fax,$isEditMode)
	);

	$tables->rows[]=array(
		'อีเมล์',
		view::inlineedit(array('group'=>'org', 'fld'=>'email', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->email,$isEditMode)
	);

	$tables->rows[]=array(
		'เว็บไซต์',
		view::inlineedit(array('group'=>'org', 'fld'=>'website', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->website,$isEditMode)
	);

	$tables->rows[]=array(
		'เฟซบุ๊ค',
		view::inlineedit(array('group'=>'org', 'fld'=>'facebook', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->facebook,$isEditMode)
	);

	$tables->rows[]=array(
		'หัวหน้าองค์กร',
		view::inlineedit(array('group'=>'org', 'fld'=>'managername', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->managername,$isEditMode)
	);

	$tables->rows[]=array(
		'ผู้ประสานงานขององค์กร',
		view::inlineedit(array('group'=>'org', 'fld'=>'contactname', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->contactname,$isEditMode)
	);

	$tables->rows[]=array(
		'ผู้ประสานงานข้อมูล',
		view::inlineedit(array('group'=>'org', 'fld'=>'contactdbname', 'tr'=>$orgInfo->orgid, 'class'=>'-fill'),$orgInfo->info->contactdbname,$isEditMode)
	);

	$orgSubjectStr=R::Page('org.subject',NULL,$orgInfo);

	$tables->rows[]=array(
		'ประเด็นการทำงาน',
		$orgSubjectStr
	);

	$tables->rows[]=array(
		'ภาระกิจ',
		view::inlineedit(array('group'=>'org', 'fld'=>'mission', 'tr'=>$orgInfo->orgid, 'class'=>'-fill','ret'=>'html'),$orgInfo->info->mission,$isEditMode,'textarea')
	);

	$tables->rows[]=array(
		'พิกัด GIS',
		view::inlineedit(array('group'=>'org', 'fld'=>'location', 'tr'=>$orgInfo->orgid, 'class'=>''),$orgInfo->info->location,$isEditMode).' <a class="sg-action" href="'.url('org/'.$orgId.'/info.map').'" data-rel="box" data-width="600" data-class-name="-map"><i class="icon -material">room</i></a>'
	);

	$tables->rows[]=array('เริ่มความสัมพันธ์เมื่อ',sg_date($orgInfo->info->created,'ว ดดด ปปปป H:i:s'));



	$ret.=$tables->build();

	//$ret.=print_o($orgInfo,'$orgInfo');


	$ret .= '<div class="-info-other">'._NL;
	// Show Officer and all membership
	if ($isEditable) {
		$officers = OrgModel::officers($orgId);

		$ret .= '<h3>เจ้าหน้าที่องค์กร</h3>';

		$tables = new Table();
		$tables->thead = array('no' => '', 'ชื่อ-นามสกุล', 'center' => 'สมาชิก', 'date -hover-parent' => 'วันที่');
		if ($officers) {
			$no = 0;
			foreach ($officers->items as $rs) {
				$ui = new Ui();
				if ($isAdmin) {
					$ui->add('<a class="sg-action" href="'.url('org/info/api/'.$orgId.'/officer.remove/'.$rs->uid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบเจ้าหน้าที่ออกจากองค์กร?"><i class="icon -cancel"></i></a>');
				}

				$menu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';

				$tables->rows[] = array(
					++$no,
					$rs->name,
					$rs->membership,
					sg_date($rs->datein,'ว ดด ปปปป')
					.$menu
				);
			}
		}

		if ($isAdmin) {
			$ret .= '<form class="sg-form" action="'.url('org/info/api/'.$orgId.'/officer.add').'" method="post" data-rel="notify" data-done="load->replace:#org-info"><input id="officer-uid" type="hidden" name="uid" value="" />';
			$tables->rows[] = array(
				'<td></td>',
				'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('api/user').'" data-altfld="officer-uid" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
				'<select name="membership" class="form-select -fill"><option value="ADMIN">Admin</option><option value="OFFICER">Officer</option><option value="TRAINER">Trainer</option><option value="MEMBER" selected="selected">Regular Member</option></select>',
				'<button class="btn"><i class="icon -add"></i><span>เพิ่มเจ้าหน้าที่</span></button>'
			);
		}

	$ret .= $tables->build();

	if ($isAdmin) $ret.='</form>';





		$ret.='<h3>สมาชิกองค์กร</h3>';
		$members=R::Model('org.get.member',$orgId);
		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อ-นามสกุล','indat -date -hover-parent'=>'วันที่เป็นสมาชิก');
		if ($members) {
			$no=0;
			foreach ($members->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('org/member/'.$rs->psnid).'">'.$rs->personName.'</a>',
					$rs->joindate
					. ($isEditable?'<nav class="nav iconset -hover"><a class="sg-action" href="'.url('org/'.$orgId.'/member.delete/'.$rs->psnid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบสมาชิกออกจากองค์กร กรุณายืนยัน?"><i class="icon -cancel -hover"></i></a></nav>':'')
				);
			}
			$ret.=$tables->build();

		} else $ret.='<p>ยังไม่มีสมาชิกขององค์กร</p>';




		$ret.='<h3>กิจกรรมขององค์กร</h3>';
		$stmt='SELECT d.`doid`, d.`orgid`, d.`doings`,d.`atdate` FROM %org_doings% d WHERE d.`orgid`=:orgid ORDER BY `atdate` DESC LIMIT 100';
		$dbs=mydb::select($stmt,':orgid',$orgId);
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','กิจกรรม','date'=>'วันที่');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('org/'.$rs->orgid.'/meeting.info/'.$rs->doid).'">'.$rs->doings.'</a>',
					sg_date($rs->atdate,'d-m-ปปปป'),
				);
			}
			$ret.=$tables->build();
		} else $ret.='<p>ยังไม่มีกิจกรรมขององค์กร</p>';


		$ret.='<h3>ผู้เข้าร่วมกิจกรรมองค์กร</h3>';
		$joins=R::Model('org.get.joinpeople',$orgId);
		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อ-นามสกุล','date'=>'วันที่เริ่มเข้าร่วม');
		if ($joins) {
			$no=0;
			foreach ($joins->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('org/member/'.$rs->psnid).'">'.$rs->personName.'</a>',
					$rs->joindate
				);
			}
			$ret.=$tables->build();
			if ($joins->count!=$joins->allJoins) $ret.='<p>หมายเหตุ ​: แสดงรายชื่อผู้เข้าร่วมกิจกรรมองค์กร '.$joins->count.' คน จากทั้งหมด '.$joins->allJoins.' คน</p>';

		} else $ret.='<p>ยังไม่มีผู้เข้าร่วมกิจกรรมขององค์กร</p>';


		$ret.='<h3>องค์กรที่มาเข้าร่วมกิจกรรม</h3>';
		$stmt='SELECT oj.*, o.`name` FROM %org_ojoin% oj LEFT JOIN %db_org% o ON o.`orgid`=oj.`jorgid` WHERE oj.`orgid`=:orgid';
		$dbs=mydb::select($stmt,':orgid',$orgId);
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','องค์กร','indate -date -hover-parent'=>'วันที่เริ่มเข้าร่วม');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('org/'.$rs->jorgid).'">'.$rs->name.'</a>',
					sg_date($rs->joindate,'d-m-ปปปป')
					.($isEditable?'<nav class="nav iconset -hover"><a class="sg-action" href="'.url('org/info/api/'.$orgId.'/join.org.remove/'.$rs->jorgid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบองค์กรออกจากการเข้าร่วม กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>':'')
				);
			}
			$ret.=$tables->build();
		} else $ret.='<p>ยังไม่เคยเข้าร่วมกิจกรรมขององค์กรใด</p>';



		$ret.='<h3>เข้าร่วมกิจกรรมขององค์กร</h3>';
		$stmt='SELECT oj.*, o.`name` FROM %org_ojoin% oj LEFT JOIN %db_org% o USING(`orgid`) WHERE oj.`jorgid`=:orgid';
		$dbs=mydb::select($stmt,':orgid',$orgId);
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','องค์กร','indate -date -hover-parent'=>'วันที่เริ่มเข้าร่วม');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('org/'.$rs->orgid).'">'.$rs->name.'</a>',
					sg_date($rs->joindate,'d-m-ปปปป')
					.($isEditable?'<nav class="nav iconset -hover"><a class="sg-action" href="'.url('org/info/api/'.$orgId.'/org.join.remove/'.$rs->orgid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบออกจากการเข้าร่วมองค์กร กรุณายืนยัน?"><i class="icon -cancel"></i></a></nav>':'')
				);
			}
			$ret.=$tables->build();
		} else $ret.='<p>ยังไม่เคยเข้าร่วมกิจกรรมขององค์กรใด</p>';



		$ret.='<h3>เอกสารขององค์กร</h3>';
		$stmt='SELECT t.`tpid`,t.`title`,t.`created` FROM %topic% t WHERE t.`orgid` = :orgid AND `type` = "story"';
		$dbs=mydb::select($stmt,':orgid',$orgId);
		if ($dbs->_num_rows) {
			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','หัวข้อ','date'=>'วันที่');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(
					++$no,
					'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
					sg_date($rs->created,'d-m-ปปปป'),
				);
			}
			$ret.=$tables->build();
		} else $ret.='<p>ยังไม่มีเอกสารขององค์กร</p>';

	}

	$ret .= '</div><!-- -info-other -->'._NL;

	if ($isViewOnly) {
		// Do nothing
	} else if ($isEditMode) {
			$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('org/'.$orgId.'/info.view', ['debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('org/'.$orgId.'/info.view/edit', ['debug' => post('debug')]).'" data-rel="replace:#org-info"><i class="icon -edit -white"></i></a></div>';
	}
	$ret.='</div><!-- org-info -->';

	//$ret.=print_o($orgInfo,'$orgInfo');


	$ret.='<style type="text/css">
	.item.-org-info td:nth-child(1) {width:30%;}
	.item.-org-info td:nth-child(2) {width:70%;}
	#org-subject {display: inline;}
	.org-subject {margin:0;padding:0;list-style-type:none; display: inline-block;}
	.org-subject li {display: inline-block; line-height:24px;}
	.org-subject .ui-item {height:24px;margin:0 20px 10px 0;padding:0 16px;display:inline-block;border:1px #ccc solid; border-radius:12px;}
	.org-subject .ui-item .icon {margin:0;padding:0;float:none; background-color:#fff; border-radius:50%;}
	.org-subject .form-select {width: 100px; height: 26px; border-radius: 24px;}
	</style>';
	return $ret;
}
?>