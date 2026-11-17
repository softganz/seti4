<?php
/**
 * Paper    :: Page Controller
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2018-06-04
 * Modified :: 2026-11-17
 * Version  :: 3
 *
 * @param int $nodeId
 * @param string $action
 *
 * @uses paper[/{nodeId}/{action}]
 */

use Paper\Model\PaperModel;

class Paper extends PageController {
	public $nodeId;

	function __construct($nodeId = null, $action = null) {
		if (empty($nodeId) && empty($action)) {
			$action = 'home';
		} elseif ($nodeId && empty($action)) {
			$action = 'view';
		}

		parent::__construct([
			'nodeId' => $nodeId,
			'action' => 'paper.' . $action,
			'args' => func_get_args(),
			'info' => is_numeric($nodeId)
				? PaperModel::get($nodeId, '{initTemplate: true}')
				: null
		]);
	}

	function build() {
		// debugMsg('Id '.$this->nodeId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;

		// if (!$isAccess) {
		// return new ErrorMessage(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// }

		return parent::build();
	}
}
?>
