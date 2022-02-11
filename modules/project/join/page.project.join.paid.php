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

function project_join_paid($self, $projectInfo) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$right = R::Model('project.join.right', $projectInfo);


	$showJoinGroup = post('group');
	$searchText = post('search');

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit)
		return message('error', 'Access Denied');

	R::View('project.toolbar', $self, 'ใบสำคัญรับเงิน - '.$calendarInfo->title, 'join', $projectInfo);

	$joinGroup = object_merge((object) array(''=>'== แสดงรายชื่อทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));

	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/paid'), NULL, 'sg-form form -inlineitem');
	$form->addConfig('method', 'GET');
	//$form->addData('rel', '#main');
	/*
	$form->addField(
		'group',
		array(
			'type' => 'select',
			'options' => $joinGroup,
			'value' => $showJoinGroup,
			'attr' => array('onchange' => 'this.form.submit()'),
		)
	);
	*/
	$form->addField('psnid', array('type' => 'hidden'));
	$form->addField(
		'search',
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


	$ret .= '<div id="report-output">';


	$getConditions->doid = $projectInfo->doingInfo->doid;
	if ($showJoinGroup) $getConditions->joingroup = $showJoinGroup;
	if ($searchText) $getConditions->search = $searchText;
	$doingInfo = R::Model('org.dopaid.get', $getConditions, '{debug: false, data: "info,bill", order: "name"}');


	$tables = new Table();
	$tables->addClass('project-join-list');
	$tables->thead = array(
		'no' => '',
		'name -nowrap' => 'ชื่อ - นามสกุล',
		'ที่อยู่',
		'money -nowrap -hover-parent' => 'จำนวนเงินเบิก(บาท)',
	);

	foreach ($doingInfo->bills as $rs) {
		$menuUi = new Ui('span');
		if ($rs->dopid) {
			$menuUi->add('<a class="sg-action -rcv-has" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$rs->dopid).'" data-rel="box" title="รายละเอียดใบสำคัญรับเงิน" data-width="640" data-height="90%"><i class="icon -material -has-rcv">attach_money</i><span class="-hidden">ใบสำคัญรับเงิน</span></a>');

			if (!$rs->islock && $right->lockRcv) {
				$menuUi->add('<a class="sg-action -rcv-unlock" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" data-rel="box" title="Mark as lock - ล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock_open</i></a>');
			} else if ($rs->islock && $right->unlockRcv) {
				$menuUi->add('<a class="sg-action -rcv-locked" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" data-rel="box" title="Mark as not lock - ปลดล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock</i></a>');
			} else {
				$menuUi->add('<a class="-rcv-locked"><i class="icon -material">'.($rs->islock ? 'lock' : 'lock_open').'</i></a>');
			}
			/*
			if ($rs->islock) {
				$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" title="Mark as not lock - ปลดล็อคใบสำคัญรับเงิน" data-rel="box" data-width="480" data-callback="projectJoinLockRcvCallback"><i class="icon -material">lock</i></a>');
			} else {
				$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" title="Mark as lock - ล็อคใบสำคัญรับเงิน" data-rel="box" data-width="480" data-callback="projectJoinLockRcvCallback"><i class="icon -material">lock_poen</i></a>');
			}
			*/

		}
		/*
		 else if ($right->createRcv) {
			$menuUi->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.create/'.$rs->psnid).'" title="สร้างใบสำคัญรับเงิน"><i class="icon -addbig -white -circle -primary'.($rs->isjoin == 3 ?' -hidden' : '').'"></i></a>');
			$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.not/'.$rs->psnid).'" title="ไม่ต้องสร้างใบสำคัญรับเงิน" data-rel="none" data-callback="projectJoinNotRcvCallback"><i class="icon -save -gray"></i></a>');
		}
		*/

		$dropUi = new Ui();
		if (!$rs->islock && $right->createRcv) {
			$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.delete/'.$rs->dopid).'" data-rel="notify" data-confirm="ต้องการลบใบสำคัญรับเงิน กรุณายืนยัน?" data-removeparent="tr"><i class="icon -delete"></i><span>ลบใบสำคัญรับเงิน</span></a>');
		}

		if ($dropUi->count())
			$menuUi->add(sg_dropbox($dropUi->build()));


		$menu = '<nav class="nav -header -icons -hover -no-print">'.$menuUi->build().'</nav>'._NL;

		$class = '';
		if ($rs->isjoin == 3) $class .= '-notrcv';
		else if ($rs->isjoin < 0) $class .= '-cancel';
		else if ($rs->isjoin) $class .= '-joined';
		if ($rs->dopid) $class .= ' -paided ';
		if ($rs->islock) $class .= ' -locked';

		unset($rs->zip);
		$tables->rows[] = array(
				++$no,
				$rs->psnid ? '<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box">'.trim($rs->paidname).'</a>' : $rs->paidname,
				$rs->address,
				($rs->total>0 ? number_format($rs->total,2) : '')
				.$menu,
				'config' => array('class' => $class, 'id'=>'psnid-'.$rs->psnid),
			);
		$totalPaid += $rs->total;
	}

	$tables->tfoot[] = array('<td></td>', 'รวมเงิน', '', number_format($totalPaid,2));
	$ret.=$tables->build();

	//$ret.=print_o($doingInfo,'$doingInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret .= '</div>';


	head('<style type="text/css">
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
	</script>');


	return $ret;
}
?>