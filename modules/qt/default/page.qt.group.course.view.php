<?php
/**
 * Assessor Take Course
 *
 * @param Integer $psnid
 * @param String $action
 * @param Integer $trid
 * @return String
 */
function qt_group_course_view($self,$qtref=NULL,$action=NULL,$trid=NULL) {
	$step=post('step');
	R::View('toolbar',$self,'บันทึกแบบประเมินผลหลักสูตร','qt.course');

	$isAdmin=user_access('admin');

	if (!$isAdmin) return message('error','access denied');


	$stmt='SELECT
					  q.*
					, t.`title`
					, u.`name`
					, CONCAT(p.`name`," ",p.`lname`) `fullname`
				FROM %qtmast% q
					LEFT JOIN %topic% t USING(tpid)
					LEFT JOIN %users% u ON u.`uid`=q.`uid`
					LEFT JOIN %db_person% p ON p.`uid`=q.`uid`
				WHERE q.`qtref`=:qtref LIMIT 1';
	$qtMast=mydb::select($stmt,':qtref',$qtref);

	$ret.='ชื่อหลักสูตร '.$qtMast->title.'<br />';
	$ret.='ผู้ประเมิน '.$qtMast->fullname.'<br />';
	$ret.='วันที่ประเมิน '.sg_date($qtMast->qtdate,'ว ดด ปปปป').'<br />';
	
	$qtTran=mydb::select('SELECT * FROM %qttran% WHERE `qtref`=:qtref',':qtref',$qtref);

	foreach ($qtTran->items as $rs) {
		$ret.='<p><b>'.$rs->part.'</b>'.($rs->rate!=''?'='.$rs->rate:'').'<br />'.$rs->value.'</p>';
	}
	//$ret.=print_o($qtTran);

	return $ret;
}

?>