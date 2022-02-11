<?php
/**
* Project :: Proposal App Bar Navigator Widget
* Created 2021-11-08
* Modify  2021-11-08
*
* @param Object $projectInfo
* @return Widget
*
* @usage new ProjectProposalNavWidget({},[])
*/

$debug = true;

class ProjectProposalNavWidget extends Widget {
	var $projectId;
	var $options;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $options = []) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->options = SG\json_decode($options);
		$this->right = (Object) [
			'access' => $this->projectInfo->RIGHT & _IS_ACCESS,
			'dashboard' => $this->projectInfo->RIGHT & _IS_RIGHT,
		];
}

	function build() {
		$isAdmin = $this->projectInfo->RIGHT & _IS_ADMIN;
		$isEdit = $this->projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);

		$proposalConfig = cfg('project')->proposal;

		if (!$proposalConfig->showAppBarNavigator) return NULL;

		$children = [];
		if ($proposalConfig->showAppBarMainNavigator) {
			$children['main'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-main',
				'children' => (function() {
					$childrens = [];
					if ($this->projectInfo->orgId) {
						$childrens[] = '<a href="'.url('org/'.$this->projectInfo->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>';
					} else if ($this->projectInfo->info->projectset) {
						$childrens[] = '<a href="'.url('project/'.$projectInfo->info->projectset).'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>';
					} else {
						$childrens[] = '<a href="'.url('project').'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>';
					}
					return $childrens;
				})(),
			]);
		}

		if ($this->projectId) {
			$children['info'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-info',
				'children' => (function() {
					$childrens = [];

					// Show button in proposal navigator config
					foreach (cfg('project')->proposal->navigator as $navKey => $item) {
						if ($item->access) {
							if (!defined($item->access)) continue;
							else if (!($this->projectInfo->RIGHT & constant($item->access))) continue;
						}
						if ($item->attribute->options) $item->attribute->options = json_encode($item->attribute->options);
						$childrens[$navKey] = '<a href="'.url('project/proposal/'.$this->projectId.($item->url ? '/'.$item->url : '')).'" title="'.$item->title.'" '.sg_implode_attr($item->attribute).'><i class="icon -material">'.$item->icon.'</i><span>'.$item->label.'</span></a>';
					}

					// Show dashboard button
					if ($this->right->dashboard) {
						$childrens['dashboard'] = '<a class="sg-action" href="'.url('project/proposal/'.$this->projectId.'/info.dashboard').'" data-rel="#main" rel="nofollow" title="แผงควบคุมโครงการ"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>';
					}

					// Show print button
					if ($this->options->showPrint) {
						$childrens[] = '<sep>';
						$childrens['print'] = '<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>';
					}

					return $childrens;
				})(),
			]);
		} else {
			$children['info'] = new Row([
				'class' => '-info',
				'children' => [],
			]);
		}

		return new Widget([
			'children' => $children,
		]);
	}
}
?>