<?php
/**
* Project :: Planning Of Area
* Created 2020-06-20
* Modify  2021-06-16
*
* @return Widget
*
* โจทย์ : แผนกองทุนตำบลของทุกตำบลรวมกันเป็นแผนอำเภอ
* การทำแผนแต่ละประเด็นในระดับอำเภอ วิธีการคือ
* - สถานการณ์และเป้าหมาย เอาค่าเฉลี่ยจากกองทุนทุกตำบลในอำเภอนั้น
* - โครงการที่พัฒนา และโครงการที่ติดตาม รวมโครงการของทุกกองทุนตำบล เข้าด้วยกัน
*/

$debug = true;

class ProjectPlanningArea extends Page {
	function build() {
		$selectArea = Array();
		$selectProvince = Array();
		$selectSector = Array();
		$selectYear = Array();

		foreach (R::Model('category.get','project:planning','catid') as $key => $value) {
			$selectPlan[$key] = $value;
		}

		foreach (mydb::select('SELECT * FROM %project_area% ORDER BY areaid+0')->items as $item) {
			$selectArea[$item->areaid] = 'เขต '.$item->areaid.' '.$item->areaname;
		}

		foreach (mydb::select('SELECT p.`changwat`, `provname` `name` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` WHERE `prtype` = "แผนงาน" AND `provname` IS NOT NULL GROUP BY `changwat` ORDER BY CONVERT(`name` USING tis620)')->items as $key => $item) {
			$selectProvince[$item->changwat] = $item->name;
		}

		foreach (project_base::$orgTypeList as $key => $value) {
			$selectSector[$key] = $value;
		}

		foreach (mydb::select('SELECT `tpid`,`title` FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype` = "ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620)')->items as $key => $item) {
			$selectSet[$item->tpid] = $item->title;
			$checkboxSet .= '<label><input type="checkbox" name="for_set[]" value="'.$item->tpid.'" />'.$item->title.'</label>';
		}

		foreach (mydb::select('SELECT p.`pryear` FROM %project% p WHERE `prtype` = "แผนงาน" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` DESC')->items as $key => $item) {
			$selectYear[$item->pryear] = 'พ.ศ.'.($item->pryear+543);
		}


		$reportBar = new Report(url('project/api/planning/area'), 'projet-planning-area');

		$reportBar->addId('projet-planning-area');
		$reportBar->addConfig('showArrowLeft', true);
		$reportBar->addConfig('showArrowRight', true);

		$reportBar->Filter('plan', Array('text' => 'แผนงาน', 'filter' => 'for_plan', 'select' => $selectPlan, 'type' => 'radio'));
		$reportBar->Filter('year', Array('text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear, 'type' => 'radio'));
		$reportBar->Filter('area', Array('text' => 'เขต', 'filter' => 'for_area', 'select' => $selectArea, 'type' => 'radio'));
		$reportBar->Filter('changwat', Array('text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince, 'type' => 'radio'));
		$reportBar->Filter('sector', Array('text' => 'องค์กร', 'filter' => 'for_sector', 'select' => $selectSector, 'type' => 'radio'));

		$reportBar->Output('html', '<p class="notify">กรุณาเลือกแผนงาน และ ปี พ.ศ.</p>');


		head('<script type="text/javascript">
		$(document).ready(function() {
			//var $sgDrawReport = $(".btn.-primary.-submit").sgDrawReport().doAction()
		});
		</script>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนกองทุนระดับพื้นที่',
				'navigator' => [
					R::View('project.nav.planning'),
				], // navigator
			]), // AppBar
			'children' => [
				$reportBar,
			], // children
		]);
	}
}
?>