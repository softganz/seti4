<?php
/**
* Show Job Status
* Created 2019-08-20
* Modify  2019-08-20
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function view_garage_job_statusbar($self, $jobInfo) {
	$statusList = GarageVar::$jobStatusList;
	$jobProcessList = GarageVar::$jobProcessList;

	$jobStatus=$jobInfo->jobstatus;
	$ret='<div class="garage-job-statusbar">';
	$ret.='<h3>Job Status Bar</h3>';
	$ui=new Ui(NULL,'ui-statusbar');
	for ($i=1;$i<=10;$i++) {
		$status=false;
		if ($i==1 && $jobInfo->carindate) $status=true;
		else if ($i==2 && !empty($jobInfo->qt)) $status=true;
		else if ($i==3 && !empty($jobInfo->replyprice)) $status=true;
		else if ($i==4 && !empty($jobInfo->waitPart)) $status=true;
		else if ($i==6 && $jobInfo->iscarreturned=='Yes') $status=true;
		else if ($i==7 && !empty($jobInfo->billing)) $status=true;
		else if ($i==9 && $jobInfo->isrecieved=='Yes') $status=true;
		else if ($i==10 && $jobInfo->isjobclosed=='Yes') $status=true;
		//else $status=$i==$jobStatus;

		if ($i == 5) {
			$jobProcessText = $jobInfo->jobprocess ? $jobProcessList[$jobInfo->jobprocess] : 'ยังไม่เริ่มดำเนินการซ่อม';
			$ui->add('<a class="sg-action status -s'.$i.' -process-'.($jobInfo->jobprocess).($jobInfo->jobprocess?' -active':'').'" href="'.url('garage/job/'.$jobInfo->tpid.'/process').'" data-tooltip="'.$jobProcessText.'" data-rel="box" data-width="480">'.SG\getFirst($jobInfo->jobprocess,'?').'</a>');
		} else {
			$ui->add('<a class="status -s'.$i.($status?' -active':'').'" href="javascript:void(0)" data-tooltip="'.$statusList[$i].'">'.$i.'</a>');
		}
	}
	$ret.=$ui->build();
	$ret.='</div>';
	//$ret.=print_o($jobInfo,'$jobInfo');
	return $ret;
}
?>