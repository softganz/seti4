<?php
/**
 * Rander Public Monitor Item
 *
 * @param Record Set $rs
 * @return String
 */
function view_publicmon_assets_render($rs, $showEdit = true) {
	static $no = 1;
	$isEdit = $showEdit && (user_access('administrator publicmons') || i()->uid == $rs->uid);


	$ret .= '<div class="header">'._NL;

	$ret .= '<span class="type -id-'.$rs->pubtype.'"><i class="icon -notification -gray"></i><span>'.$rs->pubtypename.'</span></span>';

	$ret .= '<h3><a class="sg-action" href="'.url('publicmon/assets/view/'.$rs->username).'" data-webview="'.$rs->name.'"><span class="owner-name">'.$rs->name.'</span></a></h3>';
	$ret .= $rs->address;

	$ret .= '<br /><span class="timestamp"> เมื่อ '.sg_date($rs->timedata,'ว ดด ปป H:i').' น. @'.sg_date($rs->created,'ว ดด ปป H:i').' น.';
	$ret .= '</span>'._NL;
	$ret .= '</div><!-- header -->'._NL;

	if ($isEdit) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('publicmon/app/visit/'.$rs->psnid.'/edit/'.$rs->seq).'" data-rel="#noteUnit-'.$rs->seq.' .summary"><i class="icon -edit"></i>แก้ไข</a>');
		$ui->add('<a class="sg-action" href="'.url('publicmon/edit/deletenote/'.$rs->seq).'" title="ลบทิ้ง" data-rel="none" data-confirm="ลบทิ้ง รวมทั้งภาพถ่าย'._NL._NL.'กรุณายืนยัน?" data-removeparent=".card-item"><i class="icon -delete"></i>ลบ</a>');
		$ret .= '<nav class="nav -card-main">'.sg_dropbox($ui->build(),'{class:"leftside -atright"}').'</nav>';
	}


	$ret .= '<div class="detail -sg-clearfix"><p>'
			. str_replace("\n",'<br />',$rs->detail)
			. '</p></div><!-- detail -->'._NL;



	return $ret;
}
?>