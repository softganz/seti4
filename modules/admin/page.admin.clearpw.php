<?php
function admin_clearpw($self) {
	$show=post('show');
	$update=post('update');

	if ($show) $ret.='<h3>Show password</h3>';
	if ($update) $ret.='<h3>Update password</h3>';

	$ret.='<p>Clear password from id 6-654</p>';

	$tables = new Table();
	$tables->thead=array('ชื่อกองทุน','อำเภอ','จังหวัด','Username','Password');

	/*
	$pwd = bin2hex(openssl_random_pseudo_bytes(4));
	$tables->rows[]=array('Password',$pwd);
	$pwd=sg_generatePassword(8);
	$tables->rows[]=array('Password',$pwd);
	*/

	$stmt='SELECT
					`uid`,`username`,`password`,`name`, f.`namechangwat`, f.`nameampur`, f.`fundname`
				FROM %users% u
					LEFT JOIN %project_fund% f ON u.`username`=f.`fundid`
				WHERE `uid` BETWEEN 6 AND 654
				ORDER BY `uid` ASC; -- {key:"uid"}';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');
	foreach ($dbs->items as $rs) {
		$pwd=_admin_clearpw_genpassword();
		$pwdDecript=sg_decrypt($rs->password,cfg('encrypt_key'));
		$tables->rows[]=array(
											$rs->fundname,
											$rs->nameampur,
											$rs->namechangwat,
											$rs->username,
											$show?$pwdDecript:$pwd,
											);

		if ($update) {
			$pwdEncript=sg_encrypt($pwd,cfg('encrypt_key'));
			$stmt='UPDATE %users% SET
							  `password`=:pwd
							, `last_login`=NULL
							, `last_login_ip`=NULL
							, `login_time`=NULL
							, `login_ip`=NULL
							WHERE `uid`=:uid LIMIT 1';
			mydb::query($stmt,':uid',$rs->uid, ':pwd',$pwdEncript);
		}
		//$tables->rows[]=array('<td colspan="5">'.mydb()->_query.'</td>');
	}


	$ret.=$tables->build();
	return $ret;
}

function _admin_clearpw_genpassword() {
	$pwd=str_shuffle(
					rtrim(
						base64_encode(bin2hex(openssl_random_pseudo_bytes(1))),
						'='
					).
					substr('#@%$*',rand(0,4),1).
					strtoupper(bin2hex(openssl_random_pseudo_bytes(1))).
					bin2hex(openssl_random_pseudo_bytes(1))
					);
	return $pwd;
}
?>