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
function project_list($self,$para) {
	$self->theme->title='รายชื่อโครงการ';
	$para=para($para,'order=t.tpid','sort=DESC','items=1000');

	if (!user_access('access projects') && user_access('access own projects')) {
		unset($_REQUEST);
		$_REQUEST['u']=i()->uid;
	}

	$projectset=SG\getFirst($para->set,post('set'));
	$year=SG\getFirst($para->year,post('year'));
	$province=SG\getFirst($para->province,post('province'));
	$trainer=SG\getFirst($para->trainer,post('trainer'));
	$owner=SG\getFirst($para->owner,post('owner'));
	$u=SG\getFirst($para->u,post('u'));

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
			//$dbs=mydb::select('SELECT DISTINCT `changwat`, `provname` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` ORDER BY CONVERT(`provname` USING tis620) ASC');
			$dbs=mydb::select('SELECT DISTINCT cop.`provid`, `provname` FROM %project_prov% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` ORDER BY CONVERT(`provname` USING tis620) ASC');

			$provSelect.='<form method="get" action="'.url('project/list').'"><input type="hidden" name="year" value="'.$year.'" /><select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
			foreach ($dbs->items as $rs) {
				$provSelect.='<option value="'.$rs->provid.'"'.($rs->provid==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
			}
			$provSelect.='</select>';
			$provSelect.=' <select name="org" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกหน่วยงาน</option>';
			foreach (mydb::select('SELECT DISTINCT `orgid`,`name` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) LEFT JOIN %db_org% o USING(`orgid`) WHERE o.`orgid` IS NOT NULL ORDER BY o.`sector` ASC, CONVERT(`name` USING tis620) ASC')->items as $item) {
			$provSelect.='<option value="'.$item->orgid.'"'.(post('org')==$item->orgid?' selected="selected"':'').'>'.$item->name.'</option>';
			}
			$provSelect.='</select>';
			$provSelect.='</form>';
			$ui->add($provSelect);
		}

		foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% HAVING `pryear` ORDER BY `pryear` DESC')->items as $v) {
			$ui->add('<a href="'.url('project/list',array('set'=>$projectset, 'year'=>$v->pryear,'province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u)).'">ปี '.sg_date($v->pryear,'ปปปป').'</a>');
		}
		$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';
	}
	$ret.=R::Page('project.search',$self,$para);
	if (!$self->theme->title) $self->theme->title='รายชื่อโครงการ';

	return $ret;
}
?>