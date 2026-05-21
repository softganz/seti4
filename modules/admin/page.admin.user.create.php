<?php
/**
 * Admin  :: Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2016-11-08
 * Modify  :: 2026-05-18
 * Version :: 2
 *
 * @return Widget
 *
 * @use admin/user/create
 */

use Softganz\DB;

class AdminUserCreate extends Page {
	protected $users;
	private $fields = 'uid,username,password,name,email,roles,phone,address,orgId,membership,admin_remark';

	function __construct() {
		parent::__construct([
			'users' => Request::post('users')
		]);
	}

	#[\Override]
	function build() {
		if ($this->users) {
			$result = $this->create($this->users);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Create many users',
				'leading' => new Icon('group_add'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$this->form(implode(',', (Array) $result->remains)),
					$this->showResult($result),
					// new DebugMsg($result, '$result'),
				], // children
			]), // Widget
		]);
	}

	private function form($users = null) {
		return new Form([
			'action' => Url::link('admin/user/create'),
			'id' => 'admin-user-create',
			'users' => [
				'type' => 'textarea',
				'label' => 'Users',
				'class' => '-fill',
				'rows' => 20,
				'cols' => 60,
				'value' => $users,
				'description' => 'Description:<ul><li>Each user per line.</li><li>Seperate each field with ,</li><li>Field list: ' . $this->fields . '</li></ul>'
			],
			'submit' => [
				'type' => 'button',
				'items' => [
					'reset' => [
						'type' => 'reset',
						'value' => '<i class="icon -material -gray">restart_alt</i><span>Reset</span>'
					],
					'cancel' => [
						'type' => 'cancel',
						'value' => '<i class="icon -material -gray">cancel</i><span>Cancel</span>'
					],
					'save' => [
						'type' => 'submit',
						'class' => '-primary',
						'value' => '<i class="icon -material">done_all</i><span>Create users</span>'
					],
				],
				'container' => ['class' => '-sg-text-right']
			]
		]);
	}

	private function showResult($result = null) {
		return new Widget([
			'children' => [
				new Header([
					'title' => 'Create complete',
				]),
				new Table([
					'thead' => explode(',', $this->fields),
					'children' => (Array) $result->complete
				]),
				new Header([
					'title' => 'Create error',
				]),
				new Table([
					'thead' => explode(',', $this->fields),
					'children' => array_map(
						function($remain) {
							return explode(',', $remain);
						},
						(Array) $result->remains
					)
				]),
				new Header([
					'title' => 'Query',
				]),
				new ListOrder([
					'children' => (Array) $result->query
				])
			]
		]);
	}

	private function create($users = '') {
		if (empty($users)) return;

		$result = (Object) [
			'complete' => [],
			'remains' => [],
			'query' => [],
		];

		$lines = explode("\n", $users);

		foreach ($lines as $key => $line) {
			$data = list($uid, $username, $password, $name, $email, $roles, $phone, $address, $orgId, $membership, $admin_remark) = explode(',', trim($line));

			if (empty($uid)) $uid = NULL;

			$username = trim($username);
			$password = trim($password);
			if (empty($password)) $password = substr(md5(uniqid()), 0, 8);
			$name = trim($name);
			$email = trim($email);
			$roles = str_replace('+', ',', trim($roles));
			$phone = trim($phone);
			$epassword = sg_encrypt($password, cfg('encrypt_key'));
			$datein = date('Y-m-d H:i:s');
			$status = 'enable';
			$admin_remark = trim($admin_remark);
			$data[2] = $password;
			if (empty($username) || empty($password)) continue;
			if (empty($name)) $name = $username;

			try {
				$userId = DB::query([
					'INSERT INTO %users%
					(`uid`, `username`, `password`, `name`, `status`, `roles`, `email`, `phone`, `address`, `admin_remark`, `datein`)
					VALUES
					(:uid, :username, :password, :name, :status, :roles, :email, :phone, :address, :admin_remark, :datein)',
					'var' => [
						':uid' => $uid,
						':username' => $username,
						':password' => $epassword,
						':name' => $name,
						':status' => $status,
						':roles' => $roles,
						':email' => $email,
						':phone' => $phone,
						':address' => $address,
						':admin_remark' => $admin_remark,
						':datein' => $datein
					]
				])->insertId();
				$data[0] = $userId;
				$result->complete[] = $data;
				$result->query[] = R('query');
			} catch (\Exception $exception) {
				$result->query[] = R('query');
				$result->remains[] = $line;
			}

			if ($orgId && $userId) {
				try {
					DB::query([
						'INSERT INTO %org_officer%
						(`uid`, `orgId`, `membership`)
						VALUES
						(:uid, :orgId, :membership)
						ON DUPLICATE KEY UPDATE
						`membership` = :membership',
						'var' => [
							':uid' => $userId,
							':orgId' => $orgId,
							':membership' => $membership ?? 'ADMIN'
						],
						// 'options' => ['debug' => true]
					]);
				} catch (\Exception $exception) {
				}
			}
			// if (!mydb()->_error) {
			// 	$data[0] = mydb()->insert_id;
			// 	$complete[] = implode(',', $data);
			// 	unset($lines[$key]);
			// } else {
			// 	$ret .= '<p>'.mydb()->_query.'</p>';
			// }
		}
		// $post->users = implode("\n",$lines);
		return $result;
	}
}
/**
 * Create many users in one click
 *
 * @param $_POST
 * @return String
 */
function admin_user_create($self) {
	$ret .='<h2>Create many users</h2>'._NL;
	if ($_POST['cancel']) location('admin/user');


	$post=(object)post();

	if (post('cancel')) location('admin/user');
	if ($post->users) {
		$lines=explode("\n",$post->users);
		foreach ($lines as $key => $line) {
			$data = list($uid,$username,$password,$name,$email,$roles,$phone,$address,$admin_remark) = explode(',',$line);
			if (empty($uid)) $uid = NULL;
			$username = trim($username);
			$password = trim($password);
			if (empty($password)) $password = substr(md5(uniqid()), 0, 8);
			$name = trim($name);
			$email = trim($email);
			$roles = str_replace('+',',',trim($roles));
			$phone = trim($phone);
			$epassword = sg_encrypt($password,cfg('encrypt_key'));
			$datein = date('Y-m-d H:i:s');
			$status = 'enable';
			$admin_remark = trim($admin_remark);
			$data[2] = $password;
			if (empty($username) || empty($password)) continue;
			if (empty($name)) $name = $username;
			$stmt = 'INSERT INTO %users%
				(`uid`, `username`, `password`, `name`, `status`, `roles`, `email`, `phone`, `address`, `admin_remark`, `datein`)
				VALUES
				(:uid, :username, :password, :name, :status, :roles, :email, :phone, :address, :admin_remark, :datein)';

			mydb::query($stmt,':uid',$uid, ':username',$username, ':password',$epassword, ':name',$name, ':status',$status, ':roles',$roles, ':email',$email, ':phone',$phone, ':address',$address, ':admin_remark',$admin_remark, ':datein',$datein);

			if (!mydb()->_error) {
				$data[0] = mydb()->insert_id;
				$complete[] = implode(',', $data);
				unset($lines[$key]);
			} else {
				$ret .= '<p>'.mydb()->_query.'</p>';
			}
		}
		$post->users = implode("\n",$lines);
	}

	$form = new Form('profile',url(q()),'admin-user-create');

	$form->addField(
		'users',
		array(
			'type'=>'textarea',
			'label'=>'User description :<br />uid,username,password,name,email,roles,phone,address',
			'name'=>'users',
			'class'=>'-fill',
			'rows'=>20,
			'cols'=>60,
			'value'=>htmlspecialchars($post->users),
			'description'=>'Enter username,password,name,email,roles,phone. Each user per line'
		)
	);

	$form->addField(
		'submit',
		array(
			'type'=>'button',
			'items'=>array(
				'save'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -material">done_all</i><span>Create users</span>'
				),
				'cancel'=>array(
					'type'=>'cancel',
					'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
				),
				'reset'=>array(
					'type'=>'reset',
					'value'=>'<i class="icon -material">restart_alt</i><span>Reset</span>'
				),
			),
		)
	);

	$ret.=$form->build();

	if ($complete) {
		$ret.='<h3>User was created</h3>';
		$ret.=implode('<br />',$complete);
	}
	return $ret;
}
?>