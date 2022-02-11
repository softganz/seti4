<?php
/**
 * Show activity
 *
 * @param Record Set $topic
 * @return String
 */
function project_activity_view($self,$actid) {
	if (is_object($actid)) {
		$activity=$actid;
		$actid=$activity->trid;
		$tpid=$activity->tpid;
	} else {
		$tpid=mydb::select('SELECT `tpid` FROM %project_tr% WHERE `trid`=:trid AND `formid`="activity" LIMIT 1',':trid',$actid)->tpid;
	}
	project_model::set_toolbar($self,'Project Activities');

	//if (empty($tpid)) return message('error','ไม่มีข้อมูลตามเงื่อนไขที่ระบุ');


	$stmt='SELECT tr.*, t.`title` projecttitle, c.`title`,
						u.`username`, u.`name` poster, mu.`name` modifybyname,
						mac.`detail1` mainactivity,
						mac.`text3` `presetOutputOutcome`,
						ac.`targetpreset`,
						ac.*,
						agr.`name` activitygroupName,
						GROUP_CONCAT(DISTINCT p.`fid`, "|" , p.`file`) photos
					FROM %project_tr% tr
						LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
						LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
						LEFT JOIN %project_activity% ac ON c.`id`=ac.`calid`
						LEFT JOIN %project_tr% mac ON ac.`mainact`=mac.`trid`
						LEFT JOIN %users% u ON u.`uid`=tr.`uid`
						LEFT JOIN %topic_files% p
							ON tr.`gallery` IS NOT NULL AND p.`tpid`=tr.`tpid` AND p.`gallery`=tr.`gallery`
						LEFT JOIN %users% mu ON mu.`uid`=tr.`modifyby`
						LEFT JOIN %tag% agr ON agr.`taggroup`="project:activitygroup" AND agr.`catid`=ac.`activitygroup`
					WHERE tr.`formid`="activity" AND tr.`trid`=:trid
					GROUP BY tr.trid
					LIMIT 1';
	//$rs=mydb::select($stmt,':trid',$actid);
	//$ret.=mydb()->_query;
	$rs=$activity;
	//$ret.=print_o($rs,'$rs');

	$ret.='<div class="toolbar"><h3 class="title">'.$rs->title.'</h3></div>';
	if ($isEdit) {
		$inlineAttr['class']='inline-edit';
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-activity" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->addClass('project-activity');
	$tables->thead=array('วันที่ - เวลา','กิจกรรม - รายละเอียด');
	$ui=new ui();
	$lockReportDate=project_model::get_lock_report_date($tpid);

	$lockReport=$rs->date1<=$lockReportDate;
	$isItemEdit=$isEdit && !$lockReport;

	// Show profile and status of activity
	$profile='<div class="date"><span class="day">'.sg_date($rs->date1,'ว').'</span> <span class="month">'.sg_date($rs->date1,'ดดด').'</span> <span class="year">'.sg_date($rs->date1,'ปปปป').'</span>'.($rs->detail1?' <span class="time">เวลา '.$rs->detail1.' น.</span>':'');
	$profile.='</div><!--date-->'._NL;
	$forms['trainer']='รายงานจากพี่เลี้ยง';
	$forms['owner']='รายงานจากพื้นที่'.($lockReport?' (ปิดงวดแล้ว)':'');
	$profile.='<div style="clear:both;">'.$forms[$rs->part].($rs->flag==0?'<br /><font color="red">ร่างรายงาน</font> ':'').($rs->flag==_PROJECT_DRAFTREPORT && $isItemEdit?' - <a href="'.url('paper/'.$rs->tpid.'/'.$part,array('act'=>'addreport','trid'=>$rs->trid)).'" title="แก้ไขร่างบันทึกกิจกรรม">แก้ไขร่างบันทึกกิจกรรม</a>':'').'</div>';
	$profile.='<div class="owner"><span class="owner-by">โพสท์โดย</span>';
	$profile.='<span class="owner-photo"><img class="owner-photo" src="'.model::user_photo($rs->username).'" alt="'.$rs->poster.'" /></span>';
	$profile.='<span class="owner-name">';
	$profile.=($rs->username?'<a href="'.url('profile/'.$rs->uid).'">':'').$rs->poster.($rs->username?'</a>':'');
	$profile.='</span>';
	$profile.='<span class="created">เมื่อ '.sg_date($rs->created,'ว ดดด ปปปป H:i:s').'</span>';
	$status=array();
	if ($topic->uid==$rs->uid) $status[]='Project creater';
	if (project_model::is_owner_of($tpid,$rs->uid)) $status[]='Project owner';
	if (project_model::is_trainer_of($tpid,$rs->uid)) $status[]='Project trainer';
	if ($rs->uid==i()->uid) $status[]='My Report';
	if ($isItemEdit) $status[]='Editable';
	$profile.='<div class="status">'.implode(' , ',$status).'</div>';
	$profile.= '</div><!--owner-->'._NL;
	if ($rs->modified) {
		$profile.='<div class="modify">แก้ไขโดย <strong>'.$rs->modifybyname.'</strong> เมื่อ <strong>'.sg_date($rs->modified,'ว ดดด ปปปป H:i:s').' น.</strong></div>';
	}

	$ui->clear();
	if ($isEdit) {
		if ($isItemEdit) {
			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/'.$part,array('act'=>'addreport','trid'=>$rs->trid)).'" title="แก้ไข'.($rs->flag==_PROJECT_DRAFTREPORT?'ร่าง':'').'บันทึกกิจกรรม">แก้ไข'.($rs->flag==0?'ร่าง':'').'บันทึกกิจกรรม</a>');
			$ui->add('<a class="-new" href="'.url('project/'.$tpid.'/info.expense/'.$rs->calid).'">ค่าใช้จ่าย/เอกสารการเงิน</a>');
			$ui->add('<a class="sg-action" href="'.url('project/edit/removeactivity/'.$rs->trid).'" data-confirm="ยืนยันว่าจะลบบันทึกกิจกรรมนี้จริง?" data-rel="notify" data-removeparent="#project-activity-'.$actid.'"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>');
			if (user_access('administer projects') || project_model::is_trainer_of($tpid)) {
				//$ui->add('<a href="'.url('project/edit/moveactivity/'.$rs->trid).'" class="inline-removeactivity" title="ยืนยันว่าจะย้ายรายงานนี้จริง?">ย้ายไปเป็นรายงาน'.($rs->part=='owner'?'พี่เลี้ยง':'ผู้รับผิดชอบ').'</a>');
			}
		}
		$profile.='<div class="clear"></div>'.$ui->build('ul','menu -vertical');
	}

	// Show activity detail
	$act=_NL.'<a name="tr-'.$rs->trid.'"></a>';
	if (empty($tpid)) $act.='<h3>ชื่อโครงการ : '.$rs->projecttitle.' <a href="'.url('paper/'.$rs->tpid).'">&raquo;</a></h3>'._NL;
	$act.='<h4>ชื่อกิจกรรม : '.$rs->title.'</h4>'._NL;
	//$act.='Cal Title='.$rs->calTitle.'<br />';
	//if ($rs->calid) ;
	//$act.=print_o($rs,'$rs');
	if (debug('method')) $act.=$rs->photos.print_o($rs,'$rs');
	$act.='<div class="photo">'._NL;
	$act.='<ul class="photo -activity">'._NL;
	$photoStr='';
	$rcvStr='';
	$docStr='';
	if ($rs->gallery) {
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title`, f.`tagname` FROM %topic_files% f WHERE f.`gallery`=:gallery', ':gallery',$rs->gallery);
		foreach ($photos->items as $item) {
			$photoStrItem='';
			list($photoid,$photo)=explode('|',$item);
			if ($item->type=='photo') {
				//$ret.=print_o($item,'$item');
				if ($item->tagname=='project,rcv' && !$isAccessExpense) continue;
				$photo=model::get_photo_property($item->file);
				$photo_alt=$item->title;
				$photoStrItem .= '<li>';
				$photoStrItem.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$photoStrItem.=' />';
				$photomenu=array();
				if ($item->tagname=='project,rcv') $photoStrItem.='<p>(เอกสารการเงิน)</p>';
				if ($isItemEdit) {
					$photoStrItem.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$item->fid),$item->title,$isItemEdit,'text');
				} else {
					$photoStrItem.='<span>'.$item->title.'</span>';
				}
				$photoStrItem .= '</li>'._NL;
				if ($item->tagname=='project,rcv') 
					$rcvStr.=$photoStrItem;
				else
					$photoStr.=$photoStrItem;

			} else if ($item->type=='doc') {
				$docStr.='<li>';
				$docStr.='<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'">';
				$docStr.=$item->title;
				$docStr.='</a>';
				$photomenu=array();
				$ui->clear();
				if ($isItemEdit) {
					$ui->add('[<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li">ลบไฟล์</a>]');
				}
				$docStr.=$ui->build();
				$docStr.='</li>';
			}
		}
	}
	$act.=$photoStr;
	$act.='</ul>'._NL;
	if ($docStr) $act.='<h3>ไฟล์ประกอบกิจกรรม</h3><ul class="doc">'.$docStr.'</ul>';

	if ($rcvStr || $isItemEdit) $act.='<h3>ภาพเอกสารการเงิน</h3><ul id="projectrcv-photo-'.$rs->trid.'" class="photo -rcv">'.$rcvStr.'</ul>';

	$act.='</div><!--photo-->'._NL;

	$act.='<div class="project-activity-detail">'._NL;
	if (cfg('project.usemainact')) {
		$act.='<p><strong>กิจกรรมหลัก : '.$rs->mainactivity.'</strong></p>';
	}
	$act.='<p><strong>กลุ่มกิจกรรม : '.$rs->activitygroupName.'</strong></p>';

	$extimateList=array(4=>'บรรลุผลมากกว่าเป้าหมาย',3=>'บรรลุผลตามเป้าหมาย',2=>'เกือบได้ตามเป้าหมาย',1=>'ได้น้อยกว่าเป้าหมายมาก');

	$act.='<h5>คุณภาพกิจกรรม : '.$extimateList[$rs->rate1].' ('.$rs->rate1.')</h5>';
	// เดิมใช้ค่า $rs->targetpreset
	$act.='<h5>กิจกรรมตามแผน</h5>'
						.'<p>รายละเอียดกิจกรรมตามแผน</p>'
						.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text1','tr'=>$rs->trid,'ret'=>'html','button'=>'yes'),
							$rs->text1, $isItemEdit, 'textarea')._NL;
	// เดิมใช้ค่า $rs->num8
	$act.='<h5>กิจกรรมที่ปฎิบัติจริง</h5>'
						.'<p>รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม</p>'
						.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text9','tr'=>$rs->trid,'ret'=>'html'), $rs->text9, false, 'textarea')._NL
						.'<p>รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง</p>'
						.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text2','tr'=>$rs->trid,'ret'=>'html'),
							$rs->text2, $isItemEdit, 'textarea')._NL;



	$act.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($rs->presetOutputOutcome)._NL;
	$act.='<h5>ผลที่เกิดขึ้นจริง / ผลผลิต (Output) / ผลลัพธ์ (Outcome) / ผลสรุปที่สำคัญของกิจกรรม</h5>'
						.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text4','tr'=>$rs->trid,'ret'=>'html'),
							$rs->text4, $isItemEdit, 'textarea')._NL;


	$act.='</div><!--project-activity-detail-->'._NL;
	//$act.=print_o($rs,'$rs');

	if ($rs->part=='owner') {
		$money = new Table();
		$money->addClass('project-activity-money');
		$money->caption='รายงานการใช้เงิน';
		$money->thead='<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th><th rowspan="2">สถานะ</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
		$money->rows[]=array(
													view::inlineedit(array('fld'=>'num1','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num1,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num2','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num2,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num3','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num3,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num4','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num4,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num5','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num5,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num6','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num6,2),false && $isItemEdit,'text'),
													view::inlineedit(array('fld'=>'num7','tr'=>$rs->trid,'ret'=>'numeric'),number_format($rs->num7,2),false && $isItemEdit,'text'),
													$isAdmin ? '<a href="'.url('project/edit/lockmoney/'.$rs->trid).'" class="project-lockmoney"><i class="icon -'.($rs->flag==_PROJECT_LOCKREPORT?'lock':'unlock').'"></i></a>' : '<i class="icon -'.($rs->flag==_PROJECT_LOCKREPORT ? 'lock':'unlock').' -gray"></i>',
													);
		$act .= $money->build();
	}
	$ui->clear();
	if (debug('method')) $act.=print_o($rs,'$rs').str_replace(',','<br />',$rs->photos);
	$class=$rs->flag==_PROJECT_DRAFTREPORT?'draft':$rs->part;
	$class.=$lockReport?' locked':'';
	$tables->rows[]=array($profile,$act,"config"=>array('class'=>$class));

	$ret .= $tables->build();
	$ret.='</div><!--project-activity-->';


	return $ret;
}

?>