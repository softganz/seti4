<?php
/**
* iMed :: Group Patient
* Created 2021-08-17
* Modify  2021-08-17
*
* @param Object $orgInfo
* @return Widget
*
* @usage imed/group/{id}/patient
*/


$debug = true;

class ImedGroupPatient extends Page {
	var $refApp;
	var $orgId;
	var $orgInfo;
	var $urlView = 'imed/group/';
	var $urlPatientView;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		parent::__construct();
		// debugMsg($this,'$ImedGroupPatient');
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, page: "web"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		$orgInfo = $this->orgInfo;
		$orgId = $orgInfo->orgid;

		if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

		$this->isAdmin = $isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
		$isMember = $isAdmin || $orgInfo->is->socialtype;
		$this->isCareManager = $isAdmin || in_array($isMember,array('CM','MODERATOR','PHYSIOTHERAPIST'));

		if (!($isAdmin || $isMember)) return message('error','ขออภัย ท่านไม่ได้อยู่ในกลุ่มนี้');


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.$this->orgInfo->name,
				// 'leading' => '<a class="btn -link" href="'.url($this->urlView.$this->orgId).'"><i class="icon -material">groups</i></a>',
				'navigator' => [
					new Ui([
						'children' => [
							'<a href="'.url($this->urlView.$orgId.'/patient').'"><i class="icon -material">wheelchair_pickup</i><span class="-hidden">{tr:Patients}</span></a>',
							'<a href="'.url($this->urlView.$orgId.'/visit').'" data-webview="เยี่ยมบ้าน"><i class="icon -material">medical_services</i><span class="-hidden">{tr:เยี่ยมบ้าน}</span></a>',
							'<a href="'.url($this->urlView.$orgId.'/member').'" data-webview="สมาชิก"><i class="icon -material">people</i><span class="-hidden">{tr:Members}</span></a>',
							// $isAdmin || $isPoCenter ? '<a class="sg-action" href="'.url('imed/app/pocenter/'.$orgId).'" data-rel="#main" data-webview="กายอุปกรณ์"><i class="icon -material">accessible_forward</i><span class="-hidden">กายอุปกรณ์</span></a>' : '',
							'<a href="'.url($this->urlView.$orgId.'/menu').'" data-webview="เมนู"><i class="icon -material">more_vert</i><span class="-hidden">Menu</span></a>',
						],
					]), // Ui
				], // navigator
			]), // AppBar
			'body' => new Container([
				'tagName' => 'section',
				'id' => 'imed-group-patient',
				'dataUrl' => url($this->urlView.$orgId.'/patient'),
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'title' => '@Patient of Group',
								'leading' => '<i class="icon -material">wheelchair_pickup</i>',
							]), //
						], // children
					]), // Card

					$isAdmin || $isMember ? new Form([
						'action' => url('imed/api/group/'.$orgId.'/patient.add'),
						'id' => 'add-member',
						'class' => 'sg-form -flex imed-social-patient-form',
						'checkValid' => true,
						'rel' => 'none',
						'done' => 'load->replace:#imed-group-patient',
						'children' => [
							'psnid' => ['type' => 'hidden', 'name' => 'psnid', 'id' => 'psnid'],
							'name' => [
								'type'=>'text',
								'label' => tr('ADD PATIENT'),
								'class'=>'sg-autocomplete -fill',
								'require'=>true,
								'value'=>htmlspecialchars($name),
								'placeholder'=>'+ ชื่อผู้ป่วย ที่ต้องการเพิ่มให้กลุ่มดูแล',
								'posttext' => '<div class="input-append"><span><button class="btn -primary"><i class="icon -material">add</i></button></span></div>',
								'container' => '{class: "-group -label-in"}',
								'attr' => [
									'data-query'=>url('imed/api/patients'),
									'data-altfld' => 'psnid',
								],
							],
						], // children
					]) : NULL, // Form
					$this->_paritentList(),
				],
			]),
		]);
	}

	function _getPatients() {
		mydb::where('sp.`orgid` = :orgid', ':orgid', $this->orgId);

		if (post('s') == 'new') {
			mydb::value('$ORDER$','sp.`created` DESC');
		} else {
			mydb::value('$ORDER$','CONVERT(p.`name` USING tis620) ASC,  CONVERT(p.`lname` USING tis620) ASC');
		}

		$stmt = 'SELECT
			  sp.`psnid`
			, p.`prename`
			, CONCAT(p.`name`," ",p.`lname`) `name`
			, p.`sex`
			, sp.`addby`
			, u.`name` `addByName`
			, sp.`created`
			, (SELECT COUNT(*) FROM %imed_service% sv WHERE sv.`pid` = p.`psnid`) `serviceAmt`
			, (SELECT COUNT(*) FROM %imed_careplan% cp WHERE cp.`psnid` = p.`psnid` AND `orgid` = sp.`orgid`) `planAmt`
			FROM %imed_socialpatient% sp
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %users% u ON u.`uid` = sp.`addby`
			%WHERE%
			ORDER BY $ORDER$';

		$dbs = mydb::select($stmt, ':uid', i()->uid);

		return $dbs->items;
	}

	function _paritentList() {
		return new Widget([
			'children' => (function() {
				$widgets = [];
				$isRemovePatient = $this->isAdmin || in_array($this->orgInfo->is->socialtype,array('MODERATOR','CM'));
				foreach ($this->_getPatients() as $item) {
					$isRemoveable = $isRemovePatient || $item->addby == $myUid;
					$patientUrl = url($this->urlPatientView.$item->psnid);

					$widgets[] = new Card([
						'children' => [
							new ListTile([
								'title' => $item->prename.' '.$item->name,
								'subtitle' => 'Added by '.$item->addByName.' on '.sg_date($item->created, 'ว ดด ปปปป'),
								'leading' => '<img class="profile-photo -sg-32" src="'.imed_model::patient_photo($item->psnid).'" width="48" height="48" />',
								'trailing' => new DropBox([
									'children' => [
										$isRemoveable ? '<a class="sg-action" href="'.url('imed/api/group/'.$this->orgId.'/patient.remove/'.$item->psnid).'" data-rel="none" data-done="remove:parent .widget-card" data-title="ลบผู้ป่วยออกจากกลุ่ม" data-confirm="ต้องการลบผู้ป่วยออกจากกลุ่ม กรุณายืนยัน?"><i class="icon -material -gray">cancel</i><span>Remove from Group</span></a>' : NULL,
									],
								]), // DropBox
							]), // ListTile
							new Row([
								'class' => 'detail -sg-paddingnorm',
								'mainAxisAlignment' => 'spacearound',
								'crossAxisAlignment' => 'center',
								'children' => [
									'เยี่ยมบ้าน '.number_format($item->serviceAmt).' ครั้ง',
									'<a class="sg-action btn" href="'.$patientUrl.'" data-webview="'.$item->name.'" role="patient" data-pid="'.$item->psnid.'" style="margin: 0 8px;"><i class="icon -material">medical_services</i><span>เยี่ยมบ้าน</span></a>'
								], // children
							]), // Container
						], // children
					]);
				}
				return $widgets;
			})(), // children
		]);
	}
}
?>