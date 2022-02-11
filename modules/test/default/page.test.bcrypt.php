<?php
function test_bcrypt($self) {
	$password = 'THIS is A Password';
	$ret = 'Crypt no salt = '.crypt($password).'<br />';
	$ret .= 'Crypt with CRYPT_BLOWFISH = '.crypt($password, CRYPT_BLOWFISH).'<br />';
	$ret .= 'Crypt with CRYPT_STD_DES = '.crypt($password, CRYPT_STD_DES).'<br />';
	$ret .= 'Crypt with CRYPT_EXT_DES = '.crypt($password, CRYPT_EXT_DES).'<br />';
	$ret .= 'Crypt with CRYPT_MD5 = '.crypt($password, CRYPT_MD5).'<br />';
	$ret .= 'Crypt with CRYPT_SHA256 = '.crypt($password, CRYPT_SHA256).'<br />';
	$ret .= 'Crypt with CRYPT_SHA512 = '.crypt($password, CRYPT_SHA512).'<br />';

	if (function_exists('password_hash')) $ret .= 'Password hash with PASSWORD_BCRYPT= '.password_hash($password, PASSWORD_BCRYPT).'<br />';
	else $ret .= 'NO function password_hash()<br />';
	if (!function_exists('hash_equals')) $ret .= 'NO function hash_equals()<br />';
	return $ret;
}
?>