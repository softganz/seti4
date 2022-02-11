<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_do_photo($self) {
	$stmt = 'SELECT
		*, j.`plate`
		FROM %topic_files% f
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE f.`tagname` LIKE "garage,photo%"
		ORDER BY f.`fid` DESC
		LIMIT 100';

	$dbs = mydb::select($stmt);

	new Toolbar($self,'ภาพถ่าย '.$dbs->count().' ภาพ','do');

	$photoAlbumUi = new Ui(NULL,'ui-album -'.$tagname.' -justify-left');
	foreach ($dbs->items as $photoItem) {
	
		$isEditItem = $isEditable || $photoItem->uid == i()->uid;

		$photoInfo=model::get_photo_property($photoItem->file);

		$photoNavUi = new Ui('span');
		if ($isEditItem) {
			//$photoNavUi->add('<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/info/photo.delete/'.$photoItem->fid).'" data-rel="notify" data-done="remove:parent .ui-album>.ui-item" data-title="ลบภาพ" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$photo_alt = $photoItem->title;
		$photoAlbumUi->add(
			'<a class="sg-action" href="'.url('garage/job/'.$photoItem->tpid.'/tech/'.$photoItem->refid).'" data-rel="box" data-width="640" title="'.htmlspecialchars($photo_alt).'">'
			. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />'
			. '</a>'
			. '<span style="position: absolute; top: 4px; left: 4px; background-color: #fff; opacity: 0.7; border-radius: 4px;">@'.sg_date($photoItem->timestamp, 'd/m/ปป H:i').'</span>'
			. '<span style="position: absolute; bottom: 0; left: 0; width: 100%; text-align: center; background-color: #fff; opacity: 0.7;">'.$photoItem->plate.'('.$photoItem->jobno.')'.'</span>'
			. '<nav class="nav -icons -hover">'.$photoNavUi->build().'</nav>',
			'{class: "-hover-parent"}'
		);
	}

	$ret .= $photoAlbumUi->build();

	//$ret .= print_o($dbs,'$dbs');

	return $ret;
}
?>