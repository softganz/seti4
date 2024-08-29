<?php
/**
* Admin   :: List of Cache Log
* Created :: 2018-03-07
* Modify  :: 2024-08-29
* Version :: 1
*
* @return Widget
*
* @usage admin/log/cache
*/

use Softganz\DB;
use Softganz\SetDataModel;

class AdminLogCache extends Page {
	var $order;
	var $clearCaches;
	var $showData;
	var $user;

	function __construct($arg1 = NULL) {
		parent::__construct([
			'order' => SG\getFirst(post('o'),'expire'),
			'clearCaches' => post('cid'),
			'showData' => post('data'),
			'user' => post('user')
		]);
	}

	function rightToBuild() {
		return true;
	}

	function build() {
		if ($this->clearCaches) $this->delete();

		$ctime = time();
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
							'options' => ['' => '== Select user =='] + $this->getUser()
						],
					], // children
				]),
			]), // AppBar
			'body' => $this->list($data),
		]);
	}

	function data() {
		return DB::select([
			'SELECT
			c.*,
			`expire` as `remain`,
			u.`name`, u.`last_login`, u.`roles`
			FROM %cache% c
				LEFT JOIN %users% u ON u.`username` = c.`headers`
			%WHERE%
			ORDER BY $ORDER$ ASC',
			'where' => [
				'%WHERE%' => [
					$this->user ? ['c.`headers` = :user', ':user' => $this->user] : NULL
				]
			],
			'var' => [
				'$ORDER$' => addslashes($this->order)
			]
		]);
	}

	function getUser() {
		return DB::select([
			'SELECT `cache`.`headers`, `user`.`name`
			FROM %cache% `cache`
				LEFT JOIN %users% `user` ON `cache`.`headers` = `user`.`username`
			GROUP BY `headers`
			ORDER BY CONVERT(`name` USING tis620) ASC',
			'options' => ['key' => 'headers', 'value' => 'name']
		])->items;
	}

	function delete() {
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
							'<a href="?o=headers">header</a>',
							'<a href="?o=roles">roles</a>',
							'remain -nowrap' => '<a href="?o=expire">remain in sec.</a>',
							'created -nowrap' => '<a href="?o=created">created</a>',
							'login -nowrap' => '<a href="?o=last_login">last login</a>',
							'<a href="?o=cid">cid</a>'
						],
						'children' => array_map(
							function($rs) use($ctime) {
								return [
									'<input type="checkbox" name="cid[]" value="'.$rs->cid.'" />',
									'<strong>'.$rs->headers.'</strong><br />'.$rs->name,
									$rs->roles,
									// $rs->remain .'-'. $ctime.'='.($rs->remain - $ctime),
									sg_remain2day($rs->remain - $ctime),
									date('Y-m-d H:i:s', $rs->created),
									$rs->last_login,
									$rs->cid
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
}
?>