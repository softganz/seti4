<?php
/**
* iMed Widget :: Add Patient Form
* Created 2021-08-23
* Modify 	2021-08-23
*
* @param Array $args
* @return Widget
*
* @usage new ImedPatientAddFormWidget([])
*/

$debug = true;

class ImedPatientFormAddWidget extends Widget {
	var $initName;
	var $action;
	var $class = 'sg-form';
	var $rel;
	var $done;
	var $patient = Object;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		if (!user_access('administer imed,create imed at home')) return message('error','Access denied');

		$provinceOptions = [];
		$ampurOptions = [];
		$tambonOptions = [];

		$stmt = 'SELECT
			*
			, IF(`provid`>= 80, "ภาคใต้","ภาคอื่น") `zone`
			FROM %co_province%
			ORDER BY CASE WHEN `provid`>= 80 THEN -1 ELSE 1 END ASC
				, CONVERT(`provname` USING tis620) ASC';
		foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->zone][$rs->provid] = $rs->provname;

		return new Widget([
			'children' => [
				new Form([
					'variable' => 'patient',
					'action' => SG\getFirst($this->action, url('imed/api/patient/create')),
					'id' => 'imed-patient-add',
					'class' => $this->class,
					'checkValid' => true,
					'rel' => $this->data('rel'),
					'done' => $this->data('done'),
					'children' => [
						'psnId' => ['type' => 'hidden', 'name' => 'psnId', 'value' => $this->patient->psnId],
						'cid' => [
							'type' => 'text',
							'label' => 'หมายเลขประจำตัวประชาชน 13 หลัก',
							'class' => '-fill',
							'maxlength' => 13,
							'require' => true,
							'placeholder' => 'หมายเลข 13 หลัก',
							'value' => htmlspecialchars($this->patient->cid),
							'description' => 'ป้อน ? ในกรณีที่ไม่มีบัตรประชาชนหรือยังไม่ทราบ',
						],
						'prename' => [
							'type' => 'text',
							'label' => 'คำนำหน้านาม',
							'class' => '-fill',
							'maxlength' => 20,
							'require' => true,
							'placeholder' => 'eg. นาย นาง',
							'value' => htmlspecialchars($this->patient->prename)
						],
						'fullname' => [
							'type' => 'text',
							'label' => 'ชื่อ - นามสกุล',
							'class' => '-fill',
							'maxlength' => 100,
							'require' => true,
							'placeholder' => 'ชื่อ นามสกุล',
							'value' =>htmlspecialchars(SG\getFirst($this->patient->fullname,$getInitName)),
							'description' => 'กรุณาป้อนขื่อ - นามสกุล โดยเคาะเว้นวรรคจำนวน 1 ครั้งระหว่างชื่อกับนามสกุล <!-- <a class="info" href="http://th.wiktionary.org/wiki/รายชื่ออักษรย่อในภาษาไทย" target="_blank">i</a>-->'
						],
						'sex' => [
							'type' => 'radio',
							'label' => 'เพศ:',
							'require' => true,
							'options' => array('1' => 'ชาย','2' => 'หญิง'),
							'value' => $this->patient->sex,
						],
						'areacode' => [
							'type' => 'hidden',
							'label' => 'เลือกตำบลในที่อยู่',
							'value' => $this->patient->areacode,
							'require' => true
						],
						'address' => [
							'type' => 'text',
							'label' => 'ที่อยู่',
							'class' => 'sg-address -fill',
							'maxlength' => 100,
							'require' => true,
							'attr' => array('data-altfld' => 'edit-patient-areacode'),
							'placeholder' => 'เลขที่ ซอย ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
							'value' => htmlspecialchars($this->patient->address)
						],
					'changwat' => [
							// 'label' => 'จังหวัด:',
							'type' => 'select',
							'class' => 'sg-changwat -fill',
							'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
							'value' => $this->patient->changwat,
							'attr' => array('data-altfld' => '#edit-patient-areacode'),
							// 'containerclass' => '-inlineblock',
						],
					'ampur' => [
							// 'label' => 'อำเภอ:',
							'type' => 'select',
							'class' => 'sg-ampur -fill -hidden',
							'options' => array('' => '== เลือกอำเภอ ==') + $ampurOptions,
							// 'containerclass' => '-inlineblock',
							'value' => $this->patient->ampur,
							'attr' => array('data-altfld' => '#edit-patient-areacode'),
						],
					'tambon' => [
							// 'label' => 'ตำบล:',
							'type' => 'select',
							'class' => 'sg-tambon -fill -hidden',
							'options' => array('' => '== เลือกตำบล ==') + $tambonOptions,
							'value' => $this->patient->tambon,
							// 'containerclass' => '-inlineblock',
							'attr' => array('data-altfld' => '#edit-patient-areacode'),
						],
						'save' => [
							'type' => 'button',
							'name' => 'save',
							'value' => '<i class="icon -addbig -white"></i><span>เพิ่มชื่อรายใหม่</span>',
							'container' => '{class: "-sg-text-right"}',
						],
					], // children
				]), // Form
			], // children
		]);
	}
}
?>