<?php
/**
* Project :: Follow View Information
* Created 2021-10-25
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}
*/

import('widget:project.info.appbar.php');
import('widget:project.like.status.php');

class ProjectSetInfoView extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$isAdmin = $this->projectInfo->info->isAdmin;
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';
		$isInEditMode = $isEdit;

		R::Model('reaction.add', $this->projectId, 'TOPIC.VIEW');

		$inlineAttr = [];
		if ($isInEditMode) {
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr/'.$this->projectId);
			$inlineAttr['data-refresh-url'] = url('project/'.$this->projectId, ['debug' => post('debug')]);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}


		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Container([
				'id' => 'project-set-info',
				'class' => 'project-set-info'.($isInEditMode ? ' sg-inline-edit' : ''),
				'attribute' => $inlineAttr,
				'children' => (function() {
					$childrens = [];
					$projectConfig = cfg('project')->set;


					//////////
					// if (i()->username == 'softganz') {
						// debugMsg(cfg(),'cfg()');
						// debugMsg($projectConfig,'$projectConfig');
					// }

					if ($projectConfig->showLikeStatus) {
						$childrens['like'] = new ScrollView([
							'child' => new ProjectLikeStatusWidget([
								'projectInfo' => $this->projectInfo,
							]),
						]);
					}

					if ($projectConfig->showProceedStatus) {
						$childrens['processStatus'] = new ScrollView([
							'child' => R::View('project.set.statusbar', $this->projectInfo),
						]);
					}

					$titleNo = 0;

					foreach (explode(',',$projectConfig->infoUse) as $section) {
						$item = $projectConfig->info->{$section};
						if (is_null($item) || is_null($widget = R::PageWidget($item->page, [$this->projectInfo]))) continue;

						$childrens[$section] = new Card([
							'tagName' => 'section',
							'id' => 'project-set-info-section-'.$section,
							'class' => 'project-set-info-section -'.$section,
							'children' => [
								new ListTile([
									'class' => '-sg-paddingnorm',
									'title' => ++$titleNo.'. '.$item->title,
									'leading' => '<i class="icon -material">stars</i>',
									'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
								]), // ListTile
								$widget,
								// new DebugMsg($item,'$item'),
							], // children
						]);
					}

					$childrens['creater'] = new Card([
						'id' => 'project-info-creater',
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => '<a href="'.url('project/list', ['u' => $this->projectInfo->info->uid]).'" title="'.htmlspecialchars($this->projectInfo->info->ownerName).'">'.$this->projectInfo->info->ownerName.'</a>',
								'leading' => '<a href="'.url('project/list', ['u' => $this->projectInfo->info->uid]).'" title="'.htmlspecialchars($this->projectInfo->info->ownerName).'"><img class="profile-photo" src="'.model::user_photo($this->projectInfo->info->username).'" width="32" height="32" alt="'.htmlspecialchars($this->projectInfo->info->ownerName).'" /></a>',
								'trailing' => '<a class="sg-expand btn -link -no-print" href="javascript:void(0)"><icon class="icon -material">expand_less</i></a>',
							]), // ListTile
							'<p class="-sg-paddingnorm">โครงการเข้าสู่ระบบเมื่อวันที่ '.sg_date($this->projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</p>',
						], // children
					]);
					$childrens['script'] = $this->script();

					return $childrens;
				})(), // children
			]),
		]);
	}

	function script() {
	}
}
?>