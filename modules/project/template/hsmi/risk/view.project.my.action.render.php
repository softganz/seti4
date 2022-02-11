<?php
function view_project_my_action_render($projectInfo=NULL,$actionInfo=NULL,$action=NULL) {
	$isItemEdit = $action != 'info' && $actionInfo->projectStatusCode == 1 && ($projectInfo->info->isRight || $actionInfo->uid == i()->uid);
	$isAccessExpense = $isItemEdit;

	$tpid = $actionInfo->tpid;
	$actionId = $actionInfo->actionId;

	$inlineAttr = array();
	$inlineAttr['class'] .= 'ui-item -action';
	if ($isItemEdit) {
		$inlineAttr['class'] .= ' inline-edit';
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}

	$ret .= '<a name="tr' . $actionId . '"></a>';
	$ret .= '<div id="project-action-' . $actionId . '" ' . sg_implode_attr($inlineAttr) . '>' . _NL;

	if ($isItemEdit) {
		$ui=new Ui();
		$ui->add('<a class="-disabled" href="javascript:void(0)">แก้ไข</a>');
		$ui->add('<a class="-disabled" href="javascript:void(0)">ล็อคบันทึกกิจกรรม</a>');
		$ui->add('<a class="-disabled" href="javascript:void(0)">ล็อคบันทึกการเงิน</a>');
		$ui->add('<sep>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/action.remove/'.$actionId, array('removeActivity' => 'yes')).'" data-confirm="ลบบันทึกกิจกรรม กรุณายืนยัน?" data-rel="notify" data-removeparent="div.ui-item.-action">ลบบันทึกกิจกรรม</a>');
		$ret.='<nav class="nav -card">'.sg_dropbox($ui->build(),'{icon:"dropbox -gray",class:"leftside -atright"}').'</nav>';
	}

	$ret.='<div class="owner">';
	$ret.='<span class="owner-photo"><img class="-photo" src="'.model::user_photo($actionInfo->username).'" width="32" height="32" alt="'.$actionInfo->ownerName.'" /></span>';
	$ret.='<span class="owner-name">';
	$ret.=($actionInfo->username?'<a class="sg-action" href="'.url('profile/'.$actionInfo->uid).'" data-rel="box">':'').'<b>'.$actionInfo->ownerName.($actionInfo->username?'</b></a>':'');
	$ret.=' post <a class="sg-action" href="'.url('project/my/action/'.$tpid.'/info/'.$actionId).'" data-rel="box">action</a> of <a class="sg-action" href="'.url('paper/'.$tpid).'" data-rel="box">project</a></span>';
	$ret.='<span class="created">@'.sg_date($actionInfo->created,'ว ดด ปป H:i').' น.</span>';
	$ret.='</div><!-- owner -->'._NL;

	if ($isItemEdit) {
		//$ret.='<div style="text-align:right;"><a class="sg-action" href="'.url().'" data-removeparent="carditem"><i class="icon -delete"></i></a></div>';
	}

	$ret.='<h3 class="title"><a href="'.url('project/my/action/'.$tpid).'">'.$actionInfo->projectTitle.'</a></h3>'._NL;
	$ret.='<h4 class="subtitle">'
				//.$actionInfo->activityTitle
				.view::inlineedit(
								array('group'=>'calendar', 'fld'=>'title','tr'=>$actionInfo->calid,'class'=>'-fill'),
								$actionInfo->title,
								$isItemEdit
							)
				.'</h4>'._NL;


	if (debug('method')) $ret.=$actionInfo->photos.print_o($actionInfo,'$actionInfo');



	$photoStr='';
	$rcvStr='';
	$docStr='';
	$photoCount=$rcvCount=0;
	if ($actionInfo->gallery || $actionInfo->rcvPhotos) {
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title`, f.`tagname` FROM %topic_files% f WHERE f.`gallery`=:gallery', ':gallery',$actionInfo->gallery);
		//$ret.=print_o($photos,'$photos');
		foreach ($photos->items as $item) {
			$photoStrItem='';
			if ($item->type=='photo') {
				//$ret.=print_o($item,'$item');
				if ($item->tagname=='project,rcv' && !$isAccessExpense) continue;
				$photo=model::get_photo_property($item->file);
				$photo_alt=$item->title;
				$photoStrItem .= '<li>';
				$photoStrItem.='<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$photoStrItem.=' />';
				$photoStrItem.='</a>';
				$photomenu=array();
				$ui=new Ui(NULL,'photo-menu');
				if ($isItemEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
				}
				$photoStrItem.=$ui->build('span');
				$photomenu=array();
				if ($item->tagname=='project,rcv') $photoStrItem.='<p>(เอกสารการเงิน)</p>';
				if ($isItemEdit) {
					$photoStrItem.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$item->fid,'class'=>'-fill'),$item->title,$isItemEdit,'text');
				} else {
					$photoStrItem.='<span>'.$item->title.'</span>';
				}
				$photoStrItem .= '</li>'._NL;
				if ($item->tagname=='project,rcv') {
					$rcvStr.=$photoStrItem;
					$rcvCount++;
				} else {
					$photoStr.=$photoStrItem;
					$photoCount++;
				}
			} else if ($item->type=='doc') {
				$docStr.='<li>';
				$docStr.='<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($photo_alt).'">';
				$docStr.=$item->title;
				$docStr.='</a>';
				$photomenu=array();
				$ui=new Ui();
				if ($isItemEdit) {
					$ui->add('[<a class="sg-action" href="'.url('project/edit/delphoto/'.$item->fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="this" data-removeparent="li">ลบไฟล์</a>]');
				}
				$docStr.=$ui->build();
				$docStr.='</li>';
			}
		}
	}




	$ret.='<div class="photo">'._NL;
	$ret.='<ul id="project-actphoto-'.$actionId.'" class="photoitem -count'.($photoCount>0 && $photoCount<5?$photoCount:5).' -action">'._NL;
	$ret.=$photoStr;
	$ret.='</ul>'._NL;

	if ($isItemEdit) {
		$ret.='<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/edit/tr',array('tpid'=>$tpid,'action'=>'photo','tr'=>$actionId)).'" data-rel="#project-actphoto-'.$actionId.'" data-append="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพถ่ายหรือไฟล์รายงาน</span><input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" /></span></form>'._NL;
	}






	if ($docStr) $ret.='<h3>ไฟล์ประกอบกิจกรรม</h3><ul class="doc">'.$docStr.'</ul>';




	$ret.='</div><!--photo-->'._NL;











	$ret.='<div class="summary">'._NL
				//.sg_text2html($actionInfo->real_do)._NL
				.view::inlineedit(
						array('group'=>'tr:activity:owner', 'fld'=>'text2','tr'=>$actionId,'class'=>'-fill','ret'=>'html','placeholder'=>'ระบุรายละเอียดกิจกรรมที่ดำเนินการ'),
						$actionInfo->actionReal,
						$isItemEdit,
						'textarea'
					)
				.'</div>'._NL;







	$ret.='<div class="photo">'._NL;
	if ($rcvStr || $isItemEdit) $ret.='<h4>ภาพเอกสารการเงิน</h4>'._NL.'<ul id="project-rcvphoto-'.$actionId.'" class="photoitem -count-all -rcv">'.$rcvStr.'</ul>'._NL;

	if ($isItemEdit) {
		$ret .= '<div style="margin:20px 0;">'._NL
			. '<form class="sg-upload -no-print" '
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
			. '</form>'._NL
			. '</div>'._NL;
	}
	$ret.='</div><!-- photo -->';

	$tables = new Table();
	$tables->addClass('project-money-send -row');
	$tables->thead=array('ประเภทรายจ่าย','จำนวนเงิน');
	$tables->rows[]=array(
										'ค่าตอบแทน',
										number_format($actionInfo->exp_meed,2)
									);
	$tables->rows[]=array(
										'ค่าวัสดุ',
										number_format($actionInfo->exp_material,2)
									);
	$tables->rows[]=array(
										'ค่าเดินทาง',
										number_format($actionInfo->exp_travel,2)
									);
	$tables->rows[]=array(
										'ค่าใช้สอย',
										number_format($actionInfo->exp_supply,2)
									);
	$tables->rows[]=array(
										'ค่าสาธารณูปโภค',
										number_format($actionInfo->exp_utilities,2)
									);
	$tables->rows[]=array(
										'อื่น ๆ',
										number_format($actionInfo->exp_other,2)
									);
	$tables->tfoot[]=array(
										'รวมรายจ่าย',
										number_format($actionInfo->exp_total,2)
									);

	$ret.=$tables->build();

	$tables = new Table();
	$tables->addClass('project-money-send -col');
	$tables->caption='รายงานการใช้เงิน';
	$tables->thead='<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th><th rowspan="2">สถานะ</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าวัสดุ</th><th>ค่าเดินทาง</th><th>ค่าใช้สอย</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';
	$tables->rows[]=array(
										number_format($actionInfo->exp_meed,2),
										number_format($actionInfo->exp_material,2),
										number_format($actionInfo->exp_travel,2),
										number_format($actionInfo->exp_supply,2),
										number_format($actionInfo->exp_utilities,2),
										number_format($actionInfo->exp_other,2),
										number_format($actionInfo->exp_total,2),
										$isAdmin ? '<a href="'.url('project/edit/lockmoney/'.$actionId).'" class="project-lockmoney"><i class="icon -'.($actionInfo->flag==_PROJECT_LOCKREPORT?'lock':'unlock').' -gray"></i></a>' : '<i class="icon -'.($actionInfo->flag==_PROJECT_LOCKREPORT ? 'lock':'unlock').' -gray"></i>',
									);

	$ret.=$tables->build();

	$ret.='<div class="timestamp">เมื่อวันที่ '
				//.$actionInfo->action_date.sg_date($actionInfo->action_date,'ว ดด ปปปป')
				.view::inlineedit(
						array('group'=>'tr:activity:owner', 'fld'=>'date1','tr'=>$actionId,'class'=>'-inline','ret'=>'date:ว ดด ปปปป'),
						$actionInfo->actionDate,
						$isItemEdit,
						'datepicker'
					)
				.'</div>';

	$ret.='<div class="status"><span>'.$actionInfo->activitys.' Activitys '.$actionInfo->view.' Views</span></div>'._NL;
	$ret.='<div class="action"><ul><li><a class="-disabled" href="javascript:void(0)">Like</a></li><li><a class="-disabled" href="javascript:void(0)">Comment</a></li><li><a class="-disabled" href="javascript:void(0)">Share</a></li></ul></div>'._NL;

	//$ret.=print_o($actionInfo,'$actionInfo');

	$ret.='</div><!-- carditem -->'._NL;

	return $ret;
}
?>