<?php
/**
* Module Method
*
* @param
* @return String
*/

$debug = true;

function view_user_register_confirm($register=array()) {
	$form=new Form('register',url(q()),'edit-register','sg-form');
	$form->addData('checkValid',true);

	if ($register->rel) {
		$form->addData('rel',$register->rel);
	}

	$form->addConfig('title','ยืนยันการลงทะเบียนสมาชิกใหม่ ( Member registration confirm )');
	$form->addConfig('description','<p><strong>รายละเอียดการสมัครสมาชิก ( Member detail )</strong></p><table border=0 cellspacing=5 cellpadding=0>
		<tr valign=top><td align=right nowrap><b>ชื่อสมาชิก ( Username)  :</b></td><td nowrap width=50%><b>'.$register->username.'</b></td></tr>
		<tr valign=top><td align=right>ชื่อ ( Name ) :</td><td colspan=2>'.$register->name.'</td></tr>
		<tr valign=top><td align=right>อี-เมล์ ( E-Mail ) :</td><td colspan=2>'.$register->email.'</td></tr>
		<tr><td colspan=2><!-- ถ้าหากมีต้องมีการยืนยันการสมัครทางอีเมล์ ให้แสดงข้อความ --></td></tr>
		</table>');

	if ($register->rel) $form->addField('rel',array('type'=>'hidden','value'=>$register->rel));
	if ($register->ret) $form->addField('ret',array('type'=>'hidden','value'=>$register->ret));

	$form->addField('step',array('type'=>'hidden','value'=>2));

	$form->addField('username',array('type'=>'hidden','value'=>htmlspecialchars($register->username)));

	$form->addField('password',array('type'=>'hidden','value'=>htmlspecialchars($register->password)));

	$form->addField('repassword',array('type'=>'hidden','value'=>htmlspecialchars($register->repassword)));

	$form->addField('name',array('type'=>'hidden','value'=>htmlspecialchars($register->name)));

	$form->addField('email',array('type'=>'hidden','value'=>htmlspecialchars($register->email)));

	$form->addField(
						'daykey',
						array(
							'name'=>'daykey',
							'type'=>'text',
							'label'=>'Anti-spam word',
							'size'=>10,
							'require'=>true,
							'pretext'=>'<em class="spamword">'.poison::get_daykey(5,true).'</em> ',
							'description'=>'ท่านจำเป็นต้องป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง'
							)
						);
	$form->addField(
					'submit',
					array(
						'type'=>'button',
						'items'=>array(
											'cancel'=>array(
																'type'=>'cancel',
																'class'=>'-link',
																'value'=>'<i class="icon -cancel -gray"></i><span>{tr:Cancel}</span>'
																),
											'confirm'=>array(
																'type'=>'submit',
																'class'=>'-primary',
																'value'=>'<i class="icon -save -white"></i><span>{tr:Confirm}</span>'
																),
											),
						'container'=>array('class'=>'-sg-text-right'),
						)
					);

	//$form->submit->type='submit';
	//$form->submit->items->confirm=tr('Confirm').' &raquo;';
	//$form->submit->items->cancel=tr('Cancel');

	if (cfg('member.registration.method')=='email') {
		$form->addField(
							'help',
							array(
								'type'=>'textfield',
								'value'=>'<strong>เมื่อท่านลงทะเบียนเรียบร้อย เราจะส่งอี-เมล์ถึงท่าน ตามอี-เมล์ที่ท่านระบุ และท่านจะต้องทำการยืนยันการเป็นสมาชิกจากอีเมล์ของท่าน การสมัครสมาชิกจึงจะสมบูรณ์</strong>'
								)
							);
	}

	event_tricker('user.register_confirm',$self,$register,$form);

	$ret .= $form->build();
	$ret.='<script type="text/javascript">document.getElementById("edit-daykey").focus();</script>';
	return $ret;
}
?>