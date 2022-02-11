<?php
/**
* Module :: Description
* Created 2021-12-24
* Modify  2021-12-24
*
* @param Int $userId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

class ImedMyApi extends Page {
	var $userId;
	var $action;
	var $tranId;
	var $right;
	var $userInfo;

	function __construct($action, $tranId = NULL) {
		$this->userId = SG\getFirst(post('userId'), i()->uid);
		$this->action = $action;
		$this->tranId = $tranId;
		$this->right = (Object) [
			'edit' => (i()->ok && $this->userId == i()->uid) || is_admin(),
		];
		// $this->userInfo = $userInfo;
	}

	function build() {
		// debugMsg('userId '.$this->userId.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->userId)) return message(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $userInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $userInfo->RIGHT & _IS_EDITABLE;

		if (!$this->right->edit) return message(['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'servable.save' :
				if (($servId = $this->tranId) && ($scoreValue = post('score'))) {
					mydb::query(
						'INSERT INTO %imed_servable%
						(`uid`, `servId`, `score`, `modified`)
						VALUES
						(:userId, :servId, :score, :modified)
						ON DUPLICATE KEY UPDATE
						`score` = :score
						, `modified` = :modified',
						[
							':userId' => $this->userId,
							':servId' => $servId,
							':score' => $scoreValue,
							':modified' => date('U')
						]
					);
					// $ret .= mydb()->_query;
				}
				break;

			case 'update.info':
				$ret = $this->_updateInfo();
				break;

			default:
				return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}

	function _updateInfo() {
		$data = (Object) post();
		$field = $data->fld;
		$value = $data->value;
		$isDebugable = debug('inline');
		list($group, $key) = explode(':', $data->group);
		list($returnType, $returnFormat) = explode(':',$data->returnType);

		$result = [
			'group' => $group,
			'key' => $key,
			'field' => $data->fld,
			'debug' => 'userId = '.$this->userId.', group='.$group.', part='.$part.', fld='.$field.', tr='.$tranId.'<br />Value='.$data->value.'<br />,return type='.$returnType.'<br />',
		];

		$values = (Object) [
			'userId' => $this->userId,
			'value' => $value,
		];

		switch ($group) {
			case 'profile':
				if ($field == 'fullName') {
					list($values->firstName, $values->lastName) = sg::explode_name(' ', $value);
					$stmt = 'UPDATE %users% SET `real_name` = :firstName, `last_name` = :lastName WHERE `uid` = :userId LIMIT 1';
				} else {
					$stmt = 'UPDATE %users% SET $FIELD$ = :value WHERE `uid` = :userId LIMIT 1';
				}
			break;

			case 'bigdata':
				import('model:bigdata.php');
				BigDataModel::update($key, $value);
			break;

			case 'bigdataJson':
				import('model:bigdata.php');
				BigDataModel::updateJson($key, (Object) [$field => $value]);
			break;
		}

		if ($stmt) {
			mydb::value('$FIELD$', '`'.$field.'`', false);
			mydb::query($stmt, $values);
			// debugMsg(mydb()->_query);
		}

		$result['debug'] .= 'QUERY : '.str_replace("\r", '<br />', mydb()->_query).'<br />';

		if ($returnType=='html') {
			$result['value'] = nl2br($data->value);
		} else {
			$result['value'] = $data->value;
		}

		if (!$isDebugable) unset($result['debug']);

		return $result;
	}
}
?>