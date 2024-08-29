<?php
/**
* Model   :: User Information
* Created :: 2021-07-22
* Modify  :: 2024-08-289
* Version :: 9
*
* @param Int $userId
* @return Object
*
* @usage new UserModel($userId)
* @usage UserModel::function($conditions, $options)
*/

use Softganz\DB;

class UserModel {
	var $userId;

	function __construct($userId = NULL) {
		$this->userId = empty($userId) ? i()->uid : $userId;
		if ($this->userId) $this->_getUserInfo();
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
			$conditions = (Object) ['id' => $id];
		}

		if ($id) mydb::where('`uid` = :userId', ':userId', $id);
		else if ($conditions->username) mydb::where('`username` = :username', ':username', $conditions->username);
		else if ($conditions->email) mydb::where('`email` = :email', ':email', $conditions->email);
		else return NULL;

		$result = mydb::select('SELECT u.`uid` `userId`, u.* FROM %users% u %WHERE% LIMIT 1');

		if ($debug) debugMsg(mydb()->_query);

		if ($result->_empty) return NULL;

		mydb::clearProp($result);
		$result->roles = empty($result->roles) ? array('member') : explode(',','member,'.$result->roles);

		return $result;
	}

	public static function create($user, $options = '{}') {
		$defaults = '{debug: false}';
		$options = \SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_object($user)) ; // Do nothing
		else if (is_string($user) && preg_match('/^{/',$user)) $user = \SG\json_decode($user);
		else if (is_array($user)) $user = (Object) $user;
		else $user = (Object) [];

		if (empty($user->username)) {
			return (Object) [
				'uid' => NULL,
				'complete' => false,
				'error' => true,
				'text' => 'Username not specify'
			];
		} else if (UserModel::get(['username' => $user->username])) {
			return (Object) [
				'uid' => NULL,
				'complete' => false,
				'error' => true,
				'text' => 'Username was duplicate'
			];
		}

		$result = (Object) [
			'userId' => NULL,
			'uid' => NULL,
			'complete' => false,
			'error' => false,
			'password' => NULL,
			'username' => $user->username,
			'name' => $user->name,
			'email' => $user->email,
			'auth' => 'user',
			'process' => ['UserModel::create() => request']
		];


		// debugMsg($user,'$user');

		if (in_array(cfg('member.registration.method'), ['email'])) {
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

		$user->encryptPassword = $user->password ? sg_encrypt($user->password,cfg('encrypt_key')) : NULL;
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

		mydb::query(
			'INSERT INTO %users%
			( `username` , `password` , `name` , `roles`, `phone` , `email` , `real_name`, `last_name`, `status` , `datein` , `about`, `organization`, `admin_remark` )
			VALUES
			( :username , :encryptPassword , :name , :userRoles, :phone , :email , :realName, :lastName, "enable" , :datein , "", :organization, :admin_remark )',
			$user
		);

		if (mydb()->_error) {
			$result->complete = false;
			$result->error = mydb()->_error;
			$result->process[] = 'UserModel::create() => create error';
			return $result;
		}

		$result->userId = $result->uid = $user->uid = mydb()->insert_id;
		$result->password = $user->encryptPassword;
		if ($debug) $result->process[] = mydb()->_query;
		$result->complete = true;


		sgSendLog([
			'file' => __FILE__,
			'line' => __LINE__,
			'type' => 'Create user',
			'user' => SG\getFirst(i()->uid, $result->userId),
			'name' => SG\getFirst(i()->name, $user->name),
			'description' => (Object) [
				'username' => $user->username,
				'name' => $user->name,
				'id' => $result->userId,
				'email' => $user->email,
			],
		]);

		if ($user->roles) {
			mydb::query(
				'INSERT INTO %users_role%
				(`uid`, `role`, `status`, `approved`, `created`)
				VALUES
				(:userId, :role, :status, :approved, :created)
				ON DUPLICATE KEY UPDATE
				`uid` = :userId
				',
				[
					':userId' => $result->uid,
					':role' => $user->roles->role,
					':status' => $user->roles->status,
					':approved' => $user->roles->approved,
					':created' => date('U'),
				]
			);
			if ($debug) $result->process[] = mydb()->_query;
		}

		event_tricker('user.create_user',$self,$user,$form,$result);

		$result->process[] = 'UserModel::create() => create complete';

		return $result;
	}

	//TODO: Change value in table cache/session
	public static function changeUserName($oldUsername, $newUsername) {
		if (empty($oldUsername) || empty($newUsername)) return false;
		if ($oldUsername === $newUsername) return false;
		if (file_exists('file/'.$newUsername)) return false;

		$newUserInfo = UserModel::get(['username' => $newUsername]);
		if ($newUserInfo->userId) return false;

		mydb::query(
			'UPDATE %users% SET `username` = :newUsername WHERE `username` = :oldUsername',
			[
				':oldUsername' => $oldUsername,
				':newUsername' => $newUsername,
			]
		);
		if (!mydb()->_error) {
			$oldFolder = 'file/'.$oldUsername;
			$newFolder = 'file/'.$newUsername;

			if (file_exists('file/'.$oldUsername)) rename($oldFolder, $newFolder);

			// Rename cache
			mydb::query(
				'UPDATE %cache% SET `headers` = :newUsername WHERE `headers` = :oldUsername',
				[
					':oldUsername' => $oldUsername,
					':newUsername' => $newUsername,
				]
			);
			// debugMsg(mydb()->_query);
		}
	}

	public static function updatePassword($userId, $password) {
		$newPassword = sg_encrypt($password,cfg('encrypt_key'));
		mydb::query(
			'UPDATE %users%
			SET `password` = :newPassword
			WHERE `uid` = :userId
			LIMIT 1',
			[
				':userId' => $userId,
				':newPassword' => $newPassword
			]
		);

		$userName = mydb::select('SELECT `username` FROM %users% WHERE `uid` = :userId LIMIT 1', [':userId' => $userId])->username;

		BasicModel::watch_log('user','Password change','User '.$userName.' ('.$userId.') was change password');
	}

	// delete user information
	public static function delete($uid) {
		$rs = UserModel::get($uid);
		$result = (Object) [
			'code' => NULL,
			'message' => NULL,
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
			mydb::query(
				'DELETE FROM %users% WHERE `uid` = :uid LIMIT 1',
				[':uid' => $uid]
			);

			mydb::query(
				'DELETE FROM %topic_user% WHERE `uid` = :uid',
				[':uid' => $uid]
			);

			if (mydb::table_exists('org_officer')) {
				mydb::query(
					'DELETE FROM %org_officer% WHERE `uid` = :uid',
					[':uid' => $uid]
				);
			}
		}
		return $result;
	}

	public static function clearLogin() {
		$user = (Object) [
			'ok' => false,
			'group' => [],
		];
		setcookie(cfg('cookie.id'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
		setcookie(cfg('cookie.u'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
		$_SESSION['user'] = null;
	}

	public static function signOutProcess() {
		$token = i()->token;
		$result = (Object) [
			'signed' => false,
			'group' => [],
			'cookie.id' => cfg('cookie.id'),
			'cookie.path' => cfg('cookie.path'),
			'cookie.domain' => cfg('cookie.domain'),
			'token' => $token,
			'session' => $_SESSION['user'],
			'i' => i(),
		];
		if (i()->ok) {
			$cacheId = 'user:'.$token;
			setcookie(cfg('cookie.id'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
			setcookie(cfg('cookie.u'),"",time() - 3600,cfg('cookie.path'),cfg('cookie.domain'));
			$_SESSION['user'] = NULL;
			Cache::Clear($cacheId);
		}
		// $result->signed = i()->ok;
		return $result;
	}

	public static function signInProcess($username = NULL, $password = NULL, $cookielength = NULL) {
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
			BasicModel::watch_log('user','Invalid signin','user '.$username.' not exists or disabled');
			return false;
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
			BasicModel::watch_log('user', 'Invalid signin', $username.' incorrect password');
			return false;
		}

		// Sign in ok :: Set session id to cookie
		if ($cookielength == -1) $cookielength = 10*365*24*60;
		if (empty($cookielength)) $cookielength = cfg('member.signin.remembertime');
		$remember_time = time()+$cookielength*60;

		// Create JWT token
		$session_id = Jwt::generate(
			[
				"type" => "JWT",
				"alg" => "HS256"
			],
			['id' => intval($rs->uid), 'username' => $rs->username, 'name' => $rs->name, 'roles' => $rs->roles ? explode(',',$rs->roles) : [], 'exp' => $remember_time ],
			cfg('system')->jwt->secret
		);

		if (strlen($session_id) >= 255) $session_id = md5(uniqid(rand(), true));

		setcookie(cfg('cookie.id'),$session_id,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
		setcookie(cfg('cookie.u'),$rs->username,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));

		$debug = false;//$username=='softganz';

		$debug_str .= '<p>cookie.id : '.cfg('cookie.id').'</p>';
		$debug_str .= '<p>cookie.u : '.cfg('cookie.u').'</p>';
		$debug_str .= '<p>cookie.path : '.cfg('cookie.path').'</p>';
		$debug_str .= '<p>cookie.domain : '.cfg('cookie.domain').'</p>';
		$debug_str .= '<p>remember time : '.$remember_time.' second.</p>';

		// add session into cache
		$user = (Object) [
			'ok' => true,
			'uid' => intval($rs->uid),
			'username' => $rs->username,
			'name' => $rs->name,
			'email' => $rs->email,
			'remember' => $cookielength*60,
			'ip' => GetEnv('REMOTE_ADDR'),
			'admin' => false,
			'session' => $session_id,
			'token' => $session_id,
			'roles' => $rs->roles ? explode(',',$rs->roles) : [],
		];

		$_SESSION['user'] = $user;

		cache::add('user:'.$session_id, $user, $remember_time, $username);

		mydb::query(
			'UPDATE %users% SET
			`last_login` = `login_time` ,
			`last_login_ip` = `login_ip` ,
			`login_time` = :login_time,
			`login_ip` = :ip
			WHERE uid = :uid LIMIT 1',
			[
				':login_time' => date('Y-m-d H:i:s'),
				':ip' => ip2long($user->ip),
				':uid' => $user->uid,
			]
		);

		BasicModel::watch_log('user','Signin','user '.$username.' was signin',$user->uid);

		$debug_str .= '<p>query='.mydb()->_query.'</p>';
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
			'email' => NULL,
			'name' => NULL,
			'prefix' => NULL,
			'signin' => true,
			'token' => NULL,
		]
	) {
		$user = (Object) $user;
		if (empty($user->email) || empty($user->name)) return false;

		do {
			$username = $user->prefix.\SG\uniqid(20);
		} while (UserModel::get(['username' => $username]));

		$createUserResult = UserModel::create([
			'username' => $username,
			'password' => NULL,
			'name' => $user->name,
			'email' => $user->email,
		]);

		if (!$createUserResult->uid) {
			return (Object) [
				'responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE,
				'text' => 'ไม่สามารถสร้างสมาชิกตามข้อมูลที่ระบุได้',
			];
		}

		$result = (Object) [
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
		$result = (Object) [];
		$session_id = $args['token'];
		$cookielength = 10*365*24*60;
		$remember_time = time()+$cookielength*60;
		// if ($cookielength == -1) $cookielength = 10*365*24*60;

		if ($args['email'] && $session_id) {
			$user = mydb::select('SELECT * FROM %users% WHERE `email` = :email LIMIT 1', [':email' => $args['email']]);
			$result->query = mydb()->_query;
			// debugMsg($user, '$user');
			if (!$user->uid) return (Object) ['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'Invalid email'];
			$result = (Object) [
				'ok' => true,
				'uid' => intval($user->uid),
				'username' => $user->username,
				'name' => $user->name,
				'email' => $user->email,
				'session' => $session_id,
				'token' => $session_id,
				'remember' => $cookielength*60,
				'ip' => GetEnv('REMOTE_ADDR'),
				'admin' => false,
				'roles' => $user->roles ? explode(',',$user->roles) : [],
			];

			$_SESSION['user'] = $result;
			cache::add('user:'.$session_id, $result, $remember_time, $result->username);

			if ($cookielength == -1) $cookielength = 10*365*24*60;
			if (empty($cookielength)) $cookielength = cfg('member.signin.remembertime');
			$remember_time = time()+$cookielength*60;
			setcookie(cfg('cookie.id'),$session_id,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
			setcookie(cfg('cookie.u'),$result->username,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));

			mydb::query(
				'UPDATE %users% SET
				`last_login` = `login_time` ,
				`last_login_ip` = `login_ip` ,
				`login_time` = :login_time,
				`login_ip` = :ip
				WHERE uid = :uid LIMIT 1',
				[
					':login_time' => date('Y-m-d H:i:s'),
					':ip' => ip2long($result->ip),
					':uid' => $result->uid,
				]
			);
			BasicModel::watch_log('user','Signin by Google','Email '.$result->email.' ('.$result->username.') was signin with Google',$result->uid);
		}
		return $result;
	}

	public static function checkLogin() {
		$user = (Object) [
			'ok' => false,
			'signInResult' => NULL,
		];

		// echo ('Get login '.print_o($_COOKIE,'$_COOKIE'));

		if (function_exists("apache_request_headers")) {
			$headers = apache_request_headers();
			$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
		}

		// User sign in from any page using POST method only
		if ($_POST['username'] && $_POST['password']) {
			cache::clear_expire();
			$username = $_POST['username'];
			$password = $_POST['password'];
			$remember = $_POST['remember'];
			$cookielength = $_POST['cookielength'] ? intval($_POST['cookielength']) : NULL;
			// echo '$username = '.$username.' $password = '.$password;
			// print_r($_POST);
			// if ($username=='softganz') die($username);

			if (!is_numeric($cookielength)) return $user;


			// check for email signin
			if (strpos($username,'@')) {
				$user_email = mydb::select('SELECT `username` FROM %users% WHERE `email` = :email', [':email' => $username]);
				// print_o($user_email,'$user_email',1);
				if ($user_email->_num_rows == 1) {
					$username = $user_email->items[0]->username;
				} else {
					$user->signInResult = tr('Invalid email or too many email');
					return $user;
				}
			}

			$error = NULL;
			if (empty($username) || empty($password)) {
				$user->signInResult = tr('Invalid user signin');
				return $user;
			}

			$result = UserModel::signInProcess($username,$password,$cookielength);

			if ($result === false) {
				$user->signInResult = tr('Invalid user signin');
				return $user;
			}
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
					BasicModel::watch_log('user','Invalid signin','Email '.$result->email.' ('.$result->username.') signin with Google error',$result->uid);
				}

				return $result->uid ? $result : (Object) ['signInErrorMessage' => 'Google account '.$jwt->payload->email.' is not recognized for Google Sign-In on this site. Please make sure you are using the same account that you have previously linked.'];
			} else {
				BasicModel::watch_log('user','Invalid signin','Invalid credential => '.$credential);
				return (Object) ['signInResult' => 'Invalid user signin'];
			}
		} else if ($authHeader) {
			list($authType, $authToken) = explode(' ', $authHeader);
			$user = Cache::get('user:'.$authToken)->data;
			// $user->authHeader = $authHeader;
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
				$remember_time = time()+$data->remember;
				setcookie(cfg('cookie.id'),$data->session,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
				setcookie(cfg('cookie.u'),$data->username,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
				$stmt = 'UPDATE %cache% SET `expire` = :expire WHERE `cid` = :cid LIMIT 1';
				//echo '$remember_time='.$remember_time.'<br />';
				mydb::query($stmt,':cid','user:'.$data->session, ':expire',$remember_time);
				//echo mydb()->_query.'<br />';
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

		$result = NULL;

		if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object) $conditions;
		else {
			$conditions = (Object) ['code' => $conditions];
		}

		$code = $conditions->code;

		$result->complete=false;
		$result->error=false;
		$result->process[]='user.email.confirm request';

		if (empty($code)) $result->error[]='Empty registration code';

		if ($code) {
			$stmt = 'SELECT * FROM %users% WHERE status IN ("","waiting") AND code = :code LIMIT 1';
			$result->user=$user=mydb::select($stmt, ':code',$code);
			$result->query[]=mydb()->_query;
			if ($user->_num_rows) {
				mydb::query('UPDATE %users% SET status="enable",code=NULL WHERE uid='.$user->uid.' LIMIT 1');
				$result->query[]=mydb()->_query;
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
		if (empty($prefixUsername)) return NULL;

		$prefixUsername = strtolower($prefixUsername);
		$lastUsername = mydb::select(
			'SELECT MAX(`username`) `username` FROM %users% WHERE `username` LIKE :prefixUsername LIMIT 1',
			[':prefixUsername' => $prefixUsername.$sep.'%']
		)->username;

		list($username,$lastid) = explode($sep,$lastUsername);
		if (empty($username)) $username = $prefixUsername;
		$nextUsername = $prefixUsername.$sep.sprintf('%0'.$length.'d', $lastid + 1);
		return $nextUsername;
	}

	private function _getUserInfo() {
		$result = mydb::select('SELECT * FROM %users% u WHERE `uid` = :userId LIMIT 1', ':userId', $this->userId);

		if ($result->_empty) return NULL;

		mydb::clearProp($result);
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

	public static function profilePhoto($username = NULL, $fullSize = true) {
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
		mydb::query(
			'UPDATE %users% SET `status` = "disable", `admin_remark` = CONCAT(IFNULL(`admin_remark`, ""), "@'.date('Y-m-d H:i:s').' ลบบัญชีโดยเจ้าของ") WHERE `uid` = :userId LIMIT 1',
			[':userId' => $userId]
		);
	}
}
?>