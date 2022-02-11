<?php
function garage_do_view($self,$tpid,$action=NULL) {
	$shopInfo=R::Model('garage.get.shop');

	if ($tpid) {
		$jobInfo=R::Model('garage.job.get',$shopInfo->shopid,$tpid,'{debug:false}');
	}

	new Toolbar($self,($action=='edit'?'แก้ไข':'ใบสั่งงาน').' - '.$jobInfo->plate,'job',$jobInfo);

	switch ($action) {
		case 'edit':
			$ret.=__garage_job_view_form($shopInfo,$jobInfo,$action);
			break;

		case 'save':
			$data=(object)post('job');
			$saveResult.=__garage_job_view_save($shopInfo,$tpid,$jobInfo,$data);
			$jobInfo=R::Model('garage.job.get',$shopInfo->shopid,$tpid);
			$ret.=__garage_job_view_form($shopInfo,$jobInfo);
			$ret.=$saveResult;
			location('garage/job/'.$tpid.'/do');
			break;

		default:
			$ret.=__garage_job_view_form($shopInfo,$jobInfo);
			break;
	}


	// $ret.=print_o($jobInfo,'$jobInfo');
	$ret.='<style type="text/css">
	.col-repair {white-space: nowrap;}
	</style>';
	return $ret;
}

function __garage_job_view_form($shopInfo,$data=NULL,$action=NULL) {
	$isEdit=$action==='edit';
	$editClass=$isEdit?'':'-disabled';
	$ret='';


	$brandLists=R::Model('garage.brand.getall',$shopInfo->shopid);
	$modelLists=array('1'=>'CLASS E','2'=>'JAZZ');

	$insurerList=array();
	$dbs=mydb::select('SELECT `insurerid`,`insurername` FROM %garage_insurer% WHERE `shopid`=:shopid ORDER BY CONVERT(`insurername` USING tis620)',':shopid',$shopInfo->shopid);
	foreach ($dbs->items as $rs) $insurerList[$rs->insurerid]=$rs->insurername;

	foreach(R::Model('garage.user.getall',$shopInfo->shopid) as $item) {
		$userLists[$item->uid]=$item->name.' ('.$item->position.')';
	}


	$form=new Form('job',url('garage/do/view/'.$data->tpid.'/save'),'garage-job-new');
	$form->class='garage-job-new -form';

	$form->d1s='<div class="garage-job-new -info">';
	$form->h1='<h3>รายละเอียดรถ</h3>';
	$form->jobno=array(
								'type'=>'text',
								'label'=>'เลขใบสั่งซ่อม',
								'readonly'=>true,
								'class'=>'-highlight -disabled',
								'value'=>$data->jobno,
								);
	$form->plate=array(
								'type'=>'text',
								'label'=>'ทะเบียน',
								'class'=>$editClass.' -highlight',
								'value'=>$data->plate,
								);
	$form->rcvby=array(
								'type'=>'select',
								'label'=>'ผู้รับรถ',
								'class'=>$editClass,
								'value'=>$data->rcvby,
								'options'=>array(''=>'***ระบุชื่อผู้รับรถ***')+$userLists,
								);
	$form->rcvdate=array(
								'type'=>'text',
								'label'=>'วันที่',
								'class'=>'sg-datepicker '.$editClass,
								'value'=>sg_date($data->rcvdate,'ว ดด ปปปป'),
								);
	$form->brandid=array(
								'type'=>'select',
								'label'=>'ยี่ห้อ:',
								'value'=>$data->brandid,
								'options'=>array(''=>'***ระบุยี่ห้อรถ***')+$brandLists,
								'class'=>$editClass,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/car/add').'" data-rel="box" title="เพิ่มยี่ห้อรถใหม่"><i class="icon -add"></i></a>':'',
								);
	$form->model=array(
								'type'=>'text',
								'label'=>'รุ่น:',
								'class'=>$editClass,
								'value'=>$data->modelname,
								//'options'=>$modelLists,
								//'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/model/add').'" data-rel="box" title="เพิ่มรุ่นรถใหม่"><i class="icon -add"></i></a>':'',
								);
	/*
	$form->color=array(
								'type'=>'select',
								'label'=>'สี:',
								'class'=>$editClass,
								'value'=>$data->color,
								'options'=>$colorLists,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/color/add').'" data-rel="box" title="เพิ่มสีรถใหม่"><i class="icon -add"></i></a>':'',
								);
								*/
	$form->insurerid=array(
								'type'=>'select',
								'label'=>'ประกัน:',
								'class'=>$editClass,
								'value'=>$data->insurerid,
								'options'=>array('')+$insurerList,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/insu/add').'" data-rel="box" title="เพิ่มประกันใหม่"><i class="icon -add"></i></a>':'',
								);
	$form->custid=array(
								'type'=>'text',
								'label'=>'ชื่อลูกค้า',
								'class'=>$editClass,
								'value'=>$data->customername,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/cust/add').'" data-rel="box" title="เพิ่มลูกค้าใหม่"><i class="icon -add"></i></a>':'',
								);
	$form->datetoreturn=array(
								'type'=>'text',
								'label'=>'วันที่นัดรับรถ',
								'class'=>'sg-datepicker '.$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->datetoreturn?sg_date($data->datetoreturn,'d/m/Y'):'',
								);
	$form->timetoreturn=array(
								'type'=>'time',
								'label'=>'เวลานัดรับรถ :',
								'class'=>$editClass,
								'value'=>substr($data->timetoreturn,0,5),
								'step'=>30,
								'start'=>8,
								'end'=>19,
								);
	$form->enginno=array(
								'type'=>'text',
								'label'=>'หมายเลขตัวถัง',
								'class'=>$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->bodyno,
								);
	$form->bodyno=array(
								'type'=>'text',
								'label'=>'หมายเลขเครื่อง',
								'class'=>$editClass,
								'value'=>$data->enginno,
								);
	$form->milenum=array(
								'type'=>'text',
								'label'=>'เลขไมล์',
								'class'=>$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->milenum,
								);
	if ($isEdit) {
		$form->submit1=array(
									'type'=>'submit',
									'items'=>array('save'=>'บันทึกใบสั่งงาน'),
									);
	}
	$form->d1e='</div>';

	if ($isEdit) {
		$cmdLists=R::Model('garage.template.getlist',$shopInfo->shopid,$data->templateid);

		$tables = new Table();
		$tables->addClass('-center');
		$tables->thead=array('รายการสั่งซ่อม','center -a'=>'A','center -b'=>'B','center -c'=>'C','center -d'=>'D');
		foreach ($cmdLists as $cmdKey=>$cmdItem) {
			$cmdSelectCode=NULL;
			if (array_key_exists($cmdKey, $data->items)) {
				$cmdSelectCode=$data->items[$cmdKey]->damagecode;
			} else if (!$isEdit) {
				continue;
			}
			$tables->rows[]=array(
				$cmdItem.'<input class="job-cmd-value" type="hidden" name="job[cmd]['.$cmdKey.']" value="'.$cmdSelectCode.'" />',
				'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="A" '.($cmdSelectCode==='A'?'checked="checked"':'').' />',
				'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="B" '.($cmdSelectCode==='B'?'checked="checked"':'').'/>',
				'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="C" '.($cmdSelectCode==='C'?'checked="checked"':'').'/>',
				'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="D" '.($cmdSelectCode==='D'?'checked="checked"':'').'/>',
			);
		}
		$retTr.='<div class="garage-job-new -trans">'._NL.'<h3>รายการสั่งซ่อม</h3>'._NL;
		$retTr.=($tables->rows?$tables->build():'ยังไม่มีรายการสั่งซ่อม')._NL;
		if ($isEdit) $retTr.='<p align="right"><a class="" href=""><i class="icon -add"></i><span>เพิ่มรายการสั่งซ่อม</span></a></p>';
		$retTr.='</div>'._NL;

	} else {
		$tables = new Table();
		if (cfg('garage.do.tran.damage.showtable')) {
			$tables->thead=array('รายการสั่งซ่อม','center -a'=>'A','center -b'=>'B','center -c'=>'C','center -d'=>'D','repair -nowrap'=>'ผลการซ่อม');
			$activeStr='<span class="garage-do-cmd -active">✔</span>';
			$inactiveStr='<span class="garage-do-cmd -inactive">✗</span>';
			foreach ($data->command as $rs) {
				$tables->rows[]=array(
					$rs->repairname,
					$rs->damagecode=='A'?$activeStr:$inactiveStr,
					$rs->damagecode=='B'?$activeStr:$inactiveStr,
					$rs->damagecode=='C'?$activeStr:$inactiveStr,
					$rs->damagecode=='D'?$activeStr:$inactiveStr,
					'<span class="result"></span>ภาพโป๊ว<span class="result"></span>ภาพพื้น<span class="result"></span>ภาพพ่นสี',
				);
			}
			$tables->rows[]=array('<th>รายการอะไหล่</th>','<th colspan="5"></th>');
			foreach ($data->part as $rs) {
				$tables->rows[]=array(
					$rs->repairname,
					'','','','',
					'<span class="result"></span>รออะไหล่<span class="result"></span>เรียบร้อย',
				);
			}

			$retTr.='<div class="garage-job-new -trans">'._NL.'<h3>รายการสั่งซ่อม</h3>'._NL;
			$retTr.=($tables->rows?$tables->build():'ยังไม่มีรายการสั่งซ่อม')._NL;
			$retTr.='</div>'._NL;
		} else {
			$tables->thead = array(
				'รายการสั่งซ่อม',
				'center -a'=>'รหัสความเสียหาย',
				'repair-1 -nowrap'=>'<th colspan="2">ช่างพื้น</th>',
				'repair-3 -nowrap'=>'ช่างพ่นสี',
				'repair-4 -nowrap' => '<a class="-no-print" href="javascript:viod(0)" onClick="$(\'.item-repair\').hide();$(this).closest(\'tr\').hide();return false;"><i class="icon -up"></i></a>'
			);
			foreach ($data->command as $rs) {
				$tables->rows[]=array(
					$rs->repairname,
					$rs->damagecode,
					'<span class="result"></span>ภาพโป๊ว',
					'<span class="result"></span>ภาพพื้น',
					'<span class="result"></span>ภาพพ่นสี',
					'config'=>array('class'=>'item-repair'),
				);
			}
			$tables->rows[] = array(
				'<th>รายการอะไหล่</th>',
				'<th></th>',
				'<th colspan="2">ช่างเคาะ</th>',
				'<th>ช่างประกอบ</th>',
				'<th><a href="javascript:viod(0)" onClick="$(\'.item-part\').hide();$(this).closest(\'tr\').hide();return false;"><i class="icon -up"></i></a></th>',
			);
			foreach ($data->part as $rs) {
				$tables->rows[]=array(
					$rs->repairname,
					'',
					'<span class="result"></span>ภาพเคาะ-ดึง',
					'<span class="result"></span>ภาพคู่ซาก',
					'<span class="result"></span>ภาพคู่ซาก',
					'config'=>array('class'=>'item-part'),
				);
			}
			$retTr.='<div class="garage-job-new -trans">'._NL.'<h3>รายการสั่งซ่อม</h3>'._NL;
			$retTr.=($tables->rows?$tables->build():'ยังไม่มีรายการสั่งซ่อม')._NL;
			$retTr.='</div>'._NL;

		}
	}

	$form->tr=$retTr;

	/*
	$form->commandremark=array(
		'type'=>'textarea',
		'label'=>'หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม',
		'class'=>$editClass,
		'rows'=>3,
		'value'=>$data->commandremark,
	);
	*/
	if ($isEdit) {
		$form->submit=$form->submit1;
	}
	$ret.=$form->build();

	$ret.='<p><b>หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม</b><br />'.nl2br($data->commandremark).'</p>';
	//$ret.=print_o($cmdLists,'$cmdLists');
	//$ret.=print_o($data->items[1],'$data->items[1]');
	//$ret.='<br clear="all" />'.print_o(post('job'),'$post');

	$ret.='<script type="text/javascript">
	$("body").on("click",".cmd",function() {
		var $this=$(this);
		var $parent=$this.closest("tr");
		var $cmdInput=$parent.find(".job-cmd-value");
		if ($this.val()==$cmdInput.val()) {
			$cmdInput.val("");
			$this.prop("checked", false);
		} else {
			$cmdInput.val($this.val());
			$parent.find(".cmd").prop("checked", false);
			$this.prop("checked", true);
		}

		console.log("Click "+$this.val());
		console.log($parent.find(".job-cmd-value").val())
	});
	</script>';
	$ret.='<style type="text/css">
	.result {display:inline-block;width:20px;height:20px;margin:0 4px 0 20px;border:1px #ccc solid; vertical-align:middle;}
	</style>';
	return $ret;
}

function __garage_job_view_form_old($shopInfo,$data=NULL,$action=NULL) {
	$isEdit=$action==='edit';
	$editClass=$isEdit?'':'-disabled';
	$ret='';


	$brandLists=R::Model('garage.brand.getall',$shopInfo->shopid);
	$modelLists=array('1'=>'CLASS E','2'=>'JAZZ');
	$colorLists=array('1'=>'Red','2'=>'Blue','3'=>'Green');
	$insurerLists=array('1'=>'ทิพยประกันภัย','2'=>'ธนชาติประกันภัย','3'=>'สามัคคีประกันภัย');
	foreach(R::Model('garage.user.getall',$shopInfo->shopid) as $item) {
		$userLists[$item->uid]=$item->name.' ('.$item->position.')';
	}


	$form=new Form('job',url('garage/do/view/'.$data->tpid.'/save'),'garage-job-new');
	$form->config->class='garage-job-new -form';

	$form->d1s='<div class="garage-job-new -info">';
	$form->h1='<h3>รายละเอียดรถ</h3>';
	$form->jobno=array(
								'type'=>'text',
								'label'=>'เลขใบสั่งซ่อม',
								'readonly'=>true,
								'class'=>'-highlight -disabled',
								'value'=>$data->jobno,
								);
	$form->plate=array(
								'type'=>'text',
								'label'=>'ทะเบียน',
								'class'=>$editClass.' -highlight',
								'value'=>$data->plate,
								);
	$form->rcvby=array(
								'type'=>'select',
								'label'=>'ผู้รับรถ',
								'class'=>$editClass,
								'value'=>$data->rcvby,
								'options'=>array(''=>'***ระบุชื่อผู้รับรถ***')+$userLists,
								);
	$form->rcvdate=array(
								'type'=>'text',
								'label'=>'วันที่',
								'class'=>'sg-datepicker '.$editClass,
								'value'=>$data->rcvdate,
								);
	$form->brandid=array(
								'type'=>'select',
								'label'=>'ยี่ห้อ:',
								'value'=>$data->brandid,
								'options'=>array(''=>'***ระบุยี่ห้อรถ***')+$brandLists,
								'class'=>$editClass,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/car/add').'" data-rel="box" title="เพิ่มยี่ห้อรถใหม่"><i class="icon -add"></i></a>':'',
								);
	/*
	$form->model=array(
								'type'=>'select',
								'label'=>'รุ่น:',
								'class'=>$editClass,
								'value'=>$data->model,
								'options'=>$modelLists,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/model/add').'" data-rel="box" title="เพิ่มรุ่นรถใหม่"><i class="icon -add"></i></a>':'',
								);
								*/
	/*
	$form->color=array(
								'type'=>'select',
								'label'=>'สี:',
								'class'=>$editClass,
								'value'=>$data->color,
								'options'=>$colorLists,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/color/add').'" data-rel="box" title="เพิ่มสีรถใหม่"><i class="icon -add"></i></a>':'',
								);
	$form->insurerid=array(
								'type'=>'select',
								'label'=>'ประกัน:',
								'class'=>$editClass,
								'value'=>$data->insurer,
								'options'=>$insurerLists,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/insu/add').'" data-rel="box" title="เพิ่มประกันใหม่"><i class="icon -add"></i></a>':'',
								);
	$form->custid=array(
								'type'=>'text',
								'label'=>'ชื่อลูกค้า',
								'class'=>$editClass,
								'value'=>$data->customer,
								'posttext'=>$isEdit?'<a class="sg-action -addcode" href="'.url('garage/cust/add').'" data-rel="box" title="เพิ่มลูกค้าใหม่"><i class="icon -add"></i></a>':'',
								);
								*/
	$form->datetoreturn=array(
								'type'=>'text',
								'label'=>'วันที่นัดรับรถ',
								'class'=>'sg-datepicker '.$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->datetoreturn?sg_date($data->datetoreturn,'d/m/Y'):'',
								);
	$form->timetoreturn=array(
								'type'=>'time',
								'label'=>'เวลานัดรับรถ :',
								'class'=>$editClass,
								'value'=>substr($data->timetoreturn,0,5),
								'step'=>30,
								'start'=>8,
								'end'=>19,
								);
	$form->enginno=array(
								'type'=>'text',
								'label'=>'หมายเลขตัวถัง',
								'class'=>$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->bodyno,
								);
	$form->bodyno=array(
								'type'=>'text',
								'label'=>'หมายเลขเครื่อง',
								'class'=>$editClass,
								'value'=>$data->enginno,
								);
	$form->milenum=array(
								'type'=>'text',
								'label'=>'เลขไมล์',
								'class'=>$editClass,
								'readonly'=>!$isEdit,
								'value'=>$data->milenum,
								);
	if ($isEdit) {
		$form->submit1=array(
									'type'=>'submit',
									'items'=>array('save'=>'บันทึกใบสั่งงาน'),
									);
	}
	$form->d1e='</div>';

	$cmdLists=R::Model('garage.template.getlist',$shopInfo->shopid,$data->templateid);

	$tables = new Table();
	$tables->thead=array('รายการสั่งซ่อม','center -a'=>'A','center -b'=>'B','center -c'=>'C','center -d'=>'D');
	foreach ($cmdLists as $cmdKey=>$cmdItem) {
		$cmdSelectCode=NULL;
		if (array_key_exists($cmdKey, $data->items)) {
			$cmdSelectCode=$data->items[$cmdKey]->damagecode;
		} else if (!$isEdit) {
			continue;
		}
		if ($isEdit) {
			$tables->rows[]=array(
												$cmdItem.'<input class="job-cmd-value" type="hidden" name="job[cmd]['.$cmdKey.']" value="'.$cmdSelectCode.'" />',
												'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="A" '.($cmdSelectCode==='A'?'checked="checked"':'').' />',
												'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="B" '.($cmdSelectCode==='B'?'checked="checked"':'').'/>',
												'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="C" '.($cmdSelectCode==='C'?'checked="checked"':'').'/>',
												'<input class="cmd" type="checkbox" name="cmd['.$cmdKey.']" value="D" '.($cmdSelectCode==='D'?'checked="checked"':'').'/>',
												);
		} else {
			$tables->rows[]=array(
												$cmdItem,
												$cmdSelectCode=='A'?'<span class="-active">✔</span>':'',
												$cmdSelectCode=='B'?'<span class="-active">✔</span>':'',
												$cmdSelectCode=='C'?'<span class="-active">✔</span>':'',
												$cmdSelectCode=='D'?'<span class="-active">✔</span>':'',
												);
		}
	}
	$retTr.='<div class="garage-job-new -trans">'._NL.'<h3>รายการสั่งซ่อม</h3>'._NL;
	$retTr.=($tables->rows?$tables->build():'ยังไม่มีรายการสั่งซ่อม')._NL;
	if ($isEdit) $retTr.='<p align="right"><a class="" href=""><i class="icon -add"></i><span>เพิ่มรายการสั่งซ่อม</span></a></p>';
	$retTr.='</div>'._NL;

	$form->tr=$retTr;

	$form->commandremark=array(
								'type'=>'textarea',
								'label'=>'หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม',
								'class'=>$editClass,
								'rows'=>3,
								'value'=>$data->commandremark,
								);

	if ($isEdit) {
		$form->submit=$form->submit1;
	}
	$ret.=$form->build();

	//$ret.=print_o($cmdLists,'$cmdLists');
	//$ret.=print_o($data->items[1],'$data->items[1]');
	//$ret.='<br clear="all" />'.print_o(post('job'),'$post');

	$ret.='<script type="text/javascript">
	$("body").on("click",".cmd",function() {
		var $this=$(this);
		var $parent=$this.closest("tr");
		var $cmdInput=$parent.find(".job-cmd-value");
		if ($this.val()==$cmdInput.val()) {
			$cmdInput.val("");
			$this.prop("checked", false);
		} else {
			$cmdInput.val($this.val());
			$parent.find(".cmd").prop("checked", false);
			$this.prop("checked", true);
		}

		console.log("Click "+$this.val());
		console.log($parent.find(".job-cmd-value").val())
	});
	</script>';
	$ret.='<style type="text/css">
	.active {display:block; margin:0 auto;width:20px;height:20px;line-height:20px;font-size:20px;background-color:green;border-radius:50%;color:#DAFFCE;}
	</style>';
	return $ret;
}

function __garage_job_view_save($shopInfo,$tpid,$jobInfo,$data) {
	if (empty($tpid) || empty($data->jobno)) return false;

	$data->datetoreturn=sg_date($data->datetoreturn,'Y-m-d');
	$data->milenum=sg_strip_money($data->milenum);
	$stmt='UPDATE %garage_job% SET
					  `rcvby`=:rcvby
					, `datetoreturn`=:datetoreturn
					, `timetoreturn`=:timetoreturn
					, `brandid`=:brandid
					, `plate`=:plate
					, `enginno`=:enginno
					, `bodyno`=:bodyno
					, `milenum`=:milenum
					, `commandremark`=:commandremark
				WHERE `tpid`=:tpid LIMIT 1';
	mydb::query($stmt,':tpid',$tpid, $data);
	//$ret.=mydb()->_query.'<br />';

	// หาวิธีการใหม่
	/*
	if (empty($data->cmd)) $data->cmd=array();
	foreach ($data->cmd as $repairid => $damagecode) {
		if (empty($damagecode)) {
			$stmt='DELETE FROM %garage_jobtr% WHERE `tpid`=:tpid AND `repairid`=:repairid LIMIT 1';
			mydb::query($stmt,':tpid',$tpid, ':repairid',$repairid);
		} else {
			$tr=array();
			$tr['tpid']=$tpid;
			$tr['jobtrid']=$jobInfo->items[$repairid]->jobtrid;
			$tr['uid']=i()->uid;
			$tr['repairid']=$repairid;
			$tr['damagecode']=$damagecode;
			$tr['created']=date('U');
			$stmt='INSERT INTO %garage_jobtr%
							(`jobtrid`, `tpid`, `uid`, `repairid`, `damagecode`, `created`)
						VALUES
							(:jobtrid, :tpid, :uid, :repairid, :damagecode, :created)
						ON DUPLICATE KEY UPDATE
							`damagecode`=:damagecode';
			mydb::query($stmt,$tr);
		}
		//$ret.=mydb()->_query.'<br />';
	}
	*/


	//$ret.=print_o($data,'$data');
	return $ret;
}

?>