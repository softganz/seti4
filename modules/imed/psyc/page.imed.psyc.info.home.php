<?php
/**
* iMed :: Psychiatry Patient Information Home Page
* Created 2021-05-26
* Modify  2021-05-31
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/psyc/{id}
*/

$debug = true;

import('model:imed.patient');

class ImedPsycInfoHome extends Page {
	var $psnId;
	var $patientInfo;
	var $isAccess;
	var $isEdit;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
		$this->isAdmin = is_admin('imed');
		$this->isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$this->isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;

		if (!$psnId) return message('error','Invalid Patient Information');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->patientInfo->info->realname.($this->patientInfo->info->dischar == 1 ? ' (เสียชีวิต)' : ''),
				'trailing' => new Dropbox([
					'children' => [
						'<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/group.in').'" data-rel="box" data-width="480" data-webview="สมาชิกของกลุ่ม"><i class="icon -material">groups</i><span>สมาชิกของกลุ่ม</span></a>',
						'<sep>',
						$this->isAccess ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/delete').'" data-rel="notify" data-title="ลบรายชื่อออกจากระบบ" data-confirm="ลบรายชื่อออกจากระบบ กรุณายืนยัน" data-done="load:#main:'.url('imed/app/my/care').'" title="ลบรายชื่อออกจากระบบ"><i class="icon -material">delete</i><span>ลบรายชื่อออกจากระบบ</span></a>' : NULL,
						$this->isAdmin ? '<a class="sg-action" href="'.url('imed/qt/view/'.$psnId).'" data-rel="box"><i class="icon -material">checklist</i><span>รายการแบบสอบถาม</span></a>' : NULL,
					],
				]),
				'navigator' => [
					new Ui([
						'class' => 'ui-nav -main -sg-text-right',
						'children' => [
							$this->isAccess ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/group.add').'" data-rel="box" data-width="480" data-max-height="80%" data-options=\'{"silent": true}\' title="เพิ่มผู้ป่วยเข้ากลุ่ม"><i class="icon -material">group_add</i><span class="-hidden">Add to Group</span></a>': NULL ,
							$this->isAccess ? (
								$this->patientInfo->info->dischar == 1 ? [
									'text' => '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/dead.cancel').'" data-rel="notify" title="ยกเลิกบันทึกการเสียชีวิต" data-title="ยกเลิกบันทึกการเสียชีวิต" data-confirm="ผู้ป่วยรายนี้ยังคงมีชีวิตอยู่ กรุณายืนยัน?" data-done="reload" data-options=\'{"silent": true}\'><i class="icon -material">airline_seat_individual_suite</i><span class="-hidden">ยกเลิกบันทึกการเสียชีวิต</span></a>',
									'options' => '{class: "-dead"}'
								]
								:
								'<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/dead').'" data-rel="box" data-width="480" title="บันทึกการเสียชีวิต" data-width="480"><i class="icon -material">airline_seat_individual_suite</i><span class="-hidden">บันทึกการเสียชีวิต</span></a>'
							)
							: NULL,
						],
					]),
				], // Navigator
			]), // AppBar

			'body' => new Container([
				'children' => [
					// Show patient photo
					$this->_patientProfile(),

					// Show green/yellow/red status
					$this->isAccess ? $this->_status() : NULL,

					// Show patient menu
					new Container([
						'tagName' => 'nav',
						'class' => 'nav -banner-menu',
						'children' => [
							new Ui([
								'debug' => false,
								'columnPerRow' => 2,
								'children' => $this->isAccess ? [
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.visit').'" data-webview="เยี่ยมบ้าน"><i class="icon -material">medical_services</i><span class="lang-text -th">เยี่ยมบ้าน</span></a><span class="-number">'.$this->patientInfo->visit->count.'</span>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.need').'" data-webview="ความต้องการ"><i class="icon -material">how_to_reg</i><span class="lang-text -th">ความต้องการ</span></a><span class="-number">'.$this->patientInfo->need->count.'</span>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.survey').'" data-webview="แบบประเมินอาการ"><i class="icon -material">fact_check</i><span class="lang-text -th">แบบประเมินอาการทางจิต</span></a>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.personal').'" data-webview="ข้อมูลทั่วไป"><i class="icon -material">person</i><span class="lang-text -th">ข้อมูลทั่วไป</span></a>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.health').'" data-webview="ข้อมูลสุขภาพทั่วไป"><i class="icon -material">favorite_border</i><span class="lang-text -th">ข้อมูลสุขภาพทั่วไป</span></a>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.psyc').'" data-webview="ข้อมูลสุขภาพจิต"><i class="icon -material">favorite</i><span class="lang-text -th">ข้อมูลสุขภาพจิต</span></a>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.disabled').'" data-webview="คนพิการ"><i class="icon -material">accessible</i><span class="lang-text -th">ข้อมูลคนพิการ</span></a>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.map').'" data-webview="แผนที่" data-refresh="no"><i class="icon -material">place</i><span class="lang-text -th">แผนที่</span></a>',
								] : [
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.visit').'" data-webview="เยี่ยมบ้าน"><i class="icon -doctor"></i><span class="lang-text -th">เยี่ยมบ้าน</span></a><span class="-number">'.$this->patientInfo->visit->count.'</span>',
									'<a class="sg-action btn -primary -fill" href="'.url('imed/psyc/'.$psnId.'/info.need').'" data-webview="ความต้องการ"><i class="icon -material">how_to_reg</i><span class="lang-text -th">ความต้องการ</span></a><span class="-number">'.$this->patientInfo->need->count.'</span>',
								], // children
							]), // Ui
						], // children
					]), // Container

					// Show creater
					new Container([
						'tagName' => 'p',
						'class' => '-sg-text-center',
						'child' => '<small>สร้างโดย '.$this->patientInfo->info->created_by.' เมื่อ '.sg_date($this->patientInfo->info->created_date,'ว ดด ปปปป H:i').($this->patientInfo->info->modify?' แก้ไขล่าสุดโดย '.$this->patientInfo->info->modify_by.' เมื่อ '.sg_date($this->patientInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small>',
					]),
				], // children
			]), // Container
		]); //Scaffold
	}

	function _patientProfile() {
		$psnId = $this->patientInfo->psnId;
		$ret = '<div class="imed-patient-photo-wrapper">';
		if ($this->isAccess) {
			// Show patient photo
			$ret .= '<div id="imed-patient-photo" class="-patient-photo">'
				. '<img src="'.imed_model::patient_photo($psnId).'" width="100%" height="100%" />'
				. '</div>';

			$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('imed/patient/'.$psnId.'/info/photo.upload').'" data-rel="#imed-patient-photo" x-data-done="load:#main:'.url('imed/app/'.$psnId).'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

			$ret .= '<div class="-sg-text-center">เพศ '.SG\getFirst($this->patientInfo->info->sex,'ไม่ระบุ').'<br />';
			$ret .= 'ที่อยู่ '.SG\implode_address($this->patientInfo->info, 'short');
			$ret .= '</div>';
		} else {
			// Show unkonwn photo
			$ret .= '<div class="-patient-photo">'
				. '<img src="'.imed_model::patient_photo(NULL).'" width="100%" height="100%" />'
				. '</div>';

			// Show some information
			unset($this->patientInfo->info->house,$this->patientInfo->info->village,$this->patientInfo->info->zip);
			$ret .= '<div class="-sg-text-center">เพศ '.SG\getFirst($this->patientInfo->info->sex,'ไม่ระบุ').'<br />';
			$ret .= 'ที่อยู่ '.SG\implode_address($this->patientInfo->info, 'short');
			$ret .= '</div>';
		}
		$ret .= '</div>';
		return $ret;
	}

	function _status() {
		$icons = [
			'green' => ['icon' => 'emoji_emotions', 'text' => 'อาการปกติ อยู่ที่บ้าน'],
			'yellow' => ['icon' => 'outlet', 'text' => 'มีอาการเล็กน้อย อยู่ที่บ้าน'],
			'red' => ['icon' => 'sick', 'text' => 'มีอาการทางจิต ควรรักษาที่โรงพยาบาล'],
		];

		$psycRiskStatus = PatientModel::psycRiskStatus($this->psnId);

		return new Row([
			'class' => 'imed-illness-status',
			'mainAxisAlignment' => 'center',
			'crossAxisAlignment' => 'center',
			'children' => [
				new Container([
					'class' => '-status'.($psycRiskStatus->status == 'green' ? ' -green' : ''),
					'children' => [
						'<i class="icon -material -circle" title="'.$icons['green']['text'].'">'.$icons['green']['icon'].'</i>',
					],
				]),
				new Container([
					'class' => '-status'.($psycRiskStatus->status == 'yellow' ? ' -yellow' : ''),
					'children' => [
						'<i class="icon -material -circle" title="'.$icons['yellow']['text'].'">'.$icons['yellow']['icon'].'</i>',
					],
				]),
				new Container([
					'class' => '-status'.($psycRiskStatus->status == 'red' ? ' -red' : ''),
					'children' => [
						'<i class="icon -material -circle" title="'.$icons['red']['text'].'">'.$icons['red']['icon'].'</i>',
					],
				]),
				new Container([
					'id' => 'imed-admit-button',
					'class' => '-sg-text-center'.($this->patientInfo->info->admit ? ' -admit' : ''),
					'children' => [
						'<a class="sg-action btn" href="'.url('imed/patient/'.$this->psnId.'/form.admit').'" data-rel="box" data-width="full" title="บันทึกการเข้ารับการรักษาที่โรงพยาบาล"><i class="icon -material">local_hospital</i><span>ADMIT</span></a>',
					],
				]), // Container
			], // children
		]);
	}
}
?>