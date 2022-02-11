<?php
/**
 * Project TOR
 *
 * @param Object $self
 * @param Object $projectInfo
 * @return String
 */
function project_info_tor($self, $projectInfo) {
	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PARAMETER ERROR');

	$orgId = $projectInfo->orgid;

	R::View('project.toolbar',$self,$projectInfo->title, $projectInfo->submodule,$projectInfo,'{showPrint: true}');

	$ret .= R::View('project.statusbar', $projectInfo)->build();

	$torInfo = R::Model('project.tor.get',$tpid);

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$torInfo) {
		$ret .= '<p class="notify">ยังไม่มี TOR</p>';
		if ($isEdit) {
			$ret .= '<nav class="nav -page -sg-text-center"><a class="btn -primary" href="'.url('project/'.$tpid.'/info/tor.create').'"><i class="icon -adddoc -white"></i><span>สร้าง TOR</a></nav>';
		}
		return $ret;
	}


	$fundInfo = R::Model('project.fund.get', $orgId);

	$projectPeriod = project_model::get_period($tpid);

	$inlineAttr['class']='container project-tor-view -forprint';
	if ($isEdit) {
		$inlineAttr['class'].=' inline-edit';
		$inlineAttr['data-tpid']=$tpid;
		$inlineAttr['data-update-url']=url('project/edit/tr');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}

	/*
	$ui=new Ui(NULL,'ui-nav');
	$ui->add('<a class="btn -primary" href="javascript:window.print()"><i class="icon -print -white"></i> พิมพ์</a>');
	//$ui->add('<a class="sg-action btn" href="'.url('project/'.$tpid.'/info.tor.edit').'"><i class="icon -edit"></i><span>แก้ไข</span></a>');
	if ($isEdit) {
		//$ui->add('<a class="sg-action btn'.($torInfo->photos?' -disabled':'').'" href="'.url('project/'.$tpid.'/info/tor.remove').'" data-rel="notify" data-confirm="ต้องการลบบันทึกข้อตกลงนี้ กรุณายืนยัน?" data-callback="'.url('paper/'.$tpid).'"><i class="icon -delete"></i><span>ลบ</span></a>');
	}
	$ret.='<nav class="nav -page -no-print -sg-text-right">'.$ui->build().'</nav>';
	*/


//view::inlineedit(array('group'=>'tr:activity', 'fld'=>'text9','tr'=>$rs->trid,'ret'=>'html'), $rs->text9, $isItemEdit, 'textarea')._NL


	$ret.='<div '.sg_implode_attr($inlineAttr).'>';
	$ret.='<h3>บันทึกข้อตกลง</h3><h3>การขอรับเงินอุดหนุน</h3><h3>'.$projectInfo->info->orgName.'</h3>';


	$ret .= '<p class="-address">'
		. 'เลขที่ข้อตกลง '.view::inlineedit(array('group'=>'project','fld'=>'agrno','tr'=>$tpid,'value'=>$projectInfo->info->agrno), $projectInfo->info->agrno, $isEdit).'<br />'._NL
		. 'ที่ทำการ '.$projectInfo->info->orgName.'<br />'
		. $fundInfo->info->orgaddr.' '.$fundInfo->info->orgzip.'</p>'._NL;

	$ret.='<p class="-date">วันที่ '.view::inlineedit(array('group'=>'tr:tor','fld'=>'date1','tr'=>$torInfo->torId,'ret'=>'date:ว ดดด ปปปป','value'=>$torInfo->tordate?sg_date($torInfo->tordate,'d/m/Y'):''), $torInfo->tordate, $isEdit, 'datepicker').'<p>'._NL;

	$ret.='<p class="-indent1">บันทึกนี้ ทำขึ้นเพื่อเป็นข้อตกลงในการดำเนินงานโครงการ/กิจกรรมที่ได้รับเงินอุดหนุนจาก <b>'.$projectInfo->info->orgName.'</b> ระหว่าง <b>'.$projectInfo->info->orgnamedo.'</b> โดย <b>'.$projectInfo->info->prowner.'</b> ในฐานะเป็นผู้รับผิดชอบโครงการ <b>'.$projectInfo->title.'</b> เพื่อเป็นข้อตกลงในการดำเนินงานโครงการ/กิจกรรมที่ได้รับเงินอุดหนุนจาก <b>'.$projectInfo->info->orgName.'</b> ซึ่งต่อไปในบันทึกนี้เรียกว่า “ผู้รับผิดชอบโครงการหรือกิจกรรม” ฝ่ายหนึ่ง กับ <b>'.$projectInfo->info->orgName.'</b> โดย <b>'.$fundInfo->info->chaimanName.'</b> ในฐานะประธานคณะกรรมการ'.$projectInfo->info->orgName.' ซึ่งต่อไปในบันทึกเรียกว่า “ผู้สนับสนุนโครงการหรือกิจกรรม” อีกฝ่ายหนึ่ง</p>
	<p class="-indent1">ทั้งสองฝ่าย ได้ตกลงทำบันทึกข้อตกลงกัน ดังมีรายละเอียดต่อไปนี้</p>
	<p class="-indent2">ข้อ 1 ผู้รับผิดชอบโครงการหรือกิจกรรม ตกลงจะดำเนินการโครงการ/กิจกรรมในบันทึกนี้ ซึ่งต่อไปเรียกว่า โครงการ/กิจกรรมตามที่'.$projectInfo->info->orgName.' ได้ให้เงินอุดหนุนจำนวนทั้งสิ้น '.number_format($projectInfo->info->budget,2).' บาท ('.sg_money2bath($projectInfo->info->budget,2).') ให้เป็นไปตามวัตถุประสงค์ เป้าหมาย และแผนการดำเนินงานของโครงการ/กิจกรรมตามเอกสารแนบท้ายบันทึกนี้ ตลอดจนหลักเกณฑ์ เงื่อนไข วิธีการ และตามระเบียบของ'.$projectInfo->info->orgName.' และหนังสือสั่งการของสำนักงานหลักประกันสุขภาพแห่งชาติทุกประการ</p>
	<p class="-indent2">ข้อ 2 การจ่ายเงิน ผู้สนับสนุนโครงการหรือกิจกรรม จะจ่ายเงินให้กับผู้รับผิดชอบโครงการหรือกิจกรรมตามที่คณะกรรมการกำหนด โดยมีการจ่ายให้กับผู้รับผิดชอบโครงการหรือกิจกรรม ดังนี้</p>
	<p class="-indent2">ก. จ่ายงวดเดียวทั้งโครงการ/กิจกรรม เป็นจำนวนเงินทั้งสิ้น '.number_format($projectInfo->info->budget,2) .' บาท ('.sg_money2bath($projectInfo->info->budget,2).')</p>';

	//$ret .= print_o($projectPeriod, '$projectPeriod');

	$ret .= '<p class="-indent2">ข. จ่ายเป็นงวด ดังนี้</p>';

	if ($projectPeriod) {
		$periodNo = 1;
		foreach ($projectPeriod as $periodItem) {
			$periodName = count($projectPeriod) > 1 && $periodNo == count($projectPeriod) ? 'งวดสุดท้าย' : 'งวดที่ '.$periodNo;
			$budgetPercent = $periodItem->budget * 100 / $projectInfo->info->budget;
			$ret .= '<p class="-indent3">'.$periodName.' จ่ายให้ผู้รับผิดชอบโครงการหรือกิจกรรมร้อยละ '.number_format($budgetPercent,2).' เป็นเงิน '.number_format($periodItem->budget,2).' บาท ( '.sg_money2bath($periodItem->budget).' )</p>';
			++$periodNo;
		}
	} else {
	$ret .= '<p class="-indent3">งวดที่ 1 จ่ายให้ผู้รับผิดชอบโครงการหรือกิจกรรมร้อยละ......เป็นเงิน..................บาท (...............................................)</p>
	<p class="-indent3">งวดที่ 2 จ่ายให้ผู้รับผิดชอบโครงการหรือกิจกรรมร้อยละ......เป็นเงิน..................บาท (...............................................)</p>
	<p class="-indent3">งวดสุดท้าย จ่ายให้ผู้รับผิดชอบโครงการหรือกิจกรรมร้อยละ.....เป็นเงิน..............บาท (...............................................)</p>';
	}

	$ret .= '<p class="-indent2">กรณีผู้รับผิดชอบโครงการหรือกิจกรรมเป็นหน่วยงานราชการ หน่วยงานนั้นต้องออกใบเสร็จรับเงิน ของหน่วยงานให้กับกองทุนเพื่อเป็นหลักฐานในการรับเงิน กรณีผู้รับผิดชอบโครงการหรือกิจกรรมเป็นหน่วยงาน กลุ่ม องค์กรภาคเอกชน หรือภาคประชาชน ให้ผู้แทนหน่วยงาน กลุ่ม องค์กรภาคเอกชน หรือภาคประชาชนนั้น ลงนามในใบสำคัญรับเงินที่กองทุนจัดทำขึ้นจำนวน ๒ คน และให้แนบสำเนาบัตรประชาชนของผู้รับเงินแนบใบสำคัญรับเงิน</p>
	<p class="-indent2">ข้อ 3 ผู้รับผิดชอบโครงการหรือกิจกรรมต้องนำเงินที่ได้รับไปดำเนินการตามกิจกรรมต่างๆ ในโครงการหรือกิจกรรมที่คณะกรรมการอนุมัติไป หากผู้รับผิดชอบโครงการหรือกิจกรรมไม่ดำเนินการตามโครงการหรือกิจกรรมที่อนุมัติไป เว้นแต่การไม่ดำเนินการนั้นเกิดจากเหตุสุดวิสัย พ้นวิสัยหรือเกิดภัยพิบัติ ซึ่งได้เกิดจากการกระทำ ของผู้รับผิดชอบโครงการหรือกิจกรรม ผู้รับผิดชอบโครงการหรือกิจกรรมยินยอมรับผิดชำระเงินที่ได้รับหรือเบิกจ่าย ไปแล้ว รวมทั้งค่าเสียหายหรือค่าใช้จ่ายอื่นใดอันเกิดจากการดำเนินการหรือไม่ดำเนินการดังกล่าว ให้แก่ผู้สนับสนุนโครงการหรือกิจกรรมมิต้องบอกกล่าวหรือทวงถามเป็นหนังสือแต่อย่างใด</p>
	<p class="-indent1">หากผู้รับผิดชอบโครงการหรือกิจกรรม ไม่ชำระเงินที่ได้รับหรือเบิกจ่ายไปแล้ว รวมทั้งค่าเสียหายหรือค่าใช้จ่ายอื่นใดให้แก่ผู้สนับสนุนโครงการหรือกิจกรรม ผู้รับผิดชอบโครงการหรือกิจกรรมยินยอมเสียดอกเบี้ยตามอัตราที่กฎหมายกำหนดนับแต่วันที่ได้รับเงินไปจากผู้สนับสนุนโครงการหรือกิจกรรมรวมทั้งยินยอมให้ผู้สนับสนุนโครงการหรือกิจกรรมดำเนินคดีได้ตามกฎหมาย โดยมีเงื่อนไขดังนี้</p>
	<p class="-indent2">1. การดำเนินงานต้องเป็นไปตามกิจกรรมในโครงการหรือกิจกรรมที่อนุมัติ</p>
	<p class="-indent2">2. การใช้จ่ายเงินงบประมาณในการดำเนินโครงการหรือกิจกรรมจะต้องมีหลักฐานการเบิกจ่าย และให้ผู้ที่ได้รับมอบหมายเป็นผู้เก็บหลักฐานไว้เพื่อการตรวจสอบ</p>
	<p class="-indent2">3. ในกรณีที่มีการจัดซื้อ จัดจ้าง หรือจัดหาวัสดุครุภัณฑ์ให้ใช้ราคาตามบัญชีมาตรฐานครุภัณฑ์ของทางราชการโดยอนุโลม</p>
	<p class="-indent2">4. หากมีเงินเหลือจ่ายจากการดำเนินงาน ให้คืนเงินที่เหลือให้กองทุน เพื่อดำเนินการส่งเสริมและสนับสนุนแก่โครงการหรือกิจกรรมอื่นๆ ต่อไป</p>
	<p class="-indent2">5. ให้ผู้รับผิดชอบโครงการหรือกิจกรรม รายงานผลการดำเนินการให้กองทุนตามรูปแบบและระยะเวลาที่กำหนด รวมทั้งเอกสารอื่นๆ ที่คณะกรรมการกำหนด</p>
	<p class="-indent1">กองทุนขอสงวนสิทธิ์ที่จะดำเนินการและแก้ไขเปลี่ยนแปลงตามแนวทางปฏิบัติของกองทุน ถ้าผู้รับผิดชอบโครงการหรือกิจกรรมได้รับแจ้งเปลี่ยนแปลงแก้ไขให้ปฏิบัติตามที่กองทุนกำหนด</p>
	<p class="-indent1">บันทึกนี้ ทำขึ้นเป็นสองฉบับมีข้อความถูกต้องตรงกัน โดยมอบให้ผู้รับผิดชอบโครงการหรือกิจกรรมหนึ่งฉบับ ผู้สนับสนุนโครงการหรือกิจกรรมหนึ่งฉบับ</p>
	<p class="-indent1">ทั้งสองฝ่าย ได้อ่านและมีความเข้าใจในเนื้อความตามบันทึกนี้โดยตลอดแล้ว จึงลงลายมือชื่อไว้ เป็นหลักฐานต่อหน้าพยาน</p>
	<p class="-sign">........................................................<br /><br />('.$projectInfo->info->prowner.')<br /><br />'.$projectInfo->info->orgnamedo.'<br />ผู้รับผิดชอบโครงการหรือกิจกรรม</p>
	<p class="-sign">..........................................................<br /><br />('.$fundInfo->info->chaimanName.')<br /><br />ประธานกรรมการ'.$projectInfo->info->orgName.'<br />ผู้สนับสนุนโครงการหรือกิจกรรม</p>
	<div class="row -sign">
	<p class="col -md-6 -sign">(ลงชื่อ)......................................................พยาน<br /><br />('.view::inlineedit(array('group'=>'tr:tor','fld'=>'detail1','tr'=>$torInfo->torId,'value'=>$torInfo->payan1, 'options'=>'{class: "-sg-text-center", placeholder: "ชื่อ นามสกุล"}'), $torInfo->payan1, $isEdit).')<br /><br />'.view::inlineedit(array('group'=>'tr:tor','fld'=>'detail3','tr'=>$torInfo->torId,'value'=>$torInfo->payantype1, 'options'=>'{class: "-sg-text-center", placeholder: ""}'), SG\getFirst($torInfo->payantype1,'กรรมการ'), $isEdit).'</p>
	<p class="col -md-6 -sign">(ลงชื่อ)......................................................พยาน<br /><br />('.view::inlineedit(array('group'=>'tr:tor','fld'=>'detail2','tr'=>$torInfo->torId,'value'=>$torInfo->payan2, 'options'=>'{class: "-sg-text-center", placeholder: "ชื่อ นามสกุล"}'), $torInfo->payan2, $isEdit).')<br /><br />'.view::inlineedit(array('group'=>'tr:tor','fld'=>'detail4','tr'=>$torInfo->torId,'value'=>$torInfo->payantype2, 'options'=>'{class: "-sg-text-center", placeholder: ""}'), SG\getFirst($torInfo->payantype2,'กรรมการ'), $isEdit).'</p>
	</div>
	<br clear="all" />';
	$ret.='</div>';


	if ($isEdit) {
		$ret.='<div class="container -sg-flex -no-print">';
		$ret.='<div class="-sg-flex -co-2">';
		$ret.='<div id="upload" class="-sg-text-center" style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/'.$tpid.'/info/photo.upload').'" data-rel="#project-photo-tor" data-prepend="li"><input type="hidden" name="tagname" value="tor" /><input type="hidden" name="title" value="บันทึกข้อตกลง" /><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลดไฟล์</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL;

		$ret.='<div class="-sg-text-center">';
		$ret.='<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('project/'.$tpid.'/info.tor').'&chs=180x180&choe=UTF-8&chld=L|2" alt="">';
		$ret.='<p>อัพโหลดไฟล์โดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกดอัพโหลดไฟล์ เลือกกล้องถ่ายรูป</p>';
		$ret.='</div>';
		$ret.='</div><!-- row -->';
	}


	if ($projectInfo->RIGHT & _IS_ACCESS) {
		$ret.='<ul id="project-photo-tor" class="photocard -tor">'._NL;

		// Show photos
		foreach ($torInfo->photos as $rs) {
			list($photoid, $photo) = explode('|', $rs);
			if ($rs->type=='photo') {
				$photo = model::get_photo_property($rs->file);
				$photo_alt=$rs->title;
				$ret .= '<li class="-hover-parent">';
				$ret.='<a class="sg-a" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
				$ret.=' />';
				$ret.='</a>';
				$photomenu=array();

				$ui = new Ui('span');
				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$rs->fid).'" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a>');
				}
				$ret .= '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
				/*
				if ($isEdit) {
					$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$rs->fid),$rs->title,$isEdit,'text');
				} else {
					$ret.='<span>'.$rs->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$rs->file;
				$ret.='<li><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a></li>';
			}
		}
		$ret.='</ul><!-- loapp-photo -->';
		$ret.='<br clear="all" />';
		$ret.='</div><!-- -forprint -->';
	}

	//$ret.=print_o($torInfo,'$torInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');
	//$ret .= print_o($fundInfo,'$fundInfo');

	$ret.='<style type="text/css">
	.-indent1 {text-indent: 1em;}
	.-indent2 {text-indent: 2em;}
	.-indent3 {text-indent: 3em;}
	.project-tor-view h3 {text-align:center;}
	.-sign {margin:40px 0; text-align: center;}
	.-sign>span {min-width: 15em; display: inline-block;}
	.-address {text-align: right;}
	.-date {text-align: right;}
	</style>';

	return $ret;
}
?>