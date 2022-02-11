<?php
function saveup_bank_setting($self) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');
	$ret.='<h3>Setting</h3>';
	$isEdit=user_access('create saveup content');

	$stmt='SELECT * FROM %co_category% WHERE `cat_group`="'._WESTCODE.'" ORDER BY `cat_name` ASC';
	$dbs=mydb::select($stmt);

	switch (post('action')) {
		case 'editwestcode':
			$rs=mydb::select('SELECT * FROM %co_category% WHERE `cat_id`=:cat_id LIMIT 1',':cat_id',post('id'));
			break;
	}

	$ret.='<form id="saveup-bank-trans-add" class="sg-form" data-rel="#saveup-main" method="post" action="'.url('saveup/bank/addwestcode/'.$mid).'"><input type="hidden" name="cat_id" value="'.$rs->cat_id.'" />'._NL;

	$tables = new Table();
	$tables->thead=array('no'=>'','รายชื่อขยะ','หน่วย','amt unitprice'=>'ราคาต่อหน่วย');
	$tables->rows[]=array(
		'<td></td>',
		'<input type="text" name="name" class="form-text" value="'.htmlspecialchars($rs->cat_name).'" /><h5>'.($rs->cat_id?'แก้ไข':'เพิ่ม').'รายชื่อขยะ</h5>',
		'<input type="text" name="unitname" class="form-text" value="'.htmlspecialchars($rs->cat_detail1).'" />',
		'<input type="text" name="unitprice" class="form-text" value="'.number_format($rs->cat_num1,2,'.','').'" autocomplete="off" /><p><button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>'.($rs->cat_id?'บันทึก':'เพิ่ม').'รายชื่อขยะ</span></button></p>',
	);
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			$rs->cat_name,
			$rs->cat_detail1,
			number_format($rs->cat_num1,2),
			'<a class="sg-action" href="'.url('saveup/bank/setting',array('action'=>'editwestcode','id'=>$rs->cat_id)).'" data-rel="#saveup-main">แก้ไข</a>',
			'ลบ',
		);
	}
	$ret .= $tables->build();
	$ret.='</form>';
	return $ret;
}
?>