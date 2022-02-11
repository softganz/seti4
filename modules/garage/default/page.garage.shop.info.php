<?php
/**
* Garage Shop Information Model
* Created 2019-12-02
* Modify  2019-12-02
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

import('model:user.php');

function garage_shop_info($self, $shopInfo = NULL, $action = NULL, $tranId = NULL) {
	if (!($shopId = $shopInfo->shopid)) return message('error', 'PROCESS ERROR');


	$isAdmin = is_admin('garage') || in_array($shopInfo->iam, array('ADMIN', 'MANAGER'));

	$ret = '';

	switch ($action) {

		case 'user.save':
			if ($isAdmin) {
				$data = (Object) post('user');
				$data->uid = SG\getFirst($data->uid, $tranId);
				$data->shopid = $data->shopid;
				list($data->membership, $data->position) = explode(':', $data->membership);
				if ($data->uid) {
					$stmt = 'UPDATE %garage_user% SET `shopid` = :shopid, `membership` = :membership, `position` = :position WHERE `shopid` IN (:branch) AND `uid` = :uid LIMIT 1';
					mydb::query($stmt, $data, ':branch', 'SET:'.implode(',',$shopInfo->branch));
					//$ret .= mydb()->_query.'<br />';

					$stmt = 'UPDATE %users% SET `name` = :name, `email` = :email, `phone` = :phone WHERE `uid` = :uid LIMIT 1';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query.'<br />';

					if ($data->password) {
						$data->newpassword = sg_encrypt($data->password,cfg('encrypt_key'));
						$stmt = 'UPDATE %users% SET `password` = :newpassword WHERE `uid` = :uid LIMIT 1';
						mydb::query($stmt, $data);
						//$ret .= mydb()->_query.'<br />';
					}

				} else if ($isAdmin && $data->username && $data->password && $data->name) {
					$ret .= 'CREATE USER';
					$result = UserModel::create($data, '{debug: false}');

					if ($result->complete) {
						$data->uid = $result->uid;

						$stmt = 'INSERT INTO %garage_user%
							(`shopid`, `uid`, `membership`, `position`)
							VALUES
							(:shopid, :uid, :membership, :position)';

						mydb::query($stmt, $data);
						//$ret .= mydb()->_query;

						$ret .= ' COMPLATED';
					} else {
						$ret .= ' ERROR : DUPLICATE USERNAME!!!!!';
						http_response_code(404);
					}
					//$ret .= print_o($result, '$result');
				}
				//$ret .= print_o(post(),'post()');
			}
			break;

		case 'member.remove':
			if ($isAdmin && $tranId) {
				$stmt = 'UPDATE %garage_user% SET `membership` = "DELETED" WHERE `shopid` = :shopid AND `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':shopid', $shopId, ':uid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'member.recall':
			if ($isAdmin && $tranId) {
				$stmt = 'UPDATE %garage_user% SET `membership` = "MEMBER" WHERE `shopid` = :shopid AND `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':shopid', $shopId, ':uid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		default:
			$ret .= 'ERROR!!! No Action';
			break;
	}

	//$ret .= print_o($shopInfo, '$shopInfo');
	return $ret;
}
?>