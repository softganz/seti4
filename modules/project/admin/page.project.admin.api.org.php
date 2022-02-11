<?php
/**
* Module :: Description
* Created 2021-09-25
* Modify  2021-09-25
*
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

$debug = true;

class ProjectAdminApiOrg extends Page {
	var $orgId;
	var $action;
	var $tranId;

	function __construct($orgInfo, $action, $tranId = NULL) {
		$this->orgId = $orgInfo->orgId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		debugMsg('orgId '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->orgId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $orgInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $orgInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'foo' :
				return 'Foo';
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>
<?php
/**
* Project Organization Admin
*
* @param Object $self
* @return String
*/
function project_admin_org($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$searchText = post('q');
	$orgId = SG\getFirst($orgId,post('orgid'));

	if (empty($action) && empty($orgId)) $action = 'home';
	else if (empty($action)) $action = 'view';

	$orgInfo = $orgId ? R::Model('project.org.get', $orgId) : NULL;

	switch ($action) {
		case 'addorg':
			$post=(object)post('org');
			if (empty($post->name)) {
				$ret.=__project_admin_org_form();
				return $ret;
			}
			$post->uid=i()->uid;
			$post->created=date('U');
			if (empty($post->parent)) $post->parent='func.NULL';
			$stmt='INSERT INTO %db_org%
									(`name`, `shortname`, `uid`, `parent`, `sector`, `created`)
								VALUES
									(:name, :shortname, :uid, :parent, :sector, :created)';
			mydb::query($stmt,$post);
			$newid=mydb()->insert_id;
			//location('project/admin/org',array('id'=>$newid));
			return $ret;
			break;

		case 'deleteorg':
			if ($uid && post('orgid')) {
				mydb::query('DELETE FROM %org_officer% WHERE `orgid`=:orgid AND `uid`=:uid LIMIT 1',':orgid',post('orgid'),':uid',$uid);
			}
			break;

		case 'changerole':
			if (post('action')=='changerole' && $uid && post('role')) {
				mydb::query('UPDATE %users% SET `roles`=:roles WHERE `uid`=:uid LIMIT 1',':uid',$uid,':roles',post('role'));
				$ret.=mydb()->_query;
			}
			break;

		case 'changparent':
			if ($orgId) {
				$ret.=R::Page('project.admin.org.parent',$self,$orgId);
			}
			break;

		case 'addofficer':
			if ($orgId && post('uid')) {
				$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:orgid, :uid, :membership) ON DUPLICATE KEY UPDATE `membership` = :membership';
				mydb::query($stmt, ':orgid', $orgId, ':uid', post('uid'), ':membership', post('membership'));
				$ret .= R::Page('project.admin.org.view', $self, $orgId, 'edit');
			}
			break;

		default:
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'project.admin.org.'.$action,
								$self,
								$orgInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';
			//$ret .= print_o($orgInfo, '$orgInfo');

			/*
			if ($orgId)
				$ret .= R::Page('project.admin.org.view', $self, $orgId, $action);
			else
				$ret .= R::Page('project.admin.org.list', $self);
			break;
			*/
	}
	//$ret.=print_o(post(),'post');
	return $ret;
}
?>