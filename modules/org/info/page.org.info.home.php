<?php
/**
* Org :: Home Page
* Created 2021-08-01
* Modify  2021-09-27
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}
*/

$debug = true;

import('widget:org.nav.php');
import('model:org.php');

class OrgInfoHome extends Page {
	var $orgId;
	var $orgInfo;
	var $right;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'admin' => $this->orgInfo->is->orgadmin,
		];
	}

	function build() {
		if (!$this->orgId) return message('error', 'ขออภัย!!! ไม่มีข้อมูลองค์กรตามที่ระบุ');

		$orgConfig = cfg('org');

		// debugMsg(eval_php($homepageCmd, NULL, NULL, (object)['orgId'=>$this->orgId]));
		if ($homepageCmd = property('org:HOMEPAGE:'.$this->orgId)) {
			return new Scaffold([
				'appBar' => new AppBar([
					'title' => $this->orgInfo->name,
					// 'navigator' => new OrgNavWidget($this->orgInfo),
					'trailing' => new Row([
						'children' => [
							new DropBox([
								'children' => [
									$this->right->admin ? '<a href="'.url('org/'.$this->orgId.'/info.view').'"><i class="icon -material">account_balance</i><span>ข้อมูลองค์กร</span></a>' : NULL,
									$this->right->admin ? '<a class="sg-action" href="'.url('paper/post/story', ['org' => $this->orgId]).'" data-rel="box" data-width="640"><i class="icon -material">post_add</i><span>ส่งข่าวสาร-ประชาสัมพันธ์</span></a>' : NULL,
									$this->right->admin ? '<a href="'.url('org/'.$this->orgId.'/setting').'"><i class="icon -material">settings</i><span>Settings</span></a>' : NULL,
									$this->right->admin ? '<sep>' : NULL,
									$this->right->admin ? '<a class="sg-action" href="'.url('org/new', ['parent' => $this->orgId]).'" data-rel="box" data-width="480"><i class="icon -material">group_add</i><span>สร้างองค์กรย่อย</span></a>' : NULL,
								], // children
							]), // DropBox
						], // children
					]), // Row
				]), // AppBar
				'body' => eval_php($homepageCmd, NULL, NULL, (Object)['orgId' => $this->orgId]),
			]);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
				'trailing' => new Row([
						'children' => [
							new DropBox([
								'children' => [
									$this->right->admin ? '<a href="'.url('org/'.$this->orgId.'/info.view').'"><i class="icon -material">account_balance</i><span>ข้อมูลองค์กร</span></a>' : NULL,
									$this->right->admin ? '<a href="'.url('org/'.$this->orgId.'/setting').'"><i class="icon -material">settings</i><span>Settings</span></a>' : NULL,
								], // children
							]), // DropBox
						], // children
					]), // Row
			]), // AppBar
			'body' => new Container([
				'class' => 'org-info-home',
				'children' => (function() {
					$childrens = [];
					$orgConfig = cfg('org');


					foreach (explode(',',$orgConfig->infoUse) as $section) {
						$item = $orgConfig->info->{$section};
						if (is_null($item) || is_null($widget = R::PageWidget($item->page, [$this->orgInfo]))) continue;

						$childrens[$section] = new Card([
							'tagName' => 'section',
							'id' => 'project-info-section-'.$section,
							'class' => 'project-info-section -'.$section,
							'children' => [
								new ListTile([
									'class' => '-sg-paddingnorm',
									'title' => $item->title,
									'leading' => '<i class="icon -material">'.(SG\getFirst($item->icon, 'stars')).'</i>',
									'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
								]), // ListTile
								$widget,
								// new DebugMsg($item,'$item'),
							], // children
						]);
					}

					// $childrens['creater'] = new Card([
					// 	'id' => 'project-info-creater',
					// 	'children' => [
					// 		new ListTile([
					// 			'class' => '-sg-paddingnorm',
					// 			'title' => '<a href="'.url('project/list', ['u' => $this->projectInfo->info->uid]).'" title="'.htmlspecialchars($this->projectInfo->info->ownerName).'">'.$this->projectInfo->info->ownerName.'</a>',
					// 			'leading' => '<a href="'.url('project/list', ['u' => $this->projectInfo->info->uid]).'" title="'.htmlspecialchars($this->projectInfo->info->ownerName).'"><img class="profile-photo" src="'.model::user_photo($this->projectInfo->info->username).'" width="32" height="32" alt="'.htmlspecialchars($this->projectInfo->info->ownerName).'" /></a>',
					// 			'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
					// 		]), // ListTile
					// 		'<p class="-sg-paddingnorm">โครงการเข้าสู่ระบบเมื่อวันที่ '.sg_date($this->projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>',
					// 	], // children
					// ]);
					// $childrens['script'] = $this->script();

					return $childrens;
				})(),

				// 'children' => [
				// 	$this->_projects(),
				// 	$this->_orgInfo(),
				// 	$this->_officers(),
				// 	$orgConfig->useBoard ? $this->_board() : NULL,
				// ],
			]),
		]);
	}

	function _orgInfo() {
		return new Card([
			'class' => '-area',
			'children' => [
				// if ($isEdit) {
				// 	$ui=new ui();
				// 	$ui->add('<a href="'.url('project/org/'.$orgId.'/info').'"><i class="icon -edit"></i></a>');
				// 	$ret .= $ui->build();
				// }
				new ListTile([
					'class' => '-sg-paddingnorm',
					'title' => 'รายละเอียดองค์กร',
					'leading' => '<i class="icon -material">info</i>',
					'trailing' => $this->right->admin ? '<a class="btn -link" href="'.url('org/'.$this->orgId.'/info.view').'"><i class="icon -material">find_in_page</i></a>' : NULL,
				]),
				new Table([
					'children' => [
						['ชื่อองค์กร', $this->orgInfo->name],
						['ที่อยู่', $this->orgInfo->info->address.' '.$this->orgInfo->info->zip],
						['โทรศัพท์', $this->orgInfo->info->phone],
						$this->orgInfo->info->fax ? ['โทรสาร', $this->orgInfo->info->fax] : NULL,
						['อีเมล์', $this->orgInfo->info->email],
					], // children
				]), // Table
			],
		]);
	}

	function _officers() {
		$isViewOfficer = $this->orgInfo->is->orgadmin;
		return new Card([
			'class' => '-officer',
			'children' => [
				new ListTile([
					'class' => '-sg-paddingnorm',
					'title' => 'เจ้าหน้าที่องค์กร',
					'leading' => '<i class="icon -material">people</i>',
				]),
				new Table([
					'children' => (function(){
						$rows = [];
						foreach (OrgModel::officers($this->orgId)->items as $item) {
							$rows[] = [
								'<img class="profile-photo" src="'.model::user_photo($item->username).'" width="32" height="32" alt="'.htmlspecialchars($item->name).'" title="'.htmlspecialchars($item->name).'" />',
								$item->name,
								$item->membership,
							];
						}
						return $rows;
					})(),
				]),
				$isViewOfficer ? new Nav([
					'mainAxisAlignment' => 'end',
					'children' => [
						'<a class="btn -primary" href="'.url('org/'.$this->orgId.'/info.officer').'"><i class="icon -material">people</i>จัดการสมาชิกองค์กร</a>',
					], // children
				]) : NULL,
			], // children
		]);
	}

	function _board() {
		return new Card([
			'children' => [
				new ListTile([
					'class' => '-sg-paddingnorm',
					'title' => 'คณะกรรมการ',
					'leading'=>'<i class="icon -material">group</i>',
					'trailing' => '<a class="btn -link" href="'.url('org/'.$this->orgId.'/board').'"><i class="icon -material">groups</i><span>รายชื่อ</span></a>',
				]),
			],
		]);
	}
}
?>