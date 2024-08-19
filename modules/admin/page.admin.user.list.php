<?php
/**
* Admin   :: List All Member
* Created :: 2019-09-01
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/user/list
*/

class AdminUserList extends Page {
	var $role;
	var $status;
	var $search;
	var $uid;
	var $order;
	var $items;
	var $page;

	function __construct($arg1 = NULL) {
		$this->role = post('r');
		$this->status = post('s');
		$this->search = post('q');
		$this->uid = post('sid');

		$this->order = \SG\getFirst(post('order'),'uid');
		$this->items = \SG\getFirst(post('items'), 100);
		$this->page = \SG\getFirst(post('page'), 1);
	}

	function build() {
		$orderList = [
			'uid' => 'u.`uid`',
			'username' => 'u.`username`',
			'email' => 'u.`email`',
			'name' => 'CONVERT(u.`name` USING tis620)',
			'role' => 'u.`roles`',
			'reg' => 'u.`datein`',
			'login' => 'u.`last_login`',
		];

		if ($this->uid) {
			mydb::where('u.`username` = :sid', ':sid', $this->uid);
		} else if ($this->search) {
			mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q)', ':q', '%'.$this->search.'%');
		}

		if ($this->status) mydb::where('u.`status` = :status', ':status', $this->status);
		if ($this->role) {
			mydb::where(is_numeric($this->role) ? 'u.`roles` != ""' : 'u.`roles` LIKE :role', ':role', '%'.$this->role.'%');
			$this->order = 'role';
		}

		mydb::value('$ORDER$', $orderList[$this->order]);
		mydb::value('$SORT$', 'ASC');

		if ($this->item < 0) {
			mydb::value('$LIMIT$', '');
		} else {
			$firstRow = $this->page > 1 ? ($this->page - 1) * $this->items : 0;
			mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$this->items);
		}

		$stmt = 'SELECT SQL_CALC_FOUND_ROWS
			u.`uid`, u.`username`, u.`name`
			, u.`status`
			, u.`email`, u.`roles`, u.`datein`, u.`login_time`, u.`last_login`
			, u.`admin_remark`
			FROM %users% AS u
			%WHERE%
			ORDER BY $ORDER$ $SORT$
			$LIMIT$
			';

		$dbs = mydb::select($stmt);
		// debugMsg(mydb()->_query);
		// debugMsg($dbs,'$dbs');

		$totals = $dbs->_found_rows;

		$pagePara['r'] = $this->role;
		$pagePara['s'] = $this->status;
		$pagePara['q'] = $this->search;
		$pagePara['page'] = $this->page;
		$pagePara['items'] = $this->items;
		$pagePara['order'] = $this->order;
		$pagePara['sort'] = $this->sort;

		$pageNav = new PageNavigator($this->items, $this->page, $totals, q(), false, $pagePara);

		$currentUrl = q();

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Web Site Members'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					new ScrollView(['child' => $pageNav,]),
					new ScrollView([
						'child' => new Table([
							'class' => 'user-list',
							'caption' => 'User listing',
							'thead' => [
								'',
								'name -nowrap'=>'Name <a href="'.url($currentUrl, ['order' => 'name']+$pagePara).'"><i class="icon -material'.($getOrderBy == 'name' ? ' -sg-active' : ' -sg-inactive').' -no-print">unfold_more</i></a>',
								'email'=>'Email <a href="'.url($currentUrl, ['order' => 'email']+$pagePara).'"><i class="icon -material'.($getOrderBy == 'email' ? ' -sg-active' : ' -sg-inactive').' -no-print">unfold_more</i></a>',
								'roles'=>'Roles <a href="'.url($currentUrl, ['order' => 'role']+$pagePara).'"><i class="icon -material'.($getOrderBy == 'role' ? ' -sg-active' : ' -sg-inactive').' -no-print">unfold_more</i></a>',
								'reg -date'=>'Register date <a href="'.url($currentUrl, ['order' => 'reg']+$pagePara).'"><i class="icon -material'.($getOrderBy == 'reg' ? ' -sg-active' : ' -sg-inactive').' -no-print">unfold_more</i></a>',
								'currentIn -date' => 'Current Login',
								'last -date' => 'Last login <a href="'.url($currentUrl, ['order' => 'login']+$pagePara).'"><i class="icon -material'.($getOrderBy == 'login' ? ' -sg-active' : ' -sg-inactive').' -no-print">unfold_more</i></a>',
								'remark -hover-parent' => 'Admin remark',
							],
							'children' => array_map(
								function ($rs) {
									if ($rs->uid == 1) return NULL;
									$rolesList = [];
									foreach (explode(',', $rs->roles) as $value) {
										$rolesList[] = '<a href="'.url('admin/user/list', ['r' => $value]).'">'.$value.'</a>';
									}

									$menu = new Ui();
									$menu->addConfig('nav', '{class: "nav -hover -icons -no-print"}');
									$menu->add('<a class="sg-action" href="'.url('profile/'.$rs->uid).'" title="View user profile" data-rel="box" data-width="640"><i class="icon -material">find_in_page</i></a>');
									$menu->add('<a class="sg-action" href="'.url('admin/user/edit/'.$rs->uid).'" data-rel="box" title="Edit user property" data-width="640"><i class="icon -material">edit</i></a>');

									return [
										'<img class="profile-photo" src="'.BasicModel::user_photo($rs->username).'" width="48" height="48" />',
										'<strong>'.$rs->name.'</strong><br />'.$rs->username.'('.$rs->uid.')',
										$rs->email,
										$rolesList ? implode(', ', $rolesList) : '',
										sg_date($rs->datein,'M j, Y @G:i'),
										$rs->login_time ? sg_date($rs->login_time,'M j, Y @G:i') : '',
										$rs->last_login ? sg_date($rs->last_login,'M j, Y @G:i') : '',
										$rs->admin_remark
										.$menu->build(),
										'config' => [
											'class' => 'user-'.$rs->status,
											'title' => 'User was '.$rs->status,
										]
									];
								}
								, $dbs->items
							), // children
						]), // Table
					]), // ScrollView
					new ScrollView(['child' => $pageNav,]),
					'<style>
					.user-list th {white-space: nowrap;}
					.page-nv {display: flex; white-space: nowrap; align-items: center; justify-content: flex-end;}
					.page-nv .page-items {margin: 0 4px;}
					.page-nv a {margin: 0 4px;}
					</style>'
				], // children
			]), // Widget
		]);
	}
}
?>