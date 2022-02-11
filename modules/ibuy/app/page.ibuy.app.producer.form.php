<?php
function ibuy_app_producer_form($self,$psnid) {
	R::View('ibuy.toolbar',$self,'เครือข่ายผู้ผลิต','app.hatyaigogreen');
	$ret.='<h3 class="header -sub">แบบสำรวจข้อมูลเครือข่ายผู้ผลิต</h3>';

	$ret.='<pre>
	ข้อมูลบุคคล : ชื่อ นามสกุล ที่อยู่
	เครือข่าย : รายย่อย/กลุ่ม/องค์กร/นิติบุคคล
	ชนิดของเกษตร : ข้าว,ผัก,ผลไม้,อาหารทะเล,สินค้าแปรรูป,ปศุสัตว์


	</pre>';

	$form=new Form('data',url('ibuy/app/producer/form'),NULL,'ibuy-producer-form');

	$form->addField('h1','<p style="padding:8px;border:1px #ccc solid;background:#f0f0f0;">ลำดับที่การเก็บข้อมูล <input type="text" style="width:4em;"/> / 2560</p>');

	$form->addField('h2','<h3>ข้อมูลทั่วไป</h3>');

	$form->addField(
						'prename',
						array(
							'type'=>'select',
							'label'=>'คำนำหน้าชื่อ :',
							'class'=>'-fill',
							'options'=>array('ด.ช.'=>'ด.ช.','ด.ญ.'=>'ด.ญ.','นาย'=>'นาย','นาง'=>'นาง','นางสาว'=>'นางสาว','99'=>'อื่นๆ'),
							'posttext'=>'<input class="form-text -fill -hidden" type="text" placeholder="ระบุคำนำหน้าชื่อ" style="margin:8px 0;" />',
							)
						);

	$form->addField(
						'fullname',
						array(
							'type'=>'text',
							'label'=>'ชื่อ - นามสกุล',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'cid',
						array(
							'type'=>'text',
							'label'=>'เลขที่บัตรประจำตัวประชาชน',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'birth',
						array(
							'type'=>'date',
							'label'=>'วัน/เดือน/ปีเกิด :',
							'year'=>(object)array('range'=>'-110,110','type'=>'BC'),
							'value'=>(object)array(
													'date'=>SG\getFirst($post->date['date'],date('d')),
													'month'=>SG\getFirst($post->date['month'],date('m')),
													'year'=>SG\getFirst($post->date['year'],date('Y'))
													),
							)
						);

	$form->addField(
						'sex',
						array(
							'type'=>'radio',
							'label'=>'เพศ :',
							'options'=>array('1'=>'ชาย','2'=>'หญิง'),
							)
						);


	$form->addField('t2_s','<p><b>ที่อยู่ตามทะเบียนบ้าน</b></p><div id="imed-poorman-form-curhome" style="border:1px #ccc solid;padding:16px;">');

	$form->addField(
						'registaddr',
						array(
							'type'=>'text',
							'label'=>'ที่อยู่ตามทะเบียนบ้าน',
							'class'=>'sg-address -fill',
							)
						);

	$form->addField(
						'registzip',
						array(
							'type'=>'text',
							'label'=>'รหัสไปรษณีย์',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'registmobile',
						array(
							'type'=>'text',
							'label'=>'เบอร์มือถือ',
							'class'=>'-fill',
							)
						);

	$form->addField('t2_e','</div>');


	$form->addField(
					'by',
					array(
						'type'=>'text',
						'label'=>'ผู้จัดเก็บข้อมูล',
						'class'=>'-fill',
						)
					);

	$form->addField(
					'getdate',
					array(
						'type'=>'text',
						'label'=>'วันที่เก็บข้อมูล',
						'class'=>'sg-datepicker -fill',
						)
					);

	$form->addField(
					'save',
					array(
						'type'=>'button',
						'name'=>'save',
						'items'=>array(
											'type'=>'submit',
											'class'=>'-primary',
											'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
											),
						'posttext'=>' <a class="sg-action" href="'.url('imed/app/poorman/list').'" data-rel="#main" data-done="moveto:0,0">ยกเลิก</a>',
						)
					);

	$ret.=$form->build();


	$ret.='<style type="text/css">
	.imed-poorman-form {margin:0;padding:8px;}
	</style>';
	$ret.='<script type="text/javascript">
	$("#edit-data-prename").change(function(){
		console.log($(this).val())
		if ($(this).val()=="99") {
			$(this).next().show().focus()
		} else {
			$(this).next().hide()
		}
	});
	$("input[name=\'data[issameaddress]\'").click(function() {
		console.log("Click")
		if($(this).is(":checked")) {
			$("#imed-poorman-form-regishome").show();
		} else {
			$("#imed-poorman-form-regishome").hide();
		}
	});
	</script>';
	return $ret;
}
?>