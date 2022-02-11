<?php
	/**
	 * Show activity
	 *
	 * @param Record Set $topic
	 * @return String
	 */
	function project_form_show_activity($self,$topic=NULL,$para=NULL,$body=NULL,$edit=false,$part=NULL) {
		$tpid=$topic->tpid;
		$orders=array('date'=>'date1','date1'=>'date1','trid'=>'trid','modify'=>'modified');
		$order=SG\getFirst($orders[$_REQUEST['o']],'tr.date1');
		$sort=SG\getFirst($_REQUEST['s'],'DESC');
		$items=(int)SG\getFirst($_REQUEST['i'],20);
		$orders=array('date'=>'from_date','title'=>'c.title');

		$isAdmin=user_access('administer projects');
		$isOwner=(project_model::is_owner_of($tpid) && $part=="owner")
							|| (project_model::is_trainer_of($tpid));
		$isEdit=false;

		if ($edit) {
			if ($topic->project->project_statuscode==1) {
				$isEdit=$isAdmin || $isOwner;
			} else {
				$isEdit=false;
			}
		}

		$isAccessFullExpense=user_access('access full expense') || $isOwner;
		$isAccessActivityExpense=user_access('access activity expense') || $isOwner;

		$stmt='SELECT
							tr.*, t.`title` projecttitle, c.`title`
						,	u.`username`, u.`name` poster, mu.`name` modifybyname
						FROM %project_tr% tr
							LEFT JOIN %topic% t ON t.`tpid`=tr.`tpid`
							LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
							LEFT JOIN %users% u ON u.`uid`=tr.`uid`
							LEFT JOIN %users% mu ON mu.`uid`=tr.`modifyby`
						WHERE tr.`formid`=:formid'.($tpid?' AND tr.`tpid`=:tpid':'').($part?' AND (tr.`part`="'.$part.'" '.($isEdit?'':' AND tr.`flag`>0').' ) ':' AND tr.`flag`>0').'
						GROUP BY tr.trid
						ORDER BY '.$order.' '.$sort.'
						'.($part?'':'LIMIT '.$items);
		$dbs=mydb::select($stmt,':tpid',$topic->tpid,':formid','activity',':uid',SG\getFirst(i()->uid,'func.NULL'));

		/*
		$stmt='SELECT tr.*, t.`title` projecttitle, c.`title`,
							u.`username`, u.`name` poster, mu.`name` modifybyname,
							mac.`detail1` mainactivity,
							mac.`text3` `presetOutputOutcome`,
							ac.`targetpreset`,
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
						WHERE tr.`formid`=:formid'.($tpid?' AND tr.`tpid`=:tpid':'').($part?' AND (tr.`part`="'.$part.'" '.($isEdit?'':' AND tr.`flag`>0').' ) ':' AND tr.`flag`>0').'
						GROUP BY tr.trid
						ORDER BY '.$order.' '.$sort.'
						'.($part?'':'LIMIT '.$items);
		$dbs=mydb::select($stmt,':tpid',$topic->tpid,':formid','activity',':uid',SG\getFirst(i()->uid,'func.NULL'));
		*/
		if ($isEdit) {
			$inlineAttr['class']='inline-edit';
			$inlineAttr['data-update-url']=url('project/edit/tr');
			if (post('debug')) $inlineAttr['data-debug']='yes';
		}
		$ret.='<div id="project-activity" '.sg_implode_attr($inlineAttr).'>'._NL;

		$tables = new Table();
		$tables->addClass('project-activity');
		$tables->thead=array('วันที่ - เวลา','กิจกรรม - รายละเอียด');
		$lockReportDate=project_model::get_lock_report_date($tpid);

		foreach ($dbs->items as $rs) {
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

			$ui = new Ui(NULL,'menu -vertical');
			if ($isEdit) {
				if ($isItemEdit) {
					$ui->add('<a href="'.url('paper/'.$rs->tpid.'/'.$part,array('act'=>'addreport','trid'=>$rs->trid)).'" title="แก้ไข'.($rs->flag==_PROJECT_DRAFTREPORT?'ร่าง':'').'บันทึกกิจกรรม">แก้ไข'.($rs->flag==0?'ร่าง':'').'บันทึกกิจกรรม</a>');
					$ui->add('<a class="-new" href="'.url('project/'.$tpid.'/info.expense/'.$rs->trid).'">ค่าใช้จ่าย/เอกสารการเงิน</a>');
					$ui->add('<a href="'.url('project/'.$tpid.'/info.join/'.$rs->calid).'">บันทึกผู้เข้าร่วมกิจกรรม</a>');
					$ui->add('<a class="sg-action" href="'.url('project/edit/removeactivity/'.$rs->trid).'" data-confirm="ยืนยันว่าจะลบบันทึกกิจกรรมนี้จริง?" data-rel="notify" data-removeparent="tr"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>');
					if (user_access('administer projects') || project_model::is_trainer_of($tpid)) {
						//$ui->add('<a href="'.url('project/edit/moveactivity/'.$rs->trid).'" class="inline-removeactivity" title="ยืนยันว่าจะย้ายรายงานนี้จริง?">ย้ายไปเป็นรายงาน'.($rs->part=='owner'?'พี่เลี้ยง':'ผู้รับผิดชอบ').'</a>');
					}
				}
				$profile.='<div class="clear"></div>'.$ui->build();
			}

			$ui = new Ui('span');
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
			if ($rs->gallery || $rs->rcvPhotos) {
				$photos = mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title`, f.`tagname` FROM %topic_files% f WHERE f.`tpid` = :tpid AND (f.`gallery` = :gallery OR (f.`refid` = :refid AND `tagname` = :tagname))', ':tpid', $tpid, ':refid', $rs->trid, ':tagname', 'project,rcv', ':gallery', SG\getFirst($rs->gallery,-1));

				foreach ($photos->items as $item) {
					$photoStrItem='';
					list($photoid,$photo)=explode('|',$item);
					if ($item->type=='photo') {
						//$ret.=print_o($item,'$item');
						if ($item->tagname=='project,rcv' && !$isAccessFullExpense) continue;
						$photo=model::get_photo_property($item->file);
						$photo_alt=$item->title;
						$photoStrItem .= '<li>';
						$photoStrItem.='<a class="sg-action" data-group="photo'.$rs->trid.'" href="'.$photo->_src.'" title="'.htmlspecialchars($photo_alt).'" data-rel="img">';
						$photoStrItem.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
						$photoStrItem.=' />';
						$photoStrItem.='</a>';
						$photomenu=array();
						$ui->clear();
						if ($isItemEdit) {
							$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -delete"></i></a>');
						}
						$photoStrItem.=$ui->build();
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
			/*
			if ($isItemEdit) {
				$act.='<div class="-no-print"><img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('paper/'.$tpid.'/owner').'&chs=160x160&choe=UTF-8&chld=L|2" alt="">';
				$act.='<p>อัพโหลดใบเสร็จรับเงินโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกด "ส่งภาพใบเสร็จรับเงิน" เลือกกล้องถ่ายรูป</p></div>';
			}
			*/
			if ($isItemEdit) {
				$act.='<form method="post" enctype="multipart/form-data" action="'.url('project/edit/tr',array('tpid'=>$tpid,'action'=>'photo','tr'=>$rs->trid)).'"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพถ่ายหรือไฟล์รายงาน</span><input type="file" name="photo[]" multiple="true" class="inline-upload -activityphoto" /></span></form>';
			}

			if ($rcvStr || $isItemEdit) $act.='<h3>ภาพเอกสารการเงิน</h3><ul id="projectrcv-photo-'.$rs->trid.'" class="photo -rcv">'.$rcvStr.'</ul>';

			if ($isItemEdit) {
				$act.='<div style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/'.$tpid.'/info/expense.photo.upload/'.$rs->trid).'" data-rel="#projectrcv-photo-'.$rs->trid.'" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพใบเสร็จรับเงิน</span><input type="file" name="photo[]" multiple="true" class="inline-upload -rev" /></span></form></div>'._NL;
			}

			$act.='</div><!--photo-->'._NL;

			$act.='<div class="project-activity-detail">'._NL;
			if (cfg('project.usemainact')) {
				$act.='<p><strong>กิจกรรมหลัก : '.$rs->mainactivity.'</strong></p>';
			}

			$extimateList=array(4=>'บรรลุผลมากกว่าเป้าหมาย',3=>'บรรลุผลตามเป้าหมาย',2=>'เกือบได้ตามเป้าหมาย',1=>'ได้น้อยกว่าเป้าหมายมาก');

			$act.='<h5>คุณภาพกิจกรรม : '.$extimateList[$rs->rate1].' ('.$rs->rate1.')</h5>';
			$act.='<h5>วัตถุประสงค์</h5>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'detail3','tr'=>$rs->trid,'ret'=>'html'),
									$rs->detail3, $isItemEdit, 'textarea')._NL;
			$act.='<h5>กิจกรรมตามแผน</h5>'
								.'<p>จำนวนกลุ่มเป้าหมายที่ตั้งไว้ '.$rs->targetpreset.' คน</p>'
								.'<p>รายละเอียดกลุ่มเป้าหมายที่ตั้งไว้</p>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text3','tr'=>$rs->trid,'ret'=>'html'),
									$rs->text3, $isItemEdit, 'textarea')._NL
								.'<p>รายละเอียดกิจกรรมตามแผน</p>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text1','tr'=>$rs->trid,'ret'=>'html','button'=>'yes'),
									$rs->text1, $isItemEdit, 'textarea')._NL;
			$act.='<h5>กิจกรรมที่ปฎิบัติจริง</h5>'
								.'<p>จำนวนคน/ผู้เข้าร่วมกิจกรรมจริง '.number_format($rs->num8).' คน</p>'._NL
								.'<p>รายละเอียดกลุ่มเป้าหมายที่เข้าร่วม</p>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text9','tr'=>$rs->trid,'ret'=>'html'), $rs->text9, $isItemEdit, 'textarea')._NL
								.'<p>รายละเอียดขั้นตอน กระบวนการ กิจกรรมปฎิบัติจริง</p>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text2','tr'=>$rs->trid,'ret'=>'html'),
									$rs->text2, $isItemEdit, 'textarea')._NL;
			$act.='<h5>ผลลัพธ์ที่ตั้งไว้</h5>'.sg_text2html($rs->presetOutputOutcome)._NL;
			$act.='<h5>ผลที่เกิดขึ้นจริง / ผลผลิต (Output) / ผลลัพธ์ (Outcome) / ผลสรุปที่สำคัญของกิจกรรม</h5>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text4','tr'=>$rs->trid,'ret'=>'html'),
									$rs->text4, $isItemEdit, 'textarea')._NL;
			$act.='<h5>ปัญหา/แนวทางแก้ไข</h5>'
								.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text5','tr'=>$rs->trid,'ret'=>'html'),
									$rs->text5, $isItemEdit, 'textarea')._NL;

			if (cfg('project.grantby')) {
				if ($rs->part=='owner') {
					$act.='<h5>ข้อเสนอแนะต่อ '.cfg('project.grantby').'</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text6','tr'=>$rs->trid,'ret'=>'html'),
										$rs->text6, $isItemEdit, 'textarea')._NL;
					$act.='<h5>ความต้องการสนับสนุนจากพี่เลี้ยงและ '.cfg('project.grantpass').'</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text7','tr'=>$rs->trid,'ret'=>'html'),
										$rs->text7, $isItemEdit, 'textarea')._NL;
				} else {
					$act.='<h5>ข้อเสนอแนะต่อ '.cfg('project.grantby').'</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text6','tr'=>$rs->trid,'ret'=>'html'),
										$rs->text6, $isItemEdit, 'textarea')._NL;
					$act.='<h5>ข้อเสนอแนะต่อพื้นที่</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text7','tr'=>$rs->trid,'ret'=>'html'),
										$rs->text7, $isItemEdit, 'textarea')._NL;
				}
				/*

				$act.='<h5>'.($rs->part=='owner'?'ข้อเสนอแนะต่อ '.cfg('project.grantby'):'ข้อเสนอแนะต่อพื้นที่').'</h5>'.view::inlineedit(array('fld'=>'text6','tr'=>$rs->trid,'ret'=>'html','load'=>'get','button'=>'yes'),sg_text2html($rs->text6),$isItemEdit,'textarea')._NL;
				$act.='<h5>'.($rs->part=='owner'?'ความต้องการสนับสนุนจากพี่เลี้ยงและ '.cfg('project.grantpass'):'ข้อเสนอแนะต่อ '.cfg('project.grantby')).'</h5>'.view::inlineedit(array('fld'=>'text7','tr'=>$rs->trid,'ret'=>'html','load'=>'get','button'=>'yes'),sg_text2html($rs->text7),$isItemEdit,'textarea')._NL;
				*/
				if ($rs->part=='owner') {
					$act.='<h5>คำแนะนำจากเจ้าหน้าที่ติดตามในพื้นที่</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text8','tr'=>$rs->trid,'ret'=>'html'),
										$rs->text8, $isItemEdit, 'textarea')._NL;
					$act.='<h5>ชื่อผู้ติดตามในพื้นที่ของ '.cfg('project.grantby').'</h5>'
									.view::inlineedit(array('group'=>'tr:activity', 'fld'=>'detail2','tr'=>$rs->trid,'button'=>'yes'),$rs->detail2,$isItemEdit,'text')._NL;
				}
			}


			$act.='</div><!--project-activity-detail-->'._NL;
			//$act.=print_o($rs,'$rs');

			if ($rs->part=='owner' && $isAccessActivityExpense) {
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
															$isAdmin ? '<a href="'.url('project/edit/lockmoney/'.$rs->trid).'" class="project-lockmoney"><i class="icon -'.($rs->flag==_PROJECT_LOCKREPORT?'lock':'unlock').'"></i></a>' : '<i class="icon -'.($rs->flag==_PROJECT_LOCKREPORT ? 'lock':'unlock').'"></i>',
															);
				$act .= $money->build();
			}
			$ui->clear();
			if ($isEdit) {
				$act.='<div class="clear"></div>'.$ui->build();
			}
			if (debug('method')) $act.=print_o($rs,'$rs').str_replace(',','<br />',$rs->photos);
			$class=$rs->flag==_PROJECT_DRAFTREPORT?'draft':$rs->part;
			$class.=$lockReport?' locked':'';
			$tables->rows[]=array($profile,$act,"config"=>array('class'=>$class));
		}
		$ret .= $tables->build();
		$ret.='</div><!--project-activity-->';

		if ($isEdit) {
			$ret.='<script type="text/javascript">
$(document).ready(function() {
	// Send new photo
	$("form").on("change", ".inline-upload.-activityphoto", function() {
		var $this=$(this);
		var $target=$(this).closest("div.photo").find("ul.photo.-activity");
		notify("<img src=\"/library/img/loading.gif\" alt=\"Uploading....\"/> กำลังอัพโหลดภาพถ่าย กรุณารอสักครู่....");
		$this.closest("form").ajaxForm({
			success: function(data) {
				$target.append("<li>"+data+"</li>");
				notify("ดำเนินการเสร็จแล้ว.",5000);
				$this.val("");
				$this.replaceWith($this.clone(true));
			}
		}).submit();
	});
});
</script>';
		}
		return $ret;
	}

?>