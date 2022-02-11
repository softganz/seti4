<?php
/**
* Export Join
* Created 2019-05-16
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_export($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$export = post('export');
	$showJoinGroup = post('group');
	$searchText = post('search');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isExport = $isAdmin || $projectInfo->info->membershipType == 'OWNER';

	if (!$isExport)
		return message('error', 'Access Denied');

	$joinGroup = object_merge((object) array(''=>'== แสดงรายชื่อทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));

	$ret .= '<nav class="nav -page -no-print">';
	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/export'), NULL, 'form form -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addData('rel', '#main');
	$form->addField('group',
		array(
			'type' => 'select',
			'options' => $joinGroup,
			'value' => $showJoinGroup,
			'attr' => array('onchange' => '$(this).parent(form).submit()'),
		)
	);
	$form->addField('view', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));
	$form->addField('export', array('type' => 'button', 'name' => 'export', 'value' => '<i class="icon -download -white"></i><span>EXPORT</span>'));
	$ret .= $form->build();
	$ret .= '</nav>';


	$getConditions->doid = $projectInfo->doingInfo->doid;
	if ($showJoinGroup) $getConditions->joingroup = $showJoinGroup;
	if ($searchText) $getConditions->search = $searchText;
	$doingInfo = R::Model('project.join.get', $getConditions, '{debug: false, limit: "*"}');


	$showFieldList = array(
		'prename'=>'คำนำหน้า',
		'firstname'=>'ชื่อ',
		'lastname'=>'นามสกุล'
		,'cid'=>'หมายเลขประจำตัวประชาชน',
		'phone'=>'โทรศัพท์',
		'joingroup'=>'เครือข่าย',
		'orgname'=>'องค์กร',
		'foodtype'=>'อาหาร',
		'rest'=>'พัก',
		'hotelname'=>'โรงแรม',
		'hotelmate'=>'คู่พัก',
		'hotelprice'=>'ราคาโรงแรม',
		'hotelnight'=>'จำนวนคืนพัก',
		'tripby'=>'เดินทาง',
		'tripotherby'=>'เดินทางอื่นๆ',
		'carregist'=>'ทะเบียนรถ',
		'carregprov'=>'จังหวัด',
		'carwithname'=>'ผู้เดินทางร่วม',
		'rentregist'=>'ทะเบียนรถเช่า',
		'rentpassenger'=>'ผู้โดยสารรถเช่า',
		'busprice'=>'ราคารถโดยสารประจำทาง',
		'airprice'=>'ราคาเครื่องบิน',
		'tripotherprice'=>'ราคาเดินทางอื่นๆ',
		'taxiprice'=>'ราคารถแท็กซี่',
		'trainprice'=>'ราคารถไฟ',
		'rentprice'=>'ราคารถเช่า',
		'distance'=>'ระยะทาง',
		'fixprice'=>'ราคาเหมาจ่าย',
		'address'=>'ที่อยู่',
		'tripTotalPrice'=>'TripTotalPrice',
	);

	$tables = new Table();
	$tables->thead = $showFieldList;
	foreach ($doingInfo as $key => $rs) {
		$rs->jointype = implode(',', $rs->jointype);
		$rs->tripByList = implode(',', $rs->tripByList);
		$row = array();
		foreach ($showFieldList as $fldKey=>$fld) {
			$row[] = is_numeric($rs->{$fldKey}) && intval($rs->{$fldKey}) == 0 ? '' : $rs->{$fldKey};
		}
		$tables->rows[] = (array) $row;
	}

	if ($export) {
		die(R::Model('excel.export',$tables,'project-join-'.$tpid.'-'.$calId.'-'.date('Y-m-d-H-i-s').'.xls','{debug:false}'));
		//return $ret;
	}

	$ret .= '<div style="width:100%; overflow: scroll;">';
	$ret .= $tables->build();
	$ret .= '</div>';
	//$ret .= print_o($doingInfo, '$doingInfo');

	return $ret;
}
?>