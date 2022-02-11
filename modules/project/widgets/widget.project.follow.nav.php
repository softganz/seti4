<?php
/**
* Project :: Follow App Bar Navigator Widget
* Created 2021-10-27
* Modify  2022-02-10
*
* @param Object $projectInfo
* @param Array $options
* @return Widget
*
* @usage new ProjectFollowNavWidget({},[])
*/

class ProjectFollowNavWidget extends Widget {
	var $projectId;
	var $options;
	var $right;
	var $projectInfo;

	function __construct($projectInfo = NULL, $options = []) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->options = SG\json_decode($options);
		$this->right = (Object) [
			'access' => $this->projectInfo->RIGHT & _IS_ACCESS,
		];
	}

	function build() {
		switch ($this->projectInfo->info->prtype) {
			case 'แผนงาน': $followConfig = cfg('project')->planning; break;
			case 'ชุดโครงการ': $followConfig = cfg('project')->set; break;
			default: $followConfig = cfg('project')->follow; break;
		}

		if (!$followConfig->showAppBarNavigator) return NULL;

		$children = [];
		if ($followConfig->showAppBarMainNavigator) {
			$children['main'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-main',
				'children' => (function($followConfig) {
					$childrens = [];
					if ($this->projectInfo->orgId) {
						$childrens[] = '<a href="'.url($followConfig->homeUrl.'/'.$this->projectInfo->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>';
					} else if ($this->projectInfo->info->projectset) {
						$childrens[] = '<a href="'.url('project/'.$projectInfo->info->projectset).'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>';
					} else {
						$childrens[] = '<a href="'.url('project').'"><i class="icon -material">home</i><span class="">หน้าหลัก</span></a>';
					}
					return $childrens;
				})($followConfig),
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
						$url = $menuItem->url;
						$url = preg_replace('/\{id\}/', $this->projectId, $url);

						if ($menuItem->access) {
							if (!defined($menuItem->access)) continue;
							else if (!($this->projectInfo->RIGHT & constant($menuItem->access))) continue;
						}
						$childrens[$navKey] = '<a href="'.url($url ? $url : '').'" title="'.$menuItem->title.'" '.sg_implode_attr($menuItem->attribute).'><i class="icon -material">'.$menuItem->icon.'</i><span>'.$menuItem->label.'</span></a>';
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