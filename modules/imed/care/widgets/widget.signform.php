<?php
/**
* iMed :: Care home page
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/care
*/

$debug = true;

class SignForm {
	function build() {
		return new Container([
			'children' => [
				R::View('signform', '{time:-1, showTime: false, showRegist: false}'),
				'<style type="text/css">
				.toolbar.-main.-imed h2 {text-align: center;}
				.form.signform .form-item {margin-bottom: 16px; position: relative;}
				.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
				.form.signform .form-text, .form.signform .form-password {padding-top: 16px;}
				.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
				.login.-normal h3 {display: none;}
				</style>',
			], // children
		]);
	}
}
?>