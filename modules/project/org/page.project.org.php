<?php
/**
* Project Organication
*
* @param Object $self
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*/
function project_org($self, $orgId = NULL, $action = NULL, $tranId = NULL) {

	if (empty($action) && empty($orgId)) return R::Page('project.org.home',$self);
	if (empty($action) && $orgId) {
		return R::Page('project.org.view',$self,$orgId);
	}

	$ret = '';

	$orgInfo = R::Model('project.org.get', $orgId, '{debug: false}');

	if (!$orgInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$isWebAdmin = i()->admin;
	$isAdmin = $orgInfo->info->isAdmin || (i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN')));
	$isOwner = $orgInfo->info->isOwner;
	$isTrainer = i()->ok && array_key_exists(i()->uid, $orgInfo->officers) && in_array($orgInfo->officers[i()->uid],array('ADMIN','TRAINER'));

	$isEditable = $isAdmin || $isOwner || $isTrainer;
	$isEditTrainer = $isEdit || in_array('coretrainer', i()->roles) ;


	R::View('project.toolbar', $self, 'บริหารงานโครงการขององค์กร', 'org', $orgInfo);

	//$ret .= print_o($orgInfo, '$orgInfo');

	switch ($action) {
		case 'member.add':
			if ($isAdmin && post('uid') && post('membership')) {
				$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership) ON DUPLICATE KEY UPDATE `uid` = :uid';
				mydb::query($stmt, ':orgid', $orgId, post());
				$ret .= mydb()->_query.print_o(post());
			}
			break;

		case 'member.save':
			$data=(object)post();

			$data->addusername=trim($data->addusername);
			$data->addpassword=trim($data->addpassword);
			if (empty($data->addpassword)) $data->addpassword=substr(md5(uniqid()), 0, 8);
			$data->name=trim($data->name);
			$data->email=trim($data->email);
			$data->phone=trim($data->phone);
			$data->address=trim($data->address);
			$data->encpassword=sg_encrypt($data->addpassword,cfg('encrypt_key'));
			$data->datein=date('Y-m-d H:i:s');
			$data->status='enable';
			$data->admin_remark='Add by '.i()->username;
			$stmt='INSERT INTO %users% (`username`, `password`, `name`, `status`, `email`, `phone`, `address`, `datein`, `admin_remark`)
						VALUES
						(:addusername, :encpassword, :name, :status, :email, :phone, :address, :datein, :admin_remark)';
			mydb::query($stmt,$data);
			if (!mydb()->_error) {
				$data->uid=mydb()->insert_id;
				$data->orgid=$orgInfo->orgid;
				$data->membership="MEMBER";
				$stmt='INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;

				$ret.='<div class="box">';
				$ret.='<h3>รายละเอียดสมาชิก</h3><br /><br />';
				$ret.='Username : '.$data->addusername.'<br /><br />';
				$ret.='Password : '.$data->addpassword.'<br /><br />';
				$ret.='ชื่อ-นามสกุล : '.$data->name.'<br /><br />';
				$ret.='อีเมล์ : '.$data->email.'<br /><br />';
				$ret.='โทรศัพท์ : '.$data->phone.'<br /><br />';
				$ret.='</div>';
				$ret.='<a class="btn" href="'.url('project/org/member/'.$orgInfo->orgid).'"><i class="icon -back"></i><span>รายชื่อสมาชิกทั้งหมด</span></a>';
			} else {
				$ret.='<p class="notify">มีความผิดพลาดในการสร้างสมาชิก</p>';
				$ret.=R::Page('project.org.member.create',$self,$orgInfo,$data);
			}
			//$ret.=print_o($data,'$data');
			break;

		case 'member.remove':
			if ($isAdmin && $tranId && SG\confirm()) {
				if ($orgInfo->officers[$tranId] == 'MEMBER') {
					$stmt = 'UPDATE %users% SET `status` = "disable" WHERE `uid` = :uid LIMIT 1';
					mydb::query($stmt, ':uid', $tranId);
				} else {
					$stmt = 'DELETE FROM %org_officer% WHERE `orgid` = :orgid AND `uid` = :uid LIMIT 1';
					mydb::query($stmt, ':orgid', $orgId, ':uid', $tranId);
				}
			}
			break;


		default:
			$_args = func_get_args();
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			return R::PageWidget(
				'project.org.'.$action,
				[-1 => $orgInfo] + array_slice($_args, $argIndex)
			);

			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			break;
	}

	return $ret;
}
?>
<?php
/**
* Module :: Page Controller
* Created 2021-01-01
* Modify 	2021-01-01
*
* @param Int $mainId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

$debug = true;

class PageController extends Page {
	var $mainId;
	var $action;
	var $_args = [];

	function __construct($mainId = NULL, $action = NULL) {
		$this->mainId = $mainId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		debugMsg('Id '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $mainInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$mainInfo = is_numeric($this->mainId) ? Model::get($this->mainId, '{debug: false}') : NULL;

		if (empty($this->mainId) && empty($this->action)) $this->action = 'home';
		else if ($this->mainId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->mainId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'page.sub.'.$this->action,
			[-1 => $mainInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>