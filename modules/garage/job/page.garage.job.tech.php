<?php
/**
* Garage Job Technician for take photo
* Created 2019-11-25
* Modify  2019-11-25
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_tech($self, $jobInfo, $tranId = NULL) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'PROCESS ERROR');

	new Toolbar($self,'ใบสั่งงาน - '.$jobInfo->plate,'job',$jobInfo);

	$isEditable = in_array($jobInfo->shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING','FOREMAN'));
	$isViewable = $jobInfo->is->viewable;

	if (!$isViewable) return message('error', 'Access Denied');

	$stmt = 'SELECT * FROM %garage_do% WHERE `tpid` = :tpid AND `uid` = :uid LIMIT 1';
	$doTypeInfo = mydb::select($stmt, ':tpid', $jobId, ':uid', i()->uid);

	$myDoType = $doTypeInfo->dotype;


	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	if ($tranId) {
		$headerNav->add('<a href="'.url('garage/job/'.$jobId.'/tech').'"><i class="icon -material">find_in_page</i></a>');
		$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$jobInfo->plate.'</h3>'.$headerNav->build().'</header>';
	} else {
		if ($myDoType && $doTypeInfo->status == "OPEN") {
			$headerNav->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/info/assign.leave/'.i()->uid).'" data-rel="notify" data-done="reload" data-title="ออกจากจ็อบ" data-confirm="ต้องการออกจากการเป็นช่างของจ็อบ กรุณายืนยัน?"><i class="icon -material">undo</i><span>ออกจากจ็อบ</span></a>');
		}
		if ($headerNav->count()) {
			$ret = '<header class="header -box"><h3></h3>'.$headerNav->build().'</header>';
		}
	}

	//$ret .= '@'.date('H:i:s');

	$selectOption = array();

	$cardUi = new Ui('div', 'ui-card garage-job-tech');

	foreach ($jobInfo->command as $rs) {
		if ($rs->done < 0 ) continue;
		if ($tranId && $tranId != $rs->jobtrid) continue;

		$selectOption['สั่งซ่อม'][$rs->jobtrid] = $rs->description;


		$cardStr = '<div class="header"><h3>'.$rs->description.'</h3>'
			.__garageTechnicial('ช่างพื้น,ช่างพ่นสี',$jobInfo)->build()
			. '</div>';

		$cardStr .= '<div class="do-photo -photo1"><h5>ภาพเคาะ-ดึง</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo1'], 'photo1', $isEditable)->build(true)
			. ($myDoType == 'ช่างเคาะ' || $isEditable ? __cardNav('ภาพเคาะ-ดึง', 'photo1', $rs)->build() : '')
			. '</div>';


		$cardStr .= '<div class="do-photo -photo2"><h5>ภาพโป๊ว</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo2'], 'photo2', $isEditable)->build(true)
			. ($myDoType == 'ช่างพื้น' || $isEditable ? __cardNav('ภาพโป๊ว', 'photo2', $rs)->build() : '')
			. '</div>';

		$cardStr .= '<div class="do-photo -photo3"><h5>ภาพพื้น</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo3'], 'photo3', $isEditable)->build(true)
			. ($myDoType == 'ช่างพื้น' || $isEditable ? __cardNav('ภาพพื้น', 'photo3', $rs)->build() : '')
			. '</div>';

		$cardStr .= '<div class="do-photo -photo4"><h5>ภาพพ่นสี</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo4'], 'photo4', $isEditable)->build(true)
			. ($myDoType == 'ช่างพ่นสี' || $isEditable ? __cardNav('ภาพพ่นสี', 'photo4', $rs)->build() : '')
			. '</div>';

		$cardUi->add($cardStr, '{id: "job-photo-'.$rs->jobtrid.'", class: "-sg-flex"}');
	}

	foreach ($jobInfo->part as $rs) {
		if ($rs->done < 0 ) continue;
		if ($tranId && $tranId != $rs->jobtrid) continue;

		$selectOption['อะไหล่'][$rs->jobtrid] = $rs->description;

		$cardStr = '<div class="header"><h3>'.$rs->description.'</h3>'
			. __garageTechnicial('ช่างเคาะ,ช่างประกอบ',$jobInfo)->build()
			. '</div>';

		$cardStr .= '<div class="do-photo -photo5"><h5>ภาพคู่ซาก</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo5'], 'photo5', $isEditable)->build(true)
			. ($myDoType == 'ช่างเคาะ' || $isEditable ? __cardNav('ภาพคู่ซาก', 'photo5', $rs)->build() : '')
			. '</div>';

		$cardStr .= '<div class="do-photo -photo6"><h5>ภาพคู่ซาก</h5>'
			. __photoAlbum($rs, $jobInfo->photos->items['garage,photo6'], 'photo6', $isEditable)->build(true)
			. ($myDoType == 'ช่างประกอบ' || $isEditable ? __cardNav('ภาพคู่ซาก', 'photo6', $rs)->build() : '')
			. '</div>';

		$cardUi->add($cardStr, '{id: "job-photo-'.$rs->jobtrid.'", class: "-sg-flex"}');
	}

	if ($selectOption) {
		$form = new Form(NULL, url('garage/job/'.$jobId.'/tech'), 'techForm', 'sg-form');
		$form->addData('rel', 'replace:#garage-job-tech');
		//$form->addConfig('method', 'GET');
		$form->addField(
			'trid',
			array(
				'type' => 'select',
				'class' => '-fill',
				'options' => array('' => '== ทุกรายการ ==') + $selectOption,
				'value' => $tranId,
				'attr' => array('onchange' => 'changeUrl($(this));'),
				)
		);
		$form->addField('go', array('type'=>'button','value'=>'GO','class'=>'-hidden'));

		$self->theme->navbar = $form->build();
	}

	$ret .= '<section id="garage-job-tech" >';

	//$ret .= 'TranId = '.$tranId;

	$ret .= $cardUi->build();


	$ret.='<div class="remark"><b>หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม</b><br />'.nl2br($jobInfo->commandremark).'</div>';

	$ret .= '</div><!-- garage-job-tech -->';

	//$ret .= print_o($doTypeInfo, '$doTypeInfo');
	//$ret .= print_o($jobInfo, '$jobInfo');

	$ret .= '</section>';

	head('<script type="text/javascript">
	var formAction
	$(document).ready(function() {
		formAction = $("#techForm").attr("action")
	})
	function changeUrl($this) {
		var $form = $this.closest("form")
		console.log(formAction+"/"+$this.val())
		$form.attr("action", formAction+"/"+$this.val())
		$form.submit();
		return false;
	}
	</script>'
	);
	return $ret;
}

function __photoAlbum($rs, $photoList, $tagname, $isEditable) {
	$photoAlbumUi = new Ui(NULL,'ui-album -'.$tagname.' -justify-left');
	foreach ($photoList as $photoItem) {
		if ($rs->jobtrid != $photoItem->refid) continue;
	
		$isEditItem = $isEditable || $photoItem->uid == i()->uid;

		$photoInfo = model::get_photo_property($photoItem->file);

		$photoNavUi = new Ui('span');
		if ($isEditItem) {
			$photoNavUi->add('<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/info/photo.delete/'.$photoItem->fid).'" data-rel="notify" data-done="remove:parent .ui-album>.ui-item" data-title="ลบภาพ" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$photo_alt = $photoItem->title;
		$photoAlbumUi->add(
			'<a class="sg-action" data-group="photo-'.$photoItem->jobtrid.'" href="'.url('garage/job/'.$photoItem->tpid.'/photo/'.$photoItem->fid).'" data-rel="box" title="'.htmlspecialchars($photo_alt).'">'
			. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />'
			. '</a>'
			. '<span class="timestamp">@'.$photoItem->timestamp.'</span>'
			. '<nav class="nav -icons -hover">'.$photoNavUi->build().'</nav>',
			'{id: "photo-'.$photoItem->fid.'", class: "-hover-parent"}'
		);
	}
	return $photoAlbumUi;
}



function __cardNav($text, $tagname, $rs) {
	$cardNav = new Ui();
	$cardNav->addConfig('nav', '{class: "nav -card"}');
	$cardNav->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('garage/job/'.$rs->tpid.'/info/photo.upload/'.$rs->jobtrid).'" data-rel="#job-photo-'.$rs->jobtrid.' .ui-album.-'.$tagname.'" data-append="li"><input type="hidden" name="tagname" value="'.$tagname.'" /><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>ถ่าย'.$text.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
	return $cardNav;
}

function __garageTechnicial($technicianType, $jobInfo) {
	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -header"}');
	foreach (explode(',',$technicianType) as $item) {
		foreach ($jobInfo->do[$item] as $rs) {
			$ui->add(
				'<a class="sg-action" href="'.url('garage/profile/'.$rs->uid).'" data-rel="box" data-width="320" height="320" data-webview="'.htmlspecialchars($rs->name).'"><img src="'.model::user_photo($rs->username).'" width="100%" height="100%" title="'.htmlspecialchars($rs->name).' - '.$item.'" /></a>',
				'{class: "profile-photo"}'
			);
		}
	}
	return $ui;
}
?>