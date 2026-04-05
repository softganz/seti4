<?php
/**
 * API     :: User Sign Out
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2022-07-11
 * Modify  :: 2026-04-05
 * Version :: 3
 *
 * @return JSON
 *
 * @usage api/signout
 */

class SignoutApi extends PageApi {
	function build() {
		$result = UserModel::signOutProcess();

		return $result;
	}
}
?>