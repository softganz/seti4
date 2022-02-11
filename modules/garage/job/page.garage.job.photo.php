<?php
/**
* Project fund paid document
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @return String
*/
function garage_job_photo($self, $jobInfo, $photoId = NULL) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'PROCESS ERROR');

	$shopInfo = R::Model('garage.get.shop');

	$stmt = 'SELECT
		f.*, j.`plate`
		FROM %topic_files% f
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE f.`tpid` = :tpid AND f.`fid` = :fid AND f.`type` = "photo"
		LIMIT 1';
	$photoRs = mydb::select($stmt, ':tpid', $jobId, ':fid', $photoId);


	$isEditable = i()->uid == $photoRs->uid || in_array($jobInfo->shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
	$isViewable = $jobInfo->is->viewable;

	if (!$isViewable) return message('error', 'Access Denied');

	$ret = '';

	new Toolbar( $self, 'ภาพถ่าย'.' - '.$jobInfo->oinfo->plate, 'job', $jobInfo);


	$ui = new Ui();
	if ($isEditable) {
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/photo.delete/'.$photoRs->fid).'" data-rel="notify" data-done="close | remove:#photo-'.$photoRs->fid.'" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพพร้อมทั้งคำบรรยาย?"><i class="icon -material">delete</i></a>');
	}
	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>'.$photoRs->plate.'</h3><nav class="nav">'.$ui->build().'</nav></header>';


	$photo = model::get_photo_property($photoRs->file);

	$inlineAttr = array();
	$inlineAttr['class'] = 'project-photo -sg-flex';
	if ($isEditable) {
		//$inlineAttr['class'] .= ' sg-inline-edit';
		//$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-photo" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<div class="photo-img">';
	$ret .= '<img src="'.$photo->_url.'" width="100%" />';
	$ret .= '</div>';

	/*
	$ret .= '<div class="photo-detail">';
	$ret .= '<h3>'
		. ($isEditable ? view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $photoRs->fid, 'options' => '{class: "-fill", placeholder: "ชื่อภาพ"}', 'container' => '{class: "-fill -photodetail"}'), $photoRs->title, $isEditable, 'text') : ($photoRs->title ? $photoRs->title : 'ไม่มีชื่อภาพ'))
		. '</h3>';

	if ($isEditable) {
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
	*/



	$ret .= '</div><!-- project-photo -->';

	$ret .= '<style type="text/css">
	.photo-img {flex: 1 0 65%; background-color: #000;}
	.photo-img>img {position: relative;}
	.photo-detail {flex: 1 0 30%;}
	</style>';


	//$ret .= print_o($photoRs, '$photoRs');

	return $ret;





	switch ($action) {
		case 'upload' :
			$data->tpid = $tpid;
			$data->prename = 'garage_job_'.$tpid.'_';
			$data->tagname = 'job';
			$data->title = $jobInfo->plate;
			$data->orgid = $jobInfo->shopid;
			$data->deleteurl = 'garage/job/photo/'.$tpid.'/delete/';
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);
			$ret .= $uploadResult['link'];
			//$ret.=print_o($uploadResult,'$uploadResult');
			break;

		case 'delete' :
			if ($trid && SG\confirm()) {
				$result = R::Model('photo.delete', $trid);
				//$ret.=$result['msg'];
				//$ret.=print_o($result,'$result');
			}
			break;

		default :
			$ret .= __garage_job_photo_list($tpid, $jobInfo);
			break;

	}

	return $ret;
}

function __garage_job_photo_list($tpid,$jobInfo) {
	$isEdit = $jobInfo->is->editable;


	$ret.='<div class="project-expense -rcvphoto">';

	$ret.='<div class="photocard -projectrcv">'._NL;

	if ($isEdit) {
		$ret.='<div class="noprint" style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('garage/job/photo/'.$tpid.'/upload').'" data-rel="#projectrcv-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>อัพโหลดภาพถ่ายรถ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL;
	}


	$stmt='SELECT * FROM %topic_files% f WHERE f.`tpid`=:tpid AND `tagname`="job" ORDER BY f.`fid` DESC';
	$dbs=mydb::select($stmt,':tpid',$tpid, ':trid',$trid);
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<ul id="projectrcv-photo" class="">'._NL;
	if ($dbs->items) {
		// Get photo from database
		$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`gallery`=:gallery AND `tagname`="project,paiddoc" ORDER BY `fid` DESC', ':gallery',$gallery);
		//$ret.=print_o($photos,'$photos');

		// Show photos
		foreach ($dbs->items as $item) {
			list($photoid,$photo)=explode('|',$item);
			if ($item->type=='photo') {
				$photo=model::get_photo_property($item->file);
				$photo_alt=$item->title;
				$ret .= '<li class="-hover-parent">';
				$ret.='<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$ret.='<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
				$ret.=' />';
				$ret.='</a>';
				$photomenu=array();
				$ui=new ui();
				if ($isEdit) {
					$ui->add('<a class="sg-action -no-print" href="'.url('garage/job/photo/'.$tpid.'/delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -delete"></i></a>');
				}
				$ret.=$ui->build('span','iconset -hover');
				/*
				if ($isEdit) {
					$ret.=view::inlineedit(array('group'=>'photo','fld'=>'title','tr'=>$item->fid),$item->title,$isEdit,'text');
				} else {
					$ret.='<span>'.$item->title.'</span>';
				}
				*/
				$ret .= '</li>'._NL;
			}
		}
	}
	$ret.='</ul>'._NL;
	$ret.='</div><!--photo-->'._NL;
	$ret.='</div>';
	$ret.='<style type="text/css">
	.photocard>ul {margin:0; padding:0; list-style-type:none;}
	.photocard>ul>li {height: 200px; margin:0 4px 4px 0; float: left; overflow:hidden; position: relative; display: inline-block;}
	.photocard .photoitem {height:100%;}

	.photocard.-projectrcv>ul {clear: both;}
	.photocard .ui-action {position: absolute; top:4px; right:4px;}
	.photocard .ui-action>a {background: #fff; border-radius: 50%; display: inline-block;}
	.photocard .ui-action>a:hover .icon {border-radius: 50%; background-color: red;}
	.fileinput-button {padding:20px;}
	.col-icons {width:48px; text-align:center;}

	@media (min-width:45em) { /* 720/16 = 44 tablet & iPad */
		.project-expense.-tran {width:50%; float: left; margin-right: 40px; }
		.project-expense.-rcvphoto {}
		.project-expense.-rcvphoto:after {content:""; display: block; clear: both;}
		.photocard.-projectrcv>ul {clear: none;}

	}
	</style>';
	return $ret;
}
?>