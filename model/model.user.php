<?php
/**
 * Model    :: User Information
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-07-22
 * Modified :: 2026-09-26
 * Version  :: 30
 *
 * @param Int $userId
 * @return object
 *
 * @uses new UserModel($userId)
 * @uses UserModel::function($conditions, $options)
 */

use Softganz\DB;
use Softganz\DbException;

class UserModel {
	var $userId;

	function __construct($userId = null) {
		$this->userId = empty($userId) ? i()->uid : $userId;
		if ($this->userId) $this->getUserInfo();
	}

	public static function get($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = \SG\json_decode($conditions);
		} else if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object) $conditions;
		else {
			$id = $conditions;
			$conditions = (object) ['id' => $id];
		}

		$conditions = (object) array_replace(
			[
				'id' => null,
				'username' => null,
				'email' => null,
			],
			(array) $conditions
		);

		if (!$id && $conditions->username && $conditions->email) {
			return null;
		}

		$result = DB::select([
			'SELECT u.`uid` `userId`, u.* FROM %users% u %WHERE% LIMIT 1',
			'%WHERE%' => [
				$id ? ['`uid` = :userId', ':userId' => $id] : null,
				$conditions->username ? ['`username` = :username', ':username' => $conditions->username] : null,
				$conditions->email ? ['`email` = :email', ':email' => $conditions->email] : null,
			]
		]);

		if ($debug) debugMsg(R('query'));

		if (!$result->userId) return null;

		$result->roles = empty($result->roles) ? array('member') : explode(',','member,'.$result->roles);

		return $result;
	}

	public static function create($user, $options = '{}') {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_object($user)) ; // Do nothing
		else if (is_string($user) && preg_match('/^{/',$user)) $user = \SG\json_decode($user);
		else if (is_array($user)) $user = (object) $user;
		else $user = (object) [];

		if (empty($user->username)) {
			return (object) [
				'uid' => null,
				'complete' => false,
				'error' => true,
				'text' => 'Username not specify'
			];
		} else if (UserModel::get(['username' => $user->username])) {
			return (object) [
				'uid' => null,
				'complete' => false,
				'error' => true,
				'text' => 'Username was duplicate'
			];
		}

		$result = (object) [
			'userId' => null,
			'uid' => null,
			'complete' => false,
			'error' => false,
			'password' => null,
			'username' => $user->username,
			'name' => $user->name,
			'email' => $user->email,
			'auth' => 'user',
			'process' => ['UserModel::create() => request']
		];


		// debugMsg($user,'$user');

		if (isset($user->status)) {
			; // Set status from caller
		} else if (in_array(cfg('member.registration.method'), ['email'])) {
			$user->status = 'waiting';
			$user->code = md5(uniqid(rand())); // better, difficult to guess
			$result->process[] = 'create user with email registration method';
		} else if (in_array(cfg('member.registration.method'), ['waiting','waiting,email'])) {
			$user->status = 'waiting';
			$result->process[] = 'create user with admin check registration method';
		} else {
			$user->status = 'enable';
			$result->process[] = 'create user and ready to used';
		}

		$user->encryptPassword = $user->password ? sg_encrypt($user->password,cfg('encrypt_key')) : null;
		$user->datein = 'func.NOW()';
		if (empty($user->about)) $user->about = '';
		if (empty($user->phone)) $user->phone = '';
		if (empty($user->email)) $user->email = '';
		if (empty($user->organization)) $user->organization = '';
		$user->realName = \SG\getFirst($user->name, $user->realName);
		$user->lastName = \SG\getFirst($user->lastName);
		$user->admin_remark = \SG\getFirst($user->admin_remark);

		$user->userRoles = '';
		if ($user->roles && is_string($user->roles)) {
			$user->userRoles = $user->roles;
		} else if (is_object($user->roles)) {
			$user->userRoles = $user->roles->role;
		}

		try {
			$userId = DB::query([
				'INSERT INTO %users%
				( `username` , `password` , `name` , `roles`, `phone` , `email` , `real_name`, `last_name`, `status` , `datein` , `about`, `organization`, `admin_remark` )
				VALUES
				( :username , :encryptPassword , :name , :userRoles, :phone , :email , :realName, :lastName, :status , :datein , "", :organization, :admin_remark )',
				'var' => $user
			])->insertId();
			if ($debug) $result->process[] = R('query');
		} catch (Exception $exception) {
			$result->complete = false;
			$result->process[] = 'UserModel::create() => create error';
			return $result;
		}

		$result->complete = true;
		$result->userId = $result->uid = $user->uid = $userId;
		$result->password = $user->encryptPassword;
		$result->status = $user->status;


		sgSendLog([
			'file' => __FILE__,
			'line' => __LINE__,
			'type' => 'Create user' . ($result->status === 'enable' ? '' : ' - ' . $result->status),
			'user' => SG\getFirst(i()->uid, $result->userId),
			'name' => SG\getFirst(i()->name, $user->name),
			'description' => (object) [
				'username' => $user->username,
				'name' => $user->name,
				'id' => $result->userId,
				'email' => $user->email,
				'status' => $result->status,
			],
		]);

		if ($user->roles) {
			DB::query([
				'INSERT INTO %users_role%
				(`uid`, `role`, `status`, `approved`, `created`)
				VALUES
				(:userId, :role, :status, :approved, :created)
				ON DUPLICATE KEY UPDATE
				`uid` = :userId',
				'var' => [
					':userId' => $result->uid,
					':role' => $user->roles->role,
					':status' => $user->roles->status,
					':approved' => $user->roles->approved,
					':created' => date('U'),
				]
			]);
			if ($debug) $result->process[] = R('query');
		}

		event_tricker('user.create_user',$self,$user,$form,$result);

		$result->process[] = 'UserModel::create() => create complete';

		return $result;
	}

	public static function getUsers($condition = []) {
		$condition = (object) array_replace(
			[
				'query' => null,
				'username' => null,
				'email' => null,
				'status' => 'all', // all,enable,disable,block,waiting,locked
				'option' => [
					'item' => null,
				],
			],
			(array) $condition
		);

		$condition->option = (object) $condition->option;

		return DB::select([
			'SELECT `user`.`uid` AS `userId`, `user`.`username`, `user`.`name`, `user`.`email`, `user`.`status`
			FROM %users% `user`
			%WHERE%
			ORDER BY CONVERT(`user`.`name` USING tis620) ASC
			$LIMIT$',
			'%WHERE%' => [
				$condition->status && $condition->status != 'all' ? ['`user`.`status` = :status', ':status' => $condition->status] : null,
				$condition->queryText ? ['(`user`.`username` LIKE :queryText OR `user`.`name` LIKE :queryText  OR `user`.`email` LIKE :queryText)', ':queryText' => '%'.$condition->queryText.'%'] : null,
				$condition->username ? ['`user`.`username` LIKE :username', ':username' => $condition->username.'%'] : null,
				$condition->email ? ['`user`.`email` LIKE :email', ':email' => $condition->email.'%'] : null,
			],
			'var' => [
				'$LIMIT$' => $condition->option->item ? 'LIMIT '.$condition->option->item : '',
			],
		]);
	}

	//TODO: Change value in table cache/session
	public static function changeUserName($oldUsername, $newUsername) {
		if (empty($oldUsername) || empty($newUsername)) return false;
		if ($oldUsername === $newUsername) return false;
		if (file_exists('file/'.$newUsername)) return false;

		$newUserInfo = UserModel::get(['username' => $newUsername]);
		if ($newUserInfo->userId) return false;

		try {
			DB::query([
				'UPDATE %users% SET `username` = :newUsername WHERE `username` = :oldUsername',
				'var' => [
					':oldUsername' => $oldUsername,
					':newUsername' => $newUsername,
				]
			]);
		} catch (Exception $exception) {
			throw new Exception('เปลี่ยนชื่อไม่สำเร็จ', _HTTP_ERROR_NOT_ACCEPTABLE);
		}

		$oldFolder = 'file/'.$oldUsername;
		$newFolder = 'file/'.$newUsername;

		if (file_exists('file/'.$oldUsername)) rename($oldFolder, $newFolder);

		// Rename cache
		DB::query([
			'UPDATE %cache% SET `headers` = :newUsername WHERE `headers` = :oldUsername',
			'var' => [
				':oldUsername' => $oldUsername,
				':newUsername' => $newUsername,
			]
		]);
	}

	public static function updatePassword($userId, $password) {
		$newPassword = sg_encrypt($password,cfg('encrypt_key'));
		DB::query([
			'UPDATE %users%
			SET `password` = :newPassword
			WHERE `uid` = :userId
			LIMIT 1',
			'var' => [
				':userId' => $userId,
				':newPassword' => $newPassword
			]
		]);

		$userName = DB::select([
			'SELECT `username` FROM %users% WHERE `uid` = :userId LIMIT 1',
			'var' => [':userId' => $userId]
		])->username;

		LogModel::save([
			'module' => 'user',
			'keyword' => 'Password change',
			'message' => 'User '.$userName.' ('.$userId.') was change password'
		]);
	}

	// delete user information
	public static function delete($uid) {
		$rs = UserModel::get($uid);
		$result = (object) [
			'code' => null,
			'message' => null,
		];

		$uid = $rs->uid;

		if (empty($uid)) {
			$result->code = _HTTP_ERROR_NOT_ALLOWED;
			$result->message = 'User <em>'.$uid.'</em> not exists.';
		} else if ($uid == 1) {
			$result->code = _HTTP_ERROR_NOT_ALLOWED;
			$result->message = 'User was lock.';
		} else if ($rs->status == 'enable') {
			$result->code = _HTTP_ERROR_NOT_ALLOWED;
			$result->message = 'User was active.';
		} else {
			unset($result->code);
			$result->message = 'User deleted.';
			DB::query([
				'DELETE FROM %users% WHERE `uid` = :uid LIMIT 1',
				'var' => [':uid' => $uid]
			]);

			DB::query([
				'DELETE FROM %topic_user% WHERE `uid` = :uid',
				'var' => [':uid' => $uid]
			]);

			if (DB::tableExists('%org_officer%')) {
				DB::query([
					'DELETE FROM %org_officer% WHERE `uid` = :uid',
					'var' => [':uid' => $uid]
				]);
			}
		}
		return $result;
	}

	public static function clearLogin() {
		$user = (object) [
			'ok' => false,
			'group' => [],
		];
		self::clearUserCookie();
		$_SESSION['user'] = null;
	}

	/**
	 * Sign out process
	 *
	 * @return object
	 */
	public static function signOutProcess(): object {
		$result = (object) [
			'signed' => false,
		];

		if (!i()->ok) return $result;

		self::clearUserCookie();

		$_SESSION['user'] = null;
		$_SESSION['logas'] = null;

		$cacheId = 'user:' . i()->token;
		Cache::Clear($cacheId);

		// session_unset();
		// session_destroy();

	return $result;
	}

	/**
	 * Sign in process
	 *
	 * @param string $username
	 * @param string $password
	 * @param int $cookielength
	 * @return object|boolean
	 */
	public static function signInProcess($username = null, $password = null, $cookielength = null): object|bool {
		$debug = false; //$username=='softganz';

		if (empty($username) || empty($password)) return false;

		// Check username is email
		if (preg_match('/\@/', $username)) {
			$username = DB::select([
				'SELECT `username` FROM %users% WHERE `email` = :email LIMIT 1',
				'var' => [':email' => $username]
			])->username;
		}

		$rs = DB::select([
			'SELECT * FROM %users% u WHERE `username` = :username LIMIT 1',
			'var' => [':username' => $username]
		]);

		//TODO: Bug ตอนลงทะเบียนสมาชิกใหม่ จะไม่สามารถดึงค่าจากฐานข้อมูลผ่าน function ได้

		if (!in_array($rs->status, ['enable',1])) {
			LogModel::save([
				'module' => 'user',
				'keyword' => 'Invalid signin',
				'message' => 'user '.$username.' not exists or disabled'
			]);
			return apiError(_HTTP_ERROR_FORBIDDEN, 'User not exists or disabled.');
		}

		$de_password = sg_decrypt($rs->password,cfg('encrypt_key'));

		// sign in password error -> log
		if ( $password != $de_password ) {
			$ip = GetEnv('REMOTE_ADDR');
			DB::query([
				'UPDATE %users% SET tries = tries+1, remote_ip = :ip , date_tries = NOW() WHERE username = :username LIMIT 1',
				'var' => [
					':ip' => $ip,
					':username' => $username
				]
			]);
			LogModel::save([
				'module' => 'user',
				'keyword' => 'Invalid signin',
				'message' => $username.' incorrect password'
			]);
			return false;
		}

		// Sign in ok :: Set session id to cookie
		if ($cookielength == -1) $cookielength = 10*365*24*60;
		if (empty($cookielength)) $cookielength = cfg('member.signin.remembertime');
		$rememberTime = time()+$cookielength*60;

		// Create JWT token
		$sessionId = Jwt::generate(
			[
				"type" => "JWT",
				"alg" => "HS256"
			],
			['id' => intval($rs->uid), 'username' => $rs->username, 'name' => $rs->name, 'roles' => $rs->roles ? explode(',',$rs->roles) : [], 'exp' => $rememberTime ],
			cfg('system')->loginToken->jwtSecret
		);

		if (strlen($sessionId) > 1000) $sessionId = md5(uniqid(rand(), true));

		self::setUserCookie($sessionId, $rs->username, $rememberTime);

		$debug_str .= '<p>cookie.id : '.cfg('cookie.id').'</p>';
		$debug_str .= '<p>cookie.u : '.cfg('cookie.u').'</p>';
		$debug_str .= '<p>cookie.path : '.cfg('cookie.path').'</p>';
		$debug_str .= '<p>cookie.domain : '.cfg('cookie.domain').'</p>';
		$debug_str .= '<p>remember time : '.$rememberTime.' second.</p>';

		// add session into cache
		$user = (object) [
			'ok' => true,
			'uid' => intval($rs->uid),
			'username' => $rs->username,
			'name' => $rs->name,
			'email' => $rs->email,
			'remember' => $cookielength*60,
			'ip' => GetEnv('REMOTE_ADDR'),
			'admin' => false,
			'session' => $sessionId,
			'token' => $sessionId,
			'roles' => $rs->roles ? explode(',',$rs->roles) : [],
		];

		$_SESSION['user'] = $user;

		cache::add('user:'.$sessionId, $user, $rememberTime, $username);

		DB::query([
			'UPDATE %users% SET
			`last_login` = `login_time` ,
			`last_login_ip` = `login_ip` ,
			`login_time` = :login_time,
			`login_ip` = :ip
			WHERE uid = :uid LIMIT 1',
			'var' => [
				':login_time' => date('Y-m-d H:i:s'),
				':ip' => ip2long($user->ip),
				':uid' => $user->uid,
			]
		]);

		$debug_str .= '<p>query=' . R('query') . '</p>';

		LogModel::save([
			'module' => 'user',
			'keyword' => 'Signin',
			'message' => 'user '.$username.' was signin',
			'userId' => $user->uid
		]);

		$debug_str .= print_o($_COOKIE,'$COOKIE');
		$debug_str .= print_o($user,'$user');
		if ($debug) echo $debug_str;

		return $user;
	}

	/**
	 * {name: "Real name", email: "E-mail", }
	 */
	public static function externalUserCreate(
		$user = [
			'email' => null,
			'name' => null,
			'prefix' => null,
			'signin' => true,
			'token' => null,
		]
	) {
		$user = (object) $user;
		if (empty($user->email) || empty($user->name)) return false;

		do {
			$username = $user->prefix.\SG\uniqid(20);
		} while (UserModel::get(['username' => $username]));

		$createUserResult = UserModel::create([
			'username' => $username,
			'password' => null,
			'name' => $user->name,
			'email' => $user->email,
		]);

		if (!$createUserResult->uid) {
			return (object) [
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ไม่สามารถสร้างสมาชิกตามข้อมูลที่ระบุได้',
			];
		}

		$result = (object) [
			'userId' => $createUserResult->uid,
			'username' => $createUserResult->username,
			'name' => $createUserResult->name,
			'email' => $createUserResult->email,
		];

		// print_o($user, '$user', 1);
		// Process User Sign In
		if ($result->userId && $user->signin) {
			// echo '<br /><br /><br /><br /><br />SIGN IN';
			$result->signin = UserModel::externalSignIn([
				'email' => $user->email,
				'token' => $user->token,
			]);
		}
		return $result;
	}

	public static function externalSignIn($args) {
		$result = (object) [];
		$sessionId = $args['token'];
		$cookielength = 10*365*24*60;
		$rememberTime = time()+$cookielength*60;
		// if ($cookielength == -1) $cookielength = 10*365*24*60;

		if ($args['email'] && $sessionId) {
			$user = DB::select([
				'SELECT * FROM %users% WHERE `email` = :email LIMIT 1',
				'var' => [':email' => $args['email']]
			]);
			$result->query = R('query');
			// debugMsg($user, '$user');
			if (!$user->uid) return (object) ['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'Invalid email'];
			$result = (object) [
				'ok' => true,
				'uid' => intval($user->uid),
				'username' => $user->username,
				'name' => $user->name,
				'email' => $user->email,
				'session' => $sessionId,
				'token' => $sessionId,
				'remember' => $cookielength*60,
				'ip' => GetEnv('REMOTE_ADDR'),
				'admin' => false,
				'roles' => $user->roles ? explode(',',$user->roles) : [],
			];

			$_SESSION['user'] = $result;
			cache::add('user:'.$sessionId, $result, $rememberTime, $result->username);

			if ($cookielength == -1) $cookielength = 10*365*24*60;
			if (empty($cookielength)) $cookielength = cfg('member.signin.remembertime');
			$rememberTime = time()+$cookielength*60;

			self::setUserCookie($sessionId, $result->username, $rememberTime);

			DB::query([
				'UPDATE %users% SET
				`last_login` = `login_time` ,
				`last_login_ip` = `login_ip` ,
				`login_time` = :login_time,
				`login_ip` = :ip
				WHERE uid = :uid LIMIT 1',
				'var' => [
					':login_time' => date('Y-m-d H:i:s'),
					':ip' => ip2long($result->ip),
					':uid' => $result->uid,
				]
			]);
			LogModel::save([
				'module' => 'user',
				'keyword' => 'Signin by Google',
				'message' => 'Email '.$result->email.' ('.$result->username.') was signin with Google',
				'userId' => $result->uid
			]);
		}
		return $result;
	}

	public static function checkLogin() {
		$user = (object) [
			'ok' => false,
			'signInResult' => null,
			'roles' => [],
		];

		// TODO: PHP Authen
		// "PHP_AUTH_USER": "aaaa",
		// "PHP_AUTH_PW": "sssss",

		// Check Bearer Token
		if (isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
		} else if (function_exists("apache_request_headers")) {
			$headers = apache_request_headers();
			$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
		}

		$username = Request::post('username');
		$password = Request::post('password');

		// User sign in from any page using POST method only
		if ($username && is_string($username) && $password) {
			cache::clear_expire();
			$remember = Request::post('remember');
			$cookielength = SG\getFirstInt(Request::post('cookielength'));
			// echo '$username = '.$username.' $password = '.$password;
			// print_r($_POST);
			// if ($username=='softganz') die($username);

			if (!is_numeric($cookielength)) return $user;


			// check for email signin
			if (preg_match('/@/', $username)) {
				$user_email = DB::select([
					'SELECT `username` FROM %users% WHERE `email` = :email',
					'var' => [':email' => $username]
				]);

				if ($user_email->count === 1) {
					$username = $user_email->items[0]->username;
				} else {
					$user->signInResult = tr('Invalid email or too many email');
					return $user;
				}
			}

			// No username match in database
			if (empty($username) || empty($password)) {
				$user->signInResult = tr('Invalid user signin');
				return $user;
			}

			$result = UserModel::signInProcess($username,$password,$cookielength);

			if ($result === false) {
				$user->signInResult = tr('Invalid user signin');
				return $user;
			}

			// Sign in complete
			$user = $result;
			$user->signInResult = tr('Sign in complete');

			return $user;
		} else if (($credential = post('credential')) && post('signMethod') === 'google') {
			// Google Sign In
			$jwt = Jwt::isValid($credential);

			// Check value different between email
			// $jwt->payload->nbf (running)
			// $jwt->payload->jti
			// $jwt->payload->sub (same on email, diff on other email)
			// $jwt->signature
			// $jwt->signatureProvided
			// print_o($jwt, '$jwt',1);
			// SignIn complete
			if ($jwt->payload->email) {
				$result = UserModel::externalSignIn([
					'email' => $jwt->payload->email,
					'token' => $jwt->payload->jti
				]);
				if (!$result->uid) {
					LogModel::save([
						'module' => 'user',
						'keyword' => 'Invalid signin',
						'message' => 'Email '.$result->email.' ('.$result->username.') signin with Google error',
						'userId' => $result->uid
					]);
				}

				return $result->uid ? $result : (object) ['signInErrorMessage' => 'Google account '.$jwt->payload->email.' is not recognized for Google Sign-In on this site. Please make sure you are using the same account that you have previously linked.'];
			} else {
				LogModel::save([
					'module' => 'user',
					'keyword' => 'Invalid signin',
					'message' => 'Invalid credential => '.$credential
				]);
				return (object) ['signInResult' => 'Invalid user signin'];
			}
		} else if (isset($authHeader) && $authHeader) {
			list($authType, $authToken) = explode(' ', $authHeader);
			$user = Cache::get('user:'.$authToken)->data;
			return $user;
		} else if ($token = post('token')) {
			$user = Cache::get('user:'.$token)->data;
			return $user;
		} else if (isset($_COOKIE[cfg('cookie.id')]) && isset($_COOKIE[cfg('cookie.u')])) {
			$cache = Cache::Get('user:'.$_COOKIE[cfg('cookie.id')]);
			//debugMsg('Cache='.print_o($cache,'$cache'));
			$data = $cache->data;
			//debugMsg($data,'$data',1);
			if (empty($data)) return $user;
			if ($data->username != $_COOKIE[cfg('cookie.u')]) return $user;
			if (cfg('member.signin.checkip') && $data->ip != $_SERVER['REMOTE_ADDR'] ) return $user;
			if ($cache->remain <= 0) {
				UserModel::clearLogin();
				cache::clear($cache->cid);
				return $user;
			} else {
				// set new expire time to current time + session time
				$rememberTime = time()+$data->remember;

				self::setUserCookie($data->session, $data->username, $rememberTime);

				DB::query([
					'UPDATE %cache% SET `expire` = :expire WHERE `cid` = :cid LIMIT 1',
					'var' => [
						':cid' => 'user:'.$data->session,
						':expire' => $rememberTime
					]
				]);
			}
			return $data;
		} else {
			return $user;
		}
	}

	public static function emailConfirm($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = null;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object) $conditions;
		else {
			$conditions = (object) ['code' => $conditions];
		}

		$code = $conditions->code;

		$result->complete=false;
		$result->error=false;
		$result->process[]='user.email.confirm request';

		if (empty($code)) $result->error[]='Empty registration code';

		if ($code) {
			$result->user = $user = DB::select([
				'SELECT * FROM %users% WHERE `status` IN ("","waiting") AND code = :code LIMIT 1',
				'var' => [':code' => $code]
			]);
			$result->query[] = R('query');
			if ($user->uid) {
				DB::query([
					'UPDATE %users% SET `status` = "enable", `code` = NULL WHERE `uid` = :userId LIMIT 1',
					'var' => [':userId' => $user->uid]
				]);
				$result->query[] = R('query');
				$result->process[]='Email registration confirm complete';
			} else {
				$result->error[]='No user for registration code';
			}
		}
		$result->complete=empty($result->error);
		$result->process[]='user.email.confirm request complete';

		return $result;
	}

	public static function getNextUsername($prefixUsername, $sep = '-', $length = 4) {
		if (empty($prefixUsername)) return null;

		$prefixUsername = strtolower($prefixUsername);
		$lastUsername = DB::select([
			'SELECT MAX(`username`) `username` FROM %users% WHERE `username` LIKE :prefixUsername LIMIT 1',
			'var' => [':prefixUsername' => $prefixUsername.$sep.'%']
		])->username;

		list($username,$lastid) = explode($sep,$lastUsername);
		if (empty($username)) $username = $prefixUsername;
		$nextUsername = $prefixUsername.$sep.sprintf('%0'.$length.'d', $lastid + 1);
		return $nextUsername;
	}

	public static function profilePhoto($username = null, $fullSize = true) {
		$filename = $fullSize ? 'profile.photo.jpg' : 'small.avatar.jpg';
		$photo_file = cfg('upload.folder').'/'.$username.'/'.$filename;
		$photo_url = cfg('upload.url').$username.'/'.$filename;
		if ($username && file_exists($photo_file)) {
			$time = filemtime($photo_file);
			return $photo_url.'?t='.$time;
		} else {
			return '/css/img/photography.png';
		}
		return $photo;
	}

	public static function deleteAccount($userId) {
		DB::query([
			'UPDATE %users% SET `status` = "disable", `admin_remark` = CONCAT(IFNULL(`admin_remark`, ""), "@'.date('Y-m-d H:i:s').' ลบบัญชีโดยเจ้าของ") WHERE `uid` = :userId LIMIT 1',
			'var' => [':userId' => $userId]
		]);
	}

	public static function getMemberOfGroup($userId) {
		try {
			return DB::select([
				'SELECT `officer`.`orgId`, `officer`.`membership`, `org`.`name` `orgName` 
				FROM %org_officer% `officer`
					LEFT JOIN %db_org% `org` ON `officer`.`orgId` = `org`.`orgId`
				WHERE `officer`.`uid` = :userId',
				'var' => [
					':userId' => $userId
				]
			])->items;
		} catch (Exception $exception) {
			return [];
		}
	}

	public static function countGroupByUserId($userId) {
		if (!DB::tableExists('org_officer')) return 0;

		try {
			return DB::select([
				'SELECT COUNT(*) `amt` FROM %org_officer% WHERE `uid` = :userId LIMIT 1',
				'var' => [
					':userId' => $userId
				]
			])->amt;
		} catch (Exception $exception) {
			return 0;
		}
	}

	private function getUserInfo() {
		$result = DB::select([
			'SELECT * FROM %users% u WHERE `uid` = :userId LIMIT 1',
			'var' => [':userId' => $this->userId]
		]);

		if (empty($result->uid)) return null;

		foreach ($result as $key => $value) $this->{$key} = $value;
		$this->fullName = trim($this->real_name.' '.$this->last_name);

		$this->roles = empty($result->roles) ? array('member') : explode(',','member,'.$result->roles);

		import('model:bigdata.php');

		foreach (BigDataModel::getJson('user/profile/'.$this->userId) as $key => $value) {
			$this->{$key} = $value;
		}

		foreach ($dbs = BigDataModel::items('user/profile.*/'.$this->userId)->items as $value) {
			$key = preg_replace('/profile\./', '', $value->name);
			$this->{$key} = $value->value;
			// $this->bigData[] = $value;
		}
		// debugMsg($dbs,'$dbs');
		// debugMsg($this, '$this');
	}

	private static function setUserCookie($sessionId, $username, $rememberTime) {
		// Set session id
		setcookie(
			cfg('cookie.id'),
			$sessionId,
			[
				'expires' => $rememberTime, // Expires in minute
				'path' => cfg('cookie.path'),
				'domain' => cfg('cookie.domain'),
				'secure' => true,           // Highly recommended (HTTPS only)
				'httponly' => true,         // Prevents JavaScript access
				'samesite' => 'Lax'         // Restricts cross-site requests
			]
		);

		// Set user name
		setcookie(
			cfg('cookie.u'),
			$username,
			[
				'expires' => $rememberTime, // Expires in minute
				'path' => cfg('cookie.path'),
				'domain' => cfg('cookie.domain'),
				'secure' => true,           // Highly recommended (HTTPS only)
				'httponly' => true,         // Prevents JavaScript access
				'samesite' => 'Lax'         // Restricts cross-site requests
			]
		);
	}

	private static function clearUserCookie() {
		setcookie(cfg('cookie.id'), "", time() - 3600, cfg('cookie.path'), cfg('cookie.domain'));
		setcookie(cfg('cookie.u'), "", time() - 3600, cfg('cookie.path'), cfg('cookie.domain'));
	}
}
?>