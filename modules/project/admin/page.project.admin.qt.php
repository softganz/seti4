<?php
/**
* Project qt
*
* @param Object $self
* @return String
*/
function project_admin_qt($self) {
	$action=post('act');
	$q=post('q');
	$orgid=post('id');
	$order=SG\getFirst($para->order,post('o'),'CONVERT(o.`name` USING tis620)');
	$sort=SG\getFirst($para->sort,post('s'),'ASC');

	R::View('project.toolbar',$self,'จัดการแบบสอบถาม','admin');
	$self->theme->sidebar=R::View('project.admin.menu','qt');

	$where = array();
	if (post('gr')) $where=sg::add_condition($where,'`qtgroup`=:qtgroup','qtgroup',post('gr'));

	$stmt='SELECT * FROM %qt%
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				';

	$dbs= mydb::select($stmt,$where['value']);

	$navbar.='<nav class="nav -page"><header class="header -hidden"><h3>User Management</h3></header>'._NL;
	$navbar.='<a class="btn -floating -circle48 -fixed -at-bottom -at-right" href="'.url('project/admin/qt',array('act'=>'create')).'" title="สร้างคำถามใหม่"><i class="icon -addbig -white"></i></a>';
	$navbar.='</nav>';

	$self->theme->navbar=$navbar;

	switch ($action) {
		case 'edit' :
			$ret.=__project_admin_qt_form(post('id'));
			return $ret;
			break;

		case 'update' :
			$ret.=__project_admin_qt_update();
			break;
	}

	$tables = new Table();
	$tables->caption='รายชื่อแบบสอบถาม';
	$tables->thead=array('กลุ่มแบบสอบถาม','ข้อที่','คำถาม','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
												'<a href="'.url('project/admin/qt',array('gr'=>$rs->qtgroup)).'">'.$rs->qtgroup.'</a>',
												$rs->qtno,
												$rs->question,
												'<a href="'.url('project/admin/qt',array('act'=>'edit','id'=>$rs->qtid)).'">แก้ไข</a>'
												);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __project_admin_qt_form($qtid) {
	$post=mydb::select('SELECT * FROM %qt% WHERE `qtid`=:qtid LIMIT 1',':qtid',$qtid);

	$form = new Form('qt', url('project/admin/qt'), 'org-add-qt');

	$form->act=array('type'=>'hidden','value'=>'update','name'=>'act');
	$form->qtid=array('type'=>'hidden','value'=>$qtid);

	$form->question->type='text';
	$form->question->label='คำถาม';
	$form->question->size=40;
	$form->question->class='w-9';
	$form->question->value=htmlspecialchars($post->question);

	$form->description->type='textarea';
	$form->description->label='คำอธิบาย';
	$form->description->rows=20;
	$form->description->value=htmlspecialchars($post->description);

	$dbs=mydb::select('SELECT * FROM %qtchoice% WHERE `qtid`=:qtid ORDER BY `qtchoice` ASC',':qtid',$qtid);
	foreach ($dbs->items as $rs) $choiceList[$rs->qtchoice]=$rs->choicename;

	for ($i=1; $i<=4; $i++) {
		$choiceFormName='choice'.$i;
		$form->{$choiceFormName}->type='textarea';
		$form->{$choiceFormName}->label='ตัวเลือกที่ '.$i;
		$form->{$choiceFormName}->name='choice['.$i.']';
		$form->{$choiceFormName}->class='w-9';
		$form->{$choiceFormName}->rows=3;
		$form->{$choiceFormName}->value=htmlspecialchars($choiceList[$i]);
	}

	$form->submit->type='submit';
	$form->submit->items->save='บันทึก';
	$ret .= $form->build();
	//$ret.=print_o($choiceList,'$choiceList');
	//$ret.=print_o($post,'$post');
	return $ret;
}

function __project_admin_qt_update() {
	$post=post('qt');
	$stmt='UPDATE %qt% SET `question`=:question, `description`=:description WHERE `qtid`=:qtid';
	mydb::query($stmt,$post);
	//$ret.=mydb()->_query.'<br />';

	foreach (post('choice') as $qtchoice => $choicename) {
		if ($choicename!='') {
			$stmt='INSERT INTO %qtchoice% (`qtid`, `qtchoice`, `choicename`) VALUES (:qtid, :qtchoice, :choicename)
							ON DUPLICATE KEY
							UPDATE `choicename`=:choicename';
			mydb::query($stmt,':qtid',$post['qtid'],':qtchoice',$qtchoice, ':choicename',$choicename);
			//$ret.=mydb()->_query.'<br />';
		}
	}

	//$ret.=print_o(post(),'post');
	return $ret;
}
?>