<?php
/**
* Calendar Room
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $calId
* @param String $action
* @param Int $tranId
* @param _POST Array $reg
* @return String
*/

$debug = true;

function calendar_room($self, $resvId = NULL, $action = NULL, $tranId = NULL) {

	if (empty($resvId) && empty($action)) return R::Page('calendar.room.home',$self);
	if ($resvId && empty($action)) return R::Page('calendar.room.view',$self, $resvId);

	if ($resvId) {
		$resvInfo = R::Model('calendar.get.resv',$resvId);

	}

	$isAcces = $projectInfo->RIGHT & _IS_ACCESS;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	switch ($action) {

		case 'save':
			if ($_POST) {
				$post=(object)post('room',_TRIM+_STRIPTAG);
				if (empty($post->resvid)) $post->resvid = NULL;
				if (empty($post->checkin)) $field_missing[]='วันที่-เดือน-ปีที่จอง';
				if (empty($post->from_time) || empty($post->to_time)) $field_missing[]='ตั้งแต่เวลา-ถึงเวลา';
				if (empty($post->resv_by)) $field_missing[]='จองโดยใคร';
				if (empty($post->org_name) && empty($post->org_name_etc)) $field_missing[]='หน่วยงานอะไร';
				if (empty($post->title)) $field_missing[]='ทำอะไร';
				if ($field_missing) $error[]='กรุณาป้อนข้อมูลต่อไปนี้ให้ครบถ้วน : <ol><li>'.implode('</li><li>',$field_missing).'</li></ol>';

				if (!$error) {
					// start save new item
					$simulate=debug('simulate');

					//preg_match($this->date_format,$post->checkin,$from_date);

					$post->checkin=sprintf('%04d',$from_date[3]).'-'.sprintf('%02d',$from_date[2]).'-'.sprintf('%02d',$from_date[1]);
					$post->calid=SG\getFirst($post->calid,'func.NULL');
					$post->uid=SG\getFirst(i()->uid,'func.NULL');
					$post->org_name=SG\getFirst($post->org_name,$post->org_name_etc);
					$post->equipment=SG\getFirst(implode(',',$post->equipment),'func.NULL');
					$post->created=date('U');
					$stmt='INSERT INTO %calendar_room%
										(`calid`, `roomid`, `uid`, `title`, `body`, `resv_by`, `org_name`, `checkin`, `from_time`, `to_time`, `peoples`, `equipment`, `phone`, `created`)
									VALUES
										(:calid, :roomid, :uid, :title, :body, :resv_by, :org_name, :checkin, :from_time, :to_time, :peoples, :equipment, :phone, :created)
									ON DUPLICATE KEY UPDATE
									`roomid`=:roomid
									, `title`=:title
									, `body`=:body
									, `resv_by`=:resv_by
									, `org_name`=:org_name
									, `checkin`=:checkin
									, `from_time`=:from_time
									, `to_time`=:to_time
									, `peoples`=:peoples
									, `equipment`=:equipment
									, `phone`=:phone';
					mydb::query($stmt,$post);
					//				$ret.=print_o($post,'$post');
					//				$ret.=mydb()->_query;

				}
			}
			break;

		case 'edit':
			$ret .= R::Page('calendar.room.post', $self, $resvInfo);
			break;

		default:
			//if (empty($action)) $action='home';
			//$ret = R::Page('project.join.'.$action, $self, $projectInfo, $calendarInfo, $action, $tranId);

			$argIndex = 4; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'calendar.room.'.$action,
								$self,
								$resvInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($calendarInfo, '$calendarInfo');

	return $ret;
}
?>