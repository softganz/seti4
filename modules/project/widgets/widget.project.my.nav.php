<?php
/**
* Project :: Follow App Bar Navigator Widget
* Created 2021-10-27
* Modify  2021-10-28
*
* @param Object $projectInfo
* @return Widget
*
* @usage new ProjectFollowNavWidget({},[])
*/

$debug = true;

class ProjectMyNavWidget extends Widget {
	var $options;
	// var $right;

	function __construct($projectInfo = NULL, $options = []) {
		$this->options = SG\json_decode($options);
		// $this->right = (Object) [
		// 	'access' => $this->projectInfo->RIGHT & _IS_ACCESS,
		// ];
	}

	function build() {
		$isAdmin = $this->projectInfo->RIGHT & _IS_ADMIN;
		$isEdit = $this->projectInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_TRAINER);
		$followConfig = cfg('project')->follow;

		if (!$followConfig->showAppBarNavigator) return NULL;

		$children = [];
		if ($followConfig->showAppBarMainNavigator) {
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
				'children' => (function($followConfig) {
					$childrens = [];

					// Show button in follow navigator config
					foreach (explode(',', $followConfig->navigatorUse) as $navKey) {
						$menuItem = $followConfig->navigator->{$navKey};
						if ($menuItem->access) {
							if (!defined($menuItem->access)) continue;
							else if (!($this->projectInfo->RIGHT & constant($menuItem->access))) continue;
						}
						$childrens[$navKey] = '<a href="'.url('project/'.$this->projectId.($menuItem->url ? '/'.$menuItem->url : '')).'" title="'.$menuItem->title.'" '.sg_implode_attr($menuItem->attribute).'><i class="icon -material">'.$menuItem->icon.'</i><span>'.$menuItem->label.'</span></a>';
					}

					// Show dashboard button
					if ($this->right->access) {
						$childrens['dashboard'] = '<a href="'.url('project/'.$this->projectId.'/info.dashboard').'" rel="nofollow" title="แผงควบคุมโครงการ"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>';
					}

					// Show print button
					if ($this->options->showPrint) {
						$childrens[] = '<sep>';
						$childrens['print'] = '<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>';
					}

					return $childrens;
				})($followConfig),
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