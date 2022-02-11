<?php
/**
* iMed : View Patient Summary on App
* Created 2017-06-21
* Modify  2021-06-02
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/app/{$psnId}
*/

$debug = true;

class ImedAppInfoHome extends Page {
	var $patientInfo;
	var $isAccess;
	var $isEdit;

	function __construct($patientInfo) {
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
				'trailing' => new DropBox([
					'class' => 'ui-nav',
					'children' => [
						'<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/group.in').'" data-rel="box" data-width="480" data-webview="สมาชิกของกลุ่ม"><i class="icon -material">groups</i><span>สมาชิกของกลุ่ม</span></a>',
						'<sep>',
						$this->patientInfo->care->rehab && $this->isEdit ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/rehab.remove').'" data-rel="notify" data-done="load" data-title="ลบรายชื่อออกจากผู้ป่วยรอการฟื้นฟู" data-confirm="กรุณายืนยัน?"><i class="icon -material -gray">cancel</i><span>ลบออกจากกลุ่มผู้ป่วยรอการฟื้นฟู</span></a>' : NULL,
						$this->patientInfo->care->elder && $this->isEdit ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/elder.remove').'" data-rel="notify" data-done="load" data-title="ลบรายชื่อออกจากกลุ่มผู้สูงอายุ" data-confirm="กรุณายืนยัน?"><i class="icon -material -gray">cancel</i><span>ลบออกจากกลุ่มผู้สูงอายุ</span></a>' : NULL,
						$this->isEdit ? '<sep>' : NULL,
						$this->isEdit ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/delete').'" data-rel="notify" data-title="ลบรายชื่อออกจากระบบ" data-confirm="ลบรายชื่อออกจากระบบ กรุณายืนยัน" data-done="load:#main:'.url('imed/app/my/care').'" title="ลบรายชื่อออกจากระบบ"><i class="icon -material">delete</i><span>ลบรายชื่อออกจากระบบ</span></a>' : NULL,
						$this->isAdmin ? '<sep>' : NULL,
						$this->isAdmin ? '<a class="sg-action" href="'.url('imed/qt/view/'.$psnId).'" data-rel="box"><i class="icon -material">checklist</i><span>รายการแบบสอบถาม</span></a>' : NULL,
					], // children
				]), // Ui

				'navigator' => [
					new Ui([
						'class' => 'ui-nav -main -sg-text-right',
						'children' => [
							$this->isAccess ? '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/group.add').'" data-rel="box" data-width="480" data-max-height="80%" data-options=\'{"silent": true}\'><i class="icon -material">group_add</i><span class="-hidden">Add to Group</span></a>': NULL ,
							$this->isAccess ? (
								$this->patientInfo->info->dischar == 1 ? ['text' => '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/info/dead.cancel').'" data-rel="notify" title="ยกเลิกบันทึกการเสียชีวิต" data-title="ยกเลิกบันทึกการเสียชีวิต" data-confirm="ผู้ป่วยรายนี้ยังคงมีชีวิตอยู่ กรุณายืนยัน?" data-done="reload" data-options=\'{"silent": true}\'><i class="icon -material">airline_seat_individual_suite</i><span class="-hidden">ยกเลิกบันทึกการเสียชีวิต</span></a>', 'options' => '{class: "-dead"}']
								:
									'<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/dead').'" data-rel="box" data-width="480" title="บันทึกการเสียชีวิต" data-width="480"><i class="icon -material">airline_seat_individual_suite</i><span class="-hidden">บันทึกการเสียชีวิต</span></a>'
								)
							: NULL,
						], // children
					]), // Ui
				], // navigator
			]), // AppBar

			'children' => [
				$this->_patientProfile(),

				$this->isEdit ? new Ui([
					'tagName' => 'div',
					'class' => 'ui-card -type',
					'children' => [
						'<div class="detail">'.$this->patientInfo->fullname.' เป็นคนพิการ'.($this->patientInfo->care->disabled ? '' : 'หรือไม่?')
							. ($this->patientInfo->care->disabled ? '<a class="sg-action btn -link -yes" href="'.url('imed/patient/'.$psnId.'/info/disabled.remove').'" data-rel="notify" data-done="load" data-title="ลบออกจากกลุ่มคนพิการ" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่มคนพิการ กรุณายืนยัน?"><i class="icon -material">accessible</i></a>' : '<a class="sg-action btn -link" href="'.url('imed/app/'.$psnId.'/info.disabled').'"><i class="icon -material">done</i></a>')
							. '</div>',
						'<div class="detail">'.$this->patientInfo->fullname.' เป็นผู้ป่วยติดเตียง'.($this->patientInfo->care->rehab ? '' : 'หรือไม่?')
							. ' <a class="sg-action btn -link'.($this->patientInfo->care->rehab? ' -yes' : '').'" href="'.url('imed/patient/'.$psnId.'/info/rehab.'.($this->patientInfo->care->rehab? 'remove' : 'add')).'" data-rel="notify" data-done="load:#main:'.url('imed/app/'.$psnId).'"><i class="icon '.($this->patientInfo->care->rehab ? ' -local -barthel-bed' : '-material').'">'.($this->patientInfo->care->rehab ? '' : 'done').'</i></a>'
							. '</div>',
						'<div class="detail">'.$this->patientInfo->fullname.' เป็นผู้สูงอายุ'.($this->patientInfo->care->elder ? '' : 'หรือไม่?')
							. ' <a class="sg-action btn -link'.($this->patientInfo->care->elder? ' -yes' : '').'" href="'.url('imed/patient/'.$psnId.'/info/elder.'.($this->patientInfo->care->elder? 'remove' : 'add')).'" data-rel="notify" data-done="load:#main:'.url('imed/app/'.$psnId).'"><i class="icon '.($this->patientInfo->care->elder ? ' -local -barthel-no -red' : '-material').'">'.($this->patientInfo->care->elder ? '' : 'done').'</i></a>'
							. '</div>',
					],
				]) : NULL, // Ui

				new Container([
					'tagName' => 'nav',
					'class' => 'nav -banner-menu',
					'children' => [
						new Ui([
							'debug' => false,
							'children' => $this->_patientMenu(),
						]), // Ui
					], // children
				]), // Container

				new Container([
					'class' => '-sg-text-center',
					'children' => [
						'<small>สร้างโดย '.$this->patientInfo->info->created_by.' เมื่อ '.sg_date($this->patientInfo->info->created_date,'ว ดด ปปปป H:i').($this->patientInfo->info->modify?' แก้ไขล่าสุดโดย '.$this->patientInfo->info->modify_by.' เมื่อ '.sg_date($this->patientInfo->info->modify,'ว ดด ปปปป H:i'):'').'</small>',
					], // children
				]), // Container
				'<style>
					.ui-card.-type a {position: absolute; top: 4px; right: 16px; margin:0; border-radius: 50%; padding: 10px; box-shadow: 0 0 0 1px #d2d2d2 inset; background-color: transparent;}
					.ui-card.-type a>.icon {color: #ccc;}
					.ui-card.-type a.-yes {box-shadow: 0 0 0 1px red inset; background-color: #EF4446;}
					.ui-card.-type a.-yes>.icon {color: #fff;}
					.ui-card.-type .detail {min-height: 36px;}
					.ui-menu.-imed>.ui-item {position: relative;}
					.ui-menu.-imed .-number {position: absolute; top: 4px; right: 4px; background-color: green; color: #fff; display: block; font-size: 0.8em; width: 2em; height: 2em; line-height: 2em; border-radius: 50%; pointer-events: none;}
				</style>',
			], // children
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

	function _patientMenu() {
		$psnId = $this->patientInfo->psnId;
		$menu = [
			'<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.visit').'" data-webview="เยี่ยมบ้าน"><i class="icon -material">medical_services</i><span class="lang-text -th">เยี่ยมบ้าน</span><span class="lang-text -en">Home Visit</span></a><span class="-number">'.$this->patientInfo->visit->count.'</span>',
			'<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.need').'" data-webview="ความต้องการ"><i class="icon -material">how_to_reg</i><span class="lang-text -th">ความต้องการ</span><span class="lang-text -en">Needed Information</span></a><span class="-number">'.$this->patientInfo->need->count.'</span>',
			'<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.po').'" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="lang-text -th">กายอุปกรณ์</span><span class="lang-text -en">Orthotics Information</span></a>',
			'<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.vitalsign').'" data-webview="สัญญาณชีพ"><i class="icon -material">monitor_heart</i><span class="lang-text -th">สัญญาณชีพ</span><span class="lang-text -en">Vital Sign</span></a>',
			'<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.barthel').'" data-webview="ดัชนีบาร์เธล"><i class="icon -local -vitalsign -white -not"></i><span class="lang-text -th">ดัชนีบาร์เธล</span><span class="lang-text -en">Bathel ADL Index</span></a>',
		];

		if ($this->isAccess) {
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.personal').'" data-webview="ข้อมูลทั่วไป"><i class="icon -material">person</i><span class="lang-text -th">ข้อมูลทั่วไป</span><span class="lang-text -en">Personal Information</span></a>';
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.health').'" data-webview="ข้อมูลสุขภาพ"><i class="icon -material">favorite_border</i><span class="lang-text -th">ข้อมูลสุขภาพ</span><span class="lang-text -en">Health Information</span></a>';
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.disabled').'" data-webview="คนพิการ"><i class="icon -disabled-people"></i><span class="lang-text -th">ข้อมูลคนพิการ</span><span class="lang-text -en">Disabled Information</span></a>';
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.rehab').'" data-webview="ข้อมูลผู้ป่วยรอการฟื้นฟู"><i class="icon -rehabilitation"></i><span class="lang-text -th">ผู้ป่วยรอการฟื้นฟู</span><span class="lang-text -en">Health Information</span></a>';
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.elder').'" data-webview="ผู้สูงอายุ"><i class="icon -local -barthel-no -white"></i><span class="lang-text -th">ข้อมูลผู้สูงอายุ</span><span class="lang-text -en">Elder Information</span></a>';
		}

		$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.poorqts').'" data-webview="คนยากลำบาก"><i class="icon -rehabilitation"></i><span class="lang-text -th">ข้อมูลคนยากลำบาก</span><span class="lang-text -en">&nbsp;</span></a><span class="-number">'.$this->patientInfo->poor->count.'</span>';

		if ($this->isAccess) {
			$menu[] = '<a class="sg-action btn -primary -fill" href="'.url('imed/app/'.$psnId.'/info.map').'" data-webview="แผนที่" data-refresh="no"><i class="icon -pin"></i><span class="lang-text -th">แผนที่</span><span class="lang-text -en">Map</span></a>';
		}

		return $menu;
	}
}
?>