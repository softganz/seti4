<?php
/**
 * render  :: Widget
 * Created :: 2025-10-31
 * Modify  :: 2025-10-31
 * Version :: 1
 *
 * @param Array $args
 * @return Object
 *
 * @usage import('widget:module.widgetlname.php')
 * @usage new renderAjaxWidgetl([])
 */

class renderAjaxWidget extends Widget {
	var $requestResult;
	
	function __construct($requestResult) {
		parent::__construct([
			'requestResult' => $requestResult,
		]);
	}

	#[\Override]
	function build() {
		// AJAX Call process
		// Check error result
		$ajaxResult = [];
		// debugMsg('ERROR MESSAGE');
		// debugMsg($this->pageBuildWidget, '$this->pageBuildWidget');
		
		if (is_object($this->requestResult) && $this->requestResult->widgetName === 'ErrorMessage') {
			$ajaxResult = $this->requestResult->build();
		} else if (is_object($this->requestResult) && method_exists($this->requestResult, 'build')) {
			$this->requestResult = $this->renderWidget();
		}

		// if (is_object($this->pageBuildWidget) && $this->pageBuildWidget->widgetName === 'ErrorMessage') {
		// 	debugMsg('ErrorMessage');
		// 	if ($this->pageBuildWidget->responseCode) $ajaxResult['responseCode'] = $this->pageBuildWidget->responseCode;
		// 	if ($this->pageBuildWidget->text) $ajaxResult['errorMessage'] = $this->pageBuildWidget->errorMessage;
		// } else if (is_object($this->requestResult)) {
		// 	if ($this->requestResult->responseCode) $ajaxResult['responseCode'] = $this->requestResult->responseCode;
		// 	if ($this->requestResult->text) $ajaxResult['errorMessage'] = $this->requestResult->errorMessage;
		// 	$ajaxResult = $ajaxResult + (Array) $this->requestResult;
		// }

		// Send error with json
		if ($ajaxResult['responseCode']) {
			sendHeader('application/json');
			http_response_code($ajaxResult['responseCode']);
			die(json_encode($this->requestResult->build(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		}

		if (is_array($this->requestResult) || is_object($this->requestResult)) {
			sendHeader('application/json');
			$this->requestResult = json_encode($this->requestResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		}

		die(debugMsg().process_widget($this->requestResult));
	}

	private function renderWidget() {
		$ret = '';
		if ($this->requestResult->appBar->boxHeader) {
			$ret .= $this->requestResult->appBar->build();
		}
		$ret .= $this->requestResult->build();
		return $ret;
	}
}
?>