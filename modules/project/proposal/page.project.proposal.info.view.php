<?php
/**
* Project :: Proposal Information
* Created 2021-09-24
* Modify 	2021-09-24
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}
*/

$debug = true;

import('widget:project.like.status.php');
import('widget:project.proposal.progress.php');
import('widget:project.proposal.nav.php');
import('page:project.proposal.info.review.php');

class ProjectProposalInfoView extends Page {
	var $projectId;
	var $editMode = false;
	var $right;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->right = (Object) [
			'review' => is_admin('project'),
			'access' => is_admin('project') || $this->proposalInfo->RIGHT & _IS_RIGHT,
		];
	}

	function build() {
		head('<meta name="robots" content="noindex,nofollow">');
		$actionMode = SG\getFirst(post('mode'),$_SESSION['mode']);

		unset($_SESSION['mode']);

		$currentVersion = true;

		if (!$this->projectId) {
			return message([
				'code' => _HTTP_ERROR_NOT_FOUND,
				'type' => 'error',
				'text' => ['ไม่มีข้อมูลข้อเสนอโครงการที่ระบุ','Access Denied']
			]);
			return message([
				'code' => _HTTP_ERROR_NOT_FOUND,
				'type' => 'error',
				'text' => 'ไม่มีข้อมูลข้อเสนอโครงการที่ระบุ'
			]);
			// return message('error', 'ไม่มีข้อมูลข้อเสนอโครงการที่ระบุ2');
		} else if ($this->proposalInfo->info->topicStatus == _BLOCK
			&& !user_access('administer contents,administer papers')) {
			head('<meta name="robots" content="noarchive" />');
			return message([
				'code' => _HTTP_ERROR_NOT_FOUND,
				'type' => 'error',
				'text' => 'This topic was blocked.']
			);
		} else if (!$this->right->access) {
			return message([
				'code' => _HTTP_ERROR_FORBIDDEN,
				'type' => 'notify',
				'text' => 'ขออภัย!!!:สงวนสิทธิ์ในการเข้าถึงข้อมูลเฉพาะเจ้าของหลักสูตรเท่านั้น'
			]);
		}

		if ($revId = post('rev')) {
			$currentVersion = false;
			$revData = mydb::select(
				'SELECT `flddata` FROM %bigdata% WHERE `bigid` = :revId LIMIT 1',
				[':revId' => $revId]
			)->flddata;

			// debugMsg('<pre>'.$revData.'</pre>');
			$revData = mydb::select(
				'SELECT `flddata`, `created` FROM %bigdata% WHERE `bigid` = :revId LIMIT 1',
				[':revId' => $revId]
			);
			// debugMsg($revData, '$revData');
			$revJson = preg_replace('/\r|\n/','\n', trim($revData->flddata));
			$this->proposalInfo = json_decode($revJson, false);
			$this->proposalInfo->title .= '@'.sg_date($revData->created, 'ว ดด ปปปป H:i');
			if (isset($this->proposalInfo->data)) $this->proposalInfo->data = (Array) $this->proposalInfo->data;
			unset($this->proposalInfo->RIGHT, $this->proposalInfo->info->status);
		}

		$isWebAdmin = is_admin('project');
		$isAdmin = $this->proposalInfo->RIGHT & _IS_ADMIN;
		$isTrainer = $this->proposalInfo->RIGHT & _IS_TRAINER;
		$this->isEditable = $isEditable = $this->proposalInfo->RIGHT & _IS_EDITABLE;
		$isFullView = $this->proposalInfo->RIGHT & _IS_RIGHT;
		$this->editMode = $actionMode === 'edit' && $isEditable;
		$this->proposalInfo->editMode = $this->editMode;
		$isInConsiderPeriod = true; // in_array($this->proposalInfo->info->pryear, [2021,2020,2019]) ;
		$this->hasReview = $this->proposalInfo->data['rating'] || $this->proposalInfo->data['review'];

		$is_comment_sss = user_access('comment project');
		$is_comment_hsmi = user_access('administer papers,administer projects') || $isTrainer;

		$status = [1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ'];

		R::Model('reaction.add', $this->projectId, 'TOPIC.VIEW');

// debugMsg($this->proposalInfo,'$proposalInfo');
		$inlineAttr = [];
		if ($this->editMode) {
			$inlineAttr['data-update-url'] = url('project/develop/update/'.$this->projectId);
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-refresh-url'] = url('project/proposal/'.$this->projectId, ['debug' => post('debug')]);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}

		$revList = [];
		foreach (mydb::select(
				'SELECT `bigid` `revId`, `keyid` `projectId`, `created` FROM %bigdata% WHERE `keyname` = "project.develop" AND `fldname` = "revision" AND `keyid` = :projectId ORDER BY `bigid` DESC',
				[':projectId' => $this->projectId]
			)->items as $item) {
			$revList['rev-'.$item->revId] = '<a href="'.url('project/proposal/'.$this->projectId,['rev' => $item->revId]).'">Revision : '.sg_date($item->created, 'ว ดด ปปปป H:i').'</a>';
		}

		if ($currentVersion) {
			$trailingNav = [
				$isAdmin ? '<a class="sg-action btn -status-'.$this->proposalInfo->info->status.'" href="'.url('project/proposal/'.$this->projectId.'/info.status').'" data-rel="box" title="'.(in_array($this->proposalInfo->info->status, [1,3]) ? '' : 'ไม่สามารถแก้ไข').'" data-width="480"><span>'.$status[$this->proposalInfo->info->status].'</span></a>' : '<a class="btn -status-'.$this->proposalInfo->info->status.' title="'.(in_array($this->proposalInfo->info->status, [1,3]) ? '' : 'ไม่สามารถแก้ไข').'"><span>'.$status[$this->proposalInfo->info->status].'</span></a>',
				($isWebAdmin || $isInConsiderPeriod) && $isEditable && in_array($this->proposalInfo->info->status, [1,3]) ? '<a class="sg-action btn -primary" href="'.url('project/proposal/api/'.$this->projectId.'/status.set/2', ['rev' => 'yes']).'" data-rel="notify" data-done="reload" data-title="ส่งหลักสูตรให้พิจารณา" data-confirm="ได้ทำการเขียนหรือแก้ไขหลักสูตรเป็นที่เรียบร้อยแล้ว ต้องการส่งหลักสูตรให้คณะกรรมการพิจารณา กรุณายืนยัน?<br /><em>หมายเหตุ: หลังจากส่งหลักสูตรให้พิจารณาแล้ว จะไม่สามารถแก้ไขข้อเสนอหลักสูตรได้อีกจนกว่าเจ้าหน้าที่จะเปลี่ยนสถานะให้เป็นปรับแก้</em>"><i class="icon -material">send</i><span>ส่งให้พิจารณา</span></a>' : NULL,
				$isWebAdmin && in_array($this->proposalInfo->info->status, [2]) ? new Row([
					'children' => [
						'<a class="sg-action btn" href="'.url('project/proposal/api/'.$this->projectId.'/status.set/3').'" data-rel="notify" data-done="reload" data-title="ปรับแก้หลักสูตร" data-confirm="หลักสูตรต้องมีการปรับปรับ/แก้ไข กรุณายืนยัน?"><i class="icon -material">tune</i><span>ปรับแก้</span></a>',
						'<a class="sg-action btn" href="'.url('project/proposal/api/'.$this->projectId.'/status.set/5').'" data-rel="notify" data-done="reload" data-title="หลักสูตรผ่านการพิจารณา" data-confirm="หลักสูตรได้รับการอนุมัติจากกรรมการเป็นที่เรียบร้อยแล้ว กรุณายืนยัน?"><i class="icon -material">verified</i><span>ผ่านการพิจารณา</span></a>',
						// '<a class="sg-action btn" href="'.url('project/proposal/api/'.$this->projectId.'/status.set/2').'" data-rel="notify" data-done="reload" data-title="รับหลักสูตรใว้พิจารณา" data-confirm="หลักสูตรได้พัฒนาเป็นที่เรียบร้อยแล้ว ต้องการรับหลักสูตรไว้ให้คณะกรรมการพิจารณา กรุณายืนยัน?"><i class="icon -material">verified</i><span>รับไว้พิจารณา</span></a>',
						'<a class="sg-action btn" href="'.url('project/proposal/api/'.$this->projectId.'/status.set/8').'" data-rel="notify" data-done="reload" data-title="หลักสูตรไม่ผ่านการพิจารณา" data-confirm="หลักสูตรไม่ผ่านการพิจารณาจากคณะกรรมการ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i><span>ไม่ผ่านการพิจารณา</span></a>'
					]
				]) : NULL,
				$this->proposalInfo->info->followId ? '<a class="btn" href="'.url('project/'.$this->projectId).'"><i class="icon -material">school</i><span>ติดตามหลักสูตร</span></a>' : NULL,
				$isWebAdmin && in_array($this->proposalInfo->info->status, [5]) && !$this->proposalInfo->followId ? '<a class="sg-action btn" href="'.url('project/proposal/api/'.$this->projectId.'/follow.make').'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId.'/info.nxt.period').'" data-width="480" data-title="สร้างติดตามหลักสูตร" data-confirm="หลักสูตรผ่านการพิจารณาเรียบร้อย ต้องการสร้างเป็นติดตามหลักสูตร กรุณายืนยัน?<br /><em>หมายเหตุ: สามารถสร้างเป็นติดตามหลักสูตรได้เพียงครั้งเดียวเท่านั้น</em>"><i class="icon -material -green">verified</i><span>สร้างติดตามหลักสูตร</span></a>' : NULL,
				// ...['<a href="">รับไว้พิจารณา</a>','<a href="">ไม่ผ่านการพิจารณา</a>'],
				new DropBox([
					'children' => [
						$this->proposalInfo->info->followId ? '<a href="'.url('project/'.$this->projectId).'"><i class="icon -material">school</i><span>ติดตามหลักสูตร</span></a>' : NULL,
						$isWebAdmin && in_array($this->proposalInfo->info->status, [5]) && !$this->proposalInfo->followId ? '<a class="sg-action" href="'.url('project/proposal/api/'.$this->projectId.'/follow.make').'" data-rel="notify" data-done="reload:'.url('project/'.$this->projectId).'" data-width="480" data-title="สร้างติดตามหลักสูตร" data-confirm="หลักสูตรผ่านการพิจารณาเรียบร้อย ต้องการสร้างเป็นติดตามหลักสูตร กรุณายืนยัน?<br /><em>หมายเหตุ: สามารถสร้างเป็นติดตามหลักสูตรได้เพียงครั้งเดียวเท่านั้น</em>"><i class="icon -material">verified</i><span>สร้างติดตามหลักสูตร</span></a>' : NULL,
					] + $revList, // children
				]), // DropBox
			];
		} else {
			$trailingNav = [
				'rev-current' => '<a class="btn" href="'.url('project/proposal/'.$this->projectId).'"><i class="icon -material">chevron_left</i><span>Back to Current Version</span></a>',
				new DropBox([
					'children' => $revList
				]), // DropBox
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'class' => '-proposal',
				'title' => $this->proposalInfo->title.(cfg('project')->proposal->showParentInTitle ? ' ('.$this->proposalInfo->parentTitle.')' : ''),
				'leading' => '<i class="icon -material">tune</i>',
				'trailing' => new Row([
						'class' => '-no-print',
						'crossAxisAlignment' => 'center',
						'children' => $trailingNav,
				]), // Row
				'navigator' => new ProjectProposalNavWidget($this->proposalInfo, ['showPrint' => true]),
			]),
			'body' => new Container([
				'id' => 'project-proposal-info',
				'class' => 'project-proposal-info'.($this->editMode ? ' sg-inline-edit' : ''),
				'attribute' => $inlineAttr,
				'children' => (function() {
					$childrens = [];
					$proposalConfig = cfg('project')->proposal;

					if ($proposalConfig->showLikeStatus) {
						$childrens['like'] = new ScrollView([
							'class' => '-noprint',
							'child' => new ProjectLikeStatusWidget([
								'action' => 'PDEV',
								'projectInfo' => $this->proposalInfo,
							]),
						]);
					}

					if ($proposalConfig->showProceedStatus) {
						$childrens['processStatus'] = new ScrollView([
							'child' => new ProjectProposalProgressWidget($this->proposalInfo),
						]);
					}

					if ($this->proposalInfo->info->refNo) {
						$childrens['refno'] = new Card([
							'class' => '-sg-text-right -sg-paddingnorm',
							'child' => '<b>รหัสข้อเสนอโครงการ : '.$this->proposalInfo->info->refNo.'</b>',
						]);
					}

					$titleNo = 0;

					foreach (explode(',',$proposalConfig->infoUse) as $section) {
						$item = $proposalConfig->info->{$section};
						if (is_null($item) || is_null($widget = R::PageWidget($item->page, [$this->proposalInfo]))) continue;

						$childrens[$section] = new Card([
							'tagName' => 'section',
							'id' => 'project-proposal-info-section-'.$section,
							'class' => 'project-proposal-info-section -'.$section,
							'children' => [
								new ListTile([
									'class' => '-section-title -sg-paddingnorm',
									'title' => ++$titleNo.'. '.$item->title,
									'leading' => $item->icon ? $item->icon : '<i class="icon -material">stars</i>',
									'trailing' => new Row([
										'class' => '-no-print',
										'children' => [
											'<a class="sg-expand btn -link" data-rel=".-section-review" title="ความเห็นคณะกรรมการ"><i class="icon -material'.($this->hasReview ? ' -green' : '').'">reviews</i></a>',
											'<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
										],
									]), // Row
								]), // ListTile
								new Container([
									'class' => '-section-detail',
									'child' => $widget,
								]), // Container
								new Container([
									'id' => 'project-proposal-info-review-'.$section,
									'class' => '-section-review',
									'child' => new ProjectProposalInfoReview($this->proposalInfo, SG\getFirst($item->id, $section)),
								]), // Container
								// new DebugMsg($item,'$item'),
							], // children
						]);
					}

					if ($this->editMode) {
						$childrens['edit-button'] = new FloatingActionButton([
							'class' => '-noprint',
							'child' => '<a class="sg-action btn -primary" href="'.url('project/proposal/'.$this->projectId, ['debug' => post('debug')]).'" data-rel="#main"><i class="icon -material">done_all</i><span>เรียบร้อย</span></a>',
						]);
					} else if ($this->isEditable) {
						$childrens['edit-button'] = new FloatingActionButton([
							'class' => '-noprint',
							'child' => '<a class="sg-action btn -floating" href="'.url('project/proposal/'.$this->projectId, ['mode' => 'edit', 'debug' => post('debug')]).'" data-rel="#main"><i class="icon -material">edit</i><span>แก้ไข</span></a>',
						]);
					}

					$childrens['script'] = $this->script();

					return $childrens;
				})(), // children
			]), // Container
		]);
	}

	function script() {
		head('
		<style type="text/css">
		</style>');
	}
}
?>