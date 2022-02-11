<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_photo($self, $projectInfo, $photoId) {
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isEditable = $projectInfo->info->isEdit;
	$isEdit = $isEditable && post('mode') == 'edit';

	$ret = '';

	$stmt = 'SELECT
		f.*
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND f.`fid` = :fid AND f.`type` = "photo" AND f.`tagname` = "project,info"
		LIMIT 1';
	$photoRs = mydb::select($stmt, ':tpid', $tpid, ':fid', $photoId);

	$ui = new Ui();
	if ($isEditable) {
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info.photo/'.$photoRs->fid, array('mode'=>'edit')).'" data-rel="replace:#project-photo"><i class="icon -material">edit</i></a>');
		$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$photoRs->fid).'" data-rel="notify" data-done="close | remove:#photo-'.$photoRs->fid.'" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพพร้อมทั้งคำบรรยาย?"><i class="icon -material">delete</i></a>');
	}
	$ret .= '<header class="header">'._HEADER_BACK.'<h3>'.$projectInfo->title.'</h3><nav class="nav">'.$ui->build().'</nav></header>';


	$photo = model::get_photo_property($photoRs->file);

	$inlineAttr = array();
	$inlineAttr['class'] = 'project-photo -sg-flex';
	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-photo" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<div class="photo-img">';
	$ret .= '<img src="'.$photo->_url.'" width="100%" />';
	$ret .= '</div>';

	$ret .= '<div class="photo-detail">';
	$ret .= '<h3>'
		. ($isEdit ? view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $photoRs->fid, 'options' => '{class: "-fill", placeholder: "ชื่อภาพ"}', 'container' => '{class: "-fill -photodetail"}'), $photoRs->title, $isEdit, 'text') : ($photoRs->title ? $photoRs->title : 'ไม่มีชื่อภาพ'))
		. '</h3>';

	if ($isEdit) {
		$ret .= view::inlineedit(
			array(
				'group'=>'photo',
				'fld' => 'description',
				'tr' => $photoRs->fid,
				'ret'=>'nl2br',
				'options' => '{placeholder: "ระบุรายละเอียด"}',
				'value' => trim($photoRs->description),
			),
			nl2br($photoRs->description),
			$isEdit,
			'textarea'
		);
	} else {
		$ret .= $photoRs->description ? $photoRs->description : 'ไม่มีรายละเอียด';
	}

	$ret .= '</div>';



	$ret .= '</div><!-- project-photo -->';

	$ret .= '<style type="text/css">
	.photo-img {flex: 1 0 65%; background-color: #000;}
	.photo-img>img {position: relative;}
	.photo-detail {flex: 1 0 30%;}
	</style>';

	//$ret .= print_o($photo, '$photo');
	//$ret .= print_o($photoRs, '$photoRs');
	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>