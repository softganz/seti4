<?php
/**
* Garage Job Do
* Created 2019-11-24
* Modify  2019-11-25
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_do($self, $jobInfo) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'PROCESS ERROR');


	new Toolbar($self,'ใบสั่งงาน - '.$jobInfo->plate.'@'.$jobInfo->shopShortName,'job',$jobInfo);
	page_class('-job-type-'.$jobInfo->shopShortName);

	$isEditable = in_array($jobInfo->shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
	$isViewable = $jobInfo->shopInfo->iam;

	$ret = '';



	$ret .= '<div class="garage-job-do sg-view -co-2">';

	$tables = new Table();

	$tables->colgroup = array(
		'detail' => '',
		'code -center' => '',
		'repair-1 -nowrap' => '',
		'repair-2 -nowrap' => '',
		'repair-3 -nowrap' => '',
		'repair-4 -nowrap' => '',
		'i -nowrap -hover-parent' => '',
	);

	$tables->thead = array(
		'รายการสั่งซ่อม',
		'code -center' => '', // รหัสความเสียหาย
		'<th class="-nowrap">'
		. ($isEditable ? '<a class="sg-action btn -link" href="'.url('garage/job/'.$jobId.'/assign',array('ty' => 'ช่างเคาะ')).'" data-rel="box" data-width="480">ช่างเคาะ <i class="icon -material'.($jobInfo->do['ช่างเคาะ'] ? ' -green' : '').' -no-print">add_circle_outline</i></a>' : 'ช่างเคาะ')
		. '</th>',
		'repair-1 -nowrap' => '<th colspan="2">'
			. ($isEditable ? '<a class="sg-action btn -link" href="'.url('garage/job/'.$jobId.'/assign',array('ty' => 'ช่างพื้น')).'" data-rel="box" data-width="480">ช่างพื้น <i class="icon -material'.($jobInfo->do['ช่างพื้น'] ? ' -green' : '').' -no-print">add_circle_outline</i></a>' : 'ช่างพื้น')
			. '</th>',
		'repair-3 -nowrap' => $isEditable ? '<a class="sg-action btn -link" href="'.url('garage/job/'.$jobId.'/assign',array('ty' => 'ช่างพ่นสี')).'" data-rel="box" data-width="480">ช่างพ่นสี <i class="icon -material'.($jobInfo->do['ช่างพ่นสี'] ? ' -green' : '').' -no-print">add_circle_outline</i></a>' : 'ช่างพ่นสี',
		'repair-4 -nowrap -hover-parent' => '<a class="-no-print" href="javascript:viod(0)" onClick="$(\'.item-repair\').hide();$(this).closest(\'tr\').hide();return false;"><i class="icon -material">visibility</i></a>'
	);
	foreach ($jobInfo->command as $rs) {
		$ui = new Ui();
		if ($isEditable) {
			$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/do.hide/'.$rs->jobtrid).'" data-rel="notify" data-done="load"><i class="icon -material '.($rs->done < 0 ? '-sg-inactive' : '-sg-active').'">assignment_turned_in</i></a>');
		}
		$menu = '<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';

		$tables->rows[]=array(
			$rs->repairname,
			$rs->damagecode,
			'<span class="result'.($rs->photoCount1 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพเคาะ-ดึง',
			'<span class="result'.($rs->photoCount2 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพโป๊ว',
			'<span class="result'.($rs->photoCount3 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพพื้น',
			'<span class="result'.($rs->photoCount4 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพพ่นสี',
			$menu,
			'config'=>array('class'=>'item-repair '.($rs->done < 0 ? '-notdo' : '-do')),
		);
	}
	$tables->rows[] = array(
		'<th>รายการอะไหล่</th>',
		'<th>ชิ้น</th>',
		'<th class="-nowrap">'
		. ($isEditable ? '<a class="sg-action btn -link" href="'.url('garage/job/'.$jobId.'/assign',array('ty' => 'ช่างเคาะ')).'" data-rel="box" data-width="480">ช่างเคาะ <i class="icon -material'.($jobInfo->do['ช่างเคาะ'] ? ' -green' : '').' -no-print">add_circle_outline</i></a>' : 'ช่างเคาะ')
		. '</th>',
		'<th></th>',
		'<th class="-nowrap">'
		. ($isEditable ? '<a class="sg-action btn -link" href="'.url('garage/job/'.$jobId.'/assign',array('ty' => 'ช่างประกอบ')).'" data-rel="box" data-width="480">ช่างประกอบ <i class="icon -material'.($jobInfo->do['ช่างประกอบ'] ? ' -green' : '').' -no-print">add_circle_outline</i></a>' : 'ช่างประกอบ')
		. '</th>',
		'<th></th>',
		'<th><a class="-no-print" href="javascript:viod(0)" onClick="$(\'.item-part\').hide();$(this).closest(\'tr\').hide();return false;"><i class="icon -material">visibility</i></a></th>',
	);
	foreach ($jobInfo->part as $rs) {
		$ui = new Ui();
		if ($isEditable) {
			$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/do.hide/'.$rs->jobtrid).'" data-rel="notify" data-done="load"><i class="icon -material '.($rs->done < 0 ? '-gray' : '-green').'">assignment_turned_in</i></a>');
		}
		$menu = '<nav class="nav -icons -hover -no-print">'.$ui->build().'</nav>';

		$tables->rows[]=array(
			$rs->repairname,
			'('.$rs->qty.')',
			'<span class="result'.($rs->photoCount5 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพคู่ซาก',
			'',
			'<span class="result'.($rs->photoCount6 ? ' -active' : '').'"><i class="icon -material">done</i></span>ภาพคู่ซาก',
			'',
			$menu,
			'config'=>array('class'=>'item-part '.($rs->done < 0 ? '-notdo' : '-do')),
		);
	}

	$ret .= '<div class="garage-job-tran -sg-view">';
	$ret .= ($tables->rows?$tables->build():'ยังไม่มีรายการสั่งซ่อม')._NL;

	$ret .= '<div class="remark"><b>หมายเหตุหรือคำสั่งซ่อมเพิ่มเติม</b><br />'.nl2br($jobInfo->commandremark).'</div>';

	$ret .= '</div>';

	$ret .= '<div class="garage-job-title -sg-view">';

	$ret .= '<header class="header -no-print"><h4>รายละเอียดรถ</h4></header>';
	$ret .= '<span>เลขใบสั่งซ่อม '.$jobInfo->jobno.'</span>';
	$ret .= '<span>ทะเบียน '.$jobInfo->plate.'</span>';
	$ret .= '<span>วันที่ '.sg_date($jobInfo->rcvdate,'ว ดด ปปปป').'</span>';
	$ret .= '<span>ยี่ห้อ '.$jobInfo->brandid.'</span>';
	$ret .= '<span>รุ่น '.$jobInfo->modelname.'</span>';
	$ret .= '<span>สี '.$jobInfo->colorname.'</span>';
	$ret .= '<span>เลขรถรอ '.$jobInfo->carwaitno.'</span>';
	$ret .= '<span>เลขรถเข้า '.$jobInfo->carinno.'</span>';
	$ret .= '<span class="insurer">ประกัน '.$jobInfo->insurername.'</span>';
	$ret .= '<span class="customer">ชื่อลูกค้า '.$jobInfo->customername;
	$ret .= ' โทร '.$jobInfo->customerphone.'</span>';
	$ret .= '<span>วันที่นัดรับรถ '.($jobInfo->datetoreturn ? sg_date($jobInfo->datetoreturn,'d/m/Y') : '').'</span>';
	$ret .= '<span>เวลานัดรับรถ '.substr($jobInfo->timetoreturn,0,5).'</span>';
	$ret .= '<span>หมายเลขตัวถัง'.$jobInfo->bodyno.'</span>';
	$ret .= '<span>หมายเลขเครื่อง '.$jobInfo->enginno.'</span>';
	$ret .= '<span>เลขไมล์ '.$jobInfo->milenum.'</span>';


	$ret .= '</div>';



	$ret .= '</div><!-- garage-job-do -->';

	//$ret .= print_o($jobInfo, '$jobInfo');

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
	.result {display:inline-block;width:24px;height:24px;margin:0 4px 0 20px;border:1px #ccc solid; vertical-align:middle;}
	.result>.icon {display: none;}
	.result.-active {background-color: green; border: 1px green solid; border-radius: 50%;}
	.result>.icon {display: block; color: #fff;}
	.row.-notdo>td {color: gray; text-decoration: line-through;}
	.garage-job-do.sg-view.-co-2>.garage-job-tran {padding: 0;}
	@media print {
		.result>.icon {display: none;}
		.result.-active>.icon {color: #000; display: block;}
		.row.-notdo>td {color: #000; text-decoration: none;}
		.garage-job-tran .col.-detail {font-size: 0.8em;}
		.garage-job-do.sg-view.-co-2>.garage-job-title.-sg-view {margin: 0 0.1cm 0.5cm; border: 2px #000 solid; border-radius: 8px; padding: 4px; display: flex; flex-wrap: wrap; flex: auto; justify-content: left; order: -1;}
		.garage-job-do.sg-view.-co-2>.garage-job-title.-sg-view>* {flex: initial; border: none;}
		.garage-job-do.sg-view.-co-2>.garage-job-title.-sg-view>.customer {flex: 1 0 100%;}

	}
	</style>';
	return $ret;
}
?>