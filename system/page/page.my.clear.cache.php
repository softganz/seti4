<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function my_clear_cache($self) {
	$ret = '<header class="header"><h3>ล้างแคช</h3></header>';

	//$ret .= '<nav class="nav -page -sg-text-center" style="padding: 32px 0;"><a class="btn -primary" href="'.url(q()).'" data-rel="none" data-done="close">เรียบร้อย</a></nav>';

	$ret .= '<p style="padding: 32px 8px;">ในกรณีที่ข้อมูลแคชมีการเปลี่ยนแปลงแต่การแสดงผลยังคงใช้แคชเดิม จำทำการล้างแคชเพื่อดึงข้อมูลปัจจุบันมาใช้งาน</p>';
	$form = new Form(NULL, url(q()), NULL, 'sg-form -upload green-activity-form');

	$form->addData('rel', 'silent');
	$form->addData('silent', true);
	$form->addData('done', 'close');

	$form->addField('save',
		array(
			'type' => 'button',
			'class' => '-fill',
			'value' => '<span>เรียบร้อย</span>',
			'container' => array('class' => '-sg-text-center'),
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>