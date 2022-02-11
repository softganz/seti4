<?php
/**
* iMed :: Admin Member List
* Created 2019-05-06
* Modify  2020-12-16
*
* @param Object $self
* @return String
*
* @usage imed/admin/member
*/

$debug = true;

function imed_admin_member($self) {
	if (!user_access('administer imeds')) return message('access denied');
	$searchStr = post('q');
	$order = SG\getFirst(post('o'),'u.`datein`');
	$sort = SG\getFirst(post('s'),'DESC');

	$ret .= '<section id="imed-admin-member" data-url="'.url('imed/admin/member', array('q' => $searchStr)).'">';

	$ret .= '<form id="search-member" class="sg-form search-box" method="get" action="'.url('imed/admin/member').'" role="search" data-rel="replace:#imed-admin-member">'
		. '<input type="hidden" name="sid" id="sid" />'
		. '<input type="hidden" name="o" value="name" />'
		. '<input type="hidden" name="s" value="ASC" />'
		. '<input class="" type="text" name="q" id="search-box" size="30" value="'.htmlspecialchars($searchStr).'" placeholder="Username or Name or Email" data-query="'.url('admin/get/username').'" data-callback="submit">'
		. '<button><i class="icon -search"></i></button>'
		. '</form>';


	if (post('u')) mydb::where('u.`username`=:username',':username',post('u'));
	else if ($searchStr) mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q)',':q','%'.$searchStr.'%');
	if (post('r')) mydb::where('u.roles=:role',':role',post('r'));

	mydb::value('$ORDER$', $order);
	mydb::value('$SORT$', $sort);

	$stmt = 'SELECT
		  u.*
		, GROUP_CONCAT(z.`zone`,",",z.`module`,",",z.`refid`,",",`right` SEPARATOR "<br />") `userzone`
		FROM %users% AS u
			LEFT JOIN %db_userzone% z USING(`uid`)
		%WHERE%
		GROUP BY u.`uid`
		ORDER BY $ORDER$ $SORT$
		LIMIT 100';

	$dbs = mydb::select($stmt);
	
	//$ret .= mydb()->_query;
	//$ret.=print_o($dbs);

	$ui = new Ui(NULL, 'ui-card');

	foreach ($dbs->items as $rs) {
		if ($rs->uid==1) continue;
		$ui->add(
			'<div class="header">'
			. '<span class="profile"><img class="poster-photo" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
			. '<span class="poster-name">'.$rs->name.' ('.$rs->username.' : '.$rs->uid.')</span><br />'
			. $rs->email
			. '</span>'
			. '<nav class="nav -header -sg-text-right"><a class="sg-action btn -link" href="'.url('imed/admin/user/'.$rs->uid).'" title="Edit user property" data-rel="box" data-width="480" data-height="90%" data-webview="'.$rs->name.'">'
			. '<i class="icon -material">edit</i></a></nav>'
			. '</div>'
			. '<div class="detail">'
			. ($rs->userzone ? $rs->userzone : 'No Zone Right').'<br />'
			. ($rs->admin_remark ? '<font color="#f60">Admin remark : '.$rs->admin_remark.'</font><br />':'')
			. 'Date In @'.sg_date($rs->datein,'d-m-Y G:i').'<br />'
			. '</div>',
			array(
				'class' => 'user-'.$rs->status,
				'title' => 'User was '.$rs->status
			)
		);
	}
	$ret .= $ui->build();

	$ret .= '</section>';

	return $ret;
}
?>