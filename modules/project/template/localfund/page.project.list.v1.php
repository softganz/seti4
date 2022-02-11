<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_list_v1($self,$para=NULL) {
	$self->theme->title='รายชื่อโครงการ';
	$para=para($para,'order=t.tpid','sort=DESC','items=10000');

	$projectset=SG\getFirst($para->set,post('set'));
	$year=SG\getFirst($para->year,post('year'));
	$province=SG\getFirst($para->province,post('province'));
	$trainer=SG\getFirst($para->trainer,post('trainer'));
	$owner=SG\getFirst($para->owner,post('owner'));
	$u=SG\getFirst($para->u,post('u'));
	$zone=post('zone');

	if (empty($year)) $year=SG\getFirst(property('project:year:0'),date('Y'));
	if ($trainer) {
		$para->trainer=post('trainer');
	} else if (post('owner')) {
		$para->owner=post('owner');
		unset($year);
	} else if ($u) {
		$para->u=post('u');
		unset($year);
	}
	if ($year) $para->year=$year;

	if ($year) {
		$ui=new ui();

		if (!$trainer && !$owner && !$u) {
			$zoneList=cfg('zones');
			$provSelect.='<form method="get" action="'.url('project/list/v1').'"><input type="hidden" name="year" value="'.$year.'" />';
			if ($zoneList) {
				$provSelect.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกภาค</option>';
				foreach ($zoneList as $zoneKey => $zoneItem) {
					$provSelect.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
				}
				$provSelect.='</select> ';
			}


			mydb::where('p.`prtype` = "โครงการ"');
			if ($zone) mydb::where('LEFT(p.`changwat`,1) IN (:zone)', ':zone','SET:'.$zoneList[$zone]['zoneid']);
			$stmt='SELECT DISTINCT `changwat`, `provname`
							FROM %project% p
								LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
							%WHERE%
							HAVING `provname`!=""
							ORDER BY CONVERT(`provname` USING tis620) ASC';
			$dbs=mydb::select($stmt);

			$provSelect.='<select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
			foreach ($dbs->items as $rs) {
				$provSelect.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
			}
			$provSelect.='</select>';

			$provSelect.='<select class="form-select" name="ampur"><option value="">==ทุกอำเภอ==</select>';

			$provSelect.='</form>';
			$ui->add($provSelect);
		}

		mydb::where('p.`prtype` = "โครงการ"');
		$stmt = 'SELECT DISTINCT p.`pryear` FROM %project% p %WHERE% HAVING `pryear` ORDER BY `pryear` DESC';
		$dbs = mydb::select($stmt);

		foreach ($dbs->items as $v) {
			$ui->add('<a href="'.url('project/list/v1',array('set'=>$projectset, 'year'=>$v->pryear,'province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'))).'">ปี '.sg_date($v->pryear,'ปปปป').'</a>');
		}
		$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';
	}
	$ret.=R::Page('project.search',$self,$para);
	if (!$self->theme->title) $self->theme->title='รายชื่อโครงการ';

	return $ret;
}
?>