<?php
/**
* Project :: View Financeial Document Info
* Created 2020-06-07
* Modify  2020-06-07
*
* @param Object $self
* @param Object $fundInfo
* @param Int $tranId
* @return String
*
* @usage project/fund/$orgId/financial.view/$tranId
*/

$debug = true;

function project_fund_financial_view($self, $fundInfo, $tranId) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isEdit = $fundInfo->right->editFinancial;
	$isAccess = $fundInfo->right->accessFinancial;

	if (!$isAccess) return '<p class="notify">ขออภัย : สำหรับเจ้าหน้าที่'.$fundInfo->name.'เท่านั้น</p>';


	$refcode = mydb::select('SELECT `refcode` FROM %project_gl% WHERE `pglid`=:pglid LIMIT 1', ':pglid', $tranId)->refcode;
	$glTrans = R::Model('project.gl.tran.get', $refcode);

	$isClosed = $glTrans->refdate <= $fundInfo->finclosemonth;




	$menu = '';
	if ($isEdit) {
		$menu .= '<ul>';
		//$menu .= '<li><a class="-disabled" href="'.url('project/fund/'.$orgId.'/financial.view',array('act'=>'updoc','trid'=>$tranId)).'"><i class="icon -upload"></i><span>อัพโหลดไฟล์ภาพ</span></a></li>';
		$menu .= '<li><a class="sg-action'.($isClosed?' -disabled':'').'" href="'.url('project/fund/'.$orgId.'/info/financial.delete/'.$tranId).'" data-title="ลบใบรับเงิน" data-confirm="ต้องการลบใบรับเงิน พร้อมไฟล์ประกอบใบรับเงิน กรุณายืนยัน?" data-rel="notify" data-done="reload:'.url('project/fund/'.$orgId.'/financial').'"><i class="icon -delete"></i><span>ลบใบรับเงิน'.($isClosed?'(ปิดงวด)':'').'</span></a></li>';
		$menu .= '</ul>';
		$menu = sg_dropbox($menu);
	}


	$ret .= '<div class="-no-print" style="position:relative;margin:16px 0;"><ul class="iconset -sg-text-right" style="margin-right: 40px;"><li><a class="btn -primary" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print -white"></i><span>พิมพ์</span></a></li>';
	$ret .= '</ul>';
	$ret .= '<span style="position:absolute;right:10px; top:0;">'.$menu.'</span></div>';

	$ret .= '<div class="-forprint">';
	$ret .= '<h3>'.$fundInfo->name.'</h3>';
	$ret .= '<p><b>เลขที่อ้างอิง '.$refcode.'</b></p>';
	$ret .= '<p><b>วันที่ '.sg_date($glTrans->refdate,'ว ดดด ปปปป').'</b></p>';

	$tables = new Table();
	$tables->thead=array('รหัสบัญชี','รายการ','money rev'=>'เดบิท(บาท)','money expense'=>'เครดิต(บาท)','');
	foreach ($glTrans->items as $item) {
		$tables->rows[]=array(
			$item->glcode,
			$item->glname,
			$item->amount >= 0 ? number_format($item->amount,2) : '',
			$item->amount < 0 ? number_format(abs($item->amount),2) : '',
		);
	}

	$tables->tfoot[] = array('','',number_format($glTrans->totalDr,2),number_format($glTrans->totalCr,2));

	$ret .= $tables->build();

	$ret .= '<p>บันทึกโดย '.$glTrans->createName.' เมื่อ '.sg_date($glTrans->created,'ว ดดด ปปปป H:i').' น.</p>';
	if ($glTrans->modifyName) $ret .= '<p>แก้ไขโดย '.$glTrans->modifyName.' เมื่อ '.sg_date($glTrans->modified,'ว ดดด ปปปป H:i').' น.</p>';
	//$ret .=print_o($glTrans,'$glTrans');
	//$ret .=print_o($fundInfo,'$fundInfo');
	$ret .= '</div>';




	if ($isEdit) {
		$ret .= '<div class="container -no-print">';
		$ret .= '<div class="row">';
		$ret .= '<div id="upload" class="col -md-6" style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/fund/'.$orgId.'/upload',array('tagname'=>'projectfundrcv','refid'=>$tranId)).'" data-rel="#loapp-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลดใบเสร็จรับเงิน</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL;

		$ret .= '<div class="col -md-6">';
		$ret .= '<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('project/fund/'.$orgId.'/financial.view/'.$tranId).'&chs=180x180&choe=UTF-8&chld=L|2" alt="">';
		$ret .= '<p>อัพโหลดใบเสร็จรับเงินโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกดอัพโหลดใบเสร็จ เลือกกล้องถ่ายรูป</p>';
		$ret .= '</div>';
		$ret .= '</div><!-- row -->';


		$ret .= '<ul id="loapp-photo" class="photocard -loapp">'._NL;
		// Get photo from database
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`refid`=:refid AND `tagname`="projectfundrcv" ORDER BY `fid` DESC', ':refid',$tranId);
		//$ret .=print_o($photos,'$photos');

		// Show photos
		foreach ($photos->items as $rs) {
			if ($rs->type== 'photo') {
				$photo=model::get_photo_property($rs->file);
				$photo_alt= $rs->title;
				$ret .= '<li class="-hover-parent">';
				$ret .= '<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$ret .= ' />';
				$ret .= '</a>';
				$photomenu=array();
				$ui=new Ui('span','iconset -hover');
				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
				}
				$ret .= $ui->build();
				/*
				if ($isEdit) {
					$ret .=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$rs->fid),$rs->title,$isEdit,'text');
				} else {
					$ret .= '<span>'.$rs->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			} else {
				$uploadUrl=cfg('paper.upload.document.url').$rs->file;
				$ret .= '<li><a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a></li>';
			}
		}
		$ret .= '</ul><!-- loapp-photo -->';
		$ret .= '<br clear="all" />';
		$ret .= '</div><!-- -forprint -->';
	}

	//$ret .=print_o($dbs,'$dbs');
	$ret .= '<style type="text/css">
	.nav .sg-upload {display: block; float: left; height:21px; margin:0; }
	.nav .sg-upload .btn {margin:0; }
	.photocard {margin:0; padding:0; list-style-type:none;}
	.photocard>li {height:300px; margin:0 10px 10px 0; float:left; position;relative;}
	.photocard img {height:100%;}
	.photocard .iconset {right:10px; top:10px; z-index:1;}
	</style>';
	return $ret;
}
?>