<?php
/**
* Project :: List of follow project
*
* @param Int $prjSetId
* @return Widget
*/

class ProjectList extends Page {
	var $prjSetId;
	function __construct($prjSetId = NULL) {
		$this->prjSetId = $prjSetId;
	}

	function build() {
		R::View('project.toolbar',$self,'รายชื่อโครงการ');

		$getZone = post('zone');
		$prjSetId = SG\getFirst($this->prjSetId, post('set'));

		$cfg = cfg('project');

		$listFilter = explode(',', $cfg->list->filter);

		$selectProvince = Array();
		foreach (mydb::select('SELECT p.`changwat`, `provname` `name` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` WHERE `prtype` = "โครงการ" AND `provname` IS NOT NULL GROUP BY `changwat` ORDER BY CONVERT(`name` USING tis620)')->items as $key => $item) {
			$selectProvince[$item->changwat] = $item->name;
		}

		$selectSet = Array();
		foreach (mydb::select('SELECT `tpid`,`title` FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype` = "ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620)')->items as $key => $item) {
			$selectSet[$item->tpid] = $item->title;
			$checkboxSet .= '<label><input type="checkbox" name="for_set[]" value="'.$item->tpid.'" />'.$item->title.'</label>';
		}

		$selectYear = Array();
		foreach (mydb::select('SELECT p.`pryear` FROM %project% p WHERE `prtype` = "โครงการ" AND `pryear` IS NOT NULL GROUP BY `pryear` ORDER BY `pryear` DESC')->items as $key => $item) {
			$selectYear[$item->pryear] = 'พ.ศ.'.($item->pryear+543);
		}


		$selectNew = Array('โครงการใหม่' => 'โครงการใหม่', 'โครงการต่อเนื่อง' => 'โครงการต่อเนื่อง');
		$selectStatus = Array('กำลังดำเนินโครงการ'=>'กำลังดำเนินโครงการ','ดำเนินการเสร็จสิ้น'=>'ดำเนินการเสร็จสิ้น','ยุติโครงการ'=>'ยุติโครงการ','ระงับโครงการ'=>'ระงับโครงการ');


		$reportTypeArray = array();


		$reportBar = new Report(url('project/api/follow'), 'projet-list');

		$reportBar->addId('projet-list');
		$reportBar->addConfig('dataType', 'html');
		$reportBar->addConfig('filterPretext', '<span style="margin: 1px 8px 1px 0;">'
			. (post('u') ? '<input type="hidden" name="u" value="'.post('u').'" />' : '')
			. ($prjSetId ? '<input type="hidden" name="for_set" value="'.$prjSetId.'" />' : '')
			. '<input class="form-text" type="text" name="q" placeholder="ระบุชื่อโครงการ" />'
			. '</span>');
		$reportBar->addConfig('showPage', true);

		if (in_array('changwat', $listFilter)) {
			$reportBar->Filter('changwat', Array('text' => 'จังหวัด', 'filter' => 'for_changwat', 'select' => $selectProvince));
		}

		if (!$prjSetId && in_array('set', $listFilter)) {
			$reportBar->Filter('set', Array('text' => 'ชุดโครงการ', 'filter' => 'for_set', 'select' => $selectSet));
		}

		if (in_array('year', $listFilter)) {
			$reportBar->Filter('year', Array('text' => 'ปี พ.ศ.', 'filter' => 'for_year', 'select' => $selectYear));
		}

		if (in_array('new', $listFilter)) {
			$reportBar->Filter('new', Array('text' => 'ต่อเนื่อง', 'filter' => 'for_new', 'select' => $selectNew));
		}

		$reportBar->Filter('status', Array('text' => 'สถานะ', 'filter' => 'for_status', 'select' => $selectStatus));

		$reportBar->Output('html', '<div class="loader -rotate" style="width: 128px; height: 128px; margin: 64px auto; display: block;"></div>');

		$isCreateProject = user_access('create project content')
			&&  in_array('list', explode(',', cfg('PROJECT.PROJECT.ADD_FROM_PAGE')));

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายชื่อโครงการ',
				'navigator' => [
					R::View('project.nav.default', NULL),
				], // navigator
			]), // AppBar
			'children' => [
				$reportBar,
				$isCreateProject ? new FloatingActionButton([
					'children' => [
						'<a class="sg-action btn -floating" href="'.url('project/create/'.$tpid, array('rel' => 'box')).'" data-rel="box" data-width="640" title="Create New Project"><i class="icon -material">add</i><span>เพิ่มโครงการ</span></a>',
					],
				]) : NULL,
				$this->script(),
			], // children
		]);
	}

	function script() {
		head('<script type="text/javascript">
		$(document).ready(function() {
			var $sgDrawReport = $(".btn.-primary.-submit").sgDrawReport().doAction(null,\'{dataType: "html"}\')
			$(document).on("change",".sg-drawreport .-submit", function() {
				console.log("CHANGE ",$(this).val())
				$("#page").val("")
			});
		});
		</script>');
	}
}



function project_list_v1($self, $prjSetId = NULL) {
	// $self->theme->title = 'รายชื่อโครงการ';
	// $para = para('order=t.tpid','sort=DESC','items=10000');

	// $projectset = SG\getFirst($prjSetId,post('set'));
	// $year = post('year');
	// $province = post('province');
	// $trainer = post('trainer');
	// $owner = post('owner');
	// $u = post('u');
	// $zone = post('zone');

	// $para->set = $projectset;
	// if ($prjSetId) $projectInfo = R::Model('project.get',$prjSetId, '{initTemplate: true}');

	// if (empty($year)) $year = SG\getFirst(property('project:year:0'),date('Y'));

	// if ($trainer) {
	// 	$para->trainer = post('trainer');
	// } else if (post('owner')) {
	// 	$para->owner = post('owner');
	// 	unset($year);
	// } else if ($u) {
	// 	$para->u = post('u');
	// 	unset($year);
	// }
	// if ($year) $para->year = $year;


	// debugMsg(cfg('project'), 'cfg(project)');


	// if ($year) {
	// 	$ui=new Ui();

	// 	if (!$trainer && !$owner && !$u) {
	// 		$zoneList=cfg('zones');

	// 		mydb::where('p.`prtype`="โครงการ"');
	// 		if ($projectset) mydb::where('`projectset`=:projectset ',':projectset',$projectset);

	// 		$provSelect.='<form method="get" action="'.url('project/list').'"><input type="hidden" name="year" value="'.$year.'" />';
	// 		if (post('order')) $provSelect.='<input type="hidden" name="order" value="'.htmlspecialchars(post('order')).'" />';
	// 		if ($projectset) $provSelect.='<input type="hidden" name="set" value="'.$projectset.'" />';
	// 		if ($zoneList) {
	// 			$provSelect.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');this.form.submit();return false;"><option value="">ทุกภาค</option>';
	// 			foreach ($zoneList as $zoneKey => $zoneItem) {
	// 				$provSelect.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
	// 			}
	// 			$provSelect.='</select> ';
	// 		}


	// 		if ($zone) mydb::where('LEFT(p.`changwat`,1) IN ('.$zoneList[$zone]['zoneid'].')');
	// 		$stmt='SELECT DISTINCT `changwat`, `provname`
	// 						FROM %project% p
	// 							LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
	// 						%WHERE%
	// 						HAVING `provname`!=""
	// 						ORDER BY CONVERT(`provname` USING tis620) ASC;
	// 						-- {reset:false}';
	// 		$dbs=mydb::select($stmt);
	// 		$provSelect.='<select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
	// 		foreach ($dbs->items as $rs) {
	// 			$provSelect.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
	// 		}
	// 		$provSelect.='</select>';
	// 		$provSelect.='</form>';
	// 		$ui->add($provSelect);
	// 	}

	// 	$stmt='SELECT DISTINCT `pryear` FROM %project% WHERE `prtype`="โครงการ" HAVING `pryear` ORDER BY `pryear` DESC';
	// 	$stmt='SELECT `pryear`, COUNT(*) amt
	// 					FROM %project% p
	// 					%WHERE%
	// 					GROUP BY `pryear`
	// 					ORDER BY `pryear` DESC;
	// 					-- {reset:false}';
	// 	$ui->add('<a class="btn'.($year == '*' ? ' -active' : '').' -year-all" href="'.url('project/list',array('set'=>$projectset, 'year'=>'*','province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'), 'order'=>post('order'))).'">ทุกปี</a>');
	// 	foreach (mydb::select($stmt)->items as $v) {
	// 		$ui->add('<a class="btn'.($year == $v->pryear ? ' -active' : '').' -year-'.$v->pryear.'" href="'.url('project/list',array('set'=>$projectset, 'year'=>$v->pryear,'province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'), 'order'=>post('order'))).'">ปี '.sg_date($v->pryear,'ปปปป').'</a>');
	// 	}
	// 	$ret.='<nav class="nav -page -project-list">'.$ui->build('ul').'</nav>';
	// }



	// $ret .= R::Page('project.search',$self,$para);
	// //$ret .= '<pre>'.mydb()->_query.'</pre>';
	// //$ret .= print_o(post(),'post()');


	// if (!$self->theme->title) $self->theme->title = 'รายชื่อโครงการ';
	// return $ret;
}
?>