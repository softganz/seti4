<?php
/**
 * Rander Public Monitor Item
 *
 * @param Record Set $rs
 * @return String
 */
function view_publicmon_card_render($rs, $showEdit = true) {
	static $no = 1;
	$isEdit = $showEdit && (user_access('administrator publicmons') || i()->uid == $rs->uid);


	$ret .= '<div class="header">'._NL;
	$ret .= '<span class="owner">'._NL;
	$ret .= '<a class="sg-action" href="'.url('publicmon/u/'.$rs->username).'" data-webview="'.$rs->name.'"><img class="owner-photo" src="'.model::user_photo($rs->username).'" width="40" height="40" alt="" /><span class="owner-name">'.$rs->name.'</span></a>';
	$ret .= '</span><!-- owner -->'._NL;

	$ret .= '<span class="timestamp"> เมื่อ '.sg_date($rs->timedata,'ว ดด ปป H:i').' น. @'.sg_date($rs->created,'ว ดด ปป H:i').' น.';
	if ($isEdit && $rs->appsrc) $ret.=' on '.$rs->appsrc;
	$ret .= '</span>'._NL;
	$ret .= '</div><!-- header -->'._NL;

	if ($isEdit) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('publicmon/app/visit/'.$rs->psnid.'/edit/'.$rs->seq).'" data-rel="#noteUnit-'.$rs->seq.' .summary"><i class="icon -edit"></i>แก้ไข</a>');
		$ui->add('<a class="sg-action" href="'.url('publicmon/edit/deletenote/'.$rs->seq).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="none" data-confirm="ลบรายการบันทึกนี้ทิ้ง รวมทั้งภาพถ่าย'._NL._NL.'กรุณายืนยัน?" data-removeparent=".card-item"><i class="icon -delete"></i>ลบบันทึกข้อความ</a>');
		$ret .= '<nav class="nav -card-main">'.sg_dropbox($ui->build(),'{class:"leftside -atright"}').'</nav>';
	}


	$ret .= '<span class="type -id-'.$rs->pubtype.'"><i class="icon -notification -gray"></i><span>'.$rs->pubtypename.'</span></span>';
	$ret .= '<div class="detail -sg-clearfix"><p>'
			. str_replace("\n",'<br />',$rs->detail)
			. '</p></div><!-- detail -->'._NL;

	$ret .= '<div class="photo-album">'._NL;
	$ret .= '<ul id="" class="ui-album">';
	if ($rs->photos) {
		foreach (explode(',',$rs->photos) as $photoItem) {
			list($fid,$photofile)=explode('|', $photoItem);
			if (!$fid) continue;
			$ret .= '<li class="-hover-parent">';
			$ret .= '<a class="sg-action" href="'.R::Model('publicmon.photo.url',$photofile).'" data-rel="img"><img src="'.R::Model('publicmon.photo.url',$photofile).'" /></a>';
			if ($isEdit) {
				$ui = new Ui('span','-hover');
				$ui->add('<a class="sg-action" href="'.url('publicmon/visit/'.$rs->psnid.'/deletephoto/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -delete"></i></a>');
				$ret .= '<nav class="nav iconset -no-print">'.$ui->build().'</nav>';
			}
			$ret .= '</li>'._NL;
		}
	}
	if ($isEdit) {
		$ret .= '<li>';
		$ret .= '<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('publicmon/???/'.$rs->psnid.'/uploadphoto/'.$rs->seq).'" data-rel="#noteUnit-'.$rs->seq.' .photo" data-before="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>'._NL;
		$ret .= '</li>'._NL;
	}
	$ret .= '</ul><!-- ui-album -->'._NL;
	$ret .= '</div><!-- photo-album -->'._NL;



	//$ret .= '<div class="nav -like">';

	$ui = new Ui();
	$ui->add('<a class="sg-action btn -link" href="'.url('publicmon/???/'.$rs->psnid.'/'.($isEdit?'edit':'view').'/'.$rs->seq).'" data-webview="สถานะ"><i class="icon -save -gray"></i><span>'.$rs->status.'</a>');
	$ui->add('<a class="sg-action btn -link" href="'.url('publicmon/???/'.$rs->psnid.'/'.($isEdit?'edit':'view').'/'.$rs->seq).'" data-webview="Comment"><i class="icon -chat -gray"></i><span>Comment</a>');
	$ui->add('<a class="sg-action btn -link" href="'.url('publicmon/???/'.$rs->psnid.'/'.($isEdit?'edit':'view').'/'.$rs->seq).'" data-webview="Share" ><i class="icon -share -gray"></i><span>Share</span></a>');
	$ret .= '<nav class="nav -card -card-like">'.$ui->build().'</nav><!-- nav -card -->';

	//$ret .= '</div><!-- footer -->'._NL;

	$ret .= '<div class="comment">';
	for ($i=1; $i<=2; $i++) {
		$ret .= '<div class="ui-item">';
		$ret .= '<span class="owner">'._NL;
		$ret .= '<a class="sg-action" href="'.url('publicmon/u/'.$rs->username).'" data-webview="'.$rs->name.'"><img class="owner-photo" src="'.model::user_photo($rs->username).'" width="40" height="40" alt="" /></a>';
		$ret .= '</span><!-- owner -->';

		$ret .= '<span class="message">';
		$ret .= '<a class="sg-action owner-name" href="'.url('publicmon/u/'.$rs->username).'" data-webview="'.$rs->name.'">'.$rs->name.'</a>';

		//$ret .= '<span class="timestamp"> เมื่อ '.sg_date($rs->timedata,'ว ดด ปป H:i').' น. @'.sg_date($rs->created,'ว ดด ปป H:i').' น.';
		//$ret .= '</span>'._NL;
		$ret .= '<span class="message-text">';
		$ret .= 'ได้ดำเนินการแจ้งให้งานช่างเข้าไปตรวจดูแล้วค่ะ<br />จะติดตามให้อีกที';
		$ret .= '</span><!-- message-text -->';
		$ret .= '<div class="message-footer">25 ก.ค. 61 15:30 น.</div>';
		$ret .= '</span><!-- message -->';
		$ret .= '</div>';
	}
	$ret .= '<div id="comment-box-???" class="comment-box"><img class="poster-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />&nbsp;<textarea class="form-textarea" placeholder="เขียนความคิดเห็น..."></textarea></div>';
	$ret .= '</div><!-- comment -->';


	return $ret;
}
?>