<?php
/**
* Model :: User Information
* Created 2021-07-22
* Modify  2021-07-22
*
* @param Int $userId
* @return Object
*
* @usage new UserModel($userId)
*/

$debug = true;

class UserModel {
	var $userId;

	function __construct($userId = NULL) {
		$this->userId = empty($userId) ? i()->uid : $userId;
		if ($this->userId) $this->_getUserInfo();
	}

	public static function get($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) ;
		else if (is_array($conditions)) $conditions = (object) $conditions;
		else {
			$id = $conditions;
			$conditions = (Object) ['id' => $id];
		}

		if ($id) mydb::where('`uid` = :id');
		else if ($conditions->username) mydb::where('`username` = :username');
		else if ($conditions->email) mydb::where('`email` = :email');
		else return NULL;

		$result = mydb::select('SELECT * FROM %users% u %WHERE% LIMIT 1', $conditions);

		if ($debug) debugMsg(mydb()->_query);

		if ($result->_empty) return NULL;

		mydb::clearprop($result);
		$result->roles = empty($result->roles) ? array('member') : explode(',','member,'.$result->roles);

		return $result;
	}

	public static function create($user, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if (is_object($user)) ; // Do nothing
		else if (is_string($user) && preg_match('/^{/',$user)) $user = SG\json_decode($user);
		else if (is_array($user)) $user = (Object) $user;
		else $user = (Object) [];

		$result = (Object) [
			'uid' => NULL,
			'complete' => false,
			'error' => false,
			'password' => NULL,
			'auth' => 'user',
			'process' => ['UserModel::create() => request'],
		];


		// debugMsg($user,'$user');

		if (in_array(cfg('member.registration.method'),array('email'))) {
			$user->status = 'waiting';
			$user->code = md5(uniqid(rand())); // better, difficult to guess
			$result->process[] = 'create user with email registration method';
		} else if (in_array(cfg('member.registration.method'),array('waiting','waiting,email'))) {
			$user->status = 'waiting';
			$result->process[] = 'create user with admin check registration method';
		} else {
			$user->status = 'enable';
			$result->process[] = 'create user and ready to used';
		}

		$user->encryptPassword = sg_encrypt($user->password,cfg('encrypt_key'));
		$user->datein = 'func.NOW()';
		if (empty($user->about)) $user->about = '';
		if (empty($user->phone)) $user->phone = '';
		if (empty($user->email)) $user->email = '';
		if (empty($user->organization)) $user->organization = '';
		$user->realName = SG\getFirst($user->realName);
		$user->lastName = SG\getFirst($user->lastName);
		$user->admin_remark = SG\getFirst($user->admin_remark);

		$stmt = 'INSERT INTO %users%
			( `username` , `password` , `name` , `phone` , `email` , `real_name`, `last_name`, `status` , `datein` , `about`, `organization`, `admin_remark` )
			VALUES
			( :username , :encryptPassword , :name , :phone , :email , :realName, :lastName, "enable" , :datein , "", :organization, :admin_remark )';

		mydb::query($stmt, $user);

		if (mydb()->_error) {
			$result->complete = false;
			$result->error = mydb()->_error;
			$result->process[] = 'UserModel::create() => create error';
			return $result;
		}

		$result->uid = $user->uid = mydb()->insert_id;
		$result->password = $user->encryptPassword;
		$result->process[] = mydb()->_query;
		$result->complete = true;

		if ($user->roles) {
			mydb::query(
				'INSERT INTO %users_role%
				(`uid`, `role`, `status`, `created`)
				VALUES
				(:userId, :role, :status, :created)
				ON DUPLICATE KEY UPDATE
				`uid` = :userId
				',
				[
					':userId' => $result->uid,
					':role' => $user->roles->role,
					':status' => $user->roles->status,
					':created' => date('U'),
				]
			);
			$result->process[] = mydb()->_query;
		}

		event_tricker('user.create_user',$self,$user,$form,$result);

		$result->process[] = 'UserModel::create() => create complete';

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

	public static function signInProcess($username = NULL, $password = NULL, $cookielength = false) {
		$rs = mydb::select('SELECT * FROM %users% u WHERE `username` = :username LIMIT 1', [':username' => $username]);

		//TODO: Bug ตอนลงทะเบียนสมาชิกใหม่ จะไม่สามารถดึงค่าจากฐานข้อมูลผ่าน function ได้

		$user = (Object) ['rs' => $rs];

		if (!in_array($rs->status, ['enable',1])) {
			model::watch_log('user','Invalid signin','user '.$username.' not exists or disabled');
			return false;
		}
		$de_password = sg_decrypt($rs->password,cfg('encrypt_key'));

		// sign in password error -> log
		if ( $password != $de_password ) {
			$ip = GetEnv('REMOTE_ADDR');
			$stmt = 'UPDATE %users% SET tries = tries+1, remote_ip = :ip , date_tries = NOW() WHERE username = :username LIMIT 1';
			mydb::query($stmt,':ip',$ip, ':username',$username);
			model::watch_log('user','Invalid signin',$username.' incorrect password');
			return false;
		}

		// Sign in ok :: Set session id to cookie
		$session_id = md5(uniqid(rand(), true));
		if ($cookielength == -1) $cookielength = 10*365*24*60;
		if (empty($cookielength)) $cookielength = cfg('member.signin.remembertime');
		$remember_time = time()+$cookielength*60;
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
			'session' => $session_id,
			'remember' => $cookielength*60,
			'ip' => GetEnv('REMOTE_ADDR'),
			'admin' => false,
			'roles' => $rs->roles ? explode(',',$rs->roles) : [],
		];

		$_SESSION['user'] = $user;

		cache::add('user:'.$session_id, $user, $remember_time, $username);


		model::watch_log('user','Signin','user '.$username.' was signin',$user->uid);

		$stmt = 'UPDATE %users% SET
			`last_login` = `login_time` ,
			`last_login_ip` = `login_ip` ,
			`login_time` = :login_time,
			`login_ip` = :ip
			WHERE uid = :uid LIMIT 1';

		mydb::query($stmt,':login_time', date('Y-m-d H:i:s'),':ip',ip2long($user->ip),':uid',$user->uid);

		$debug_str .= '<p>query='.mydb()->_query.'</p>';
		$debug_str .= print_o($_COOKIE,'$COOKIE');
		$debug_str .= print_o($user,'$user');
		if ($debug) echo $debug_str;

		return $user;
	}

	public static function checkLogin() {
		$user = (Object) [
			'ok' => false,
			'signInResult' => NULL,
		];

		// echo ('Get login '.print_o($_COOKIE,'$_COOKIE'));

		// User sign in from any page
		if ($_POST['username'] && $_POST['password']) {
			cache::clear_expire();
			$username = $_POST['username'];
			$password = $_POST['password'];
			$remember = $_POST['remember'];
			$cookielength = $_POST['cookielength'];
			// echo '$username = '.$username.' $password = '.$password;
			//if ($username=='softganz') die($username);

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

	function _getUserInfo() {
		$result = mydb::select('SELECT * FROM %users% u WHERE `uid` = :userId LIMIT 1', ':userId', $this->userId);

		if ($result->_empty) return NULL;

		mydb::clearprop($result);
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

	public static function validUsername($username, $pattern = NULL) {
		if (is_null($pattern)) $pattern = cfg('member.username.format');

		$result = true;

		if (strlen($username) < 4) {
			//-- username length
			$result = 'ชื่อสมาชิก (Username) อย่างน้อย 4 อักษร';
		} else if (!preg_match($pattern, $username)) {
			//-- check valid char
			$result = 'ชื่อสมาชิก (Username) <strong><em>'.$username.'</em></strong> มีอักษรหรือความยาวไม่ตรงตามเงื่อนไข';
		} else if (mydb::select(
			'SELECT `username` FROM %users% WHERE `username` = :username LIMIT 1;
			-- {reset: false}',
			[':username' => $username]
		)->username) {
			//-- duplicate username
			$result = 'ชื่อสมาชิก (Username) <strong><em>'.$register->username.'</em></strong> มีผู้อื่นใช้ไปแล้ว กรุณาใช้ชื่อใหม่';
		}
		return $result;
	}
}
?>