<?php
/**
* Project :: Follow Operate Information
* Created 2020-06-04
* Modify 	2021-10-27
*
* @param Int $projectId
* @param String $action
* @return Widget
*
* @usage project[/{id}/{operate}/{tranId}]
*/

$debug = true;

class ProjectOperate extends Page {
	var $projectId;
	var $action;
	var $_args = [];
	var $projectInfo;

	function __construct($projectInfo, $action = NULL, $tranId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->projectId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (!$this->projectId) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

		$projectId = $this->projectId;
		$projectInfo = $this->projectInfo;
		$tranId = $this->tranId;

		// $isAccess = $projectInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		// $projectInfo = is_numeric($this->projectId) ? Model::get($this->projectId, '{debug: false}') : NULL;

		if (empty($this->projectId) && empty($this->action)) $this->action = 'home';
		else if ($this->projectId && empty($this->action)) $this->action = 'home';

		$isOwner = project_model::is_owner_of($projectId);
		$isTrainer = project_model::is_trainer_of($projectId);
		$isAdmin = user_access('administer projects');
		$isTeam = $isAdmin || $isOwner || $isTrainer;
		$isEdit = $projectInfo->info->project_statuscode==1 && $isTeam;
		$isAccessActivityExpense = user_access('access activity expense') || $isOwner;

		$ret = '';
		//$ret .= 'TPID = '.$projectId.' , Action = '.$action.' , TranId = '.$tranId;
		$ret .= '<h3>Project Operate</h3>';

		switch ($this->action) {
			case 'createresult' :
				$period = $tranId;
				if ($isEdit && $period) {
					$formid = 'ส.1';
					//$ret .= 'CREATE PERIOD '.$period;
					// Create if not
					$currentReport=mydb::select('SELECT `period`, COUNT(*) reportItems FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid`=:formid GROUP BY `period`',':tpid',$projectId,':formid','ส.1');

					$stmt = 'SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "ส.1" AND `part`="title" AND `period` = :period LIMIT 1';
					$rs = mydb::select($stmt, ':tpid', $projectId, ':period', $period);
					if ($rs->_empty) {
						$stmt = 'INSERT INTO %project_tr% (`tpid`, `formid`, `period`, `part`, `detail1`, `uid`, `created`) VALUES (:tpid, :formid, :period, :part, :detail1, :uid, :created)';
						mydb::query($stmt, ':tpid', $projectId, ':formid', $formid, ':period', $period, ':part','title', ':detail1', date('d/m/Y'), ':uid', i()->uid, ':created', date('U'));
						//$ret .= mydb()->_query;
						location('project/'.$projectId.'/operate.result/'.$period);
					} else {
						location('project/'.$projectId.'/operate.result/'.$period);
					}
				}
				break;

			case 'removeresult' :
					$period = $tranId;
					mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="ส.1" AND `period`=:period',':tpid',$projectId,':period',$period);
					location('project/'.$projectId.'/operate');
					break;

			case 'm1pass' :
				$period = $tranId;
				project_model::lock_period($projectId,$period,post('step'));
				break;

			case 'm1note' :
				$period = $tranId;
				$periodInfo=project_model::get_period($projectId,$period);
				$noteField=array('note_owner'=>'text1', 'note_complete'=>'text2', 'note_trainer'=>'text3', 'note_hsmi'=>'text4', 'note_sss'=>'text5');
				if ($noteField[post('note')]) {
					$stmt='UPDATE %project_tr% SET `'.$noteField[post('note')].'`=:note WHERE `trid`=:trid LIMIT 1';
					mydb::query($stmt,':trid', $periodInfo->trid, ':note',post('msg'));
					//$ret .= mydb()->_query;
					$ret = 'บันทึกเรียบร้อย';
				}
				//location($url,array('action'=>'status'));
				break;

			case 'm1create' :
				$period = $tranId;
				$periodInfo=project_model::get_period($projectId,$period);
				if ($periodInfo && $periodInfo->from_date && $periodInfo->to_date && $periodInfo->budget) {

					mydb::query('UPDATE %project_tr% SET `flag`=:flag, `detail1`=`date1`, `detail2`=`date2` WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period LIMIT 1', ':tpid', $projectId, ':period', $period, ':flag', _PROJECT_DRAFTREPORT );
					//$ret .= mydb()->_query;

					$stmt='INSERT INTO %project_tr% SET `tpid`=:tpid, `formid`="ง.1", `part`="summary", `period`=:period, `uid`=:uid, `num1`=0, `num2`=0, `num3`=0, `num4`=0, `num5`=0, `num6`=0, `created`=:created';
					mydb::query($stmt,':tpid',$projectId, ':period',$period, ':uid',i()->uid, ':created',date('U'));

					//$ret .= mydb()->_query;
					location('project/'.$projectId.'/operate.m1/'.$period);

				} else {
					$ret.='<p class="notify">ยังไม่มีการกำหนด <strong>งวดที่ '.$period.'</strong> ในรายละเอียดโครงการ หรือ <strong>ป้อนรายละเอียดของงวดไม่ครบถ้วน</strong> เช่น วันเริ่มงวด,วันสิ้นสุดงวด,งบประมาณ<br /> กรุณา <a href="'.url('project/'.$projectId).'">กำหนดงวดที่ '.$period.' และรายละเอียดให้ครบถ้วนก่อน</a></p>';
					return $ret;
				}
				break;

			case 'm1delete' :
				$period = $tranId;
				$periodInfo=project_model::get_period($projectId,$period);
				if ($isAdmin && $tranId && $periodInfo->flag == _PROJECT_DRAFTREPORT) {
					$ret.='Delete report';
					$stmt='DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid`="ง.1" AND `period` = :period';
					mydb::query($stmt,':tpid',$projectId, ':period',$period);
					// debugMsg(mydb()->_query);

					$stmt='UPDATE %project_tr% SET `flag`=NULL, `detail1`=NULL, `detail2`=NULL WHERE `tpid`=:tpid AND `formid`="info" AND `part`="period" AND `period`=:period LIMIT 1';
					mydb::query($stmt,':tpid',$projectId, ':period',$period);
					// debugMsg(mydb()->_query);


					// debugMsg($periodInfo,'$periodInfo');
					location('project/'.$projectId.'/operate');
				}
				break;

			default:
				$argIndex = 2;

				// debugMsg('PAGE CONTROLLER Id = '.$this->projectId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
				//debugMsg($this->_args, '$args');

				return R::PageWidget(
					'project.operate.'.$this->action,
					[-1 => $this->projectInfo] + array_slice($this->_args, $argIndex)
				);
		}

		return $ret;
	}
}
?>