<?php
/**
* iMed Widget : Render Visit Unit
* Created 2021-08-20
* Modify  2021-08-20
*
* @param Object $actionInfo
* @param Object $options
* @return Widget
*
* @usage import('model:imed.visit.render')
* @uasge new ImedVisitRenderWidget($actionInfo, $options)
*/

$debug = true;

class ProjectActionRenderWidget extends Widget {
	var $actionId;
	var $options;
	var $right;
	var $actionInfo;

	function __construct($actionInfo, $options = '{}') {
		$this->actionId = SG\getFirst($actionInfo->actionId);
		$this->projectId = SG\getFirst($actionInfo->projectId);
		$this->actionInfo = $actionInfo;
		$this->options = $options;
		$this->right = (Object) [
			'admin' => is_admin('project'),
			'owner' => false,
		];
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, refApp: null}';
		$options = SG\json_decode($this->options, $defaults);
		$debug = $options->debug;

		$actionInfo = $this->actionInfo;

		// debugMsg($this,'$this');
		// debugMsg($options, '$options');

		$this->right->owner = i()->uid == $this->actionInfo->uid;
		$this->right->edit = $options->showEdit && (is_admin('project') || i()->uid == $actionInfo->uid);

		$posterUrl = '<a class="sg-action" href="'.url('project/app/activity/', ['u' => $actionInfo->uid]).'" >';
		// $posterLink = '<a class="sg-action" href="'.$posterUrl.'" data-webview="'.$actionInfo->ownerName.'">';

		// switch ($options->refApp) {
		// 	case 'app':
		// 		$patientUrl = '<a class="sg-action" href="'.url('imed/app/'.$psnId).'" data-webview="'.$actionInfo->patient_name.'">';
		// 		break;

		// 	case 'psyc':
		// 		$patientUrl = '<a class="sg-action" href="'.url('imed/psyc/'.$psnId).'" data-webview="'.$actionInfo->patient_name.'">';
		// 		break;

		// 	default:
		// 		$patientUrl = '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/view').'" data-rel="box" data-width="480" data-max-height="80%" x-role="patient" data-pid="'.$psnId.'">';
		// 		break;
		// }

		$photoAlbum = new Ui([
			'type' => 'album',
			'class' => '-justify-left',
			'forceBuild' => true,
		]);

		if ($actionInfo->photos) {
			// foreach (explode(',',$actionInfo->photos) as $photoItem) {
			// 	list($photoFileID,$photoFileName) = explode('|', trim($photoItem));
			// 	if (!$photoFileID) continue;

			// 	$photoInfo = imed_model::upload_photo($photoFileName);

			// 	$Ui = new Ui('span');
			// 	$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
			// 	if ($this->right->edit) {
			// 		$Ui->add('<a class="sg-action -no-print" href="'.url('imed/api/visit/'.$psnId.'/photo.delete/'.$seqId, array('f'=>$photoFileID)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-before="remove:parent li"><i class="icon -material">cancel</i></a>');
			// 	}

			// 	$photoAlbum->add(
			// 		'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
			// 		. $Ui->build(),
			// 		array(
			// 			'class' => 'sg-action -hover-parent',
			// 			'href' => $photoInfo->_url,
			// 			'data-rel' => 'img',
			// 			'data-group' => 'imed-'.$seqId,
			// 			'onclick' => '',
			// 		)
			// 	);
			// }
		}

		$cameraStr = R()->appAgent ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

		return new Widget([
			'children' => [
				new ListTile([
					'crossAxisAlignment' => 'start',
					'title' => $posterUrl.$actionInfo->ownerName.'</a>',
					'subtitle' => '<span class="-visit-patient">'
						. ($actionInfo->service ? ' {tr:'.$actionInfo->service.'}' : '')
						. ($psnId ? ' '.$patientUrl.$actionInfo->prename.''.$actionInfo->patient_name.'</a>':'')
						.'</span><!-- -visit-patient -->'
						. '<span class="timestamp"> เมื่อ '
						. ($actionInfo->timedata == $actionInfo->created ? sg_date($actionInfo->timedata,'ว ดด ปปปป H:i').' น.' : sg_date($actionInfo->timedata,'ว ดด ปปปป')). ' '
						. ($this->right->admin ? '@'.sg_date($actionInfo->created,'H:i').' on '.$actionInfo->appagent : '')
						. '</span><!-- timestamp -->',
					'leading' => $posterUrl.'<img class="profile-photo" src="'.model::user_photo($actionInfo->username).'" width="32" height="32" alt="" /></a>',
					'trailing' => new DropBox([
						'children' => [
							$this->right->edit ? '<a class="sg-action" href="'.url('project/app/action/form/'.$this->projectId.'/'.$this->actionId).'" data-rel="box" data-width="full"><i class="icon -material">edit</i><span>แก้ไขรายละเอียด</span></a>' : NULL,
							$this->right->edit ? '<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/action.remove/'.$this->actionId).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="notify" data-title="ลบรายการบันทึก" data-confirm="ลบรายการบันทึกนี้ทิ้ง กรุณายืนยัน?" data-done="remove:parent .widget-card"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>' : '',
						], // children
					]), // DropBox
				]),

				// Detail
				new Column([
					'class' => 'detail',
					'children' => [
						'<h5><a href="'.url('project/'.$actionInfo->projectId).'">'.$actionInfo->title.'</a> @<a href="'.url('project/'.$actionInfo->projectId).'">'.$actionInfo->projectTitle.'</a>'.($actionInfo->parentTitle ? '/<a href="'.url('project/'.$actionInfo->parentId).'">'.$actionInfo->parentTitle.'</a>' : '').'</h5>',
						nl2br($actionInfo->actionReal),
						'ผลลัพท์: '.nl2br($actionInfo->outputOutcomeReal),
					], // children
				]), // Container

				$this->_photoAlbum(),

				$actionInfo->needItems ? $needWidget : NULL,

				$infoBar ? '<div id="vitalsign-'.$seqId.'"class="-vitalsign">'._NL.$infoBar.'</div><!-- vitalsign-detail -->'._NL : NULL,

				// Bottom Navigator
				// $psnId && $seqId && $this->right->edit ? new Row([
				// 	'tagName' => 'nav',
				// 	'class' => 'nav -card',
				// 	'mainAxisAlignment' => 'spacearound',
				// 	'crossAxisAlignment' => 'center',
				// 	'children' => [
				// 		'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('imed/api/visit/'.$psnId.'/photo.upload/'.$seqId).'" data-rel="#imed-visit-'.$seqId.' .ui-album" data-append="li" data-class="ui-item sg-action -hover-parent"><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>'.$cameraStr.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>',
				// 		'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.need/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full" data-options=\'{"class": "-expand"}\'><i class="icon -material">how_to_reg</i><span>ความต้องการ</span></a>',
				// 		'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.vitalsign/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -material '.($hasVitalsign?'-has':'-not').'">monitor_heart</i><span>{tr:Vital Signs}</span></a>',
				// 		'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.barthel/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -local -barthel-'.$barthel->level.'"></i><span>{tr:Barthel ADL Index}</span></a>',
				// 		'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/qt/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -material">fact_check</i><span>แบบบันทึก</span></a>',
				// 	], // children
				// ]) : NULL,

				// print_o($actionInfo, '$actionInfo'),
			],
		]);
	}

	function _photoAlbum() {
		// Create Photo Album
		return new Ui([
			'type' => 'album',
			'id' => 'project-activity-photo-'.$this->actionId,
			'class' => '-justify-left',
			'children' => (function() {
				$widgets = [];

				if ($this->actionInfo->photos) {
					foreach (explode(',',$this->actionInfo->photos) as $photoItem) {
						list($fid,$photofile) = explode('|', $photoItem);
						if (!$fid || !is_numeric($fid)) continue;

						$photoInfo = model::get_photo_property($photofile);

						$Ui = new Ui('span');
						$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
						if ($this->right->edit) {
							$Ui->add('<a class="sg-action -no-print" href="'.url('project/info/api/'.$projectId.'/photo.delete/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>');
						}

						$widgets[] = '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
							. $Ui->build();
						// 	array(
						// 		'id' => 'project-activity-photo-'.$fid,
						// 		'class' => 'sg-action -hover-parent',
						// 		'href' => $photoInfo->_url,
						// 		'data-rel' => 'img',
						// 		'data-group' => 'project-'.$this->actionId,
						// 		'onclick' => '',
						// 	)
						// );
					}
				}

				if ($this->right->owner || $this->right->admin) {
					$widgets[] = '<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/info/api/'.$this->projectId.'/photo.upload/'.$this->actionId).'" data-rel="#project-activity-photo-'.$this->actionId.'" data-before="li">'
						. '<input type="hidden" name="tagname" value="action" />'
						. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i>'
						. '<span class="-sg-is-desktop">'.$cameraStr.'</span>'
						. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
						. '<input class="-hidden" type="submit" value="upload" />'
						. '</form>';
						// array('class' => '-upload-btn')
					// );
				}
				// debugMsg($widgets, '$widgets');
				return $widgets;
			})(),
		]);
		if ($this->actionInfo->photos) {
			foreach (explode(',',$this->actionInfo->photos) as $photoItem) {
				list($fid,$photofile) = explode('|', $photoItem);
				if (!$fid || !is_numeric($fid)) continue;

				$photoInfo = model::get_photo_property($photofile);

				$Ui = new Ui('span');
				$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
				if ($this->right->edit) {
					$Ui->add('<a class="sg-action -no-print" href="'.url('project/'.$projectId.'/info/photo.delete/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>');
				}

				$photoAlbum->add(
					'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
					. $Ui->build(),
					array(
						'id' => 'project-activity-photo-'.$fid,
						'class' => 'sg-action -hover-parent',
						'href' => $photoInfo->_url,
						'data-rel' => 'img',
						'data-group' => 'project-'.$this->actionId,
						'onclick' => '',
					)
				);
			}
		}

	}
}
?>