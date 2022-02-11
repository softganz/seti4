<?php
/**
* Project owner
*
* @param Object $self
* @param Object $para
* @return String
*/
function project_list_v1($self, $prjSetId = NULL) {
	$self->theme->title = 'รายชื่อโครงการ';
	$para = para('order=t.tpid','sort=DESC','items=10000');

	$projectset = SG\getFirst($prjSetId,post('set'));
	$year = post('year');
	$province = post('province');
	$trainer = post('trainer');
	$owner = post('owner');
	$u = post('u');
	$zone = post('zone');

	$para->set = $projectset;
	if ($prjSetId) $projectInfo = R::Model('project.get',$prjSetId, '{initTemplate: true}');

	if (empty($year)) $year = SG\getFirst(property('project:year:0'),date('Y'));

	if ($trainer) {
		$para->trainer = post('trainer');
	} else if (post('owner')) {
		$para->owner = post('owner');
		unset($year);
	} else if ($u) {
		$para->u = post('u');
		unset($year);
	}
	if ($year) $para->year = $year;




	if ($year) {
		$ui=new Ui();

		if (!$trainer && !$owner && !$u) {
			$zoneList=cfg('zones');

			mydb::where('p.`prtype`="โครงการ"');
			if ($projectset) mydb::where('`projectset`=:projectset ',':projectset',$projectset);

			$provSelect.='<form method="get" action="'.url('project/list/v1').'"><input type="hidden" name="year" value="'.$year.'" />';
			if (post('order')) $provSelect.='<input type="hidden" name="order" value="'.htmlspecialchars(post('order')).'" />';
			if ($projectset) $provSelect.='<input type="hidden" name="set" value="'.$projectset.'" />';
			if ($zoneList) {
				$provSelect.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');this.form.submit();return false;"><option value="">ทุกภาค</option>';
				foreach ($zoneList as $zoneKey => $zoneItem) {
					$provSelect.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
				}
				$provSelect.='</select> ';
			}


			if ($zone) mydb::where('LEFT(p.`changwat`,1) IN ('.$zoneList[$zone]['zoneid'].')');
			$stmt='SELECT DISTINCT `changwat`, `provname`
							FROM %project% p
								LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
							%WHERE%
							HAVING `provname`!=""
							ORDER BY CONVERT(`provname` USING tis620) ASC;
							-- {reset:false}';
			$dbs=mydb::select($stmt);
			$provSelect.='<select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
			foreach ($dbs->items as $rs) {
				$provSelect.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
			}
			$provSelect.='</select>';
			$provSelect.='</form>';
			$ui->add($provSelect);
		}

		$stmt='SELECT DISTINCT `pryear` FROM %project% WHERE `prtype`="โครงการ" HAVING `pryear` ORDER BY `pryear` DESC';
		$stmt='SELECT `pryear`, COUNT(*) amt
						FROM %project% p
						%WHERE%
						GROUP BY `pryear`
						ORDER BY `pryear` DESC;
						-- {reset:false}';
		$ui->add('<a class="btn'.($year == '*' ? ' -active' : '').' -year-all" href="'.url('project/list/v1',array('set'=>$projectset, 'year'=>'*','province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'), 'order'=>post('order'))).'">ทุกปี</a>');
		foreach (mydb::select($stmt)->items as $v) {
			$ui->add('<a class="btn'.($year == $v->pryear ? ' -active' : '').' -year-'.$v->pryear.'" href="'.url('project/list/v1',array('set'=>$projectset, 'year'=>$v->pryear,'province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'), 'order'=>post('order'))).'">ปี '.sg_date($v->pryear,'ปปปป').'</a>');
		}
		$ret.='<nav class="nav -page -project-list">'.$ui->build('ul').'</nav>';
	}



	$ret .= R::Page('project.search',$self,$para);
	//$ret .= '<pre>'.mydb()->_query.'</pre>';
	//$ret .= print_o(post(),'post()');


	if (!$self->theme->title) $self->theme->title = 'รายชื่อโครงการ';

	return $ret;
}
?>