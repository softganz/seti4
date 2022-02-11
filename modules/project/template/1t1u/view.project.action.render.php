<?php
/**
* Project View :: Action Render
* Created 2021-02-13
* Modify  2021-02-13
*
* @param Object $projectInfo
* @param Object $actionInfo
* @return String
*
* @usage R::View("project.action.render")
*/

$debug = true;

function view_project_action_render($projectInfo, $actionInfo) {

	if (empty($actionInfo)) return;

	$tpid = $actionInfo->tpid;
	$actionId = $actionInfo->actionId;

	$isEdit = $projectInfo->info->isEdit || $actionInfo->uid==i()->uid;
	$isAdmin = $projectInfo->info->isAdmin;
	$isAccessExpense = $isEdit || $isAdmin || $projectInfo->info->isOwner;// || $actionInfo->uid==i()->uid;
	$isEmployeeAction = in_array($projectInfo->info->ownertype, array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE));
	
	$showBudget = $projectInfo->is->showBudget;

	$activityOption = $projectInfo->settings->activity;
	$showFields = new stdClass();
	if ($activityOption->field) {
		foreach (explode(',', $activityOption->field) as $item) $showFields->{trim($item)} = true;
	}


	$lockReportDate = project_model::get_lock_report_date($tpid);

	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (debug('inline')) $inlineAttr['data-debug'] = 'yes';
	}
	$inlineAttr['class'] .= ' project-action-item';

	$inlineAttr['class'] .= ' -'.($actionInfo->flag == _PROJECT_DRAFTREPORT ? 'draft' : $actionInfo->part);
	if ($lockReport) $inlineAttr['class'] .= ' -locked';

	$ret.='<div id="project-action-'.$actionId.'" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret.='<h3 class="title">'.$actionInfo->title.'</h3>';



	$lockReport = $actionInfo->actionDate <= $lockReportDate;
	$isItemEdit = $isEdit && !$lockReport;

	// Show profile and status of action
	$profileStr = '<div class="date"><span class="day">'.sg_date($actionInfo->actionDate,'ว').'</span> <span class="month">'.sg_date($actionInfo->actionDate,'ดดด').'</span> <span class="year">'.sg_date($actionInfo->actionDate,'ปปปป').'</span>'.($actionInfo->actionTime ? ' <span class="time">เวลา '.$actionInfo->actionTime.' น.</span>' : '');
	$profileStr .= '</div><!--date-->'._NL;
	$profileStr .= '<div class="owner">';
	$profileStr .= '<span class="owner-photo"><img class="owner-photo" src="'.model::user_photo($actionInfo->username).'" alt="'.$actionInfo->ownerName.'" /></span>';
	$profileStr .= '<span class="owner-name">';
	$profileStr .= ($actionInfo->username ? '<a href="'.url('profile/'.$actionInfo->uid).'">':'').$actionInfo->ownerName.($actionInfo->username?'</a>' : '');
	$profileStr .= '</span>';
	//$profileStr .= '<span class="created">เมื่อ '.sg_date($actionInfo->created, 'ว ดด ปปปป H:i:s').'</span>';

	$forms['trainer'] = 'รายงานจากพี่เลี้ยง';
	$forms['owner'] = 'รายงานจากพื้นที่'.($lockReport ? ' (ปิดงวดแล้ว)' : '');
	$profileStr .='<div style="clear:both;">'
		.$forms[$actionInfo->part]
		.($actionInfo->flag == 0 ? '<br /><font color="red">ร่างรายงาน</font> ' : '')
		.($actionInfo->flag == _PROJECT_DRAFTREPORT && $isItemEdit ? ' - <a href="'.url('paper/'.$actionInfo->tpid.'/'.$part, array('act' => 'addreport', 'trid' => $actionId)).'" title="แก้ไขร่างบันทึกกิจกรรม">แก้ไขร่างบันทึกกิจกรรม</a>' : '')
		.'</div>';

	$status = array();
	if ($topic->uid == $actionInfo->uid) $status[] = 'Project creater';
	if (project_model::is_owner_of($tpid, $actionInfo->uid)) $status[] = 'Project owner';
	if (project_model::is_trainer_of($tpid, $actionInfo->uid)) $status[] = 'Project trainer';
	if ($actionInfo->uid == i()->uid) $status[] = 'My Report';
	if ($isItemEdit) $status[] = 'Editable';

	$profileStr .= '<div class="status">'.implode(' , ', $status).'</div>';
	if ($actionInfo->modified) {
		//$profileStr .= '<div class="modify">แก้ไขโดย <strong>'.$actionInfo->modifybyname.'</strong> เมื่อ <strong>'.sg_date($actionInfo->modified, 'ว ดด ปปปป H:i:s').' น.</strong></div>';
	}
	$profileStr .= '</div><!--owner-->'._NL;



	$ui = new Ui(NULL, 'ui-menu -no-print');
	if ($isEdit) {
		if ($isItemEdit) {
			$ui->add('<a class="sg-action" href="'.url('project/'.$actionInfo->tpid.'/info/action.post/'.$actionInfo->actionId).'" data-rel="box" data-width="640" title="แก้ไข'.($actionInfo->flag == _PROJECT_DRAFTREPORT ? 'ร่าง' : '').'บันทึกกิจกรรม">'
				.'<i class="icon -material">edit</i>'
				.'<span>แก้ไข'.($actionInfo->flag == 0 ? 'ร่าง' : '').'บันทึกกิจกรรม</span>'
				.'</a>');

			if (!$isEmployeeAction) {
				$ui->add('<a class="" href="'.url('project/'.$tpid.'/info.expense/'.$actionId).'">'
					.'<i class="icon -material">attach_money</i>'
					.'<span>ค่าใช้จ่าย/เอกสารการเงิน</span>'
					.'</a>');

				$ui->add('<a class="" href="'.url('project/'.$tpid.'/info.join/'.$actionInfo->calid).'">'
					.'<i class="icon -material">people</i>'
					.'<span>ผู้เข้าร่วมกิจกรรม</span>'
					.'</a>');
			}

			$ui->add('<sep>');

			$ui->add('<a class="sg-action" href="'.url('project/' . $tpid .'/info/action.remove/' . $actionInfo->actionId) . '" data-confirm="ยืนยันว่าจะลบบันทึกกิจกรรมนี้จริง?" data-rel="notify" data-done="remove:#project-action-'.$actionId.' | load->replace:#project-action-plan:'.url('project/'.$tpid.'/action.plan').'">'
				.'<i class="icon -material">delete</i>'
				.'<span>ลบบันทึกกิจกรรม</span>'
				.'</a>');

			if (user_access('administer projects') || project_model::is_trainer_of($tpid)) {
				//$ui->add('<a href="'.url('project/edit/moveactivity/'.$actionId).'" class="inline-removeactivity" title="ยืนยันว่าจะย้ายรายงานนี้จริง?">ย้ายไปเป็นรายงาน'.($actionInfo->part=='owner'?'พี่เลี้ยง':'ผู้รับผิดชอบ').'</a>');
			}
		}
		$profileStr .= '<div class="clear"></div>'.$ui->build('ul','menu -vertical');
	}



	// Show action detail
	$actStr = _NL.'<a name="tr-'.$actionId.'"></a>';
	if (empty($tpid)) $actStr .= '<h3>ชื่อโครงการ : '.$actionInfo->projectTitle.' <a href="'.url('paper/'.$actionInfo->tpid).'">&raquo;</a></h3>'._NL;
	//$actStr.='<h4>ชื่อกิจกรรม : '.$actionInfo->title.'</h4>'._NL;
	//.$actionInfo->title.'</h4>'._NL;
	//$actStr.='Cal Title='.$actionInfo->calTitle.'<br />';
	//if ($actionInfo->calid) ;
	//if (debug('method')) $actStr.=$actionInfo->photos.print_o($actionInfo,'$actionInfo');




	// Generate Photo Show
	$photoStr = $rcvStr = $docStr='';

	if ($actionInfo->gallery || $actionInfo->rcvPhotos) {
		$stmt = 'SELECT
			f.`fid`, f.`type`, f.`file`, f.`title`, f.`tagname`
			FROM %topic_files% f
			WHERE f.`tpid` = :tpid
				AND (((f.`refid` = :refid AND f.`tagname` = "project,action") OR f.`gallery` = :gallery) OR (f.`refid` = :refid AND `tagname` = :tagname))';
		$photos = mydb::select($stmt, ':tpid', $tpid, ':refid', $actionId, ':tagname', 'project,rcv', ':gallery', SG\getFirst($actionInfo->gallery,-1));
		//$ret .= mydb()->_query;

		if (i()->username == 'softganz') {
			//$ret .= print_o($actionInfo,'$actionInfo');
			//$ret .= print_o($photos,'$photos');
		}

		foreach ($photos->items as $item) {
			$photoStrItem = '';
			$ui = new Ui('span');
			if ($item->tagname == 'project,rcv' && !$isAccessExpense) continue;

			if ($item->type == 'photo') {
				//$ret.=print_o($item,'$item');
				$photo=model::get_photo_property($item->file);

				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
				}

				$photo_alt = $item->title;
				$photoStrItem .= '<li class="-hover-parent">';

				$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

				$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem .= '<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$photoStrItem .= ' />';
				$photoStrItem .= '</a>';


				if ($item->tagname == 'project,rcv') $photoStrItem .= '<p>(เอกสารการเงิน)</p>';
				if ($isItemEdit) {
					$photoStrItem .= view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $item->fid, 'class' => '-fill'), $item->title, $isItemEdit, 'text');
				} else {
					$photoStrItem .= '<span>'.$item->title.'</span>';
				}
				$photoStrItem .= '</li>'._NL;

				if ($item->tagname == 'project,rcv') 
					$rcvStr .= $photoStrItem;
				else
					$photoStr .= $photoStrItem;

			} else if ($item->type == 'doc') {
				//if ($item->tagname == 'project,rcv' && !$isAccessExpense) continue;
				$photoStrItem .= '<li class="-hover-parent">';
				$photoStrItem .= '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem .= '<img class="doc-logo -pdf" src="http://img.softganz.com/icon/icon-file.png" width="63" style="display: block; padding: 16px; margin: 0 auto; background-color: #eee; border-radius: 50%;" />';
				$photoStrItem .= $item->title;
				$photoStrItem .= '</a>';

				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/docs.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
				}
				$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
				$photoStrItem .= '</li>';

				$docStr .= $photoStrItem;
			}
		}
	}


	$actStr .= '<div class="-photolist -action">'._NL;
	$actStr .= '<ul id="project-actphoto-'.$actionId.'" class="ui-album">'._NL;
	$actStr .= $photoStr;
	$actStr .= '</ul>'._NL;

	if ($isItemEdit) {
		$actStr .= '<form class="sg-upload -no-print" method="post" enctype="multipart/form-data" '
			.'action="'.url('project/edit/tr', array('tpid' => $tpid, 'action' => 'photo', 'tr' => $actionId)).'" '
			.'data-rel="#project-actphoto-'.$actionId.'" data-append="li">'
			. '<input type="hidden" name="tag" value="project,action" />'
			.'<span class="btn btn-success fileinput-button"><i class="icon -camera"></i>'
			.'<span>ส่งภาพถ่ายหรือไฟล์รายงาน</span>'
			.'<input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" />'
			.'</span>'
			.'</form>'._NL;
	}

	$actStr .= '</div><!--photo-->'._NL;






	if ($docStr) $actStr .= '<section class="--doclist"><h3>ไฟล์ประกอบกิจกรรม</h3><ul class="ui-item -sg-flex -doc -justify-left">'.$docStr.'</ul></section>';


	if (!$isEmployeeAction) {
		$actStr .= '<div class="-photolist -rcv">'._NL;
		if ($rcvStr || $isAccessExpense) {
			$actStr .= '<h3>ภาพเอกสารการเงิน</h3>'._NL
				. '<ul id="project-rcvphoto-'.$actionId.'" class="">'
				. $rcvStr
				. '</ul>'._NL;
		}

		if ($isItemEdit) {
			$actStr .= '<form class="sg-upload -no-print" '
				. 'method="post" '
				. 'enctype="multipart/form-data" '
				. 'action="'.url('project/'.$tpid.'/info/expense.photo.upload/'.$actionInfo->actionId).'" '
				.'data-rel="#project-rcvphoto-'.$actionId.'" '
				. 'data-append="li">'
				. '<input type="hidden" name="tagname" value="action" />'
				. '<span class="btn btn-success fileinput-button"><i class="icon -material">add_a_photo</i>'
				. '<span>ส่งภาพใบเสร็จรับเงิน</span>'
				. '<input type="file" name="photo[]" multiple="true" class="inline-upload -rcv" />'
				. '</span>'
				. '</form>';
		}
		$actStr .= '</div><!--photo-->'._NL;
	}






	$actStr .= '<div class="project-action-summary">'._NL;


	if ($showFields->objectiveDetail) {
		$actStr.='<h5>วัตถุประสงค์</h5>'.sg_text2html($actionInfo->objectiveDetail)._NL;
	}




	if ($showFields->actionPreset) {
		$actStr .= '<h5>กิจกรรมตามแผน</h5>';
		if ($showFields->targetPreset) {
			$actStr .= '<p>จำนวนกลุ่มเป้าหมายที่ตั้งไว้ '.number_format($actionInfo->targetPresetAmt).' คน</p>'
			. '<h6>รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้</h6>'
			. '<p>'.nl2br($actionInfo->targetPresetDetail)._NL;
		}
		$actStr .= '<h6>รายละเอียดกิจกรรมตามแผน</h6>'
			. '<p>'.nl2br($actionInfo->actionPreset).'</p>'._NL;
	}




	$actStr .= '<h5>กิจกรรมที่ปฎิบัติ</h5>';
	if ($showFields->targetJoin) {
		$actStr .= '<p>จำนวนคน/ผู้เข้าร่วมกิจกรรมจริง '.number_format($actionInfo->targetJoinAmt).' คน</p>'
			. '<h6>รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม</h6>'
			. view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text3','tr'=>$actionId,'ret'=>'html'),
				$actionInfo->targetJoinDetail, $isItemEdit, 'textarea')._NL
			. '<h6>รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง</h6>';
	}
	$actStr .= view::inlineedit(array('group' => 'tr:activity', 'fld' => 'text2', 'tr' => $actionId, 'ret' => 'html'),
			$actionInfo->actionReal, $isItemEdit, 'textarea')._NL;




	if ($showFields->outputPreset) {
		$actStr.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($actionInfo->outputOutcomePreset)._NL;
	}



	$actStr .= '<h5>ผลที่เกิดขึ้นจริง / ผลผลิต (Output) / ผลลัพธ์ (Outcome) / ผลสรุปที่สำคัญของกิจกรรม</h5>'
		.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text4','tr'=>$actionId,'ret'=>'html'),
			$actionInfo->outputOutcomeReal, $isItemEdit, 'textarea')._NL;



	if ($showFields->rate1 && $actionInfo->rate1 >= 0) {
		$actStr.='<h5>ประเมินความสำเร็จของการดำเนินกิจกรรม</h5><p>'.$extimateList[$actionInfo->rate1].' ('.$actionInfo->rate1.')</p>';
	}


	if ($actionInfo->targetJoinAmt > 0) {
		$actStr .= '<h5>กลุ่มเป้าหมายที่เข้าร่วม</h5>'
			. 'จำนวน '.number_format($actionInfo->targetJoinAmt,0).' คน '
			. 'จากที่ตั้งไว้ '.number_format($actionInfo->targetPresetAmt,0).' คน<br />'
			. 'ประกอบด้วย'
			. view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text9','tr'=>$actionId,'ret'=>'html'), $actionInfo->targetJoinDetail, $isItemEdit, 'textarea')._NL;
	}


	if ($showFields->problem) {
		$actStr .= '<h5>ปัญหา/แนวทางแก้ไข</h5>'
			.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text5','tr'=>$actionId,'ret'=>'html'),
				$actionInfo->problem, $isItemEdit, 'textarea')._NL;
	}




	if ($actionInfo->part == 'owner') {
		if ($showFields->recommendation) {
			$actStr .= '<h5>ข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by.'</h5>'
				.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text5','tr'=>$actionId,'ret'=>'html'),
					$actionInfo->recommendation, $isItemEdit, 'textarea')._NL;
		}


		if ($showFields->support) {
			$actStr .= '<h5>ความต้องการสนับสนุนจากพี่เลี้ยงและ '.$projectInfo->settings->grant->pass.'</h5>'
				.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text5','tr'=>$actionId,'ret'=>'html'),
					$actionInfo->support, $isItemEdit, 'textarea')._NL;
		}

		if ($showFields->followerName) {
			$actStr .= '<h5>ชื่อผู้ติดตามในพื้นที่ของ '.$projectInfo->settings->grant->by.'</h5>'
				.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'detail2','tr'=>$actionId),$actionInfo->followerName, $isItemEdit, 'text')._NL;
		}

	} else {
		if ($showFields->recommendation) {
			$actStr .= '<h5>ข้อเสนอแนะต่อ '.$projectInfo->settings->grant->by.'</h5>'
				. view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text6','tr'=>$actionId,'ret'=>'html'),
				$actionInfo->recommendation, $isItemEdit, 'textarea')._NL;
		}

		if ($showFields->support) {
			$actStr .= '<h5>ข้อเสนอแนะต่อพื้นที่</h5>'
				. view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text7','tr'=>$actionId,'ret'=>'html'),
				$actionInfo->support, $isItemEdit, 'textarea')._NL;
		}
	}


	if ($showFields->followerRecommendation) {
		$actStr .= '<h5>คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่</h5>'
			.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text5','tr'=>$actionId,'ret'=>'html'),
				$actionInfo->followerRecommendation, $isItemEdit, 'textarea')._NL;
	}


	$actStr .= '</div><!--project-action-summary-->'._NL;





	if (!$isEmployeeAction && $showBudget && $actionInfo->part == 'owner') {
		$moneyTable = new Table();
		$moneyTable->addClass('project-action-money-table -center');
		$moneyTable->caption = 'รายงานการใช้เงิน';
		$moneyTable->thead = '<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th><th rowspan="2">สถานะ</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';

		$moneyTable->rows[] = array(
			view::inlineedit(array('fld' => 'num1', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_meed, 2), false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num2', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_wage, 2), false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num3', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_supply, 2), false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num4', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_material, 2), false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num5', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_utilities, 2),false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num6', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_other, 2), false && $isItemEdit, 'text'),
			view::inlineedit(array('fld' => 'num7', 'tr' => $actionId, 'ret' => 'numeric'), number_format($actionInfo->exp_total, 2), false && $isItemEdit, 'text'),
			$isAdmin ? '<a href="'.url('project/edit/lockmoney/'.$actionId).'" class="project-lockmoney"><i class="icon -'.($actionInfo->flag == _PROJECT_LOCKREPORT ? 'lock' : 'unlock').'"></i></a>' : '<i class="icon -'.($actionInfo->flag == _PROJECT_LOCKREPORT ? 'lock' : 'unlock').' -gray"></i>',
		);

		$actStr .= '<div class="project-action-money">'.$moneyTable->build().'</div>'._NL;
	}






	$ret .= '<div class="project-action-profile">'.$profileStr.'</div>'._NL;
	$ret .= '<div id="action-'.$actionId.'" class="project-action-detail">'.$actStr.'</div>'._NL;


	if (debug('method')) $ret .= print_o($actionInfo, '$actionInfo').str_replace(',', '<br />', $actionInfo->photos);
	
	//$ret .= print_o($actionInfo,'$actionInfo');
	//$ret .= print_o($activityOption,'$activityOption');
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret.='</div><!--project-action-->';


	return $ret;
}
?>
