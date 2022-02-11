<?php
/**
* Project :: Organization Map
* Created 2021-05-31
* Modify  2021-11-02
*
* @param String $arg1
* @return Widget
*
* @usage project/map/org
*/

$debug = true;

class ProjectMapOrg extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		$map = [
			'zoom' => 6,
			'center' => ['lat' => 13.2000, 'lng' => 100.0000],
			'dropPin' => false,
			'drag' => 'map',
			'pin' => [],
			'markers' => [],
		];

		if (!mydb::table_exists('%co_subdistloc%')) return message('error', 'ไม่มีข้อมูลพิกัดอำเภอ');

		if (post('s')) mydb::where('o.`sector` = :sector', ':sector', post('s'));
		$stmt = 'SELECT
			o.`orgId`
			, o.`name` `orgName`
			--	, X(p.location) lat, Y(p.location) lng, p.project_status, p.project_status+0 project_statuscode,
			, cos.`lat`
			, cos.`lng`
			, cop.`provname` `changwatName`
			, o.`location`
			FROM %db_org% o
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
				LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = LEFT(o.`areacode`, 6)
			%WHERE%
			GROUP BY o.`orgid`
			ORDER BY CONVERT(`name` USING tis620) ASC
		';

		$orgDbs = mydb::select($stmt);

		// debugMsg(mydb()->_query);
		// debugMsg($orgDbs,'$orgDbs');

		foreach ($orgDbs->items as $item) {
			if ($item->location) {
				list($lat, $lng) = explode(',', $item->location);
			} else {
				$lat = $item->lat;
				$lng = $item->lng;
			}

			if (!$item->lat && !$lng) continue;

			$map['markers'][] = [
				'lat' => floatval($lat),
				'lng' => floatval($lng),
				'title' => $item->orgName,
				'icon' => '',
				// 'content' => '<h5><a href="'.url('org/'.$item->orgId).'" target="_blank">'.$item->orgName.'</a></h5>',
				'content'=>'<h4><a href="'.url('org/'.$item->orgId).'" target="_blank">'.$item->orgName.'</a></h4>'
					. '<p>จังหวัด'.$item->changwatName.'<br />'
					. 'แผนงาน : '.$item->planningCount.'<br />'
					. 'พัฒนาโครงการ : '.$item->developCount.'<br />'
					. 'ติดตามโครงการ '.$item->projectCount.'<br />'
					. '<a href="'.url('org/'.$item->orgId).'" target="_blank">รายละเอียดองค์กร</a>'
					. '</p>',
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่องค์กร',
			]),
			'body' => new Container([
				'id' => 'map',
				'class' => 'app-output',
				'children' => [
					new Ui([
						'type' => 'menu',
						'children' => array_map(
							function($value, $key) {
								return '<a href="'.url('project/map/org', ['s' => $key]).'"><span class="" style="width:20px;height:20px;display:inline-block;background:#FD675B;border-radius:20px;border:2px #fff solid; text-align:center; line-height:20px;">'.($key).'</span> '.$value.'</a>';
							},
							project_base::$orgTypeList,
							array_keys(project_base::$orgTypeList)
						), // children
					]), // Ui
					'<div id="map-canvas" class="map-canvas">กำลังโหลดแผนที่!!!!</div>',
					$this->script($map),
					// new DebugMsg($map, '$map'),
				], // children
			]), // Container
		]);
	}

	function script($map) {
		return '
		<style type="text/css">
		.app-output {position:relative;}
		.app-output .ui-menu {padding:8px;position: absolute; z-index:1; top:8px; right:8px; border-radius:2px; background-color:#fff; opacity:0.9;}
		</style>
		<script type="text/javascript">
			let orgMap
			$.getScript("/js/gmaps.js", function(data, textStatus, jqxhr) {loadGoogleMaps("initMap")})
			function initMap() {
				orgMap = new sgDrawMap("orgMap",'.json_encode($map).');
			}
		</script>';
	}
}
?>