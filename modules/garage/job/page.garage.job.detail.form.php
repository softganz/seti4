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

function garage_job_detail_form($self, $jobInfo,$action=NULL) {
	$shopInfo = $jobInfo->shopInfo;
	$isEdit = $jobInfo->is->editable && $action==='edit';
	$editClass=$isEdit?'':'-disabled';
	$ret='';


	$carTypeLists = array();
	foreach (R::Model('garage.cartype.get.all',$shopInfo->shopid) as $item) {
		$carTypeLists[$item->cartypeid] = $item->cartypename;
	}

	$brandLists = R::Model('garage.brand.getall',$shopInfo->shopid);
	$modelLists = array('1'=>'CLASS E','2'=>'JAZZ');
	$colorLists = array('1'=>'Red','2'=>'Blue','3'=>'Green');
	foreach(R::Model('garage.user.getall',$shopInfo->shopid) as $item) {
		$userLists[$item->uid]=$item->name.' ('.$item->position.')';
	}


	$form = new Form('job',url('garage/job/'.$jobInfo->tpid.'/info/detail.save'),'garage-job-new');
	$form->addClass('garage-job-new -form');
	$form->addClass('sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load->replace:this:'.url('garage/job/'.$jobInfo->jobId.'/detail.form/edit').' | moveto: 0,0');

	$form->addText('<section class="box"><h3>ข้อมูลใบสั่งซ่อม</h3>');
	$form->addField(
		'jobno',
		array(
			'type'=>'text',
			'label'=>'เลขใบสั่งซ่อม',
			'readonly'=>true,
			'class'=>'-highlight -disabled',
			'value'=>$jobInfo->jobno,
		)
	);

	$form->addField(
		'plate',
		array(
			'type'=>'text',
			'label'=>'ทะเบียน',
			'class'=>$editClass.' -highlight',
			'value'=>$jobInfo->plate,
		)
	);

	$form->addField(
		'rcvby',
		array(
			'type'=>'select',
			'label'=>'ผู้รับรถ:',
			'class'=> $editClass.' -fill',
			'value'=>$jobInfo->rcvby,
			'options'=>array(''=>'***ระบุชื่อผู้รับรถ***')+$userLists,
		)
	);

	$form->addField(
		'rcvdate',
		array(
			'type'=>'text',
			'label'=>'วันที่เปิดใบสั่งซ่อม',
			'class'=>'sg-datepicker '.$editClass,
			'value'=>sg_date($jobInfo->rcvdate,'d/m/Y'),
		)
	);

	$form->addField(
		'carwaitno',
		array(
			'type'=>'text',
			'label'=>'เลขรถรอ',
			'class'=>$editClass.' -highlight',
			'maxlength' => 10,
			'value'=>$jobInfo->carwaitno,
		)
	);

	$form->addField(
		'carinno',
		array(
			'type'=>'text',
			'label'=>'เลขรถเข้า',
			'class'=>$editClass.' -highlight',
			'maxlength' => 10,
			'value'=>$jobInfo->carinno,
		)
	);

	$form->addText('</section>');

	$form->addText('<section class="box"><h3>ข้อมูลรถ</h3>');

	$form->addField(
		'cartype',
		array(
			'type'=>'select',
			'label'=>'ประเภทรถ:',
			'value'=>$jobInfo->cartype,
			'options'=>array(''=>'***ระบุประเภทรถ***')+$carTypeLists,
			'class'=>$editClass.' -fill',
			// 'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/car/add').'" data-rel="box" title="เพิ่มยี่ห้อรถใหม่"><i class="icon -add"></i></a>':'',
		)
	);

	$form->addField(
		'brandid',
		array(
			'type'=>'select',
			'label'=>'ยี่ห้อ:',
			'value'=>$jobInfo->brandid,
			'options'=>array(''=>'***ระบุยี่ห้อรถ***')+$brandLists,
			'class'=>$editClass.' -fill',
			// 'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/car/add').'" data-rel="box" title="เพิ่มยี่ห้อรถใหม่"><i class="icon -add"></i></a>':'',
		)
	);

	$form->addField(
		'modelname',
		array(
			'type'=>'text',
			'label'=>'รุ่น',
			'class'=>$editClass,
			'value'=>$jobInfo->modelname,
		)
	);

	$form->addField(
		'colorname',
		array(
			'type'=>'text',
			'label'=>'สี',
			'class'=>$editClass,
			'value'=>$jobInfo->colorname,
		)
	);

	/*
	$form->model=array(
		'type'=>'select',
		'label'=>'รุ่น:',
		'class'=>$editClass,
		'value'=>$jobInfo->model,
		'options'=>$modelLists,
		'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/model/add').'" data-rel="box" title="เพิ่มรุ่นรถใหม่"><i class="icon -add"></i></a>':'',
	);

	$form->color=array(
		'type'=>'select',
		'label'=>'สี:',
		'class'=>$editClass,
		'value'=>$jobInfo->color,
		'options'=>$colorLists,
		'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/color/add').'" data-rel="box" title="เพิ่มสีรถใหม่"><i class="icon -add"></i></a>':'',
	);
	*/

	$form->addField(
		'bodyno',
		array(
			'type'=>'text',
			'label'=>'หมายเลขตัวถัง',
			'class'=>$editClass,
			'maxlength' => 20,
			'value'=>$jobInfo->bodyno,
		)
	);

	$form->addField(
		'enginno',
		array(
			'type'=>'text',
			'label'=>'หมายเลขเครื่อง',
			'class'=>$editClass,
			'readonly'=>!$isEdit,
			'maxlength' => 20,
			'value'=>$jobInfo->enginno,
		)
	);

	$form->addField(
		'milenum',
		array(
			'type'=>'text',
			'label'=>'เลขไมล์',
			'class'=>$editClass,
			'readonly'=>!$isEdit,
			'maxlength' => 7,
			'value'=>$jobInfo->milenum,
		)
	);

	$form->addText('</section>');

	$form->addText('<section class="box"><h3>ข้อมูลประกันภัย</h3>');

	$form->addField(
		'insurerid',
		array('type'=>'hidden','value'=>$jobInfo->insurerid)
	);

	$form->addField(
		'insurername',
		array(
			'type'=>'text',
			'label'=>'บริษัทประกัน/ผู้จ่ายเงิน:',
			'class'=>'sg-autocomplete'.$editClass,
			'value'=>$jobInfo->insurername,
			'autocomplete'=>'Off',
			//'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/insu/add').'" data-rel="box" title="เพิ่มประกันใหม่"><i class="icon -add"></i></a>':'',
			'attr'=>array(
				'data-query'=>url('garage/api/insurer'),
				'data-select'=>'label',
				'data-altfld'=>'edit-job-insurerid',
			),
		)
	);

	$form->addField(
		'customerid',
		array('type'=>'hidden','value'=>$jobInfo->customerid)
	);

	$form->addField(
		'customername',
		array(
			'type' => 'text',
			'label' => 'ชื่อเจ้าของรถ:',
			'class' => 'sg-autocomplete'.$editClass,
			'value' => $jobInfo->customername,
			'autocomplete' => 'Off',
			'posttext' => '<div class="input-append">'
				. '<span class="-primary"><a class="sg-action btn -link -addcode -nowrap" href="'.url('garage/code/customer/form',array('callback' => 'updateCustomerId')).'" data-rel="box" data-width="480" title="เพิ่มลูกค้าใหม่"><i class="icon -material">person_add</i><span class="-hidden">เพิ่มลูกค้าใหม่</span></a></span>'
				. '</div>',
			//'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/cust/add').'" data-rel="box" title="เพิ่มลูกค้าใหม่"><i class="icon -add"></i></a>':'',
			'attr'=>array(
				'data-query' => url('garage/api/customer'),
				'data-select' => 'label',
				'data-altfld' => 'edit-job-customerid',
			),
			'container' => '{class: "-group"}',
		)
	);

	/*
	$form->addField(
		'datetoreturn',
		array(
			'type'=>'text',
			'label'=>'วันที่นัดรับรถ',
			'class'=>'sg-datepicker '.$editClass,
			'readonly'=>!$isEdit,
			'value'=>$jobInfo->datetoreturn?sg_date($jobInfo->datetoreturn,'d/m/Y'):'',
		)
	);

	$form->addField(
		'timetoreturn',
		array(
			'type'=>'time',
			'label'=>'เวลานัดรับรถ :',
			'class'=>$editClass,
			'value'=>substr($jobInfo->timetoreturn,0,5),
			'step'=>30,
			'start'=>8,
			'end'=>19,
		)
	);
	*/

	$form->addField(
		'insuno',
		array(
			'type'=>'text',
			'label'=>'เลขกรมธรรม์',
			'class'=>$editClass,
			'readonly'=>!$isEdit,
			'maxlength' => 20,
			'value'=>$jobInfo->insuno,
		)
	);

	$form->addField(
		'insuclaimcode',
		array(
			'type'=>'text',
			'label'=>'เลขรับแจ้งประกันภัย',
			'class'=>$editClass,
			'readonly'=>!$isEdit,
			'maxlength' => 40,
			'value'=>$jobInfo->insuclaimcode,
		)
	);

	$form->addText('</section>');

	$form->addField(
		'commandremark',
		array(
			'type'=>'textarea',
			'label'=>'หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม',
			'class'=>$editClass.' -fill',
			'rows'=>6,
			'value'=>$jobInfo->commandremark,
		)
	);

	if ($isEdit) {
		$form->addField(
			'submit',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
				'container' => array('class'=>'-sg-text-right'),
			)
		);
	}

	if ($isEdit) {
		//$form->submit=$form->submit1;
	}
	$ret.=$form->build();
	//$ret.=print_o($cmdLists,'$cmdLists');

	//$ret.='<br clear="all" />'.print_o(post('job'),'$post');
	$ret.='<script type="text/javascript">
	</script>';
	$ret.='<style type="text/css">
	.active {display:block; margin:0 auto;width:20px;height:20px;line-height:20px;font-size:20px;background-color:green;border-radius:50%;color:#DAFFCE;}
	</style>';
	return $ret;
}
?>