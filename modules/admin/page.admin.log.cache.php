<?php
/**
* Admin   :: List of Cache Log
* Created :: 2018-03-07
* Modify  :: 2024-08-29
* Version :: 2
*
* @return Widget
*
* @usage admin/log/cache
*/

use Softganz\DB;
use Softganz\SetDataModel;

class AdminLogCache extends Page {
	var $clearCaches;
	var $showData;
	var $user;
	var $order = 'expire';
	var $sort = 'ASC';

	function __construct($arg1 = NULL) {
		parent::__construct([
			'order' => SG\getFirst(post('order'), $this->order),
			'clearCaches' => post('cid'),
			'showData' => post('data'),
			'user' => post('user'),
			'sort' => SG\getFirst(post('sort'), $this->sort),
		]);
	}

	function rightToBuild() {
		return true;
	}

	function build() {
		if ($this->clearCaches) $this->delete();

		$data = $this->data();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Cache viewer '.number_format($data->count).' sessions.',
				'child' => new Form([
					'class' => 'sg-form form-report',
					'action' => url('admin/log/cache'),
					'rel' => '#main',
					'children' => [
						'user' => [
							'type' => 'select',
							'onChange' => 'submit',
							'value' => $this->user,
							'options' => ['' => '== Select user =='] + $this->getUser()
						],
					], // children
				]),
			]), // AppBar
			'body' => $this->list($data),
		]);
	}

	private function data() {
		return DB::select([
			'SELECT
			`cache`.*,
			`expire` as `remain`,
			`user`.`name`, `user`.`last_login`, `user`.`roles`
			FROM %cache% `cache`
				LEFT JOIN %users% `user` ON `user`.`username` = `cache`.`headers`
			%WHERE%
			ORDER BY $ORDER$ $SORT$, `cache`.`headers` ASC',
			'where' => [
				'%WHERE%' => [
					$this->user ? ['`cache`.`headers` = :user', ':user' => $this->user] : NULL,
					$this->order === 'roles' ? ['`user`.`roles` != ""'] : NULL
				]
			],
			'var' => [
				'$ORDER$' => addslashes($this->order),
				'$SORT$' => addslashes($this->sort)
			]
		]);
	}

	private function getUser() {
		return DB::select([
			'SELECT `cache`.`headers`, `user`.`name`
			FROM %cache% `cache`
				LEFT JOIN %users% `user` ON `cache`.`headers` = `user`.`username`
			GROUP BY `headers`
			ORDER BY CONVERT(`name` USING tis620) ASC',
			'options' => ['key' => 'headers', 'value' => 'name']
		])->items;
	}

	private function delete() {
		if (empty($this->clearCaches)) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'ไม่ระบุรายการที่ต้องการลบ');

		DB::query([
			'DELETE FROM %cache%
			WHERE cid IN ( :cid )',
			'var' => [
				':cid' => new SetDataModel($this->clearCaches)
			]
		]);
	}

	function list($data) {
		$ctime = time();
		return new Form([
			'class' => 'sg-form',
			'action' => url('admin/log/cache'),
			'rel' => '#main',
			'children' => [
				new Row([
					'style' => 'gap: 8px; margin-bottom: 16px;',
					'children' => [
						'<button class="btn" type="submit" name="delete" value="Delete"><i class="icon -material">delete</i><span>DELETE SELECTED CACHES</span></button>',
						'<a class="btn" href="'.url('admin/log/cache').'"><i class="icon -material">refresh</i><span>REFRESH</span></a>',
						'<a class="sg-action btn -danger" href="'.url('admin/log/cache/clear').'" data-rel="none" data-title="Clear All Caches" data-confirm="ต้องการล้างแคชทั้งหมด กรุณายืนยัน?"><span>CLEAR ALL CACHES</span></a>'
					], // children
				]),
				new ScrollView([
					'child' => new Table([
						'caption' => 'Cache viewer',
						'thead' => [
							'',
							'headers -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'headers']).'" data-rel="#main">User</a>',
							'roles -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'roles']).'" data-rel="#main">Roles</a>',
							'remain -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'expire']).'" data-rel="#main">Remain time</a>',
							'created -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'created', 'sort' => 'DESC']).'" data-rel="#main">Cache create time</a>',
							'login -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'last_login', 'sort' => 'DESC']).'" data-rel="#main">Last login time</a>',
							'cid -nowrap' => '<a class="sg-action" href="'.url('admin/log/cache', ['user' => $this->user, 'order' => 'cid']).'" data-rel="#main">Cache ID</a>',
						],
						'children' => array_map(
							function($rs) use($ctime) {
								return [
									'<input type="checkbox" name="cid[]" value="'.$rs->cid.'" />',
									'<strong><a class="sg-action" href="'.url('admin/log/cache', ['user' => $rs->headers]).'" data-rel="#main">'.$rs->headers.'</a></strong><br />'.$rs->name,
									$rs->roles,
									// $rs->remain .'-'. $ctime.'='.($rs->remain - $ctime),
									sg_remain2day($rs->remain - $ctime),
									date('Y-m-d H:i:s', $rs->created),
									$rs->last_login,
									'<a class="sg-action" href="'.url('admin/log/cache..detail', ['detail' => $rs->cid]).'" data-rel="box" data-width="full">'.$rs->cid.'</a>'
									// '<details><summary>'.$rs->cid.'</summary>'
									// . '<pre>'.print_r(preg_match('/^\{/', $rs->data) ? json_decode($rs->data) : unserialize($rs->data), 1).'</pre>'
									// . '</details>'
								];
							},
							(Array) $data->items
						), // children
					]), // Table
				]), // ScrollView
				// 	if ($showData) $tables->rows[] = '<tr><td colspan="7">'.$rs->data.'</td></tr>';
			], // children
		]);
	}

	function detail() {
		$token = post('detail');
		if (empty($token)) return error(_HTTP_ERROR_BAD_REQUEST, 'ไม่ระบุ');

		$data = DB::select([
			'SELECT `data` FROM %cache% WHERE `cid` = :token LIMIT 1',
			'var' => [':token' => $token]
		])->data;

		return new ScrollView([
			'child' => '<pre>'.print_r(preg_match('/^\{/', $data) ? json_decode($data) : unserialize($data), 1).'</pre>'
		]);
	}
}
?>