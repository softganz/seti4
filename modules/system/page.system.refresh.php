<?php
/**
 * System  :: Refresh Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-03-05
 * Modify  :: 2026-03-05
 * Version :: 1
 *
 * @param String $url
 * @return Widget
 *
 * @usage module/{Id}/method
 */

class SystemRefresh extends Page {
	var $url;
	var $refreshTime = 10;

	function __construct() {
		parent::__construct([
			'url' => Request::get('url'),
			'refreshTime' => SG\getFirstInt(Request::get('time'), $this->refreshTime),
		]);
	}

	function rightToBuild() {
		if (!is_admin()) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		if ($this->refreshTime < 1) return error(_HTTP_ERROR_BAD_REQUEST, 'Time must br greater than 1');

		return true;
	}

	#[\Override]
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->url,
				'trailing' => new Row([
					'children' => [
						'<span id="time">' . date('Y-m-d H:i:s') . '</span>',
						new Button([
							'type' => 'primary',
							'href' => 'javascript:void(0)',
							'icon' => new Icon('refresh'),
							'text' => 'Refresh',
							'onClick' => 'refresh()'
						])
					], // children
				]), // Row
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Container([
						'id' => 'result'
					]),
					new Container([
						'id' => 'error',
						'child' => '<ol></ol>'
					]),
				], // children
			]), // Widget
			'script' => $this->script(),
		]);
	}

	private function script() {
		head(
			'<script>
			let refreshTime = '.$this->refreshTime.';
			let refreshUrl = "' . $this->url . '?logCounter=no' . '";
			let sendLog = true;

			setInterval(function() {
				refresh();
			}, refreshTime * 1000);

			refresh();

			function refresh() {
				$.get(refreshUrl)
				.done((data) => {
					$("#time").text(new Date().toLocaleString("th-TH"));
					$("#result").html(data);
				})
				.fail((response) => {
					console.log(response);
					$("#result").html("<p class=\"error\">ERROR!!!!!</p>");

					let errorCode = 0;
					let errorMessage = response.statusText || nul;

					if (response.responseJSON) {
						errorCode = response.responseJSON.responseCode || 0;
						errorMessage = response.responseJSON.errorMessage || errorMessage;
					}

					$("#error ol").append("<li><span>ERROR: " + new Date().toLocaleString("th-TH") + " [" + errorCode + "] </span>" + errorMessage + "</li>");

					if (sendLog) {
						console.log("SEND LOG");
						console.log({
								url: "'._DOMAIN.$_SERVER['REQUEST_URI'].'",
								type: "PHP Down",
								host: "'._DOMAIN.$_SERVER['REQUEST_URI'].'",
								date: "'.date('Y-m-d H:i:s').'",
								description: "PHP was Down",
							});
						$.post(
							"https://service.softganz.com/system/issue/new",
							{
								url: "'._DOMAIN.$_SERVER['REQUEST_URI'].'",
								type: "PHP Down",
								// host: "PHP Down",
								date: "'.date('Y-m-d H:i:s').'",
								description: "PHP was Down",
							}
						).done((data) => {
						console.log(data)
						}).fail((response) => {
							console.log("FAIL", response);
						});
					}
				});
			}
			</script>
			<style>
			.page.-footer {display: none;}
			#result {
				white-space: pre-wrap; font-family: monospace;
				.error {
					padding: 3rem; text-align: center;
					color: red; font-size: 3rem; background-color: red; color: #fff;
				}
			}
			#error {
				header {display: flex; align-items: center; gap: 1rem;}
			}
			</style>'
		);
	}
}
// 'user' => function_exists('i') ? i()->uid : NULL,
// 'name' => function_exists('i') ? i()->name : NULL,
// 'file' => NULL,
// 'line' => NULL,
// 'description' => NULL,
// 'data' => (Object) [
// 	'get' => (Object) Request::get(),
// 	'post' => (Object) Request::post(),
// ]

?>