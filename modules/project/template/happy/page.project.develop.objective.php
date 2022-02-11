<?php
function project_develop_objective($self,$tpid) {
	if (empty($tpid) || mydb::select('SELECT `tpid` FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid)->_empty) return 'No project';

	$stmt = 'SELECT t.*,  u.`username`, u.`name`
			, d.`status`
		FROM %topic% t
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %project_dev% d ON d.`tpid`=t.`tpid`
		WHERE t.`tpid`=:tpid LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid);

	$isAdmin=user_access('administer projects');
	$is_edit=(in_array($rs->status,array(1,3)) || ($rs->status==2 && $cdate>='2014-04-19 16:00:00' && $cdate<='2014-04-27 16:00:00') ) && (user_access('administer projects','edit own project content',$rs->uid) || project_model::is_trainer_of($tpid));

	$objTypeList=array();
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;

	$action=post('action');

	switch ($action) {
		case 'info':
			$rs=project_model::get_info($tpid)->objective[post('id')];
			$ret.='<h3>'.$rs->objectiveTypeName.'</h3><h4>วัตถุประสงค์</h4><p>'.$rs->title.'</p><h4>ตัวชี้วัด</h4>'.sg_text2html($rs->indicator);
			if ($isAdmin) {
				$rs->created=sg_date($rs->created,'Y-m-d H:i:s');
				if ($rs->modified) $rs->modified=sg_date($rs->modified,'Y-m-d H:i:s');
				//$ret.=print_o($rs,'$rs');
			}
			return $ret;
			break;
	}

	if ($action) {
		if (!$is_edit) return '<p class="notify">เกิดข้อผิดพลาด สิทธิ์ในการเข้าถึงถูกปฏิเสธ</p>';
		switch ($action) {
			case 'add' :
				if (empty($objTypeList)) return '<p class="notify">ยังไม่มีการกำหนดกลุ่มวัตถุประสงค์ กรุณาติดต่อผู้ดูและระบบ</p>';
				if (!post('data')) {
					$ret.='<h3>เพิ่มวัตถุประสงค์</h3>';

					$form = new Form([
						'variable' => 'data',
						'action' => url(q()),
						'id' => 'project-edit-movemainact',
						'class' => 'sg-form',
						'rel' => '#project-develop-objective',
						'done' => 'close',
						'children' => [
							'action' => ['type' => 'hidden', 'name' => 'action', 'value' => 'add'],
							'parent' => [
								'type' => 'select',
								'label' => 'เลือกกลุ่มวัตถุประสงค์ :',
								'options' => $objTypeList,
							],
							'title' => ['type' => 'text', 'label' => 'วัตถุประสงค์/เป้าหมาย', 'class' => '-fill'],
							'indicators' => ['type' => 'textarea', 'label' => 'ตัวชี้วัดความสำเร็จ',],
							'save' => ['type' => 'button','value' => 'บันทึก',],
						],
					]);

					$ret .= $form->build();

					return $ret;
				} else {
					$post=(object)post('data');
					$post->tpid=$tpid;
					$post->uid=i()->uid;
					$post->created=date('U');
					$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `uid`, `formid`, `part`, `text1`,`text2`, `created`) VALUES (:tpid, :parent, :uid, "info" , "objective", :title, :indicators, :created)';
					mydb::query($stmt,$post);
					//$ret.=mydb()->_query;
				}
				break;
			case 'remove' :
				if (post('id')) {
					$delrs=mydb::select('SELECT * FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('id'));
					mydb::query('DELETE FROM %project_tr% WHERE `trid`=:trid LIMIT 1',':trid',post('id'));
					mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `parent`=:trid',':tpid',$tpid,':trid',post('id'));
					model::watch_log('project','remove objective','ลบวัตถุประสงค์ หมายเลข '.post('id').'<br />'.sg_text2html($delrs->text1).'<br />'.sg_text2html($delrs->text2),NULL,$tpid);
				}
				break;

			case 'move' :
				if (post('to')) {
					if (post('id')) {
						mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:from LIMIT 1',':from',post('id'), ':to',post('to'));
					}
				} else {
					$ret .= '<h3>ย้ายวัตถุประสงค์</h3>';

					$form = new Form([
						'variable' => 'data',
						'action' => url(q()),
						'id' => 'project-edit-movemainact',
						'class' => 'sg-form',
						'rel' => 'project-develop-objective',
						'done' => 'close',
						'children' => [
							'action' => ['type' => 'hidden', 'name' => 'action', 'value' => 'move'],
							'id' => ['type' => 'hidden', 'name' => 'id', 'value' => post('id')],
							'to' => [
								'type' => 'radio',
								'name' => 'to',
								'label' => 'เลือกวัตถุประสงค์ที่ต้องการย้ายไป : ',
								'options' => $objTypeList,
							],
							'save' => ['type' => 'button', 'value' => 'บันทึก',]
						],
					]);

					$ret .= $form->build();

					return $ret;
				}
				break;
		}
	}

	$info = project_model::get_info($tpid);
	$objectiveNo=0;

	$tables = new Table([
		'class' => 'project-develop-objective',
		'colgroup' => ['width="0%"','width="50%"','width="50%"','width="0%"'],
		'thead' => ['no'=>'','วัตถุประสงค์ / เป้าหมาย','ตัวชี้วัดความสำเร็จ',''],
		'children' => (function($objTypeList, $info, $is_edit, $isAdmin){
			$rows = [];
			foreach ($objTypeList as $objTypeId => $objTypeName) {
				if ($objTypeId == 1) $rows[] = '<tr><th colspan="4">วัตถุประสงค์โดยตรง</th></tr>';
				else if ($objTypeId==2) $rows[] = '<tr><th colspan="4">วัตถุประสงค์โดยอ้อม</th></tr>';

				$rows[] = ['<td colspan="4"><strong>'.$objTypeName.'</strong></td>'];
				foreach ($info->objective as $objective) {
					if ($objective->objectiveType!=$objTypeId) continue;
					$tpid = $objective->tpid;
					$rows[] = [
						++$objectiveNo,
						view::inlineedit(
							['group' => 'tr:info:objective', 'fld' => 'text1', 'tr' => $objective->trid],
							$objective->title,
							$is_edit,
							'textarea'
						)
						. ($is_edit ? '<br /><a class="sg-action -no-print" href="'.url('project/history', ['tpid' => $tpid, 'k' => 'tr,info,objective,text1,'.$objective->trid]).'" data-rel="box">?</a>' : ''),
						view::inlineedit(
							['group' => 'tr:info:objective', 'fld' => 'text2', 'tr' => $objective->trid, 'ret' => 'html'],
							$objective->indicator,
							$is_edit,
							'textarea'
						)
						. ($is_edit ? '<br /><a class="sg-action -no-print" href="'.url('project/history', ['tpid' => $tpid, 'k' => 'tr,info,objective,text2,'.$objective->trid]).'" data-rel="box">?</a>' : ''),
						$is_edit && empty($mtables->rows) ? sg_dropbox('<ul><li><a href="'.url('project/develop/objective/'.$tpid, ['action' => 'move', 'id' => $objective->trid]).'" class="sg-action" title="ย้ายวัตถุประสงค์" data-rel="box">ย้ายวัตถุประสงค์</a></li><li><a class="sg-action" href="'.url('project/develop/objective/'.$tpid, ['action' => 'remove', 'id' => $objective->trid]).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr">ลบวัตถุประสงค์</a></li>'.($isAdmin?'<li><a class="sg-action" href="'.url('project/develop/objective/'.$tpid, ['action' => 'info', 'id' => $objective->trid]).'" data-rel="box">ข้อมูลเฉพาะ</a></li>':'').'</ul>','{type:click}'):'',
					];
				}
			}
			return $rows;
		})($objTypeList, $info, $is_edit, $isAdmin),
	]);

	/*
		$ret.='<div id="project-objective-'.$objective->trid.'" class="project-dev-objective">'._NL;
		$ret.='<h4>วัตถุประสงค์ ข้อที่ '.(++$objectiveNo).'</h4>'._NL;
		$ret.=view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid), $objective->title, $is_edit, 'textarea')._NL;
		$ret.='<h5>ตัวชี้วัด</h5>'._NL;
		$ret.=view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html'),$objective->indicator,$is_edit,'textarea')._NL;
		$j=0;
		$actid=0;
		$subBudget=$subTarget1=$subTarget2=$subActivity=$subActivityBudget=0;

		// Show delete button on empty objective
		if ($is_edit && empty($mtables->rows)) {
			$ret.='<li><a class="sg-action button" href="'.url('project/develop/objective/'.$tpid,array('action'=>'remove','id'=>$objective->trid)).'" data-rel="#project-develop-objective" data-confirm="ต้องการลบวัตถุประสงค์ ข้อที่ '.$objectiveNo.' จริงหรือไม่?">ลบวัตถุประสงค์ข้อที่ '.$objectiveNo.'</a></li>';
			$ret.='</ul>';
		}
		*/

	$ret .= $tables->build();

	return $ret;
}
?>