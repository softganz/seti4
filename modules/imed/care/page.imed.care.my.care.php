<?php
/**
* iMed :: My Patient Care
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/care/my/care
*/

$debug = true;

class ImedCareMyCare extends Page {
	function build() {
		if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมาชิกในความดูแล',
			]), // AppBar
			'child' => new Container([
				'tagName' => 'section',
				'children' => [
					R::View('imed.my.patient', ['ref' => 'care']),
					'<header class="header"><h3>MY HOME VISIT</h3></header>',
					'<div id="imed-my-note" class="sg-load" data-url="'.url('imed/visits',['u' => i()->uid, 'ref' => 'care']).'" data-replace="true">'._NL
					. '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 48px auto; display: block;"></div>'
					. '</div><!-- imed-my-note -->'
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>