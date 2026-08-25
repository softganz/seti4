<?php
/**
 * SignForm :: View User Sign In Form
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2019-05-06
 * Modified :: 2026-08-25
 * Version  :: 4
 *
 * @param Object $options
 * @return String
 */

// @Deprecated

function view_signform($options = '{}') {
	$defaults = '{debug: false, id: "signin-'.uniqid().'", class: "signform", time: 10080, showTime: true, showInfo: true, showGuide: true, showRegist: true, rel: null, signret: null, done: "reload", regRel: "#main"}';
	$options = \SG\json_decode($options, $defaults);
	$debug = $options->debug;

	return new SignForm((Array) $options);
}
?>