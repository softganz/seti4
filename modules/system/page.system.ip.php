<?php
/**
 * System  :: Show IP Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-03-04
 * Modify  :: 2026-03-04
 * Version :: 1
 *
 * @return Widget
 *
 * @usage system/ip
 */

class SystemIp extends Page {
	#[\Override]
	function build() {
		// Check for shared internet/proxy headers first
		$ip = @$_SERVER['HTTP_CLIENT_IP'];
		if(filter_var($ip, FILTER_VALIDATE_IP)) {
			return $ip;
		}
		
		// Check for IP addresses from a chain of proxies
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		if(filter_var($forward, FILTER_VALIDATE_IP)) {
			return $forward;
		}
		
		// Fallback to the immediate remote address
		return $_SERVER['REMOTE_ADDR'];
		
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'IP',
			]), // AppBar
			'body' => 'Your IP is '.get_re
		]);
	}
}
?>