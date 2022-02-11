<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

import('widget:org.nav.php');
import('model:org.php');

class OrgInfoOfficer extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		if (!$this->orgId) return message(['text' => 'PROCESS ERROR']);

		$isAdmin = $this->orgInfo->is->orgadmin;

		$this->isEditable = is_admin() || $isAdmin;
			// || in_array($orgMember, array('MANAGER','ADMIN','OWNER','TRAINER'))
			// || in_array($topicMember, array('MANAGER','ADMIN','OWNER','TRAINER'));

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมาชิก : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'body' => new Container([
				'id' => 'officer-member',
				'class' => 'who-access',
				'dataUrl' => url('org/'.$this->orgId.'/info.officer'),
				'children' => [
					'<header class="header -box">'._HEADER_BACK.'<h3>Who has access</h3></header>',
					new Container([
						'class' => '-members',
						'child' => new Table([
							'children' => (function(){
								$result = [];
								foreach (OrgModel::officers($this->orgId)->items as $item) {
									$ui = new Ui([
										'children' => [
											// $this->isEditable ? '<a class="sg-action" href="'.url('profile/'.$item->uid).'" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>' : NULL,
											$this->isEditable && $item->orgUid != $item->uid ? '<a class="sg-action btn -link" href="'.url('org/info/api/'.$this->orgId.'/officer.remove/'.$item->uid).'" data-rel="notify" data-removeparent="tr"  data-title="ลบชื่อออกจากองค์กร" data-confirm="ต้องการลบชื่อออกจากเจ้าหน้าที่องค์กร กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>' : NULL,
										],
									]);

									$menu = $ui->count() ? $ui->build() : '';

									$result[] = [
											'<img class="ownerphoto" src="'.model::user_photo($item->username).'" width="48" height="48" alt="'.htmlspecialchars($item->name).'" title="'.htmlspecialchars($item->name).'" />',
											'<span>'.$item->name
											.($item->uid == i()->uid ? ' (is you)' : '')
											.'</span>'
											.($this->isEditable ? '<span class="-email">'.$item->email.' ('.$item->username.')</span>' : ''),
											($item->orgUid == $item->uid ? 'Is ' : '').$item->membership,
											$menu,
											'config' => $this->isEditable ? ['class' => 'sg-action', 'href' => url('profile/'.$item->uid), 'data-rel' => 'box'] : '',
										];
								}
								return $result;
							})(), // children
						]), // Table
					]),
					$this->isEditable ? new Form([
						'action' => url('org/info/api/'.$this->orgId.'/officer.add'),'add-owner',
						'class' => 'sg-form -sg-flex project-member-form',
						'rel' => 'notify',
						'done' => 'load->replace:#officer-member',
						'children' => [
							'uid' => ['type'=>'hidden', 'id'=>'uid'],
							'name' => [
								'type'=>'text',
								'class'=>'sg-autocomplete -fill',
								'require'=>true,
								'value'=>htmlspecialchars($name),
								'placeholder'=>'ระบุ ชื่อจริง หรือ อีเมล์ ของสมาชิกที่ต้องการแบ่งปันการใช้งาน',
								'attr'=>[
									'data-query'=>url('api/user'),
									//'data-callback' => 'submit',
									'data-altfld' => 'uid',
								],
								'container' => '{style: "flex: 1"}',
							],
							'membership' => [
								'type' => 'select',
								'options' => [
									'ADMIN'=>'ADMIN',
									'MANAGER'=>'MANAGER',
									'TRAINER'=>'TRAINER',
									'OFFICER'=>'OFFICER',
									'REGULAR MEMBER'=>'REGULAR MEMBER'
								],
								'value' => 'OFFICER',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">add</i>',
							],
						], // children
					]) : NULL, // Form
				], // children
			]), // Widget
		]);
	}
}
?>