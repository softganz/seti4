<?php
/**
* Project :: Good Project Report
* Created 2021-08-02
* Modify  2021-08-02
*
* @param Array $_REQUEST
* @return Widget
*
* @usage project/report/goodproject
*/

$debug = true;

class ProjectReportGoodProject extends Page {
	function build() {
		$year = post('y');
		$group = post('i');
		$changwat = post('p');

		$parts = [
			'5.1'=>'เกิดความรู้ หรือ นวัตกรรมชุมชน',
			'5.2'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'5.3'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ',
			'5.4'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'5.5'=>'เกิดกระบวนการชุมชน',
			'5.6'=>'มิติสุขภาวะปัญญา/สุขภาวะทางจิตวิญญาณ'
		];

		mydb::where('tr.`formid` IN ("ประเมิน","valuation") AND tr.`rate1`=1');
		if ($year) mydb::where('p.`pryear` = :year',':year',$year);
		if ($group) mydb::where('tr.`part` LIKE :part',':part',$group.'%');
		if ($changwat) mydb::where('p.`changwat` = :changwat',':changwat',$changwat);

		$stmt = 'SELECT
				tr.`trid`, tr.`tpid`, tr.`formid`, tr.`part`
			, tr.`rate1`
			, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
			, X(p.`location`) lat, Y(p.`location`) lng
			, p.`project_status`
			, p.`project_status`+0 `project_statuscode`
			, p.`changwat`, cop.`provname`
			FROM %project_tr% tr
				LEFT JOIN %topic% t USING(tpid)
				LEFT JOIN %project% p USING(tpid)
				LEFT JOIN %co_province% cop ON cop.`provid` =p.`changwat`
			%WHERE%
			GROUP BY tr.`tpid`
			ORDER BY p.`pryear` DESC, CONVERT(`provname` USING tis620) ASC, CONVERT(t.`title` USING tis620) ASC';
		$dbs = mydb::select($stmt);
		//$ret .= print_o($dbs,'$dbs');





		$iconPart='https://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_';
		$icons=array('green','purple','yellow','blue','red','orange','brown');
		if ($_REQUEST['g']) $self->theme->title.=' : '.$parts[$_REQUEST['g']];

		$gis['center']='13.604486109074745,100.1000';
		$gis['zoom']=6;


		$tables = new Table();
		$tables->id='project-list';
		$tables->thead=array('no'=>'','ข้อตกลงเลขที่','รหัสโครงการ','ปี','จังหวัด','ชื่อโครงการ','สถานะโครงการ');
		$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
		$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
		$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
		$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';

		foreach ($dbs->items as $rs) {
			if ($rs->lat && $rs->lng) {
				$icon = 'https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
				$icon = $iconPart.($icons[substr($_REQUEST['g'],2,1)+0]).'.png';
				$gis['markers'][] = [
					'latitude' => $rs->lat,
					'longitude' => $rs->lng,
					'title' => $rs->title,
					'icon' => $icon,
					'content' => '<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'" target="_blank">รายละเอียดโครงการ</a> | <a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" target="_blank">คุณค่าของโครงการ</a></p>'
				];
			}
			$tables->rows[] = [
				++$no,
				$rs->agrno,
				$rs->prid,
				$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
				$rs->provname,
				'<a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a>',$rs->project_status,
				'config' => ['class' => 'project-status-'.$rs->project_statuscode],
			];
		}


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนที่การประเมินคุณค่าโครงการ',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'variable' => 'report',
						'action' => url(q()),
						'id' => 'project-report',
						'class' => 'form -inlineitem',
						'method' => 'GET',
						'children' => [
							'year' => [
								'type' => 'select',
								'name' => 'y',
								'options' => (function() {
									$options = [NULL => '--- ทุกปี ---'];
									foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items as $item) {
										$options[$item->pryear]='พ.ศ. '.($item->pryear+543);
									}
									return $options;
								})(),
								'value' => $year,
							],
							'province' => [
								'type' => 'select',
								'name' => 'p',
								'options' => (function() {
									$options = [NULL => '--- ทุกจังหวัด ---'];
									$dbs = mydb::select('SELECT DISTINCT `changwat`, `provname` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
									foreach ($dbs->items as $rs) {
										$options[$rs->changwat]=$rs->provname;
									}
									return $options;
								})(),
								'value' => $changwat,
							],
							'inno' => [
								'type' => 'select',
								'name' => 'i',
								'options' => (function($parts) {
									$options = [NULL => '--- ทุกนวัตกรรม ---'];
									foreach ($parts as $key=>$item) {
										$options[$key]=$item;
									}
									return $options;
								})($parts),
								'value' => $group,
							],
							'go' => [
								'type' => 'button',
								'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
							],
						], // children
					]), // Form
					'<div class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL,
					(function($parts) {
						$ret = '<ul class="project-list">'._NL;
						foreach ($parts as $k => $v) {
							$ret .= '<li><p><a href="'.url('project/report/goodproject','g='.$k).'"><span style="width:20px;height:20px;display:inline-block;background:#FD675B;border-radius:20px;border:2px #fff solid;">'.substr($k,-1).'</span><br />'.$v.'</a></p></li>'._NL;
						}
						$ret .= '</ul>'._NL;
						return $ret;
					})($parts),

					$tables->build(),

					$this->_script($gis),
				], // children
			]), // Widget
		]);
	}

	function _script($gis) {
		head('jquery.ui.map','<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
		head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

		return '<script type="text/javascript"><!--
			$(document).ready(function() {
			var imgSize = new google.maps.Size(12, 20);
			var gis='.json_encode($gis).';
			var is_point=false;
			$map=$(".app-output");
			$map.gmap({
					center: gis.center,
					zoom: gis.zoom,
					scrollwheel: false
				})
				.bind("init", function(event, map) {
					if (gis.markers) {
						$.each( gis.markers, function(i, marker) {
							$map.gmap("addMarker", {
								position: new google.maps.LatLng(marker.latitude, marker.longitude),
								icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
								draggable: false,
							}).mouseover(function() {
								$map.gmap("openInfoWindow", { "content": marker.content }, this);
							});
						});
					}
				})
			});
			--></script>';
	}
}
?>