<?php
/**
* Project Action Join Name Listing
* Created 2019-02-22
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_list($self, $projectInfo) {
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

	//$ret .= print_o($right, '$right');

	/*
	header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	*/


	// ดูรายการได้เฉพาะผู้ที่มีรายชื่อในโครงการเท่านั้น
	if (!$right->accessJoin) return message('error','Access Denied');


	R::View('project.toolbar', $self, 'ลงทะเบียน - '.$projectInfo->calendarInfo->title, 'join', $projectInfo);


	$orderList = array(
		'name' => 'CONVERT(`fullname` USING tis620)',
		'network' => 'CONVERT(`joingroup` USING tis620)',
		'created' => '`created`',
	);

	$joinGroupList = object_merge((object) array('*'=>'== ทุกเครือข่าย ==') ,json_decode($projectInfo->doingInfo->paidgroup));
	$joinGroupList->my = 'ลงทะเบียนโดยฉัน';

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


	$form = new Form(NULL, url('project/join/'.$tpid.'/'.$calId.'/list'), NULL, 'sg-form -no-print -inlineitem');
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







	// Show All of Register
	// Only show for auth

	$ret .= '<div id="report-output">';

	mydb::where('d.`tpid` = :tpid AND d.`calid` = :calid', ':tpid', $tpid, ':calid', $calId);
	/*
	if ($_SESSION['auth.join.refcode'])
		mydb::where('ds.`refcode` = :refcode', ':refcode', $_SESSION['auth.join.refcode']);
		*/

	if (post('psnid')) {
		mydb::where('ds.`psnid` = :psnid', ':psnid', post('psnid'));
	} else if ($getSearchText != '') {
		list($name, $lname) = sg::explode_name(' ', $getSearchText);
		mydb::where('(p.`cid` LIKE :name OR p.`phone` LIKE :name OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').'))',':name','%'.$name.'%', ':lname','%'.$lname.'%');
	} else if ($getJoinGroup == 'my' && i()->ok) {
		mydb::where('ds.`uid` = :uid', ':uid', i()->uid);
	} else {
		if ($getChangwat) mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
		if ($getJoinGroup && $getJoinGroup != '*')
			mydb::where('ds.`joingroup` = :joingroup', ':joingroup', $getJoinGroup);
	}

	if ($getStatus == 'rcv') {
		mydb::where('do.`dopid` IS NOT NULL');
	} else if ($getStatus == 'norcv') {
		mydb::where('do.`dopid` IS NULL');
	} else if ($getStatus == 'cancel') {
		mydb::where('ds.`isjoin` < 0');
	} else if ($getStatus == 'notprove') {
		mydb::where('ds.`isjoin` = 0');
	}

	mydb::value('$order', $orderList[$getOrderBy]);
	mydb::value('$sort', $getSortDir == 'd' ? 'DESC' : 'ASC');

	// Show Register
	$stmt = 'SELECT
		  d.*
		, ds.*
		, p.`prename`, CONCAT(p.`name`," ",p.`lname`) `fullname`
		, p.`cid`
		, GROUP_CONCAT(do.`dopid`) `dopid`
		, COUNT(do.`dopid`) `hasrcv`
		, do.`islock`
		, GROUP_CONCAT(do.`dopid`) `dopids`
		, do.`formid`
		FROM %org_dos% ds
			LEFT JOIN %org_doings% d USING(`doid`)
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %org_dopaid% do ON do.`doid` = ds.`doid` AND do.`psnid` = ds.`psnid`
		%WHERE%
		GROUP BY `psnid`
		ORDER BY $order $sort
		';

	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;


	$myUid = i()->uid;
	$totals = 0;
	$currentUrl = q();

	$cardUi = new Ui('div', 'ui-card -hover-parent');

	$tables = new Table();
	$tables->addClass('project-join-list');
	$tables->thead = array(
		'no' => '',
		'name -nowrap' => 'ชื่อ-นามสกุล<a href="'.url($currentUrl, array('status'=>$getStatus, 'group'=> $getJoinGroup,'search' => $getSearchText, 'o' => 'name', 's' => $getSortDir == 'a' ? 'd' : 'a')).'"><i class="icon -material'.($getOrderBy == 'name' ? ' -sg-active' : ' -sg-inactive').'">unfold_more</i></a>',
		'type -center' => 'เลขประจำตัวประชาชน',
		'network -nowrap' => 'เครือข่าย<a href="'.url($currentUrl, array('status'=>$getStatus, 'group'=> $getJoinGroup,'search' => $getSearchText, 'o' => 'network', 's' => $getSortDir == 'a' ? 'd' : 'a')).'"><i class="icon -material'.($getOrderBy == 'network' ? ' -sg-active' : ' -sg-inactive').'">unfold_more</i></a>',
		'travel' => 'เดินทาง',
		'rest -center -nowrap' => 'ที่พัก',
		'register -date -hover-parent -nowrap' => 'สมัครเมื่อ<a href="'.url($currentUrl, array('status'=>$getStatus, 'group'=> $getJoinGroup,'search' => $getSearchText, 'o' => 'created', 's' => $getSortDir == 'a' ? 'd' : 'a')).'"><i class="icon -material'.($getOrderBy == 'created' ? ' -sg-active' : ' -sg-inactive').'">unfold_more</i></a>'
	);


	foreach ($dbs->items as $rs) {
		// Generate item menu
		$menuUi = new Ui('span');
		$dropUi = new Ui();

		// All member can view register information
		$menuUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box" title="ดูรายละเอียด" data-width="640"><i class="icon -material">search</i></a>');


		// For member who can edit this record
		if ($right->editJoin || $rs->uid == $myUid) {

			if ($rs->isjoin >= 0 && $rs->isjoin <= 2) {
				$menuUi->add('<a class="sg-action -join-edit" href="'.url('project/join/'.$tpid.'/'.$calId.'/edit/'.$rs->psnid).'" data-rel="box" data-width="640" title="แก้ไขรายละเอียด"><i class="icon -material">edit</i></a>',$rs->islock ? '{class: "-hidden"}' : NULL);
			}


			if ($rs->hasrcv) {
				// Created recieve
				$menuUi->add('<a class="sg-action -rcv-has" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$rs->dopid).'" data-rel="box" title="รายละเอียดใบสำคัญรับเงิน" data-width="640" data-height="90%"><i class="icon -material -has-rcv">attach_money</i><span class="-hidden">ใบสำคัญรับเงิน</span></a>');

				if (!$rs->islock && $right->lockRcv) {
					$menuUi->add('<a class="sg-action -rcv-unlock" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" data-rel="box" title="Mark as lock - ล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock_open</i></a>');
				} else if ($rs->islock && $right->unlockRcv) {
					$menuUi->add('<a class="sg-action -rcv-locked" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$rs->dopid).'" data-rel="box" title="Mark as not lock - ปลดล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock</i></a>');
				} else {
					$menuUi->add('<a class="-rcv-locked"><i class="icon -material">'.($rs->islock ? 'lock' : 'lock_open').'</i></a>');
				}
			} else {
				if ($rs->isjoin < 0) {
					// Not joined
					$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/cancel/'.$rs->psnid).'" data-rel="notify" title="กลับมาลงทะเบียน"  x-data-callback="projectJoinMakeCancelCallback" data-done="load->replace:#report-output"><i class="icon -material -gray">restore</i><span>กลับมาลงทะเบียน</span></a>');
					if ($right->createRcv) {
						$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/delete/'.$rs->psnid).'" data-rel="notify" data-title="ลบข้อมูลการลงทะเบียน" data-confirm="ต้องการลบข้อมูลการลงทะเบียนนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material -gray">cancel</i><span>ลบข้อมูลการลงทะเบียน</span></a>');
					}
				} else if ($rs->isjoin == 0) {
					// Joined but unproved
					if ($right->editJoin) {
						$menuUi->add('<a class="sg-action -join-unproved" href="'.url('project/join/'.$tpid.'/'.$calId.'/proved/'.$rs->psnid).'" title="Mark as check - บันทึกการตรวจสอบข้อมูล" data-rel="none" x-data-callback="projectJoinMakeJoinCallback" data-done="load->replace:#report-output"><i class="icon -material -gray">done</i></a>',$rs->islock ? '{class: "-hidden"}' : NULL);
					}
					if ($right->editJoin || $rs->uid == i()->uid) {
						$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/cancel/'.$rs->psnid).'" data-rel="notify" title="ยกเลิกการลงทะเบียน"  x-data-callback="projectJoinMakeCancelCallback" data-done="load->replace:#report-output"><i class="icon -material">cancel_presentation</i><span>ยกเลิกการลงทะเบียน</span></a>');
					}
				} else if ($rs->isjoin <= 2) {
					// Joined and proved
					if ($right->editJoin) {
						$menuUi->add('<a class="sg-action -join-proved" href="'.url('project/join/'.$tpid.'/'.$calId.'/proved/'.$rs->psnid).'" title="Mark as not check - ยกเลิกการตรวจสอบข้อมูล" data-rel="none" x-data-callback="projectJoinMakeJoinCallback" data-done="load->replace:#report-output"><i class="icon -material">done</i></a>',$rs->islock ? '{class: "-hidden"}' : NULL);
					} else {
						$menuUi->add('<a class="sg-action -join-proved"><i class="icon -material">done</i></a>');
					}
				}

				if ($right->createRcv) {
					if ($rs->isjoin >= 0 && $rs->isjoin < 3) {
						$menuUi->add('<a class="sg-action -rcv-create" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.create/'.$rs->psnid).'" data-rel="box" title="สร้างใบสำคัญรับเงิน" data-width="640"><i class="icon -material">attach_money</i><span class="-hidden">สร้างใบสำคัญรับเงิน</span></a>','{class: "'.($rs->isjoin ? '' : ' -hidden').'"}');
						$dropUi->add('<a class="sg-action -rcv-not" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.not/'.$rs->psnid).'" title="ไม่ต้องสร้างใบสำคัญรับเงิน" data-rel="none" x-data-callback="projectJoinNotRcvCallback" data-done="load->replace:#report-output"><i class="icon -material -gray">done_all</i><span>ไม่ต้องสร้างใบสำคัญรับเงิน</span></a>');
					} else if ($rs->isjoin == 3) {
						$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.not/'.$rs->psnid).'" title="ต้องสร้างใบสำคัญรับเงิน" data-rel="none" x-data-callback="projectJoinNotRcvCallback" data-done="load->replace:#report-output"><i class="icon -material -gray">done_all</i><span>ต้องสร้างใบสำคัญรับเงิน</span></a>');
					}
				}
			}
		}



		if ($right->adminWeb) {
			$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/admininfo/'.$rs->psnid).'" data-rel="box" data-width="640"><i class="icon -material -gray">info</i><span>Information</span></a>');
		}

		if ($dropUi->count()) $menuUi->add(sg_dropbox($dropUi->build()));

		$menu = '<nav class="nav -header -icons -hover -no-print">'.$menuUi->build().'</nav>'._NL;
		$showFull = $right->accessJoin || $rs->uid == $myUid;




		$class = '';
		if ($rs->isjoin == 3) $class .= '-notrcv';
		else if ($rs->isjoin < 0) $class .= '-cancel';
		else if ($rs->isjoin) $class .= '-joined';
		if ($rs->dopid) $class .= ' -paided ';
		if ($rs->islock) $class .= ' -locked';


		if ($rs->isjoin >= 0) $totals++;

		$tables->rows[] = array(
				$rs->isjoin>=0 ? ++$no : '<td></td>',
				'<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$rs->psnid).'" data-rel="box" data-width="640">'.trim($rs->prename.' '.$rs->fullname).'</a>',
				$showFull ? $rs->cid :'',
				$rs->joingroup.($rs->formid ? ' ('.$rs->formid.')' : ''),
				$showFull ? str_replace(',', ', ', $rs->tripby) : '',
				$showFull ? $rs->rest . ($rs->withdrawrest<0 ? ' (ไม่เบิก)' : '').($rs->hotelmate ? '<br />('.$rs->hotelmate.')' : '') :'',
				//	$showFull ? $rs->hotelmate :'',
				($rs->created ? sg_date($rs->created, 'ว ดด ปป') : '')
				.($showFull ? $menu : '')
				,
				//$showFull ? $menu : '',
				'config' => array('class' => $class, 'id'=>'psnid-'.$rs->psnid),
			);


		$cardStr = '<header class="header -hover-parent">'
			. '<h4>'.trim($rs->prename.' '.$rs->fullname).'</h4>'
			. ($showFull ? $menu : '')
			. '</header>'
			. '<div class="detail">'
			. ($showFull ? $rs->regtype.' ' :'')
			. ($showFull ? ' ร่วม '.str_replace(',', ', ', $rs->jointype).' ' : '')
			. ' เครือข่าย : '.$rs->joingroup
			. ($showFull ? ' อาหาร : '.$rs->foodtype :'')
			. ($showFull ? ' เดินทาง : '.$rs->tripby : '')
			. ($rs->created ? '<br /><span class="timestamp">@'.sg_date($rs->created, 'ว ดด ปป').'</span>' : '')
			. '</div>';
		//$cardUi->add($cardStr,'{class: "-hover-parent"}');
	}

	//$ret .= $cardUi->build();

	if ($dbs->count()) {
		$ret .= $tables->build();

		$ret .= '<p>จำนวนผู้ลงทะเบียนล่วงหน้า <b>'.$totals.'</b> คน</p>';
	} else {
		$ret .= message('notify', 'ไม่มีผู้ลงทะเบียน : ยังไม่มีผู้ลงทะเบียนเข้าร่วมกิจกรรมจาก "'.$getJoinGroup.'"');
	}
	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '</div>';

	head('<script type="text/javascript">
		function projectJoinMakeJoinCallback($this, ui) {
			console.log("Mark Join")
			var $parent = $this.closest("tr")
			$parent.toggleClass("-joined")
			$this.toggleClass("-join-proved")
			$parent.find("a.-rcv-create").parent().toggleClass("-hidden")
		}

		function projectJoinMakeCancelCallback($this, ui) {
			console.log("Mark Cancel")
			var $parent = $this.closest("tr")
			$parent.toggleClass("-cancel")
			$this.find("i").toggleClass("-gray").text("restore")
			$parent.find("a.-join-edit").parent().toggleClass("-hidden")
			$parent.find("a.-join-unproved").parent().toggleClass("-hidden")
			$parent.find("a.-rcv-create").parent().toggleClass("-hidden")
		}

		function projectJoinNotRcvCallback($this, ui) {
			console.log("Mark Not Recieve")
			var $parent = $this.closest("tr")
			$parent.toggleClass("-notrcv")
			$parent.find("a.-join-edit").parent().toggleClass("-hidden")
			$parent.find("a.-join-unproved").parent().toggleClass("-hidden")
			$parent.find("a.-join-proved").parent().toggleClass("-hidden")
			$parent.find("a.-rcv-create").parent().toggleClass("-hidden")
		}
		</script>
		<style type="text/css">
		.navbar.-main .form .form-select, .navbar.-main .form .form-text {width: 160px;}
		</style>
		'
	);


	return $ret;
}
?>