<?php
/**
* iMed :: App Need
* Created 2021-06-01
* Modify  2021-06-01
*
* @return Widget
*
* @usage imed/app/need
*/

$debug = true;

class ImedAppNeed extends Page {
	function __construct() {}

	function build() {
		if ($_SESSION['imedapp'] === 'psyc') {
			return R::Page('imed.psyc.need');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ความต้องการ',
				'navigator' => [
					new Ui([
						'class' => 'ui-nav -main',
						'children' => [
							'<a class="sg-action" href="'.url('imed/needs', ['ref' => 'app']).'" data-rel="#main" data-done="moveto:0,0" data-options=\'{"silent": true}\'><i class="icon -material">account_balance</i></a>',
							i()->ok ? '<a class="sg-action" href="'.url('imed/needs', ['show'=>'my', 'ref' => 'app']).'" data-rel="#main" data-done="moveto:0,0" data-options=\'{"silent": true}\'><i class="icon -material">person</i></a>' : NULL,
							'<a class="sg-action" href="'.url('imed/app/need/summary').'"data-rel="#main" data-done="moveto:0,0" data-options=\'{"silent": true}\'><i class="icon -material">pie_chart</i></a>',
						],
					]), // Ui
				],
			]), // AppBar
			'children' => [
				'<div id="imed-need" class="sg-load" data-url="'.url('imed/needs', ['ref' => 'app']).'" data-replace="true">'._NL
				. '<div class="loader -rotate" style="width: 48px; height: 48px; margin: 48px auto; display: block;"></div>'
				. '</div><!-- imed-my-note -->',
			],
		]);
	}
}
?>