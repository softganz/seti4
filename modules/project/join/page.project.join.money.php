<?php
/**
* Project Action Join Money
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_money($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;


	$getStatus = post('status');
	$getJoinGroup = SG\getFirst(post('group'));
	$getChangwat = post('pv');
	$getSearchText = SG\getFirst(post('search'));
	$getOrderBy = SG\getFirst(post('o'),'created');
	$getSortDir = SG\getFirst(post('s'),'d');

	$right = R::Model('project.join.right', $projectInfo);

	$ret = '';

	R::View('project.toolbar', $self, 'การเงิน - '.$projectInfo->calendarInfo->title, 'join', $projectInfo);

	$joinGroupList = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));
	$joinGroupList->my = 'ลงทะเบียนโดยฉัน';
	$joinGroupList->all = 'ใบสำคัญรับเงินทั้งหมด';

	// Get province
	mydb::where('ds.`doid` = :doid AND ds.`isjoin` >= 0', ':doid', $projectInfo->doingInfo->doid);
	if ($getJoinGroup && $getJoinGroup != '*')
			mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $getJoinGroup);

	$stmt = 'SELECT
		p.`changwat`
		, cop.`provname`
		, COUNT(*) `amt`
		FROM %org_dos% ds
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
		%WHERE%
		GROUP BY `changwat`
		';
	$dbs = mydb::select($stmt);

	$changwatList = array('' => '== ทุกจังหวัด ==');
	foreach ($dbs->items as $rs) {
		if ($rs->changwat) {
			$changwatList[$rs->changwat] = $rs->provname.'  ('.$rs->amt.' คน)';
		} else {
			$changwatList['na'] = 'ไม่ระบุ  ('.$rs->amt.' คน)';
		}
	}


	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/money'), NULL, 'sg-form -no-print -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addField('status', array('type' => 'hidden', 'value' => $getStatus));
	$form->addField('o', array('type' => 'hidden', 'value' => $getOrderBy));
	$form->addField('s', array('type' => 'hidden', 'value' => $getSortDir));
	$form->addField(
		'group',
		array(
			'type' => 'select',
			'options' => $joinGroupList,
			'value' => $getJoinGroup,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);
	$form->addField(
		'pv',
		array(
			'type' => 'select',
			'options' => $changwatList,
			'value' => $getChangwat,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);

	$form->addField('psnid', array('type' => 'hidden'));
	$form->addField(
		'search',
		array(
			'type' => 'text',
			'class' => 'sg-autocomplete',
			'placeholder' => 'ค้นชื่อ , CID , โทร',
			'attr' => array(
				'data-query'=>url('project/api/join/person/'.$tpid.'/'.$calId),
				'data-altfld'=>'edit-psnid',
				'data-callback'=>'submit',
			),
		)
	);
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -material -white">search</i>'));

	$self->theme->navbar = $form->build();

	if  ($getJoinGroup == 'all' && empty($getSearchText)) {
		$ret .= R::Page('project.join.paid', NULL, $projectInfo);
	} else {
		$ret .= R::Page('project.join.list', NULL, $projectInfo);
	}


	head('<style type="text/css">
		.navbar.-main .form .form-select, .navbar.-main .form .form-text {width: 160px;}
		</style>
		'
	);

	return $ret;
}



/*
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');
	$tpid = $projectInfo->tpid;


	$calendarInfo = is_object($calId) ? $calId : R::Model('project.calendar.get', $calId);
	$calId = $projectInfo->calid = $calendarInfo->calid;

	$doingInfo = R::Model('org.doing.get', array('calid' => $calId), '{data: "info"}');

	$showJoinGroup = post('group');
	$searchText = post('search');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit)
		return message('error', 'Access Denied');

	R::View('project.toolbar', $self, 'การเงิน - '.$calendarInfo->title, 'join', $projectInfo);

	$joinGroup = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($doingInfo->paidgroup));

	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/money'), NULL, 'sg-form form -inlineitem');
	$form->addConfig('method', 'GET');
	//$form->addData('rel', '#main');
	$form->addField('group',
					array(
						'type' => 'select',
						'options' => $joinGroup,
						'value' => $showJoinGroup,
						'attr' => array('onchange' => 'this.form.submit()'),
					)
				);
	$form->addField('psnid', array('type' => 'hidden'));
	$form->addField('search',
					array(
						'type' => 'text',
						'class' => 'sg-autocomplete',
						//'value' => $searchText,
						'placeholder' => 'ค้นชื่อ , CID , โทร',
						'attr' => array(
												'data-query'=>url('project/api/join/person/'.$tpid.'/'.$calId),
												'data-altfld'=>'edit-psnid',
												'data-callback'=>'submit',
											),
					)
				);
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -search -white"></i>'));

	$self->theme->navbar = $form->build();


	$getConditions->doid = $calendarInfo->doingInfo->doid;
	if ($showJoinGroup && $showJoinGroup != '*') $getConditions->joingroup = $showJoinGroup;
	if ($searchText) $getConditions->search = $searchText;
	$doingInfo = R::Model('org.dopaid.get', $getConditions, '{debug: false}');

	$tables = new Table();
	$tables->thead = array(
										'no' => '',
										'name -nowrap' => 'ชื่อ - นามสกุล',
										'ที่อยู่',
										'เครือข่าย',
										'เดินทาง',
										'rest -center' => 'ที่พัก',
										'money -nowrap -hover-parent' => 'จำนวนเงินเบิก(บาท)',
									);

	foreach ($doingInfo->members as $rs) {
		$menuUi = new Ui('span');
		if ($rs->dopid) {
			$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$rs->dopid).'" data-rel="box" data-width="720" data-height="90%"><i class="icon -material">attach_money</i></a>');

			if ($rs->islock)
				$menuUi->add('<a class="sg-action -rcv-locked" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" title="Mark as not lock - ปลดล็อคใบสำคัญรับเงิน" data-rel="box" data-width="480" data-callback="projectJoinLockRcvCallback"><i class="icon -material -gray">lock</i></a>');
			else
				$menuUi->add('<a class="sg-action -rcv-unlock" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" title="Mark as lock - ล็อคใบสำคัญรับเงิน" data-rel="box" data-width="480" data-callback="projectJoinLockRcvCallback"><i class="icon -material">lock_open</i></a>');

		} else {
			$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.create/'.$rs->psnid).'" title="สร้างใบสำคัญรับเงิน" data-rel="box" data-width="720" data-height="90%"><i class="icon -addbig -white -circle -primary'.($rs->isjoin == 3 ?' -hidden' : '').'"></i></a>');
			$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.not/'.$rs->psnid).'" title="ไม่ต้องสร้างใบสำคัญรับเงิน" data-rel="none" data-callback="projectJoinNotRcvCallback"><i class="icon -save -gray"></i></a>');
		}

		$dropUi = new Ui();
		if ($rs->dopid && !$rs->islock)
			//$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.delete/'.$rs->dopid).'" data-rel="#main" data-title="ลบใบสำคัญรับเงิน" data-ret="'.url('project/join/'.$tpid.'/'.$calId.'/money').'" data-confirm="ต้องการลบใบสำคัญรับเงิน กรุณายืนยัน?"><i class="icon -delete"></i><span>ลบใบสำคัญรับเงิน</span>');

		if ($dropUi->count())
			$menuUi->add(sg_dropbox($dropUi->build()));


		$menu = '<nav class="nav -icons -hover -no-print">'.$menuUi->build().'</nav>'._NL;

		$class = '';
		if ($rs->isjoin == 3) $class .= '-notrcv ';
		else if ($rs->isjoin) $class .= '-joined ';
		if ($rs->dopid) $class .= ' -paided ';
		if ($rs->islock) $class .= ' -locked';

		unset($rs->zip);
		$tables->rows[]=array(
											++$no,
											'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box" data-width="720" data-height="90%">'.trim($rs->prename.' '.$rs->name.' '.$rs->lname).'</a>',
											SG\implode_address($rs,'short'),
											$rs->joingroup,
											str_replace(',', ', ', $rs->tripby),
											$rs->rest,
											($rs->total>0 ? number_format($rs->total,2) : '')
											.$menu,
											'config' => array('class' => $class, 'id'=>'psnid-'.$rs->psnid),
										);
	}

	$ret.=$tables->build();

	//$ret.=print_o($doingInfo,'$doingInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');
	//$ret.=print_o($calendarInfo,'$calendarInfo');

	head('<style type="text/css">
	tr.-joined {color:green;}
	tr.-joined a {color: green;}
	tr.-joined>td:first-child {border-left: 2px green solid;}
	tr.-joined>td {background-color: #f3ffeb;}
	tr.-paided {color: #b651ff;}
	tr.-paided a {color: #b651ff;}
	tr.-paided>td:first-child {border-left: 2px #b651ff solid;}
	tr.-paided>td {background-color: #f6edff; border-bottom:1px #eeddff solid;}
	tr.-locked>td:first-child {border-left: 2px #f00 solid;}
	tr.-locked>td {background-color: #ffefef; border-bottom:1px #eeddff solid;}
	tr.-notrcv>td:first-child {border-left: 2px #333 solid;}
	tr.-notrcv>td {background-color: #ddd; border-bottom:1px #eeddff solid;}
	</style>');

	head('<script type="text/javascript">
	function projectJoinLockRcvCallback($this, ui) {
		//console.log("Mark Lock")
		var $parent = $this.closest("tr")
		$parent.toggleClass("-locked")
		var $icon = $this.find("i")
		if ($icon.hasClass("-lock"))
			$icon.removeClass("-lock -gray").addClass("-unlock")
		else
			$icon.removeClass("-unlock").addClass("-lock -gray")

	}
	function projectJoinNotRcvCallback($this, ui) {
		console.log("Mark Not Recieve")
		var $parent = $this.closest("tr")
		$parent.toggleClass("-notrcv")
		var $icon = $this.find("i")
		$parent.find("i.icon.-addbig").toggleClass("-hidden")
		if ($icon.hasClass("-save"))
			$icon.removeClass("-save -gray").addClass("-save")
		else
			$icon.removeClass("-save").addClass("-save -gray")

	}
	</script>
	<style type="text/css">
	.navbar.-main .form .form-select, .navbar.-main .form .form-text {width: 160px;}
	</style>
	');


	return $ret;
}
*/
?>