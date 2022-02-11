<?php
/**
 * Add new member
 *
 * @param Array $_POST['set']
 * @return String and die / Location
 */
function saveup_bank_addmember($self) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');
	$ret.='<h3>เพิ่มชื่อบัญชี</h3>';
	if (!user_access('create saveup content')) return R::View('signform');

	if ($_POST['member']) {
		$post=(object)post('member',_TRIM);
		if ($post->firstname=='') $error='กรุณาป้อน "ชื่อ"';
		else if ($post->lastname=='') $error='กรุณาป้อน "นามสกุล"';
		else if (mydb::select('SELECT `mid` FROM %saveup_member% WHERE `mid`=:mid LIMIT 1',':mid',$post->mid)->_num_rows) $error='เลขที่บัญชีซ้ำ';
		if ($error) {
			$ret.=notify($error);
		} else {
			$post->uid = i()->uid;
			$post->userId = NULL;
			$post->created = date('U');
			$post->date_regist= $ post->date_approve=date('Y-m-d');
			$post->balance=$post->bsd == 'B' ? $post->netamount : 0;

			$stmt = 'INSERT INTO %saveup_member%
				(`mid`, `prename`, `firstname`, `lastname`, `date_regist`, `date_approve`)
				VALUES (:mid, :prename, :firstname, :lastname, :date_regist, :date_approve)';

			mydb::query($stmt,$post);

			location('saveup/bank/member/'.$mid);
		}
	}

	$form = new Form([
		'variable' => 'member',
		'action' => url(q()),'saveup-addmember',
	]);

	$form->addField(
						'mid',
						array(
							'type'=>'text',
							'label'=>'หมายเลขบัญชี',
							'class'=>'-fill',
							'maxlength'=>6,
							'value'=>$post->mid
							)
						);

	$form->addField(
						'prename',
						array(
							'type'=>'text',
							'label'=>'คำนำหน้านาม',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$post->prename
							)
						);

	$form->addField(
						'firstname',
						array(
							'type'=>'text',
							'label'=>'ชื่อ',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$post->firstname
							)
						);

	$form->addField(
						'lastname',
						array(
							'type'=>'text',
							'label'=>'นามสกุล',
							'class'=>'-fill',
							'maxlength'=>50,
							'value'=>$post->lastname
							)
						);

	$form->addField(
					'fieldname',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>เพิ่มสมาชิก</span>',
						)
					);

	$ret .= $form->build();

	return $ret;
}
?>