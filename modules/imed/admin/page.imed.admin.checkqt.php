<?php
/**
* Check disable questionair
*
* @param Object $self
* @param $_GET
* @return String
*/

function imed_admin_checkqt($self) {
	$filter = post('f');
	$part = post('p');
	$action = post('action');

	$selfUrl = 'imed/admin/checkqt';

	if ($rs->_empty)
		return message('error','ไม่มีข้อมูลของรายการที่ท่านต้องการอยู่ในฐานข้อมูล');
	else if (!user_access('administer imeds'))
		return message('error','access denied');

	$qid = post('qid');
	if ($action == 'delete' && $qid && SG\confirm()) {
		mydb::query('DELETE FROM %imed_qt% WHERE qid = :qid LIMIT 1',':qid', $qid);
		$ret .= 'Questionnaire id '.$qid.' was deleted.';
		return $ret;
	}




	if (!$action) {
		$ret = '<h3>ตรวจสอบแบบสอบถาม</h3>';
		$ret .= '<form class="sg-form" method="get" action="'.url($selfUrl, array('action' => 'search')).'" data-rel="#imed-app-checkqt"><p><label>รหัสแบบสอบถาม </label><input type="text" id="filter" name="f" class="form-text" value="'.$filter.'" /> <button class="btn -primary" type="submit" name="go" value=" Go "><i class="icon -search -white"></i><span>Go</span></button> <button type="submit" value="Clear" class="btn" onclick="$(\'#filter\').val(\'\');return false;"><i class="icon -cancel"></i><span>Clear</span></button> จำนวน <strong>'.$dbs->_num_rows.'</strong> รายการ</p>';
		$ret .= '</form>';
		$ret .= '<div id="imed-app-checkqt">';
	}

	if ($filter) mydb::where('q.part LIKE :part', ':part', $filter);
	$stmt = 'SELECT
					q.`pid`
				, CONCAT(`name`," ",`lname`) `name`
				, `part`
				, q.`value`
				, COUNT(*) `amt`
				FROM %imed_qt% q
					LEFT JOIN %db_person% p ON p.`psnid` = q.`pid`
				%WHERE%
				GROUP BY `pid`,`part`
				HAVING `amt` > 1
				ORDER BY `part` ASC, `name` ASC';
	$dbs = mydb::select($stmt);


	$tables = new Table();
	$tables->addClass('left');
	$tables->thead=array('','รหัส','ชื่อ','รหัสแบบสอบถาม','รายละเอียด','amt'=>'จำนวนรายการ');
	foreach ($dbs->items as $rs) {
		if ($filter) $pidList[] = $rs->pid;
		$tables->rows[] = array(
			++$no,
			$rs->pid,
			'<a href="'.url('imed',['pid'=>$rs->pid]).'" target="_blank">'.SG\getFirst(trim($rs->name),'N/A').'</a>',
			'<a class="sg-action" href="'.url($selfUrl,array('f'=>$rs->part)).'" data-rel="box">'.$rs->part.'</a>',
			$rs->value,
			$rs->amt
		);
	}
	$ret .= $tables->build();

	if ($filter) {
		mydb::where('q.`pid` IN ('.implode(',',$pidList).')');
		mydb::where('`part` = :part', ':part', $filter);
		$stmt = 'SELECT
				q.*
			, CONCAT(p.`name`," ",p.`lname`) `disabledname`
			, uc.`name` `namecreate`
			, um.`name` `namemodify`
			FROM `sgz_imed_qt` q
				LEFT JOIN %db_person% p ON p.`psnid` = q.`pid`
				LEFT JOIN %users% uc ON uc.`uid` = q.`ucreated`
				LEFT JOIN %users% um ON um.`uid` = q.`umodify`
			%WHERE% 
			ORDER BY q.`pid` ASC, q.`qid` ASC';
		$qtdbs = mydb::select($stmt);


		$tables = new Table();
		$tables->addClass('right');
		$tables->thead=array('','','pid','qid','value','created','');
		foreach ($qtdbs->items as $k => $rs) {
			$tables->rows[] = array(
				$qtdbs->items[$k+1]->pid==$rs->pid?'<a class="sg-action" href="'.url($selfUrl,array('action'=>'delete','qid'=>$rs->qid,'confirm'=>'yes')).'" data-rel="none" data-removeparent="tr"><i class="icon -cancel"></i></a>':'',
				'<a href="javascript:void(0)" title="Name '.$rs->disabledname._NL.'Part '.$rs->part._NL.'Created by '.$rs->namecreate.' on '.date('Y-m-d H:i:s',$rs->dcreated)._NL.($rs->umodify?' Modify by '.$rs->namemodify.' on '.date('Y-m-d H:i:s',$rs->dmodify):'').'">?</a>',
					$rs->pid,
					$rs->qid,
					$rs->value,
					date('Y-m-d H:i:s',$rs->dcreated),
					'<a class="sg-action" href="'.url($selfUrl,array('action'=>'delete','qid'=>$rs->qid,'confirm'=>'yes')).'" data-rel="none" data-removeparent="tr"><i class="icon -cancel"></i></a>'
				);
		}
		$ret .= $tables->build();
	}

	if (!$action) {
		$ret .= '</div>';
	}

	$ret .= '<style tyle="text/css">
	table.left {width:60%;float:left;display:table;}
	table.right {width:38%;margin-left:2%;float:right;display:table;}
	</style>';

	return $ret;
}
?>