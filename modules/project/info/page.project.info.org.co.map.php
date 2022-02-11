<?php
/**
* Project :: Map of Co-Organization
* Created 2021-10-11
* Modify  2021-10-11
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.org.co.map
*/

$debug = true;

import('model:project.follow.php');

class ProjectInfoOrgCoMap extends Page {
	var $projectId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = $projectInfo->right;
	}

	function build() {
		if (!$this->projectId) return message('error', 'PROCESS ERROR');

		$map = [
			'zoom' => 6,
			'center' => ['lat' => 13.2000, 'lng' => 100.0000],
			'dropPin' => false,
			'drag' => 'map',
			'pin' => [],
			'markers' => [],
		];

		foreach (ProjectFollowModel::getOrgCo(['projectId' => $this->projectId])->items as $item) {
			if (!$item->location) continue;
			list($currentLat, $currentLnt) = explode(',', $item->location);
			$map['markers'][] = [
				'lat' => floatval($currentLat),
				'lng' => floatval($currentLnt),
				'title' => $item->orgName,
				'content' => '<h5><a href="'.url('org/'.$item->orgId).'" target="_blank">'.$item->orgName.'</a></h5>'.$item->detail,
				'icon' => '',
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่องค์กรร่วม',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Container([
				'id' => 'map',
				'class' => 'app-output',
				'children' => [
					'<div id="map-canvas" class="map-canvas">กำลังโหลดแผนที่!!!!</div>',
					'<script type="text/javascript">
					let orgMap
					$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})
					function initMap() {
						orgMap = new sgDrawMap("orgMap",'.json_encode($map).');
					}
					</script>',
					// new DebugMsg($map, '$map'),
				], // children
			]), // Container
		]);
	}
}
?>