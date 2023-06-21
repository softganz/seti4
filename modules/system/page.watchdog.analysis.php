<?php
/**
* Watchdog :: Analysis
* Created 2020-01-01
* Modify  2020-06-07
*
* @param Object $self
* @param Array $_GET
* @return String
*/

$debug = true;

function watchdog_analysis($self) {
	$module = post('module');
	$keyword = post('keyword');
	$days = intval(\SG\getFirst(post('d'),30));
	$items = \SG\getFirst(post('items'),1000);
	$ret = '';

	$self->theme->title = 'Watchdog Anlysis';
	user_menu('analysis', 'analysis', url('watchdog/analysis'));

	$is_admin = user_access('administer watchdogs');

	if ($is_admin && $whatdelete = post('delete')) {
		if ($whatdelete == 'emptymodule') {
			mydb::query('DELETE FROM %watchdog% WHERE `module` IS NULL OR `module` = ""');
		} else {
			list($module, $keyword) = explode(':', $whatdelete);
			mydb::query('DELETE FROM %watchdog% WHERE '.($module ? '`module` = :module' : '`module` IS NULL').' AND `keyword` = :keyword', ':module', $module, ':keyword', $keyword);
		}
		$ret .= 'Delete watchdog completed.';
		return $ret;
	}

	if ($module || $keyword) {
		if ($module) mydb::where('w.`module` = :module',':module',$module);
		if ($keyword) mydb::where('w.`keyword` = :keyword',':keyword',$keyword);
		if (post('u')) mydb::where('w.`uid` = :uid',':uid',post('u'));
		if (post('msg')) mydb::where('w.`message` LIKE :msg',':msg','%'.post('msg').'%');

		$stmt = 'SELECT SQL_CALC_FOUND_ROWS w.*, u.`uid`, u.`username`
			FROM %watchdog% w
				LEFT JOIN %users% u USING(uid)
			%WHERE%
			ORDER BY wid DESC
			LIMIT '.$items;

		$logs = mydb::select($stmt);

		$ret .= '<header class="header -box"><h3>Watchdog List '.$logs->count().($logs->count() == $items ? ' of '.$logs->_found_rows : '').' items</h3></header>';

		$ret .= R::View('watchdog.listing',$logs);
		return $ret;
	}

	$stmt = 'SELECT COUNT(*) `totalLog`, COUNT(DISTINCT DATE_FORMAT(`date`, "%Y-%m-%d")) `totalDate` FROM %watchdog% LIMIT 1';
	$summaryDbs = mydb::select($stmt);

	$self->theme->title .= ' '.number_format($summaryDbs->totalLog).' items in '.number_format($summaryDbs->totalDate).' days';

	$ui = new Ui();

	$form = new Form(NULL, url('watchdog/analysis'), NULL, '-inlineitem');
	$form->addConfig('method', 'GET');
	$form->addField('d', array('type' => 'text', 'label' => 'Show Last ', 'class' => '-numeric', 'size' => 6, 'value' => $days, 'posttext' => ' days'));
	$form->addField('go', array('type' => 'button', 'value' => '<i class="icon -material">search</i><span>GO</span>'));
	$ui->add($form->build());

	if ($is_admin) {
		$ui->add('<a class="sg-action btn -link" href="'.url('watchdog/analysis',array('delete'=>'emptymodule')).'" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?" data-rel="notify"><i class="icon -material">delete</i><span>Clear empty module on watchlog</span></a>&nbsp;');
	}

	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	mydb::where('TIMESTAMPDIFF(DAY, `date`, NOW()) < :days', ':days', $days);

	$stmt = 'SELECT
		`module`
		, `keyword`
		, COUNT(*) totals
		FROM %watchdog%
		%WHERE%
		GROUP BY `module`, `keyword`';

	$dbs = mydb::select($stmt);

	//$ret .= mydb()->_query;

	$tables = new Table();
	$tables->addClass('watchdog-keyword-list');
	$tables->thead=array('Module', 'Keyword', 'amt' => 'Count', 'icons' => '');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			'<a class="sg-action" href="'.url('watchdog/analysis',array('module'=>$rs->module)).'" data-rel="box">'.$rs->module.'</a>',
			'<a class="sg-action" href="'.url('watchdog/analysis',array('module'=>$rs->module,'keyword'=>urlencode($rs->keyword))).'" data-rel="box">'.$rs->keyword.'</a>',
			$rs->totals,
			$is_admin?'<a class="sg-action" href="'.url('watchdog/analysis','delete='.$rs->module.':'.$rs->keyword).'" data-title="Delete logs" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?" data-rel="this" data-done="remove:parent tr"><i class="icon -material">delete</i></a>':'',
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>