<?php
/**
 * Admin    :: Delete User Information
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-11-15
 * Version  :: 4
 */

function admin_user_delete($self, $uid) {
	$ret = '';
	$error = [];

	if (\SG\confirm()) {
		$rs = \UserModel::get($uid);

		$uid = $rs->uid;

		if (!$uid) {
			return message('error', 'User <em>' . $uid . '</em> not exists.');
		}

		if ($uid == 1) {
			$error[] = 'User was lock';
		}
		if ($rs->status == 'enable') {
			$error[] = 'User was active';
		}
		if (count($rs->roles) > 1) {
			$error[] = 'User was in group';
		}

		if ($error) {
			$ret .= message(
				'error',
				'This user was not delete by reason : <ul><li>'
					. implode('</li><li>', $error)
					. '</li></ul>'
			);
		} else {
			\DB::query(
				'DELETE FROM %users% WHERE `uid` = :uid LIMIT 1',
				var: [
					':uid' => $uid
				]
			);

			\DB::query(
				'DELETE FROM %topic_user% WHERE `uid` = :uid',
				var: [
					':uid' => $uid
				]
			);

			if (\DB::tableExists('%org_officer%')) {
				\DB::query(
					'DELETE FROM %org_officer% WHERE `uid` = :uid',
					var: [
						':uid' => $uid
					]
				);
			}

			location('admin/user/list');
		}
	}
	return $ret;
}
?>
