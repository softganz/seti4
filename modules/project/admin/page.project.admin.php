<?php
/**
* Project :: Admin Main Page
* Created 2021-10-30
* Modify  2021-10-30
*
* @return Widget
*
* @usage project/admin
*/

$debug = true;

class ProjectAdmin extends Page {
	var $arg1;

	function __construct($arg1 = NULL) {
		$this->arg1 = $arg1;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Project Administrator v'.cfg('project.version'),'admin',
			]),
			'sideBar' => R::View('project.admin.menu'),
			'body' => new ScrollView([
				'children' => [
					new Table([
						'class' => 'user-list',
						'caption' => 'รายชื่อสมาชิก',
						'thead' => ['name' => 'ชื่อ', 'amt' => 'โครงการ', 'zone' => 'หน่วยงาน', 'กลุ่ม', 'date' => 'วันที่สมัคร',],
						'children' => (function() {
							$childrens = [];

							mydb::value('$items',100);
							mydb::value('$order','uid');
							mydb::value('$sort','DESC');

							$stmt = 'SELECT SQL_CALC_FOUND_ROWS
								  u.*
								, (SELECT GROUP_CONCAT(org.`name` SEPARATOR "<br />") FROM %org_officer% o LEFT JOIN %db_org% org USING(`orgid`) WHERE o.`uid`=u.`uid`) `orgName`
								, (SELECT COUNT(*) FROM %topic% t WHERE t.`type`="project" AND t.`uid`=u.`uid`) `projects`
								, (SELECT COUNT(*) FROM %topic% d WHERE d.`type`="project-develop" AND d.`uid`=u.`uid`) `develops`
								FROM %users% AS u
								ORDER BY $order $sort
								LIMIT $items';

							$dbs = mydb::select($stmt);

							foreach ($dbs->items as $rs) {
								if ($rs->uid==1) continue;
								$childrens[] = [
									'<a class="sg-action" href="'.url('project/admin/user/'.$rs->uid).'" data-rel="box" data-width="480" title="User Information"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
									($rs->projects?$rs->projects:'-').'/'.($rs->develops?$rs->develops:'-'),
									$rs->orgName
									.($rs->admin_remark?'<br /><font color="#f60">Admin remark : '.sg_text2html($rs->admin_remark).'</font>':''),
									$rs->roles,
									sg_date($rs->datein,'d-m-Y G:i'),
									'config'=>array('class'=>'user-'.$rs->status,'title'=>'User was '.$rs->status)
								];
							}
							return $childrens;
						})(), // children
					]), // Table
				], // children
			]), // ScrollView
		]);
	}
}
?>