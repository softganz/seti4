<?php
/**
* System  :: Show mobile style Page
* Created :: 2025-06-21
* Modify  :: 2025-06-22
* Version :: 2
*
* @return Widget
*
* @usage system/mobile
*/

class SystemMobile extends Page {
	var $device;
	var $domain;

	function __construct() {
		parent::__construct([
			'device' => SG\getFirst(Request::all('device'), $this->device),
			'domain' => SG\getFirst(Request::all('domain'), _DOMAIN),
		]);
	}

	function build() {
		$this->style();

		return new Container([
			'class' => 'mobile'.' -'.$this->device,
			'id' => 'mobile',
			'children' => [
				new Row([
					'class' => '-header',
					'children' => [
						new DOM(['span', 'child' => date('H:i')]),
						'<spacer>',
						$this->device,
						'<spacer>',
						new Dropbox([
							'icon' => new Icon('devices'),
							'children' => [
								new Form([
									'class' => 'sg-form select-device',
									'method' => 'get',
									'action' => url('system/mobile'),
									'children' => [
										'domain' => [
											'id' => 'dropbox-domain',
											'type' => 'hidden',
											'value' => $this->domain,
										],
										'device' => [
											'type' => 'radio',
											'class' => '-no-wrap',
											'onChange' => 'submit',
											'choice' => [
												'' => 'None',
												'iphone' => 'iPhone',
												'iphone16' => 'iPhone 16',
												'moto-g21' => 'Moto G21',
												'samsung-s23' => 'Samsung Galaxy S23',
												// 'samsung-a54' => 'Samsung Galaxy A54',
												// 'samsung-a34' => 'Samsung Galaxy A34',
												// 'samsung-a14' => 'Samsung Galaxy A14',
											],
										]
									], // children
								]), // Form
							], // children
						]), // Dropbox
						new Button([
							'id' => 'fullscreen',
							'type' => 'link',
							'icon' => new Icon('fullscreen'),
						]), // Button
					], // children
				]), // Row
				new Container([
					'class' => '-device-middle',
					'children' => [
						new Container([
							'class' => '-left'
						]), // Container
						new Container([
							'class' => '-device-content',
							'children' => [
								new Form([
									'class' => '-address-bar',
									'method' => 'get',
									'action' => url('system/mobile'),
									'children' => [
										'domain' => [
											'type' => 'text',
											'class' => '-fill',
											'onEnter' => 'submit',
											'value' => $this->domain,
										],
										'device' => [
											'type' => 'hidden',
											'value' => $this->device,
										],
									], // children
								]), // Form
								'<iframe id="mainframe" src="' . $this->domain . '"></iframe>',
							], // children
						]), // Container
						new Container([
							'class' => '-right'
						]), // Container
					], // children
				]), // Container
				new Container([
					'class' => '-footer',
				]), // Container
				$this->script(),
			], // children
		]);
	}

	private function style() {
		head('
			<style>
				* {box-sizing: border-box;}
				:root {
					--device-radius: 32px;
					--border-width: 8px;
					--device-width: 443px;
					--device-height: 100vh;
					--header-height: 38px;
					--header-padding: 0 20px;
				}
				.-iphone {
					--device-radius: 32px;
					--border-width: 8px;
					--device-width: 375px;
					--device-height: 812px;
					--header-height: 64px;
					--header-padding: 0 16px;
				}
				.-iphone16 {
					--device-radius: 48px;
					--device-width: 440px;
					--device-height: 956px;
					--header-height: 48px;
					--header-padding: 0 32px;
				}
				.-moto-g21 {
					--device-radius: 32px;
					--device-width: 432px;
					--device-height: 960px;
					--header-height: 42px;
					--header-padding: 0 16px;
				}
				.-samsung-s23 {
					--device-radius: 32px;
					--device-width: 360px;
					--device-height: 780px;
					--header-height: 42px;
					--header-padding: 0 16px;
				}

				body, .page.-page, .page.-content, .page.-primary, .page.-main {
					width: 100%;
					height: 100%;
					margin: 0;
					padding: 0;
					overflow: hidden;
					background: #fff;
					max-width: 100vw;
				}
				.page.-header, .page.-footer {display: none;}
				.mobile {
					width: calc(var(--device-width) + 2 * var(--border-width));
					height: calc(var(--device-height) - 32px);
					margin: 16px auto;
					display: flex;
					flex-wrap: wrap;
					flex-direction: column;
					background-color: #000;
					box-shadow: 0 0 24px rgba(0, 0, 0, 0.8);
					border-radius: var(--device-radius);
					overflow: hidden;
					.-header {
						height: var(--header-height);
						padding: var(--header-padding);
						color: #fff;
						display: flex;
						align-items: center;
						font-size: 1.2rem;
					}
					.-device-middle {
						display: flex;
						flex-direction: row;
						flex-wrap: nowrap;
						flex: 1;
						overflow: hidden;
						.-left {
							width: var(--border-width);
						}
						.-right {
							width: var(--border-width);
						}
						.-device-content {
							flex: 1;
							display: flex;
							flex-direction: column;
						}
						.-address-bar {
							width: 100%;
							.form-text {width: 100%; border-radius: 24px; background-color: #a2a2a2; box-shadow: none; padding: 8px 16px;}
						}
						iframe {
							width: var(--device-width);
							height: 100%;
							border: none;
							margin: 0 auto;
							display: block;
							overflow: hidden;
							flex: 1;
							border-radius: 0 0 calc(var(--device-radius) - var(--border-width)) calc(var(--device-radius) - var(--border-width));
						}
					}
					.-footer {
						height: var(--border-width);
					}
					.widget-dropbox {
						.sg-dropbox--content {
							background-color: #000;
							border: none;
							box-shadow: none;
						}
						.sg-dropbox--arrow {display: none;}
						.select-device {
							input {display: none;}
							.option {white-space: nowrap;}
							abbr:hover {
								background-color: #333;
								border-radius: 4px;
							}
						}
					}
				}
			</style>'
		);
	}

	private function script() {
		return '
		<script type="module">
			document.getElementById("fullscreen").addEventListener("click", (event) => {
				event.preventDefault();
				if (!document.fullscreenElement) {
					enterFullscreen();
				} else {
					exitFullscreen();
				}
			});

			trackIframeNavigation({
				"iframe": document.getElementById("mainframe"),
				"onStart": url => {},
				"onFinish": url => {
					document.getElementById("edit-domain").value = url;
				}
			});

			// Enter fullscreen mode for a body
			function enterFullscreen() {
				const element = document.querySelector("body");
				if (element.requestFullscreen) {
					element.requestFullscreen();
				} else if (element.webkitRequestFullscreen) { // Safari
					element.webkitRequestFullscreen();
				} else if (element.msRequestFullscreen) { // IE11
					element.msRequestFullscreen();
				}
			}

			// Exit fullscreen mode
			function exitFullscreen() {
				if (document.exitFullscreen) {
					document.exitFullscreen();
				} else if (document.webkitExitFullscreen) { // Safari
					document.webkitExitFullscreen();
				} else if (document.msExitFullscreen) { // IE11
					document.msExitFullscreen();
				}
			}		

			function trackIframeNavigation(options) {
				options = Object.assign(
					{
						iframe: null,
						// callbacks
						onStart: null,
						onFinish: null
					},
					options
				);

				if (!options.iframe) return false;

				let lastHref = null;

				function fixLinks(doc) {
					try {
						const links = doc.querySelectorAll("a[target=_blank]");
						links.forEach(link => link.setAttribute("target", "_self"));
					} catch (e) {
						// Ignore cross-origin iframes
					}
				}

				function observeLinks(doc) {
					try {
						// Observe DOM changes to fix links added by AJAX
						const observer = new doc.defaultView.MutationObserver(() => fixLinks(doc));
						observer.observe(doc.body, { childList: true, subtree: true });
					} catch (e) {
						// Ignore cross-origin iframes
					}
				}

				function onLoad() {
					const win = options.iframe.contentWindow;
					const doc = options.iframe.contentDocument;
					if (!doc) return;

					// Fix links immediately
					fixLinks(doc);
					// Observe for AJAX changes
					observeLinks(doc);

					const href = win.location.href;
					if (href !== lastHref) {
						if (options.onFinish) options.onFinish(href);
						lastHref = href;
					}

					// Attach beforeunload for next navigation
					win.addEventListener("beforeunload", onBeforeUnload);
				}

				function onBeforeUnload() {
					if (options.onStart) options.onStart(options.iframe.contentWindow.location.href);
				}

				options.iframe.addEventListener("load", onLoad);

				// Initial attach if already loaded
				if (options.iframe.contentWindow) {
					options.iframe.contentWindow.addEventListener("beforeunload", onBeforeUnload);
				}
			}
		</script>';
	}
}
?>