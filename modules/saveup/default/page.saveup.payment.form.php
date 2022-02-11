<?php
/**
* Save :: Member Monthly Payment Form
* Created 2018-06-22
* Modify  2021-09-01
*
* @return Widget
*
* @usage saveup/payment/form
*/

$debug = true;

class SaveupPaymentForm extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แจ้งการโอนเงิน',
			]),
			'body' => new Widget([
				'children' => [
					!i()->ok ? message('notify','คำแนะนำ : กรุณาเข้าสู่ระบบสมาชิกก่อนทำการแจ้งโอนเงิน เพื่อประโยชน์ในการดูบันทึกการแจ้งโอนเงินย้อนหลังของท่าน <br /><nav class="-sg-text-center"><a class="btn -primary" href="'.url('signin', array('ret' => 'saveup/payment/form')).'">เข้าสู่ระบบสมาชิก</a></nav>') : NULL,
					new Form([
						'variable' => 'payment',
						'action' => url('saveup/payment/update'),
						'id' => 'saveup-confirm',
						'class' => 'sg-form',
						'enctype' => 'multipart/form-data',
						'checkValid' => true,
						'children' => [
							'payfor' => [
								'type' => 'select',
								'label' => 'โอนเงินเป็นค่า :',
								'options' => [
									'ค่าออมทรัพย์' => 'ค่าสัจจะ-เงินกู้-ค่าบำรุง-ค่าปรับ',
									'ค่ากองทุนบำนาญ' => 'ค่ากองทุนบำนาญ',
									'อื่น ๆ' => 'อื่น ๆ'
								],
								'require' => true,
								'value' => $post->payfor,
							],
							'payacc' => [
								'type' => 'radio',
								'label' => 'โอนเงินโดยวิธี :',
								'require' => true,
								'value' => $post->payacc,
								'options' => (function() {
									$options = [];
									foreach (cfg('saveup.payment.account') as $item) $options[$item] = $item;
									return $options;
								})(),
							],
							'date' => [
								'type' => 'date',
								'label' => 'วันที่โอน :',
								'require' => true,
								'year' => (Object) ['range' => '-1,2','type' => 'BC'],
								'value' => (Object) [
									'date'=>SG\getFirst($post->date['date'],date('d')),
									'month'=>SG\getFirst($post->date['month'],date('m')),
									'year'=>SG\getFirst($post->date['year'],date('Y'))
								],
							],
							'time' => [
								'type' => 'text',
								'label' => 'เวลา (ประมาณ)',
								'maxlength' => 5,
								'require' => true,
								'value' => htmlspecialchars($post->time)
							],
							'amt' => [
								'type' => 'text',
								'label' => 'จำนวนเงินที่โอน (บาท) :',
								'maxlength' => 10,
								'require' => true,
								'value' => htmlspecialchars($post->amt)
							],
							'poster' => [
								'type' => 'text',
								'label' => 'ชื่อผู้โอน',
								'class' => '-fill',
								'require' => true,
								'value' => htmlspecialchars($post->poster)
							],
							'remark' => [
								'type' => 'textarea',
								'label' => 'รายละเอียด :',
								'class' => '-fill',
								'rows' => 7,
								'value' => htmlspecialchars($post->remark),
								'description' => 'กรุณาป้อนรายละเอียดในการโอนเงินว่าเป็นค่าอะไรบ้าง โดยป้อน 1 รายการต่อ 1 บรรทัด'
							],
							'photo' => i()->ok ? [
								'type' => 'file',
								'name' => 'photo',
								'label' => '<i class="icon -image"></i>ภาพถ่ายใบเสร็จรับเงิน',
								'container' => '{class: "btn -upload"}',
							] : '<p class="notify"><a class="sg-action" href="'.url('signin', array('ret' => 'saveup/payment/form')).'" data-rel="box">เข้าสู่ระบบสมาชิกเพื่อส่งภาพถ่ายใบโอนเงิน</a></p>',
							'confirm' => [
								'type' => 'button',
								'value' => '<i class="icon -save -white"></i><span>แจ้งการโอนเงิน</span>',
							],
						], // children
					]), // Form
					i()->ok ? R::Page('saveup.payment.trans',$self,i()->uid) : NULL,
				], // children
			]),
		]);
	}
}
?>