<?php
function garage_job_new($self) {
	$self->theme->title='เปิดใบสั่งซ่อมสำหรับรับรถใหม่';

	$ret='';
	$shopInfo=R::Model('garage.get.shop');

	$post=(object)post('job');
	if ($post->newjob && $post->jobno && $post->plate) {
		$jobid=R::Model('garage.job.create',$shopInfo->shopid,$post);
		$ret.='JobId='.$jobid;
		if ($jobid) location('garage/do/view/'.$jobid.'/edit');
	}

	$data=(object)post('job');
	$ret.=__garage_job_new_create($shopInfo,$post);

	$stmt='SELECT * FROM %garage_job% WHERE `shopid`=:shopid AND `isjobclosed`!="Yes" ORDER BY `tpid` DESC LIMIT 20';
	$dbs=mydb::select($stmt,':shopid',$shopInfo->shopid);

	$ret.='<h3>รายการสั่งซ่อมล่าสุด</h3>';
	$tables = new Table();
	$tables->thead=array('เลขใบซ่อม','วันรับรถ','ทะเบียน','รายละเอียดรถ','ลูกค้า','ประกัน','ผู้สั่งซ่อม','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->jobno,
			sg_date($rs->rcvdate,'ว ดด ปป H:i'),
			$rs->plate,
			$rs->brand.' '.$rs->model.$rs->color,
			$rs->customer,
			$rs->insurer,
			$rs->orderby,
			'<a href="'.url('garage/do/view/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -view"></i></a>'
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($shopInfo,'$shopInfo');
	return $ret;
}

function __garage_job_new_create($shopInfo,$data) {
	$templateLists=R::Model('garage.template.getall',$shopInfo->shopid);

	$form=new Form('job',url('garage/job/new'),'garage-job-new');
	$form->class='sg-form garage-job-new';
	$form->checkValid=true;

	$form->newjob=array('type'=>'hidden','value'=>'yes');
	$form->jobno=array(
		'type'=>'text',
		'label'=>'เลขที่ใบสั่งซ่อม',
		'class'=>'-fill -highlight',
		'require'=>true,
		'readonly'=>true,
		'value'=>R::Model('garage.nextno',$shopInfo->shopid,'job')->nextNo,
		'placeholder'=>'ป้อนเลขที่ใบสั่งซ่อม เช่น 60/00000',
		'description'=>'เลขที่ใบสั่งซ่อมจะสร้างให้อัตโนมัติจากเลขที่ล่าสุดในระบบ และไม่สามารถแก้ไขได้',
	);

	$form->plate=array(
		'type'=>'text',
		'label'=>'ทะเบียนรถ',
		'class'=>'-fill -highlight',
		'require'=>true,
		'value'=>$data->plate,
		'placeholder'=>'กก 0000 สข',
	);

	$form->templateid=array(
		'type'=>'select',
		'label'=>'รายการสั่งซ่อม :',
		'class'=>'-fill -highlight',
		'options'=>$templateLists,
		'value'=>$data->templateid,
	);

	$form->desc='กรุณาป้อนข้อมูลเบื้องต้นให้ครบถ้วน ส่วนข้อมูลรายละเอียดอื่น ๆ จะดำเนินการป้อนในหน้าถัดไป';

	$form->submit=array(
		'type'=>'submit',
		'items'=>array('save'=>'เปิดใบสั่งซ่อมสำหรับรับรถใหม่'),
	);

	$ret.=$form->build();
	//$ret.='หรือ <a class="button" href=""><i class="icon -add"></i><span>ขอเลขที่ใบสั่งซ่อมถัดไป</span></a>';
	return $ret;
}

function __garage_job_new_form($shopInfo,$data=NULL) {
	$ret='';

	$brandLists=array('1'=>'BENZ','2'=>'HONDA','3'=>'TOYOTA');
	$modelLists=array('1'=>'CLASS E','2'=>'JAZZ');
	$colorLists=array('1'=>'Red','2'=>'Blue','3'=>'Green');
	$insurerLists=array('1'=>'ทิพยประกันภัย','2'=>'ธนชาติประกันภัย','3'=>'สามัคคีประกันภัย');

	$form=new Form('job',url('garage/job/new'),'garage-job-new');
	$form->class='garage-job-new -form';

	$form->jobno=array(
								'type'=>'text',
								'label'=>'เลขที่ใบสั่งซ่อม',
								'value'=>$data->jobno,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/job/add').'" data-rel="box" title="สร้างเลข JOB ใหม่"><i class="icon -add"></i></a>',
								);
	$form->rcvby=array(
								'type'=>'text',
								'label'=>'ผู้รับรถ',
								'value'=>$data->rcvby,
								);
	$form->rcvdate=array(
								'type'=>'text',
								'label'=>'วันที่',
								'class'=>'sg-datepicker',
								'value'=>$data->rcvdate,
								);
	$form->brand=array(
								'type'=>'select',
								'label'=>'ยี่ห้อ:',
								'value'=>$data->brand,
								'options'=>$brandLists,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/car/add').'" data-rel="box" title="เพิ่มยี่ห้อรถใหม่"><i class="icon -add"></i></a>',
								);
	$form->model=array(
								'type'=>'select',
								'label'=>'รุ่น:',
								'value'=>$data->model,
								'options'=>$modelLists,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/model/add').'" data-rel="box" title="เพิ่มรุ่นรถใหม่"><i class="icon -add"></i></a>',
								);
	$form->plate=array(
								'type'=>'text',
								'label'=>'ทะเบียน',
								'value'=>$data->plate,
								);
	$form->color=array(
								'type'=>'select',
								'label'=>'สี:',
								'value'=>$data->color,
								'options'=>$colorLists,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/color/add').'" data-rel="box" title="เพิ่มสีรถใหม่"><i class="icon -add"></i></a>',
								);
	$form->insurerid=array(
								'type'=>'select',
								'label'=>'ประกัน:',
								'value'=>$data->insurer,
								'options'=>$insurerLists,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/insu/add').'" data-rel="box" title="เพิ่มประกันใหม่"><i class="icon -add"></i></a>',
								);
	$form->custid=array(
								'type'=>'text',
								'label'=>'ชื่อลูกค้า',
								'value'=>$data->customer,
								'posttext'=>'<a class="sg-action -addcode" href="'.url('garage/cust/add').'" data-rel="box" title="เพิ่มลูกค้าใหม่"><i class="icon -add"></i></a>',
								);
	$form->datetoreturn=array(
								'type'=>'text',
								'label'=>'วันรับรถ',
								'class'=>'sg-datepicker',
								'value'=>$data->datetoreturn,
								);
	$form->timetoreturn=array(
								'type'=>'time',
								'label'=>'เวลา:',
								'value'=>$data->timetoreturn,
								);
	$form->enginno=array(
								'type'=>'text',
								'label'=>'หมายเลขตัวถัง',
								'value'=>$data->bodyno,
								);
	$form->bodyno=array(
								'type'=>'text',
								'label'=>'หมายเลขเครื่อง',
								'value'=>$data->enginno,
								);
	$form->milenum=array(
								'type'=>'text',
								'label'=>'เลขไมล์',
								'value'=>$data->milenum,
								);
	$form->submit=array(
								'type'=>'submit',
								'items'=>array('save'=>'บันทึกรับรถใหม่'),
								);
	$ret.=$form->build();

	$cmdLists=array('1'=>'กันชนหน้า','2'=>'สปอยเลอร์กันชนหน้า','3'=>'ฝากระโปรงหน้า','4'=>'กันชนหลัง','5'=>'สปอยเลอร์กันชนหลัง','6'=>'บังโคลนหน้า L','7'=>'ประตูหน้า L','8'=>'ประตูหลัง L');
	$tables = new Table();
	$tables->thead=array('รายการสั่งซ่อม','center -a'=>'A','center -b'=>'B','center -c'=>'C','center -d'=>'D');
	foreach ($cmdLists as $cmdKey=>$cmdItem) {
		$tables->rows[]=array(
											$cmdItem,
											'<input type="radio" name="cmd['.$cmdKey.']" value="A" />',
											'<input type="radio" name="cmd['.$cmdKey.']" value="B" />',
											'<input type="radio" name="cmd['.$cmdKey.']" value="C" />',
											'<input type="radio" name="cmd['.$cmdKey.']" value="D" />',
											);
	}
	$ret.='<div class="garage-job-new -trans">'._NL.'<h3>รายการสั่งซ่อม</h3>'._NL.$tables->build()._NL;
	$ret.='<p align="right"><a class="" href=""><i class="icon -add"></i><span>เพิ่มรายการสั่งซ่อม</a></p>';
	$ret.='</div>'._NL;
	$ret.='<br clear="all" />'.print_o(post('job'),'$post');
	return $ret;
}
?>