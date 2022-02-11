<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_assign($self, $jobInfo) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'PROCESS ERROR');

	$shopId = $jobInfo->shopid;

	$getJobType = post('ty');

	new Toolbar($self,'ใบสั่งงาน','job',$jobInfo);

	$headerUi = new Ui();
	$headerUi->addConfig('nav', '{class: "nav"}');
	$headerUi->add('<a class="sg-action btn -primary" data-rel="back">เรียบร้อย</a>');

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>กำหนด'.$getJobType.'</h3>'.$headerUi->build().'</header>';

	$stmt = 'SELECT
		su.*, u.`username`, u.`name`
		, d.`dotype`
		FROM %garage_user% su
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %garage_do% d ON d.`tpid` = :tpid AND d.`uid` = su.`uid`
		WHERE su.`shopid` = :shopid OR (su.`shopid` IN (SELECT `shopparent` FROM %garage_shop% WHERE `shopid` = :shopid))';
	$dbs = mydb::select($stmt, ':shopid', $shopId, ':tpid', $jobId);

	$ui = new Ui(NULL, 'ui-menu garage-job-assign');
	foreach ($dbs->items as $rs) {
		$ui->add(
			'<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/assign.'.($rs->dotype ? 'remove' : 'save').'/'.$rs->uid, array('ty'=>$getJobType)).'" data-rel="notify" data-done="load:parent .box-page:'.url('garage/job/'.$jobId.'/assign',array('ty'=>$getJobType)).'">'
			. '<div class="profile-photo -sg-32">'
			//. '<i class="icon">'
			. '<img src="'.model::user_photo($rs->username).'" width="100%" height="100%" />'
			//. '</i>'
			. '</div>'
			. $rs->name
			. ($rs->dotype ? ' ('.$rs->dotype.')' : '')
			.'</a>'
			. '<nav class="nav -icons -hover -center-right"><i class="icon -material">'.($rs->dotype ? 'cancel' : 'done').'</i></nav>',
			array('class' => '-hover-parent'.($rs->dotype ? ' -active' : ''))
		);
	}

	$ret .= $ui->build();

	//$ret .= print_o($dbs,'$dbs');

	//$ret .= print_o($jobInfo);
	return $ret;
}
?>