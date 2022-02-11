<?php
/**
* Org :: Action Information
* Created 2021-12-06
* Modify  2021-12-06
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.action
*/

import('widget:org.nav.php');

class OrgInfoAction extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		$dbs = mydb::select(
			'SELECT a.*, u.`username`, u.`name`
		FROM
			(SELECT
			  p.`tpid`, t.`orgid`, an.`trid` `actionId`, an.`uid`
			, an.`date1` `actionDate`
			, c.`title`
			, an.`text2` `actionReal`
			, an.`text4` `outputOutcomeReal`
			, an.`created`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_tr% an ON an.`tpid` = p.`tpid` AND an.`formid` = "activity" AND an.`part` = "owner"
				LEFT JOIN %calendar% c ON c.`id` = an.`calid`
			WHERE t.`orgid` = :orgid
			UNION
			SELECT
				NULL, an.`orgid`, an.`trid`, an.`uid`
			, an.`date1`
			, ac.`detail1`
			, an.`text2` `actionReal`
			, an.`text4` `outputOutcomeReal`
			, an.`created`
			FROM %project_tr% an
				LEFT JOIN %project_tr% ac ON ac.`trid` = an.`refid`
			WHERE an.`orgid` = :orgid AND an.`formid` = "activity" AND an.`part` = "org"
			ORDER BY `actionDate` DESC, `actionId` DESC
		) a
			LEFT JOIN %users% u USING(`uid`)
		',
			[':orgid' => $this->orgId]
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กิจกรรม : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]),
			'body' => new Card([
				'class' => 'project-knet -action',
				'children' => [
					$this->right->edit ? '<nav class="nav -sg-text-right -sg-paddingnorm"><a class="sg-action btn -primary" href="'.url('project/knet/'.$this->orgId.'/action.add').'" data-rel="box" data-width="640"><i class="icon -material">add_circle</i><span>บันทึกกิจกรรม</span></a></nav>' : NULL,
					new Widget([
						'thead' => ['weight -nowrap -hover-parent'=>''],
						'showHeader' => false,
						'children' => array_map(
							function($rs) {
								return new Card([
									'children' => [
										new ListTile([
											'crossAxisAlignment' => 'start',
											'class' => '-sg-paddingnorm',
											'title' => $rs->title,
											'leading' => '<img class="profile-photo" src="'.model::user_photo($rs->username).'" width="24" height="24" alt="" />',
											'subtitle' => '<b>'.$rs->name.'</b>'
											. '<span class="card-item -timestamp"> เมื่อ '.sg_date($rs->actionDate,'ว ดด ปป').' น. @'.sg_date($rs->created,'ว ดด ปป H:i').'</span>',
											'trailing' => $this->right->edit ? new DropBox([
												'children' => [
													'<a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/action.edit/'.$rs->actionId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไข</span></a>',
													empty($rs->tpid) ? '<a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/action.delete/'.$rs->actionId).'" data-title="ลบรายการบันทึก" data-rel="notify" data-confirm="ลบรายการบันทึกนี้ทิ้ง รวมทั้งภาพถ่าย'._NL._NL.'กรุณายืนยัน?" data-done="remove:parent .widget-card"><i class="icon -delete"></i>ลบทิ้ง</a>' : NULL,
												], // children
											]) : NULL, // DropBox
										]), // ListTile

										// Photo
										$this->showPhoto($rs->actionId),

										// Detail
										new Container([
											'class' => '-summary',
											'children' => [
												'<b>รายละเอียด:</b><div>'.sg_text2html($rs->actionReal).'</div>',
												'<b>ผลผลิต/ผลลัพธ์:</b><div>'.sg_text2html($rs->outputOutcomeReal).'</div>',
											], // children
										]), // Container
										// new DebugMsg($rs, '$rs'),
									],
								]);
							},
							$dbs->items
						),
					]), // Widget
				],
			]),
		]);
	}

	function showPhoto($actionId) {
		$stmt = 'SELECT
			f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
			FROM %topic_files% f
			WHERE f.`orgid` = :orgid AND f.`refid` = :refid AND f.`type` = "photo" AND f.`tagname` = "project,knet,action"
			';

		$photoDbs = mydb::select($stmt, ':orgid', $this->orgId, ':refid', $actionId);

		return new Container([
			'children' => [
				new Ui([
					'type' => 'album',
					'class' => '-justify-left',
					'id' => 'project-info-photo-card-'.$actionId,
					'forceBuild' => true,
					'children' => array_map(
						function($item) {
							$photoStrItem = '';
							$ui = new Ui('span');

							if ($item->type == 'photo') {
								$photo = model::get_photo_property($item->file);

								if ($this->right->edit) {
									$ui->add('<a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
								}

								$photo_alt = $item->title;

								$photoStrItem .= '<nav class="nav -icons -xhover -top-right -no-print" style="z-index: 1">'.$ui->build().'</nav>';

								$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
								$photoStrItem .= '<img class="photoitem photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
								$photoStrItem .= ' />';
								$photoStrItem .= '</a>';


								if ($this->right->edit) {
									$photoStrItem .= view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $item->fid, 'options' => '{class: "-fill", placeholder: "คำอธิบายภาพ"}', 'container' => '{class: "-fill -photodetail"}'), $item->title, $this->right->edit, 'text');
								} else {
									$photoStrItem .= '<span>'.$item->title.'</span>';
								}

								return $photoStrItem;//, '{class: "-hover-parent"}'];

							} else if ($item->type == 'doc') {
								$docStr = '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($item->title).'" target="_blank">';
								$docStr .= '<img class="photoitem" src="http://img.softganz.com/icon/pdf-icon.png" width="80%" alt="'.$item->title.'" />';
								$docStr .= '</a>';

								if ($this->right->edit) {
									$ui->add(' <a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/photo.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-title="ลบไฟล์" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
								}
								$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
								return $docStr; //, '{class: "-doc -hover-parent"}');
							} else if ($item->type == 'movie') {
								list($a,$youtubeId) = explode('?v=', $item->file);
								$docStr = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtubeId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div class="detail"><span>'.$item->title.'</span><span><a href="'.$item->file.'" target="_blank">View on YouTube</a></span></div>';

								if ($this->right->edit) {
									$ui->add(' <a class="sg-action" href="'.url('project/knet/'.$this->orgId.'/vdo.delete/'.$item->fid).'" title="ลบ Video" data-title="ลบ Video" data-confirm="ยืนยันว่าจะลบ Video นี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
								}
								$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
								return $docStr; //, '{class: "-vdo -hover-parent"}');
							}
						},
						$photoDbs->items
					),
				]), // Ui

				$this->right->edit ?
					'<nav class="nav -sg-text-center -no-print" style="padding: 32px 0;">'
						. '<form class="sg-upload -no-print" '
						. 'method="post" enctype="multipart/form-data" '
						. 'action="'.url('project/knet/'.$this->orgId.'/photo.upload/'.$actionId).'" '
						. 'data-rel="#project-info-photo-card-'.$actionId.'" data-append="li">'
						. '<input type="hidden" name="tagname" value="action" />'
						. '<span class="btn -primary btn-success fileinput-button"><i class="icon -material">add_a_photo</i>'
						. '<span>ส่งภาพถ่าย</span>'
						. '<input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" />'
						. '</span>'
						. '</form>'
						. '</nav>'
					: NULL,

				// new debugMsg($photoDbs, '$photoDbs'),
			],
		]);
	}
}
?>