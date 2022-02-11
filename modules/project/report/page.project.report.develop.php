<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_develop($self) {
	R::View('project.toolbar', $self, 'โครงการอยู่ระหว่างการพัฒนา', 'report');

	$stmt='SELECT t.*, u.`name` FROM %topic% t LEFT JOIN %users% u USING(uid) WHERE `type`="project-develop" ORDER BY `changed` DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->caption='รายชื่อโครงการกำลังพัฒนา';
	$tables->thead=array('no'=>'', 'date created'=>'วันที่เริ่มพัฒนา', 'date changed'=>'แก้ไขล่าสุด', 'title'=>'ชื่อโครงการ','พัฒนาโดย');
	$no=0;
	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				sg_date($rs->created,'ว ดดด ปปปป'),
				$rs->changed?sg_date($rs->changed,'ว ดดด ปปปป H:i').' น.':'',
				'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'.'<br /><span>'.$rs->area.'</span>',
				$rs->name,
			);
		}
	} else {
		$tables->rows[]=array('<td colspan="3">ไม่มีโครงการที่กำลังพัฒนา <a href="'.url('project/develop/create').'">คลิกที่นี่</a> เพื่อเริ่มต้นพัฒนาโครงการใหม่</td>');
	}
	$ret .= $tables->build();
	return $ret;
}
?>